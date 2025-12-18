<?php
/**
 * AJAX para obtener datos de programas
 */

// Determinar la ruta base del proyecto
$rutaBase = dirname(__DIR__);

require_once $rutaBase . "/controladores/programa.controlador.php";
require_once $rutaBase . "/modelos/programa.modelo.php";

// Filtrar programas por grado académico
if(isset($_POST["grado"])) {
    $grado = $_POST["grado"];
    $respuesta = ProgramasModelos::BuscarProgramasConFiltros($grado);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($respuesta);
    exit();
}
// Obtener detalle de un programa específico
elseif(isset($_POST["idprograma"])) {
    $id = $_POST["idprograma"];
    $respuesta = ProgramasControladores::MostrarDetalleProgramaControlador($id);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($respuesta);
    exit();
}

// Si no se recibió ningún parámetro válido
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['error' => 'No se recibieron parámetros válidos']);
exit();



