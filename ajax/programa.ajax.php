<?php
require_once __DIR__ . '/control-solicitud.php'; // corte de internet: sin envíos duplicados
require_once __DIR__ . '/validar-sesion.php';
ExigirSesionAjax();
/**
 * AJAX para obtener datos de programas
 */

// Determinar la ruta base del proyecto
$rutaBase = dirname(__DIR__);

require_once $rutaBase . "/controladores/programa.controlador.php";
require_once $rutaBase . "/modelos/programa.modelo.php";

// Filtrar programas por grado académico (y opcionalmente por sede)
if(isset($_POST["grado"])) {
    $grado = $_POST["grado"];
    $sede = isset($_POST["sede"]) ? $_POST["sede"] : null;
    // Solo activos, el último registrado primero, con su estado según las fechas
    // (Por iniciar / En curso / Concluido) para distinguir programas del mismo nombre
    $respuesta = ProgramasModelos::BuscarProgramasConFiltros($grado, null, null, $sede, true);
    foreach ($respuesta as &$programa) {
        $estado = ProgramasControladores::EstadoProgramaControlador($programa);
        $programa['EstadoClave'] = $estado['clave'];
        $programa['EstadoEtiqueta'] = $estado['etiqueta'];
    }
    unset($programa);

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



