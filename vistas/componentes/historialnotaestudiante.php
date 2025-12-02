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
                                        CONSULTA DE CALIFICACIONES
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
                                            Identificación y Consulta de Notas
                                        </h3>
                                    </div>
                                </div>
                                <div class="kt-portlet__body">
                                    
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Cédula de Identidad (CI) o Usuario:</label>
                                        <div class="col-lg-4">
                                            <input type="text" class="form-control" id="input_ci_estudiante" placeholder="Ingrese su CI o Usuario">
                                        </div>
                                        <div class="col-lg-2">
                                            <button type="button" class="btn btn-brand" id="btn_cargar_programas">
                                                <i class="flaticon2-download"></i> Cargar Programas
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="kt-separator kt-separator--border-dashed kt-separator--space-md"></div>

                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Programas Inscritos:</label>
                                        <div class="col-lg-6">
                                            <select class="form-control" id="select_programa_calificaciones" name="select_programa_calificaciones" disabled>
                                                <option value="">-- Ingrese su CI para cargar los programas --</option>
                                                
                                                </select>
                                            <span class="form-text text-muted">Seleccione un programa para ver el detalle de sus notas.</span>
                                        </div>
                                    </div>

                                    <div class="kt-separator kt-separator--border-dashed kt-separator--space-lg"></div>
                                    <h4 class="kt-section__title kt-margin-t-20">Detalle de Calificaciones Finales</h4>
                                    <div class="kt-section__content">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered table-hover" id="tabla_calificaciones_finales">
                                                <thead class="thead-dark">
                                                    <tr>
                                                        <th>Módulo/Asignatura</th>
                                                        <th>Docente</th>
                                                        <th>Nota Obtenida</th>
                                                        <th>Ponderación Máxima</th>
                                                        <th>Estado</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="contenido_calificaciones">
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">
                                                            <i class="flaticon-file-2"></i> Seleccione un programa en el menú superior.
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
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
        </div>
    </div>
    
    </body>