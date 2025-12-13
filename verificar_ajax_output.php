<?php
/**
 * Verificar que el archivo AJAX no tenga espacios en blanco antes del <?php
 */

echo "=== VERIFICACIÓN DE ARCHIVO AJAX ===\n\n";

$archivo = 'ajax/calificacion.ajax.php';

// Leer el archivo completo
$contenido = file_get_contents($archivo);

// Verificar BOM
$bom = substr($contenido, 0, 3);
if ($bom === "\xEF\xBB\xBF") {
    echo "❌ ENCONTRADO: BOM (Byte Order Mark) al inicio del archivo\n";
    echo "   Esto puede causar problemas con JSON\n\n";
} else {
    echo "✓ No hay BOM al inicio\n\n";
}

// Verificar espacios antes de <?php
if (substr($contenido, 0, 5) !== '<?php') {
    echo "❌ PROBLEMA: Hay caracteres antes de <?php\n";
    echo "   Primeros 20 bytes (hex):\n";
    echo "   " . bin2hex(substr($contenido, 0, 20)) . "\n\n";
    echo "   Primeros 20 caracteres (visible):\n";
    echo "   " . var_export(substr($contenido, 0, 20), true) . "\n\n";
} else {
    echo "✓ El archivo inicia correctamente con <?php\n\n";
}

// Verificar espacios al final
$ultimos20 = substr($contenido, -20);
if (trim($ultimos20) !== '?>') {
    echo "⚠ ADVERTENCIA: Hay contenido después de ?>\n";
    echo "   Últimos 20 caracteres:\n";
    echo "   " . var_export($ultimos20, true) . "\n\n";
} else {
    echo "✓ El archivo termina correctamente\n\n";
}

echo "=== RESUMEN ===\n";
echo "Tamaño del archivo: " . strlen($contenido) . " bytes\n";
echo "Primera línea: " . strtok($contenido, "\n") . "\n";
echo "Última línea: " . substr($contenido, strrpos($contenido, "\n") + 1) . "\n";
?>
