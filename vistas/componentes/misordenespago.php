<?php
    $Validar = new FuncionesControladores();
    $Validar -> ValidarSessionControlador();
    date_default_timezone_set("America/La_Paz");
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
    <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed ">
        <div class="kt-header-mobile__logo">
            <a href="panel">
            <img alt="Logo" src="vistas/recursos/assets/media/logos/logo0.png" width="40" />
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
                            <div class="kt-portlet">
                                <div class="kt-portlet__head">
                                    <div class="kt-portlet__head-label">
                                        <h3 class="kt-portlet__head-title">
                                            Órdenes de Pago de Matrícula, Cuotas y Módulos
                                        </h3>
                                    </div>
                                </div>
                                <div class="kt-portlet__body" style="padding: 2.5rem; background: #f8f9fa;">

                                    <div class="alert alert-light mb-4" style="border-left: 4px solid #1e3c72; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.06);">
                                        <i class="flaticon2-information text-primary"></i>
                                        Aquí encuentra todas sus órdenes de pago generadas (matrícula, cuotas del programa y pago de
                                        módulos). Presione <strong>Imprimir</strong> para descargar el comprobante que debe presentar
                                        en el banco.
                                    </div>

                                    <div id="ordenes_cargando" class="text-center" style="padding: 3rem;">
                                        <div class="kt-spinner kt-spinner--lg kt-spinner--brand"></div>
                                        <p class="mt-3 text-muted">Cargando sus órdenes de pago...</p>
                                    </div>

                                    <div id="ordenes_vacio" style="display:none; text-align:center; padding: 3rem; color:#B5B5C3;">
                                        <i class="flaticon2-file" style="font-size: 3rem; color:#E4E6EF;"></i>
                                        <p style="margin-top:1rem; font-size:1.1rem; font-weight:500;">Todavía no tiene órdenes de pago generadas.</p>
                                    </div>

                                    <div class="row mb-3 align-items-end" id="ordenes_filtros" style="display:none;">
                                        <div class="col-md-6 col-lg-4 mb-2">
                                            <label class="small font-weight-bold mb-1">Programa</label>
                                            <select class="form-control select2-uniforme" id="filtro_programa">
                                                <option value="">Todos los programas</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 col-lg-4 mb-2">
                                            <label class="small font-weight-bold mb-1">Tipo</label>
                                            <select class="form-control select2-uniforme" id="filtro_tipo">
                                                <option value="">Todos</option>
                                                <option value="MATRICULA">Matrícula</option>
                                                <option value="CUOTA">Cuota</option>
                                                <option value="CLINICA">Clínica</option>
                                                <option value="MODULOS">Pago de Módulos</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-4 mb-2 text-lg-right">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn_ver_anuladas" style="display:none;" data-toggle="modal" data-target="#modalOrdenesAnuladas">
                                                <i class="fa fa-ban"></i> Ver anuladas (<span id="conteo_anuladas">0</span>)
                                            </button>
                                        </div>
                                    </div>

                                    <div class="table-responsive" id="ordenes_tabla_wrap" style="display:none;">
                                        <table class="table table-hover" id="tabla_ordenes_pago">
                                            <thead>
                                                <tr>
                                                    <th>N° Orden</th>
                                                    <th>Programa</th>
                                                    <th>Tipo</th>
                                                    <th class="text-right">Monto</th>
                                                    <th>Generada</th>
                                                    <th>Fecha Límite</th>
                                                    <th class="text-center">Estado</th>
                                                    <th class="text-center">Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody id="contenido_ordenes_pago"></tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Modal: Órdenes de Pago Anuladas (fuera de la lista principal, solo consulta) -->
                        <div class="modal fade" id="modalOrdenesAnuladas" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"><i class="fa fa-ban text-muted"></i> Órdenes de Pago Anuladas</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <small class="text-muted d-block mb-3">
                                            <i class="flaticon2-information"></i> Estas órdenes fueron anuladas y no tienen validez para el pago.
                                            Se muestran aquí solo como referencia.
                                        </small>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>N° Orden</th>
                                                        <th>Programa</th>
                                                        <th>Tipo</th>
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

    <script src="vistas/recursos/assets/js/scripts/misordenespago.js?v=<?php echo time(); ?>"></script>

    <style>
        .badge-orden {
            padding: 7px 14px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.78rem;
            text-transform: uppercase;
        }

        .badge-pendiente { background: linear-gradient(135deg, #b7791f 0%, #d4a017 100%); color: white; }
        .badge-confirmado { background: linear-gradient(135deg, #1e7145 0%, #27ae60 100%); color: white; }
        .badge-anulado { background: #e4e6ef; color: #7e8299; }
        .badge-vencido { background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%); color: white; }

        .btn-imprimir-orden {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border: none;
            border-radius: 20px;
            padding: 6px 16px;
            font-size: 0.82rem;
            font-weight: 600;
        }

        .btn-imprimir-orden:hover {
            color: white;
            opacity: 0.9;
        }

        #tabla_ordenes_pago tbody td {
            vertical-align: middle;
        }
    </style>

    </body>
