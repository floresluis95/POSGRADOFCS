<?php
require_once __DIR__ . '/control-solicitud.php'; // corte de internet: sin envíos duplicados
require_once __DIR__ . '/validar-sesion.php';
ExigirSesionAjax(['ADM', 'SEC', 'AUX']); // Solo el personal (no estudiantes ni docentes)
// Cuentas de los programas: las configura Administración o Secretaría; el Auxiliar solo las lista
ExigirRolParaAcciones(['registrar', 'cambiarEstado', 'marcarPrincipal'], ['ADM', 'SEC']);
/**
 * AJAX para gestión de cuentas por programa (registrar, listar,
 * cambiar estado, marcar principal) sin recargar la página.
 */
$rutaBase = dirname(__DIR__);

require_once $rutaBase . "/controladores/programacuenta.controlador.php";
require_once $rutaBase . "/modelos/programacuenta.modelo.php";

$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'registrar':
        header('Content-Type: application/json; charset=utf-8');

        $programaID = (int)($_POST['ProgramaID'] ?? 0);
        $tipoCuenta = strtoupper(htmlspecialchars(trim($_POST['TipoCuenta'] ?? '')));
        $numeroCuenta = strtoupper(htmlspecialchars(trim($_POST['NumeroCuenta'] ?? '')));
        $banco = strtoupper(htmlspecialchars(trim($_POST['Banco'] ?? '')));

        if ($programaID <= 0 || $tipoCuenta === '' || $numeroCuenta === '') {
            echo json_encode(['estado' => 'error', 'mensaje' => 'Debe indicar el tipo y el número de cuenta']);
            exit();
        }

        $resultado = ProgramaCuentaModelo::RegistrarModelo([
            'ProgramaID' => $programaID,
            'TipoCuenta' => $tipoCuenta,
            'NumeroCuenta' => $numeroCuenta,
            'Banco' => $banco,
        ]);

        $mensajes = [
            'exitoso'   => 'La cuenta se registró correctamente',
            'duplicado' => 'Ese tipo y número de cuenta ya está registrado para este programa',
            'error'     => 'No se pudo registrar la cuenta',
        ];

        echo json_encode([
            'estado' => $resultado,
            'mensaje' => $mensajes[$resultado] ?? $mensajes['error'],
        ]);
        exit();

    case 'cambiarEstado':
        header('Content-Type: application/json; charset=utf-8');

        $id = (int)($_POST['ProgramaCuentaID'] ?? 0);
        $nuevoEstado = ($_POST['NuevoEstado'] ?? '0') == '1' ? '1' : '0';

        $resultado = ProgramaCuentaModelo::CambiarEstadoModelo($id, $nuevoEstado);
        $mensaje = $nuevoEstado == '1' ? 'La cuenta fue activada correctamente' : 'La cuenta fue dada de baja correctamente';

        echo json_encode([
            'estado' => $resultado,
            'mensaje' => $resultado == 'exitoso' ? $mensaje : 'No se pudo modificar la cuenta',
        ]);
        exit();

    case 'marcarPrincipal':
        header('Content-Type: application/json; charset=utf-8');

        $id = (int)($_POST['ProgramaCuentaID'] ?? 0);
        $resultado = ProgramaCuentaModelo::MarcarPrincipalModelo($id);

        echo json_encode([
            'estado' => $resultado,
            'mensaje' => $resultado == 'exitoso' ? 'La cuenta fue marcada como principal' : 'No se pudo marcar la cuenta como principal',
        ]);
        exit();

    case 'listarHtml':
        // Devuelve las filas <tr> de la tabla de cuentas de un programa
        $programaID = (int)($_POST['ProgramaID'] ?? 0);
        $ctrl = new ProgramaCuentaControlador();
        $ctrl->ListarCuentasProgramaControlador($programaID);
        exit();

    case 'listarOptionsHtml':
        // Devuelve las <option> de las cuentas activas de un programa/tipo,
        // para poblar el <select> de "cuenta a depositar" en Pre-Registro
        $programaID = (int)($_POST['ProgramaID'] ?? 0);
        $tipoCuenta = strtoupper(htmlspecialchars(trim($_POST['TipoCuenta'] ?? 'MATRICULA')));
        $ctrl = new ProgramaCuentaControlador();
        $ctrl->RenderOptionsCuentaProgramaControlador($programaID, $tipoCuenta);
        exit();

    case 'listarTiposHtml':
        // Devuelve las <option> del datalist de tipos de cuenta ya usados
        $ctrl = new ProgramaCuentaControlador();
        $ctrl->RenderTiposCuentaDatalistControlador();
        exit();

    default:
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['estado' => 'error', 'mensaje' => 'Acción no válida']);
        exit();
}
