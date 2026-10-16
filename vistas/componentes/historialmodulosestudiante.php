<?php
    $Validar = new FuncionesControladores();
    $Validar -> ValidarSessionControlador();
    date_default_timezone_set("America/La_Paz");
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
    <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed ">
        <div class="kt-header-mobile__logo">
            <a href="panel">
            <img alt="Posgrado Odontología" src="vistas/recursos/assets/media/logos/logo.png" height="36" />
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
                                        HISTORIAL DE PAGOS
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
                            <div class="hp-card">
                                <div class="hp-cab">
                                    <div>
                                        <h3><i class="fas fa-wallet"></i> Estado de cuenta</h3>
                                        <p>Lo pagado y el saldo de su programa, con el comprobante de cada pago.</p>
                                    </div>
                                    <a href="#" target="_blank" class="btn hp-btn-recibo" id="btn_recibo_pagos" style="display:none;">
                                        <i class="fa fa-print"></i> Imprimir recibo de pagos
                                    </a>
                                </div>

                                <div class="hp-cuerpo">
                                    <!-- Selector de programa (si tiene uno solo, se elige solo) -->
                                    <div class="hp-programa" id="grupo_programa">
                                        <label for="select_programa_modulos">Programa</label>
                                        <select class="form-control" id="select_programa_modulos" name="select_programa_modulos" disabled>
                                            <option value="">Cargando programas...</option>
                                        </select>
                                    </div>

                                    <div id="aviso_elegir_programa" class="hp-vacio" style="display: none;">
                                        <i class="flaticon2-layers"></i>
                                        <p>Elija un programa para ver su estado de cuenta.</p>
                                    </div>

                                    <div id="area_resultados_modulos" style="display: none;">

                                        <!-- Resumen: total, pagado y saldo con su avance -->
                                        <div class="hp-resumen">
                                            <div class="hp-dato">
                                                <span>Total del programa</span>
                                                <strong id="total_programa">Bs. 0,00</strong>
                                                <small id="plan_texto"></small>
                                            </div>
                                            <div class="hp-dato is-pagado">
                                                <span>Pagado</span>
                                                <strong id="total_pagado">Bs. 0,00</strong>
                                                <small><span id="cantidad_pagados">0</span> cuota(s)</small>
                                            </div>
                                            <div class="hp-dato is-saldo">
                                                <span>Saldo</span>
                                                <strong id="total_pendiente">Bs. 0,00</strong>
                                                <small id="estado_plan_texto"></small>
                                            </div>
                                        </div>
                                        <div class="hp-avance">
                                            <div class="progress hp-barra" title="Avance del pago">
                                                <div class="progress-bar" id="barra_avance" role="progressbar" style="width: 0%;"></div>
                                            </div>
                                            <span><strong id="porcentaje_avance">0%</strong> pagado</span>
                                        </div>

                                        <!-- Si hay órdenes por pagar se avisa y se manda a Mis órdenes de pago -->
                                        <a href="misordenespago" class="hp-por-pagar" id="aviso_por_pagar" style="display:none;"></a>

                                        <!-- Órdenes aún no validadas: por pagar o con voucher por validar (cuotas y clínicas) -->
                                        <div id="seccion_sin_validar" style="display:none;">
                                            <div class="hp-seccion-titulo">Órdenes aún no validadas <span class="hp-orden-nota">· De la última orden generada a la primera</span></div>
                                            <div class="table-responsive mb-3">
                                                <table class="table hp-tabla hp-resp" id="tabla_sin_validar">
                                                    <thead>
                                                        <tr>
                                                            <th>N° de orden</th>
                                                            <th>Concepto</th>
                                                            <th class="text-right">Monto</th>
                                                            <th>Generada</th>
                                                            <th>Fecha límite</th>
                                                            <th class="text-center">Estado</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tbody_sin_validar"></tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- Matrícula: aparte, sin numerar -->
                                        <div id="seccion_matricula" style="display:none;">
                                            <div class="hp-seccion-titulo">Matrícula</div>
                                            <div class="table-responsive mb-3">
                                                <table class="table hp-tabla hp-resp" id="tabla_matricula">
                                                    <thead>
                                                        <tr>
                                                            <th>Fecha de pago</th>
                                                            <th>Concepto</th>
                                                            <th>N° de voucher</th>
                                                            <th class="text-right">Monto</th>
                                                            <th class="text-center">Comprobante</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tbody_matricula"></tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- Pagos realizados: cuotas y clínicas, numerados (el último pago arriba) -->
                                        <div class="hp-seccion-titulo">Pagos realizados <span class="hp-orden-nota">· Del último pago realizado al primero</span></div>
                                        <div class="table-responsive">
                                            <table class="table hp-tabla hp-resp" id="tabla_pagos_realizados">
                                                <thead>
                                                    <tr>
                                                        <th>Fecha de pago</th>
                                                        <th>Concepto</th>
                                                        <th>N° de voucher</th>
                                                        <th class="text-right">Monto</th>
                                                        <th class="text-center">Comprobante</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tbody_pagos"></tbody>
                                            </table>
                                        </div>
                                        <p class="hp-nota">Las clínicas se pagan aparte y no cuentan para el saldo del programa.</p>
                                    </div>
                                </div>
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

    <!-- Modal: comprobante (voucher) de un pago -->
    <div class="modal fade" id="modalFotoRecibo" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header hp-modal-cab">
                    <div>
                        <small>Comprobante de pago</small>
                        <h5 class="modal-title" id="comprobante_titulo"></h5>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="hp-comprobante-datos" id="comprobante_datos"></div>
                    <div class="hp-comprobante-imagen">
                        <img id="imagen_recibo_grande" src="" alt="Comprobante de pago">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Historial de pagos (estado de cuenta): mismo estilo sobrio que Mis órdenes de pago */
        .hp-card { background: #fff; border: 1px solid #e3e7ef; border-radius: 12px; box-shadow: 0 2px 10px rgba(20, 40, 80, .05); overflow: hidden; margin-bottom: 24px; }
        .hp-cab { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; padding: 18px 24px; background: #1e3c72; border-bottom: 3px solid #d9a941; }
        .hp-cab h3 { margin: 0; color: #fff; font-size: 17px; font-weight: 700; }
        .hp-cab p { margin: 3px 0 0; color: #c9d5ea; font-size: 12.5px; }
        .hp-btn-recibo { background: #fff; color: #1e3c72; font-weight: 600; border-radius: 8px; padding: 7px 16px; }
        .hp-btn-recibo:hover { background: #eef2f9; color: #1e3c72; }
        .hp-cuerpo { padding: 20px 24px 24px; }
        .hp-programa { max-width: 520px; margin-bottom: 18px; }
        .hp-programa label { display: block; font-size: 11.5px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 5px; }
        .hp-programa .form-control { height: 40px; border-radius: 8px; border-color: #d8dde8; }
        .hp-vacio { text-align: center; padding: 2.5rem 1rem; color: #94a3b8; }
        .hp-vacio i { font-size: 2.5rem; color: #e2e8f0; }
        .hp-vacio p { margin: .75rem 0 0; font-weight: 500; }

        .hp-resumen { display: grid; grid-template-columns: repeat(3, 1fr); border: 1px solid #e3e7ef; border-radius: 10px; overflow: hidden; }
        .hp-dato { display: flex; flex-direction: column; padding: 14px 18px; border-right: 1px solid #e3e7ef; min-width: 0; }
        .hp-dato:last-child { border-right: 0; }
        .hp-dato span { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .4px; }
        .hp-dato strong { font-size: 1.3rem; color: #1f2937; white-space: nowrap; font-variant-numeric: tabular-nums; margin: 2px 0; }
        .hp-dato small { color: #64748b; font-size: 12px; }
        .hp-dato.is-pagado strong { color: #1e7145; }
        .hp-dato.is-saldo strong { color: #b91c1c; }
        .hp-dato.is-saldo.is-al-dia strong { color: #1e7145; }
        .hp-avance { display: flex; align-items: center; gap: 12px; margin: 12px 0 18px; font-size: 12.5px; color: #475569; white-space: nowrap; }
        .hp-barra { flex: 1; height: 8px; border-radius: 4px; background: #eef0f5; margin: 0; }
        .hp-barra .progress-bar { background: #1e7145; border-radius: 4px; }

        .hp-por-pagar { display: flex; align-items: center; gap: 10px; padding: 11px 16px; margin-bottom: 18px; border-radius: 10px; background: #fff7e6; border: 1px solid #f0d9a6; color: #8a5a00; font-size: 13px; font-weight: 600; }
        .hp-por-pagar:hover { color: #6b4500; text-decoration: none; background: #fff1d6; }
        .hp-por-pagar .hp-ir { margin-left: auto; white-space: nowrap; }

        .hp-seccion-titulo { font-size: 11.5px; font-weight: 700; color: #1e3c72; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 8px; }
        .hp-tabla { margin: 0; font-size: 13px; }
        .hp-tabla thead th { background: #f8fafc; color: #475569; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; border-top: 0; border-bottom: 1px solid #e3e7ef; white-space: nowrap; }
        .hp-tabla td { vertical-align: middle; border-color: #eef1f6; color: #1f2937; }
        .hp-tabla td small { color: #64748b; }
        .hp-tabla td[data-label="Monto"] { font-weight: 700; white-space: nowrap; font-variant-numeric: tabular-nums; }
        .hp-tabla td[data-label="Fecha de pago"] { white-space: nowrap; }
        .hp-concepto-clinica { color: #6b4e0e; }
        .hp-orden-nota { font-weight: 500; color: #64748b; text-transform: none; letter-spacing: 0; }
        /* N° de cuota destacado en el concepto */
        .hp-cuota { display: inline-block; padding: 2px 10px; border-radius: 6px; background: #eef2f9; color: #1e3c72; font-weight: 700; white-space: nowrap; }
        .hp-cuota small { color: #64748b; font-weight: 600; }
        .hp-tabla td[data-label="Generada"], .hp-tabla td[data-label="Fecha límite"] { white-space: nowrap; }
        .hp-estado { display: inline-block; padding: 4px 11px; border-radius: 999px; font-size: 11.5px; font-weight: 600; line-height: 1.35; white-space: normal; text-align: center; }
        .hp-estado.is-pendiente { background: #fff7e6; color: #8a5a00; border: 1px solid #f0d9a6; }
        .hp-estado.is-vencida { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .hp-estado.is-por-validar { background: #eef2f9; color: #1e3c72; border: 1px solid #c9d6ee; }
        .btn-comprobante { display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; border-radius: 8px; padding: 5px 12px; font-size: 12px; font-weight: 600; background: #fff; color: #1e3c72; border: 1px solid #c9d5ea; }
        .btn-comprobante:hover { background: #eef2f9; color: #1e3c72; text-decoration: none; }
        .hp-nota { margin: 10px 2px 0; font-size: 12px; color: #64748b; }

        .hp-modal-cab { background: #1e3c72; color: #fff; border-bottom: 3px solid #d9a941; }
        .hp-modal-cab small { color: #c9d5ea; font-size: 11.5px; }
        .hp-modal-cab .modal-title { color: #fff !important; font-weight: 700; }
        .hp-modal-cab .close { color: #fff; opacity: .85; text-shadow: none; }
        .hp-comprobante-datos { display: flex; flex-wrap: wrap; gap: 8px 24px; margin-bottom: 14px; font-size: 13px; color: #475569; }
        .hp-comprobante-datos strong { color: #1f2937; }
        .hp-comprobante-imagen { text-align: center; background: #f1f3f7; border-radius: 10px; padding: 12px; }
        .hp-comprobante-imagen img { max-width: 100%; max-height: 70vh; border-radius: 6px; }

        /* Celular: resumen en una columna y cada pago como tarjeta */
        @media (max-width: 767px) {
            .hp-cab { padding: 16px; }
            .hp-cuerpo { padding: 14px 12px 16px; }
            .hp-btn-recibo { width: 100%; text-align: center; }
            .hp-resumen { grid-template-columns: 1fr; }
            .hp-dato { border-right: 0; border-bottom: 1px solid #e3e7ef; flex-direction: row; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 2px 10px; }
            .hp-dato:last-child { border-bottom: 0; }
            .hp-dato strong { font-size: 1.1rem; }
            .hp-dato small { width: 100%; text-align: right; }
            .hp-resp thead { display: none; }
            .hp-resp, .hp-resp tbody, .hp-resp tr, .hp-resp td { display: block; width: 100%; }
            .hp-resp tr { border: 1px solid #e3e7ef; border-radius: 10px; margin-bottom: 10px; padding: 2px 14px 10px; }
            .hp-resp td { display: flex; justify-content: space-between; align-items: center; gap: 12px; border: none; border-bottom: 1px solid #f0f1f5; padding: 8px 0; text-align: right !important; }
            .hp-resp td::before { content: attr(data-label); flex-shrink: 0; font-size: 11.5px; font-weight: 600; color: #64748b; text-align: left; }
            .hp-resp td[colspan] { display: block; text-align: center !important; border: none; }
            .hp-resp td[colspan]::before { content: none; }
            .hp-resp td:last-child { border-bottom: none; }
        }
    </style>

    <script>
        // El script se carga cuando jQuery ya está disponible
        window.addEventListener('load', function() {
            if (typeof jQuery === 'undefined') {
                console.error('jQuery no está cargado!');
                return;
            }
            var script = document.createElement('script');
            script.src = 'vistas/recursos/assets/js/scripts/historial-modulos-estudiante.js?v=<?php echo filemtime(__DIR__ . '/../recursos/assets/js/scripts/historial-modulos-estudiante.js'); ?>';
            document.body.appendChild(script);
        });
    </script>
</body>
