<?php
/**
 * RECIBO DE PAGO DE MÓDULOS
 * Genera un recibo imprimible con el detalle de pagos realizados y cuotas pendientes
 */

session_start();
if (!isset($_SESSION['Validar']) || $_SESSION['Validar'] !== true) {
    die('Acceso denegado');
}

// Obtener parámetros
$idinscripcion = isset($_GET['idinscripcion']) ? intval($_GET['idinscripcion']) : 0;

if ($idinscripcion === 0) {
    die('ID de inscripción inválido');
}

// Incluir modelos
require_once '../../modelos/conexion.modelo.php';
require_once '../../modelos/pagomodulo.modelo.php';

date_default_timezone_set("America/La_Paz");
$fechaGeneracion = date('d/m/Y H:i:s');
$numeroRecibo = 'REC-' . str_pad($idinscripcion, 6, '0', STR_PAD_LEFT) . '-' . date('Ymd');

try {
    $pdo = Conexion::Conectar();

    // Obtener datos del estudiante e inscripción
    $stmtEst = $pdo->prepare("
        SELECT
            e.EstudianteID,
            e.Nombre,
            e.Apaterno,
            e.Amaterno,
            e.Ci,
            e.Complemento,
            e.Exp,
            ep.idInscripcion,
            ep.ProgramaID,
            ep.FechaInscripcion,
            ep.nvauchermatricula as NumeroVaucher,
            p.NombrePrograma,
            p.Codigo as CodigoPrograma,
            p.GradoAcademico,
            p.CostoMatricula
        FROM estudianteprograma ep
        INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
        INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
        WHERE ep.idInscripcion = :idinscripcion
    ");
    $stmtEst->bindParam(':idinscripcion', $idinscripcion, PDO::PARAM_INT);
    $stmtEst->execute();
    $estudiante = $stmtEst->fetch(PDO::FETCH_ASSOC);

    if (!$estudiante) {
        die('Inscripción no encontrada');
    }

    $nombreCompleto = $estudiante['Nombre'] . ' ' . $estudiante['Apaterno'] . ' ' . $estudiante['Amaterno'];
    $ciCompleto = $estudiante['Ci'];
    if (!empty($estudiante['Complemento'])) {
        $ciCompleto .= '-' . $estudiante['Complemento'];
    }
    $ciCompleto .= ' ' . $estudiante['Exp'];

    // Obtener módulos con estado de pago
    $modulos = PagoModuloModelo::ObtenerModulosConEstadoPagoModelo($estudiante['ProgramaID'], $idinscripcion);

    // Separar módulos pagados y pendientes
    $modulosPagados = array_filter($modulos, function($m) { return $m['Pagado'] == 1; });
    $modulosPendientes = array_filter($modulos, function($m) { return $m['Pagado'] == 0; });

    // Calcular totales
    $totalPagado = array_sum(array_column($modulosPagados, 'CostoPagado'));
    $totalPendiente = array_sum(array_column($modulosPendientes, 'Costo'));
    $totalPrograma = floatval($estudiante['CostoMatricula']);

} catch (PDOException $e) {
    die('Error al obtener datos: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Pago - <?php echo $numeroRecibo; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #333;
            background: #fff;
            padding: 20px;
        }

        /* ENCABEZADO DEL RECIBO */
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
            position: relative;
        }

        .header h1 {
            color: #667eea;
            font-size: 24pt;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .header h2 {
            color: #764ba2;
            font-size: 16pt;
            margin-bottom: 10px;
        }

        .numero-recibo {
            position: absolute;
            top: 0;
            right: 0;
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 12pt;
        }

        /* INFORMACIÓN DEL ESTUDIANTE */
        .info-estudiante {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .info-estudiante table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-estudiante td {
            padding: 5px 10px;
            font-size: 10pt;
        }

        .info-estudiante td:first-child {
            font-weight: bold;
            width: 150px;
            color: #667eea;
        }

        /* SECCIÓN DE MÓDULOS PAGADOS */
        .seccion {
            margin-bottom: 30px;
        }

        .seccion-titulo {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 10px 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            font-size: 12pt;
            font-weight: bold;
        }

        .seccion-titulo.pendiente {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }

        /* TABLA */
        .tabla-modulos {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .tabla-modulos thead {
            background: #667eea;
            color: white;
        }

        .tabla-modulos thead th {
            padding: 10px 8px;
            text-align: center;
            font-size: 10pt;
            border: 1px solid #5867dd;
        }

        .tabla-modulos tbody td {
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 10pt;
        }

        .tabla-modulos tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .col-numero { width: 5%; text-align: center; }
        .col-codigo { width: 12%; text-align: center; }
        .col-modulo { width: 40%; }
        .col-costo { width: 15%; text-align: right; font-weight: bold; }
        .col-fecha { width: 15%; text-align: center; font-size: 9pt; }
        .col-voucher { width: 13%; text-align: center; font-size: 9pt; }

        /* RESUMEN DE PAGOS */
        .resumen {
            background: #f8f9fa;
            border: 2px solid #667eea;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }

        .resumen table {
            width: 100%;
            border-collapse: collapse;
        }

        .resumen tr {
            border-bottom: 1px solid #ddd;
        }

        .resumen tr:last-child {
            border-bottom: none;
            font-size: 14pt;
            font-weight: bold;
            color: #667eea;
        }

        .resumen td {
            padding: 10px;
        }

        .resumen td:last-child {
            text-align: right;
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-pagado {
            background-color: #28a745;
            color: white;
        }

        .badge-pendiente {
            background-color: #dc3545;
            color: white;
        }

        /* PIE DE PÁGINA */
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            font-size: 9pt;
            color: #666;
            text-align: center;
        }

        /* BOTÓN DE IMPRESIÓN */
        .btn-imprimir {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 12pt;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            z-index: 1000;
        }

        .btn-imprimir:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            @page {
                size: letter;
                margin: 1.5cm;
            }
        }

        .alerta-pendientes {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .alerta-pendientes h4 {
            color: #856404;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <!-- BOTÓN DE IMPRESIÓN -->
    <button onclick="window.print()" class="btn-imprimir no-print">
        🖨️ Imprimir Recibo
    </button>

    <!-- ENCABEZADO -->
    <div class="header">
        <div class="numero-recibo">N° <?php echo $numeroRecibo; ?></div>
        <h1>RECIBO DE PAGO</h1>
        <h2>Facultad de Ciencias Sociales</h2>
        <p>Fecha de Emisión: <?php echo $fechaGeneracion; ?></p>
    </div>

    <!-- INFORMACIÓN DEL ESTUDIANTE -->
    <div class="info-estudiante">
        <table>
            <tr>
                <td>Estudiante:</td>
                <td><strong><?php echo htmlspecialchars($nombreCompleto); ?></strong></td>
                <td>C.I.:</td>
                <td><strong><?php echo htmlspecialchars($ciCompleto); ?></strong></td>
            </tr>
            <tr>
                <td>Programa:</td>
                <td colspan="3"><strong><?php echo htmlspecialchars($estudiante['NombrePrograma']); ?></strong></td>
            </tr>
            <tr>
                <td>Grado Académico:</td>
                <td><?php echo htmlspecialchars($estudiante['GradoAcademico']); ?></td>
                <td>Código:</td>
                <td><?php echo htmlspecialchars($estudiante['CodigoPrograma']); ?></td>
            </tr>
        </table>
    </div>

    <!-- MÓDULOS PAGADOS -->
    <?php if (count($modulosPagados) > 0): ?>
    <div class="seccion">
        <div class="seccion-titulo">
            ✓ MÓDULOS PAGADOS (<?php echo count($modulosPagados); ?>)
        </div>
        <table class="tabla-modulos">
            <thead>
                <tr>
                    <th class="col-numero">#</th>
                    <th class="col-codigo">Código</th>
                    <th class="col-modulo">Módulo</th>
                    <th class="col-costo">Monto Pagado</th>
                    <th class="col-fecha">Fecha Pago</th>
                    <th class="col-voucher">N° Voucher</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($modulosPagados as $index => $mod): ?>
                <tr>
                    <td class="col-numero"><?php echo $index + 1; ?></td>
                    <td class="col-codigo"><?php echo htmlspecialchars($mod['Codigo']); ?></td>
                    <td class="col-modulo"><?php echo htmlspecialchars($mod['NombreModulo']); ?></td>
                    <td class="col-costo">Bs. <?php echo number_format($mod['CostoPagado'], 2); ?></td>
                    <td class="col-fecha"><?php echo $mod['FechaPago'] ? date('d/m/Y', strtotime($mod['FechaPago'])) : '-'; ?></td>
                    <td class="col-voucher"><?php echo htmlspecialchars($mod['NumeroVaucher'] ?: '-'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- MÓDULOS PENDIENTES -->
    <?php if (count($modulosPendientes) > 0): ?>
    <div class="seccion">
        <div class="seccion-titulo pendiente">
            ⚠ CUOTAS PENDIENTES (<?php echo count($modulosPendientes); ?>)
        </div>
        <table class="tabla-modulos">
            <thead>
                <tr>
                    <th class="col-numero">#</th>
                    <th class="col-codigo">Código</th>
                    <th class="col-modulo">Módulo</th>
                    <th class="col-costo">Monto a Pagar</th>
                    <th colspan="2" class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($modulosPendientes as $index => $mod): ?>
                <tr>
                    <td class="col-numero"><?php echo $index + 1; ?></td>
                    <td class="col-codigo"><?php echo htmlspecialchars($mod['Codigo']); ?></td>
                    <td class="col-modulo"><?php echo htmlspecialchars($mod['NombreModulo']); ?></td>
                    <td class="col-costo">Bs. <?php echo number_format($mod['Costo'], 2); ?></td>
                    <td colspan="2" class="text-center">
                        <span class="badge badge-pendiente">PENDIENTE</span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="alerta-pendientes">
            <h4>⚠️ IMPORTANTE</h4>
            <p>
                El estudiante tiene <strong><?php echo count($modulosPendientes); ?> módulo(s) pendiente(s) de pago</strong>
                por un total de <strong>Bs. <?php echo number_format($totalPendiente, 2); ?></strong>.
                Debe regularizar su situación para poder acceder a estos módulos.
            </p>
        </div>
    </div>
    <?php endif; ?>

    <!-- RESUMEN DE PAGOS -->
    <div class="resumen">
        <table>
            <tr>
                <td>Costo Total del Programa:</td>
                <td>Bs. <?php echo number_format($totalPrograma, 2); ?></td>
            </tr>
            <tr>
                <td>Total Módulos Pagados (<?php echo count($modulosPagados); ?>):</td>
                <td style="color: #28a745;">Bs. <?php echo number_format($totalPagado, 2); ?></td>
            </tr>
            <tr>
                <td>Total Módulos Pendientes (<?php echo count($modulosPendientes); ?>):</td>
                <td style="color: #dc3545;">Bs. <?php echo number_format($totalPendiente, 2); ?></td>
            </tr>
            <tr style="background: #e9ecef;">
                <td>SALDO:</td>
                <td><?php
                    $saldo = $totalPrograma - $totalPagado;
                    echo ($saldo > 0 ? 'Bs. ' . number_format($saldo, 2) : 'CANCELADO');
                ?></td>
            </tr>
        </table>
    </div>

    <!-- PIE DE PÁGINA -->
    <div class="footer">
        <p><strong>Este recibo es válido como comprobante de pagos realizados</strong></p>
        <p style="margin-top: 10px;">
            Generado automáticamente por el Sistema de Gestión Académica<br>
            Para consultas, acercarse a la oficina de tesorería
        </p>
        <p style="margin-top: 20px; font-size: 8pt; color: #999;">
            Recibo generado el <?php echo $fechaGeneracion; ?> | Usuario: <?php echo htmlspecialchars($_SESSION['Usuario']); ?>
        </p>
    </div>

</body>
</html>
