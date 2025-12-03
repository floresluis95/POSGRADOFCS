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
                                        CALIFICACIONES FINALES
                                    </h2>
                                    <span class="kt-subheader__separator kt-hidden"></span>
                                    <div class="kt-subheader__breadcrumbs">
                                        <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                                        <span class="kt-subheader__breadcrumbs-separator"></span>
                                        <h4>
                                            RESULTADOS ACADÉMICOS
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
                                            Filtro de Calificaciones
                                        </h3>
                                    </div>
                                </div>
                                <div class="kt-portlet__body">
                                    
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Filtrar por Programa:</label>
                                        <div class="col-lg-6">
                                            <select class="form-control" id="select_programa_calificaciones" name="select_programa_calificaciones">
                                                <option value="">-- Todos los Programas --</option>
                                            </select>
                                            <span class="form-text text-muted">Opcional: Elija un programa específico para filtrar resultados.</span>
                                        </div>
                                    </div>

                                    <div class="kt-separator kt-separator--border-dashed kt-separator--space-md"></div>
                                    
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Buscar por CI, Nombre o Apellido:</label>
                                        <div class="col-lg-3">
                                            <select class="form-control" id="select_filtro_tipo" name="select_filtro_tipo">
                                                <option value="ci">Cédula de Identidad (CI)</option>
                                                <option value="nombre">Nombre</option>
                                                <option value="apellido">Apellido</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-3">
                                            <input type="text" class="form-control" id="input_filtro_valor" name="input_filtro_valor" placeholder="Ingrese valor de búsqueda">
                                        </div>
                                        <div class="col-lg-3">
                                            <button type="button" class="btn btn-primary" id="btn_buscar_calificaciones">
                                                <i class="flaticon-search"></i> Buscar
                                            </button>
                                        </div>
                                    </div>


                                    <div class="kt-separator kt-separator--border-dashed kt-separator--space-lg"></div>
                                    <h4 class="kt-section__title kt-margin-t-20">Resultados de Calificaciones</h4>
                                    <div class="kt-section__content">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered table-hover" id="tabla_calificaciones_finales">
                                                <thead class="thead-dark">
                                                    <tr>
                                                        <th>CI/Estudiante</th>
                                                        <th>Módulo/Asignatura</th>
                                                        <th>Calificación Final</th>
                                                        <th>Estado</th>
                                                        <th>Fecha de Registro</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="contenido_calificaciones">
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">
                                                            <i class="flaticon-file-2"></i> Realice una búsqueda o seleccione un programa.
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
    <script src="vistas/recursos/assets/js/scripts/buscar-calificaciones.js"></script>

    <style>
        .kt-user-card-v2 {
            display: flex;
            align-items: center;
        }

        .kt-user-card-v2__details {
            display: flex;
            flex-direction: column;
        }

        .kt-user-card-v2__name {
            font-weight: 600;
            color: #464457;
            font-size: 1rem;
        }

        .kt-user-card-v2__desc {
            color: #74788d;
            font-size: 0.875rem;
        }

        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .table-striped tbody tr:hover {
            background-color: #f4f5f8;
            transition: background-color 0.2s ease;
        }

        .kt-badge--pill {
            border-radius: 12px;
            padding: 4px 10px;
            font-size: 0.8rem;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        /* Estilos para el botón de buscar */
        #btn_buscar_calificaciones {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        #btn_buscar_calificaciones:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        /* Mejorar el select */
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
    </style>

    </body>