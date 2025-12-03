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

                        <div class="kt-subheader kt-grid__item" id="kt_subheader" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 2rem 0;">
                            <div class="kt-container">
                                <div class="kt-subheader__main">
                                    <h2 class="kt-subheader__title" style="color: white; font-size: 2rem; font-weight: 700;">
                                        <i class="flaticon2-chart" style="font-size: 2.5rem;"></i> CONSULTA DE CALIFICACIONES
                                    </h2>
                                    <span class="kt-subheader__separator kt-hidden"></span>
                                    <div class="kt-subheader__breadcrumbs">
                                        <a href="#" class="kt-subheader__breadcrumbs-home" style="color: rgba(255,255,255,0.8);"><i class="flaticon2-shelter"></i></a>
                                        <span class="kt-subheader__breadcrumbs-separator" style="color: rgba(255,255,255,0.5);"></span>
                                        <h4 style="color: rgba(255,255,255,0.9); font-weight: 500;">
                                            ACCESO ESTUDIANTIL
                                        </h4>
                                    </div>
                                </div>
                                <div class="kt-subheader__toolbar">
                                    <div class="kt-subheader__wrapper">
                                        <div id="lafecha" style="font-size:13pt; color: white; font-weight: 500;"></div>
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
                                <div class="kt-portlet__body" style="padding: 2.5rem; background: #f8f9fa;">

                                    <!-- Información del estudiante -->
                                    <div class="mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; padding: 2rem; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                                                    <div style="background: rgba(255,255,255,0.2); border-radius: 50%; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; margin-right: 1.5rem;">
                                                        <i class="flaticon2-user" style="font-size: 2rem; color: white;"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="mb-1" style="color: white; font-weight: 700; font-size: 1.5rem;">
                                                            <span id="estudiante_nombre">Estudiante</span>
                                                        </h4>
                                                        <p class="mb-0" style="color: rgba(255,255,255,0.8); font-size: 1rem;">
                                                            <i class="flaticon2-line-chart"></i> Acceda a sus calificaciones por programa académico
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 text-right">
                                                <div style="background: rgba(255,255,255,0.2); border-radius: 30px; padding: 12px 24px; display: inline-block;">
                                                    <i class="flaticon2-protected" style="color: white; font-size: 1.2rem;"></i>
                                                    <span style="color: white; font-weight: 600; font-size: 1rem; margin-left: 8px;">Sesión Activa</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Selector de programa -->
                                    <div style="background: white; border-radius: 15px; padding: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08); margin-bottom: 2rem;">
                                        <div class="row align-items-center">
                                            <div class="col-lg-3">
                                                <label class="mb-0" style="font-weight: 700; font-size: 1.1rem; color: #464E5F;">
                                                    <i class="flaticon2-layers-1" style="color: #667eea; font-size: 1.5rem; margin-right: 10px;"></i>
                                                    Programa:
                                                </label>
                                            </div>
                                            <div class="col-lg-9">
                                                <select class="form-control" id="select_programa_calificaciones" name="select_programa_calificaciones" disabled style="height: 50px; border: 2px solid #E4E6EF; border-radius: 10px; font-size: 1rem; font-weight: 500;">
                                                    <option value="">Cargando programas...</option>
                                                </select>
                                                <small class="form-text" style="color: #B5B5C3; margin-top: 8px;">
                                                    <i class="flaticon2-information"></i> Seleccione un programa para visualizar sus calificaciones
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tabla de calificaciones -->
                                    <div class="mb-4" style="background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.08);">
                                        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1.5rem 2rem; display: flex; justify-content: space-between; align-items: center;">
                                            <h3 style="color: white; font-weight: 700; margin: 0; font-size: 1.4rem;">
                                                <i class="flaticon2-line-chart" style="font-size: 1.8rem; margin-right: 10px;"></i>
                                                Detalle de Calificaciones Finales
                                            </h3>
                                            <button class="btn btn-light btn-sm" id="btn_descargar_pdf" style="display: none; border-radius: 25px; padding: 10px 24px; font-weight: 600; box-shadow: 0 4px 15px rgba(255,255,255,0.3);">
                                                <i class="flaticon2-download"></i> Descargar PDF
                                            </button>
                                        </div>
                                        <div style="padding: 2rem;">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0" id="tabla_calificaciones_finales">
                                                    <thead>
                                                        <tr style="background: #f8f9fa; border-bottom: 2px solid #667eea;">
                                                            <th width="30%" style="padding: 1rem; font-weight: 700; color: #464E5F; font-size: 0.9rem;">MÓDULO / ASIGNATURA</th>
                                                            <th width="25%" style="padding: 1rem; font-weight: 700; color: #464E5F; font-size: 0.9rem;">DOCENTE</th>
                                                            <th class="text-center" width="15%" style="padding: 1rem; font-weight: 700; color: #464E5F; font-size: 0.9rem;">NOTA OBTENIDA</th>
                                                            <th class="text-center" width="15%" style="padding: 1rem; font-weight: 700; color: #464E5F; font-size: 0.9rem;">PONDERACIÓN</th>
                                                            <th class="text-center" width="15%" style="padding: 1rem; font-weight: 700; color: #464E5F; font-size: 0.9rem;">ESTADO</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="contenido_calificaciones">
                                                        <tr>
                                                            <td colspan="5" class="text-center" style="padding: 3rem; color: #B5B5C3;">
                                                                <i class="flaticon2-list-3" style="font-size: 3rem; color: #E4E6EF;"></i>
                                                                <p style="margin-top: 1rem; font-size: 1.1rem; font-weight: 500;">Seleccione un programa en el menú superior</p>
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
    <script src="vistas/recursos/assets/js/scripts/historial-estudiante.js?v=<?php echo time(); ?>"></script>

    <style>
        /* Estilos personalizados mejorados */
        .table tbody tr {
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        /* Estilos para el select */
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
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

        /* Botón de descarga */
        #btn_descargar_pdf {
            transition: all 0.3s ease;
        }

        #btn_descargar_pdf:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4) !important;
        }

        /* Badge de estado */
        .badge-estado {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .badge-aprobado {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .badge-reprobado {
            background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
            color: white;
        }

        .badge-pendiente {
            background: linear-gradient(135deg, #ffa800 0%, #ffcd00 100%);
            color: white;
        }

        /* Nota destacada */
        .nota-destacada {
            font-size: 1.3rem;
            font-weight: 700;
            padding: 8px 16px;
            border-radius: 10px;
            display: inline-block;
        }

        .nota-aprobado {
            background: #e8f5f3;
            color: #11998e;
        }

        .nota-reprobado {
            background: #fee5ed;
            color: #ee0979;
        }

        /* Spinner de carga */
        .kt-spinner {
            width: 3rem;
            height: 3rem;
        }

        /* Mejorar tbody */
        tbody td {
            padding: 1.2rem 1rem;
            vertical-align: middle;
        }

        /* Fondo del body */
        body {
            background: #f8f9fa;
        }
    </style>

    </body>