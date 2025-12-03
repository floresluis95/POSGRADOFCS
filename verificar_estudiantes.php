<?php
/**
 * Script para verificar la tabla estudiante y su relación con usuario
 */

require_once 'modelos/conexion.modelo.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Verificación de Tabla Estudiante</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #667eea; color: white; }
        .success { background-color: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 15px 0; }
        .error { background-color: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 15px 0; }
        .warning { background-color: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 15px 0; }
        .info { background-color: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔍 Verificación de Tabla Estudiante</h2>

        <?php
        try {
            $pdo = Conexion::Conectar();

            // 1. Ver estructura de la tabla estudiante
            echo "<h3>1️⃣ Estructura de la tabla ESTUDIANTE</h3>";
            $stmt = $pdo->query("DESCRIBE estudiante");
            $estructura = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo "<table>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            foreach ($estructura as $campo) {
                echo "<tr>";
                echo "<td><strong>{$campo['Field']}</strong></td>";
                echo "<td>{$campo['Type']}</td>";
                echo "<td>{$campo['Null']}</td>";
                echo "<td>{$campo['Key']}</td>";
                echo "<td>{$campo['Default']}</td>";
                echo "<td>{$campo['Extra']}</td>";
                echo "</tr>";
            }
            echo "</table>";

            // 2. Ver todos los estudiantes
            echo "<h3>2️⃣ Contenido de la tabla ESTUDIANTE</h3>";
            $stmt2 = $pdo->query("SELECT * FROM estudiante ORDER BY EstudianteID");
            $estudiantes = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            if (count($estudiantes) > 0) {
                echo "<p><strong>Total de estudiantes:</strong> " . count($estudiantes) . "</p>";
                echo "<table>";
                echo "<tr>";
                echo "<th>EstudianteID</th>";
                echo "<th>CI</th>";
                echo "<th>Nombre Completo</th>";
                echo "<th>Email</th>";
                echo "<th>Estado</th>";
                echo "</tr>";

                foreach ($estudiantes as $est) {
                    $nombreCompleto = trim($est['Nombre'] . ' ' . $est['Apaterno'] . ' ' . $est['Amaterno']);
                    $ci = $est['Ci'];
                    if (!empty($est['Complemento'])) {
                        $ci .= '-' . $est['Complemento'];
                    }
                    $ci .= ' ' . $est['Exp'];

                    echo "<tr>";
                    echo "<td><strong>{$est['EstudianteID']}</strong></td>";
                    echo "<td>{$ci}</td>";
                    echo "<td>{$nombreCompleto}</td>";
                    echo "<td>" . (isset($est['Email']) ? $est['Email'] : 'N/A') . "</td>";
                    echo "<td>" . (isset($est['Estado']) ? $est['Estado'] : 'N/A') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<div class='warning'>";
                echo "<p>⚠️ <strong>NO HAY ESTUDIANTES</strong> en la tabla 'estudiante'</p>";
                echo "</div>";
            }

            // 3. Ver relación usuario-estudiante
            echo "<h3>3️⃣ Relación USUARIO ↔ ESTUDIANTE</h3>";
            $stmt3 = $pdo->query("
                SELECT
                    u.ID as UsuarioID,
                    u.Usuario,
                    u.EstudianteID,
                    u.Tipo,
                    e.EstudianteID as EstudianteIDReal,
                    e.Nombre,
                    e.Apaterno,
                    e.Amaterno,
                    e.Ci
                FROM usuario u
                LEFT JOIN estudiante e ON u.EstudianteID = e.EstudianteID
                WHERE u.Tipo = 'EST'
                ORDER BY u.ID
            ");
            $relaciones = $stmt3->fetchAll(PDO::FETCH_ASSOC);

            echo "<table>";
            echo "<tr>";
            echo "<th>Usuario ID</th>";
            echo "<th>Usuario</th>";
            echo "<th>EstudianteID<br>(en usuario)</th>";
            echo "<th>¿Existe en estudiante?</th>";
            echo "<th>Nombre Estudiante</th>";
            echo "</tr>";

            foreach ($relaciones as $rel) {
                $existe = !is_null($rel['EstudianteIDReal']);
                $rowClass = $existe ? '' : 'style="background-color: #f8d7da;"';

                echo "<tr $rowClass>";
                echo "<td>{$rel['UsuarioID']}</td>";
                echo "<td><strong>{$rel['Usuario']}</strong></td>";
                echo "<td><strong>{$rel['EstudianteID']}</strong></td>";
                echo "<td>" . ($existe ? '✅ SÍ' : '❌ NO') . "</td>";

                if ($existe) {
                    $nombre = trim($rel['Nombre'] . ' ' . $rel['Apaterno'] . ' ' . $rel['Amaterno']);
                    echo "<td>{$nombre}</td>";
                } else {
                    echo "<td><em style='color: red;'>ESTUDIANTE NO EXISTE</em></td>";
                }
                echo "</tr>";
            }
            echo "</table>";

            // 4. Verificar IDs faltantes
            echo "<h3>4️⃣ Diagnóstico de Problemas</h3>";

            $problemasEncontrados = false;
            foreach ($relaciones as $rel) {
                if (is_null($rel['EstudianteIDReal'])) {
                    $problemasEncontrados = true;
                    echo "<div class='error'>";
                    echo "<p>❌ <strong>PROBLEMA ENCONTRADO:</strong></p>";
                    echo "<ul>";
                    echo "<li>Usuario ID: {$rel['UsuarioID']}</li>";
                    echo "<li>Usuario: {$rel['Usuario']}</li>";
                    echo "<li>Tiene EstudianteID = {$rel['EstudianteID']} en la tabla 'usuario'</li>";
                    echo "<li>Pero NO existe un estudiante con ese ID en la tabla 'estudiante'</li>";
                    echo "</ul>";
                    echo "<p><strong>Solución:</strong> Necesita crear el estudiante con EstudianteID = {$rel['EstudianteID']} o cambiar el EstudianteID del usuario.</p>";
                    echo "</div>";
                }
            }

            if (!$problemasEncontrados) {
                echo "<div class='success'>";
                echo "<p>✅ Todas las relaciones están correctas. Todos los usuarios estudiantes tienen un registro correspondiente en la tabla 'estudiante'.</p>";
                echo "</div>";
            }

        } catch (PDOException $e) {
            echo "<div class='error'>";
            echo "<h3>❌ Error en la base de datos</h3>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "</div>";
        }
        ?>

        <div style="margin-top: 30px;">
            <a href="index.php" style="padding: 10px 20px; background-color: #667eea; color: white; text-decoration: none; border-radius: 5px;">← Volver al inicio</a>
        </div>
    </div>
</body>
</html>
