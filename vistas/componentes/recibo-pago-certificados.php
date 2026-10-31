<?php
/**
 * Recibo de pago de certificados, con el mismo formato que el recibo de pago del programa
 * (recibo-pago-modulos.php): hoja carta, poca tinta, cabecera institucional y resumen.
 * Dos modos (desde Registro de documentos):
 *  - Estudiante: ?estudianteID=&programaID=      todos los certificados que pagó en ese programa
 *  - Reporte:    ?desde=&hasta=&tipo=&programaID= certificados pagados con esos filtros
 */
if (session_status() === PHP_SESSION_NONE) session_start();
// Solo Administración (la única que ve Registro de documentos)
if (!isset($_SESSION['Validar']) || $_SESSION['Validar'] !== true || ($_SESSION['Tipo'] ?? '') !== 'ADM') die('Acceso denegado');

require_once __DIR__ . '/../../modelos/certificado.modelo.php';
require_once __DIR__ . '/../../modelos/institucion.modelo.php'; // datos y logos de la institución (encabezado)

date_default_timezone_set("America/La_Paz");
$fechaGeneracion = date('d/m/Y H:i:s');

$fecha = function ($campo) {
    $valor = trim($_GET[$campo] ?? '');
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor) ? $valor : null;
};
$tipos = CertificadoModelo::TiposParaListado();
// Nombre que va en el recibo: el del certificado sin documento, o el del tipo
$nombreCertificado = function ($doc) use ($tipos) { return $doc['NombreCertificado'] ?: ($tipos[$doc['TipoDocumento']] ?? $doc['TipoDocumento']); };
$estudianteID = intval($_GET['estudianteID'] ?? 0);
$programaID = intval($_GET['programaID'] ?? 0);
$modoEstudiante = $estudianteID > 0;

$filtros = $modoEstudiante
    ? ['estudianteID' => $estudianteID, 'programaID' => $programaID ?: null]
    : [
        'desde' => $fecha('desde'),
        'hasta' => $fecha('hasta'),
        'tipo' => array_key_exists($_GET['tipo'] ?? '', $tipos) ? $_GET['tipo'] : null,
        'programaID' => $programaID ?: null,
        'sede' => trim($_GET['sede'] ?? '') ?: null,
        'grado' => array_key_exists($_GET['grado'] ?? '', CertificadoModelo::GradosAcademicos()) ? $_GET['grado'] : null,
    ];

// Sin ningún filtro la pantalla muestra las últimas 100 emisiones: el reporte, lo mismo
$sinFiltros = !$modoEstudiante && !array_filter($filtros);

try {
    $documentos = CertificadoModelo::ListarDocumentosEmitidosModelo(null, $sinFiltros ? 100 : 5000, $filtros);
    // Recibo de algunos pagos (los marcados en Registro de documentos): solo esos y solo si
    // son de este estudiante (ya vienen filtrados por estudiante y programa)
    if ($modoEstudiante && !empty($_GET['ids'])) {
        $ids = array_filter(array_map('intval', explode(',', (string)$_GET['ids'])));
        $documentos = array_values(array_filter($documentos, function ($doc) use ($ids) {
            return in_array((int)$doc['IdDocumento'], $ids, true);
        }));
    }
    // En el recibo van del más antiguo al más reciente
    $documentos = array_reverse($documentos);

    $programa = null;
    if ($programaID) {
        $stmt = Conexion::Conectar()->prepare("SELECT NombrePrograma, Codigo, GradoAcademico FROM programa WHERE ProgramaID = :id");
        $stmt->execute([':id' => $programaID]);
        $programa = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    $estudiante = null;
    if ($modoEstudiante) {
        $stmt = Conexion::Conectar()->prepare("SELECT Nombre, Apaterno, Amaterno, Ci, Complemento, Exp FROM estudiante WHERE EstudianteID = :id");
        $stmt->execute([':id' => $estudianteID]);
        $estudiante = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        if (!$estudiante) die('Estudiante no encontrado');
    }
} catch (PDOException $e) {
    error_log('recibo-pago-certificados.php: ' . $e->getMessage());
    die('No se pudieron obtener los datos');
}

$conMonto = function ($doc) { return $doc['Monto'] !== null && $doc['Monto'] !== ''; };
$totalPagado = array_sum(array_map(function ($doc) use ($conMonto) { return $conMonto($doc) ? (float)$doc['Monto'] : 0; }, $documentos));
$sinMonto = count(array_filter($documentos, function ($doc) use ($conMonto) { return !$conMonto($doc); }));

// Cuántos y cuánto por certificado (para el resumen del reporte): por nombre, así los
// certificados sin documento salen cada uno con el suyo
$porTipo = [];
foreach ($documentos as $doc) {
    $clave = $nombreCertificado($doc);
    $porTipo[$clave] = $porTipo[$clave] ?? ['cantidad' => 0, 'monto' => 0];
    $porTipo[$clave]['cantidad']++;
    $porTipo[$clave]['monto'] += $conMonto($doc) ? (float)$doc['Monto'] : 0;
}

$numeroRecibo = $modoEstudiante
    ? 'RCE-' . str_pad($estudianteID, 6, '0', STR_PAD_LEFT) . '-' . date('Ymd')
    : 'RPC-' . date('Ymd-Hi');

function BsRecibo($monto) { return 'Bs. ' . number_format((float)$monto, 2, ',', '.'); }
function FechaRecibo($valor) { return $valor ? date('d/m/Y', strtotime($valor)) : '-'; }

$nombreEstudiante = $estudiante ? trim($estudiante['Nombre'] . ' ' . $estudiante['Apaterno'] . ' ' . $estudiante['Amaterno']) : '';
$ciEstudiante = $estudiante ? $estudiante['Ci'] . (!empty($estudiante['Complemento']) ? '-' . $estudiante['Complemento'] : '') . ' ' . $estudiante['Exp'] : '';
$periodo = (!empty($filtros['desde']) || !empty($filtros['hasta']))
    ? trim((!empty($filtros['desde']) ? 'Del ' . FechaRecibo($filtros['desde']) : 'Hasta') . (!empty($filtros['hasta']) ? (!empty($filtros['desde']) ? ' al ' : ' ') . FechaRecibo($filtros['hasta']) : ' a la fecha'))
    : ($sinFiltros ? 'Últimas 100 emisiones' : 'Todas las fechas');
?>
<!DOCTYPE html><html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recibo - <?php echo $numeroRecibo; ?></title>

<style>
/* Mismo formato que el recibo de pago del programa: hoja carta con márgenes de 10 mm,
   pensado para imprimir con poca tinta (sin fondos de color, líneas finas y claras). */
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

.etq{display:block;font-size:6.5pt;font-weight:bold;letter-spacing:.3px;text-transform:uppercase;color:#666;margin-bottom:1px}

.info-estudiante{border:1px solid #888;display:grid;grid-template-columns:2fr 1fr;margin-bottom:8px}
.info-estudiante > div{padding:4px 8px}
.info-estudiante > div:nth-child(odd){border-right:1px solid #ddd}
.info-estudiante > div:nth-child(n+3){border-top:1px solid #ddd}
.info-estudiante .nombre{font-size:11pt;font-weight:bold}
.info-estudiante .programa{font-size:9.5pt;font-weight:bold}

.seccion{margin:8px 0}
.seccion-titulo{padding:3px 8px;font-size:8pt;font-weight:bold;letter-spacing:.5px;text-transform:uppercase;background:#ebebeb;color:#111}

.tabla-modulos{width:100%;border-collapse:collapse}
.tabla-modulos th,.tabla-modulos td{padding:3px 8px;font-size:8.5pt;border-bottom:1px solid #e3e3e3;vertical-align:top}
.tabla-modulos th{font-size:6.5pt;font-weight:bold;letter-spacing:.3px;text-transform:uppercase;color:#666;text-align:left;border-bottom:1px solid #999}
.tabla-modulos .num{text-align:right;white-space:nowrap}
.tabla-modulos td.num{font-weight:bold}
.tabla-modulos .n{width:26px;color:#666}
.tabla-modulos .nowrap{white-space:nowrap}
.tabla-modulos small{display:block;color:#666;font-size:7pt}
.total-fila td{font-weight:bold;border-top:1px solid #888;border-bottom:none}
.sin-monto{color:#888;font-weight:normal}

.aviso{padding:4px 8px;border-left:3px solid #111;margin-top:6px;font-size:8.5pt}

.resumen{border:1px solid #888;margin-top:10px;display:flex}
.resumen > div{flex:1;padding:4px 8px;border-right:1px solid #ddd}
.resumen > div:last-child{border-right:none}
.resumen strong{font-size:10pt}
.resumen .total strong{font-size:11pt}
.resumen .plan{display:block;font-size:7pt;color:#555}

.footer{margin-top:12px;padding-top:5px;border-top:1px solid #ddd;text-align:center;font-size:7.5pt;line-height:1.3;color:#666}

.btn-imprimir{position:fixed;bottom:15px;right:15px;background:#1e3c72;color:#fff;border:none;padding:10px 20px;border-radius:20px;font-size:11pt;cursor:pointer}

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
    <h2><?php echo $modoEstudiante ? 'RECIBO DE PAGO DE CERTIFICADOS' : 'REPORTE DE PAGOS DE CERTIFICADOS'; ?></h2>
    <div class="datos-recibo">
        <span class="numero-recibo">N° <?php echo $numeroRecibo; ?></span>
        Emisión: <?php echo $fechaGeneracion; ?>
    </div>
</div>

<?php if ($modoEstudiante): ?>
<div class="info-estudiante">
    <div><span class="etq">Estudiante</span><span class="nombre"><?php echo htmlspecialchars($nombreEstudiante); ?></span></div>
    <div><span class="etq">C.I.</span><strong><?php echo htmlspecialchars($ciEstudiante); ?></strong></div>
    <div><span class="etq">Programa</span><span class="programa"><?php echo htmlspecialchars($programa['NombrePrograma'] ?? '-'); ?></span></div>
    <div><span class="etq">Grado · Código</span><?php echo htmlspecialchars($programa['GradoAcademico'] ?? '-'); ?> · <strong><?php echo htmlspecialchars($programa['Codigo'] ?? '-'); ?></strong></div>
</div>
<?php else: ?>
<div class="info-estudiante">
    <div><span class="etq">Programa</span><span class="programa"><?php echo htmlspecialchars($programa['NombrePrograma'] ?? 'Todos los programas'); ?></span></div>
    <div><span class="etq">Periodo</span><strong><?php echo htmlspecialchars($periodo); ?></strong></div>
    <div><span class="etq">Certificado</span><strong><?php echo htmlspecialchars($filtros['tipo'] ? $tipos[$filtros['tipo']] : 'Todos los certificados'); ?></strong></div>
    <div><span class="etq">Sede · Grado</span><strong><?php
        echo htmlspecialchars(($filtros['sede'] ?: 'Todas las sedes') . ' · '
            . ($filtros['grado'] ? CertificadoModelo::GradosAcademicos()[$filtros['grado']] : 'Todos los grados')); ?></strong></div>
</div>
<?php endif; ?>

<?php if (!$documentos): ?>
<div class="aviso">No hay certificados emitidos con estos datos.</div>
<?php else: ?>
<div class="seccion">
<div class="seccion-titulo">Certificados pagados (<?php echo count($documentos); ?>)</div>
<table class="tabla-modulos"><thead>
<tr>
    <th class="n">N°</th>
    <th>Certificado</th>
    <?php if (!$modoEstudiante): ?><th>Estudiante</th><?php endif; ?>
    <th>F. pago</th>
    <th>F. registro</th>
    <th>Voucher</th>
    <th class="num">Monto</th>
</tr>
</thead><tbody>
<?php foreach ($documentos as $i => $doc): ?>
<tr>
    <td class="n"><?php echo $i + 1; ?></td>
    <td><?php echo htmlspecialchars($nombreCertificado($doc)); ?>
        <?php if (!$modoEstudiante && !$programaID && !empty($doc['NombrePrograma'])): ?><small><?php echo htmlspecialchars($doc['NombrePrograma']); ?></small><?php endif; ?></td>
    <?php if (!$modoEstudiante): ?>
    <td><?php echo htmlspecialchars($doc['NombreEstudiante'] ?: '-'); ?><?php if ($doc['Ci']): ?><small>C.I. <?php echo htmlspecialchars($doc['Ci']); ?></small><?php endif; ?></td>
    <?php endif; ?>
    <td class="nowrap"><?php echo FechaRecibo($doc['FechaPago']); ?></td>
    <td class="nowrap"><?php echo FechaRecibo($doc['FechaEmision']); ?></td>
    <td class="nowrap"><?php echo htmlspecialchars($doc['NumeroVaucher'] ?: '-'); ?></td>
    <td class="num"><?php echo $conMonto($doc) ? BsRecibo($doc['Monto']) : '<span class="sin-monto">Sin monto</span>'; ?></td>
</tr>
<?php endforeach; ?>
<tr class="total-fila">
    <td colspan="<?php echo $modoEstudiante ? 5 : 6; ?>">Total pagado</td>
    <td class="num"><?php echo BsRecibo($totalPagado); ?></td>
</tr>
</tbody></table>
</div>

<?php if (!$modoEstudiante && count($porTipo) > 1): ?>
<div class="seccion">
<div class="seccion-titulo">Resumen por certificado</div>
<table class="tabla-modulos"><thead>
<tr><th>Certificado</th><th class="num">Cantidad</th><th class="num">Monto</th></tr>
</thead><tbody>
<?php foreach ($porTipo as $clave => $resumen): ?>
<tr>
    <td><?php echo htmlspecialchars($clave); ?></td>
    <td class="num"><?php echo $resumen['cantidad']; ?></td>
    <td class="num"><?php echo BsRecibo($resumen['monto']); ?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div>
<?php endif; ?>
<?php endif; ?>

<div class="resumen">
    <div><span class="etq">Certificados</span><strong><?php echo count($documentos); ?></strong>
        <?php if ($sinMonto): ?><span class="plan"><?php echo $sinMonto; ?> sin monto registrado</span><?php endif; ?></div>
    <?php if ($modoEstudiante): ?>
    <div><span class="etq">Programa</span><strong><?php echo htmlspecialchars($programa['Codigo'] ?? '-'); ?></strong></div>
    <?php else: ?>
    <div><span class="etq">Estudiantes</span><strong><?php echo count(array_unique(array_column($documentos, 'EstudianteID'))); ?></strong></div>
    <?php endif; ?>
    <div class="total"><span class="etq">Total pagado</span><strong><?php echo BsRecibo($totalPagado); ?></strong></div>
</div>

<div class="footer">
<p><strong><?php echo $modoEstudiante ? 'Este recibo es válido como comprobante de pago de certificados.' : 'Reporte de pagos de certificados.'; ?></strong> Sistema de Gestión Académica</p>
<p style="color:#999">Generado el <?php echo $fechaGeneracion; ?> | Usuario: <?php echo htmlspecialchars($_SESSION['Usuario'] ?? ''); ?></p>
</div>

</div></div>

<?php if ($modoEstudiante): // el reporte puede ocupar varias hojas: no se achica ?>
<script>
// Si el recibo no entra en una hoja carta, se reduce proporcionalmente para que entre
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
<?php endif; ?>
</body></html>
