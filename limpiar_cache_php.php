<?php
/**
 * Limpiar caché de OPcache de PHP
 */

echo "<h1>Limpieza de Caché PHP</h1>";
echo "<pre>";

// Verificar si OPcache está habilitado
if (function_exists('opcache_reset')) {
    echo "OPcache detectado...\n";

    if (opcache_reset()) {
        echo "✅ OPcache limpiado exitosamente\n";
    } else {
        echo "⚠️ No se pudo limpiar OPcache\n";
    }

    // Mostrar estado
    $status = opcache_get_status();
    echo "\nEstado de OPcache:\n";
    echo "- Habilitado: " . ($status['opcache_enabled'] ? 'Sí' : 'No') . "\n";
    echo "- Archivos en caché: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";

} else {
    echo "ℹ️ OPcache no está habilitado\n";
}

echo "\n✅ Caché PHP limpiado\n";
echo "\nAhora prueba nuevamente:\n";
echo "- <a href='test_ajax_simple.php'>Test AJAX Simple</a>\n";
echo "- <a href='test_ajax_desde_navegador.html'>Test AJAX desde Navegador</a>\n";
echo "- <a href='index.php?ruta=notasdocente'>Notasdocente</a>\n";

echo "</pre>";
?>
