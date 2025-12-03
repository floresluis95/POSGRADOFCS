<?php
/**
 * Obtener y mostrar imagen de voucher
 */

// Iniciar sesión si aún no se ha iniciado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../modelos/voucher.modelo.php';

// Validar que el usuario esté autenticado
if (!isset($_SESSION['Validar']) || !$_SESSION['Validar']) {
    header('HTTP/1.1 403 Forbidden');
    exit('Acceso denegado');
}

// Validar que se haya enviado el ID
if (!isset($_GET['id'])) {
    header('HTTP/1.1 400 Bad Request');
    exit('ID no especificado');
}

$idPago = (int)$_GET['id'];
$esThumbnail = isset($_GET['thumb']) && $_GET['thumb'] == '1';

// Obtener la imagen desde la base de datos
$imagen = VoucherModelo::ObtenerImagenVoucherModelo($idPago);

if ($imagen === null) {
    header('HTTP/1.1 404 Not Found');
    exit('Imagen no encontrada');
}

// Detectar el tipo de imagen
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->buffer($imagen);

// Validar que sea una imagen
$tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($mimeType, $tiposPermitidos)) {
    header('HTTP/1.1 415 Unsupported Media Type');
    exit('Tipo de archivo no válido');
}

// Si es thumbnail, redimensionar la imagen
if ($esThumbnail && extension_loaded('gd')) {
    $imagenOriginal = imagecreatefromstring($imagen);

    if ($imagenOriginal !== false) {
        // Obtener dimensiones originales
        $anchoOriginal = imagesx($imagenOriginal);
        $altoOriginal = imagesy($imagenOriginal);

        // Calcular nuevas dimensiones (máximo 300px de ancho)
        $anchoMax = 300;
        $ratio = $anchoOriginal / $anchoMax;

        if ($ratio > 1) {
            $nuevoAncho = $anchoMax;
            $nuevoAlto = (int)($altoOriginal / $ratio);
        } else {
            $nuevoAncho = $anchoOriginal;
            $nuevoAlto = $altoOriginal;
        }

        // Crear imagen redimensionada
        $imagenRedimensionada = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

        // Preservar transparencia para PNG
        if ($mimeType === 'image/png') {
            imagealphablending($imagenRedimensionada, false);
            imagesavealpha($imagenRedimensionada, true);
            $transparent = imagecolorallocatealpha($imagenRedimensionada, 255, 255, 255, 127);
            imagefilledrectangle($imagenRedimensionada, 0, 0, $nuevoAncho, $nuevoAlto, $transparent);
        }

        // Redimensionar con mejor calidad
        imagecopyresampled(
            $imagenRedimensionada,
            $imagenOriginal,
            0, 0, 0, 0,
            $nuevoAncho, $nuevoAlto,
            $anchoOriginal, $altoOriginal
        );

        // Generar la imagen redimensionada en buffer
        ob_start();

        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                imagejpeg($imagenRedimensionada, null, 75); // Calidad 75%
                break;
            case 'image/png':
                imagepng($imagenRedimensionada, null, 6); // Compresión nivel 6
                break;
            case 'image/gif':
                imagegif($imagenRedimensionada);
                break;
            case 'image/webp':
                imagewebp($imagenRedimensionada, null, 75);
                break;
        }

        $imagen = ob_get_clean();

        // Liberar memoria
        imagedestroy($imagenOriginal);
        imagedestroy($imagenRedimensionada);
    }
}

// Establecer headers para mostrar la imagen
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . strlen($imagen));
header('Cache-Control: max-age=3600'); // Cache por 1 hora

// Mostrar la imagen
echo $imagen;
exit;
?>
