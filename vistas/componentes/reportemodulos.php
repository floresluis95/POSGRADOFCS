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

                            <!-- Estadísticas por Sede -->
                            <div class="kt-portlet mb-3">
                                <div class="kt-portlet__head" style="background: #fff; border-bottom: 2px solid #EBEDF3; min-height: 45px;">
                                    <div class="kt-portlet__head-label">
                                        <h3 style="color: #3b3f5c; margin: 0; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: .3px;">
                                            <i class="flaticon2-location" style="color: #5867dd;"></i> Programas por Sede
                                        </h3>
                                    </div>
                                </div>
                                <div class="kt-portlet__body" style="padding: 15px;">
                                    <?php
                                    $sedes = new ReporteModulosControlador();
                                    $sedes->MostrarEstadisticaSedesControlador();
                                    ?>
                                </div>
                            </div>

                            <!-- Programas y Módulos Categorizados -->
                            <div class="kt-portlet mb-3">
                                <div class="kt-portlet__head" style="background: #fff; border-bottom: 2px solid #EBEDF3; min-height: 45px;">
                                    <div class="kt-portlet__head-label">
                                        <h3 style="color: #3b3f5c; margin: 0; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: .3px;">
                                            <i class="fa fa-th-large" style="color: #34bfa3;"></i> Programas y Módulos del Sistema
                                        </h3>
                                    </div>
                                </div>
                                <div class="kt-portlet__body" style="padding: 0;">
                                    <?php
                                    $tablaProgramas = new ReporteModulosControlador();
                                    $tablaProgramas->MostrarTablaProgramasConModulosControlador();
                                    ?>
                                </div>
                            </div>

                            <!-- Reporte de Pagos por Programa -->
                            <div class="kt-portlet">
                                <div class="kt-portlet__head" style="background: #fff; border-bottom: 2px solid #EBEDF3; min-height: 45px;">
                                    <div class="kt-portlet__head-label">
                                        <h3 style="color: #3b3f5c; margin: 0; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: .3px;">
                                            <i class="fa fa-money-bill-wave" style="color: #575962;"></i> Reporte de Pagos por Programa
                                        </h3>
                                    </div>
                                </div>

                                <div class="kt-portlet__body">
                                    <small class="text-muted d-block mb-3">
                                        <i class="fa fa-info-circle"></i> Cuántos estudiantes inscritos tiene cada programa, con qué
                                        plan de pago (Regular, Contado con descuento o Grupal), cuántos ya completaron su pago y
                                        cuánto falta cobrar en total.
                                    </small>

                                    <div class="row mb-3 align-items-end">
                                        <div class="col-md-4 form-group mb-2 mb-md-0">
                                            <label class="small font-weight-bold mb-1">Grado Académico</label>
                                            <select class="form-control form-control-sm" id="reportePagosGrado">
                                                <option value="">Todos los grados</option>
                                            </select>
                                        </div>
                                        <div class="col-md-8 text-md-right">
                                            <button type="button" class="btn btn-sm btn-primary" id="btnActualizarReportePagos">
                                                <i class="fa fa-sync"></i> Actualizar
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnImprimirReportePagos">
                                                <i class="fa fa-print"></i> Imprimir
                                            </button>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered table-hover mb-0" id="tablaReportePagos">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Programa</th>
                                                    <th class="text-center">Grado</th>
                                                    <th class="text-center">Inscritos</th>
                                                    <th class="text-center">Plan<br>Regular</th>
                                                    <th class="text-center">Plan Contado<br>(Descuento)</th>
                                                    <th class="text-center">Plan<br>Grupal</th>
                                                    <th class="text-center">Sin<br>Plan</th>
                                                    <th class="text-center">Completaron<br>el Pago</th>
                                                    <th class="text-center">Con Saldo<br>Pendiente</th>
                                                    <th class="text-right">Total<br>Programado</th>
                                                    <th class="text-right">Total<br>Pagado</th>
                                                    <th class="text-right">Saldo<br>Pendiente</th>
                                                    <th class="text-center">Detalle</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tablaReportePagosBody">
                                                <tr><td colspan="13" class="text-center text-muted py-4"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>
                                            </tbody>
                                            <tfoot id="tablaReportePagosFooter"></tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php
                        $Footer = new FuncionesControladores();
                        $Footer->FooterResumidoControlador();
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
                <div class="modal-header" style="background: #3b3f5c; color: white;">
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
                            <p class="mt-3" style="color: #575962; font-weight: 600;">Cargando módulos...</p>
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

    <!-- Modal: Detalle de Pagos por Estudiante (de un programa) -->
    <div class="modal fade" id="modalDetallePagosPrograma" tabindex="-1" role="dialog" aria-labelledby="modalDetallePagosProgramaLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: #3b3f5c; color: white;">
                    <h5 class="modal-title" id="modalDetallePagosProgramaLabel">
                        <i class="fa fa-users"></i> Detalle de Pagos: <span id="detallePagosProgramaNombre"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover mb-0" id="tablaDetallePagosEstudiante">
                            <thead class="thead-light">
                                <tr>
                                    <th>Estudiante</th>
                                    <th style="width:110px;">C.I.</th>
                                    <th class="text-center" style="width:150px;">Plan</th>
                                    <th class="text-center" style="width:110px;">Cuotas</th>
                                    <th class="text-right" style="width:120px;">Costo Total</th>
                                    <th class="text-right" style="width:120px;">Pagado</th>
                                    <th class="text-right" style="width:120px;">Saldo</th>
                                    <th class="text-center" style="width:110px;">Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tablaDetallePagosEstudianteBody">
                                <tr><td colspan="8" class="text-center text-muted py-3"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" id="btnImprimirDetallePagos">
                        <i class="fa fa-print"></i> Imprimir
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="vistas/recursos/assets/vendors/general/jquery/dist/jquery.js"></script>
    <script src="vistas/recursos/assets/js/scripts/reportemodulos.js?v=<?php echo time(); ?>"></script>

    <style>
        #tablaReportePagos thead th,
        #tablaDetallePagosEstudiante thead th {
            font-size: 11px;
            text-transform: uppercase;
            vertical-align: middle;
        }

        /* Estilos para modal de módulos */
        .modulo-card {
            background: white;
            border-radius: 4px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            border: 1px solid #EBEDF3;
            border-left: 3px solid #5867dd;
        }

        .modulo-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .modulo-codigo {
            background: #f1f3f4;
            color: #5867dd;
            padding: 4px 12px;
            border-radius: 3px;
            font-weight: 700;
            font-size: 12px;
        }

        .modulo-nombre {
            font-size: 14px;
            font-weight: 600;
            color: #464E5F;
            margin-bottom: 0.5rem;
        }

        .modulo-costo {
            font-size: 16px;
            font-weight: 700;
            color: #34bfa3;
        }

        .modulo-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 0.75rem;
            border-top: 1px solid #f0f0f0;
        }
    </style>
</body>
