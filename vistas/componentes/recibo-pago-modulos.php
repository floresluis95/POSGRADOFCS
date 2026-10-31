<?php
if (session_status() === PHP_SESSION_NONE) session_start();
// El personal (desde Matriculados) o el propio estudiante (Mis órdenes de pago), solo el suyo
if (!isset($_SESSION['Validar']) || $_SESSION['Validar'] !== true || !in_array($_SESSION['Tipo'] ?? '', ['ADM', 'SEC', 'AUX', 'EST'], true)) die('Acceso denegado');

$idinscripcion = isset($_GET['idinscripcion']) ? intval($_GET['idinscripcion']) : 0;
if ($idinscripcion === 0) die('ID de inscripción inválido');

require_once file_exists(__DIR__ . '/../../modelos/conexion.modelo.php') ? __DIR__ . '/../../modelos/conexion.modelo.php' : __DIR__ . '/../../core/conexion.modelo.php';
if ($_SESSION['Tipo'] === 'EST') {
    $stmtDueno = Conexion::Conectar()->prepare("SELECT 1 FROM estudianteprograma WHERE idInscripcion = :id AND EstudianteID = :est");
    $stmtDueno->execute([':id' => $idinscripcion, ':est' => (int)($_SESSION['EstudianteID'] ?? 0)]);
    if (!$stmtDueno->fetchColumn()) die('Acceso denegado');
}
require_once '../../modelos/modulopagos_core.php';
require_once __DIR__ . '/../../modelos/voucher.modelo.php';
require_once __DIR__ . '/../../modelos/pagoclinica.modelo.php';
require_once __DIR__ . '/../../modelos/institucion.modelo.php'; // datos y logos de la institución (encabezado)

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
               " . VoucherModelo::SqlFechaOrdenMatricula('ep.idInscripcion') . " AS FechaOrdenMatricula,
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

    // Órdenes de cuota sin voucher validado: van en la misma tabla, en su lugar por N°,
    // marcadas "PENDIENTE DE PAGO" (no suman a lo pagado ni cambian el saldo)
    $cuotasPendientes = array_map(function($o) {
        return [
            'NumeroCuota' => $o['NumeroCuota'],
            'montocuota' => $o['MontoTotal'],
            'FechaSolicitud' => $o['FechaGeneracion'],
            'fechapago' => null,
            'nvaucher' => null,
            'pendiente' => true,
            'extraviado' => $o['Estado'] === 'EXTRAVIADO',
        ];
    }, PagoModuloModelo::ListarCuotasSinPagarModelo($idinscripcion));
    $filasCuotas = array_merge(
        array_map(function($c) { return $c + ['pendiente' => false, 'extraviado' => false]; }, $cuotasPagadas),
        $cuotasPendientes
    );
    usort($filasCuotas, function($a, $b) { return (int)$a['NumeroCuota'] <=> (int)$b['NumeroCuota']; });

    // Clínicas (aparte: no se suman al costo ni al saldo del programa). Las pendientes
    // también se listan, marcadas; el total es solo de las pagadas.
    $clinicasRecibo = array_values(array_filter(
        PagoClinicaModelo::ListarPorInscripcionModelo($idinscripcion),
        function($cl) { return in_array($cl['Estado'], ['CONFIRMADO', 'PENDIENTE', 'EXTRAVIADO'], true); }
    ));
    usort($clinicasRecibo, function($a, $b) { return (int)$a['IdOrdenPago'] <=> (int)$b['IdOrdenPago']; });
    $clinicasPagadas = array_values(array_filter($clinicasRecibo, function($cl) { return $cl['Estado'] === 'CONFIRMADO'; }));
    $clinicasPendientes = count($clinicasRecibo) - count($clinicasPagadas);
    $totalClinicas = array_sum(array_map(function($cl) { return floatval($cl['MontoTotal']); }, $clinicasPagadas));

} catch(PDOException $e){ die('Error al obtener datos: '.$e->getMessage()); }

// Con muchas cuotas la tabla se reparte en dos columnas para que el recibo siga en una hoja
$columnasCuotas = count($filasCuotas) > 12
    ? array_chunk($filasCuotas, (int)ceil(count($filasCuotas) / 2))
    : [$filasCuotas];
$numeroInicialColumna = 0;

/* "PENDIENTE DE PAGO" en el lugar del voucher; con voucher extraviado, que falta registrarlo */
function MarcaPendienteRecibo($fila)
{
    return '<span class="marca-pendiente">' . (!empty($fila['extraviado']) ? 'VOUCHER POR VALIDAR' : 'PENDIENTE DE PAGO') . '</span>';
}

function TextoCuotaRecibo($c, $resumenPrograma)
{
    if ($resumenPrograma['numeroCuotasPlan'] && $c['NumeroCuota']) {
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
   En pantalla se muestra la hoja tal como saldrá impresa.
   Pensado para imprimir con poca tinta: sin fondos de color, solo negro y grises,
   pocas líneas (finas y claras) y los datos importantes en negrita. */
@page{size:letter;margin:10mm}
*{margin:0;padding:0;box-sizing:border-box;-webkit-print-color-adjust:exact;print-color-adjust:exact}
body{font-family:Arial,sans-serif;font-size:9pt;line-height:1.25;color:#111;background:#e9ecef}

.papel{width:215.9mm;min-height:279.4mm;padding:10mm;margin:15px auto;background:#fff;box-shadow:0 2px 12px rgba(0,0,0,.15)}
.hoja{width:195.9mm}

.header{display:flex;align-items:center;justify-content:space-between;padding-bottom:4px;border-bottom:1.5px solid #111}
.header-content{text-align:center;flex-grow:1;margin:0 8px;line-height:1.15}
.header-content h3{font-size:11pt}
.header-content h4{font-size:9pt}
.header-content p{font-size:7.5pt;color:#555}
.logo{width:60px;text-align:center}
.logo img{max-width:100%;max-height:52px}

.titulo-recibo{display:flex;justify-content:space-between;align-items:flex-end;margin:8px 0 6px}
.titulo-recibo h2{font-size:13pt;letter-spacing:1.5px}
.titulo-recibo .datos-recibo{text-align:right;font-size:7.5pt;color:#555;line-height:1.4}
.numero-recibo{display:block;font-size:9.5pt;font-weight:bold;color:#111}

/* Etiqueta pequeña y gris pegada a su dato (sin línea entre ambos) */
.etq{display:block;font-size:6.5pt;font-weight:bold;letter-spacing:.3px;text-transform:uppercase;color:#666;margin-bottom:1px}

/* Marco con los datos del estudiante: solo líneas claras entre columnas */
.info-estudiante{border:1px solid #888;display:grid;grid-template-columns:2fr 1fr;margin-bottom:8px}
.info-estudiante > div{padding:4px 8px}
.info-estudiante > div:nth-child(odd){border-right:1px solid #ddd}
.info-estudiante > div:nth-child(n+3){border-top:1px solid #ddd}
.info-estudiante .nombre{font-size:11pt;font-weight:bold}
.info-estudiante .programa{font-size:9.5pt;font-weight:bold}

.seccion{margin:8px 0}
.seccion-titulo{padding:3px 8px;font-size:8pt;font-weight:bold;letter-spacing:.5px;text-transform:uppercase;background:#ebebeb;color:#111}

/* Tablas: sin líneas verticales; solo una línea fina bajo cada fila */
.tabla-modulos{width:100%;border-collapse:collapse}
.tabla-modulos th,.tabla-modulos td{padding:3px 8px;font-size:8.5pt;border-bottom:1px solid #e3e3e3}
.tabla-modulos th{font-size:6.5pt;font-weight:bold;letter-spacing:.3px;text-transform:uppercase;color:#666;text-align:left;border-bottom:1px solid #999}
.tabla-modulos .num{text-align:right;white-space:nowrap}
.tabla-modulos td.num{font-weight:bold}
.tabla-modulos tr.descuento td{font-style:italic;color:#444}

/* Cuotas en una o dos columnas */
.columnas-cuotas{display:flex;gap:10px;align-items:flex-start}
.columnas-cuotas > table{flex:1}

.aviso{padding:4px 8px;border-left:3px solid #111;margin-top:6px;font-size:8.5pt}

/* Resumen final: un marco y líneas claras entre los datos; el saldo destacado */
.resumen{border:1px solid #888;margin-top:10px;display:flex}
.resumen > div{flex:1;padding:4px 8px;border-right:1px solid #ddd}
.resumen > div:last-child{border-right:none}
.resumen strong{font-size:10pt}
.resumen .saldo strong{font-size:11pt}
.resumen .plan{display:block;font-size:7pt;color:#555}
.seccion-nota{font-weight:normal;text-transform:none;font-size:7.5pt;color:#666;margin-left:6px}
.total-clinicas td{font-weight:bold;border-top:1px solid #888}
/* Orden pendiente: en gris cursiva y con la marca en recuadro (se distingue al imprimir en negro) */
.fila-pendiente td{color:#555;font-style:italic}
.marca-pendiente{display:inline-block;font-style:normal;font-weight:bold;font-size:7pt;letter-spacing:.3px;color:#111;border:1px solid #555;padding:0 4px}

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
    <div class="logo"><img src="<?php echo InstitucionModelo::LogoSrc('LOGO_IZQUIERDO', '../../'); ?>"></div>
    <div class="header-content">
        <h3><?php echo htmlspecialchars(InstitucionModelo::Universidad()); ?></h3>
        <h4><?php echo htmlspecialchars(InstitucionModelo::Facultad()); ?></h4>
        <h4><?php echo htmlspecialchars(InstitucionModelo::Unidad()); ?></h4>
        <p><?php echo htmlspecialchars(InstitucionModelo::DireccionCompleta(' · ')); ?></p>
    </div>
    <div class="logo"><img src="<?php echo InstitucionModelo::LogoSrc('LOGO_DERECHO', '../../'); ?>"></div>
</div>

<div class="titulo-recibo">
    <h2>RECIBO DE PAGO</h2>
    <div class="datos-recibo">
        <span class="numero-recibo">N° <?php echo $numeroRecibo; ?></span>
        Emisión: <?php echo $fechaGeneracion; ?>
    </div>
</div>

<div class="info-estudiante">
    <div><span class="etq">Estudiante</span><span class="nombre"><?php echo htmlspecialchars($nombreCompleto); ?></span></div>
    <div><span class="etq">C.I.</span><strong><?php echo htmlspecialchars($ciCompleto); ?></strong></div>
    <div><span class="etq">Programa</span><span class="programa"><?php echo htmlspecialchars($estudiante['NombrePrograma']); ?></span></div>
    <div><span class="etq">Grado · Código</span><?php echo htmlspecialchars($estudiante['GradoAcademico']); ?> · <strong><?php echo htmlspecialchars($estudiante['CodigoPrograma']); ?></strong></div>
</div>

<?php if($costoMatriculaPagado>0): ?>
<div class="seccion">
<div class="seccion-titulo">Matrícula cancelada</div>
<table class="tabla-modulos"><thead>
<tr><th>Concepto</th><th class="num">Monto</th><th>F. orden</th><th>F. pago</th><th>Voucher</th></tr>
</thead><tbody>
<tr>
<td>Matrícula - <?php echo htmlspecialchars($estudiante['NombrePrograma']); ?></td>
<td class="num">Bs. <?php echo number_format($costoMatriculaPagado,2); ?></td>
<td><?php echo $estudiante['FechaOrdenMatricula'] ? date('d/m/Y',strtotime($estudiante['FechaOrdenMatricula'])) : '-'; ?></td>
<td><?php echo date('d/m/Y',strtotime($estudiante['FechaInscripcion'])); ?></td>
<td><?php echo htmlspecialchars($estudiante['VoucherMatricula'] ?: '-'); ?></td>
</tr>
<?php if($montoDescuentoMatricula > 0): ?>
<tr class="descuento">
<?php // costomatricula ya es el monto CON descuento: esta fila solo informa cuánto se descontó ?>
<td>Incluye descuento de matrícula (<?php echo number_format($porcentajeDescuentoMatricula,1); ?>%)</td>
<td class="num">Bs. <?php echo number_format($montoDescuentoMatricula,2); ?></td>
<td colspan="3"></td>
</tr>
<?php endif; ?>
</tbody></table></div>
<?php endif; ?>

<?php if($resumenPrograma['tienePlan']): ?>

<?php if(count($filasCuotas)>0): ?>
<div class="seccion"><div class="seccion-titulo">Cuotas del programa (<?php echo count($cuotasPagadas); ?> pagada<?php echo count($cuotasPagadas) === 1 ? '' : 's'; ?><?php
    if (count($cuotasPendientes)) echo ' · ' . count($cuotasPendientes) . ' pendiente' . (count($cuotasPendientes) === 1 ? '' : 's') . ' de pago'; ?>)</div>
<div class="columnas-cuotas">
<?php foreach($columnasCuotas as $columna): ?>
<table class="tabla-modulos"><thead>
<tr><th>Cuota</th><th class="num">Monto</th><th>F. orden</th><th>F. pago</th><th>Voucher</th></tr>
</thead><tbody>
<?php foreach($columna as $c): ?>
<tr<?php echo $c['pendiente'] ? ' class="fila-pendiente"' : ''; ?>>
<td><?php echo TextoCuotaRecibo($c, $resumenPrograma); ?></td>
<td class="num">Bs. <?php echo number_format($c['montocuota'],2); ?></td>
<td><?php echo $c['FechaSolicitud']?date('d/m/Y',strtotime($c['FechaSolicitud'])):'-'; ?></td>
<td><?php echo $c['fechapago']?date('d/m/Y',strtotime($c['fechapago'])):'-'; ?></td>
<td><?php echo $c['pendiente'] ? MarcaPendienteRecibo($c) : htmlspecialchars($c['nvaucher'] ?: '-'); ?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php endforeach; ?>
</div></div>
<?php endif; ?>

<?php if($resumenPrograma['saldoPendiente'] > 0): ?>
<div class="aviso">
<strong>IMPORTANTE:</strong> Saldo pendiente del programa: <strong>Bs. <?php echo number_format($resumenPrograma['saldoPendiente'],2); ?></strong>
</div>
<?php endif; ?>
<?php else: ?>
<div class="aviso">Este estudiante todavía no tiene un Plan de Pago del Programa definido.</div>
<?php endif; ?>

<?php if(count($clinicasRecibo)>0): ?>
<div class="seccion"><div class="seccion-titulo"><?php echo $clinicasPendientes === 0
    ? 'Clínicas pagadas (' . count($clinicasPagadas) . ')'
    : 'Clínicas (' . count($clinicasPagadas) . ' pagada' . (count($clinicasPagadas) === 1 ? '' : 's') . ' · ' . $clinicasPendientes . ' pendiente' . ($clinicasPendientes === 1 ? '' : 's') . ' de pago)'; ?> <span class="seccion-nota">aparte del costo y saldo del programa</span></div>
<table class="tabla-modulos"><thead>
<tr><th>Concepto</th><th class="num">Monto</th><th>F. orden</th><th>F. pago</th><th>Voucher</th></tr>
</thead><tbody>
<?php foreach($clinicasRecibo as $cl): $clPendiente = $cl['Estado'] !== 'CONFIRMADO'; ?>
<tr<?php echo $clPendiente ? ' class="fila-pendiente"' : ''; ?>>
<td>Clínica · Orden <?php echo htmlspecialchars($cl['NumeroOrden']); ?></td>
<td class="num">Bs. <?php echo number_format($cl['MontoTotal'],2); ?></td>
<td><?php echo $cl['FechaGeneracion']?date('d/m/Y',strtotime($cl['FechaGeneracion'])):'-'; ?></td>
<td><?php echo $cl['FechaPago']?date('d/m/Y',strtotime($cl['FechaPago'])):'-'; ?></td>
<td><?php echo $clPendiente ? MarcaPendienteRecibo(['extraviado' => $cl['Estado'] === 'EXTRAVIADO']) : htmlspecialchars($cl['NumeroVoucher'] ?: '-'); ?></td>
</tr>
<?php endforeach; ?>
<tr class="total-clinicas"><td>Total clínicas pagadas</td><td class="num">Bs. <?php echo number_format($totalClinicas,2); ?></td><td colspan="3"></td></tr>
</tbody></table></div>
<?php endif; ?>

<?php
// Resumen en una sola fila (incluye el plan de pago, que antes iba en una tabla aparte)
$nombresPlan = ['REGULAR' => 'Plan Regular', 'DESCUENTO' => 'Plan al Contado', 'GRUPAL' => 'Plan Grupal'];
?>
<div class="resumen">
    <div><span class="etq">Matrícula pagada</span><strong>Bs. <?php echo number_format($costoMatriculaPagado,2); ?></strong></div>
    <?php if($resumenPrograma['tienePlan']): ?>
    <div><span class="etq">Costo del programa</span><strong>Bs. <?php echo number_format($resumenPrograma['costoTotal'],2); ?></strong>
        <span class="plan"><?php
            echo $nombresPlan[$resumenPrograma['tipoPlan']] ?? $resumenPrograma['tipoPlan'];
            if ($resumenPrograma['numeroCuotasPlan']) {
                echo ' · ' . $resumenPrograma['numeroCuotasPlan'] . ' cuotas';
            }
        ?></span></div>
    <div><span class="etq">Pagado del programa</span><strong>Bs. <?php echo number_format($resumenPrograma['totalPagado'],2); ?></strong></div>
    <div class="saldo"><span class="etq">Saldo del programa</span><strong>
        <?php echo $resumenPrograma['saldoPendiente']>0?'Bs. '.number_format($resumenPrograma['saldoPendiente'],2):'CANCELADO'; ?>
    </strong></div>
    <?php endif; ?>
    <?php if(count($clinicasPagadas)>0): ?>
    <div><span class="etq">Clínicas pagadas</span><strong>Bs. <?php echo number_format($totalClinicas,2); ?></strong>
        <span class="plan">aparte del programa</span></div>
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
