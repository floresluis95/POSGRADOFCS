<?php
/**
 * Validación de sesión común para los endpoints AJAX.
 * Se incluye como primera línea de cada archivo de ajax/ y corta la petición
 * (401/403 con JSON) si no hay una sesión iniciada o el tipo de usuario no está
 * permitido. La única excepción es recuperarpassword.ajax.php (recuperar la
 * contraseña desde el login), que no exige sesión.
 *
 * Uso:
 *   require_once __DIR__ . '/validar-sesion.php';
 *   ExigirSesionAjax();                // cualquier usuario con sesión
 *   ExigirSesionAjax(['ADM']);         // solo administradores
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('ExigirSesionAjax')) {
    /**
     * @param string[] $tiposPermitidos Tipos de usuario (ADM, DOC, EST...) permitidos; vacío = cualquiera con sesión
     */
    function ExigirSesionAjax(array $tiposPermitidos = [])
    {
        $conSesion = !empty($_SESSION['Validar']);
        $tipoPermitido = empty($tiposPermitidos) || in_array($_SESSION['Tipo'] ?? '', $tiposPermitidos, true);

        if ($conSesion && $tipoPermitido) {
            return;
        }

        http_response_code($conSesion ? 403 : 401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'mensaje' => $conSesion ? 'No tiene permisos para esta acción' : 'Sesión no válida. Inicie sesión nuevamente',
        ]);
        exit;
    }
}
