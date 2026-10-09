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
    $cuotasPagadas = array_values(array_filter($cuotas, function($c) { return $c['Estado'] === 'PAGADO'; }));

    // En el recibo las cuotas van en orden (1, 2, 3...); el modelo las trae por registro descendente
    usort($cuotasPagadas, function($a, $b) {
        return [(int)$a['NumeroCuota'], $a['fechapago']] <=> [(int)$b['NumeroCuota'], $b['fechapago']];
    });

} catch(PDOException $e){ die('Error al obtener datos: '.$e->getMessage()); }

// Con muchas cuotas la tabla se reparte en dos columnas para que el recibo siga en una hoja
$columnasCuotas = count($cuotasPagadas) > 12
    ? array_chunk($cuotasPagadas, (int)ceil(count($cuotasPagadas) / 2))
    : [$cuotasPagadas];
$numeroInicialColumna = 0;

function TextoCuotaRecibo($c, $resumenPrograma)
{
    if (!$resumenPrograma['cuotaUnica'] && $resumenPrograma['numeroCuotasPlan'] && $c['NumeroCuota']) {
        return $c['NumeroCuota'] . ' de ' . $resumenPrograma['numeroCuotasPlan'];
    }
    return $c['NumeroCuota'] ?: '-';
}
?>
<!DOCTYPE html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recibo - <?php echo $numeroRecibo; ?></title>

<style>
/* Hoja carta (215.9 x 279.4 mm) con márgenes de 10 mm: el área útil es 195.9 x 259.4 mm.
   En pantalla se muestra la hoja tal como saldrá impresa. */
@page{size:letter;margin:10mm}
*{margin:0;padding:0;box-sizing:border-box;-webkit-print-color-adjust:exact;print-color-adjust:exact}
body{font-family:Arial,sans-serif;font-size:9pt;line-height:1.25;color:#333;background:#e9ecef}

.papel{width:215.9mm;min-height:279.4mm;padding:10mm;margin:15px auto;background:#fff;box-shadow:0 2px 12px rgba(0,0,0,.15)}
.hoja{width:195.9mm}

.header{display:flex;align-items:center;justify-content:space-between;padding-bottom:4px;border-bottom:2px solid #ccc}
.header-content{text-align:center;flex-grow:1;margin:0 8px;line-height:1.15}
.header-content h3{font-size:11pt}
.header-content h4{font-size:9pt}
.header-content p{font-size:7.5pt;color:#555}
.logo{width:60px;text-align:center}
.logo img{max-width:100%;max-height:52px}

.titulo-recibo{text-align:center;margin:6px 0 0}
.titulo-recibo h2{font-size:12.5pt;color:#1e3c72;letter-spacing:1px;margin-bottom:2px}
.titulo-recibo .datos-recibo{font-size:8pt;color:#555}
.numero-recibo{display:inline-block;background:#1e3c72;color:#fff;padding:1px 8px;font-size:8pt;border-radius:3px;font-weight:bold;margin-right:6px}

.info-estudiante{background:#f8f9fa;border-left:3px solid #1e3c72;padding:5px 8px;margin:8px 0;border-radius:3px}
.info-estudiante table{width:100%;border-collapse:collapse}
.info-estudiante td{padding:1px 4px;vertical-align:top}
.info-estudiante td.etiqueta{width:1%;white-space:nowrap;color:#555}

.seccion{margin:7px 0}
.seccion-titulo{padding:3px 8px;border-radius:3px 3px 0 0;font-size:8.5pt;font-weight:bold;background:#28a745;color:#fff}

.tabla-modulos{width:100%;border-collapse:collapse}
.tabla-modulos th,.tabla-modulos td{border:1px solid #ddd;padding:2px 5px;font-size:8pt}
.tabla-modulos thead{background:#1e3c72;color:#fff}
.tabla-modulos th{font-weight:bold;text-align:left}
.tabla-modulos .num{text-align:right;white-space:nowrap}

/* Cuotas en una o dos columnas */
.columnas-cuotas{display:flex;gap:6px;align-items:flex-start}
.columnas-cuotas > table{flex:1}

.aviso{background:#fff3cd;padding:4px 8px;border-left:3px solid #ffc107;margin-top:6px;font-size:8.5pt}

.resumen{border:1.5px solid #1e3c72;border-radius:4px;margin-top:10px;display:flex}
.resumen > div{flex:1;padding:4px 8px;border-right:1px solid #ddd;background:#f8f9fa}
.resumen > div:last-child{border-right:none;background:#eef2f9}
.resumen span{display:block;font-size:7.5pt;color:#555}
.resumen strong{font-size:9.5pt}

.footer{margin-top:12px;padding-top:5px;border-top:1px solid #ddd;text-align:center;font-size:7.5pt;line-height:1.3;color:#666}

.btn-imprimir{position:fixed;bottom:15px;right:15px;background:#1e3c72;color:#fff;border:none;padding:10px 20px;border-radius:20px;font-size:11pt;cursor:pointer}

/* Medida del área útil de la hoja, para el ajuste automático */
#medidaHoja{position:absolute;visibility:hidden;height:259.4mm;width:1px;top:0;left:0}

@media print{
    .no-print{display:none}
    body{background:#fff}
    .papel{width:auto;min-height:0;padding:0;margin:0;box-shadow:none}
}
</style>
</head>
<body>

<button onclick="window.print()" class="btn-imprimir no-print">🖨️ Imprimir</button>
<div id="medidaHoja"></div>

<div class="papel"><div class="hoja">

<div class="header">
    <div class="logo"><img src="../../extensiones/imagenespdf/logouto.png"></div>
    <div class="header-content">
        <h3>UNIVERSIDAD TÉCNICA DE ORURO</h3>
        <h4>FACULTAD DE CIENCIAS DE LA SALUD</h4>
        <h4>COORDINACIÓN DE POSGRADO - ODONTOLOGÍA</h4>
        <p>Calle Presidente Montes entre Murguía y Aldana, Edificio UTO 2do Piso · Telf: 5237317 - Fax: 5247110 - Oruro - Bolivia</p>
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
<tr><td class="etiqueta">Estudiante:</td><td><strong><?php echo htmlspecialchars($nombreCompleto); ?></strong></td>
<td class="etiqueta">C.I.:</td><td><strong><?php echo htmlspecialchars($ciCompleto); ?></strong></td></tr>
<tr><td class="etiqueta">Programa:</td><td colspan="3"><strong><?php echo htmlspecialchars($estudiante['NombrePrograma']); ?></strong></td></tr>
<tr><td class="etiqueta">Grado:</td><td><?php echo htmlspecialchars($estudiante['GradoAcademico']); ?></td>
<td class="etiqueta">Código:</td><td><?php echo htmlspecialchars($estudiante['CodigoPrograma']); ?></td></tr>
</table></div>

<?php if($costoMatriculaPagado>0): ?>
<div class="seccion">
<div class="seccion-titulo" style="background:#1e3c72">✓ MATRÍCULA CANCELADA</div>
<table class="tabla-modulos"><thead>
<tr><th>Concepto</th><th class="num">Monto</th><th>Fecha</th><th>Voucher</th></tr>
</thead><tbody>
<tr>
<td>Matrícula - <?php echo htmlspecialchars($estudiante['NombrePrograma']); ?></td>
<td class="num">Bs. <?php echo number_format($costoMatriculaPagado,2); ?></td>
<td><?php echo date('d/m/Y',strtotime($estudiante['FechaInscripcion'])); ?></td>
<td><?php echo htmlspecialchars($estudiante['VoucherMatricula'] ?: '-'); ?></td>
</tr>
<?php if($montoDescuentoMatricula > 0): ?>
<tr style="background:#d4edda">
<?php // costomatricula ya es el monto CON descuento: esta fila solo informa cuánto se descontó ?>
<td><strong>Incluye descuento de matrícula (<?php echo number_format($porcentajeDescuentoMatricula,1); ?>%)</strong></td>
<td class="num" style="color:#28a745"><strong>Bs. <?php echo number_format($montoDescuentoMatricula,2); ?></strong></td>
<td colspan="2"></td>
</tr>
<?php endif; ?>
</tbody></table></div>
<?php endif; ?>

<?php if($resumenPrograma['tienePlan']): ?>

<?php if(count($cuotasPagadas)>0): ?>
<div class="seccion"><div class="seccion-titulo">✓ CUOTAS PAGADAS (<?php echo count($cuotasPagadas); ?>)</div>
<div class="columnas-cuotas">
<?php foreach($columnasCuotas as $columna): ?>
<table class="tabla-modulos"><thead>
<tr><th>Cuota</th><th class="num">Monto</th><th>Fecha</th><th>Voucher</th></tr>
</thead><tbody>
<?php foreach($columna as $c): ?>
<tr>
<td><?php echo TextoCuotaRecibo($c, $resumenPrograma); ?></td>
<td class="num">Bs. <?php echo number_format($c['montocuota'],2); ?></td>
<td><?php echo $c['fechapago']?date('d/m/Y',strtotime($c['fechapago'])):'-'; ?></td>
<td><?php echo htmlspecialchars($c['nvaucher'] ?: '-'); ?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php endforeach; ?>
</div></div>
<?php endif; ?>

<?php if($resumenPrograma['saldoPendiente'] > 0): ?>
<div class="aviso">
<strong>⚠ IMPORTANTE:</strong> Saldo pendiente del programa: <strong>Bs. <?php echo number_format($resumenPrograma['saldoPendiente'],2); ?></strong>
</div>
<?php endif; ?>
<?php else: ?>
<div class="aviso">⚠ Este estudiante todavía no tiene un Plan de Pago del Programa definido.</div>
<?php endif; ?>

<?php
// Resumen en una sola fila (incluye el plan de pago, que antes iba en una tabla aparte)
$nombresPlan = ['REGULAR' => 'Plan Regular', 'DESCUENTO' => 'Plan al Contado', 'GRUPAL' => 'Plan Grupal'];
?>
<div class="resumen">
    <div><span>Matrícula pagada</span><strong>Bs. <?php echo number_format($costoMatriculaPagado,2); ?></strong></div>
    <?php if($resumenPrograma['tienePlan']): ?>
    <div><span>Costo del programa<?php
        echo ' · ' . ($nombresPlan[$resumenPrograma['tipoPlan']] ?? $resumenPrograma['tipoPlan']);
        if (!$resumenPrograma['cuotaUnica'] && $resumenPrograma['numeroCuotasPlan']) {
            echo ' (' . $resumenPrograma['numeroCuotasPlan'] . ' cuotas)';
        }
    ?></span><strong>Bs. <?php echo number_format($resumenPrograma['costoTotal'],2); ?></strong></div>
    <div><span>Pagado del programa</span><strong style="color:#28a745">Bs. <?php echo number_format($resumenPrograma['totalPagado'],2); ?></strong></div>
    <div><span>Saldo del programa</span><strong style="color:<?php echo $resumenPrograma['saldoPendiente'] > 0 ? '#dc3545' : '#28a745'; ?>">
        <?php echo $resumenPrograma['saldoPendiente']>0?'Bs. '.number_format($resumenPrograma['saldoPendiente'],2):'CANCELADO ✓'; ?>
    </strong></div>
    <?php endif; ?>
</div>

<div class="footer">
<p><strong>Este recibo es válido como comprobante de pago.</strong> Sistema de Gestión Académica</p>
<p style="color:#999">Generado el <?php echo $fechaGeneracion; ?> | Usuario: <?php echo htmlspecialchars($_SESSION['Usuario']); ?></p>
</div>

</div></div>

<script>
// Si el contenido no entra en una hoja carta, se reduce proporcionalmente para que entre
// (hasta 55 %). Se calcula al cargar y otra vez justo antes de imprimir.
function ajustarAHoja() {
    var hoja = document.querySelector('.hoja');
    hoja.style.zoom = 1;
    var alto = document.getElementById('medidaHoja').offsetHeight;
    var contenido = hoja.scrollHeight;
    hoja.style.zoom = contenido > alto ? Math.max(0.55, alto / contenido * 0.98).toFixed(3) : 1;
}
window.addEventListener('load', ajustarAHoja);
window.addEventListener('beforeprint', ajustarAHoja);
</script>
</body></html>
