<?php
/**
 * AJAX de Requisitos de Matriculación (ventana "Requisitos" de Matriculados,
 * lista de requisitos del modal de Inscripción, pantalla "Catálogo de
 * Requisitos" del ADM y "Mis Requisitos" del estudiante).
 */

require_once __DIR__ . '/validar-sesion.php';

$accion = $_POST['accion'] ?? '';

// Permisos por acción: el estudiante solo ve los suyos; el catálogo lo cambia
// solo el ADM; lo demás es del personal de inscripciones.
if ($accion === 'misRequisitos') {
    ExigirSesionAjax(['EST']);
} elseif (in_array($accion, ['listarCatalogo', 'guardarCatalogo', 'estadoCatalogo'], true)) {
    ExigirSesionAjax(['ADM']);
} else {
    ExigirSesionAjax(['ADM', 'SEC', 'AUX']);
}

require_once __DIR__ . '/../controladores/requisito.controlador.php';

header('Content-Type: application/json; charset=utf-8');

switch ($accion) {
    case 'catalogo':
        // Con programaID: solo los requisitos del grado académico de ese programa
        echo json_encode(['success' => true, 'requisitos' => RequisitoModelo::ListarActivosModelo((int)($_POST['programaID'] ?? 0))]);
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

    case 'listarCatalogo':
        echo json_encode(['success' => true, 'requisitos' => RequisitoModelo::ListarCatalogoModelo()]);
        break;

    case 'guardarCatalogo':
        echo json_encode(RequisitoControlador::GuardarCatalogoControlador($_POST));
        break;

    case 'estadoCatalogo':
        echo json_encode(RequisitoControlador::CambiarEstadoControlador($_POST['idRequisito'] ?? 0, $_POST['estado'] ?? ''));
        break;

    case 'misRequisitos':
        echo json_encode(RequisitoControlador::MisRequisitosControlador((int)($_SESSION['EstudianteID'] ?? 0)));
        break;

    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
}
