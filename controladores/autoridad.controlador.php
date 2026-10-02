<?php
/**
 * Controlador de Autoridades (pantalla "Autoridades" del ADM).
 * Valida los datos antes de guardar el Director y el Coordinador de Posgrado.
 */

require_once __DIR__ . '/../modelos/autoridad.modelo.php';

class AutoridadControlador
{
    /**
     * @param array $entrada ['DIRECTOR' => ['titulo' => .., 'nombre' => ..], 'COORDINADOR' => [...]]
     * @return array
     */
    public static function GuardarControlador($entrada, $responsable)
    {
        if (!is_array($entrada)) {
            return ['success' => false, 'mensaje' => 'Datos no válidos'];
        }

        // Validar todo antes de guardar, para no dejar un cargo cambiado y el otro no
        $datos = [];
        foreach (AutoridadModelo::CARGOS as $cargo) {
            $titulo = trim(preg_replace('/\s+/', ' ', $entrada[$cargo]['titulo'] ?? ''));
            $nombre = trim(preg_replace('/\s+/', ' ', $entrada[$cargo]['nombre'] ?? ''));
            $etiqueta = $cargo === 'DIRECTOR' ? 'del Director' : 'del Coordinador';

            if ($nombre === '') {
                return ['success' => false, 'mensaje' => "Ingrese el nombre $etiqueta"];
            }
            if (mb_strlen($nombre, 'UTF-8') > 150 || mb_strlen($titulo, 'UTF-8') > 50) {
                return ['success' => false, 'mensaje' => "El título o nombre $etiqueta es demasiado largo"];
            }
            $datos[$cargo] = [$titulo, $nombre];
        }

        foreach ($datos as $cargo => [$titulo, $nombre]) {
            if (!AutoridadModelo::GuardarModelo($cargo, $titulo, $nombre, $responsable)) {
                return ['success' => false, 'mensaje' => 'No se pudo guardar. Intente nuevamente'];
            }
        }

        return ['success' => true, 'mensaje' => 'Autoridades actualizadas'];
    }

    /**
     * Nombre del usuario en sesión, para registrar quién hizo el cambio.
     */
    public static function UsuarioSesion()
    {
        $nombre = trim(($_SESSION['Nombres'] ?? '') . ' ' . ($_SESSION['ApellidoPaterno'] ?? ''));
        return $nombre !== '' ? $nombre : ($_SESSION['Usuario'] ?? null);
    }
}
