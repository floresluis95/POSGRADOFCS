<?php
/**
 * AJAX para obtener módulos por programa
 */

require_once "../modelos/pagomodulo.modelo.php";

// Obtener módulos con estado de pago por inscripción
if (isset($_POST["programaID"]) && isset($_POST["idinscripcion"])) {
    $programaID = (int)$_POST["programaID"];
    $idinscripcion = (int)$_POST["idinscripcion"];

    error_log("AJAX - Obtener módulos con estado: ProgramaID=$programaID, idinscripcion=$idinscripcion");

    $modulos = PagoModuloModelo::ObtenerModulosConEstadoPagoModelo($programaID, $idinscripcion);

    error_log("AJAX - Módulos obtenidos: " . count($modulos));

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
