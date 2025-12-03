<?php
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
    <div class="kt-grid kt-grid--hor kt-grid--root" style="background:#E0DEDE;">
        <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver kt-page">
            <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-wrapper" id="kt_wrapper">

                <?php
                $NavBar = new FuncionesControladores();
                $NavBar->NavBarControlador();
                ?>
                <button class="kt-aside-close" id="kt_aside_close_btn"><i class="la la-close"></i></button>
                <?php
                $Sidebar = new FuncionesControladores();
                $Sidebar->SidebarControlador();
                ?>

                <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
                    <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

                        <!-- Subheader -->
                        <div class="kt-subheader kt-grid__item" id="kt_subheader">
                            <div class="kt-container">
                                <div class="kt-subheader__main">
                                    <h2 class="">REPORTES</h2>
                                    <span class="kt-subheader__separator kt-hidden"></span>
                                    <div class="kt-subheader__breadcrumbs">
                                        <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                                        <span class="kt-subheader__breadcrumbs-separator"></span>
                                        <h4>INSCRITOS POR MÓDULO</h4>
                                    </div>
                                </div>
                                <div class="kt-subheader__toolbar">
                                    <div class="kt-subheader__wrapper">
                                        <div id="lafecha" style="font-size:13pt"><?php echo date('d/m/Y H:i:s'); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contenido principal -->
                        <div class="container">

                            <!-- Panel de Estadísticas -->
                            <?php
                            $stats = new ReporteModulosControlador();
                            $stats->MostrarPanelEstadisticasControlador();
                            ?>

                            <!-- Tabla de Programas y Módulos -->
                            <div class="kt-portlet mb-4">
                                <div class="kt-portlet__head" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                                    <div class="kt-portlet__head-label">
                                        <h3 style="color: white; margin: 0;">
                                            <i class="fa fa-list-alt"></i> Programas y Módulos del Sistema
                                        </h3>
                                    </div>
                                </div>
                                <div class="kt-portlet__body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover table-bordered">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th style="width: 100px;">Código</th>
                                                    <th>Nombre del Programa</th>
                                                    <th class="text-center" style="width: 150px;">Grado</th>
                                                    <th class="text-center" style="width: 150px;">Total Módulos</th>
                                                    <th class="text-center" style="width: 150px;">Inscritos</th>
                                                    <th class="text-center" style="width: 120px;">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $tablaProgramas = new ReporteModulosControlador();
                                                $tablaProgramas->MostrarTablaProgramasConModulosControlador();
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Generador de Reportes -->
                            <div class="kt-portlet">
                                <div class="kt-portlet__head" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                    <div class="kt-portlet__head-label">
                                        <h3 style="color: white; margin: 0;">
                                            <i class="fa fa-chart-bar"></i> Generador de Reportes de Inscritos
                                        </h3>
                                    </div>
                                </div>

                                <div class="kt-portlet__body">
                                    <!-- Filtros -->
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><i class="fa fa-graduation-cap"></i> <strong>1. Grado Académico</strong></label>
                                                <select class="form-control select2-basic" id="grado-select">
                                                    <option value="">-- Seleccione Grado --</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><i class="fa fa-book"></i> <strong>2. Programa/Carrera</strong></label>
                                                <select class="form-control select2-basic" id="programa-select" disabled>
                                                    <option value="">-- Seleccione Programa --</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><i class="fa fa-list"></i> <strong>3. Módulo (Opcional)</strong></label>
                                                <select class="form-control select2-basic" id="modulo-select" disabled>
                                                    <option value="">-- Todos los Módulos --</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-2 d-flex align-items-end">
                                            <div class="form-group w-100">
                                                <button class="btn btn-primary btn-block" id="btn-buscar" style="height: 40px;">
                                                    <i class="fa fa-search"></i> Buscar
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <hr>

                                    <!-- Área de resultados -->
                                    <div id="resultados-reporte">
                                        <div class="alert alert-info text-center">
                                            <i class="fa fa-info-circle fa-2x"></i>
                                            <h5 class="mt-2">Use los filtros de arriba y presione <strong>Buscar</strong> para generar el reporte</h5>
                                        </div>
                                    </div>

                                    <!-- Botón de imprimir -->
                                    <div class="text-right mt-4" id="contenedor-imprimir" style="display: none;">
                                        <button class="btn btn-warning btn-lg" id="btn-imprimir">
                                            <i class="fa fa-print"></i> Imprimir Reporte
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php
                        $Footer = new FuncionesControladores();
                        $Footer->FooterControlador();
                        ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Módulos por Programa -->
    <div class="modal fade" id="modalModulosPrograma" tabindex="-1" role="dialog" aria-labelledby="modalModulosProgramaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h5 class="modal-title" id="modalModulosProgramaLabel">
                        <i class="flaticon2-layers"></i> Módulos del Programa
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="contenido-modulos-programa">
                        <div class="text-center p-5">
                            <div class="kt-spinner kt-spinner--lg kt-spinner--brand"></div>
                            <p class="mt-3" style="color: #667eea; font-weight: 600;">Cargando módulos...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="vistas/recursos/assets/vendors/general/jquery/dist/jquery.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="vistas/recursos/assets/js/scripts/reportemodulos.js?v=<?php echo time(); ?>"></script>

    <style>
        .select2-container--default .select2-selection--single {
            height: 40px !important;
            padding: 6px 12px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
        }

        .tabla-reporte {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .tabla-reporte thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            padding: 12px;
            border: none;
        }

        .tabla-reporte tbody tr {
            transition: background 0.3s ease;
        }

        .tabla-reporte tbody tr:hover {
            background: #f8f9fa;
        }

        .badge-modulo {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
        }

        .badge-estado-pagado {
            background: #28a745;
            color: white;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 10px;
        }

        .badge-estado-pendiente {
            background: #ffc107;
            color: #000;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 10px;
        }

        /* Estilos para modal de módulos */
        .modulo-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            cursor: pointer;
            border-left: 4px solid transparent;
        }

        .modulo-card:hover {
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
            transform: translateY(-3px);
            border-left-color: #667eea;
        }

        .modulo-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .modulo-codigo {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 14px;
        }

        .modulo-inscritos {
            background: linear-gradient(135deg, #fd397a 0%, #e91e63 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 14px;
        }

        .modulo-nombre {
            font-size: 16px;
            font-weight: 600;
            color: #464E5F;
            margin-bottom: 0.5rem;
        }

        .modulo-costo {
            font-size: 20px;
            font-weight: 700;
            color: #11998e;
        }

        .modulo-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid #f0f0f0;
        }
    </style>
</body>
