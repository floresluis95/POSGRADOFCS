<?php
require_once 'modelos/conexion.modelo.php';

echo "=== VERIFICANDO SEDES ===\n\n";

// Ver sedes distintas
$stmt = Conexion::Conectar()->prepare("SELECT DISTINCT Sede FROM programa WHERE Estado = 'ACTIVO'");
$stmt->execute();
$sedes = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "Sedes encontradas:\n";
print_r($sedes);

// Contar programas por sede
echo "\n\nProgramas por Sede:\n";
$stmt2 = Conexion::Conectar()->prepare("
    SELECT Sede, COUNT(*) as Total
    FROM programa
    WHERE Estado = 'ACTIVO'
    GROUP BY Sede
    ORDER BY Total DESC
");
$stmt2->execute();
$conteo = $stmt2->fetchAll(PDO::FETCH_ASSOC);
print_r($conteo);
?>
