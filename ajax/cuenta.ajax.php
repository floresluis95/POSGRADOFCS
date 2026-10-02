<?php
require_once __DIR__ . '/validar-sesion.php';
ExigirSesionAjax(['ADM', 'SEC', 'AUX']); // Solo el personal (no estudiantes ni docentes)
/**
 * AJAX para gestión de cuentas (registrar, listar, cambiar estado)
 * Evita recargar la página completa mientras el modal de cuentas
 * está abierto sobre el modal de "Nuevo programa".
 */
$rutaBase = dirname(__DIR__);

require_once $rutaBase . "/controladores/cuenta.controlador.php";
require_once $rutaBase . "/modelos/cuenta.modelo.php";

$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'registrar':
        header('Content-Type: application/json; charset=utf-8');

        $numeroCuenta = strtoupper(htmlspecialchars(trim($_POST['NumeroCuenta'] ?? '')));
        $banco = strtoupper(htmlspecialchars(trim($_POST['Banco'] ?? '')));

        if (empty($numeroCuenta)) {
            echo json_encode(['estado' => 'error', 'mensaje' => 'Debe indicar el número de cuenta']);
            exit();
        }

        $resultado = CuentaModelo::RegistrarCuentaModelo([
            'NumeroCuenta' => $numeroCuenta,
            'Banco' => $banco
        ]);

        $mensajes = [
            'exitoso'   => 'La cuenta se registró correctamente',
            'duplicado' => 'Esa cuenta ya está registrada',
            'error'     => 'No se pudo registrar la cuenta'
        ];

        echo json_encode([
            'estado' => $resultado,
            'mensaje' => $mensajes[$resultado] ?? $mensajes['error']
        ]);
        exit();

    case 'cambiarEstado':
        header('Content-Type: application/json; charset=utf-8');

        $id = (int)($_POST['CuentaID'] ?? 0);
        $nuevoEstado = ($_POST['NuevoEstado'] ?? '0') == '1' ? '1' : '0';

        $resultado = CuentaModelo::CambiarEstadoCuentaModelo($id, $nuevoEstado);
        $mensaje = $nuevoEstado == '1' ? 'La cuenta fue activada correctamente' : 'La cuenta fue dada de baja correctamente';

        echo json_encode([
            'estado' => $resultado,
            'mensaje' => $resultado == 'exitoso' ? $mensaje : 'No se pudo modificar la cuenta'
        ]);
        exit();

    case 'listarHtml':
        // Devuelve las filas <tr> de la tabla de administración de cuentas
        $ctrl = new CuentaControlador();
        $ctrl->ListarCuentasControlador();
        exit();

    case 'listarActivasHtml':
        // Devuelve las <option> de cuentas activas para el select de programas
        $ctrl = new CuentaControlador();
        $ctrl->RenderOpcionesCuentasActivasControlador();
        exit();

    default:
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['estado' => 'error', 'mensaje' => 'Acción no válida']);
        exit();
}
