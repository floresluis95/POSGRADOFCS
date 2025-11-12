<?php
require_once "../controladores/programa.controlador.php";
require_once "../modelos/programa.modelo.php";

if(isset($_POST["idprograma"])) {
    $id = $_POST["idprograma"];
    $respuesta = ProgramasControladores::MostrarDetalleProgramaControlador($id);
    echo json_encode($respuesta);
}



if (isset($_POST["grado"])) {
    $grado = $_POST["grado"];
    $respuesta = ProgramaModelo::MostrarPorGrado($grado);
    echo json_encode($respuesta);
}