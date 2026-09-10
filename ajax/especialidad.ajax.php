<?php
/**
 * AJAX para gestión del catálogo de especialidades (registrar, listar,
 * cambiar estado) sin recargar la página.
 */
$rutaBase = dirname(__DIR__);

require_once $rutaBase . "/controladores/especialidad.controlador.php";
require_once $rutaBase . "/modelos/especialidad.modelo.php";

$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'registrar':
        header('Content-Type: application/json; charset=utf-8');

        $nombre = htmlspecialchars(trim($_POST['Nombre'] ?? ''));

        if ($nombre === '') {
            echo json_encode(['estado' => 'error', 'mensaje' => 'Debe indicar el nombre de la especialidad']);
            exit();
        }

        $resultado = EspecialidadModelo::RegistrarModelo($nombre);

        $mensajes = [
            'exitoso'   => 'La especialidad se registró correctamente',
            'duplicado' => 'Esa especialidad ya está registrada',
            'error'     => 'No se pudo registrar la especialidad',
        ];

        echo json_encode([
            'estado' => $resultado,
            'mensaje' => $mensajes[$resultado] ?? $mensajes['error'],
            'nombre' => $nombre,
        ]);
        exit();

    case 'cambiarEstado':
        header('Content-Type: application/json; charset=utf-8');

        $id = (int)($_POST['EspecialidadID'] ?? 0);
        $nuevoEstado = ($_POST['NuevoEstado'] ?? '0') == '1' ? '1' : '0';

        $resultado = EspecialidadModelo::CambiarEstadoModelo($id, $nuevoEstado);
        $mensaje = $nuevoEstado == '1' ? 'La especialidad fue activada correctamente' : 'La especialidad fue dada de baja correctamente';

        echo json_encode([
            'estado' => $resultado,
            'mensaje' => $resultado == 'exitoso' ? $mensaje : 'No se pudo modificar la especialidad',
        ]);
        exit();

    case 'listarHtml':
        // Filas <tr> de la tabla de administración
        $ctrl = new EspecialidadControlador();
        $ctrl->ListarEspecialidadesControlador();
        exit();

    case 'listarOpcionesHtml':
        // <option> de especialidades activas para el select del formulario
        $ctrl = new EspecialidadControlador();
        $ctrl->RenderOpcionesActivasControlador();
        exit();

    default:
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['estado' => 'error', 'mensaje' => 'Acción no válida']);
        exit();
}
