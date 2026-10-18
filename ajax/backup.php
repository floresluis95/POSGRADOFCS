<?php
require_once __DIR__ . '/validar-sesion.php';
ExigirSesionAjax(['ADM']); // Solo el administrador puede descargar la base completa
/**
 * Respaldo de la base de datos: genera un .sql con la estructura y los datos de
 * todas las tablas (y las vistas) y lo envía al navegador como descarga, para
 * que quede guardado en la PC de quien lo pidió (carpeta Descargas).
 *
 * Se arma con PHP/PDO y no con mysqldump para que funcione igual en el
 * servidor de producción, donde puede no estar instalado o no estar permitido
 * ejecutar programas. Para restaurar: phpMyAdmin > Importar, o
 *   mysql -u root proyecto < respaldo.sql
 */

require_once file_exists(__DIR__ . '/../modelos/conexion.modelo.php') ? __DIR__ . '/../modelos/conexion.modelo.php' : __DIR__ . '/../core/conexion.modelo.php';

set_time_limit(0);
ini_set('memory_limit', '512M');

$pdo = Conexion::Conectar();
$pdo->exec("SET NAMES utf8mb4");
// Misma zona que el SET time_zone del archivo: si no, las columnas TIMESTAMP se corren 4 horas al restaurar
$pdo->exec("SET time_zone = '+00:00'");
$baseDatos = $pdo->query("SELECT DATABASE()")->fetchColumn();

$archivo = 'respaldo_' . $baseDatos . '_' . date('Y-m-d_H-i') . '.sql';
header('Content-Type: application/sql; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $archivo . '"');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

function ValorSql(PDO $pdo, $valor)
{
    return $valor === null ? 'NULL' : $pdo->quote($valor);
}

echo "-- Respaldo de la base `$baseDatos`\n";
echo "-- Generado: " . date('d/m/Y H:i:s') . " por " . ($_SESSION['Usuario'] ?? $_SESSION['Nombre'] ?? 'ADM') . "\n";
echo "-- Restaurar: phpMyAdmin > Importar, o mysql -u root $baseDatos < $archivo\n\n";
echo "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\nSET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\nSET time_zone = '+00:00';\n\n";

$tablas = [];
$vistas = [];
foreach ($pdo->query("SHOW FULL TABLES")->fetchAll(PDO::FETCH_NUM) as $fila) {
    if ($fila[1] === 'VIEW') {
        $vistas[] = $fila[0];
    } else {
        $tablas[] = $fila[0];
    }
}

foreach ($tablas as $tabla) {
    $crear = $pdo->query("SHOW CREATE TABLE `$tabla`")->fetch(PDO::FETCH_NUM)[1];
    echo "-- ------------------------------------------------------------\n";
    echo "-- Tabla `$tabla`\n";
    echo "-- ------------------------------------------------------------\n";
    echo "DROP TABLE IF EXISTS `$tabla`;\n$crear;\n\n";

    $stmt = $pdo->query("SELECT * FROM `$tabla`");
    $columnas = null;
    $lote = [];
    while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($columnas === null) {
            $columnas = '`' . implode('`, `', array_keys($fila)) . '`';
        }
        $lote[] = '(' . implode(', ', array_map(function ($v) use ($pdo) { return ValorSql($pdo, $v); }, $fila)) . ')';
        // INSERT de 200 filas: más rápido de restaurar y no excede max_allowed_packet
        if (count($lote) === 200) {
            echo "INSERT INTO `$tabla` ($columnas) VALUES\n" . implode(",\n", $lote) . ";\n";
            $lote = [];
            flush();
        }
    }
    if ($lote) {
        echo "INSERT INTO `$tabla` ($columnas) VALUES\n" . implode(",\n", $lote) . ";\n";
    }
    echo "\n";
}

// Las vistas van al final porque dependen de las tablas
foreach ($vistas as $vista) {
    $crear = $pdo->query("SHOW CREATE VIEW `$vista`")->fetch(PDO::FETCH_NUM)[1];
    // Sin DEFINER: así se restaura aunque el usuario de MySQL sea otro
    $crear = preg_replace('/DEFINER=`[^`]*`@`[^`]*`\s*/', '', $crear);
    echo "-- Vista `$vista`\n";
    echo "DROP VIEW IF EXISTS `$vista`;\n$crear;\n\n";
}

echo "SET FOREIGN_KEY_CHECKS = 1;\n";
echo "-- Fin del respaldo\n";
