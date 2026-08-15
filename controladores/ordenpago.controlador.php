<?php
/**
 * Controlador de Orden de Pago (Preregistro)
 * Gestiona el preregistro de estudiantes sin voucher
 */

require_once __DIR__ . '/../modelos/ordenpago.modelo.php';
require_once __DIR__ . '/../modelos/conexion.modelo.php';

class OrdenPagoControladores
{
    /**
     * Registrar nueva orden de pago (preregistro)
     */
    public function RegistrarOrdenPagoControlador()
    {
        error_log("=== RegistrarOrdenPagoControlador ejecutado ===");
        error_log("POST data: " . print_r($_POST, true));

        if (isset($_POST["registrarOrdenPago"])) {
            error_log("POST registrarOrdenPago detectado");

            // Página a la que se debe regresar tras procesar el formulario (por defecto 'ordenpago')
            $paginaRedirect = isset($_POST['paginaRedirect'])
                ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['paginaRedirect'])
                : 'ordenpago';
            if ($paginaRedirect === '') {
                $paginaRedirect = 'ordenpago';
            }

            // Validar datos requeridos
            if (empty($_POST['idcliente']) || empty($_POST['programa']) ||
                empty($_POST['montoAPagar']) || empty($_POST['fechaInscripcion'])) {

                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "Todos los campos son obligatorios", "error")
                .then(function () {
                    location.href="' . $paginaRedirect . '";
                });
                </script>';
                return;
            }

            // Verificar si es pago completo
            $pagoCompleto = isset($_POST['pagoCompleto']) && $_POST['pagoCompleto'] == '1' ? 1 : 0;

            // Obtener el monto que viene del formulario
            $montoDesdeFormulario = floatval($_POST['montoAPagar']);

            if ($pagoCompleto) {
                // Pago completo: no se cobra matrícula separada
                $montoMatricula = 0;
                // montoPagado es el monto TOTAL del programa (con descuento si lo hay)
                $montoPagado = $montoDesdeFormulario;
            } else {
                // Solo matrícula
                $montoMatricula = $montoDesdeFormulario;
                $montoPagado = $montoDesdeFormulario;
            }

            // Obtener descuento aplicado (si existe)
            $porcentajeDescuento = isset($_POST['porcentajeDescuento']) ? floatval($_POST['porcentajeDescuento']) : 0;
            $montoDescuentoAplicado = isset($_POST['montoDescuento']) ? floatval($_POST['montoDescuento']) : 0;

            // Preparar datos para insertar
            $datosOrdenPago = array(
                "EstudianteID" => (int)$_POST['idcliente'],
                "ProgramaID" => (int)$_POST['programa'],
                "costomatricula" => $montoMatricula,
                "montoPagado" => $montoPagado,
                "pagoCompleto" => $pagoCompleto,
                "porcentajeDescuento" => $porcentajeDescuento,
                "montoDescuento" => $montoDescuentoAplicado,
                "FechaInscripcion" => htmlspecialchars(trim($_POST['fechaInscripcion']))
            );

            error_log("Datos de orden de pago preparados: " . print_r($datosOrdenPago, true));

            // Registrar en la base de datos
            $resultado = OrdenPagoModelos::RegistrarPreregistroModelo($datosOrdenPago);

            if ($resultado['status'] == 'exitoso') {
                // Redirigir al PDF de orden de pago
                $idInscripcion = $resultado['idInscripcion'];
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal({
                    title: "EXITOSO!",
                    text: "Orden de Pago registrada. Se generará el PDF automáticamente.",
                    icon: "success",
                    buttons: false,
                    timer: 2000
                }).then(function () {
                    window.open("vistas/componentes/orden-pago-pdf.php?idinscripcion=' . $idInscripcion . '", "_blank");
                    location.href="' . $paginaRedirect . '";
                });
                </script>';
            } elseif ($resultado['status'] == 'duplicado') {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "El estudiante ya está inscrito en este programa", "error")
                .then(function () {
                    location.href="' . $paginaRedirect . '";
                });
                </script>';
            } else {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "No se pudo registrar la orden de pago: ' . $resultado['mensaje'] . '", "error")
                .then(function () {
                    location.href="' . $paginaRedirect . '";
                });
                </script>';
            }
        }
    }

    /**
     * Listar preregistros (órdenes de pago pendientes)
     */
    public function ListarPreregistrosControlador()
    {
        $preregistros = OrdenPagoModelos::ListarPreregistrosModelo();
        return $preregistros;
    }
}

// Ejecutar controlador si viene de POST
if (isset($_POST["registrarOrdenPago"])) {
    $ordenPago = new OrdenPagoControladores();
    $ordenPago->RegistrarOrdenPagoControlador();
}
?>
