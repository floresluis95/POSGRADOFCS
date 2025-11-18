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
            // Log de depuración
            error_log("ObtenerProgramasPorGradoControlador - POST recibido: " . print_r($_POST, true));

            if (!isset($_POST['gradoAcademico']) || empty($_POST['gradoAcademico'])) {
                error_log("ObtenerProgramasPorGradoControlador - ERROR: gradoAcademico no proporcionado");
                echo json_encode([
                    'success' => false,
                    'message' => 'Grado académico no proporcionado',
                    'debug' => $_POST
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $gradoAcademico = trim($_POST['gradoAcademico']);
            error_log("ObtenerProgramasPorGradoControlador - Buscando programas para: " . $gradoAcademico);

            // Validar que el grado académico sea válido
            $gradosValidos = ['DIPLOMADO', 'MAESTRIA', 'ESPECIALIDAD', 'MAESTRÍA'];
            if (!in_array($gradoAcademico, $gradosValidos)) {
                error_log("ObtenerProgramasPorGradoControlador - ERROR: Grado académico no válido: " . $gradoAcademico);
                echo json_encode([
                    'success' => false,
                    'message' => 'Grado académico no válido: ' . $gradoAcademico,
                    'gradosValidos' => $gradosValidos
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $programas = InscripcionModelos::ListarProgramasPorGradoModelo($gradoAcademico);
            error_log("ObtenerProgramasPorGradoControlador - Programas encontrados: " . count($programas));

            if ($programas && count($programas) > 0) {
                echo json_encode([
                    'success' => true,
                    'data' => $programas,
                    'count' => count($programas)
                ], JSON_UNESCAPED_UNICODE);
            } else {
                error_log("ObtenerProgramasPorGradoControlador - No se encontraron programas");
                echo json_encode([
                    'success' => false,
                    'message' => 'No se encontraron programas para: ' . $gradoAcademico,
                    'gradoAcademico' => $gradoAcademico
                ], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $e) {
            error_log("Error en ObtenerProgramasPorGradoControlador: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener programas: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], JSON_UNESCAPED_UNICODE);
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
                echo json_encode([
                    'success' => false,
                    'message' => 'ID de programa no proporcionado'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $programaID = intval($_POST['programaID']);

            if ($programaID <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID de programa no válido'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $programa = InscripcionModelos::ObtenerDetalleProgramaModelo($programaID);

            if ($programa) {
                echo json_encode([
                    'success' => true,
                    'data' => $programa
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Programa no encontrado o inactivo'
                ], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $e) {
            error_log("Error en ObtenerDetalleProgramaControlador: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener detalles: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
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
                    echo json_encode([
                        'success' => false,
                        'message' => "Campo '{$campo}' es requerido"
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }

            // Validar tipos de datos
            $estudianteID = intval($_POST['estudianteID']);
            $programaID = intval($_POST['programaID']);
            $pagoInicial = floatval($_POST['pagoInicial']);
            $montoModulos = floatval($_POST['montoModulos']);
            $cantidadModulos = intval($_POST['cantidadModulos']);

            // Validaciones adicionales
            if ($estudianteID <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Debe seleccionar un estudiante válido'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            if ($programaID <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Debe seleccionar un programa válido'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            if ($pagoInicial < 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'El pago inicial no puede ser negativo'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            if ($cantidadModulos <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'La cantidad de módulos debe ser mayor a cero'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // 1. Registrar inscripción en tabla estudianteprograma
            $datosInscripcion = [
                'EstudianteID' => $estudianteID,
                'ProgramaID' => $programaID,
                'FechaInscripcion' => date('Y-m-d')
            ];

            $idInscripcion = InscripcionModelos::RegistrarInscripcionModelo($datosInscripcion);

            if (!$idInscripcion) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al registrar inscripción. Verifique que el estudiante no esté ya inscrito en este programa.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // 2. Obtener datos del programa para el plan de pagos
            $programa = InscripcionModelos::ObtenerDetalleProgramaModelo($programaID);

            if (!$programa) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se pudo obtener información del programa'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // 3. Calcular costo por módulo
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
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al crear plan de pagos'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // Respuesta exitosa
            echo json_encode([
                'success' => true,
                'message' => 'Inscripción registrada exitosamente',
                'data' => [
                    'idInscripcion' => $idInscripcion,
                    'planPagoID' => $planPagoID,
                    'estudianteID' => $estudianteID,
                    'programaID' => $programaID,
                    'fechaInscripcion' => date('Y-m-d')
                ]
            ], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            error_log("Error en RegistrarInscripcionControlador: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al procesar inscripción: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
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
                echo json_encode([
                    'success' => false,
                    'message' => 'Datos incompletos para registrar el voucher'
                ], JSON_UNESCAPED_UNICODE);
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
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al registrar voucher'
                ], JSON_UNESCAPED_UNICODE);
            }

        } catch (Exception $e) {
            error_log("Error en RegistrarVoucherControlador: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al registrar voucher: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
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
                echo json_encode([
                    'success' => false,
                    'message' => 'Plan de pago no especificado'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $planPagoID = intval($_POST['planPagoID']);
            $cuotas = InscripcionModelos::ObtenerCuotasPlanModelo($planPagoID);

            if ($cuotas !== false) {
                echo json_encode([
                    'success' => true,
                    'data' => $cuotas
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al obtener cuotas'
                ], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $e) {
            error_log("Error en ObtenerCuotasPlanControlador: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener cuotas: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
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
                echo json_encode([
                    'success' => false,
                    'message' => 'Estudiante no especificado'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $estudianteID = intval($_POST['estudianteID']);
            $estadoPagos = InscripcionModelos::ObtenerEstadoPagosEstudianteModelo($estudianteID);

            if ($estadoPagos !== false) {
                echo json_encode([
                    'success' => true,
                    'data' => $estadoPagos
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al obtener estado de pagos'
                ], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $e) {
            error_log("Error en ObtenerEstadoPagosEstudianteControlador: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener estado: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * Listar todas las inscripciones
     */
    public function ListarInscripcionesControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $inscripciones = InscripcionModelos::ListarInscripcionesModelo();

            echo json_encode([
                'success' => true,
                'data' => $inscripciones
            ], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            error_log("Error en ListarInscripcionesControlador: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al listar inscripciones: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
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
        case 'listarInscripciones':
            $controlador->ListarInscripcionesControlador();
            break;
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Acción no válida'
            ], JSON_UNESCAPED_UNICODE);
            break;
    }
}
