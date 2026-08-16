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

<<<<<<< Updated upstream
            // Página a la que se debe regresar tras procesar el formulario (por defecto 'ordenpago')
            $paginaRedirect = isset($_POST['paginaRedirect'])
                ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['paginaRedirect'])
                : 'ordenpago';
            if ($paginaRedirect === '') {
                $paginaRedirect = 'ordenpago';
            }

            // Validar datos requeridos
=======
            // Validar datos requeridos (incluyendo nuevos campos)
>>>>>>> Stashed changes
            if (empty($_POST['idcliente']) || empty($_POST['programa']) ||
                empty($_POST['montoAPagar']) || empty($_POST['fechaInscripcion']) ||
                empty($_POST['nombreFactura']) || empty($_POST['nitCiFactura']) ||
                empty($_POST['responsable'])) {

                echo '
                <script>
<<<<<<< Updated upstream
                swal("ERROR!", "Todos los campos son obligatorios", "error")
                .then(function () {
                    location.href="' . $paginaRedirect . '";
=======
                window.addEventListener("load", function() {
                    swal("ERROR!", "Todos los campos obligatorios deben ser completados", "error")
                    .then(function () {
                        location.href="ordenpago";
                    });
>>>>>>> Stashed changes
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

            // Capturar campos de facturación
            $nombreFactura = htmlspecialchars(trim($_POST['nombreFactura']));
            $nitCiFactura = htmlspecialchars(trim($_POST['nitCiFactura']));
            $responsable = htmlspecialchars(trim($_POST['responsable']));
            $firma = isset($_POST['firma']) ? htmlspecialchars(trim($_POST['firma'])) : '';

            // Preparar datos para insertar
            $datosOrdenPago = array(
                "EstudianteID" => (int)$_POST['idcliente'],
                "ProgramaID" => (int)$_POST['programa'],
                "costomatricula" => $montoMatricula,
                "montoPagado" => $montoPagado,
                "pagoCompleto" => $pagoCompleto,
                "porcentajeDescuento" => $porcentajeDescuento,
                "montoDescuento" => $montoDescuentoAplicado,
                "FechaInscripcion" => htmlspecialchars(trim($_POST['fechaInscripcion'])),
                "NombreFactura" => $nombreFactura,
                "NitCiFactura" => $nitCiFactura,
                "Responsable" => $responsable,
                "Firma" => $firma
            );

            error_log("Datos de orden de pago preparados: " . print_r($datosOrdenPago, true));

            // Registrar en la base de datos
            $resultado = OrdenPagoModelos::RegistrarPreregistroModelo($datosOrdenPago);

            if ($resultado['status'] == 'exitoso') {
                // Redirigir a la vista de orden generada
                $idOrdenPago = $resultado['idOrdenPago'];

                echo '
                <script>
<<<<<<< Updated upstream
                swal({
                    title: "EXITOSO!",
                    text: "Orden de Pago registrada. Se generará el PDF automáticamente.",
                    icon: "success",
                    buttons: false,
                    timer: 2000
                }).then(function () {
                    window.open("vistas/componentes/orden-pago-pdf.php?idinscripcion=' . $idInscripcion . '", "_blank");
                    location.href="' . $paginaRedirect . '";
=======
                window.addEventListener("load", function() {
                    swal("EXITOSO!", "Orden de Pago registrada correctamente", "success")
                    .then(function () {
                        window.location.href = "orden-generada&id=' . $idOrdenPago . '";
                    });
>>>>>>> Stashed changes
                });
                </script>';
            } elseif ($resultado['status'] == 'duplicado') {
                echo '
                <script>
<<<<<<< Updated upstream
                swal("ERROR!", "El estudiante ya está inscrito en este programa", "error")
                .then(function () {
                    location.href="' . $paginaRedirect . '";
=======
                window.addEventListener("load", function() {
                    swal("ERROR!", "El estudiante ya está inscrito en este programa", "error")
                    .then(function () {
                        location.href="ordenpago";
                    });
>>>>>>> Stashed changes
                });
                </script>';
            } else {
                echo '
                <script>
<<<<<<< Updated upstream
                swal("ERROR!", "No se pudo registrar la orden de pago: ' . $resultado['mensaje'] . '", "error")
                .then(function () {
                    location.href="' . $paginaRedirect . '";
=======
                window.addEventListener("load", function() {
                    swal("ERROR!", "No se pudo registrar la orden de pago: ' . $resultado['mensaje'] . '", "error")
                    .then(function () {
                        location.href="ordenpago";
                    });
>>>>>>> Stashed changes
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

    /**
     * Convertir número a letras (para monto literal en PDF)
     */
    private function numeroALetras($numero)
    {
        $entero = floor($numero);
        $decimales = round(($numero - $entero) * 100);

        $letras = $this->convertirEnteroALetras($entero);

        if ($decimales > 0) {
            return strtoupper($letras . ' CON ' . $decimales . '/100 BOLIVIANOS');
        } else {
            return strtoupper($letras . ' 00/100 BOLIVIANOS');
        }
    }

    private function convertirEnteroALetras($numero)
    {
        $unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
        $decenas = ['', '', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
        $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

        if ($numero == 0) return 'CERO';
        if ($numero == 100) return 'CIEN';

        $resultado = '';

        // Millones
        if ($numero >= 1000000) {
            $millones = floor($numero / 1000000);
            if ($millones == 1) {
                $resultado .= 'UN MILLON ';
            } else {
                $resultado .= $this->convertirEnteroALetras($millones) . ' MILLONES ';
            }
            $numero %= 1000000;
        }

        // Miles
        if ($numero >= 1000) {
            $miles = floor($numero / 1000);
            if ($miles == 1) {
                $resultado .= 'MIL ';
            } else {
                $resultado .= $this->convertirEnteroALetras($miles) . ' MIL ';
            }
            $numero %= 1000;
        }

        // Centenas
        if ($numero >= 100) {
            $centena = floor($numero / 100);
            $resultado .= $centenas[$centena] . ' ';
            $numero %= 100;
        }

        // Decenas y unidades
        if ($numero >= 10 && $numero < 20) {
            $resultado .= $especiales[$numero - 10] . ' ';
        } elseif ($numero >= 20) {
            $decena = floor($numero / 10);
            $unidad = $numero % 10;
            $resultado .= $decenas[$decena];
            if ($unidad > 0) {
                $resultado .= ' Y ' . $unidades[$unidad];
            }
            $resultado .= ' ';
        } elseif ($numero > 0) {
            $resultado .= $unidades[$numero] . ' ';
        }

        return trim($resultado);
    }
}

// Ejecutar controlador si viene de POST
if (isset($_POST["registrarOrdenPago"])) {
    $ordenPago = new OrdenPagoControladores();
    $ordenPago->RegistrarOrdenPagoControlador();
}
?>
