<?php
/**
 * Listar primeros estudiantes en la BD
 */

require_once 'modelos/conexion.modelo.php';

try {
    $pdo = Conexion::Conectar();

    $stmt = $pdo->query("SELECT EstudianteID, Ci, Nombre, Apaterno, Amaterno, Correo, Celular FROM estudiante LIMIT 10");
    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h2>Primeros 10 Estudiantes en la Base de Datos</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr>
            <th>ID</th>
            <th>CI</th>
            <th>Nombre</th>
            <th>Apellidos</th>
            <th>Correo</th>
            <th>Celular</th>
          </tr>";

    foreach ($estudiantes as $est) {
        echo "<tr>";
        echo "<td>{$est['EstudianteID']}</td>";
        echo "<td>{$est['Ci']}</td>";
        echo "<td>{$est['Nombre']}</td>";
        echo "<td>{$est['Apaterno']} {$est['Amaterno']}</td>";
        echo "<td>{$est['Correo']}</td>";
        echo "<td>{$est['Celular']}</td>";
        echo "</tr>";
    }

    echo "</table>";

    if (count($estudiantes) > 0) {
        $primerID = $estudiantes[0]['EstudianteID'];
        echo "<br><br>";
        echo "<strong>Prueba con el primer estudiante (ID: {$primerID}):</strong><br>";
        echo "<a href='test_ajax_estudiante.php?id={$primerID}' target='_blank'>Test AJAX con ID {$primerID}</a>";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
