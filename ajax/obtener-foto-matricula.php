<?php
/**
 * Obtener y mostrar la imagen del voucher de MATRÍCULA de una inscripción
 * (estudianteprograma.foto). Antes no existía ningún endpoint para verla.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../modelos/historialpagos.modelo.php';

if (!isset($_SESSION['Validar']) || !$_SESSION['Validar']) {
    header('HTTP/1.1 403 Forbidden');
    exit('Acceso denegado');
}

if (!isset($_GET['idInscripcion'])) {
    header('HTTP/1.1 400 Bad Request');
    exit('ID no especificado');
}

$idInscripcion = (int)$_GET['idInscripcion'];
$esThumbnail = isset($_GET['thumb']) && $_GET['thumb'] == '1';

$imagen = HistorialPagosModelos::ObtenerFotoMatriculaModelo($idInscripcion);

if ($imagen === null) {
    header('HTTP/1.1 404 Not Found');
    exit('Imagen no encontrada');
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->buffer($imagen);

$tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($mimeType, $tiposPermitidos)) {
    header('HTTP/1.1 415 Unsupported Media Type');
    exit('Tipo de archivo no válido');
}

if ($esThumbnail && extension_loaded('gd')) {
    $imagenOriginal = imagecreatefromstring($imagen);

    if ($imagenOriginal !== false) {
        $anchoOriginal = imagesx($imagenOriginal);
        $altoOriginal = imagesy($imagenOriginal);

        $anchoMax = 300;
        $ratio = $anchoOriginal / $anchoMax;

        if ($ratio > 1) {
            $nuevoAncho = $anchoMax;
            $nuevoAlto = (int)($altoOriginal / $ratio);
        } else {
            $nuevoAncho = $anchoOriginal;
            $nuevoAlto = $altoOriginal;
        }

        $imagenRedimensionada = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

        if ($mimeType === 'image/png') {
            imagealphablending($imagenRedimensionada, false);
            imagesavealpha($imagenRedimensionada, true);
            $transparent = imagecolorallocatealpha($imagenRedimensionada, 255, 255, 255, 127);
            imagefilledrectangle($imagenRedimensionada, 0, 0, $nuevoAncho, $nuevoAlto, $transparent);
        }

        imagecopyresampled(
            $imagenRedimensionada,
            $imagenOriginal,
            0, 0, 0, 0,
            $nuevoAncho, $nuevoAlto,
            $anchoOriginal, $altoOriginal
        );

        ob_start();

        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                imagejpeg($imagenRedimensionada, null, 75);
                break;
            case 'image/png':
                imagepng($imagenRedimensionada, null, 6);
                break;
            case 'image/gif':
                imagegif($imagenRedimensionada);
                break;
            case 'image/webp':
                imagewebp($imagenRedimensionada, null, 75);
                break;
        }

        $imagen = ob_get_clean();

        imagedestroy($imagenOriginal);
        imagedestroy($imagenRedimensionada);
    }
}

header('Content-Type: ' . $mimeType);
header('Content-Length: ' . strlen($imagen));
header('Cache-Control: max-age=3600');

echo $imagen;
exit;
