<?php
/**
 * AJAX para obtener datos de estudiantes
 */

// Determinar la ruta base del proyecto
$rutaBase = dirname(__DIR__);

require_once $rutaBase . '/modelos/estudiantes.modelo.php';
require_once $rutaBase . '/modelos/conexion.modelo.php';

class AjaxEstudiantes
{
    /**
     * Obtener datos completos de un estudiante por ID
     */
    public function ObtenerEstudiantePorId($id)
    {
        try {
            $pdo = Conexion::Conectar();

            $stmt = $pdo->prepare("
                SELECT
                    e.*,
                    p.NombreProfesion
                FROM estudiante e
                LEFT JOIN profesion p ON e.IdProfesion = p.IdProfesion
                WHERE e.EstudianteID = :id
            ");

            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($estudiante) {
                // Retornar JSON
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($estudiante);
                exit(); // Detener ejecución
            } else {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'Estudiante no encontrado']);
                exit(); // Detener ejecución
            }

        } catch (PDOException $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
            exit(); // Detener ejecución
        }
    }
}

// Procesar la petición
if (isset($_POST['idestudiante'])) {
    $ajax = new AjaxEstudiantes();
    $ajax->ObtenerEstudiantePorId($_POST['idestudiante']);
    exit(); // Asegurar que no se ejecute nada más
}
?>
