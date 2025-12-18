<?php
session_start(); if (!isset($_SESSION['Validar']) || $_SESSION['Validar'] !== true) die('Acceso denegado');

$idinscripcion = isset($_GET['idinscripcion']) ? intval($_GET['idinscripcion']) : 0;
if ($idinscripcion === 0) die('ID de inscripción inválido');

require_once '../../modelos/conexion.modelo.php';
require_once '../../modelos/pagomodulo.modelo.php';

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
               ep.pagoCompleto,
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

    $modulos=PagoModuloModelo::ObtenerModulosConEstadoPagoModelo($estudiante['ProgramaID'],$idinscripcion);
    $modulosPagados=array_filter($modulos,function($m){return $m['Pagado']==1;});
    $modulosPendientes=array_filter($modulos,function($m){return $m['Pagado']==0;});

    // Calcular montos
    $pagoCompleto = isset($estudiante['pagoCompleto']) ? intval($estudiante['pagoCompleto']) : 0;
    $montoPagado = isset($estudiante['montoPagado']) ? floatval($estudiante['montoPagado']) : 0;
    $montoDescuento = isset($estudiante['montoDescuento']) ? floatval($estudiante['montoDescuento']) : 0;
    $porcentajeDescuento = isset($estudiante['porcentajeDescuento']) ? floatval($estudiante['porcentajeDescuento']) : 0;
    $costoMatriculaBD = floatval($estudiante['CostoMatricula']); // Costo de matrícula del programa (BD)
    $costoProgramaBD = floatval($estudiante['CostoTotalPrograma']); // Costo del programa (BD)

    // Si es pago completo y tiene montoPagado, usar ese, sino usar el costo guardado en costomatricula
    $costoMatriculaPagado = ($pagoCompleto == 1 && $montoPagado > 0) ? $montoPagado : floatval($estudiante['costomatricula']);

    // Calcular el costo original TOTAL (antes del descuento)
    // Cuando es pago completo: TOTAL = Matrícula + Programa
    // El montoPagado + montoDescuento también nos da el total original
    if ($pagoCompleto == 1) {
        // Usar el cálculo más preciso: montoPagado + montoDescuento
        $costoOriginal = $montoPagado + $montoDescuento;
        // Verificar con la suma de matrícula + programa (debe coincidir)
        $totalDesdePrograma = $costoMatriculaBD + $costoProgramaBD;
        // Si hay diferencia significativa, usar el total desde programa
        if (abs($costoOriginal - $totalDesdePrograma) > 0.01) {
            $costoOriginal = $totalDesdePrograma;
        }
    } else {
        $costoOriginal = floatval($estudiante['costomatricula']);
    }

    $totalPagadoModulos=array_sum(array_column($modulosPagados,'CostoPagado'));
    $totalPendiente=array_sum(array_column($modulosPendientes,'Costo'));
    $totalPrograma=floatval($estudiante['CostoTotalPrograma']);
    $saldoPrograma=$totalPrograma-$totalPagadoModulos;

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
    line-height:1.1;           /* 🔽 INTERLINEADO REDUCIDO */
    color:#333;
    background:#fff;
    padding:15px;
}

/* HEADER */
.header{display:flex;align-items:center;justify-content:space-between;padding:5px 0;border-bottom:2px solid #ccc}
.header-content{text-align:center;flex-grow:1;margin:0 10px;line-height:1.1}
.header-content h3,.header-content h4{margin:3px 0;line-height:1.1}
.header-content p{margin:2px 0;font-size:9pt}

/* LOGOS */
.logo{width:80px}
.logo img{max-width:100%;max-height:65px}

/* RECIBO */
.numero-recibo{
    position:absolute;top:12px;right:12px;
    background:#667eea;color:#fff;padding:5px 12px;
    font-size:8pt;border-radius:4px;font-weight:bold
}

/* INFO ESTUDIANTE */
.info-estudiante{
    background:#f8f9fa;border-left:4px solid #667eea;
    padding:10px;margin:15px 0;border-radius:5px
}
.info-estudiante table{width:100%;line-height:1.0} /* 🔽 INTERLINEADO TABLA */

/* SECCIONES */
.seccion{margin:15px 0}
.seccion-titulo{
    padding:6px 10px;border-radius:4px;font-size:11pt;
    margin-bottom:5px;font-weight:bold
}

/* COLORES SECCIONES */
.seccion-titulo.pendiente{background:#dc3545;color:white}
.seccion-titulo:not(.pendiente){background:#28a745;color:white}

/* TABLAS */
.tabla-modulos{
    width:100%;border-collapse:collapse;margin-bottom:10px;
    line-height:1.0   /* 🔽 INTERLINEADO */
}
.tabla-modulos th,.tabla-modulos td{
    border:1px solid #ddd;
    padding:5px;font-size:9.5pt
}
.tabla-modulos thead{background:#667eea;color:#fff}

/* RESUMEN */
.resumen{
    background:#f8f9fa;border:2px solid #667eea;
    border-radius:6px;padding:12px;margin-top:20px
}
.resumen table{width:100%;line-height:1.0}
.resumen td{padding:6px}

/* FOOTER */
.footer{
    margin-top:25px;padding-top:10px;
    border-top:1px solid #ddd;text-align:center;
    font-size:8.5pt;line-height:1.1;color:#666
}

/* BOTÓN */
.btn-imprimir{
    position:fixed;top:15px;right:15px;
    background:#667eea;color:#fff;border:none;
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
<div class="numero-recibo">N° <?php echo $numeroRecibo; ?></div>

<div class="header">
    <div class="logo"><img src="../../extensiones/imagenespdf/logouto.png"></div>
    <div class="header-content">
        <h3>UNIVERSIDAD TÉCNICA DE ORURO</h3>
        <h4>FACULTAD DE CIENCIAS DE LA SALUD</h4>
        <h4>POSGRADO - ODONTOLOGÍA</h4>
            <p>AV. Del Moreno Edificio San Agustin II (Ex Almacenes COMIBOL) Telefonos: 52 - 37317 - Fax: 52 - 47110 </p>

        <p>Oruro - Bolivia</p>
        <p>Emisión: <?php echo $fechaGeneracion; ?></p>
    </div>
    <div class="logo"><img src="../../extensiones/imagenespdf/logofcs.png"></div>
</div>

<h3>RECIBO DE PAGO</h3>

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
<div class="seccion-titulo" style="background:#667eea">✓ <?php echo $pagoCompleto == 1 ? 'PAGO COMPLETO REGISTRADO' : 'MATRÍCULA CANCELADA'; ?></div>
<table class="tabla-modulos"><thead>
<tr><th>#</th><th>Concepto</th><th>Monto</th><th>Fecha</th><th>Voucher</th></tr>
</thead><tbody>
<?php if($pagoCompleto == 1): ?>
<!-- Desglose para Pago Completo -->
<tr style="background:#e7f3ff">
<td>1</td><td><strong>Matrícula - <?php echo $estudiante['NombrePrograma']; ?></strong></td>
<td>Bs. <?php echo number_format($costoMatriculaBD,2); ?></td>
<td rowspan="<?php echo $montoDescuento > 0 ? 5 : 3; ?>"><?php echo date('d/m/Y',strtotime($estudiante['FechaInscripcion'])); ?></td>
<td rowspan="<?php echo $montoDescuento > 0 ? 5 : 3; ?>"><?php echo $estudiante['VoucherMatricula'] ?: '-'; ?></td>
</tr>
<tr style="background:#e7f3ff">
<td>2</td><td><strong>Costo del Programa</strong></td>
<td>Bs. <?php echo number_format($costoProgramaBD,2); ?></td>
</tr>
<tr style="background:#fff3cd">
<td colspan="2"><strong>Subtotal (Matrícula + Programa)</strong></td>
<td><strong>Bs. <?php echo number_format($costoOriginal,2); ?></strong></td>
</tr>
<?php if($montoDescuento > 0): ?>
<tr style="background:#d4edda">
<td colspan="2"><strong>Descuento Aplicado (<?php echo number_format($porcentajeDescuento,1); ?>%)</strong></td>
<td style="color:#28a745"><strong>- Bs. <?php echo number_format($montoDescuento,2); ?></strong></td>
</tr>
<tr style="background:#c3e6cb; font-weight:bold">
<td colspan="2"><strong>TOTAL PAGADO</strong></td>
<td><strong>Bs. <?php echo number_format($montoPagado,2); ?></strong></td>
</tr>
<?php endif; ?>
<?php else: ?>
<!-- Solo Matrícula -->
<tr>
<td>1</td><td>Matrícula - <?php echo $estudiante['NombrePrograma']; ?></td>
<td>Bs. <?php echo number_format($costoMatriculaPagado,2); ?></td>
<td><?php echo date('d/m/Y',strtotime($estudiante['FechaInscripcion'])); ?></td>
<td><?php echo $estudiante['VoucherMatricula'] ?: '-'; ?></td>
</tr>
<?php endif; ?>
</tbody></table></div>
<?php endif; ?>

<?php if(count($modulosPagados)>0): ?>
<div class="seccion"><div class="seccion-titulo">✓ MÓDULOS PAGADOS (<?php echo count($modulosPagados); ?>)</div>
<table class="tabla-modulos"><thead>
<tr><th>#</th><th>Código</th><th>Módulo</th><th>Monto</th><th>Fecha</th><th>Voucher</th></tr>
</thead><tbody>
<?php foreach($modulosPagados as $i=>$m): ?>
<tr>
<td><?php echo $i+1; ?></td><td><?php echo $m['Codigo']; ?></td>
<td><?php echo $m['NombreModulo']; ?></td>
<td>Bs. <?php echo number_format($m['CostoPagado'],2); ?></td>
<td><?php echo $m['FechaPago']?date('d/m/Y',strtotime($m['FechaPago'])):'-'; ?></td>
<td><?php echo $m['NumeroVaucher'] ?: '-'; ?></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>

<?php if(count($modulosPendientes)>0): ?>
<div class="seccion"><div class="seccion-titulo pendiente">⚠ CUOTAS PENDIENTES (<?php echo count($modulosPendientes); ?>)</div>
<table class="tabla-modulos"><thead>
<tr><th>#</th><th>Código</th><th>Módulo</th><th>Monto</th><th colspan="2">Estado</th></tr></thead><tbody>
<?php foreach($modulosPendientes as $i=>$m): ?>
<tr>
<td><?php echo $i+1; ?></td><td><?php echo $m['Codigo']; ?></td>
<td><?php echo $m['NombreModulo']; ?></td>
<td>Bs. <?php echo number_format($m['Costo'],2); ?></td>
<td colspan="2"><span style="background:#dc3545;color:#fff;padding:3px 10px;border-radius:10px">PENDIENTE</span></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<div style="background:#fff3cd;padding:10px;border-left:4px solid #ffc107;margin-top:10px">
<strong>⚠ IMPORTANTE:</strong> Pendiente: <?php echo count($modulosPendientes); ?> módulo(s), Total: <strong>Bs. <?php echo number_format($totalPendiente,2); ?></strong>
</div></div>
<?php endif; ?>

<div class="resumen"><table>
<?php if($montoDescuento > 0): ?>
<tr style="background:#e7f3ff"><td><strong>Costo Original del Programa:</strong></td><td><strong>Bs. <?php echo number_format($costoOriginal,2); ?></strong></td></tr>
<tr style="background:#d4edda"><td>Descuento Aplicado (<?php echo number_format($porcentajeDescuento,1); ?>%):</td><td style="color:#28a745"><strong>- Bs. <?php echo number_format($montoDescuento,2); ?></strong></td></tr>
<tr style="background:#fff"><td><strong>Monto Pagado (con descuento):</strong></td><td><strong>Bs. <?php echo number_format($costoMatriculaPagado,2); ?></strong></td></tr>
<?php else: ?>
<tr><td>Matrícula Pagada:</td><td>Bs. <?php echo number_format($costoMatriculaPagado,2); ?></td></tr>
<?php endif; ?>
<tr><td>Costo Programa:</td><td>Bs. <?php echo number_format($totalPrograma,2); ?></td></tr>
<tr><td>Pagado Módulos:</td><td>Bs. <?php echo number_format($totalPagadoModulos,2); ?></td></tr>
<tr><td>Pendiente:</td><td>Bs. <?php echo number_format($totalPendiente,2); ?></td></tr>
<tr><td><strong>Saldo Total:</strong></td><td><strong>
<?php echo $saldoPrograma>0?'Bs. '.number_format($saldoPrograma,2):'CANCELADO ✓'; ?>
</strong></td></tr>
</table></div>

<div class="footer">
<p><strong>Este recibo es válido como comprobante de pago.</strong></p>
<p>Sistema de Gestión Académica</p>
<p style="font-size:8pt;color:#999">Generado el <?php echo $fechaGeneracion; ?> | Usuario: <?php echo $_SESSION['Usuario']; ?></p>
</div>

</body></html>
