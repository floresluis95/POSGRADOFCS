<?php
/**
 * Script de verificación para reportenotas
 */

echo "<h1>Verificación de Reportenotas</h1>";

// 1. Verificar que el archivo existe
$archivo = __DIR__ . '/vistas/componentes/reportenotas.php';
echo "<h2>1. Verificación de archivo</h2>";
echo "Ruta: " . $archivo . "<br>";
echo "Existe: " . (file_exists($archivo) ? "SÍ ✓" : "NO ✗") . "<br>";
echo "Última modificación: " . date("Y-m-d H:i:s", filemtime($archivo)) . "<br>";

// 2. Verificar contenido del archivo
echo "<h2>2. Verificación de contenido</h2>";
$contenido = file_get_contents($archivo);

// Buscar líneas específicas
$lineas_buscar = [
    'btnVerAuditoria' => 'Botón de Auditoría',
    'ModalAuditoria' => 'Modal de Auditoría',
    'reportenotas.js' => 'Script JavaScript externo'
];

foreach ($lineas_buscar as $texto => $descripcion) {
    $encontrado = strpos($contenido, $texto) !== false;
    echo "$descripcion: " . ($encontrado ? "SÍ ✓" : "NO ✗") . "<br>";

    if ($encontrado) {
        // Mostrar contexto
        $lineas = explode("\n", $contenido);
        foreach ($lineas as $num => $linea) {
            if (stripos($linea, $texto) !== false) {
                echo "&nbsp;&nbsp;&nbsp;→ Línea " . ($num + 1) . ": " . htmlspecialchars(trim($linea)) . "<br>";
                break;
            }
        }
    }
}

// 3. Verificar archivo JavaScript
echo "<h2>3. Verificación de JavaScript</h2>";
$js_archivo = __DIR__ . '/vistas/recursos/assets/js/scripts/reportenotas.js';
echo "Ruta: " . $js_archivo . "<br>";
echo "Existe: " . (file_exists($js_archivo) ? "SÍ ✓" : "NO ✗") . "<br>";
if (file_exists($js_archivo)) {
    echo "Tamaño: " . filesize($js_archivo) . " bytes<br>";
    echo "Última modificación: " . date("Y-m-d H:i:s", filemtime($js_archivo)) . "<br>";
}

// 4. Verificar OPcache (si está habilitado)
echo "<h2>4. Verificación de Caché PHP</h2>";
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    if ($status !== false) {
        echo "OPcache habilitado: SÍ<br>";
        echo "<strong style='color: orange;'>⚠️ IMPORTANTE: Limpiando caché de PHP...</strong><br>";

        // Limpiar opcache
        if (function_exists('opcache_reset')) {
            opcache_reset();
            echo "<strong style='color: green;'>✓ Caché de PHP limpiado</strong><br>";
        }
    } else {
        echo "OPcache: No habilitado<br>";
    }
} else {
    echo "OPcache: No disponible<br>";
}

// 5. Mostrar últimas 20 líneas del archivo PHP
echo "<h2>5. Últimas líneas de reportenotas.php</h2>";
$lineas = explode("\n", $contenido);
$total = count($lineas);
$inicio = max(0, $total - 20);

echo "<pre style='background: #f0f0f0; padding: 10px; border-radius: 5px;'>";
for ($i = $inicio; $i < $total; $i++) {
    echo sprintf("%4d: %s\n", $i + 1, htmlspecialchars($lineas[$i]));
}
echo "</pre>";

// 6. URL de acceso
echo "<h2>6. Instrucciones de acceso</h2>";
echo "<p><strong>URL directa:</strong> <a href='http://localhost/POSGRADOFCS/reportenotas' target='_blank'>http://localhost/POSGRADOFCS/reportenotas</a></p>";
echo "<p><strong>Pasos:</strong></p>";
echo "<ol>";
echo "<li>Haz clic en el enlace de arriba</li>";
echo "<li>Presiona <strong>Ctrl + Shift + R</strong> para recargar sin caché</li>";
echo "<li>Presiona <strong>F12</strong> para abrir herramientas de desarrollador</li>";
echo "<li>Ve a la pestaña <strong>Console</strong> y verifica si hay errores</li>";
echo "<li>Haz clic en <strong>Ver Módulos</strong> de cualquier programa</li>";
echo "<li>Deberías ver el botón <strong>AUDITORÍA</strong> (azul) junto al botón IMPRIMIR (rojo)</li>";
echo "</ol>";

echo "<hr>";
echo "<p style='color: green; font-weight: bold;'>✓ Verificación completada</p>";
echo "<p>Si después de seguir estos pasos aún no ves los cambios, presiona F12 y comparte los errores de la consola.</p>";
?>
