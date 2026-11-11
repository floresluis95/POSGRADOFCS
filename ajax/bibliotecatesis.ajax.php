<?php
require_once __DIR__ . '/control-solicitud.php'; // corte de internet: sin envíos duplicados
/**
 * AJAX de préstamos de la Biblioteca de tesis (ver modelos/prestamotesis.modelo.php):
 *  - solicitar / cancelar: el estudiante o docente, sobre sus propios préstamos.
 *  - reservar / entregar / rechazar / devolver: solo el Administrador.
 */
require_once __DIR__ . '/validar-sesion.php';

$accion = $_POST['accion'] ?? '';
$deSolicitante = in_array($accion, ['solicitar', 'cancelar'], true);
ExigirSesionAjax($deSolicitante ? ['EST', 'DOC'] : ['ADM']);

require_once __DIR__ . '/../modelos/prestamotesis.modelo.php';
require_once __DIR__ . '/../modelos/ordenpagoprincipal.modelo.php'; // nombre de quien atiende

header('Content-Type: application/json; charset=utf-8');

$tipo = $_SESSION['Tipo'] ?? '';
$personaID = (int) ($tipo === 'DOC' ? ($_SESSION['DocenteID'] ?? 0) : ($_SESSION['EstudianteID'] ?? 0));

switch ($accion) {
    case 'solicitar':
        echo json_encode(PrestamoTesisModelo::Solicitar($_POST['idTrabajo'] ?? 0, $tipo, $personaID));
        break;

    case 'cancelar':
        echo json_encode(PrestamoTesisModelo::Cancelar($_POST['idPrestamo'] ?? 0, $tipo, $personaID));
        break;

    case 'reservar':
    case 'entregar':
    case 'rechazar':
    case 'devolver':
        echo json_encode(PrestamoTesisModelo::AccionAdministrador(
            $accion,
            $_POST['idPrestamo'] ?? 0,
            OrdenPagoModelos::ResponsableSesion(),
            $_POST['texto'] ?? ''
        ));
        break;

    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no reconocida']);
}
