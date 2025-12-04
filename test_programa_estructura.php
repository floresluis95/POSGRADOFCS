<?php
require_once 'modelos/conexion.modelo.php';

echo "=== VERIFICANDO TABLA PROGRAMA ===\n\n";

// Ver todos los programas
$stmt = Conexion::Conectar()->prepare("SELECT * FROM programa LIMIT 5");
$stmt->execute();
$programas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total de programas en la tabla: " . count($programas) . "\n\n";

if (!empty($programas)) {
    echo "Campos de la tabla:\n";
    print_r(array_keys($programas[0]));
    echo "\n\nPrimer programa:\n";
    print_r($programas[0]);

    // Ver qué valores tiene el campo Estado
    $stmt2 = Conexion::Conectar()->prepare("SELECT DISTINCT Estado FROM programa");
    $stmt2->execute();
    $estados = $stmt2->fetchAll(PDO::FETCH_COLUMN);
    echo "\n\nValores distintos en campo Estado:\n";
    print_r($estados);

    // Contar con Estado = 1
    $stmt3 = Conexion::Conectar()->prepare("SELECT COUNT(*) as total FROM programa WHERE Estado = 1");
    $stmt3->execute();
    $total1 = $stmt3->fetch(PDO::FETCH_ASSOC)['total'];
    echo "\n\nProgramas con Estado = 1: " . $total1 . "\n";

    // Contar con Estado = 'ACTIVO'
    $stmt4 = Conexion::Conectar()->prepare("SELECT COUNT(*) as total FROM programa WHERE Estado = 'ACTIVO'");
    $stmt4->execute();
    $total2 = $stmt4->fetch(PDO::FETCH_ASSOC)['total'];
    echo "Programas con Estado = 'ACTIVO': " . $total2 . "\n";
}
?>
