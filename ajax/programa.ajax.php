<?php
require_once "../controladores/programa.controlador.php";
require_once "../modelos/programa.modelo.php";

// Filtrar programas por grado académico
if(isset($_POST["grado"])) {
    $grado = $_POST["grado"];
    $respuesta = ProgramasModelos::BuscarProgramasConFiltros($grado);
    echo json_encode($respuesta);
}
// Obtener detalle de un programa específico
elseif(isset($_POST["idprograma"])) {
    $id = $_POST["idprograma"];
    $respuesta = ProgramasControladores::MostrarDetalleProgramaControlador($id);
    echo json_encode($respuesta);
}



