<?php
/**
 * Controlador de Pago del Programa (cuotas)
 * Gestiona el registro y consulta de cuotas del programa por inscripción.
 */

require_once __DIR__ . '/../modelos/modulopagos_core.php';
require_once file_exists(__DIR__ . '/../modelos/conexion.modelo.php') ? __DIR__ . '/../modelos/conexion.modelo.php' : __DIR__ . '/../core/conexion.modelo.php';

class PagoModuloControlador
{
    /**
     * Registrar el pago de una cuota del programa vía AJAX: responde JSON.
     */
    public function RegistrarPagoModuloAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->ProcesarRegistroCuota());
        exit();
    }

    /**
     * Lógica de validación y registro de una cuota del programa.
     * Devuelve ['estado' => 'exitoso'|'sin_orden'|'validacion'|'error', 'mensaje' => ..., 'idinscripcion' => ...]
     */
    private function ProcesarRegistroCuota()
    {
        if (empty($_POST['idinscripcion']) || empty($_POST['fechaPago']) || empty($_POST['montoCuota'])) {
            return ['estado' => 'validacion', 'mensaje' => 'Debe ingresar la fecha y el monto de la cuota'];
        }

        $idinscripcion = (int)$_POST['idinscripcion'];
        $fechaPago = htmlspecialchars(trim($_POST['fechaPago']));
        $numeroVaucher = htmlspecialchars(trim($_POST['numeroVaucher'] ?? ''));
        $montoCuota = floatval($_POST['montoCuota']);

        if ($montoCuota <= 0) {
            return ['estado' => 'validacion', 'mensaje' => 'El monto de la cuota debe ser mayor a 0'];
        }

        $archivoVoucher = null;
        if (isset($_FILES['fmodulo']) && $_FILES['fmodulo']['error'] == 0) {
            $archivoVoucher = file_get_contents($_FILES['fmodulo']['tmp_name']);
        }

        $resultado = PagoModuloModelo::RegistrarCuotaProgramaModelo([
            'idinscripcion' => $idinscripcion,
            'montocuota' => $montoCuota,
            'fechapago' => $fechaPago,
            'nvaucher' => $numeroVaucher,
            'fmodulo' => $archivoVoucher
        ]);

        $mensajes = [
            'exitoso'   => 'El pago de la cuota se registró correctamente',
            'sin_orden' => 'Debe generar primero la orden de pago (pestaña "Orden de Pago") antes de registrar el pago',
            'error'     => 'No se pudo registrar el pago'
        ];

        $estado = $resultado === 'exitoso' ? 'exitoso' : ($resultado === 'sin_orden' ? 'sin_orden' : 'error');

        return [
            'estado' => $estado,
            'mensaje' => $mensajes[$resultado] ?? $mensajes['error'],
            'idinscripcion' => $idinscripcion
        ];
    }

    /**
     * Rechazar el voucher de una cuota (queda disponible para reintentar).
     */
    public function RechazarCuotaAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (empty($_POST['idpago'])) {
            echo json_encode(['estado' => 'error', 'mensaje' => 'Falta el ID de la cuota']);
            exit();
        }

        $motivo = isset($_POST['motivo']) ? htmlspecialchars(trim($_POST['motivo'])) : null;
        $resultado = PagoModuloModelo::RechazarCuotaModelo((int)$_POST['idpago'], $motivo);

        echo json_encode([
            'estado' => $resultado ? 'exitoso' : 'error',
            'mensaje' => $resultado ? 'La cuota quedó marcada como rechazada' : 'No se pudo rechazar la cuota'
        ]);
        exit();
    }

    /**
     * Anular una cuota registrada.
     */
    public function AnularCuotaAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (empty($_POST['idpago'])) {
            echo json_encode(['estado' => 'error', 'mensaje' => 'Falta el ID de la cuota']);
            exit();
        }

        $resultado = PagoModuloModelo::AnularPagoModuloModelo((int)$_POST['idpago']);

        echo json_encode([
            'estado' => $resultado ? 'exitoso' : 'error',
            'mensaje' => $resultado ? 'La cuota fue anulada' : 'No se pudo anular la cuota'
        ]);
        exit();
    }

    /**
     * Resumen de pago del programa (costo total, pagado, saldo) + historial de cuotas,
     * para la pestaña "Pago de Módulos" (Cuotas del Programa) en Matriculados.
     */
    public function ObtenerResumenCuotasAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (empty($_POST['idinscripcion']) || empty($_POST['programaID'])) {
            echo json_encode(['success' => false, 'mensaje' => 'Faltan datos requeridos']);
            exit();
        }

        $idinscripcion = (int)$_POST['idinscripcion'];
        $programaID = (int)$_POST['programaID'];

        echo json_encode([
            'success' => true,
            'resumen' => PagoModuloModelo::ObtenerResumenPagoProgramaModelo($idinscripcion, $programaID),
            'cuotas' => PagoModuloModelo::ObtenerCuotasPorInscripcionModelo($idinscripcion)
        ]);
        exit();
    }

    /**
     * Obtener programas inscritos del estudiante logueado
     */
    public function ObtenerProgramasEstudianteControlador()
    {
        if (!isset($_SESSION['EstudianteID'])) {
            return json_encode([
                'success' => false,
                'error' => 'No se encontró el estudiante en la sesión'
            ]);
        }

        $estudianteID = $_SESSION['EstudianteID'];
        $programas = PagoModuloModelo::ObtenerProgramasEstudianteConInscripcionModelo($estudianteID);

        return json_encode([
            'success' => true,
            'programas' => $programas
        ]);
    }

    /**
     * Obtener detalle de pago del programa (resumen + cuotas) del estudiante por programa
     */
    public function ObtenerDetalleModulosEstudianteControlador()
    {
        if (!isset($_SESSION['EstudianteID'])) {
            return json_encode([
                'success' => false,
                'error' => 'No se encontró el estudiante en la sesión'
            ]);
        }

        if (!isset($_POST['programaID'])) {
            return json_encode([
                'success' => false,
                'error' => 'Falta el ID del programa'
            ]);
        }

        $estudianteID = $_SESSION['EstudianteID'];
        $programaID = (int)$_POST['programaID'];

        $detalle = PagoModuloModelo::ObtenerDetalleModulosEstudianteModelo($estudianteID, $programaID);

        return json_encode([
            'success' => true,
            'detalle' => $detalle
        ]);
    }
}
