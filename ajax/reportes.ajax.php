<?php
require_once __DIR__ . '/control-solicitud.php'; // corte de internet: sin envíos duplicados
require_once __DIR__ . '/validar-sesion.php';
ExigirSesionAjax(['ADM', 'SEC']); // Solo Administración y Secretaría (igual que su página)
/**
 * AJAX Handler para Reportes PDF
 * Procesa las solicitudes de generación de reportes
 */

require_once '../controladores/reportes.controlador.php';

// El contador en vivo de la pantalla llega por GET (solo lee): se trata igual que un POST
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET' && ($_GET['tipo_reporte'] ?? '') === 'contar_estudiantes') {
    $_POST = $_GET;
}

if (isset($_POST['tipo_reporte'])) {
    $tipoReporte = $_POST['tipo_reporte'];

    $programaID = isset($_POST['programa_id']) && !empty($_POST['programa_id'])
        ? intval($_POST['programa_id'])
        : null;
    $sede = isset($_POST['sede']) && !empty($_POST['sede'])
        ? trim($_POST['sede'])
        : null;

    // Fechas solo en formato AAAA-MM-DD; si las pusieron al revés se intercambian
    // en lugar de mostrar un reporte vacío
    $validarFecha = function ($campo) {
        $valor = trim($_POST[$campo] ?? '');
        $fecha = DateTime::createFromFormat('Y-m-d', $valor);
        return ($fecha && $fecha->format('Y-m-d') === $valor) ? $valor : null;
    };
    $fechaDesde = $validarFecha('fecha_desde');
    $fechaHasta = $validarFecha('fecha_hasta');
    if ($fechaDesde && $fechaHasta && $fechaDesde > $fechaHasta) {
        list($fechaDesde, $fechaHasta) = [$fechaHasta, $fechaDesde];
    }

    // Filtros de los reportes de cuentas (Movimientos e Ingresos por mes)
    $filtrosCuenta = [
        'sede' => $sede,
        'programaID' => $programaID,
        'numeroCuenta' => trim($_POST['numero_cuenta'] ?? '') ?: null,
        'concepto' => in_array($_POST['concepto'] ?? '', ['MATRICULA', 'CUOTAS', 'CLINICA'], true) ? $_POST['concepto'] : null,
        'desde' => $fechaDesde,
        'hasta' => $fechaHasta,
    ];

    // Filtros de la Lista general y del Reporte personalizado (una fila por inscripción)
    $filtrosEstudiantes = [
        'sede' => $sede,
        'programaID' => $programaID,
        'grado' => in_array($_POST['grado'] ?? '', ReportesModelos::ListaGrados(), true) ? $_POST['grado'] : null,
        'gestion' => in_array((int)($_POST['gestion'] ?? 0), ReportesModelos::ListaGestiones(), true) ? (int)$_POST['gestion'] : null,
        'situacion' => in_array($_POST['situacion'] ?? '', ['ACTIVO', 'ABANDONO', 'SIN_INSCRIPCION'], true) ? $_POST['situacion'] : null,
        'profesionID' => (int)($_POST['profesion_id'] ?? 0) ?: null,
        'universidadID' => (int)($_POST['universidad_id'] ?? 0) ?: null,
        'desde' => $fechaDesde,
        'hasta' => $fechaHasta,
        'orden' => ($_POST['orden'] ?? '') === 'programa' ? 'programa' : 'apellido',
    ];

    switch ($tipoReporte) {
        case 'contar_estudiantes':
            // Contador en vivo de la pantalla (JSON): cuántos saldrán en la Lista general / Personalizado
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(ReportesControladores::ContarEstudiantes($filtrosEstudiantes, !empty($_POST['solo_con_saldo'])));
            break;

        case 'estudiantes':
            // Lista general: una fila por inscripción, con programa, gestión y abandonos marcados
            ReportesControladores::GenerarReporteEstudiantes($filtrosEstudiantes);
            break;

        case 'personalizado':
            // Reporte personalizado: columnas, agrupación y orden elegidos en pantalla (sin cambios de BD)
            ReportesControladores::GenerarReportePersonalizado($filtrosEstudiantes, [
                'titulo' => $_POST['titulo'] ?? '',
                'columnas' => is_array($_POST['columnas'] ?? null) ? $_POST['columnas'] : [],
                'agrupar' => $_POST['agrupar'] ?? '',
                'orden' => $_POST['orden_personalizado'] ?? 'apellido',
                'orientacion' => $_POST['orientacion'] ?? '',
                'marcar' => !empty($_POST['marcar_abandonos']),
                'soloConSaldo' => !empty($_POST['solo_con_saldo']),
            ]);
            break;

        case 'por_programa':
            // Reporte de estudiantes por programa y/o sede
            ReportesControladores::GenerarReportePorPrograma($programaID, $sede);
            break;

        case 'modulos':
            // Reporte de módulos por programa y/o sede
            ReportesControladores::GenerarReporteModulos($programaID, $sede);
            break;

        case 'ejecutivo':
            // Reporte ejecutivo (KPIs para toma de decisiones) por sede y/o programa
            ReportesControladores::GenerarReporteEjecutivo($sede, $programaID);
            break;

        case 'recaudacion':
            // Recaudación por cuenta bancaria (las fechas filtran solo lo recaudado)
            ReportesControladores::GenerarReporteRecaudacion($sede, $programaID, $fechaDesde, $fechaHasta);
            break;

        case 'estado_cuenta':
            // Estado de cuenta de un estudiante por C.I. (sin complemento ni expedido: "1234567-1A LP" → "1234567")
            ReportesControladores::GenerarReporteEstadoCuenta(preg_replace('/\D/', '', explode('-', trim($_POST['ci'] ?? ''))[0]));
            break;

        case 'saldos':
            // Saldos por estudiante de cada programa
            ReportesControladores::GenerarReporteSaldos($sede, $programaID, !empty($_POST['solo_con_saldo']));
            break;

        case 'abandonos':
            // Abandonos: costo, pagado y sin cobrar, por sede, grado, programa y estudiante
            ReportesControladores::GenerarReporteAbandonos([
                'sede' => $sede,
                'programaID' => $programaID,
                'grado' => in_array($_POST['grado'] ?? '', ReportesModelos::ListaGrados(), true) ? $_POST['grado'] : null,
                'ci' => preg_replace('/\D/', '', explode('-', trim($_POST['ci'] ?? ''))[0]) ?: null,
            ]);
            break;

        case 'notas_programa':
            // Consolidado de notas (estudiantes × módulos) de un programa
            ReportesControladores::GenerarReporteNotasPrograma($programaID);
            break;

        case 'movimientos':
            // Cada pago recibido, con estudiante, voucher y cuenta
            ReportesControladores::GenerarReporteMovimientos($filtrosCuenta);
            break;

        case 'cuentas_programa':
            // Cuentas de cada programa: resumen y pagos de cada cuenta
            ReportesControladores::GenerarReporteCuentasPrograma($sede, $programaID, $fechaDesde, $fechaHasta);
            break;

        case 'ingresos_mes':
            // Lo recaudado mes a mes por concepto
            ReportesControladores::GenerarReporteIngresosMes($filtrosCuenta);
            break;

        default:
            echo '<script>
                swal("ERROR!", "Tipo de reporte no válido", "error")
                .then(function () { window.close(); });
            </script>';
            break;
    }
} else {
    echo '<script>
        swal("ERROR!", "No se especificó el tipo de reporte", "error")
        .then(function () { window.close(); });
    </script>';
}
?>
