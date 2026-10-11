<?php
/**
 * Controlador de Pago de Clínicas
 * Valida los datos que llegan desde la pestaña "Clínicas" del modal Gestión de Pagos
 * (Matriculados) y delega en PagoClinicaModelo. Todas las respuestas son arrays que
 * el ajax devuelve como JSON.
 */

require_once __DIR__ . '/../modelos/pagoclinica.modelo.php';
require_once __DIR__ . '/../modelos/voucher.modelo.php';
require_once __DIR__ . '/../modelos/ordenpagoprincipal.modelo.php';

class PagoClinicaControlador
{
    /**
     * Estado de la pestaña: si el programa maneja clínicas, la orden pendiente
     * (si existe) y el historial de órdenes/pagos de clínica de la inscripción.
     */
    public function ObtenerEstadoControlador()
    {
        $idinscripcion = (int)($_POST['idinscripcion'] ?? 0);
        $programaID = (int)($_POST['programaID'] ?? 0);

        if ($idinscripcion <= 0 || $programaID <= 0) {
            return ['success' => false, 'mensaje' => 'Faltan la inscripción o el programa'];
        }

        $tieneClinica = PagoClinicaModelo::ProgramaTieneClinicaModelo($programaID);

        return [
            'success' => true,
            'tieneClinica' => $tieneClinica,
            'orden' => $tieneClinica ? PagoClinicaModelo::ObtenerOrdenPendienteModelo($idinscripcion) : null,
            'historial' => $tieneClinica ? PagoClinicaModelo::ListarPorInscripcionModelo($idinscripcion) : [],
        ];
    }

    /**
     * Generar la orden de pago de clínica (monto libre).
     */
    public function RegistrarOrdenControlador()
    {
        $estudianteID = (int)($_POST['estudianteID'] ?? 0);
        $idinscripcion = (int)($_POST['idinscripcion'] ?? 0);
        $programaID = (int)($_POST['programaID'] ?? 0);
        $montoTotal = round((float)($_POST['montoTotal'] ?? 0), 2);

        if ($estudianteID <= 0 || $idinscripcion <= 0 || $programaID <= 0) {
            return ['success' => false, 'mensaje' => 'Faltan datos requeridos'];
        }
        if ($montoTotal <= 0) {
            return ['success' => false, 'mensaje' => 'El monto de la clínica debe ser mayor a 0'];
        }
        if (!PagoClinicaModelo::ProgramaTieneClinicaModelo($programaID)) {
            return ['success' => false, 'mensaje' => 'Este programa no tiene una cuenta de CLINICA activa'];
        }

        $fechaVencimiento = trim($_POST['fechaVencimiento'] ?? '');
        if ($fechaVencimiento !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaVencimiento)) {
            return ['success' => false, 'mensaje' => 'La fecha límite de pago no es válida'];
        }

        $resultado = PagoClinicaModelo::RegistrarOrdenModelo([
            'EstudianteID' => $estudianteID,
            'idinscripcion' => $idinscripcion,
            'ProgramaID' => $programaID,
            'ProgramaCuentaID' => !empty($_POST['programaCuentaID']) ? (int)$_POST['programaCuentaID'] : null,
            'MontoTotal' => $montoTotal,
            'FechaVencimiento' => $fechaVencimiento !== '' ? $fechaVencimiento : null,
            // El responsable es siempre el usuario de la sesión, no un campo del formulario
            'ResponsableGeneracion' => $this->UsuarioSesion(),
            'Firma' => htmlspecialchars(trim($_POST['firma'] ?? '')),
            'NombreFactura' => trim($_POST['nombreFactura'] ?? ''),
            'NitCiFactura' => trim($_POST['nitCiFactura'] ?? ''),
        ]);

        return [
            'success' => $resultado['estado'] === 'exitoso',
            'mensaje' => $resultado['mensaje'],
            'numeroOrden' => $resultado['numeroOrden'] ?? null,
            'idOrdenPago' => $resultado['idOrdenPago'] ?? null,
        ];
    }

    /**
     * Registrar el voucher del pago de clínica: confirma la orden pendiente.
     */
    public function RegistrarPagoControlador()
    {
        $idinscripcion = (int)($_POST['idinscripcion'] ?? 0);
        $fechaPago = trim($_POST['fechaPago'] ?? '');
        $numeroVoucher = htmlspecialchars(trim($_POST['numeroVoucher'] ?? ''));

        if ($idinscripcion <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaPago)) {
            return ['success' => false, 'mensaje' => 'Debe ingresar la fecha de pago'];
        }
        if ($numeroVoucher === '') {
            return ['success' => false, 'mensaje' => 'El N° de voucher o comprobante es obligatorio'];
        }
        if (empty($_FILES['fotoVoucher']) || $_FILES['fotoVoucher']['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'mensaje' => 'La foto o archivo del voucher es obligatoria'];
        }
        if ($_FILES['fotoVoucher']['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'mensaje' => 'El archivo es muy grande. Máximo 5MB'];
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES['fotoVoucher']['tmp_name']);
        $permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
        if (!in_array($mime, $permitidos, true)) {
            return ['success' => false, 'mensaje' => 'Formato no permitido. Solo JPG, PNG, GIF, WEBP o PDF'];
        }

        // El N° de voucher es único en todo el sistema (matrículas, cuotas y clínicas)
        $usadoEn = VoucherModelo::VoucherYaUsadoModelo($numeroVoucher);
        if ($usadoEn !== null) {
            return ['success' => false, 'mensaje' => VoucherModelo::MensajeVoucherRepetido($numeroVoucher, $usadoEn)];
        }

        $resultado = PagoClinicaModelo::RegistrarPagoModelo([
            'idinscripcion' => $idinscripcion,
            'FechaPago' => $fechaPago,
            'NumeroVoucher' => $numeroVoucher,
            'FotoVoucher' => file_get_contents($_FILES['fotoVoucher']['tmp_name']),
            'ResponsableRegistro' => $this->UsuarioSesion(),
        ]);

        return [
            'success' => $resultado['estado'] === 'exitoso',
            'mensaje' => $resultado['mensaje'],
        ];
    }

    /**
     * Enviar al navegador la foto/PDF del voucher de un pago de clínica.
     */
    public function MostrarFotoVoucherControlador($idPagoClinica)
    {
        $archivo = PagoClinicaModelo::ObtenerFotoVoucherModelo((int)$idPagoClinica);
        if ($archivo === null) {
            header('HTTP/1.1 404 Not Found');
            exit('Archivo no encontrado');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        header('Content-Type: ' . $finfo->buffer($archivo));
        header('Content-Length: ' . strlen($archivo));
        echo $archivo;
        exit;
    }

    private function UsuarioSesion()
    {
        return OrdenPagoModelos::ResponsableSesion();
    }
}
