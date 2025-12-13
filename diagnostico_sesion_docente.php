<?php
/**
 * Script de diagnóstico para verificar la sesión del docente
 * Accede a este archivo desde el navegador después de iniciar sesión como docente
 */

session_start();
require_once 'modelos/conexion.modelo.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico Sesión Docente</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .card { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        h2 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
    </style>
</head>
<body>
    <h1>🔍 Diagnóstico de Sesión - Notasdocente</h1>

    <div class="card">
        <h2>1. Variables de Sesión</h2>
        <?php if (isset($_SESSION['Usuario'])): ?>
            <p class="success">✓ Sesión activa</p>
            <pre><?php print_r($_SESSION); ?></pre>
        <?php else: ?>
            <p class="error">✗ No hay sesión activa. Por favor inicia sesión primero.</p>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['Usuario'])): ?>
    <div class="card">
        <h2>2. Buscar Docente en BD</h2>
        <?php
        $ci = $_SESSION['Usuario'];
        echo "<p>CI de sesión: <strong>$ci</strong></p>";

        try {
            $pdo = Conexion::Conectar();

            // Buscar en tabla docente
            $stmt = $pdo->prepare("SELECT * FROM docente WHERE Ci = :ci LIMIT 1");
            $stmt->bindParam(":ci", $ci, PDO::PARAM_STR);
            $stmt->execute();
            $docente = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($docente) {
                echo "<p class='success'>✓ Docente encontrado en la base de datos</p>";
                echo "<pre>";
                print_r($docente);
                echo "</pre>";
            } else {
                echo "<p class='error'>✗ No se encontró docente con CI: $ci</p>";

                // Buscar CIs similares
                echo "<h3>CIs registrados en tabla docente:</h3>";
                $stmt = $pdo->query("SELECT DocenteID, Ci, Nombre, Apaterno, Amaterno FROM docente ORDER BY DocenteID LIMIT 10");
                $docentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo "<pre>";
                foreach ($docentes as $d) {
                    echo "DocenteID: {$d['DocenteID']} - CI: {$d['Ci']} - {$d['Nombre']} {$d['Apaterno']}\n";
                }
                echo "</pre>";
            }

        } catch (PDOException $e) {
            echo "<p class='error'>Error de BD: " . $e->getMessage() . "</p>";
        }
        ?>
    </div>

    <?php if (isset($docente) && $docente): ?>
    <div class="card">
        <h2>3. Módulos Asignados</h2>
        <?php
        try {
            $docenteID = $docente['DocenteID'];

            $stmt = $pdo->prepare("
                SELECT m.*, p.NombrePrograma, p.GradoAcademico
                FROM modulos m
                LEFT JOIN programa p ON m.ProgramaId = p.ProgramaID
                WHERE m.DocenteID = :docenteID
                ORDER BY p.GradoAcademico, m.codigomodulo
            ");
            $stmt->bindParam(":docenteID", $docenteID, PDO::PARAM_INT);
            $stmt->execute();
            $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($modulos) > 0) {
                echo "<p class='success'>✓ Total módulos asignados: " . count($modulos) . "</p>";
                echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr><th>ID</th><th>Grado</th><th>Programa</th><th>Módulo</th><th>Código</th><th>Estado</th></tr>";
                foreach ($modulos as $m) {
                    echo "<tr>";
                    echo "<td>{$m['Idmodulo']}</td>";
                    echo "<td>{$m['GradoAcademico']}</td>";
                    echo "<td>{$m['NombrePrograma']}</td>";
                    echo "<td>{$m['nombremodulo']}</td>";
                    echo "<td>{$m['codigomodulo']}</td>";
                    echo "<td>{$m['estadomodulo']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='warning'>⚠ No hay módulos asignados a este docente</p>";
            }

        } catch (PDOException $e) {
            echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
        }
        ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <div class="card">
        <h2>4. Acciones</h2>
        <p>
            <a href="index.php?ruta=notasdocente" style="display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 4px;">
                Ir a Notasdocente
            </a>
        </p>
        <p>
            <a href="?refresh=1" style="display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 4px;">
                Actualizar Diagnóstico
            </a>
        </p>
    </div>
</body>
</html>
