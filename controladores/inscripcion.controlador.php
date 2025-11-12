<?php
/**
 * Controlador de Inscripción
 * Gestiona inscripciones, planes de pago y vouchers
 * Compatible con PHP 8 - MVC
 */

// Incluir modelos necesarios
require_once __DIR__ . '/../modelos/inscripcion.modelo.php';
require_once __DIR__ . '/../modelos/conexion.modelo.php';

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class InscripcionControladores
{
    /**
     * Procesar solicitud AJAX para obtener programas por grado académico
     */
    public function ObtenerProgramasPorGradoControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            if (!isset($_POST['gradoAcademico']) || empty($_POST['gradoAcademico'])) {
                echo json_encode(['success' => false, 'message' => 'Grado académico no proporcionado'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $gradoAcademico = $_POST['gradoAcademico'];
            $programas = InscripcionModelos::ListarProgramasPorGradoModelo($gradoAcademico);

            if ($programas && count($programas) > 0) {
                echo json_encode(['success' => true, 'data' => $programas], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se encontraron programas'], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $e) {
            error_log("Error en ObtenerProgramasPorGradoControlador: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * Procesar solicitud AJAX para obtener detalles de un programa
     */
    public function ObtenerDetalleProgramaControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            if (!isset($_POST['programaID']) || empty($_POST['programaID'])) {
                echo json_encode(['success' => false, 'message' => 'ID de programa no proporcionado'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $programaID = intval($_POST['programaID']);
            $programa = InscripcionModelos::ObtenerDetalleProgramaModelo($programaID);

            if ($programa) {
                echo json_encode(['success' => true, 'data' => $programa], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(['success' => false, 'message' => 'Programa no encontrado'], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $e) {
            error_log("Error en ObtenerDetalleProgramaControlador: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * Registrar nueva inscripción con plan de pagos
     */
    public function RegistrarInscripcionControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            // Validar datos requeridos
            $camposRequeridos = ['estudianteID', 'programaID', 'pagoInicial', 'montoModulos', 'cantidadModulos'];
            foreach ($camposRequeridos as $campo) {
                if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                    echo json_encode(['success' => false, 'message' => "Campo {$campo} es requerido"], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }

            // 1. Registrar inscripción
            $datosInscripcion = [
                'EstudianteID' => intval($_POST['estudianteID']),
                'ProgramaID' => intval($_POST['programaID']),
                'FechaInscripcion' => date('Y-m-d')
            ];

            $idInscripcion = InscripcionModelos::RegistrarInscripcionModelo($datosInscripcion);

            if (!$idInscripcion) {
                echo json_encode(['success' => false, 'message' => 'Error al registrar inscripción. Verifique que el estudiante no esté ya inscrito.'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // 2. Obtener datos del programa para el plan de pagos
            $programa = InscripcionModelos::ObtenerDetalleProgramaModelo(intval($_POST['programaID']));

            if (!$programa) {
                echo json_encode(['success' => false, 'message' => 'Programa no encontrado'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // 3. Calcular costo por módulo
            $pagoInicial = floatval($_POST['pagoInicial']);
            $montoModulos = floatval($_POST['montoModulos']);
            $cantidadModulos = intval($_POST['cantidadModulos']);
            $costoTotal = $pagoInicial + $montoModulos;
            $costoPorModulo = round($montoModulos / $cantidadModulos, 2);

            // 4. Crear plan de pagos
            $datosPlan = [
                'idInscripcion' => $idInscripcion,
                'CostoTotal' => $costoTotal,
                'MontoPagoInicial' => $pagoInicial,
                'MontoModulos' => $montoModulos,
                'CantidadModulos' => $cantidadModulos,
                'CostoPorModulo' => $costoPorModulo,
                'FechaInicio' => $programa['FechaInicio']
            ];

            $planPagoID = InscripcionModelos::CrearPlanPagosModelo($datosPlan);

            if (!$planPagoID) {
                echo json_encode(['success' => false, 'message' => 'Error al crear plan de pagos'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            echo json_encode([
                'success' => true,
                'message' => 'Inscripción registrada exitosamente',
                'data' => [
                    'idInscripcion' => $idInscripcion,
                    'planPagoID' => $planPagoID
                ]
            ], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            error_log("Error en RegistrarInscripcionControlador: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * Registrar voucher de pago
     */
    public function RegistrarVoucherControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            // Validar datos requeridos
            if (!isset($_POST['cuotaID']) || !isset($_POST['montoPago']) || !isset($_POST['metodoPago'])) {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // Generar código de voucher
            $codigoVoucher = InscripcionModelos::GenerarCodigoVoucherModelo();

            // Procesar archivo si se subió
            $archivoVoucher = null;
            if (isset($_FILES['archivoVoucher']) && $_FILES['archivoVoucher']['error'] == 0) {
                $uploadDir = __DIR__ . '/../vistas/recursos/vouchers/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $extension = pathinfo($_FILES['archivoVoucher']['name'], PATHINFO_EXTENSION);
                $nombreArchivo = $codigoVoucher . '.' . $extension;
                $rutaArchivo = $uploadDir . $nombreArchivo;

                if (move_uploaded_file($_FILES['archivoVoucher']['tmp_name'], $rutaArchivo)) {
                    $archivoVoucher = 'vistas/recursos/vouchers/' . $nombreArchivo;
                }
            }

            $datosVoucher = [
                'CuotaID' => intval($_POST['cuotaID']),
                'CodigoVoucher' => $codigoVoucher,
                'MontoPago' => floatval($_POST['montoPago']),
                'FechaPago' => isset($_POST['fechaPago']) ? $_POST['fechaPago'] : date('Y-m-d H:i:s'),
                'MetodoPago' => $_POST['metodoPago'],
                'NumeroTransaccion' => isset($_POST['numeroTransaccion']) ? $_POST['numeroTransaccion'] : null,
                'ArchivoVoucher' => $archivoVoucher,
                'RegistradoPor' => isset($_SESSION['IdPersonal']) ? $_SESSION['IdPersonal'] : null,
                'Observaciones' => isset($_POST['observaciones']) ? $_POST['observaciones'] : null
            ];

            $voucherID = InscripcionModelos::RegistrarVoucherModelo($datosVoucher);

            if ($voucherID) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Voucher registrado exitosamente',
                    'data' => [
                        'voucherID' => $voucherID,
                        'codigoVoucher' => $codigoVoucher
                    ]
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al registrar voucher'], JSON_UNESCAPED_UNICODE);
            }

        } catch (Exception $e) {
            error_log("Error en RegistrarVoucherControlador: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * Obtener cuotas de un plan de pagos
     */
    public function ObtenerCuotasPlanControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            if (!isset($_POST['planPagoID'])) {
                echo json_encode(['success' => false, 'message' => 'Plan de pago no especificado'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $planPagoID = intval($_POST['planPagoID']);
            $cuotas = InscripcionModelos::ObtenerCuotasPlanModelo($planPagoID);

            if ($cuotas !== false) {
                echo json_encode(['success' => true, 'data' => $cuotas], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al obtener cuotas'], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $e) {
            error_log("Error en ObtenerCuotasPlanControlador: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * Obtener estado de pagos de un estudiante
     */
    public function ObtenerEstadoPagosEstudianteControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            if (!isset($_POST['estudianteID'])) {
                echo json_encode(['success' => false, 'message' => 'Estudiante no especificado'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $estudianteID = intval($_POST['estudianteID']);
            $estadoPagos = InscripcionModelos::ObtenerEstadoPagosEstudianteModelo($estudianteID);

            if ($estadoPagos !== false) {
                echo json_encode(['success' => true, 'data' => $estadoPagos], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al obtener estado de pagos'], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $e) {
            error_log("Error en ObtenerEstadoPagosEstudianteControlador: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
}

// Procesar solicitudes AJAX
if (isset($_POST['action'])) {
    $controlador = new InscripcionControladores();

    switch ($_POST['action']) {
        case 'obtenerProgramas':
            $controlador->ObtenerProgramasPorGradoControlador();
            break;
        case 'obtenerDetallePrograma':
            $controlador->ObtenerDetalleProgramaControlador();
            break;
        case 'registrarInscripcion':
            $controlador->RegistrarInscripcionControlador();
            break;
        case 'registrarVoucher':
            $controlador->RegistrarVoucherControlador();
            break;
        case 'obtenerCuotasPlan':
            $controlador->ObtenerCuotasPlanControlador();
            break;
        case 'obtenerEstadoPagos':
            $controlador->ObtenerEstadoPagosEstudianteControlador();
            break;
    }
}
