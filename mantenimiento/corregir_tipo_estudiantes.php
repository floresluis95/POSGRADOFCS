<?php
/**
 * Script para corregir el tipo de usuario de estudiantes
 * Asegura que todos los usuarios con EstudianteID tengan Tipo = 'EST'
 */

require_once 'modelos/conexion.modelo.php';

echo "<h2>Corrección de Tipos de Usuario - Estudiantes</h2>";
echo "<hr>";

try {
    $pdo = Conexion::Conectar();

    // 1. Verificar usuarios estudiantes sin tipo EST
    echo "<h3>1. Verificando usuarios estudiantes...</h3>";
    $stmt = $pdo->prepare("
        SELECT
            u.ID,
            u.Usuario,
            u.Tipo,
            u.EstudianteID,
            e.Nombre,
            e.Apaterno,
            e.Amaterno
        FROM usuario u
        LEFT JOIN estudiante e ON u.EstudianteID = e.EstudianteID
        WHERE u.EstudianteID IS NOT NULL
        ORDER BY u.ID
    ");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<p>Total de usuarios estudiantes encontrados: <strong>" . count($usuarios) . "</strong></p>";

    if (count($usuarios) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>
                <th>ID</th>
                <th>Usuario</th>
                <th>Tipo Actual</th>
                <th>EstudianteID</th>
                <th>Nombre Completo</th>
                <th>Estado</th>
              </tr>";

        $corregidos = 0;
        $correctos = 0;

        foreach ($usuarios as $usuario) {
            $nombreCompleto = trim($usuario['Nombre'] . ' ' . $usuario['Apaterno'] . ' ' . $usuario['Amaterno']);
            $tipoActual = $usuario['Tipo'];
            $estiloFila = "";
            $estado = "";

            if ($tipoActual != 'EST') {
                // Corregir tipo
                $stmtUpdate = $pdo->prepare("
                    UPDATE usuario
                    SET Tipo = 'EST'
                    WHERE ID = :id
                ");
                $stmtUpdate->bindParam(':id', $usuario['ID'], PDO::PARAM_INT);

                if ($stmtUpdate->execute()) {
                    $estiloFila = "background-color: #d4edda;";
                    $estado = "✅ CORREGIDO: {$tipoActual} → EST";
                    $corregidos++;
                } else {
                    $estiloFila = "background-color: #f8d7da;";
                    $estado = "❌ ERROR al corregir";
                }
            } else {
                $estiloFila = "background-color: #d1ecf1;";
                $estado = "✓ Ya tiene tipo EST";
                $correctos++;
            }

            echo "<tr style='{$estiloFila}'>
                    <td>{$usuario['ID']}</td>
                    <td>{$usuario['Usuario']}</td>
                    <td><strong>{$tipoActual}</strong></td>
                    <td>{$usuario['EstudianteID']}</td>
                    <td>{$nombreCompleto}</td>
                    <td>{$estado}</td>
                  </tr>";
        }

        echo "</table>";
        echo "<br>";
        echo "<div style='background-color: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3;'>";
        echo "<h4>Resumen:</h4>";
        echo "<ul>";
        echo "<li><strong>Usuarios corregidos:</strong> {$corregidos}</li>";
        echo "<li><strong>Usuarios ya correctos:</strong> {$correctos}</li>";
        echo "<li><strong>Total procesado:</strong> " . count($usuarios) . "</li>";
        echo "</ul>";
        echo "</div>";
    }

    echo "<br><hr>";

    // 2. Verificar usuarios docentes
    echo "<h3>2. Verificando usuarios docentes...</h3>";
    $stmt2 = $pdo->prepare("
        SELECT
            u.ID,
            u.Usuario,
            u.Tipo,
            u.DocenteID,
            d.Nombre,
            d.Apaterno,
            d.Amaterno
        FROM usuario u
        LEFT JOIN docente d ON u.DocenteID = d.DocenteID
        WHERE u.DocenteID IS NOT NULL
        ORDER BY u.ID
    ");
    $stmt2->execute();
    $docentes = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    echo "<p>Total de usuarios docentes encontrados: <strong>" . count($docentes) . "</strong></p>";

    if (count($docentes) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>
                <th>ID</th>
                <th>Usuario</th>
                <th>Tipo Actual</th>
                <th>DocenteID</th>
                <th>Nombre Completo</th>
                <th>Estado</th>
              </tr>";

        $corregidosDoc = 0;
        $correctosDoc = 0;

        foreach ($docentes as $docente) {
            $nombreCompleto = trim($docente['Nombre'] . ' ' . $docente['Apaterno'] . ' ' . $docente['Amaterno']);
            $tipoActual = $docente['Tipo'];
            $estiloFila = "";
            $estado = "";

            if ($tipoActual != 'DOC') {
                // Corregir tipo
                $stmtUpdate2 = $pdo->prepare("
                    UPDATE usuario
                    SET Tipo = 'DOC'
                    WHERE ID = :id
                ");
                $stmtUpdate2->bindParam(':id', $docente['ID'], PDO::PARAM_INT);

                if ($stmtUpdate2->execute()) {
                    $estiloFila = "background-color: #d4edda;";
                    $estado = "✅ CORREGIDO: {$tipoActual} → DOC";
                    $corregidosDoc++;
                } else {
                    $estiloFila = "background-color: #f8d7da;";
                    $estado = "❌ ERROR al corregir";
                }
            } else {
                $estiloFila = "background-color: #d1ecf1;";
                $estado = "✓ Ya tiene tipo DOC";
                $correctosDoc++;
            }

            echo "<tr style='{$estiloFila}'>
                    <td>{$docente['ID']}</td>
                    <td>{$docente['Usuario']}</td>
                    <td><strong>{$tipoActual}</strong></td>
                    <td>{$docente['DocenteID']}</td>
                    <td>{$nombreCompleto}</td>
                    <td>{$estado}</td>
                  </tr>";
        }

        echo "</table>";
        echo "<br>";
        echo "<div style='background-color: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3;'>";
        echo "<h4>Resumen Docentes:</h4>";
        echo "<ul>";
        echo "<li><strong>Usuarios corregidos:</strong> {$corregidosDoc}</li>";
        echo "<li><strong>Usuarios ya correctos:</strong> {$correctosDoc}</li>";
        echo "<li><strong>Total procesado:</strong> " . count($docentes) . "</li>";
        echo "</ul>";
        echo "</div>";
    }

    echo "<br><hr>";
    echo "<div style='background-color: #d4edda; padding: 20px; border: 2px solid #28a745; border-radius: 5px;'>";
    echo "<h3 style='color: #155724; margin-top: 0;'>✅ Proceso Completado Exitosamente</h3>";
    echo "<p><strong>Los tipos de usuario han sido verificados y corregidos.</strong></p>";
    echo "<p>Ahora los estudiantes deben poder ver el sidebar correcto al iniciar sesión.</p>";
    echo "</div>";

    echo "<br>";
    echo "<p><a href='index.php' style='padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>← Volver al inicio</a></p>";

} catch (PDOException $e) {
    echo "<div style='background-color: #f8d7da; padding: 15px; border-left: 4px solid #dc3545;'>";
    echo "<h3 style='color: #721c24;'>❌ Error en la base de datos</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background-color: #f5f5f5;
}

h2, h3 {
    color: #333;
}

table {
    background-color: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

th {
    font-weight: 600;
    text-align: left;
}

td {
    padding: 8px;
}
</style>
