<?php
/**
 * Test de Reapertura de Módulos (Solo Administradores)
 */

session_start();
$_SESSION['Validar'] = true;
$_SESSION['Tipo'] = 'ADM'; // Simular administrador
$_SESSION['Usuario'] = 'admin123';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html><html><head><title>Test Reapertura Admin</title>";
echo "<style>
    body { font-family: monospace; padding: 20px; background: #f5f5f5; }
    .box { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    table th, table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    table th { background: #667eea; color: white; }
    .badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
    .badge-admin { background: #dc3545; color: white; }
    .badge-doc { background: #5867dd; color: white; }
    .badge-est { background: #1dc9b7; color: white; }
</style>";
echo "</head><body>";

echo "<h1>🔓 Test de Reapertura de Módulos (Administrador)</h1>";

// Mostrar información del usuario
echo "<div class='box'>";
echo "<h2>Usuario Actual</h2>";
echo "<p><strong>Tipo:</strong> <span class='badge badge-admin'>{$_SESSION['Tipo']}</span></p>";
echo "<p><strong>Usuario:</strong> {$_SESSION['Usuario']}</p>";

$tipoUsuario = $_SESSION["Tipo"] ?? '';
$esAdministrador = ($tipoUsuario === 'ADM');

if ($esAdministrador) {
    echo "<p class='success'>✅ Tiene permisos de administrador</p>";
    echo "<p class='info'>💡 El botón 'Reabrir' estará visible en el modal de módulos cerrados</p>";
} else {
    echo "<p class='error'>❌ NO tiene permisos de administrador</p>";
    echo "<p class='warning'>⚠️ El botón 'Reabrir' NO estará visible</p>";
}

echo "</div>";

// Test 1: Verificar módulos cerrados
echo "<div class='box'>";
echo "<h2>1. Módulos Cerrados en la Base de Datos</h2>";

require_once 'modelos/conexion.modelo.php';
require_once 'modelos/calificacion.modelo.php';

try {
    $conexion = Conexion::conectar();

    // Buscar módulos cerrados
    $modulosCerrados = [];
    try {
        $stmt = $conexion->prepare("
            SELECT
                m.Idmodulo,
                m.nombremodulo,
                m.codigomodulo,
                m.estadomodulo,
                m.ValidadoPor,
                m.FechaValidacion
            FROM modulos m
            WHERE m.estadomodulo IN ('VALIDADO', 'CERRADO')
            LIMIT 10
        ");
        $stmt->execute();
        $modulosCerrados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error al buscar módulos: " . $e->getMessage() . "</p>";
    }

    if (count($modulosCerrados) > 0) {
        echo "<p class='success'>✅ Encontrados " . count($modulosCerrados) . " módulos cerrados</p>";

        echo "<table>";
        echo "<tr>";
        echo "<th>#</th>";
        echo "<th>ID</th>";
        echo "<th>Módulo</th>";
        echo "<th>Código</th>";
        echo "<th>Estado</th>";
        echo "<th>Cerrado Por</th>";
        echo "<th>Fecha Cierre</th>";
        echo "<th>Acción</th>";
        echo "</tr>";

        foreach ($modulosCerrados as $i => $mod) {
            echo "<tr>";
            echo "<td>" . ($i + 1) . "</td>";
            echo "<td>" . $mod['Idmodulo'] . "</td>";
            echo "<td>" . htmlspecialchars($mod['nombremodulo']) . "</td>";
            echo "<td>" . htmlspecialchars($mod['codigomodulo']) . "</td>";
            echo "<td><span class='badge badge-admin'>" . $mod['estadomodulo'] . "</span></td>";
            echo "<td>" . ($mod['ValidadoPor'] ?? '-') . "</td>";
            echo "<td>" . ($mod['FechaValidacion'] ?? '-') . "</td>";
            echo "<td>";
            echo "<button onclick=\"testReabrirModulo(" . $mod['Idmodulo'] . ")\">Reabrir</button>";
            echo "</td>";
            echo "</tr>";
        }

        echo "</table>";

    } else {
        echo "<p class='warning'>⚠️ No hay módulos cerrados en la base de datos</p>";
        echo "<p class='info'>💡 Para crear un módulo cerrado:</p>";
        echo "<ol>";
        echo "<li>Ve a 'notasdocente'</li>";
        echo "<li>Selecciona un docente y módulo</li>";
        echo "<li>Registra calificaciones</li>";
        echo "<li>Click en 'Validar y Cerrar'</li>";
        echo "</ol>";
    }

} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "</div>";

// Test 2: Simular reapertura
echo "<div class='box'>";
echo "<h2>2. Test de Reapertura via AJAX</h2>";

if (count($modulosCerrados) > 0) {
    $moduloPrueba = $modulosCerrados[0];
    echo "<p><strong>Módulo de prueba:</strong></p>";
    echo "<ul>";
    echo "<li><strong>ID:</strong> " . $moduloPrueba['Idmodulo'] . "</li>";
    echo "<li><strong>Nombre:</strong> " . $moduloPrueba['nombremodulo'] . "</li>";
    echo "<li><strong>Estado actual:</strong> " . $moduloPrueba['estadomodulo'] . "</li>";
    echo "</ul>";

    echo "<p class='info'>💡 Para probar la reapertura:</p>";
    echo "<ol>";
    echo "<li>Abre la consola del navegador (F12)</li>";
    echo "<li>Haz clic en el botón 'Reabrir' en la tabla de arriba</li>";
    echo "<li>Verifica la respuesta en consola</li>";
    echo "</ol>";

} else {
    echo "<p class='warning'>⚠️ No hay módulos para probar. Crea un módulo cerrado primero.</p>";
}

echo "</div>";

// Test 3: Instrucciones de uso
echo "<div class='box'>";
echo "<h2>3. Flujo Completo de Prueba</h2>";
echo "<ol style='line-height: 2em;'>";
echo "<li><strong>Crear módulo cerrado:</strong>";
echo "<ul>";
echo "<li>Accede a 'notasdocente' como docente o admin</li>";
echo "<li>Registra calificaciones y cierra el módulo</li>";
echo "</ul>";
echo "</li>";

echo "<li><strong>Verificar en rnotasestudiante (como admin):</strong>";
echo "<ul>";
echo "<li>Accede a 'rnotasestudiante'</li>";
echo "<li>Selecciona el docente</li>";
echo "<li>Verifica que el módulo NO aparece en la tabla principal</li>";
echo "<li>Verifica que aparece botón 'Ver Módulos Cerrados'</li>";
echo "</ul>";
echo "</li>";

echo "<li><strong>Abrir modal de módulos cerrados:</strong>";
echo "<ul>";
echo "<li>Click en 'Ver Módulos Cerrados'</li>";
echo "<li>Verifica que aparece el modal con tabla</li>";
echo "<li>Verifica que hay 2 botones: 'Imprimir' y 'Reabrir'</li>";
echo "<li>El botón 'Reabrir' solo aparece si eres administrador</li>";
echo "</ul>";
echo "</li>";

echo "<li><strong>Reabrir el módulo:</strong>";
echo "<ul>";
echo "<li>Click en botón 'Reabrir' (amarillo con icono de candado abierto)</li>";
echo "<li>Confirma la acción en el diálogo</li>";
echo "<li>Espera el mensaje de éxito</li>";
echo "<li>El módulo debe aparecer nuevamente en la tabla principal</li>";
echo "</ul>";
echo "</li>";

echo "<li><strong>Verificar reapertura:</strong>";
echo "<ul>";
echo "<li>El módulo ya no aparece en módulos cerrados</li>";
echo "<li>El módulo aparece en la tabla principal con estado 'ABIERTO'</li>";
echo "<li>Los docentes pueden editar las calificaciones nuevamente</li>";
echo "</ul>";
echo "</li>";
echo "</ol>";
echo "</div>";

// Test 4: Verificar permisos
echo "<div class='box'>";
echo "<h2>4. Verificar Permisos</h2>";

echo "<p><strong>Usuarios NO administradores:</strong></p>";
echo "<ul>";
echo "<li>NO verán el botón 'Reabrir' en el modal</li>";
echo "<li>Solo verán el botón 'Imprimir'</li>";
echo "<li>Si intentan acceder directamente al AJAX, recibirán error de permisos</li>";
echo "</ul>";

echo "<p><strong>Usuarios administradores:</strong></p>";
echo "<ul>";
echo "<li>✅ Verán ambos botones: 'Imprimir' y 'Reabrir'</li>";
echo "<li>✅ Pueden reabrir cualquier módulo cerrado</li>";
echo "<li>✅ La acción queda registrada en el sistema</li>";
echo "</ul>";

echo "</div>";

// JavaScript para test de reapertura
echo "<script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>";
echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
echo "<script>
function testReabrirModulo(moduloID) {
    console.log('Test reabrir módulo:', moduloID);

    Swal.fire({
        title: '¿Reabrir este módulo?',
        text: 'Módulo ID: ' + moduloID,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, reabrir',
        cancelButtonText: 'Cancelar'
    }).then(function(result) {
        if (result.value) {
            $.ajax({
                url: 'ajax/calificacion.ajax.php',
                method: 'POST',
                data: {
                    accion: 'reabrirModulo',
                    moduloID: moduloID
                },
                dataType: 'json',
                beforeSend: function() {
                    Swal.fire({
                        title: 'Reabriendo...',
                        text: 'Por favor espere',
                        allowOutsideClick: false,
                        didOpen: function() {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    console.log('Respuesta:', response);

                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Módulo reabierto',
                            text: 'El módulo ha sido reabierto exitosamente',
                            timer: 2000
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'No se pudo reabrir el módulo'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', xhr, status, error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error de conexión: ' + error
                    });
                }
            });
        }
    });
}
</script>";

echo "<div class='box'>";
echo "<h2>5. Acciones Rápidas</h2>";
echo "<p>";
echo "<a href='index.php?ruta=rnotasestudiante' style='background:#667eea;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;display:inline-block;margin:5px;'>📝 Ir a rnotasestudiante</a>";
echo "<a href='index.php?ruta=notasdocente' style='background:#1dc9b7;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;display:inline-block;margin:5px;'>👨‍🏫 Ir a notasdocente</a>";
echo "<a href='limpiar_cache_php.php' style='background:#fd397a;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;display:inline-block;margin:5px;'>🗑️ Limpiar Caché</a>";
echo "</p>";
echo "</div>";

echo "</body></html>";
?>
