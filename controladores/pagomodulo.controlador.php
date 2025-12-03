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
     * Registrar pago de uno o múltiples módulos
     */
    public function RegistrarPagoModuloControlador()
    {
        error_log("=== RegistrarPagoModuloControlador ejecutado ===");
        error_log("POST data: " . print_r($_POST, true));
        error_log("FILES data: " . print_r($_FILES, true));

        if (isset($_POST["registrarPagoModulo"])) {
            error_log("POST registrarPagoModulo detectado");

            // Validar datos requeridos
            if (empty($_POST['idinscripcion']) || empty($_POST['fechaPago']) ||
                (empty($_POST['costos']) || !is_array($_POST['costos']))) {

                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "Debe seleccionar al menos un módulo e ingresar todos los costos", "error")
                .then(function () {
                    location.href="matriculados";
                });
                </script>';
                return;
            }

            $idinscripcion = (int)$_POST['idinscripcion'];
            $fechaPago = htmlspecialchars(trim($_POST['fechaPago']));
            $numeroVaucher = htmlspecialchars(trim($_POST['numeroVaucher'] ?? ''));
            $costosModulos = $_POST['costos']; // Array: [IdModulo => costo]

            // Procesar archivo de voucher (si existe) - mismo para todos
            $archivoVoucher = null;
            if (isset($_FILES['fmodulo']) && $_FILES['fmodulo']['error'] == 0) {
                $archivoVoucher = file_get_contents($_FILES['fmodulo']['tmp_name']);
            }

            // Registrar cada módulo
            $exitosos = 0;
            $errores = 0;
            $duplicados = 0;
            $modulosProcesados = array();

            foreach ($costosModulos as $idModulo => $costo) {
                // Validar costo
                if (empty($costo) || floatval($costo) <= 0) {
                    $errores++;
                    continue;
                }

                // Preparar datos para insertar
                $datosPago = array(
                    "idinscripcion" => $idinscripcion,
                    "IdModulo" => (int)$idModulo,
                    "costomodulo" => floatval($costo),
                    "fechapago" => $fechaPago,
                    "nvaucher" => $numeroVaucher,
                    "fmodulo" => $archivoVoucher
                );

                error_log("Registrando módulo ID {$idModulo}: " . print_r($datosPago, true));

                // Registrar en la base de datos
                $resultado = PagoModuloModelo::RegistrarPagoModuloModelo($datosPago);

                if ($resultado == 'exitoso') {
                    $exitosos++;
                    $modulosProcesados[] = $idModulo;
                } elseif ($resultado == 'duplicado') {
                    $duplicados++;
                } else {
                    $errores++;
                }
            }

            // Mensajes según resultado
            $totalProcesados = count($costosModulos);

            if ($exitosos == $totalProcesados) {
                // Todos exitosos
                $mensaje = $exitosos == 1
                    ? "El pago del módulo se registró correctamente"
                    : "Los pagos de {$exitosos} módulos se registraron correctamente";

                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("EXITOSO!", "' . $mensaje . '\\n\\n¿Desea ver el recibo de pago?", "success", {
                    buttons: {
                        cancel: "No, volver",
                        confirm: "Sí, ver recibo"
                    }
                })
                .then(function (value) {
                    if (value) {
                        window.open("vistas/componentes/recibo-pago-modulos.php?idinscripcion=' . $idinscripcion . '", "_blank");
                    }
                    location.href="matriculados";
                });
                </script>';
            } elseif ($exitosos > 0) {
                // Algunos exitosos, algunos con errores
                $mensaje = "Se registraron {$exitosos} de {$totalProcesados} módulos.";
                if ($duplicados > 0) $mensaje .= " {$duplicados} ya estaban registrados.";
                if ($errores > 0) $mensaje .= " {$errores} tuvieron errores.";

                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("PARCIAL!", "' . $mensaje . '", "warning")
                .then(function () {
                    location.href="matriculados";
                });
                </script>';
            } elseif ($duplicados > 0 && $errores == 0) {
                // Todos eran duplicados
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "Todos los módulos seleccionados ya fueron registrados", "error")
                .then(function () {
                    location.href="matriculados";
                });
                </script>';
            } else {
                // Todos fallaron
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "No se pudo registrar ningún pago", "error")
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
     * Obtener detalle de módulos (pagados y pendientes) del estudiante por programa
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
?>
