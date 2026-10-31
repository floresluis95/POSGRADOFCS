<?php
    $Validar = new FuncionesControladores();
    $Validar -> ValidarSessionControlador();
    date_default_timezone_set("America/La_Paz");

    // Pagos de certificados del estudiante que inició sesión (nunca de otro: el ID sale
    // de la sesión). Solo se muestran: no puede ver ni imprimir el certificado ni el recibo.
    require_once 'modelos/certificado.modelo.php';
    $tiposCertificado = CertificadoModelo::TiposParaListado();
    $pagosCertificados = CertificadoModelo::PagosCertificadosEstudianteModelo((int)($_SESSION['EstudianteID'] ?? 0));

    $tieneMonto = function ($pago) { return $pago['Monto'] !== null && $pago['Monto'] !== ''; };
    $totalCertificados = array_sum(array_map(function ($pago) use ($tieneMonto) { return $tieneMonto($pago) ? (float)$pago['Monto'] : 0; }, $pagosCertificados));
    $ultimoPago = $pagosCertificados[0] ?? null;
    // Nombre que se muestra: el del certificado sin documento (ej. PRO BIBLIOTECA), o el del tipo
    $nombreCertificado = function ($pago) use ($tiposCertificado) {
        return $pago['NombreCertificado'] ?: ($tiposCertificado[$pago['TipoDocumento']] ?? $pago['TipoDocumento']);
    };

    // Separados por programa: si el estudiante está en dos programas a la vez (por
    // ejemplo dos maestrías), cada uno con sus certificados y su total
    $porPrograma = [];
    foreach ($pagosCertificados as $pago) {
        $clave = (int)$pago['ProgramaID'];
        if (!isset($porPrograma[$clave])) {
            $porPrograma[$clave] = [
                'programa' => $pago['NombrePrograma'] ?: 'Sin programa',
                'grado' => $pago['GradoAcademico'] ? CertificadoModelo::GradoLegible($pago['GradoAcademico']) : '',
                'codigo' => $pago['Codigo'] ?? '',
                'pagos' => [],
                'total' => 0,
            ];
        }
        $porPrograma[$clave]['pagos'][] = $pago;
        $porPrograma[$clave]['total'] += $tieneMonto($pago) ? (float)$pago['Monto'] : 0;
    }

    function BsMisCertificados($monto) { return 'Bs. ' . number_format((float)$monto, 2, ',', '.'); }
    function FechaMisCertificados($valor) { return $valor ? date('d/m/Y', strtotime($valor)) : '-'; }
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
  <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed " >
    <div class="kt-header-mobile__logo">
      <a href="panel">
         <img alt="<?php echo htmlspecialchars(InstitucionModelo::NombreSistema()); ?>" src="<?php echo InstitucionModelo::LogoUrl('LOGO_SISTEMA'); ?>" height="36" />
      </a>
    </div>
    <div class="kt-header-mobile__toolbar">
      <button class="kt-header-mobile__toolbar-toggler kt-header-mobile__toolbar-toggler--left" id="kt_aside_mobile_toggler"><span></span></button>
      <button class="kt-header-mobile__toolbar-toggler" id="kt_header_mobile_toggler"><span></span></button>
      <button class="kt-header-mobile__toolbar-topbar-toggler" id="kt_header_mobile_topbar_toggler"><i class="flaticon-more-1"></i></button>
    </div>
  </div>

  <div class="kt-grid kt-grid--hor kt-grid--root" style="background:#E0DEDE;">
    <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver kt-page">
      <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-wrapper" id="kt_wrapper">

    <?php
      $NavBar = new FuncionesControladores();
      $NavBar -> NavBarControlador();
    ?>
    <button class="kt-aside-close " id="kt_aside_close_btn"><i class="la la-close"></i></button>
    <?php
      $Sidebar = new FuncionesControladores();
      $Sidebar -> SidebarControlador();
    ?>

                <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
                    <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

                        <div class="kt-subheader kt-grid__item" id="kt_subheader">
                            <div class="kt-container">
                                <div class="kt-subheader__main">
                                    <h2 class="kt-subheader__title">MIS CERTIFICADOS</h2>
                                    <span class="kt-subheader__separator kt-hidden"></span>
                                    <div class="kt-subheader__breadcrumbs">
                                        <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                                        <span class="kt-subheader__breadcrumbs-separator"></span>
                                        <h4>PAGOS DE CERTIFICADOS</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="kt-container kt-grid__item kt-grid__item--fluid">
                          <div class="mc" id="mis-certificados">

                            <?php if (!$pagosCertificados): ?>
                            <div class="mc-tarjeta mc-vacio">
                                <i class="fas fa-file-alt"></i>
                                <p>Todavía no tiene certificados pagados.</p>
                                <small>Los certificados se solicitan y pagan en Secretaría de Posgrado.</small>
                            </div>
                            <?php else: ?>

                            <!-- Resumen: último certificado pagado, cantidad y total -->
                            <div class="mc-resumen">
                                <div class="mc-tarjeta mc-ultimo" id="mc-ultimo">
                                    <span class="mc-etq">Último certificado pagado</span>
                                    <strong class="mc-tipo-grande" data-tipo="<?php echo htmlspecialchars($ultimoPago['TipoDocumento']); ?>">
                                        <?php echo htmlspecialchars($nombreCertificado($ultimoPago)); ?>
                                    </strong>
                                    <span class="mc-detalle">
                                        <?php echo htmlspecialchars($ultimoPago['NombrePrograma'] ?: ''); ?><br>
                                        <?php echo FechaMisCertificados($ultimoPago['FechaEmision']); ?>
                                        · Voucher <?php echo htmlspecialchars($ultimoPago['NumeroVaucher'] ?: '-'); ?>
                                        <?php if ($tieneMonto($ultimoPago)): ?>· <?php echo BsMisCertificados($ultimoPago['Monto']); ?><?php endif; ?>
                                    </span>
                                </div>
                                <div class="mc-tarjeta mc-cifra">
                                    <span class="mc-etq">Certificados pagados</span>
                                    <strong id="mc-cantidad"><?php echo count($pagosCertificados); ?></strong>
                                </div>
                                <div class="mc-tarjeta mc-cifra">
                                    <span class="mc-etq">Total pagado</span>
                                    <strong id="mc-total"><?php echo BsMisCertificados($totalCertificados); ?></strong>
                                </div>
                            </div>

                            <!-- Detalle por programa (solo lectura) -->
                            <?php foreach ($porPrograma as $programaID => $grupo): ?>
                            <div class="mc-tarjeta mc-programa-grupo" data-programa="<?php echo (int)$programaID; ?>">
                                <div class="mc-cabeza">
                                    <h3><i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($grupo['programa']); ?></h3>
                                    <span class="mc-grado"><?php echo htmlspecialchars(trim($grupo['grado'] . ($grupo['codigo'] ? ' · ' . $grupo['codigo'] : ''), ' ·')); ?></span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table mc-tabla">
                                        <thead>
                                            <tr>
                                                <th>Certificado</th>
                                                <th>F. pago</th>
                                                <th>F. registro</th>
                                                <th>N° voucher</th>
                                                <th class="text-right">Monto</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($grupo['pagos'] as $pago): $esUltimo = $pago === $ultimoPago; ?>
                                            <tr<?php echo $esUltimo ? ' class="mc-fila-ultima"' : ''; ?>>
                                                <td data-etq="Certificado"><span class="mc-tipo" data-tipo="<?php echo htmlspecialchars($pago['TipoDocumento']); ?>"><?php echo htmlspecialchars($nombreCertificado($pago)); ?></span>
                                                    <?php if ($esUltimo): ?><span class="mc-marca-ultimo">Último</span><?php endif; ?></td>
                                                <td class="text-nowrap" data-etq="F. pago"><?php echo FechaMisCertificados($pago['FechaPago']); ?></td>
                                                <td class="text-nowrap" data-etq="F. registro"><?php echo FechaMisCertificados($pago['FechaEmision']); ?></td>
                                                <td class="text-nowrap" data-etq="N° voucher"><?php echo htmlspecialchars($pago['NumeroVaucher'] ?: '-'); ?></td>
                                                <td class="text-right text-nowrap mc-monto" data-etq="Monto"><?php echo $tieneMonto($pago) ? BsMisCertificados($pago['Monto']) : '<span class="text-muted">-</span>'; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4">Total del programa</td>
                                                <td class="text-right text-nowrap"><?php echo BsMisCertificados($grupo['total']); ?></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <p class="mc-nota mc-nota-suelta"><i class="fas fa-info-circle"></i> Esta pantalla es solo de consulta. El certificado y su recibo se entregan en Secretaría de Posgrado.</p>
                            <?php endif; ?>

                          </div>
                        </div>

                        <?php
                            $Footer = new FuncionesControladores();
                            $Footer -> FooterResumidoControlador();
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<style>
/* ---- Mis certificados (prefijo mc-): pagos de certificados del estudiante, solo consulta ---- */
.mc { max-width: 1000px; margin: 0 auto; --mc-primario: #1e3c72; --mc-texto: #1f2937; --mc-texto-2: #64748b; --mc-borde: #e3e7ef; }
.mc-tarjeta {
    background: #fff; border: 1px solid var(--mc-borde); border-radius: 14px; margin-bottom: 16px;
    box-shadow: 0 2px 10px rgba(15, 23, 42, .05); overflow: hidden;
}
.mc-etq { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: var(--mc-texto-2); }

/* Resumen */
.mc-resumen { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 16px; }
.mc-resumen .mc-tarjeta { padding: 18px 22px; margin-bottom: 16px; }
.mc-ultimo { border-left: 4px solid var(--mc-primario); }
.mc-tipo-grande { display: block; margin-top: 6px; font-size: 17px; font-weight: 700; color: var(--mc-tipo-color, var(--mc-primario)); }
.mc-detalle { display: block; margin-top: 4px; font-size: 13px; color: var(--mc-texto-2); }
.mc-cifra strong { display: block; margin-top: 6px; font-size: 22px; font-weight: 700; color: var(--mc-texto); font-variant-numeric: tabular-nums; }

/* Tabla */
.mc-cabeza { padding: 16px 22px; border-bottom: 1px solid var(--mc-borde); }
.mc-cabeza h3 { margin: 0; font-size: 15px; font-weight: 700; color: var(--mc-texto); }
.mc-cabeza h3 i { color: var(--mc-primario); margin-right: 6px; }
.mc-cabeza { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 4px 12px; }
.mc-grado { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px; color: var(--mc-texto-2); }
.mc .mc-nota-suelta { background: none; border: 0; padding: 0 4px 16px; }
[data-tipo="SIN_DOCUMENTO"] { --mc-tipo-color: #475569; --mc-tipo-suave: #eef1f5; }
.mc-tabla { margin: 0; font-size: 13px; }
.mc-tabla thead th { background: #f8fafc; color: var(--mc-texto-2); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; border-top: 0; border-bottom: 1px solid var(--mc-borde); white-space: nowrap; padding: 10px 16px; }
.mc-tabla td { padding: 12px 16px; vertical-align: middle; border-color: #eef1f6; color: var(--mc-texto); }
.mc-tabla .mc-monto { font-weight: 600; font-variant-numeric: tabular-nums; }
.mc-tabla .mc-programa { font-size: 12.5px; color: var(--mc-texto-2); }
.mc-tabla tfoot td { background: #f7f8fa; font-weight: 700; border-top: 2px solid #e2e6ee; }
.mc-tabla tr.mc-fila-ultima td:first-child { box-shadow: inset 4px 0 0 var(--mc-primario); }
.mc-tipo { display: inline-block; padding: 3px 10px; border-radius: 999px; background: var(--mc-tipo-suave, #eef2f9); color: var(--mc-tipo-color, var(--mc-primario)); font-size: 11.5px; font-weight: 600; white-space: nowrap; }
.mc-tipo-otro { background: #e3f5f2; color: #0f766e; }
.mc-marca-ultimo { display: inline-block; margin-left: 6px; padding: 2px 8px; border-radius: 999px; background: var(--mc-primario); color: #fff; font-size: 10.5px; font-weight: 700; }
.mc-nota { margin: 0; padding: 12px 22px; font-size: 12.5px; color: var(--mc-texto-2); background: #f8fafc; border-top: 1px solid var(--mc-borde); }
.mc-nota i { color: var(--mc-primario); margin-right: 4px; }

/* Un color por tipo de certificado (los mismos de Registro de documentos) */
[data-tipo="CALIFICACIONES"] { --mc-tipo-color: #1e3c72; --mc-tipo-suave: #eef2f9; }
[data-tipo="HISTORIAL_ACADEMICO"] { --mc-tipo-color: #0f766e; --mc-tipo-suave: #e3f5f2; }
[data-tipo="APROBACION_MONOGRAFIA"] { --mc-tipo-color: #6b21a8; --mc-tipo-suave: #f3e8ff; }
[data-tipo="MEDIO_MAGNETICO"] { --mc-tipo-color: #b45309; --mc-tipo-suave: #fff1dc; }
[data-tipo="REVISION"] { --mc-tipo-color: #be123c; --mc-tipo-suave: #ffe4ea; }

/* Sin pagos */
.mc-vacio { text-align: center; padding: 44px 20px; color: var(--mc-texto-2); }
.mc-vacio i { display: block; font-size: 32px; color: #c3cad8; margin-bottom: 12px; }
.mc-vacio p { margin: 0; font-size: 14.5px; font-weight: 600; color: var(--mc-texto); }
.mc-vacio small { display: block; margin-top: 4px; font-size: 12.5px; }

@media (max-width: 767.98px) {
    .mc-resumen { grid-template-columns: 1fr 1fr; gap: 12px; }
    .mc-ultimo { grid-column: 1 / -1; }
    /* En el celular cada pago es una tarjeta con todos sus datos (sin deslizar de costado) */
    .mc-tabla thead { display: none; }
    .mc-tabla, .mc-tabla tbody, .mc-tabla tfoot, .mc-tabla tr, .mc-tabla td { display: block; width: 100%; }
    .mc-tabla tbody tr { padding: 10px 16px; border-bottom: 1px solid #eef1f6; }
    .mc-tabla tbody td { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 4px 0; border: 0; text-align: right !important; white-space: normal !important; }
    .mc-tabla tbody td::before { content: attr(data-etq); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px; color: var(--mc-texto-2); text-align: left; flex-shrink: 0; }
    .mc-tabla tr.mc-fila-ultima td:first-child { box-shadow: none; }
    .mc-tabla tr.mc-fila-ultima { box-shadow: inset 4px 0 0 var(--mc-primario); }
    /* La marca "Último" no entra junto al nombre: lo indican la franja y la tarjeta de arriba */
    .mc-tabla .mc-marca-ultimo { display: none; }
    .mc-tabla tfoot tr { display: flex; justify-content: space-between; padding: 12px 16px; background: #f7f8fa; }
    .mc-tabla tfoot td { width: auto; padding: 0; border: 0; background: none; }
}
</style>
</body>
