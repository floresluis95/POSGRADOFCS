<?php
/**
 * Script de prueba para verificar el registro de módulos
 */

require_once __DIR__ . '/modelos/modulo.modelo.php';
require_once __DIR__ . '/modelos/conexion.modelo.php';

echo "<h2>TEST: Registro de Módulos por Programa</h2>";
echo "<hr>";

// Verificar conexión a BD
try {
    $pdo = Conexion::Conectar();
    echo "✓ Conexión a BD: OK<br>";
} catch (Exception $e) {
    echo "✗ Error de conexión: " . $e->getMessage() . "<br>";
    die();
}

// Datos de prueba
$datosTest = [
    'programaID' => 1, // Cambiar por un ProgramaID válido
    'modulos' => [
        [
            'nombremodulo' => 'Módulo de Prueba 1',
            'codigomodulo' => 'MODULO I',
            'docenteID' => null
        ],
        [
            'nombremodulo' => 'Módulo de Prueba 2',
            'codigomodulo' => 'MODULO II',
            'docenteID' => null
        ]
    ]
];

echo "<h3>Datos de prueba:</h3>";
echo "<pre>";
print_r($datosTest);
echo "</pre>";

// Verificar si ya existen módulos
$stmtCheck = $pdo->prepare("SELECT COUNT(*) as total FROM modulos WHERE ProgramaId = :programaID");
$stmtCheck->bindParam(":programaID", $datosTest['programaID'], PDO::PARAM_INT);
$stmtCheck->execute();
$result = $stmtCheck->fetch(PDO::FETCH_ASSOC);

echo "<h3>Módulos existentes para ProgramaID {$datosTest['programaID']}: {$result['total']}</h3>";

if ($result['total'] > 0) {
    echo "<p style='color:orange'>⚠ Ya existen módulos. Se cancelará la prueba de inserción.</p>";
    echo "<p>Para probar, primero elimina los módulos existentes o usa otro ProgramaID.</p>";
} else {
    echo "<p style='color:green'>✓ No hay módulos previos. Procediendo con la prueba...</p>";

    // Intentar registrar
    echo "<h3>Intentando registrar módulos...</h3>";
    $resultado = ModuloModelo::RegistrarModulosPorProgramaModelo($datosTest);

    echo "<h3>Resultado: <strong style='color:blue'>{$resultado}</strong></h3>";

    if ($resultado == 'exitoso') {
        echo "<p style='color:green'>✓ ¡Módulos registrados exitosamente!</p>";

        // Verificar registros
        $stmtVerify = $pdo->prepare("SELECT * FROM modulos WHERE ProgramaId = :programaID ORDER BY Idmodulo DESC LIMIT 5");
        $stmtVerify->bindParam(":programaID", $datosTest['programaID'], PDO::PARAM_INT);
        $stmtVerify->execute();
        $modulosRegistrados = $stmtVerify->fetchAll(PDO::FETCH_ASSOC);

        echo "<h3>Módulos registrados en la BD:</h3>";
        echo "<pre>";
        print_r($modulosRegistrados);
        echo "</pre>";
    } else {
        echo "<p style='color:red'>✗ Error al registrar módulos</p>";
    }
}

echo "<hr>";
echo "<h3>Verificación de tabla 'modulos':</h3>";

// Mostrar estructura de la tabla
try {
    $stmtStruct = $pdo->query("DESCRIBE modulos");
    $estructura = $stmtStruct->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Default</th><th>Extra</th></tr>";
    foreach ($estructura as $campo) {
        echo "<tr>";
        echo "<td>{$campo['Field']}</td>";
        echo "<td>{$campo['Type']}</td>";
        echo "<td>{$campo['Null']}</td>";
        echo "<td>{$campo['Default']}</td>";
        echo "<td>{$campo['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color:red'>Error al obtener estructura: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>Script completado.</em></p>";
?>
