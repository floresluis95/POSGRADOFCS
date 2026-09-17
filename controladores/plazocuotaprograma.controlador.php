<?php
/**
 * Controlador del Plazo de Cuota por Programa (Calendario Académico).
 * Permisos:
 *  - ADM y SEC: pueden crear/editar/anular plazos y listar el panel.
 *  - EST: solo consulta el aviso propio (si tiene cuota vencida).
 */

require_once __DIR__ . '/../modelos/plazocuotaprograma.modelo.php';

class PlazoCuotaProgramaControlador
{
    private static function puedeEscribir()
    {
        $tipo = $_SESSION['Tipo'] ?? '';
        return in_array($tipo, ['ADM', 'SEC'], true);
    }

    /**
     * Listar plazos registrados (panel de administración).
     */
    public function ListarPlazosAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!self::puedeEscribir()) {
            echo json_encode(['success' => false, 'mensaje' => 'No tiene permiso para ver esta información']);
            exit();
        }

        echo json_encode(['success' => true, 'data' => PlazoCuotaProgramaModelo::ListarPlazosModelo()]);
        exit();
    }

    /**
     * Registrar un nuevo plazo de cuota para un programa.
     */
    public function RegistrarPlazoAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!self::puedeEscribir()) {
            echo json_encode(['success' => false, 'mensaje' => 'No tiene permiso para registrar plazos']);
            exit();
        }

        $programaID = isset($_POST['programaID']) ? (int)$_POST['programaID'] : 0;
        $fechaPlazo = trim($_POST['fechaPlazo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (empty($programaID) || empty($fechaPlazo)) {
            echo json_encode(['success' => false, 'mensaje' => 'Debe indicar el programa y la fecha plazo']);
            exit();
        }

        $idPlazo = PlazoCuotaProgramaModelo::RegistrarPlazoModelo([
            'ProgramaID' => $programaID,
            'FechaPlazo' => $fechaPlazo,
            'Descripcion' => $descripcion,
            'CreadoPorUsuarioID' => $_SESSION['IdUsuario'] ?? null,
        ]);

        if ($idPlazo === false) {
            echo json_encode(['success' => false, 'mensaje' => 'No se pudo registrar el plazo']);
            exit();
        }

        echo json_encode(['success' => true, 'idPlazo' => $idPlazo, 'mensaje' => 'Plazo de cuota registrado correctamente']);
        exit();
    }

    /**
     * Actualizar un plazo existente.
     */
    public function ActualizarPlazoAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!self::puedeEscribir()) {
            echo json_encode(['success' => false, 'mensaje' => 'No tiene permiso para editar plazos']);
            exit();
        }

        $idPlazo = isset($_POST['idPlazo']) ? (int)$_POST['idPlazo'] : 0;
        $programaID = isset($_POST['programaID']) ? (int)$_POST['programaID'] : 0;
        $fechaPlazo = trim($_POST['fechaPlazo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (empty($idPlazo) || empty($programaID) || empty($fechaPlazo)) {
            echo json_encode(['success' => false, 'mensaje' => 'Debe indicar el programa y la fecha plazo']);
            exit();
        }

        $ok = PlazoCuotaProgramaModelo::ActualizarPlazoModelo([
            'IdPlazo' => $idPlazo,
            'ProgramaID' => $programaID,
            'FechaPlazo' => $fechaPlazo,
            'Descripcion' => $descripcion,
        ]);

        echo json_encode([
            'success' => $ok,
            'mensaje' => $ok ? 'Plazo actualizado correctamente' : 'No se pudo actualizar el plazo'
        ]);
        exit();
    }

    /**
     * Anular (baja lógica) un plazo de cuota.
     */
    public function AnularPlazoAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!self::puedeEscribir()) {
            echo json_encode(['success' => false, 'mensaje' => 'No tiene permiso para eliminar plazos']);
            exit();
        }

        $idPlazo = isset($_POST['idPlazo']) ? (int)$_POST['idPlazo'] : 0;
        if (empty($idPlazo)) {
            echo json_encode(['success' => false, 'mensaje' => 'El plazo no existe']);
            exit();
        }

        $ok = PlazoCuotaProgramaModelo::AnularPlazoModelo($idPlazo);

        echo json_encode([
            'success' => $ok,
            'mensaje' => $ok ? 'Plazo eliminado correctamente' : 'No se pudo eliminar el plazo'
        ]);
        exit();
    }

    /**
     * Aviso para el Estudiante logueado: si tiene cuota(s) vencida(s) según
     * el plazo de su(s) programa(s), para mostrarlo como notificación al
     * ingresar al sistema.
     */
    public function AvisoEstudianteAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        $tipo = $_SESSION['Tipo'] ?? '';
        $estudianteID = $_SESSION['EstudianteID'] ?? null;

        if ($tipo !== 'EST' || empty($estudianteID)) {
            echo json_encode(['success' => true, 'hayAviso' => false, 'avisos' => []]);
            exit();
        }

        $avisos = PlazoCuotaProgramaModelo::ObtenerAvisosProgramaModelo($estudianteID);

        echo json_encode([
            'success' => true,
            'hayAviso' => count($avisos) > 0,
            'avisos' => $avisos
        ]);
        exit();
    }
}
