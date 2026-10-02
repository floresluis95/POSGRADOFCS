<?php
if (session_status() === PHP_SESSION_NONE) session_start();
// Solo el personal (se abre desde Matriculados)
if (!isset($_SESSION['Validar']) || $_SESSION['Validar'] !== true || !in_array($_SESSION['Tipo'] ?? '', ['ADM', 'SEC', 'AUX'], true)) die('Acceso denegado');

$idinscripcion = isset($_GET['idinscripcion']) ? intval($_GET['idinscripcion']) : 0;
if ($idinscripcion === 0) die('ID de inscripción inválido');

require_once file_exists(__DIR__ . '/../../modelos/conexion.modelo.php') ? __DIR__ . '/../../modelos/conexion.modelo.php' : __DIR__ . '/../../core/conexion.modelo.php';
require_once '../../modelos/modulopagos_core.php';

date_default_timezone_set("America/La_Paz");
$fechaGeneracion = date('d/m/Y H:i:s');
$numeroRecibo = 'REC-' . str_pad($idinscripcion, 6, '0', STR_PAD_LEFT) . '-' . date('Ymd');

try {
    $pdo = Conexion::Conectar();
    $stmtEst = $pdo->prepare("
        SELECT e.EstudianteID,e.Nombre,e.Apaterno,e.Amaterno,e.Ci,e.Complemento,e.Exp,
               ep.idInscripcion,ep.ProgramaID,ep.FechaInscripcion,
               ep.costomatricula,
               ep.montoPagado,
               ep.montoDescuento,
               ep.porcentajeDescuento,
               ep.nvauchermatricula AS VoucherMatricula,
               p.NombrePrograma,p.Codigo AS CodigoPrograma,p.GradoAcademico,p.Costo AS CostoTotalPrograma,p.CostoMatricula,
               p.Version,p.NumeroTramite
        FROM estudianteprograma ep
        INNER JOIN estudiante e ON ep.EstudianteID=e.EstudianteID
        INNER JOIN programa p ON ep.ProgramaID=p.ProgramaID
        WHERE ep.idInscripcion=:idinscripcion
    ");
    $stmtEst->bindParam(':idinscripcion',$idinscripcion,PDO::PARAM_INT);
    $stmtEst->execute();
    $estudiante=$stmtEst->fetch(PDO::FETCH_ASSOC);
    if(!$estudiante) die('Inscripción no encontrada');

    $nombreCompleto=$estudiante['Nombre'].' '.$estudiante['Apaterno'].' '.$estudiante['Amaterno'];
    $ciCompleto=$estudiante['Ci'].(!empty($estudiante['Complemento'])?'-'.$estudiante['Complemento']:'').' '.$estudiante['Exp'];

    $costoMatriculaPagado = floatval($estudiante['costomatricula']);
    $montoDescuentoMatricula = floatval($estudiante['montoDescuento']);
    $porcentajeDescuentoMatricula = floatval($estudiante['porcentajeDescuento']);

    // Plan de pago del programa y sus cuotas (las cuotas ya no están ligadas a módulos:
    // su número y monto son libres en el Plan Regular, o una sola cuota por el total
    // en Contado/Grupal).
    $resumenPrograma = PagoModuloModelo::ObtenerResumenPagoProgramaModelo($idinscripcion, $estudiante['ProgramaID']);
    $cuotas = PagoModuloModelo::ObtenerCuotasPorInscripcionModelo($idinscripcion);
    $cuotasPagadas = array_filter($cuotas, function($c) { return $c['Estado'] === 'PAGADO'; });

} catch(PDOException $e){ die('Error al obtener datos: '.$e->getMessage()); }
?>
<!DOCTYPE html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recibo - <?php echo $numeroRecibo; ?></title>

<style>
*{margin:0;padding:0;box-sizing:border-box}
body{
    font-family:Arial,sans-serif;
    font-size:11pt;
    line-height:1.1;
    color:#333;
    background:#fff;
    padding:15px;
}

.header{display:flex;align-items:center;justify-content:space-between;padding:5px 0;border-bottom:2px solid #ccc}
.header-content{text-align:center;flex-grow:1;margin:0 10px;line-height:1.1}
.header-content h3,.header-content h4{margin:3px 0;line-height:1.1}
.header-content p{margin:2px 0;font-size:9pt}

.logo{width:80px}
.logo img{max-width:100%;max-height:65px}

/* Título del documento: va debajo del encabezado institucional (antes el N° de
   recibo estaba en position:absolute y quedaba encima del logo derecho). */
.titulo-recibo{text-align:center;margin:10px 0 0}
.titulo-recibo h2{font-size:15pt;color:#1e3c72;letter-spacing:1px;margin-bottom:4px}
.titulo-recibo .datos-recibo{font-size:9pt;color:#555}
.numero-recibo{
    display:inline-block;
    background:#1e3c72;color:#fff;padding:3px 10px;
    font-size:8.5pt;border-radius:4px;font-weight:bold;margin-right:8px;
    -webkit-print-color-adjust:exact;print-color-adjust:exact
}

.info-estudiante{
    background:#f8f9fa;border-left:4px solid #1e3c72;
    padding:10px;margin:15px 0;border-radius:5px
}
.info-estudiante table{width:100%;line-height:1.0}

.seccion{margin:15px 0}
.seccion-titulo{
    padding:6px 10px;border-radius:4px;font-size:11pt;
    margin-bottom:5px;font-weight:bold
}

.seccion-titulo.pendiente{background:#dc3545;color:white}
.seccion-titulo:not(.pendiente){background:#28a745;color:white}

.tabla-modulos{
    width:100%;border-collapse:collapse;margin-bottom:10px;
    line-height:1.0
}
.tabla-modulos th,.tabla-modulos td{
    border:1px solid #ddd;
    padding:5px;font-size:9.5pt
}
.tabla-modulos thead{background:#1e3c72;color:#fff}

.resumen{
    background:#f8f9fa;border:2px solid #1e3c72;
    border-radius:6px;padding:12px;margin-top:20px
}
.resumen table{width:100%;line-height:1.0}
.resumen td{padding:6px}

.footer{
    margin-top:25px;padding-top:10px;
    border-top:1px solid #ddd;text-align:center;
    font-size:8.5pt;line-height:1.1;color:#666
}

.btn-imprimir{
    position:fixed;bottom:15px;right:15px;
    background:#1e3c72;color:#fff;border:none;
    padding:10px 20px;border-radius:20px;
    font-size:11pt;cursor:pointer
}

@media print{
    .no-print{display:none}
    @page{margin:1cm}
}
</style>
</head>
<body>

<button onclick="window.print()" class="btn-imprimir no-print">🖨️ Imprimir</button>
<div class="header">
    <div class="logo"><img src="../../extensiones/imagenespdf/logouto.png"></div>
    <div class="header-content">
        <h3>UNIVERSIDAD TÉCNICA DE ORURO</h3>
        <h4>FACULTAD DE CIENCIAS DE LA SALUD</h4>
        <h4>COORDINACIÓN DE POSGRADO - ODONTOLOGÍA</h4>
        <p>Calle Presidente Montes entre Murguía y Aldana, Edificio UTO 2do Piso</p>
        <p>Telf: 5237317 - Fax: 5247110 - Oruro - Bolivia</p>
    </div>
    <div class="logo"><img src="../../extensiones/imagenespdf/logofcs.png"></div>
</div>

<div class="titulo-recibo">
    <h2>RECIBO DE PAGO</h2>
    <div class="datos-recibo">
        <span class="numero-recibo">N° <?php echo $numeroRecibo; ?></span>
        Emisión: <?php echo $fechaGeneracion; ?>
    </div>
</div>

<div class="info-estudiante">
<table>
<tr><td>Estudiante:</td><td><strong><?php echo $nombreCompleto; ?></strong></td>
<td>C.I.</td><td><strong><?php echo $ciCompleto; ?></strong></td></tr>
<tr><td>Programa:</td><td colspan="3"><strong><?php echo $estudiante['NombrePrograma']; ?></strong></td></tr>
<tr><td>Grado:</td><td><?php echo $estudiante['GradoAcademico']; ?></td>
<td>Código:</td><td><?php echo $estudiante['CodigoPrograma']; ?></td></tr>
</table></div>

<?php if($costoMatriculaPagado>0): ?>
<div class="seccion">
<div class="seccion-titulo" style="background:#1e3c72">✓ MATRÍCULA CANCELADA</div>
<table class="tabla-modulos"><thead>
<tr><th>#</th><th>Concepto</th><th>Monto</th><th>Fecha</th><th>Voucher</th></tr>
</thead><tbody>
<tr>
<td>1</td><td>Matrícula - <?php echo $estudiante['NombrePrograma']; ?></td>
<td>Bs. <?php echo number_format($costoMatriculaPagado,2); ?></td>
<td><?php echo date('d/m/Y',strtotime($estudiante['FechaInscripcion'])); ?></td>
<td><?php echo $estudiante['VoucherMatricula'] ?: '-'; ?></td>
</tr>
<?php if($montoDescuentoMatricula > 0): ?>
<tr style="background:#d4edda">
<td colspan="2"><strong>Descuento de matrícula aplicado (<?php echo number_format($porcentajeDescuentoMatricula,1); ?>%)</strong></td>
<td colspan="3" style="color:#28a745"><strong>- Bs. <?php echo number_format($montoDescuentoMatricula,2); ?></strong></td>
</tr>
<?php endif; ?>
</tbody></table></div>
<?php endif; ?>

<?php if($resumenPrograma['tienePlan']): ?>
<div class="seccion">
<div class="seccion-titulo" style="background:#3b3f5c">
    <?php
    $nombresPlan = ['REGULAR' => 'Plan Regular', 'DESCUENTO' => 'Plan al Contado', 'GRUPAL' => 'Plan Grupal'];
    echo $nombresPlan[$resumenPrograma['tipoPlan']] ?? $resumenPrograma['tipoPlan'];
    if (!$resumenPrograma['cuotaUnica'] && $resumenPrograma['numeroCuotasPlan']) {
        echo ' (' . $resumenPrograma['numeroCuotasPlan'] . ' cuotas planificadas)';
    }
    ?>
</div>
<table class="tabla-modulos"><thead>
<tr><th>Costo del Programa</th><th>Pagado</th><th>Saldo Pendiente</th></tr>
</thead><tbody>
<tr>
<td>Bs. <?php echo number_format($resumenPrograma['costoTotal'],2); ?></td>
<td style="color:#28a745"><strong>Bs. <?php echo number_format($resumenPrograma['totalPagado'],2); ?></strong></td>
<td style="color:<?php echo $resumenPrograma['saldoPendiente'] > 0 ? '#dc3545' : '#28a745'; ?>">
    <strong><?php echo $resumenPrograma['saldoPendiente'] > 0 ? 'Bs. ' . number_format($resumenPrograma['saldoPendiente'],2) : 'CANCELADO ✓'; ?></strong>
</td>
</tr>
</tbody></table></div>

<?php if(count($cuotasPagadas)>0): ?>
<div class="seccion"><div class="seccion-titulo">✓ CUOTAS PAGADAS (<?php echo count($cuotasPagadas); ?>)</div>
<table class="tabla-modulos"><thead>
<tr><th>Cuota</th><th>Monto</th><th>Fecha</th><th>Voucher</th></tr>
</thead><tbody>
<?php foreach($cuotasPagadas as $c): ?>
<tr>
<td>
<?php
    if (!$resumenPrograma['cuotaUnica'] && $resumenPrograma['numeroCuotasPlan'] && $c['NumeroCuota']) {
        echo $c['NumeroCuota'] . ' de ' . $resumenPrograma['numeroCuotasPlan'];
    } else {
        echo $c['NumeroCuota'] ?: '-';
    }
?>
</td>
<td>Bs. <?php echo number_format($c['montocuota'],2); ?></td>
<td><?php echo $c['fechapago']?date('d/m/Y',strtotime($c['fechapago'])):'-'; ?></td>
<td><?php echo $c['nvaucher'] ?: '-'; ?></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>

<?php if($resumenPrograma['saldoPendiente'] > 0): ?>
<div style="background:#fff3cd;padding:10px;border-left:4px solid #ffc107;margin-top:10px">
<strong>⚠ IMPORTANTE:</strong> Saldo pendiente del programa: <strong>Bs. <?php echo number_format($resumenPrograma['saldoPendiente'],2); ?></strong>
</div>
<?php endif; ?>
<?php else: ?>
<div class="seccion">
<div class="alert" style="background:#fff3cd;padding:10px;border-left:4px solid #ffc107;">
⚠ Este estudiante todavía no tiene un Plan de Pago del Programa definido.
</div>
</div>
<?php endif; ?>

<div class="resumen"><table>
<tr><td>Matrícula Pagada:</td><td>Bs. <?php echo number_format($costoMatriculaPagado,2); ?></td></tr>
<?php if($resumenPrograma['tienePlan']): ?>
<tr><td>Costo Programa:</td><td>Bs. <?php echo number_format($resumenPrograma['costoTotal'],2); ?></td></tr>
<tr><td>Pagado del Programa:</td><td>Bs. <?php echo number_format($resumenPrograma['totalPagado'],2); ?></td></tr>
<tr><td><strong>Saldo Total del Programa:</strong></td><td><strong>
<?php echo $resumenPrograma['saldoPendiente']>0?'Bs. '.number_format($resumenPrograma['saldoPendiente'],2):'CANCELADO ✓'; ?>
</strong></td></tr>
<?php endif; ?>
</table></div>

<div class="footer">
<p><strong>Este recibo es válido como comprobante de pago.</strong></p>
<p>Sistema de Gestión Académica</p>
<p style="font-size:8pt;color:#999">Generado el <?php echo $fechaGeneracion; ?> | Usuario: <?php echo $_SESSION['Usuario']; ?></p>
</div>

</body></html>
