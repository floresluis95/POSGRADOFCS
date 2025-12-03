<?php
/**
 * Script de diagnóstico para problema de estudiante no encontrado
 */

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'modelos/conexion.modelo.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico: Estudiante no encontrado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #667eea;
            color: white;
        }
        .success { background-color: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 15px 0; }
        .error { background-color: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 15px 0; }
        .warning { background-color: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 15px 0; }
        .info { background-color: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3; margin: 15px 0; }
        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        .btn {
            padding: 10px 20px;
            background-color: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔍 Diagnóstico: Problema "Estudiante no encontrado"</h2>

        <?php if (!isset($_SESSION['Validar']) || !$_SESSION['Validar']): ?>
            <div class="warning">
                <h3>⚠️ No hay sesión activa</h3>
                <p>Por favor, inicie sesión primero para diagnosticar el problema.</p>
                <a href="ingreso" class="btn">Ir a Inicio de Sesión</a>
            </div>
        <?php else: ?>

            <div class="info">
                <h3>📊 Información de la Sesión Actual</h3>
                <p><strong>Usuario (de sesión):</strong> <code><?php echo isset($_SESSION['Usuario']) ? $_SESSION['Usuario'] : 'NO DEFINIDO'; ?></code></p>
                <p><strong>Tipo de Usuario:</strong> <code><?php echo isset($_SESSION['Tipo']) ? $_SESSION['Tipo'] : 'NO DEFINIDO'; ?></code></p>
                <p><strong>EstudianteID (de sesión):</strong> <code><?php echo isset($_SESSION['EstudianteID']) ? $_SESSION['EstudianteID'] : 'NO DEFINIDO'; ?></code></p>
            </div>

            <?php
            $pdo = Conexion::Conectar();

            // 1. Buscar en la tabla usuario
            echo "<h3>1️⃣ Verificando tabla USUARIO</h3>";

            if (isset($_SESSION['Usuario'])) {
                $usuario = $_SESSION['Usuario'];

                $stmt = $pdo->prepare("SELECT * FROM usuario WHERE Usuario = :usuario");
                $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
                $stmt->execute();
                $usuarioData = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($usuarioData) {
                    echo "<div class='success'>";
                    echo "<p>✅ Usuario encontrado en la tabla 'usuario'</p>";
                    echo "</div>";

                    echo "<table>";
                    echo "<tr><th>Campo</th><th>Valor</th></tr>";
                    foreach ($usuarioData as $key => $value) {
                        if ($key !== 'Password') {
                            echo "<tr><td><strong>$key</strong></td><td>" . ($value !== null ? htmlspecialchars($value) : '<em>NULL</em>') . "</td></tr>";
                        }
                    }
                    echo "</table>";

                    // 2. Verificar EstudianteID
                    echo "<h3>2️⃣ Verificando EstudianteID</h3>";

                    if (!empty($usuarioData['EstudianteID'])) {
                        $estudianteID = $usuarioData['EstudianteID'];

                        echo "<div class='success'>";
                        echo "<p>✅ EstudianteID encontrado: <strong>$estudianteID</strong></p>";
                        echo "</div>";

                        // 3. Buscar en tabla estudiante por ID
                        echo "<h3>3️⃣ Buscando en tabla ESTUDIANTE por EstudianteID</h3>";

                        $stmt2 = $pdo->prepare("SELECT * FROM estudiante WHERE EstudianteID = :id");
                        $stmt2->bindParam(':id', $estudianteID, PDO::PARAM_INT);
                        $stmt2->execute();
                        $estudianteData = $stmt2->fetch(PDO::FETCH_ASSOC);

                        if ($estudianteData) {
                            echo "<div class='success'>";
                            echo "<p>✅ <strong>ESTUDIANTE ENCONTRADO en la tabla 'estudiante'</strong></p>";
                            echo "</div>";

                            echo "<table>";
                            echo "<tr><th>Campo</th><th>Valor</th></tr>";
                            foreach ($estudianteData as $key => $value) {
                                echo "<tr><td><strong>$key</strong></td><td>" . ($value !== null ? htmlspecialchars($value) : '<em>NULL</em>') . "</td></tr>";
                            }
                            echo "</table>";

                        } else {
                            echo "<div class='error'>";
                            echo "<p>❌ NO se encontró estudiante con EstudianteID = $estudianteID</p>";
                            echo "</div>";
                        }

                        // 4. Verificar método usado en el código
                        echo "<h3>4️⃣ Verificando método ObtenerEstudiantePorCIModelo()</h3>";
                        echo "<div class='info'>";
                        echo "<p>El método actual busca por <code>Ci</code> en la tabla estudiante:</p>";
                        echo "<code>SELECT * FROM estudiante WHERE Ci = '{$usuario}'</code>";
                        echo "</div>";

                        $stmt3 = $pdo->prepare("SELECT * FROM estudiante WHERE Ci = :ci");
                        $stmt3->bindParam(':ci', $usuario, PDO::PARAM_STR);
                        $stmt3->execute();
                        $estudiantePorCI = $stmt3->fetch(PDO::FETCH_ASSOC);

                        if ($estudiantePorCI) {
                            echo "<div class='success'>";
                            echo "<p>✅ Estudiante encontrado por CI</p>";
                            echo "<p><strong>Estudiante:</strong> {$estudiantePorCI['Nombre']} {$estudiantePorCI['Apaterno']}</p>";
                            echo "</div>";
                        } else {
                            echo "<div class='error'>";
                            echo "<p>❌ NO se encontró estudiante con CI = '{$usuario}'</p>";
                            echo "<p><strong>Problema identificado:</strong> El método busca por CI pero debe buscar por EstudianteID</p>";
                            echo "</div>";
                        }

                    } else {
                        echo "<div class='error'>";
                        echo "<p>❌ <strong>PROBLEMA:</strong> El usuario NO tiene EstudianteID asociado</p>";
                        echo "<p>Este usuario no está vinculado a ningún estudiante en la base de datos.</p>";
                        echo "</div>";
                    }

                } else {
                    echo "<div class='error'>";
                    echo "<p>❌ Usuario NO encontrado en la tabla 'usuario'</p>";
                    echo "</div>";
                }
            }
            ?>

            <h3>💡 Solución Recomendada</h3>
            <div class="info">
                <p>El problema es que el método <code>ObtenerProgramasEstudianteControlador()</code> debería usar <strong>$_SESSION['EstudianteID']</strong> directamente en lugar de buscar por CI.</p>
                <p>La sesión ya tiene el EstudianteID guardado cuando el usuario inicia sesión.</p>
            </div>

            <div style="margin-top: 30px;">
                <a href="panel" class="btn">← Volver al Panel</a>
                <a href="salir" class="btn" style="background-color: #dc3545;">Cerrar Sesión</a>
            </div>

        <?php endif; ?>
    </div>
</body>
</html>
