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

                        <div class="kt-subheader   kt-grid__item" id="kt_subheader">
                            <div class="kt-container ">
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
                                        <div id="lafecha" style="font-size:13pt"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="kt-container kt-grid__item kt-grid__item--fluid">
                            <div class="kt-portlet">
                                <div class="kt-portlet__head">
                                    <div class="kt-portlet__head-label">
                                        <h3 class="kt-portlet__head-title">
                                            Estado de Pagos de Módulos
                                        </h3>
                                    </div>
                                </div>
                                <div class="kt-portlet__body" style="padding: 2rem;">

                                    <!-- Información del estudiante -->
                                    <div class="alert alert-light border-primary mb-4" style="border-left: 4px solid #5867dd;">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="mb-2" style="color: #5867dd;">
                                                    <i class="flaticon2-user kt-font-primary"></i>
                                                    <span id="estudiante_nombre">Estudiante</span>
                                                </h5>
                                                <p class="mb-0 text-muted">
                                                    <i class="flaticon2-dollar"></i>
                                                    Consulte el detalle de pagos de módulos por programa
                                                </p>
                                            </div>
                                            <div class="col-md-4 text-right">
                                                <span class="kt-badge kt-badge--brand kt-badge--inline kt-badge--pill" style="font-size: 1rem; padding: 8px 16px;">
                                                    <i class="flaticon2-hourglass"></i> Sesión Activa
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Selector de programa -->
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label" style="font-weight: 600;">
                                            <i class="flaticon2-list-3 text-primary"></i> Programas Inscritos:
                                        </label>
                                        <div class="col-lg-7">
                                            <select class="form-control form-control-lg" id="select_programa_modulos" name="select_programa_modulos" disabled>
                                                <option value="">Cargando programas...</option>
                                            </select>
                                            <span class="form-text text-muted">
                                                Seleccione un programa para ver el detalle de sus módulos
                                            </span>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <!-- Área de resultados -->
                                    <div id="area_resultados_modulos" style="display: none;">

                                        <!-- Resumen de pagos -->
                                        <div class="row mb-4" id="resumen_pagos_container">
                                            <div class="col-lg-4">
                                                <div class="kt-portlet kt-portlet--height-fluid">
                                                    <div class="kt-portlet__body">
                                                        <div class="kt-widget24">
                                                            <div class="kt-widget24__details">
                                                                <div class="kt-widget24__info">
                                                                    <h4 class="kt-widget24__title">
                                                                        Total Programa
                                                                    </h4>
                                                                    <span class="kt-widget24__desc">
                                                                        Costo total
                                                                    </span>
                                                                </div>
                                                                <span class="kt-widget24__stats kt-font-brand">
                                                                    Bs. <span id="total_programa">0.00</span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="kt-portlet kt-portlet--height-fluid">
                                                    <div class="kt-portlet__body">
                                                        <div class="kt-widget24">
                                                            <div class="kt-widget24__details">
                                                                <div class="kt-widget24__info">
                                                                    <h4 class="kt-widget24__title kt-font-success">
                                                                        Total Pagado
                                                                    </h4>
                                                                    <span class="kt-widget24__desc">
                                                                        <span id="cantidad_pagados">0</span> módulos pagados
                                                                    </span>
                                                                </div>
                                                                <span class="kt-widget24__stats kt-font-success">
                                                                    Bs. <span id="total_pagado">0.00</span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="kt-portlet kt-portlet--height-fluid">
                                                    <div class="kt-portlet__body">
                                                        <div class="kt-widget24">
                                                            <div class="kt-widget24__details">
                                                                <div class="kt-widget24__info">
                                                                    <h4 class="kt-widget24__title kt-font-danger">
                                                                        Total Pendiente
                                                                    </h4>
                                                                    <span class="kt-widget24__desc">
                                                                        <span id="cantidad_pendientes">0</span> módulos pendientes
                                                                    </span>
                                                                </div>
                                                                <span class="kt-widget24__stats kt-font-danger">
                                                                    Bs. <span id="total_pendiente">0.00</span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Módulos Pagados -->
                                        <div class="kt-portlet kt-portlet--mobile mb-4" id="modulos_pagados_container">
                                            <div class="kt-portlet__head" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                                                <div class="kt-portlet__head-label">
                                                    <h3 class="kt-portlet__head-title" style="color: white;">
                                                        <i class="flaticon2-check-mark"></i> Módulos Pagados
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="kt-portlet__body kt-portlet__body--fit">
                                                <div class="table-responsive">
                                                    <table class="table table-striped table-bordered table-hover" id="tabla_modulos_pagados">
                                                        <thead class="thead-light">
                                                            <tr>
                                                                <th class="text-center" width="8%">#</th>
                                                                <th width="25%">Módulo</th>
                                                                <th width="15%">Código</th>
                                                                <th class="text-center" width="12%">Costo</th>
                                                                <th class="text-center" width="12%">Fecha Pago</th>
                                                                <th class="text-center" width="15%">N° Voucher</th>
                                                                <th width="13%">Estado</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="tbody_modulos_pagados">
                                                            <tr>
                                                                <td colspan="7" class="text-center text-muted">
                                                                    <i class="flaticon2-information"></i> No hay módulos pagados
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Módulos Pendientes -->
                                        <div class="kt-portlet kt-portlet--mobile" id="modulos_pendientes_container">
                                            <div class="kt-portlet__head" style="background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);">
                                                <div class="kt-portlet__head-label">
                                                    <h3 class="kt-portlet__head-title" style="color: white;">
                                                        <i class="flaticon2-hourglass-1"></i> Módulos Pendientes de Pago
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="kt-portlet__body kt-portlet__body--fit">
                                                <div class="table-responsive">
                                                    <table class="table table-striped table-bordered table-hover" id="tabla_modulos_pendientes">
                                                        <thead class="thead-light">
                                                            <tr>
                                                                <th class="text-center" width="8%">#</th>
                                                                <th width="30%">Módulo</th>
                                                                <th width="18%">Código</th>
                                                                <th width="24%">Docente</th>
                                                                <th class="text-center" width="20%">Costo a Pagar</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="tbody_modulos_pendientes">
                                                            <tr>
                                                                <td colspan="5" class="text-center text-muted">
                                                                    <i class="flaticon2-information"></i> No hay módulos pendientes
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
                    $Footer -> FooterControlador();
                ?>
            </div>
        </div>
    </div>

    <script src="vistas/recursos/assets/js/scripts/historial-modulos-estudiante.js"></script>
</body>
