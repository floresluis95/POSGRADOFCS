<?php
/**
 * AJAX de Requisitos de Matriculación (ventana "Requisitos" de Matriculados
 * y lista de requisitos del modal de Inscripción).
 */

require_once __DIR__ . '/validar-sesion.php';
ExigirSesionAjax(['ADM', 'SEC', 'AUX']);

require_once __DIR__ . '/../controladores/requisito.controlador.php';

header('Content-Type: application/json; charset=utf-8');

switch ($_POST['accion'] ?? '') {
    case 'catalogo':
        echo json_encode(['success' => true, 'requisitos' => RequisitoModelo::ListarActivosModelo()]);
        break;

    case 'obtener':
        echo json_encode(RequisitoControlador::ObtenerControlador($_POST['idInscripcion'] ?? 0));
        break;

    case 'guardar':
        echo json_encode(RequisitoControlador::GuardarControlador(
            $_POST['idInscripcion'] ?? 0,
            $_POST['requisitos'] ?? [],
            RequisitoControlador::UsuarioSesion()
        ));
        break;

    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
}
