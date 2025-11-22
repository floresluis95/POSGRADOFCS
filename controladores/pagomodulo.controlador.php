<?php
/**
 * Controlador de Pago de Módulos
 * Gestiona el registro y consulta de pagos de módulos
 */

require_once __DIR__ . '/../modelos/pagomodulo.modelo.php';
require_once __DIR__ . '/../modelos/conexion.modelo.php';

class PagoModuloControlador
{
    /**
     * Registrar pago de módulo
     */
    public function RegistrarPagoModuloControlador()
    {
        error_log("=== RegistrarPagoModuloControlador ejecutado ===");
        error_log("POST data: " . print_r($_POST, true));
        error_log("FILES data: " . print_r($_FILES, true));

        if (isset($_POST["registrarPagoModulo"])) {
            error_log("POST registrarPagoModulo detectado");

            // Validar datos requeridos
            if (empty($_POST['idinscripcion']) || empty($_POST['moduloSeleccionado']) ||
                empty($_POST['costoModulo']) || empty($_POST['fechaPago'])) {

                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "Todos los campos obligatorios deben ser completados", "error")
                .then(function () {
                    location.href="matriculados";
                });
                </script>';
                return;
            }

            // Procesar archivo de voucher (si existe)
            $archivoVoucher = null;
            if (isset($_FILES['fmodulo']) && $_FILES['fmodulo']['error'] == 0) {
                $archivoVoucher = file_get_contents($_FILES['fmodulo']['tmp_name']);
            }

            // Preparar datos para insertar
            $datosPago = array(
                "idinscripcion" => (int)$_POST['idinscripcion'],
                "nmodulo" => htmlspecialchars(trim($_POST['moduloSeleccionado'])),
                "costomodulo" => floatval($_POST['costoModulo']),
                "fechapago" => htmlspecialchars(trim($_POST['fechaPago'])),
                "nvaucher" => htmlspecialchars(trim($_POST['numeroVaucher'] ?? '')),
                "fmodulo" => $archivoVoucher
            );

            error_log("Datos a registrar: " . print_r($datosPago, true));

            // Registrar en la base de datos
            $resultado = PagoModuloModelo::RegistrarPagoModuloModelo($datosPago);

            if ($resultado == 'exitoso') {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("EXITOSO!", "El pago del módulo se registró correctamente", "success")
                .then(function () {
                    location.href="matriculados";
                });
                </script>';
            } elseif ($resultado == 'duplicado') {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "Este módulo ya fue registrado para esta inscripción", "error")
                .then(function () {
                    location.href="matriculados";
                });
                </script>';
            } else {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "No se pudo registrar el pago del módulo", "error")
                .then(function () {
                    location.href="matriculados";
                });
                </script>';
            }
        }
    }

    /**
     * Listar pagos de módulos en una tabla HTML
     */
    public function ListarPagosModulosControlador()
    {
        if (isset($_GET['idinscripcion'])) {
            $idinscripcion = (int)$_GET['idinscripcion'];
            $pagos = PagoModuloModelo::ObtenerPagosModulosPorInscripcionModelo($idinscripcion);

            if (empty($pagos)) {
                echo '<tr>
                        <td colspan="6" class="text-center">No hay pagos de módulos registrados</td>
                      </tr>';
                return;
            }

            foreach ($pagos as $key => $pago) {
                $fechaFormateada = date('d/m/Y', strtotime($pago['fechapago']));
                $estadoBadge = $pago['Estado'] == 'PAGADO' ? 'badge-success' : 'badge-warning';

                echo '<tr>
                        <td class="text-center"><strong>' . ($key + 1) . '</strong></td>
                        <td>' . $pago['nmodulo'] . '</td>
                        <td class="text-center"><strong>Bs. ' . number_format($pago['costomodulo'], 2) . '</strong></td>
                        <td class="text-center">' . $pago['nvaucher'] . '</td>
                        <td class="text-center">' . $fechaFormateada . '</td>
                        <td class="text-center">
                            <span class="badge ' . $estadoBadge . '">' . $pago['Estado'] . '</span>
                        </td>
                      </tr>';
            }
        }
    }
}
?>
