<?php
/**
 * Controlador de Vouchers
 * Gestiona la consulta de vouchers de pago
 */

require_once __DIR__ . '/../modelos/voucher.modelo.php';
require_once __DIR__ . '/../modelos/conexion.modelo.php';

class VoucherControlador
{
    /**
     * Obtener vouchers del programa con sus módulos asociados
     */
    public function ObtenerVouchersProgramaControlador()
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

        $vouchers = VoucherModelo::ObtenerVouchersProgramaModelo($estudianteID, $programaID);

        return json_encode([
            'success' => true,
            'vouchers' => $vouchers
        ]);
    }
}
?>
