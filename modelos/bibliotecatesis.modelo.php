<?php
require_once __DIR__ . '/../core/conexion.modelo.php';
require_once __DIR__ . '/prestamotesis.modelo.php';
require_once __DIR__ . '/institucion.modelo.php';

/**
 * Biblioteca de tesis: catálogo de los trabajos de grado (tabla trabajogrado, fuente única
 * del título; se registra y corrige en Registro de documentos), con el autor (estudiante),
 * el programa y si el ejemplar físico está prestado (tabla prestamotesis).
 * Aquí solo hay SELECT: los préstamos se escriben en PrestamoTesisModelo.
 */
class BibliotecaTesisModelo
{
    const CAMPOS = ['todo', 'autor', 'titulo', 'palabras'];
    const ORDENES = ['recientes', 'antiguas', 'titulo', 'autor'];
    const POR_PAGINA = 15;

    // Año de la tesis: aprobación, si no la entrega, si no el registro
    const SQL_ANIO = "YEAR(COALESCE(tg.FechaAprobacion, tg.FechaEntrega, tg.FechaRegistro))";

    /**
     * Filtros normalizados a partir de lo que llega por GET.
     * $soloAprobadas forzado (estudiantes y docentes solo ven las aprobadas).
     */
    public static function Filtros(array $get, $forzarAprobadas = false)
    {
        $campo = $get['campo'] ?? 'todo';
        $orden = $get['orden'] ?? 'recientes';
        return [
            'texto' => mb_substr(trim(preg_replace('/\s+/u', ' ', (string) ($get['q'] ?? ''))), 0, 200),
            'campo' => in_array($campo, self::CAMPOS, true) ? $campo : 'todo',
            'programa' => max(0, (int) ($get['programa'] ?? 0)),
            'grado' => preg_replace('/[^A-Z]/', '', strtoupper((string) ($get['grado'] ?? ''))),
            'anio' => (int) ($get['anio'] ?? 0),
            'aprobadas' => $forzarAprobadas || !empty($get['aprobadas']),
            'orden' => in_array($orden, self::ORDENES, true) ? $orden : 'recientes',
        ];
    }

    /* Palabras buscadas (2 letras o más; se ignoran "y", "a"...), como máximo 10 */
    public static function Terminos(array $f)
    {
        if ($f['texto'] === '') {
            return [];
        }
        if ($f['campo'] === 'titulo') {
            return [$f['texto']];
        }
        $palabras = array_filter(explode(' ', $f['texto']), function ($p) {
            return mb_strlen($p) >= 2;
        });
        return array_slice(array_values($palabras), 0, 10);
    }

    /**
     * WHERE de la búsqueda.
     *  - autor:    cada palabra debe estar en el nombre/apellidos o C.I. del estudiante
     *  - titulo:   la frase tal cual dentro del título
     *  - palabras: cada palabra clave debe estar en el título (en cualquier orden)
     *  - todo:     cada palabra en el título o en el autor
     * La comparación ignora mayúsculas y tildes (collation utf8mb4_general_ci).
     */
    private static function Condiciones(array $f)
    {
        $autor = "CONCAT_WS(' ', e.Nombre, e.Apaterno, e.Amaterno, e.Apaterno, e.Nombre, e.Ci)";
        $condiciones = ["TRIM(tg.TituloTrabajo) <> ''"];
        $parametros = [];

        foreach (self::Terminos($f) as $termino) {
            $like = '%' . self::EscaparLike($termino) . '%';
            if ($f['campo'] === 'titulo' || $f['campo'] === 'palabras') {
                $condiciones[] = "tg.TituloTrabajo LIKE ?";
                $parametros[] = $like;
            } elseif ($f['campo'] === 'autor') {
                $condiciones[] = "$autor LIKE ?";
                $parametros[] = $like;
            } else {
                $condiciones[] = "(tg.TituloTrabajo LIKE ? OR $autor LIKE ?)";
                $parametros[] = $like;
                $parametros[] = $like;
            }
        }
        if ($f['programa'] > 0) {
            $condiciones[] = "tg.ProgramaID = ?";
            $parametros[] = $f['programa'];
        }
        if ($f['grado'] !== '') {
            $condiciones[] = "p.GradoAcademico = ?";
            $parametros[] = $f['grado'];
        }
        if ($f['anio'] > 0) {
            $condiciones[] = self::SQL_ANIO . " = ?";
            $parametros[] = $f['anio'];
        }
        if ($f['aprobadas']) {
            $condiciones[] = "tg.FechaAprobacion IS NOT NULL";
        }
        return [implode(' AND ', $condiciones), $parametros];
    }

    public static function ContarTesis(array $f)
    {
        list($where, $parametros) = self::Condiciones($f);
        $stmt = Conexion::Conectar()->prepare("
            SELECT COUNT(*)
            FROM trabajogrado tg
            INNER JOIN estudiante e ON e.EstudianteID = tg.EstudianteID
            INNER JOIN programa p ON p.ProgramaID = tg.ProgramaID
            WHERE $where");
        $stmt->execute($parametros);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Una página de resultados. Cada tesis trae, si el ejemplar está ocupado, el estado del
     * préstamo (APROBADO = reservada, PRESTADO) y la fecha límite.
     */
    public static function BuscarTesis(array $f, $pagina = 1)
    {
        list($where, $parametros) = self::Condiciones($f);
        $ordenes = [
            'recientes' => "COALESCE(tg.FechaAprobacion, tg.FechaEntrega, DATE(tg.FechaRegistro)) DESC, tg.TituloTrabajo",
            'antiguas' => "COALESCE(tg.FechaAprobacion, tg.FechaEntrega, DATE(tg.FechaRegistro)) ASC, tg.TituloTrabajo",
            'titulo' => "tg.TituloTrabajo",
            'autor' => "e.Apaterno, e.Amaterno, e.Nombre",
        ];
        $prestamo = PrestamoTesisModelo::TablaExiste()
            ? "(SELECT CONCAT(pt.Estado, '|', COALESCE(pt.FechaLimite, '')) FROM prestamotesis pt
                WHERE pt.IdTrabajo = tg.IdTrabajo AND pt.Estado IN ('APROBADO', 'PRESTADO')
                ORDER BY pt.IdPrestamo DESC LIMIT 1)"
            : "NULL";
        $offset = (max(1, (int) $pagina) - 1) * self::POR_PAGINA;

        $stmt = Conexion::Conectar()->prepare("
            SELECT tg.IdTrabajo, tg.TituloTrabajo, tg.Tribunal, tg.CalificacionNumeral,
                   tg.FechaEntrega, tg.FechaAprobacion, " . self::SQL_ANIO . " AS Anio,
                   e.Ci, e.Nombre, e.Apaterno, e.Amaterno,
                   p.NombrePrograma, p.GradoAcademico, p.Version, p.Sede,
                   $prestamo AS PrestamoActual
            FROM trabajogrado tg
            INNER JOIN estudiante e ON e.EstudianteID = tg.EstudianteID
            INNER JOIN programa p ON p.ProgramaID = tg.ProgramaID
            WHERE $where
            ORDER BY {$ordenes[$f['orden']]}
            LIMIT " . self::POR_PAGINA . " OFFSET " . (int) $offset);
        $stmt->execute($parametros);
        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as &$fila) {
            $partes = $fila['PrestamoActual'] ? explode('|', $fila['PrestamoActual']) : [null, null];
            $fila['EstadoEjemplar'] = $partes[0] ?: 'DISPONIBLE';
            $fila['FechaLimiteEjemplar'] = $partes[1] ?: null;
        }
        return $filas;
    }

    /* Cifras del encabezado: tesis (aprobadas si $soloAprobadas), programas y prestadas */
    public static function Estadisticas($soloAprobadas)
    {
        $db = Conexion::Conectar();
        $filtro = $soloAprobadas ? "AND tg.FechaAprobacion IS NOT NULL" : "";
        $fila = $db->query("
            SELECT COUNT(*) AS Tesis, COUNT(DISTINCT tg.ProgramaID) AS Programas,
                   COUNT(DISTINCT tg.EstudianteID) AS Autores
            FROM trabajogrado tg
            WHERE TRIM(tg.TituloTrabajo) <> '' $filtro")->fetch(PDO::FETCH_ASSOC);
        $fila['Prestadas'] = PrestamoTesisModelo::TablaExiste()
            ? (int) $db->query("SELECT COUNT(DISTINCT IdTrabajo) FROM prestamotesis WHERE Estado = 'PRESTADO'")->fetchColumn()
            : 0;
        return $fila;
    }

    /* Programas que tienen al menos un trabajo de grado (para el filtro) */
    public static function ProgramasConTesis($soloAprobadas = false)
    {
        $stmt = Conexion::Conectar()->query("
            SELECT DISTINCT p.ProgramaID, p.NombrePrograma, p.Version
            FROM trabajogrado tg
            INNER JOIN programa p ON p.ProgramaID = tg.ProgramaID
            WHERE TRIM(tg.TituloTrabajo) <> ''" . ($soloAprobadas ? " AND tg.FechaAprobacion IS NOT NULL" : "") . "
            ORDER BY p.NombrePrograma, p.Version
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Grados académicos y años que tienen tesis (para los filtros) */
    public static function GradosYAnios($soloAprobadas = false)
    {
        $filtro = "TRIM(tg.TituloTrabajo) <> ''" . ($soloAprobadas ? " AND tg.FechaAprobacion IS NOT NULL" : "");
        $db = Conexion::Conectar();
        $grados = $db->query("
            SELECT DISTINCT p.GradoAcademico FROM trabajogrado tg
            INNER JOIN programa p ON p.ProgramaID = tg.ProgramaID
            WHERE $filtro ORDER BY p.GradoAcademico")->fetchAll(PDO::FETCH_COLUMN);
        $anios = $db->query("
            SELECT DISTINCT " . self::SQL_ANIO . " AS Anio FROM trabajogrado tg
            WHERE $filtro ORDER BY Anio DESC")->fetchAll(PDO::FETCH_COLUMN);
        return [array_values(array_filter($grados)), array_values(array_filter($anios))];
    }

    /* Tribunal guardado como "PRESIDENTE: X|TRIBUNAL: Y|..." → [['PRESIDENTE', 'X'], ...] */
    public static function TribunalLineas($tribunal)
    {
        $lineas = [];
        foreach (array_filter(array_map('trim', explode('|', (string) $tribunal))) as $linea) {
            $partes = array_map('trim', explode(':', $linea, 2));
            $lineas[] = count($partes) === 2 ? $partes : ['', $partes[0]];
        }
        return $lineas;
    }

    /**
     * Referencia en estilo APA 7 (para copiar en una bibliografía):
     * Mamani Quispe, J. C. (2026). Título en tipo oración [Tesis de maestría, Universidad...].
     * El título se guarda en MAYÚSCULAS: se pasa a tipo oración (las siglas quedan en minúscula,
     * por eso se puede corregir antes de usarla).
     */
    public static function ReferenciaApa(array $t, $universidad)
    {
        $apellidos = InstitucionModelo::Formal(mb_strtolower(trim($t['Apaterno'] . ' ' . $t['Amaterno']), 'UTF-8'));
        $iniciales = [];
        foreach (preg_split('/\s+/u', trim((string) $t['Nombre'])) as $nombre) {
            if ($nombre !== '') {
                $iniciales[] = mb_strtoupper(mb_substr($nombre, 0, 1, 'UTF-8'), 'UTF-8') . '.';
            }
        }
        $titulo = mb_strtolower(trim($t['TituloTrabajo']), 'UTF-8');
        $titulo = mb_strtoupper(mb_substr($titulo, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($titulo, 1, null, 'UTF-8');
        $tipos = [
            'DIPLOMADO' => 'Monografía de diplomado',
            'ESPECIALIDAD' => 'Trabajo de grado de especialidad',
            'MAESTRIA' => 'Tesis de maestría',
            'DOCTORADO' => 'Tesis doctoral',
        ];
        $tipo = $tipos[$t['GradoAcademico']] ?? 'Trabajo de grado';
        return trim($apellidos . ', ' . implode(' ', $iniciales)) . ' (' . ($t['Anio'] ?: 's. f.') . '). '
            . rtrim($titulo, '.') . ' [' . $tipo . ', ' . InstitucionModelo::Formal(mb_strtolower($universidad, 'UTF-8')) . '].';
    }

    private static function EscaparLike($texto)
    {
        return addcslashes($texto, '%_\\');
    }
}
