<?php
/**
 * AJAX para obtener módulos por programa
 */

require_once "../modelos/inscripcionmodulo.modelo.php";

// Obtener módulos por programa
if (isset($_POST["programaID"])) {
    $programaID = (int)$_POST["programaID"];
    $modulos = InscripcionModuloModelos::ObtenerModulosPorProgramaModelo($programaID);

    echo json_encode($modulos);
    exit();
}
?>
