<?php
/**
 * AJAX para obtener módulos por programa
 */

require_once "../modelos/pagomodulo.modelo.php";
require_once "../modelos/modulo.modelo.php";

// Obtener módulos con estado de pago por inscripción
if (isset($_POST["programaID"]) && isset($_POST["idinscripcion"])) {
    $programaID = (int)$_POST["programaID"];
    $idinscripcion = (int)$_POST["idinscripcion"];

    $modulos = PagoModuloModelo::ObtenerModulosConEstadoPagoModelo($programaID, $idinscripcion);

    echo json_encode($modulos);
    exit();
}

// Obtener módulos directamente por ProgramaId (desde tabla modulos)
if (isset($_POST["accion"]) && $_POST["accion"] == "obtenerModulosPorProgramaId") {
    $programaID = (int)$_POST["programaID"];

    $modulos = ModuloModelo::ObtenerModulosPorProgramaIdModelo($programaID);

    echo json_encode($modulos);
    exit();
}

// Obtener módulos por programa (sin estado de pago) - para compatibilidad
if (isset($_POST["programaID"])) {
    $programaID = (int)$_POST["programaID"];
    $modulos = PagoModuloModelo::ObtenerModulosPorProgramaModelo($programaID);

    echo json_encode($modulos);
    exit();
}
?>
