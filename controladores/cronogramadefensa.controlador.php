<?php
/**
 * Controlador del Cronograma de Defensa (Tesis/Monografía) del Panel principal.
 * Permisos: ADM y SEC pueden crear/editar/anular. Cualquier usuario
 * autenticado puede listarlo (se muestra a todos en el Panel).
 */

require_once __DIR__ . '/../modelos/cronogramadefensa.modelo.php';

class CronogramaDefensaControlador
{
    private static function puedeEscribir()
    {
        $tipo = $_SESSION['Tipo'] ?? '';
        return in_array($tipo, ['ADM', 'SEC'], true);
    }

    public function ListarAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'data' => CronogramaDefensaModelo::ListarActivasModelo()]);
        exit();
    }

    public function RegistrarAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!self::puedeEscribir()) {
            echo json_encode(['success' => false, 'mensaje' => 'No tiene permiso para registrar defensas']);
            exit();
        }

        $nombre = trim($_POST['nombreEstudiante'] ?? '');
        $programaID = isset($_POST['programaID']) && $_POST['programaID'] !== '' ? (int)$_POST['programaID'] : null;
        $fecha = trim($_POST['fechaDefensa'] ?? '');
        $hora = trim($_POST['horaDefensa'] ?? '');
        $lugar = trim($_POST['lugar'] ?? '');
        $detalle = trim($_POST['detalle'] ?? '');

        if (empty($nombre) || empty($fecha) || empty($lugar)) {
            echo json_encode(['success' => false, 'mensaje' => 'El nombre, la fecha y el lugar son obligatorios']);
            exit();
        }

        $idDefensa = CronogramaDefensaModelo::RegistrarModelo([
            'NombreEstudiante' => $nombre,
            'ProgramaID' => $programaID,
            'FechaDefensa' => $fecha,
            'HoraDefensa' => $hora,
            'Lugar' => $lugar,
            'Detalle' => $detalle,
            'CreadoPorUsuarioID' => $_SESSION['IdUsuario'] ?? null,
        ]);

        if ($idDefensa === false) {
            echo json_encode(['success' => false, 'mensaje' => 'No se pudo registrar la defensa']);
            exit();
        }

        echo json_encode(['success' => true, 'idDefensa' => $idDefensa, 'mensaje' => 'Defensa registrada correctamente']);
        exit();
    }

    public function ActualizarAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!self::puedeEscribir()) {
            echo json_encode(['success' => false, 'mensaje' => 'No tiene permiso para editar defensas']);
            exit();
        }

        $idDefensa = isset($_POST['idDefensa']) ? (int)$_POST['idDefensa'] : 0;
        $nombre = trim($_POST['nombreEstudiante'] ?? '');
        $programaID = isset($_POST['programaID']) && $_POST['programaID'] !== '' ? (int)$_POST['programaID'] : null;
        $fecha = trim($_POST['fechaDefensa'] ?? '');
        $hora = trim($_POST['horaDefensa'] ?? '');
        $lugar = trim($_POST['lugar'] ?? '');
        $detalle = trim($_POST['detalle'] ?? '');

        if (empty($idDefensa) || empty($nombre) || empty($fecha) || empty($lugar)) {
            echo json_encode(['success' => false, 'mensaje' => 'El nombre, la fecha y el lugar son obligatorios']);
            exit();
        }

        $ok = CronogramaDefensaModelo::ActualizarModelo([
            'IdDefensa' => $idDefensa,
            'NombreEstudiante' => $nombre,
            'ProgramaID' => $programaID,
            'FechaDefensa' => $fecha,
            'HoraDefensa' => $hora,
            'Lugar' => $lugar,
            'Detalle' => $detalle,
        ]);

        echo json_encode([
            'success' => $ok,
            'mensaje' => $ok ? 'Defensa actualizada correctamente' : 'No se pudo actualizar la defensa'
        ]);
        exit();
    }

    public function AnularAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!self::puedeEscribir()) {
            echo json_encode(['success' => false, 'mensaje' => 'No tiene permiso para eliminar defensas']);
            exit();
        }

        $idDefensa = isset($_POST['idDefensa']) ? (int)$_POST['idDefensa'] : 0;
        if (empty($idDefensa)) {
            echo json_encode(['success' => false, 'mensaje' => 'La defensa no existe']);
            exit();
        }

        $ok = CronogramaDefensaModelo::AnularModelo($idDefensa);

        echo json_encode([
            'success' => $ok,
            'mensaje' => $ok ? 'Defensa eliminada correctamente' : 'No se pudo eliminar la defensa'
        ]);
        exit();
    }
}
