<?php
/**
 * Planillas de calificaciones ya generadas (carpeta pdfs_generados/).
 *
 * Cada vez que se genera la planilla de un módulo se guarda una copia del PDF
 * junto con un .json del mismo nombre con los datos para listarla después
 * (programa, módulo, docente, quién la generó y cuándo). No usa la base de
 * datos: el historial vive solo en esa carpeta.
 *
 * Nombre de los archivos nuevos: calificaciones_P{programa}_M{modulo}_{AAAAMMDD_HHMMSS}.pdf
 * Los anteriores a este cambio (calificaciones_{código}_{fecha}.pdf) no tienen
 * .json: se listan por el código del módulo y se marcan como "anteriores",
 * porque dos programas pueden tener un módulo con el mismo código.
 */
class PlanillaGuardadaModelo
{
    const PATRON_ARCHIVO = '/^calificaciones_[A-Za-z0-9 _\-ÁÉÍÓÚÑáéíóúñ]+_\d{8}_\d{6}\.pdf$/u';

    public static function Carpeta()
    {
        return __DIR__ . '/../pdfs_generados';
    }

    /**
     * Guarda el PDF y su ficha. Devuelve el nombre del archivo o '' si no se pudo.
     */
    public static function Guardar($contenidoPdf, array $datos)
    {
        $dir = self::Carpeta();
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        if (!is_dir($dir) || !is_writable($dir)) {
            return '';
        }
        $nombre = sprintf('calificaciones_P%d_M%d_%s.pdf', (int)$datos['programaID'], (int)$datos['moduloID'], date('Ymd_His'));
        if (@file_put_contents($dir . '/' . $nombre, $contenidoPdf) === false) {
            return '';
        }
        $datos['generado'] = date('Y-m-d H:i:s');
        @file_put_contents($dir . '/' . substr($nombre, 0, -4) . '.json', json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return $nombre;
    }

    /**
     * Planillas guardadas de un módulo de un programa, la más reciente primero.
     * Incluye las anteriores sin ficha cuyo código de módulo coincide.
     */
    public static function Listar($programaID, $moduloID, $moduloCodigo)
    {
        if (!self::PuedeVerModulo($programaID, $moduloID)) {
            return [];
        }
        $dir = self::Carpeta();
        $lista = [];
        if (!is_dir($dir)) {
            return $lista;
        }

        foreach (glob($dir . '/' . sprintf('calificaciones_P%d_M%d_*.pdf', (int)$programaID, (int)$moduloID)) ?: [] as $ruta) {
            $nombre = basename($ruta);
            $ficha = @json_decode((string)@file_get_contents(substr($ruta, 0, -4) . '.json'), true) ?: [];
            $lista[] = [
                'archivo' => $nombre,
                'generado' => $ficha['generado'] ?? date('Y-m-d H:i:s', filemtime($ruta)),
                'usuario' => $ficha['usuario'] ?? '',
                'docente' => $ficha['docenteNombre'] ?? '',
                'fechaInicio' => $ficha['fechaInicio'] ?? '',
                'fechaFin' => $ficha['fechaFin'] ?? '',
                'fechaFirma' => $ficha['fechaGeneracion'] ?? '',
                'estudiantes' => $ficha['totalEstudiantes'] ?? null,
                'anterior' => false,
            ];
        }

        // Archivos de antes de este cambio: calificaciones_{código}_{AAAAMMDD_HHMMSS}.pdf
        // (solo para el personal: no se sabe de qué programa son)
        $codigo = in_array($_SESSION['Tipo'] ?? '', ['ADM', 'SEC', 'AUX'], true) ? trim((string)$moduloCodigo) : '';
        if ($codigo !== '') {
            foreach (glob($dir . '/calificaciones_*.pdf') ?: [] as $ruta) {
                $nombre = basename($ruta);
                if (!preg_match('/^calificaciones_(.+)_(\d{8})_(\d{6})\.pdf$/u', $nombre, $m)) continue;
                if (preg_match('/^P\d+_M\d+$/', $m[1])) continue; // los nuevos ya se listaron
                if ($m[1] !== str_replace('/', '_', $codigo)) continue;
                $lista[] = [
                    'archivo' => $nombre,
                    'generado' => date('Y-m-d H:i:s', filemtime($ruta)),
                    'usuario' => '', 'docente' => '', 'fechaInicio' => '', 'fechaFin' => '', 'fechaFirma' => '',
                    'estudiantes' => null,
                    'anterior' => true,
                ];
            }
        }

        usort($lista, function ($a, $b) { return strcmp($b['generado'], $a['generado']); });
        return $lista;
    }

    /**
     * ¿El usuario de la sesión puede ver las planillas de este módulo?
     * ADM, SEC y AUX: todas. Docente: solo las de módulos que tiene asignados
     * (modulos.DocenteID). Cualquier otro tipo de usuario: ninguna.
     */
    public static function PuedeVerModulo($programaID, $moduloID)
    {
        $tipo = $_SESSION['Tipo'] ?? '';
        if (in_array($tipo, ['ADM', 'SEC', 'AUX'], true)) {
            return true;
        }
        if ($tipo !== 'DOC' || empty($_SESSION['DocenteID'])) {
            return false;
        }
        if (!class_exists('Conexion')) {
            require_once file_exists(__DIR__ . '/conexion.modelo.php') ? __DIR__ . '/conexion.modelo.php' : __DIR__ . '/../core/conexion.modelo.php';
        }
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT 1 FROM modulos WHERE Idmodulo = :modulo AND ProgramaId = :programa AND DocenteID = :docente LIMIT 1"
            );
            $stmt->execute([':modulo' => (int)$moduloID, ':programa' => (int)$programaID, ':docente' => (int)$_SESSION['DocenteID']]);
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('PlanillaGuardadaModelo::PuedeVerModulo: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ¿El usuario de la sesión puede abrir este archivo? Los anteriores (sin
     * programa ni módulo en el nombre) solo los ve el personal, porque no se
     * puede saber de qué programa son.
     */
    public static function PuedeVerArchivo($archivo)
    {
        if (preg_match('/^calificaciones_P(\d+)_M(\d+)_\d{8}_\d{6}\.pdf$/', (string)$archivo, $m)) {
            return self::PuedeVerModulo((int)$m[1], (int)$m[2]);
        }
        return in_array($_SESSION['Tipo'] ?? '', ['ADM', 'SEC', 'AUX'], true);
    }

    /**
     * Ruta completa de un archivo guardado, o '' si el nombre no es válido o no
     * existe (impide salir de la carpeta con "../").
     */
    public static function Ruta($archivo)
    {
        $archivo = (string)$archivo;
        if ($archivo !== basename($archivo) || !preg_match(self::PATRON_ARCHIVO, $archivo)) {
            return '';
        }
        $ruta = self::Carpeta() . '/' . $archivo;
        return is_file($ruta) ? $ruta : '';
    }
}
