<?php
    $Validar = new FuncionesControladores();
    $Validar -> ValidarSessionControlador();
    date_default_timezone_set("America/La_Paz");
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
    <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed ">
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
                                    <h2 class="kt-subheader__title">
                                        MIS ÓRDENES DE PAGO
                                    </h2>
                                    <span class="kt-subheader__separator kt-hidden"></span>
                                    <div class="kt-subheader__breadcrumbs">
                                        <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                                        <span class="kt-subheader__breadcrumbs-separator"></span>
                                        <h4>
                                            ACCESO ESTUDIANTIL
                                        </h4>
                                    </div>
                                </div>
                                <div class="kt-subheader__toolbar">
                                    <div class="kt-subheader__wrapper">
                                        <div id="lafecha"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="kt-container kt-grid__item kt-grid__item--fluid">
                            <div class="mop-card">
                                <div class="mop-cab">
                                    <div>
                                        <h3><i class="fas fa-file-invoice"></i> Mis órdenes de pago</h3>
                                        <p>Matrícula, cuotas del programa y clínicas.</p>
                                    </div>
                                    <!-- Lo pagado, sus comprobantes y el recibo están en Historial de pagos -->
                                    <a href="historialmodulosestudiante" class="btn mop-btn-recibo" id="btn_historial_pagos">
                                        <i class="fas fa-wallet"></i> Mis pagos y recibo
                                    </a>
                                </div>

                                <div class="mop-aviso">
                                    <i class="fas fa-university"></i>
                                    <span>Imprima su orden de pago y preséntela en <strong>Tesoro Universitario</strong> para realizar el pago.</span>
                                </div>

                                <div class="mop-cuerpo">
                                    <div id="ordenes_cargando" class="text-center" style="padding: 3rem;">
                                        <div class="kt-spinner kt-spinner--lg kt-spinner--brand"></div>
                                        <p class="mt-3 text-muted">Cargando sus órdenes de pago...</p>
                                    </div>

                                    <div id="ordenes_vacio" class="mop-vacio" style="display:none;">
                                        <i class="flaticon2-file"></i>
                                        <p>Todavía no tiene órdenes de pago generadas.</p>
                                    </div>

                                    <!-- Siguiente cuota: el estudiante genera su orden (solo si no tiene una por pagar) -->
                                    <div id="generar_cuota" style="display:none;">
                                        <div class="mop-seccion-titulo">Su siguiente cuota</div>
                                        <div class="mop-por-pagar" id="lista_generar_cuota"></div>
                                    </div>

                                    <!-- Lo que falta pagar: arriba y destacado -->
                                    <div id="por_pagar" style="display:none;">
                                        <div class="mop-seccion-titulo">Por pagar</div>
                                        <div class="mop-por-pagar" id="lista_por_pagar"></div>
                                    </div>

                                    <!-- Matrícula: aparte de la tabla (no entra en la numeración de cuotas y clínicas) -->
                                    <div id="matricula" style="display:none;">
                                        <div class="mop-seccion-titulo">Matrícula</div>
                                        <div class="table-responsive mb-3">
                                            <table class="table mop-tabla mop-resp" id="tabla_matricula">
                                                <thead>
                                                    <tr>
                                                        <th>N° de orden</th>
                                                        <th>Programa</th>
                                                        <th>Concepto</th>
                                                        <th class="text-right">Monto</th>
                                                        <th>Generada</th>
                                                        <th>Fecha límite</th>
                                                        <th class="text-center">Estado</th>
                                                        <th class="text-center">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="contenido_matricula"></tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="mop-seccion-titulo" id="titulo_todas" style="display:none;">Cuotas y clínicas</div>
                                    <div class="mop-filtros" id="ordenes_filtros" style="display:none;">
                                        <div class="mop-filtro">
                                            <label for="filtro_programa">Programa</label>
                                            <select class="form-control" id="filtro_programa">
                                                <option value="">Todos los programas</option>
                                            </select>
                                        </div>
                                        <div class="mop-filtro mop-filtro-tipo">
                                            <label for="filtro_tipo">Concepto</label>
                                            <select class="form-control" id="filtro_tipo">
                                                <option value="">Todos</option>
                                            </select>
                                        </div>
                                        <div class="mop-filtro-boton">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn_ver_anuladas" style="display:none;" data-toggle="modal" data-target="#modalOrdenesAnuladas">
                                                <i class="fa fa-ban"></i> Anuladas (<span id="conteo_anuladas">0</span>)
                                            </button>
                                        </div>
                                    </div>

                                    <div class="table-responsive" id="ordenes_tabla_wrap" style="display:none;">
                                        <table class="table mop-tabla mop-resp" id="tabla_ordenes_pago">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">N°</th>
                                                    <th>N° de orden</th>
                                                    <th>Programa</th>
                                                    <th>Concepto</th>
                                                    <th class="text-right">Monto</th>
                                                    <th>Generada</th>
                                                    <th>Fecha límite</th>
                                                    <th class="text-center">Estado</th>
                                                    <th class="text-center">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody id="contenido_ordenes_pago"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal: órdenes anuladas (fuera de la lista principal, solo consulta) -->
                        <div class="modal fade" id="modalOrdenesAnuladas" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header mop-modal-cab">
                                        <div>
                                            <small>Solo como referencia: no tienen validez para el pago</small>
                                            <h5 class="modal-title">Órdenes de pago anuladas</h5>
                                        </div>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm mop-tabla-anuladas mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>N° de orden</th>
                                                        <th>Programa</th>
                                                        <th>Concepto</th>
                                                        <th class="text-right">Monto</th>
                                                        <th>Generada</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="contenido_ordenes_anuladas"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                    </div>
                                </div>
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

    <script src="vistas/recursos/assets/js/scripts/misordenespago.js?v=<?php echo filemtime(__DIR__ . '/../recursos/assets/js/scripts/misordenespago.js'); ?>"></script>

    <style>
        /* Mis órdenes de pago: vista sobria con el azul institucional */
        .mop-card { background: #fff; border: 1px solid #e3e7ef; border-radius: 12px; box-shadow: 0 2px 10px rgba(20, 40, 80, .05); overflow: hidden; margin-bottom: 24px; }
        .mop-cab { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; padding: 18px 24px; background: #1e3c72; border-bottom: 3px solid #d9a941; }
        .mop-cab h3 { margin: 0; color: #fff; font-size: 17px; font-weight: 700; }
        .mop-cab p { margin: 3px 0 0; color: #c9d5ea; font-size: 12.5px; }
        .mop-btn-recibo { background: #fff; color: #1e3c72; font-weight: 600; border-radius: 8px; padding: 7px 16px; }
        .mop-btn-recibo:hover, .mop-btn-recibo:focus { background: #eef2f9; color: #1e3c72; }
        .mop-aviso { display: flex; align-items: center; gap: 10px; padding: 12px 24px; background: #f8fafc; border-bottom: 1px solid #e3e7ef; color: #334155; font-size: 13px; }
        .mop-aviso i { color: #1e3c72; font-size: 16px; }
        .mop-cuerpo { padding: 20px 24px 24px; }
        .mop-vacio { text-align: center; padding: 3rem 1rem; color: #94a3b8; }
        .mop-vacio i { font-size: 3rem; color: #e2e8f0; }
        .mop-vacio p { margin-top: 1rem; font-size: 1rem; font-weight: 500; }

        /* Por pagar: una tarjeta por orden, con monto, fecha límite y botón para imprimirla */
        .mop-seccion-titulo { font-size: 11.5px; font-weight: 700; color: #1e3c72; text-transform: uppercase; letter-spacing: .5px; margin: 0 0 8px; }
        .mop-por-pagar { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 8px; margin-bottom: 16px; }
        .mop-pp { border: 1px solid #e3e7ef; border-left: 3px solid #d9a941; border-radius: 8px; padding: 8px 12px; background: #fff; display: flex; flex-direction: column; gap: 2px; font-size: 12px; }
        .mop-pp.is-vencida { border-left-color: #b91c1c; }
        .mop-pp.is-espera { border-left-color: #1e3c72; }
        .mop-pp-cab { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
        .mop-pp-concepto { font-weight: 700; color: #1f2937; font-size: 12.5px; }
        .mop-pp-monto { font-size: 1rem; font-weight: 700; color: #1e3c72; font-variant-numeric: tabular-nums; }
        .mop-pp small { color: #64748b; font-size: 12px; }
        .mop-pp .btn-imprimir-orden { align-self: flex-start; margin-top: 4px; }
        .mop-pp.is-nueva { border-left-color: #1e7145; background: #fbfefc; }
        .btn-generar-cuota { align-self: flex-start; margin-top: 4px; display: inline-flex; align-items: center; gap: 6px; border-radius: 8px; padding: 6px 14px; font-size: 12.5px; font-weight: 600; background: #1e7145; color: #fff; border: 1px solid #1e7145; }
        .btn-generar-cuota:hover { background: #17603a; color: #fff; }
        .btn-generar-cuota:disabled { opacity: .6; }
        /* Ventana "Generar orden de pago": detalle por cuota y total */
        .mop-detalle-cuotas { width: 100%; margin-top: 10px; font-size: 13.5px; border: 1px solid #e3e7ef; border-radius: 8px; border-collapse: separate; border-spacing: 0; overflow: hidden; }
        .mop-detalle-cuotas td { padding: 6px 12px; text-align: left; border-bottom: 1px solid #eef1f6; color: #1f2937; }
        .mop-detalle-cuotas td.text-right { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
        .mop-detalle-cuotas .mop-detalle-total td { background: #f4f7fc; border-bottom: 0; font-weight: 700; color: #1e3c72; font-size: 15px; }

        .mop-filtros { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px; margin-bottom: 16px; }
        .mop-filtro { flex: 1 1 260px; max-width: 420px; }
        .mop-filtro-tipo { flex: 0 1 220px; }
        .mop-filtro label { display: block; font-size: 11.5px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 5px; }
        .mop-filtro .form-control { height: 40px; border-radius: 8px; border-color: #d8dde8; }
        .mop-filtro-boton { margin-left: auto; }

        .mop-tabla { margin: 0; font-size: 13px; }
        .mop-tabla thead th { background: #f8fafc; color: #475569; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; border-top: 0; border-bottom: 1px solid #e3e7ef; white-space: nowrap; }
        .mop-tabla td { vertical-align: middle; border-color: #eef1f6; color: #1f2937; }
        .mop-tabla td small { color: #64748b; }
        .mop-tabla td.mop-numero { color: #64748b; font-weight: 600; width: 44px; }
        .mop-tabla td[data-label="Monto"], .mop-tabla td[data-label="Generada"], .mop-tabla td[data-label="Fecha límite"] { white-space: nowrap; }
        .mop-tabla td[data-label="Monto"] { font-weight: 700; font-variant-numeric: tabular-nums; }

        /* Estados: discretos y con el texto completo aunque la pantalla sea angosta */
        .badge-orden { display: inline-block; padding: 4px 11px; border-radius: 999px; font-size: 11.5px; font-weight: 600; line-height: 1.35; white-space: normal; text-align: center; max-width: 150px; }
        .badge-pendiente { background: #fff7e6; color: #8a5a00; border: 1px solid #f0d9a6; }
        .badge-confirmado { background: #ecfdf3; color: #1e7145; border: 1px solid #b7e4c7; }
        .badge-extraviado { background: #eef2f9; color: #1e3c72; border: 1px solid #c9d6ee; }
        .badge-anulado { background: #f1f3f7; color: #64748b; border: 1px solid #e3e7ef; }
        .badge-vencido { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

        .mop-acciones { display: inline-flex; gap: 6px; flex-wrap: nowrap; }
        .btn-imprimir-orden { display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; border-radius: 8px; padding: 5px 12px; font-size: 12px; font-weight: 600; }
        .btn-imprimir-orden { background: #1e3c72; color: #fff; border: 1px solid #1e3c72; }
        .btn-imprimir-orden:hover { background: #16305c; color: #fff; }
        /* Orden ya pagada: botón claro (al pulsarlo se avisa que ya se pagó) */
        .btn-imprimir-orden[data-pagada] { background: #fff; color: #1e3c72; border-color: #c9d5ea; }
        .btn-imprimir-orden[data-pagada]:hover { background: #eef2f9; color: #1e3c72; }

        .mop-modal-cab { background: #1e3c72; color: #fff; border-bottom: 3px solid #d9a941; }
        .mop-modal-cab small { color: #c9d5ea; font-size: 11.5px; }
        .mop-modal-cab .modal-title { color: #fff !important; font-weight: 700; }
        .mop-modal-cab .close { color: #fff; opacity: .85; text-shadow: none; }
        .mop-tabla-anuladas th { font-size: 11px; text-transform: uppercase; color: #64748b; background: #f8fafc; }

        /* Tablet y celular: cada orden es una tarjeta, con la etiqueta de cada dato a la
           izquierda; nada se corta ni hay que desplazarse de lado */
        @media (max-width: 767px) {
            .mop-cab, .mop-aviso { padding-left: 16px; padding-right: 16px; }
            .mop-cuerpo { padding: 14px 12px 16px; }
            .mop-filtro, .mop-filtro-tipo { flex: 1 1 100%; max-width: none; }
            .mop-filtro-boton { margin-left: 0; }
            #btn_historial_pagos { width: 100%; }
            .mop-por-pagar { grid-template-columns: 1fr; }
            .mop-resp thead { display: none; }
            .mop-resp, .mop-resp tbody, .mop-resp tr, .mop-resp td { display: block; width: 100%; }
            .mop-resp tr { background: #fff; border: 1px solid #e3e7ef; border-radius: 10px; margin-bottom: 12px; padding: 4px 14px 12px; }
            .mop-resp tbody td { display: flex; justify-content: space-between; align-items: center; gap: 12px; border: none; border-bottom: 1px solid #f0f1f5; padding: 8px 0; text-align: right !important; min-width: 0; }
            .mop-resp tbody td > * { min-width: 0; overflow-wrap: anywhere; }
            .mop-resp tbody td::before { content: attr(data-label); flex-shrink: 0; font-size: 11.5px; font-weight: 600; color: #64748b; text-align: left; }
            .mop-resp .badge-orden { max-width: 60%; }
            .mop-resp tbody td[colspan] { display: block; text-align: center !important; }
            .mop-resp tbody td[colspan]::before, .mop-resp td.celda-accion::before { content: none; }
            .mop-resp td.celda-accion { border-bottom: none; padding-top: 12px; }
            .mop-resp td.celda-accion .mop-acciones { width: 100%; }
            .mop-resp td.celda-accion .mop-acciones > * { flex: 1; justify-content: center; padding: 9px 12px; }
        }
    </style>

    </body>
