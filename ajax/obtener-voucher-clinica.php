<?php
/**
 * Foto (o PDF) del voucher de un pago de clínica, para los historiales.
 * El personal ve cualquiera; el estudiante solo los de sus inscripciones
 * (igual que obtener-voucher-imagen.php con las cuotas).
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../modelos/pagoclinica.modelo.php';

if (!isset($_SESSION['Validar']) || !$_SESSION['Validar']) {
    header('HTTP/1.1 403 Forbidden');
    exit('Acceso denegado');
}

$idPagoClinica = (int)($_GET['id'] ?? 0);
if ($idPagoClinica <= 0) {
    header('HTTP/1.1 400 Bad Request');
    exit('ID no especificado');
}

$tipoSesion = $_SESSION['Tipo'] ?? '';
$esPersonal = in_array($tipoSesion, ['ADM', 'SEC', 'AUX'], true);
$esSuyo = $tipoSesion === 'EST' && PagoClinicaModelo::PagoEsDelEstudianteModelo($idPagoClinica, $_SESSION['EstudianteID'] ?? 0);
if (!$esPersonal && !$esSuyo) {
    header('HTTP/1.1 403 Forbidden');
    exit('Acceso denegado');
}

$archivo = PagoClinicaModelo::ObtenerFotoVoucherModelo($idPagoClinica);
if ($archivo === null) {
    header('HTTP/1.1 404 Not Found');
    exit('Archivo no encontrado');
}

// Al registrarse solo se aceptan imágenes o PDF (PagoClinicaControlador::RegistrarPagoControlador)
$mime = (new finfo(FILEINFO_MIME_TYPE))->buffer($archivo);
$permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
if (!in_array($mime, $permitidos, true)) {
    header('HTTP/1.1 415 Unsupported Media Type');
    exit('Tipo de archivo no válido');
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . strlen($archivo));
header('Cache-Control: private, max-age=3600');
echo $archivo;
