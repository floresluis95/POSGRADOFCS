<?php
/**
 * AJAX de Autoridades (Director y Coordinador de Posgrado).
 * Solo el ADM puede verlas y cambiarlas.
 */

require_once __DIR__ . '/validar-sesion.php';
ExigirSesionAjax(['ADM']);

require_once __DIR__ . '/../controladores/autoridad.controlador.php';

header('Content-Type: application/json; charset=utf-8');

switch ($_POST['accion'] ?? '') {
    case 'listar':
        echo json_encode(['success' => true, 'autoridades' => AutoridadModelo::ListarModelo()]);
        break;

    case 'guardar':
        echo json_encode(AutoridadControlador::GuardarControlador(
            $_POST['autoridades'] ?? null,
            AutoridadControlador::UsuarioSesion()
        ));
        break;

    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
}
