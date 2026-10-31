<?php
/**
 * Logo de la institución subido desde Configuración (LOGO_IZQUIERDO, LOGO_DERECHO o
 * LOGO_SISTEMA). Público: el logo del sistema se ve también en el ingreso y en el
 * inicio. Solo entrega logos, nunca la firma ni el sello.
 */
require_once __DIR__ . '/../modelos/institucion.modelo.php';

$clave = $_GET['clave'] ?? '';
if (!isset(InstitucionModelo::LOGOS[$clave])) {
    http_response_code(404);
    exit;
}

$png = InstitucionModelo::LogoSubido($clave);
if ($png === null) {
    // Sin logo subido: el de por defecto
    $ruta = InstitucionModelo::LogoPorDefecto($clave);
    if (!file_exists($ruta)) {
        http_response_code(404);
        exit;
    }
    $png = file_get_contents($ruta);
}

// La URL lleva la fecha del cambio (?v=...): se puede guardar en caché
header('Content-Type: image/png');
header('Cache-Control: public, max-age=31536000');
header('Content-Length: ' . strlen($png));
echo $png;
