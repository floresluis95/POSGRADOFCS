<?php
/**
 * Forzar actualización del archivo AJAX
 */

$archivo = __DIR__ . '/ajax/calificacion.ajax.php';

// Crear backup
$backup = $archivo . '.backup.' . date('YmdHis');
copy($archivo, $backup);

// Contenido correcto
$contenido_nuevo = <<<'EOT'
<?php
/**
 * AJAX para Calificaciones
 * Maneja las peticiones AJAX del módulo de calificaciones
 */

require_once __DIR__ . '/../controladores/calificacion.controlador.php';
require_once __DIR__ . '/../modelos/calificacion.modelo.php';

// Verificar sesión
session_start();
if (!isset($_SESSION['Validar']) || $_SESSION['Validar'] !== true) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Sesión no válida'
    ]);
    exit;
}

// Determinar la acción solicitada
if (isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    $controlador = new CalificacionControlador();

    switch ($accion) {
        case 'buscarDocente':
            $controlador->BuscarDocenteControlador();
            break;

        case 'obtenerAsignaciones':
            $controlador->ObtenerAsignacionesDocenteControlador();
            break;

        case 'obtenerProgramas':
            $controlador->ObtenerProgramasPorGradoControlador();
            break;

        case 'obtenerModulos':
            $controlador->ObtenerModulosDocenteControlador();
            break;

        case 'obtenerEstudiantes':
            $controlador->ObtenerEstudiantesPorModuloControlador();
            break;

        case 'guardarCalificaciones':
            $controlador->GuardarCalificacionesControlador();
            break;

        case 'obtenerDocente':
            $controlador->ObtenerDocenteLogueadoControlador();
            break;

        case 'buscarCalificaciones':
            $controlador->BuscarCalificacionesControlador();
            break;

        case 'obtenerProgramasConCalificaciones':
            $controlador->ObtenerProgramasConCalificacionesControlador();
            break;

        case 'obtenerEstudianteLogueado':
            $controlador->ObtenerEstudianteLogueadoControlador();
            break;

        case 'obtenerProgramasEstudiante':
            $controlador->ObtenerProgramasEstudianteControlador();
            break;

        case 'obtenerCalificacionesEstudiantePrograma':
            $controlador->ObtenerCalificacionesEstudianteProgramaControlador();
            break;

        case 'validarCerrarModulo':
            $controlador->ValidarCerrarModuloControlador();
            break;

        case 'reabrirModulo':
            $controlador->ReabrirModuloControlador();
            break;

        case 'verificarPermisoEdicion':
            $controlador->VerificarPermisoEdicionControlador();
            break;

        default:
            echo json_encode([
                'status' => 'error',
                'message' => 'Acción no válida'
            ]);
            break;
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Acción no especificada'
    ]);
}
?>
EOT;

// Escribir el archivo
$result = file_put_contents($archivo, $contenido_nuevo);

header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html><head><title>Actualización Forzada</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#f5f5f5;}";
echo ".success{color:green;font-weight:bold;}.error{color:red;font-weight:bold;}";
echo ".box{background:white;padding:20px;margin:10px 0;border-radius:8px;}</style>";
echo "</head><body>";

echo "<h1>🔄 Actualización Forzada del Archivo AJAX</h1>";

echo "<div class='box'>";
if ($result !== false) {
    echo "<p class='success'>✅ Archivo actualizado exitosamente</p>";
    echo "<p>Bytes escritos: $result</p>";
    echo "<p>Backup creado en: <code>$backup</code></p>";
} else {
    echo "<p class='error'>❌ Error al actualizar el archivo</p>";
}
echo "</div>";

echo "<div class='box'>";
echo "<h2>Verificación:</h2>";
$contenido = file_get_contents($archivo);
$lineas = explode("\n", $contenido);
echo "<p><strong>Línea 7:</strong></p>";
echo "<pre>" . htmlspecialchars($lineas[6]) . "</pre>";

if (strpos($lineas[6], '__DIR__') !== false) {
    echo "<p class='success'>✅ Archivo contiene __DIR__ correctamente</p>";
} else {
    echo "<p class='error'>❌ Archivo NO contiene __DIR__</p>";
}
echo "</div>";

echo "<div class='box'>";
echo "<h2>Próximos pasos:</h2>";
echo "<ol>";
echo "<li><strong>Reinicia Apache</strong> en el panel de XAMPP</li>";
echo "<li><a href='limpiar_cache_php.php'>Limpia el caché de PHP</a></li>";
echo "<li><a href='test_ajax_simple.php'>Prueba el AJAX de nuevo</a></li>";
echo "</ol>";
echo "</div>";

echo "</body></html>";
?>
