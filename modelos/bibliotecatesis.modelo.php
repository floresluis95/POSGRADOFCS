<?php
require_once __DIR__ . '/../core/conexion.modelo.php';

/**
 * Biblioteca de tesis: SOLO CONSULTA (únicamente SELECT; no escribe en la base).
 * Los trabajos salen de la tabla trabajogrado (fuente única del título, ver Registro
 * de documentos), con el autor (estudiante) y el programa.
 */
class BibliotecaTesisModelo
{
    const CAMPOS = ['todo', 'autor', 'titulo', 'palabras'];

    /**
     * Busca tesis.
     *  - autor:    cada palabra debe estar en el nombre/apellidos o C.I. del estudiante
     *  - titulo:   la frase tal cual dentro del título
     *  - palabras: cada palabra clave debe estar en el título (en cualquier orden)
     *  - todo:     cada palabra en el título o en el autor
     * La comparación ignora mayúsculas y tildes (collation utf8mb4_general_ci).
     */
    public static function BuscarTesis($texto, $campo = 'todo', $programaID = 0, $soloAprobadas = false)
    {
        $campo = in_array($campo, self::CAMPOS, true) ? $campo : 'todo';
        $texto = trim(preg_replace('/\s+/u', ' ', (string) $texto));

        $autor = "CONCAT_WS(' ', e.Nombre, e.Apaterno, e.Amaterno, e.Apaterno, e.Nombre, e.Ci)";
        $condiciones = [];
        $parametros = [];

        if ($texto !== '') {
            if ($campo === 'titulo') {
                $condiciones[] = "tg.TituloTrabajo LIKE ?";
                $parametros[] = '%' . self::EscaparLike($texto) . '%';
            } else {
                // Palabras de 2 letras o más (se ignoran "de", "y", etc. si son de 1 letra)
                $palabras = array_filter(explode(' ', $texto), function ($p) {
                    return mb_strlen($p) >= 2;
                });
                foreach (array_slice(array_values($palabras), 0, 10) as $palabra) {
                    $like = '%' . self::EscaparLike($palabra) . '%';
                    if ($campo === 'autor') {
                        $condiciones[] = "$autor LIKE ?";
                        $parametros[] = $like;
                    } elseif ($campo === 'palabras') {
                        $condiciones[] = "tg.TituloTrabajo LIKE ?";
                        $parametros[] = $like;
                    } else {
                        $condiciones[] = "(tg.TituloTrabajo LIKE ? OR $autor LIKE ?)";
                        $parametros[] = $like;
                        $parametros[] = $like;
                    }
                }
            }
        }

        if ((int) $programaID > 0) {
            $condiciones[] = "tg.ProgramaID = ?";
            $parametros[] = (int) $programaID;
        }
        if ($soloAprobadas) {
            $condiciones[] = "tg.FechaAprobacion IS NOT NULL";
        }

        $sql = "
            SELECT tg.IdTrabajo, tg.TituloTrabajo, tg.Tribunal, tg.CalificacionNumeral,
                   tg.FechaEntrega, tg.FechaAprobacion,
                   e.Ci, e.Nombre, e.Apaterno, e.Amaterno,
                   p.NombrePrograma, p.GradoAcademico, p.Version, p.Sede
            FROM trabajogrado tg
            INNER JOIN estudiante e ON e.EstudianteID = tg.EstudianteID
            INNER JOIN programa p ON p.ProgramaID = tg.ProgramaID
            WHERE TRIM(tg.TituloTrabajo) <> ''" .
            ($condiciones ? ' AND ' . implode(' AND ', $condiciones) : '') . "
            ORDER BY COALESCE(tg.FechaAprobacion, tg.FechaEntrega, DATE(tg.FechaRegistro)) DESC, tg.TituloTrabajo
            LIMIT 300";

        $stmt = Conexion::Conectar()->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Programas que tienen al menos un trabajo de grado (para el filtro) */
    public static function ProgramasConTesis()
    {
        $stmt = Conexion::Conectar()->query("
            SELECT DISTINCT p.ProgramaID, p.NombrePrograma, p.Version
            FROM trabajogrado tg
            INNER JOIN programa p ON p.ProgramaID = tg.ProgramaID
            ORDER BY p.NombrePrograma, p.Version
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Tribunal guardado como "PRESIDENTE: X|TRIBUNAL: Y|..." → lista de líneas */
    public static function TribunalLineas($tribunal)
    {
        return array_values(array_filter(array_map('trim', explode('|', (string) $tribunal))));
    }

    private static function EscaparLike($texto)
    {
        return addcslashes($texto, '%_\\');
    }
}
