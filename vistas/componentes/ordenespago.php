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
                                    <h2 class="kt-subheader__title">ÓRDENES DE PAGO</h2>
                                    <span class="kt-subheader__separator kt-hidden"></span>
                                    <div class="kt-subheader__breadcrumbs">
                                        <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                                        <span class="kt-subheader__breadcrumbs-separator"></span>
                                        <h4>GESTIÓN DE PAGOS</h4>
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

                            <!-- Contadores (según los filtros aplicados) -->
                            <div class="op-contadores" id="op_contadores">
                                <div class="op-contador"><span>Órdenes</span><strong id="op_total">0</strong></div>
                                <div class="op-contador op-c-pendiente"><span>Pendientes</span><strong id="op_pendientes">0</strong></div>
                                <div class="op-contador op-c-vencida"><span>Vencidas</span><strong id="op_vencidas">0</strong></div>
                                <div class="op-contador op-c-pagada"><span>Pagadas</span><strong id="op_pagadas">0</strong></div>
                                <div class="op-contador op-c-anulada"><span>Anuladas</span><strong id="op_anuladas">0</strong></div>
                            </div>

                            <div class="op-tarjeta">
                                <!-- Filtros -->
                                <div class="op-filtros">
                                    <div class="op-filtro op-filtro-buscar">
                                        <label for="op_buscar">Buscar</label>
                                        <input type="search" class="form-control" id="op_buscar" placeholder="Nombre, C.I. o N° de orden" autocomplete="off">
                                    </div>
                                    <div class="op-filtro">
                                        <label for="op_tipo">Tipo</label>
                                        <select class="form-control" id="op_tipo">
                                            <option value="">Todos</option>
                                            <option value="MATRICULA">Matrícula</option>
                                            <option value="CUOTA">Cuota</option>
                                            <option value="CLINICA">Clínica</option>
                                            <option value="MODULOS">Módulos</option>
                                        </select>
                                    </div>
                                    <div class="op-filtro">
                                        <label for="op_estado">Estado</label>
                                        <select class="form-control" id="op_estado">
                                            <option value="">Todos</option>
                                            <option value="PENDIENTE">Pendiente</option>
                                            <option value="VENCIDA">Vencida</option>
                                            <option value="PAGADA">Pagada</option>
                                            <option value="ANULADO">Anulada</option>
                                        </select>
                                    </div>
                                    <div class="op-filtro op-filtro-programa">
                                        <label for="op_programa">Programa</label>
                                        <select class="form-control" id="op_programa">
                                            <option value="">Todos</option>
                                        </select>
                                    </div>
                                    <div class="op-filtro">
                                        <label for="op_desde">Generada desde</label>
                                        <input type="date" class="form-control" id="op_desde">
                                    </div>
                                    <div class="op-filtro">
                                        <label for="op_hasta">Hasta</label>
                                        <input type="date" class="form-control" id="op_hasta">
                                    </div>
                                    <div class="op-filtro op-filtro-limpiar">
                                        <button type="button" class="btn btn-outline-secondary" id="op_limpiar" title="Quitar filtros">
                                            <i class="fa fa-undo"></i> Limpiar
                                        </button>
                                    </div>
                                </div>

                                <!-- Lista -->
                                <table class="table mb-0" id="op_tabla">
                                    <thead>
                                        <tr>
                                            <th>N° Orden</th>
                                            <th>Estudiante</th>
                                            <th>Programa</th>
                                            <th>Tipo</th>
                                            <th class="text-right">Monto</th>
                                            <th>Generada</th>
                                            <th class="text-center">Estado</th>
                                            <th class="text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody id="op_cuerpo">
                                        <tr><td colspan="8" class="op-vacio">Cargando órdenes de pago...</td></tr>
                                    </tbody>
                                </table>

                                <div class="op-pie">
                                    <span id="op_mostrando"></span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="op_mas" style="display: none;">Mostrar más</button>
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

    <style>
        /* Contadores */
        .op-contadores { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; }
        .op-contador {
            flex: 1 1 140px; background: #fff; border-radius: 10px; padding: 12px 16px;
            border-left: 4px solid #1e3c72; box-shadow: 0 1px 3px rgba(30, 60, 114, 0.08);
        }
        .op-contador span { display: block; font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; color: #7e8299; }
        .op-contador strong { font-size: 1.5rem; color: #464E5F; line-height: 1.2; }
        .op-c-pendiente { border-left-color: #b7791f; }
        .op-c-vencida { border-left-color: #c0392b; }
        .op-c-pagada { border-left-color: #1e7145; }
        .op-c-anulada { border-left-color: #9aa0ac; }

        .op-tarjeta { background: #fff; border-radius: 10px; box-shadow: 0 1px 3px rgba(30, 60, 114, 0.08); margin-bottom: 20px; }

        /* Filtros */
        .op-filtros { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; padding: 16px 20px; border-bottom: 1px solid #eef0f5; }
        .op-filtro { flex: 1 1 140px; min-width: 0; }
        .op-filtro-buscar { flex: 2 1 220px; }
        .op-filtro-programa { flex: 2 1 220px; }
        .op-filtro-limpiar { flex: 0 0 auto; }
        .op-filtro label { display: block; margin-bottom: 4px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; color: #56637a; }

        /* Tabla */
        #op_tabla thead th {
            border-top: none; border-bottom: 1px solid #E4E6EF; padding: 0.6rem 0.75rem;
            font-size: 0.72rem; font-weight: 600; text-transform: uppercase; color: #7e8299; white-space: nowrap;
        }
        #op_tabla thead th:first-child, #op_tabla tbody td:first-child { padding-left: 20px; }
        #op_tabla thead th:last-child, #op_tabla tbody td:last-child { padding-right: 20px; }
        #op_tabla tbody td { padding: 0.6rem 0.75rem; vertical-align: middle; font-size: 0.86rem; color: #464E5F; border-top: 1px solid #f3f4f8; }
        #op_tabla tbody tr.op-anulada td { color: #9aa0ac; }
        .op-numero { font-weight: 600; white-space: nowrap; }
        .op-sub { display: block; font-size: 0.75rem; color: #9aa0ac; }
        .op-monto { font-weight: 600; white-space: nowrap; }
        .op-fecha { white-space: nowrap; }

        .op-estado {
            display: inline-block; padding: 3px 10px; border-radius: 20px;
            font-size: 0.7rem; font-weight: 600; text-transform: uppercase; white-space: nowrap;
        }
        .op-estado-pendiente { background: #fdf6e3; color: #8a6212; }
        .op-estado-vencida { background: #fdecea; color: #a5281b; }
        .op-estado-pagada { background: #e6f4ec; color: #1e7145; }
        .op-estado-anulada { background: #eef0f5; color: #7e8299; }

        .op-imprimir {
            display: inline-flex; align-items: center; gap: 6px; white-space: nowrap;
            padding: 5px 14px; border-radius: 20px; border: 1px solid #1e3c72;
            color: #1e3c72; background: #fff; font-size: 0.8rem; font-weight: 600;
        }
        .op-imprimir:hover { background: #1e3c72; color: #fff; text-decoration: none; }

        .op-vacio { text-align: center; color: #9aa0ac; padding: 2rem 1rem !important; }
        .op-pie { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 12px 20px; border-top: 1px solid #eef0f5; font-size: 0.8rem; color: #7e8299; }

        /* Celular y tablet pequeña: cada orden como tarjeta */
        @media (max-width: 991px) {
            #op_tabla thead { display: none; }
            #op_tabla, #op_tabla tbody, #op_tabla tr, #op_tabla td { display: block; width: 100%; }
            #op_tabla tbody { padding: 12px; }
            #op_tabla tbody tr { border: 1px solid #eef0f5; border-radius: 8px; margin-bottom: 10px; padding: 4px 14px; }
            #op_tabla tbody td, #op_tabla tbody td:first-child, #op_tabla tbody td:last-child {
                display: flex; justify-content: space-between; align-items: center; gap: 12px;
                padding: 7px 0; text-align: right !important;
            }
            #op_tabla tbody td::before { content: attr(data-label); flex-shrink: 0; font-size: 0.72rem; font-weight: 600; color: #7e8299; text-align: left; }
            #op_tabla tbody tr td:first-child { border-top: none; }
            #op_tabla tbody td.op-celda-accion::before, #op_tabla tbody td[colspan]::before { content: none; }
            #op_tabla tbody td.op-celda-accion { justify-content: center; padding-top: 10px; }
            #op_tabla tbody td.op-celda-accion .op-imprimir { width: 100%; justify-content: center; padding: 8px 14px; }
            #op_tabla tbody td[colspan] { display: block; text-align: center !important; border: none; }
        }

        @media (max-width: 575px) {
            .op-filtros { padding: 12px; }
            .op-filtro, .op-filtro-buscar, .op-filtro-programa { flex: 1 1 100%; }
            .op-contador { flex: 1 1 calc(50% - 6px); }
        }
    </style>

    <script src="vistas/recursos/assets/js/scripts/ordenespago.js?v=<?php echo time(); ?>"></script>
</body>
