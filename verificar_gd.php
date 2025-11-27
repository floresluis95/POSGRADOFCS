<?php
echo "Verificando extensión GD:\n\n";

if (extension_loaded('gd')) {
    echo "✓ GD está HABILITADA\n";
    echo "Versión: " . GD_VERSION . "\n\n";

    echo "Formatos soportados:\n";
    echo "  - JPEG: " . (function_exists('imagecreatefromjpeg') ? 'SI' : 'NO') . "\n";
    echo "  - PNG: " . (function_exists('imagecreatefrompng') ? 'SI' : 'NO') . "\n";
    echo "  - GIF: " . (function_exists('imagecreatefromgif') ? 'SI' : 'NO') . "\n";
} else {
    echo "✗ GD NO está habilitada\n";
    echo "\nPara habilitar GD, edita php.ini y descomenta:\n";
    echo "extension=gd\n";
}

echo "\nVerificando Imagick:\n";
if (extension_loaded('imagick')) {
    echo "✓ Imagick está HABILITADA\n";
} else {
    echo "✗ Imagick NO está habilitada\n";
}
?>
