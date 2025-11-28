<?php
/**
 * Script de prueba para verificar el sistema de login
 * Este script verifica que la consulta funcione correctamente para los 3 tipos de usuarios
 */

require_once 'modelos/conexion.modelo.php';
require_once 'modelos/ingreso.modelo.php';

echo "<h2>Prueba del Sistema de Login</h2>";
echo "<hr>";

// Listar todos los usuarios en el sistema
echo "<h3>1. Usuarios registrados en el sistema:</h3>";
try {
    $conexion = Conexion::Conectar();
    $stmt = $conexion->prepare("
        SELECT
            u.Usuario,
            u.Tipo,
            u.Estado,
            CASE
                WHEN u.IdPersonal IS NOT NULL THEN 'Personal'
                WHEN u.EstudianteID IS NOT NULL THEN 'Estudiante'
                WHEN u.DocenteID IS NOT NULL THEN 'Docente'
                ELSE 'Desconocido'
            END as TipoUsuario
        FROM usuario u
        WHERE u.Estado = 1
        ORDER BY u.Tipo, u.Usuario
    ");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($usuarios) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr style='background-color: #f0f0f0;'><th>Usuario</th><th>Tipo</th><th>Categoría</th><th>Estado</th></tr>";
        foreach ($usuarios as $u) {
            $color = '';
            if ($u['TipoUsuario'] == 'Estudiante') $color = '#d4edda';
            if ($u['TipoUsuario'] == 'Docente') $color = '#d1ecf1';
            if ($u['TipoUsuario'] == 'Personal') $color = '#fff3cd';

            echo "<tr style='background-color: $color;'>";
            echo "<td><strong>" . htmlspecialchars($u['Usuario']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($u['Tipo']) . "</td>";
            echo "<td>" . htmlspecialchars($u['TipoUsuario']) . "</td>";
            echo "<td>" . ($u['Estado'] == 1 ? 'Activo' : 'Inactivo') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p><strong>Total de usuarios activos:</strong> " . count($usuarios) . "</p>";
    } else {
        echo "<p style='color: orange;'>⚠ No hay usuarios activos en el sistema.</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error al listar usuarios: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// Probar la consulta de login con diferentes usuarios
echo "<h3>2. Prueba de consulta de login:</h3>";

// Obtener un usuario de cada tipo para probar
try {
    $conexion = Conexion::Conectar();

    // Usuario de Personal
    $stmt = $conexion->prepare("SELECT u.Usuario FROM usuario u WHERE u.IdPersonal IS NOT NULL AND u.Estado = 1 LIMIT 1");
    $stmt->execute();
    $userPersonal = $stmt->fetch(PDO::FETCH_ASSOC);

    // Usuario Estudiante
    $stmt = $conexion->prepare("SELECT u.Usuario FROM usuario u WHERE u.EstudianteID IS NOT NULL AND u.Estado = 1 LIMIT 1");
    $stmt->execute();
    $userEstudiante = $stmt->fetch(PDO::FETCH_ASSOC);

    // Usuario Docente
    $stmt = $conexion->prepare("SELECT u.Usuario FROM usuario u WHERE u.DocenteID IS NOT NULL AND u.Estado = 1 LIMIT 1");
    $stmt->execute();
    $userDocente = $stmt->fetch(PDO::FETCH_ASSOC);

    // Probar cada tipo de usuario
    $usuariosPrueba = [];
    if ($userPersonal) $usuariosPrueba[] = ['usuario' => $userPersonal['Usuario'], 'tipo' => 'Personal'];
    if ($userEstudiante) $usuariosPrueba[] = ['usuario' => $userEstudiante['Usuario'], 'tipo' => 'Estudiante'];
    if ($userDocente) $usuariosPrueba[] = ['usuario' => $userDocente['Usuario'], 'tipo' => 'Docente'];

    if (count($usuariosPrueba) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr style='background-color: #f0f0f0;'><th>Usuario</th><th>Tipo Esperado</th><th>Resultado</th><th>Datos Cargados</th></tr>";

        foreach ($usuariosPrueba as $prueba) {
            $resultado = IngresoModelos::ValidarIngresoModelo($prueba['usuario']);

            if ($resultado) {
                $tipo_detectado = 'Desconocido';
                $datos_nombre = '';

                if (!empty($resultado['IdPersonal'])) {
                    $tipo_detectado = 'Personal';
                    $datos_nombre = $resultado['Nombres'] . ' ' . $resultado['ApellidoPaterno'];
                } elseif (!empty($resultado['EstudianteID'])) {
                    $tipo_detectado = 'Estudiante';
                    $datos_nombre = $resultado['EstudianteNombre'] . ' ' . $resultado['EstudianteApaterno'];
                } elseif (!empty($resultado['DocenteID'])) {
                    $tipo_detectado = 'Docente';
                    $datos_nombre = $resultado['DocenteNombre'] . ' ' . $resultado['DocenteApaterno'];
                }

                $correcto = ($tipo_detectado == $prueba['tipo']);
                $color = $correcto ? '#d4edda' : '#f8d7da';
                $icono = $correcto ? '✅' : '❌';

                echo "<tr style='background-color: $color;'>";
                echo "<td><strong>" . htmlspecialchars($prueba['usuario']) . "</strong></td>";
                echo "<td>" . htmlspecialchars($prueba['tipo']) . "</td>";
                echo "<td>$icono $tipo_detectado</td>";
                echo "<td>" . htmlspecialchars($datos_nombre) . "</td>";
                echo "</tr>";
            } else {
                echo "<tr style='background-color: #f8d7da;'>";
                echo "<td><strong>" . htmlspecialchars($prueba['usuario']) . "</strong></td>";
                echo "<td>" . htmlspecialchars($prueba['tipo']) . "</td>";
                echo "<td>❌ No encontrado</td>";
                echo "<td>-</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠ No hay usuarios de ningún tipo para probar.</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>Error en la prueba: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// Verificar estructura de tablas
echo "<h3>3. Verificación de estructura de tablas:</h3>";

try {
    $conexion = Conexion::Conectar();

    echo "<h4>Tabla usuario:</h4>";
    $stmt = $conexion->prepare("DESCRIBE usuario");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $camposRequeridos = ['IdPersonal', 'EstudianteID', 'DocenteID'];
    $camposEncontrados = array_column($columnas, 'Field');

    foreach ($camposRequeridos as $campo) {
        if (in_array($campo, $camposEncontrados)) {
            echo "✅ Campo <strong>$campo</strong> existe<br>";
        } else {
            echo "❌ Campo <strong>$campo</strong> NO existe<br>";
        }
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3 style='color: green;'>✅ Prueba completada</h3>";
echo "<p><a href='index.php'>Volver al inicio</a> | <a href='docentes'>Ir a Docentes</a> | <a href='estudiantes'>Ir a Estudiantes</a></p>";
?>
