<?php
echo "=== Verificación de GD ===\n\n";

if (extension_loaded('gd')) {
    echo "✓ ¡GD ESTÁ HABILITADA!\n\n";
    echo "Información de GD:\n";
    echo "  - Versión: " . GD_VERSION . "\n";

    $info = gd_info();
    echo "\nFormatos soportados:\n";
    foreach ($info as $key => $value) {
        if (is_bool($value)) {
            $value = $value ? 'SI' : 'NO';
        }
        echo "  - $key: $value\n";
    }
} else {
    echo "✗ GD NO está habilitada\n";
    echo "Por favor, reinicia Apache manualmente desde el Panel de Control de XAMPP\n";
}
?>
