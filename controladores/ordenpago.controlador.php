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
        if (isset($_POST["registrarOrdenPago"])) {

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
                <script>
                window.addEventListener("load", function() {
                    swal("ERROR!", "Todos los campos obligatorios deben ser completados", "error")
                    .then(function () {
                        location.href="' . $paginaRedirect . '";
                    });
                });
                </script>';
                return;
            }

            // Verificar si es pago completo
            $pagoCompleto = isset($_POST['pagoCompleto']) && $_POST['pagoCompleto'] == '1' ? 1 : 0;

            // Obtener el monto que viene del formulario (ya con el descuento del plan de pago aplicado)
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

            // Obtener descuento aplicado (si existe), según el plan de pago elegido
            $porcentajeDescuento = isset($_POST['porcentajeDescuento']) ? floatval($_POST['porcentajeDescuento']) : 0;
            $montoDescuentoAplicado = isset($_POST['montoDescuento']) ? floatval($_POST['montoDescuento']) : 0;
            $tipoPago = isset($_POST['tipoPago']) ? htmlspecialchars(trim($_POST['tipoPago'])) : 'PLAN REGULAR';

            $estudianteID = (int)$_POST['idcliente'];

            // Campos de facturación: si el formulario no los envía (el pre-registro no los pide),
            // se derivan de los datos del propio estudiante.
            $nombreFactura = !empty($_POST['nombreFactura']) ? htmlspecialchars(trim($_POST['nombreFactura'])) : '';
            $nitCiFactura = !empty($_POST['nitCiFactura']) ? htmlspecialchars(trim($_POST['nitCiFactura'])) : '';

            if ($nombreFactura === '' || $nitCiFactura === '') {
                $pdo = Conexion::Conectar();
                $stmtEst = $pdo->prepare("SELECT Nombre, Apaterno, Amaterno, Ci, Complemento FROM estudiante WHERE EstudianteID = :id");
                $stmtEst->bindParam(':id', $estudianteID, PDO::PARAM_INT);
                $stmtEst->execute();
                $est = $stmtEst->fetch(PDO::FETCH_ASSOC);

                if ($est) {
                    if ($nombreFactura === '') {
                        $nombreFactura = trim($est['Apaterno'] . ' ' . $est['Amaterno'] . ' ' . $est['Nombre']);
                    }
                    if ($nitCiFactura === '') {
                        $nitCiFactura = $est['Ci'] . (!empty($est['Complemento']) ? '-' . $est['Complemento'] : '');
                    }
                }
            }

            $responsable = isset($_POST['responsable']) ? htmlspecialchars(trim($_POST['responsable'])) : '';
            $firma = isset($_POST['firma']) ? htmlspecialchars(trim($_POST['firma'])) : '';

            // Preparar datos para insertar
            $datosOrdenPago = array(
                "EstudianteID" => $estudianteID,
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
                "Firma" => $firma,
                "Observaciones" => $tipoPago
            );

            // Registrar en la base de datos
            $resultado = OrdenPagoModelos::RegistrarPreregistroModelo($datosOrdenPago);

            if ($resultado['status'] == 'exitoso') {
                $idOrdenPago = $resultado['idOrdenPago'];

                echo '
                <script>
                window.addEventListener("load", function() {
                    swal("¡Pre-registro exitoso!", "La orden de pago de matrícula se generó correctamente.", "success")
                    .then(function () {
                        window.location.href = "orden-generada?id=' . $idOrdenPago . '";
                    });
                });
                </script>';
            } elseif ($resultado['status'] == 'duplicado') {
                echo '
                <script>
                window.addEventListener("load", function() {
                    swal("ERROR!", "' . addslashes($resultado['mensaje']) . '", "error")
                    .then(function () {
                        location.href="' . $paginaRedirect . '";
                    });
                });
                </script>';
            } else {
                echo '
                <script>
                window.addEventListener("load", function() {
                    swal("ERROR!", "No se pudo registrar la orden de pago: ' . addslashes($resultado['mensaje']) . '", "error")
                    .then(function () {
                        location.href="' . $paginaRedirect . '";
                    });
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
?>
