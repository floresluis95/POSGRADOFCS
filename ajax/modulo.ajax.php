<?php
require_once __DIR__ . '/validar-sesion.php';
ExigirSesionAjax();
/**
 * AJAX para obtener módulos por programa
 */

require_once "../modelos/modulopagos_core.php";
require_once "../modelos/modulo.modelo.php";
require_once "../controladores/modulo.controlador.php";

// Registrar módulos por programa (responde JSON, sin recargar la página)
if (isset($_POST["accion"]) && $_POST["accion"] == "registrarModulosPorPrograma") {
    $controlador = new ModuloControlador();
    $controlador->RegistrarModulosPorProgramaAjaxControlador();
}

// Actualizar un módulo (responde JSON, sin recargar la página)
if (isset($_POST["accion"]) && $_POST["accion"] == "actualizarModulo") {
    $controlador = new ModuloControlador();
    $controlador->ActualizarModuloAjaxControlador();
}

// Revisar ortografía de nombres de módulos antes de guardar (sugerencias "¿Quiso decir...?")
if (isset($_POST["accion"]) && $_POST["accion"] == "sugerirCorreccionesNombres") {
    $controlador = new ModuloControlador();
    $controlador->SugerirCorreccionesNombresAjaxControlador();
}

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

// Cargar tabla HTML de módulos filtrada por programa
if (isset($_POST["accion"]) && $_POST["accion"] == "cargarTablaModulos") {
    // Filas de la tabla de módulos del programa elegido (las arma el controlador)
    $programaID = isset($_POST["programaIDFiltro"]) ? (int)$_POST["programaIDFiltro"] : null;
    ModuloControlador::TablaModulosPorProgramaControlador($programaID);
    exit();
}
?>
