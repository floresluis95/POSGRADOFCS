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
                                                    <i class="flaticon2-medical-records"></i>
                                                    Acceda a sus calificaciones seleccionando un programa inscrito
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
                                            <select class="form-control form-control-lg" id="select_programa_calificaciones" name="select_programa_calificaciones" disabled>
                                                <option value="">Cargando programas...</option>
                                            </select>
                                            <span class="form-text text-muted">
                                                <i class="flaticon2-information"></i> Seleccione un programa para ver el detalle de sus calificaciones.
                                            </span>
                                        </div>
                                    </div>

                                    <div class="kt-separator kt-separator--border-dashed kt-separator--space-lg"></div>

                                    <!-- Título de la tabla -->
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h4 class="kt-section__title mb-0">
                                            <i class="flaticon2-chart text-success"></i> Detalle de Calificaciones Finales
                                        </h4>
                                        <button class="btn btn-outline-brand btn-sm" id="btn_descargar_pdf" style="display: none;">
                                            <i class="flaticon2-download"></i> Descargar PDF
                                        </button>
                                    </div>

                                    <!-- Tabla de calificaciones -->
                                    <div class="kt-section__content">
                                        <div class="table-responsive" style="border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
                                            <table class="table table-striped table-bordered table-hover mb-0" id="tabla_calificaciones_finales">
                                                <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                                    <tr>
                                                        <th style="width: 30%;">Módulo/Asignatura</th>
                                                        <th style="width: 25%;">Docente</th>
                                                        <th class="text-center" style="width: 15%;">Nota Obtenida</th>
                                                        <th class="text-center" style="width: 15%;">Ponderación Máxima</th>
                                                        <th class="text-center" style="width: 15%;">Estado</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="contenido_calificaciones">
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-5">
                                                            <i class="flaticon-file-2" style="font-size: 3rem; opacity: 0.3;"></i>
                                                            <p class="mt-2">Seleccione un programa en el menú superior.</p>
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

    <!-- Scripts necesarios -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="vistas/recursos/assets/js/scripts/historial-estudiante.js"></script>

    <style>
        /* Estilos personalizados */
        .table-striped tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.2s ease;
        }

        .kt-badge--pill {
            border-radius: 20px;
            padding: 6px 14px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        /* Estilos para el select */
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .form-control-lg {
            font-size: 1rem;
            padding: 0.75rem 1rem;
        }

        /* Animación para las filas */
        tbody tr {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Colores para los estados */
        .kt-font-success {
            color: #1dc9b7 !important;
            font-weight: 700;
        }

        .kt-font-danger {
            color: #fd397a !important;
            font-weight: 700;
        }

        .kt-font-brand {
            color: #5867dd !important;
            font-weight: 700;
        }

        /* Resumen de calificaciones */
        .table-info {
            background-color: #f8f9fa !important;
            border-top: 3px solid #5867dd;
            border-bottom: 3px solid #5867dd;
        }

        .table-info h6 {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Mejora visual de la alerta */
        .alert {
            border-radius: 8px;
        }

        /* Botón de descarga */
        #btn_descargar_pdf {
            transition: all 0.3s ease;
        }

        #btn_descargar_pdf:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(88, 103, 221, 0.4);
        }

        /* Mejorar el thead */
        thead th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 1rem 0.75rem;
        }

        /* Mejorar tbody */
        tbody td {
            padding: 1rem 0.75rem;
            vertical-align: middle;
        }
    </style>

    </body>