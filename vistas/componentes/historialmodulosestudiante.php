<?php
    $Validar = new FuncionesControladores();
    $Validar -> ValidarSessionControlador();
    date_default_timezone_set("America/La_Paz");
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
    <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed ">
        <div class="kt-header-mobile__logo">
            <a href="demo9/index.html">
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
                                        HISTORIAL DE MÓDULOS
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
                                            Estado de Pagos de Modulos
                                        </h3>
                                    </div>
                                </div>
                                <div class="kt-portlet__body" style="padding: 2rem; background: #f8f9fa;">

                                    <!-- Información del estudiante -->
                                    <div class="d-flex align-items-center justify-content-between mb-3 pb-3" style="border-bottom: 1px solid #E4E6EF;">
                                        <div>
                                            <h5 class="mb-0" style="font-weight: 600; color: #464E5F;">
                                                <i class="flaticon2-user" style="color: #1e3c72;"></i>
                                                <span id="estudiante_nombre">Estudiante</span>
                                            </h5>
                                            <small class="text-muted">Consulte el estado de sus pagos de módulos</small>
                                        </div>
                                    </div>

                                    <!-- Selector de programa -->
                                    <div class="form-group row align-items-center mb-4">
                                        <label class="col-lg-2 col-form-label" style="font-weight: 600; color: #464E5F;">Programa:</label>
                                        <div class="col-lg-10">
                                            <select class="form-control select2-uniforme" id="select_programa_modulos" name="select_programa_modulos" disabled>
                                                <option value="">Cargando programas...</option>
                                            </select>
                                            <small class="form-text text-muted">Seleccione un programa para visualizar el detalle completo de sus módulos</small>
                                        </div>
                                    </div>

                                    <!-- Área de resultados -->
                                    <div id="area_resultados_modulos" style="display: none;">

                                        <!-- Resumen de pagos -->
                                        <div class="row mb-3" id="resumen_pagos_container">
                                            <div class="col-md-4 mb-2">
                                                <div class="d-flex align-items-center" style="background: white; border: 1px solid #E4E6EF; border-left: 3px solid #1e3c72; border-radius: 4px; padding: 0.85rem 1rem;">
                                                    <i class="flaticon2-pie-chart" style="font-size: 1.3rem; color: #1e3c72; margin-right: 0.85rem;"></i>
                                                    <div>
                                                        <div class="text-muted" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Costo Total</div>
                                                        <strong style="font-size: 1.15rem; color: #464E5F;">Bs. <span id="total_programa">0.00</span></strong>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <div class="d-flex align-items-center" style="background: white; border: 1px solid #E4E6EF; border-left: 3px solid #27ae60; border-radius: 4px; padding: 0.85rem 1rem;">
                                                    <i class="flaticon2-check-mark" style="font-size: 1.3rem; color: #27ae60; margin-right: 0.85rem;"></i>
                                                    <div>
                                                        <div class="text-muted" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Total Pagado</div>
                                                        <strong style="font-size: 1.15rem; color: #464E5F;">Bs. <span id="total_pagado">0.00</span></strong>
                                                        <span class="text-muted" style="font-size: 0.8rem;">(<span id="cantidad_pagados">0</span> cuotas)</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <div class="d-flex align-items-center" style="background: white; border: 1px solid #E4E6EF; border-left: 3px solid #dc3545; border-radius: 4px; padding: 0.85rem 1rem;">
                                                    <i class="flaticon2-hourglass-1" style="font-size: 1.3rem; color: #dc3545; margin-right: 0.85rem;"></i>
                                                    <div>
                                                        <div class="text-muted" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Total Pendiente</div>
                                                        <strong style="font-size: 1.15rem; color: #464E5F;">Bs. <span id="total_pendiente">0.00</span></strong>
                                                        <div class="text-muted" style="font-size: 0.8rem;" id="estado_plan_texto">Saldo por cancelar</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Estado de Matrícula -->
                                        <div class="mb-4" id="matricula_container" style="background: white; border: 1px solid #E4E6EF; border-radius: 4px;">
                                            <div style="padding: 0.9rem 1.25rem; border-bottom: 1px solid #E4E6EF;">
                                                <h6 style="margin: 0; font-weight: 600; color: #464E5F; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px;">
                                                    <i class="flaticon2-graduation-cap" style="color: #1e3c72;"></i>
                                                    Matrícula
                                                </h6>
                                            </div>
                                            <div style="padding: 1.1rem 1.25rem;" id="matricula_cuerpo">
                                                <span class="text-muted">Sin información de matrícula</span>
                                            </div>
                                        </div>

                                        <!-- Historial de Cuotas -->
                                        <div class="mb-4" id="modulos_pagados_container" style="background: white; border: 1px solid #E4E6EF; border-radius: 4px;">
                                            <div style="padding: 0.9rem 1.25rem; border-bottom: 1px solid #E4E6EF;">
                                                <h6 style="margin: 0; font-weight: 600; color: #464E5F; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px;">
                                                    <i class="flaticon2-check-mark" style="color: #27ae60;"></i>
                                                    Historial de Cuotas
                                                </h6>
                                            </div>
                                            <div style="padding: 1.25rem;">
                                                <div class="table-responsive">
                                                    <table class="table table-hover table-sm" id="tabla_modulos_pagados" style="margin-bottom: 0;">
                                                        <thead>
                                                            <tr style="background: #f8f9fa; border-bottom: 2px solid #E4E6EF;">
                                                                <th class="text-center" width="8%" style="padding: 0.6rem; font-weight: 600; color: #464E5F; font-size: 0.8rem;">N° CUOTA</th>
                                                                <th class="text-center" width="15%" style="padding: 0.6rem; font-weight: 600; color: #464E5F; font-size: 0.8rem;">MONTO</th>
                                                                <th class="text-center" width="17%" style="padding: 0.6rem; font-weight: 600; color: #464E5F; font-size: 0.8rem;">FECHA GENERADA</th>
                                                                <th class="text-center" width="17%" style="padding: 0.6rem; font-weight: 600; color: #464E5F; font-size: 0.8rem;">FECHA DE PAGO</th>
                                                                <th class="text-center" width="21%" style="padding: 0.6rem; font-weight: 600; color: #464E5F; font-size: 0.8rem;">Nº VOUCHER</th>
                                                                <th class="text-center" width="21%" style="padding: 0.6rem; font-weight: 600; color: #464E5F; font-size: 0.8rem;">ESTADO</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="tbody_modulos_pagados">
                                                            <tr>
                                                                <td colspan="6" class="text-center" style="padding: 2rem; color: #B5B5C3;">
                                                                    <i class="flaticon2-information" style="font-size: 2rem; color: #E4E6EF;"></i>
                                                                    <p style="margin-top: 0.5rem; font-size: 1rem; font-weight: 500;">No hay cuotas registradas</p>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

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

    <!-- Modal: Foto del recibo/voucher de una cuota. Tamaño fijo (ver estilo abajo)
         para que todas las fotos se muestren igual sin importar su resolución
         original. -->
    <div class="modal fade" id="modalFotoRecibo" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="flaticon2-photograph"></i> Foto del Recibo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img id="imagen_recibo_grande" src="" alt="Foto del recibo" class="foto-recibo-tamano-fijo">
                </div>
            </div>
        </div>
    </div>

    <style>
        .foto-recibo-tamano-fijo {
            width: 500px;
            max-width: 100%;
            height: 500px;
            object-fit: contain;
            border-radius: 10px;
            background: #f8f9fa;
        }
    </style>

    <script>
        // Esperar a que jQuery esté completamente cargado
        window.addEventListener('load', function() {
            if (typeof jQuery === 'undefined') {
                console.error('jQuery no está cargado!');
                return;
            }

            // Cargar el script después de que jQuery esté disponible
            var script = document.createElement('script');
            script.src = 'vistas/recursos/assets/js/scripts/historial-modulos-estudiante.js?v=<?php echo time(); ?>';
            document.body.appendChild(script);
        });
    </script>
</body>
