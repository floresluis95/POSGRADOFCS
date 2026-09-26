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
                                        <h4>REPORTE GENERAL DE PROGRAMAS</h4>
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

                            <!-- Reporte de Pagos por Programa (contenido en reportemodulos.js) -->
                            <div class="kt-portlet rp" id="reportePagos">
                                <div class="rp-head">
                                    <div>
                                        <h3 class="rp-titulo"><i class="fa fa-money-bill-wave"></i> Reporte de Pagos por Programa</h3>
                                        <p class="rp-subtitulo">
                                            Estado de cobro de cada programa. <strong>Cobrado</strong> incluye la matrícula ya cancelada y las cuotas pagadas.
                                            Las <strong>clínicas</strong> se muestran aparte y no se suman a matrícula ni cuotas.
                                        </p>
                                    </div>
                                    <div class="rp-acciones">
                                        <button type="button" class="btn btn-sm rp-btn" id="btnActualizarReportePagos" title="Actualizar">
                                            <i class="fa fa-sync"></i> Actualizar
                                        </button>
                                        <button type="button" class="btn btn-sm rp-btn" id="btnImprimirReportePagos" title="Imprimir">
                                            <i class="fa fa-print"></i> Imprimir
                                        </button>
                                    </div>
                                </div>

                                <div class="rp-body">
                                    <!-- Filtros -->
                                    <div class="rp-filtros">
                                        <div class="rp-filtro">
                                            <label class="rp-label" for="reportePagosGrado">Grado académico</label>
                                            <select class="form-control select2-uniforme" id="reportePagosGrado">
                                                <option value="">Todos los grados</option>
                                            </select>
                                        </div>
                                        <div class="rp-filtro rp-filtro-busqueda">
                                            <label class="rp-label" for="reportePagosBuscar">Buscar programa</label>
                                            <div class="rp-buscar">
                                                <i class="fa fa-search"></i>
                                                <input type="search" class="form-control" id="reportePagosBuscar" placeholder="Nombre, código o sede...">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Indicadores generales (según el filtro) -->
                                    <div class="rp-kpis" id="reportePagosKpis"></div>

                                    <!-- Tabla por programa -->
                                    <div class="rp-tabla-wrap table-responsive">
                                        <table class="table rp-tabla mb-0" id="tablaReportePagos">
                                            <thead>
                                                <tr class="rp-grupos">
                                                    <th rowspan="2" class="rp-col-programa">Programa</th>
                                                    <th rowspan="2" class="text-center">Estudiantes</th>
                                                    <th colspan="3" class="text-center rp-grupo-programa">Matrícula y cuotas</th>
                                                    <th colspan="2" class="text-center rp-grupo-clinica">Clínicas</th>
                                                    <th rowspan="2" class="rp-no-print"></th>
                                                </tr>
                                                <tr>
                                                    <th class="text-right rp-grupo-programa">Programado</th>
                                                    <th class="text-right rp-grupo-programa rp-col-avance">Cobrado</th>
                                                    <th class="text-right rp-grupo-programa">Saldo</th>
                                                    <th class="text-right rp-grupo-clinica">Cobrado</th>
                                                    <th class="text-right rp-grupo-clinica">Por cobrar</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tablaReportePagosBody">
                                                <tr><td colspan="8" class="rp-vacio"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>
                                            </tbody>
                                            <tfoot id="tablaReportePagosFooter"></tfoot>
                                        </table>
                                    </div>

                                    <div class="rp-leyenda">
                                        <span><b>R</b> Regular</span>
                                        <span><b>C</b> Contado (descuento)</span>
                                        <span><b>G</b> Grupal</span>
                                        <span><b>S</b> Sin plan</span>
                                        <span class="rp-leyenda-sep">&middot;</span>
                                        <span><i class="fa fa-check-circle" style="color:#15803d"></i> Completaron el pago</span>
                                        <span><i class="fa fa-clock" style="color:#b45309"></i> Con saldo pendiente</span>
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
    <div class="modal fade rp" id="modalDetallePagosPrograma" tabindex="-1" role="dialog" aria-labelledby="modalDetallePagosProgramaLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header rp-modal-header">
                    <div>
                        <span class="rp-eyebrow">Detalle de pagos por estudiante</span>
                        <h5 class="modal-title" id="modalDetallePagosProgramaLabel"><span id="detallePagosProgramaNombre"></span></h5>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body rp-body">
                    <div class="rp-kpis rp-kpis-compacto" id="detallePagosKpis"></div>

                    <div class="rp-tabla-wrap table-responsive">
                        <table class="table rp-tabla mb-0" id="tablaDetallePagosEstudiante">
                            <thead id="tablaDetallePagosEstudianteHead"></thead>
                            <tbody id="tablaDetallePagosEstudianteBody">
                                <tr><td colspan="8" class="rp-vacio"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer rp-modal-footer">
                    <button type="button" class="btn btn-sm rp-btn" id="btnImprimirDetallePagos">
                        <i class="fa fa-print"></i> Imprimir
                    </button>
                    <button type="button" class="btn btn-sm rp-btn" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="vistas/recursos/assets/vendors/general/jquery/dist/jquery.js"></script>
    <script src="vistas/recursos/assets/js/scripts/reportemodulos.js?v=<?php echo time(); ?>"></script>

    <style>
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

        /* =========================================================
           Reporte de Pagos por Programa (prefijo rp-)
           Acotado a .rp (portlet del reporte y su modal de detalle).
           ========================================================= */
        .rp {
            --rp-primario: #1e3c72;
            --rp-primario-suave: #eef2f9;
            --rp-texto: #1f2937;
            --rp-texto-2: #6b7280;
            --rp-borde: #e5e7eb;
            --rp-fondo: #f7f8fa;
            --rp-exito: #15803d;
            --rp-exito-suave: #ecfdf3;
            --rp-alerta: #b45309;
            --rp-alerta-suave: #fffbeb;
            --rp-peligro: #b91c1c;
            --rp-peligro-suave: #fef2f2;
            --rp-clinica: #0f766e;
            --rp-clinica-suave: #f0fdfa;
            color: var(--rp-texto);
        }
        .kt-portlet.rp { border-radius: 10px; overflow: hidden; }
        .rp-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            flex-wrap: wrap;
            padding: 18px 22px;
            border-bottom: 1px solid var(--rp-borde);
            background: #fff;
        }
        .rp-titulo {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
            color: var(--rp-texto);
        }
        .rp-titulo i { color: var(--rp-primario); margin-right: 6px; }
        .rp-subtitulo {
            margin: 4px 0 0;
            font-size: 12.5px;
            color: var(--rp-texto-2);
            max-width: 720px;
        }
        .rp-acciones { display: flex; gap: 8px; }
        .rp .rp-btn {
            background: #fff;
            border: 1px solid var(--rp-borde);
            color: var(--rp-texto);
            border-radius: 6px;
            font-weight: 500;
        }
        .rp .rp-btn:hover { background: var(--rp-fondo); }
        .rp-body { padding: 18px 22px 22px; background: #fff; }

        /* Filtros */
        .rp-filtros { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 18px; }
        .rp-filtro { flex: 0 1 260px; min-width: 200px; }
        .rp-filtro-busqueda { flex: 1 1 280px; }
        .rp-label { display: block; font-size: 12px; font-weight: 600; color: var(--rp-texto); margin-bottom: 4px; }
        .rp-buscar { position: relative; }
        .rp-buscar i { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--rp-texto-2); font-size: 12px; }
        .rp-buscar .form-control { padding-left: 32px; }
        .rp .form-control { border-color: var(--rp-borde); border-radius: 6px; font-size: 13px; }

        /* Indicadores */
        .rp-kpis {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }
        .rp-kpi {
            background: var(--rp-fondo);
            border-radius: 8px;
            padding: 12px 14px;
            min-width: 0;
        }
        .rp-kpi-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--rp-texto-2);
        }
        .rp-kpi-valor {
            font-size: 20px;
            font-weight: 600;
            margin-top: 2px;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .rp-kpi-sub { font-size: 12px; color: var(--rp-texto-2); margin-top: 2px; }
        .rp-kpi.is-cobrado .rp-kpi-valor { color: var(--rp-exito); }
        .rp-kpi.is-saldo .rp-kpi-valor { color: var(--rp-peligro); }
        .rp-kpi.is-clinica { background: var(--rp-clinica-suave); }
        .rp-kpi.is-clinica .rp-kpi-label,
        .rp-kpi.is-clinica .rp-kpi-valor { color: var(--rp-clinica); }
        .rp-barra {
            height: 6px;
            border-radius: 3px;
            background: var(--rp-borde);
            overflow: hidden;
            margin-top: 8px;
        }
        .rp-barra span { display: block; height: 100%; background: var(--rp-primario); border-radius: 3px; }
        .rp-barra span.is-completo { background: var(--rp-exito); }
        .rp-kpis-compacto { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .rp-kpis-compacto .rp-kpi-valor { font-size: 17px; }

        /* Tabla */
        .rp-tabla-wrap { border: 1px solid var(--rp-borde); border-radius: 10px; }
        .rp-tabla { font-size: 13px; color: var(--rp-texto); }
        .rp-tabla thead th {
            background: var(--rp-fondo);
            border-top: 0;
            border-bottom: 1px solid var(--rp-borde);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--rp-texto-2);
            padding: 8px 10px;
            vertical-align: bottom;
            white-space: nowrap;
        }
        .rp-tabla thead .rp-grupos th { padding-top: 10px; padding-bottom: 4px; vertical-align: middle; }
        .rp-tabla thead th.rp-grupo-programa { box-shadow: inset 0 -2px 0 var(--rp-primario); color: var(--rp-primario); }
        .rp-tabla thead th.rp-grupo-clinica { box-shadow: inset 0 -2px 0 var(--rp-clinica); color: var(--rp-clinica); background: var(--rp-clinica-suave); }
        .rp-tabla thead tr:last-child th.rp-grupo-programa,
        .rp-tabla thead tr:last-child th.rp-grupo-clinica { box-shadow: none; }
        .rp-tabla td {
            border-top: 1px solid var(--rp-borde);
            padding: 10px 10px;
            vertical-align: middle;
        }
        .rp-tabla tbody tr:hover td { background: #fafbfc; }
        .rp-tabla td.rp-celda-clinica { background: #fbfefe; }
        .rp-tabla tbody tr:hover td.rp-celda-clinica { background: var(--rp-clinica-suave); }
        .rp-tabla .text-right, .rp-num { font-variant-numeric: tabular-nums; white-space: nowrap; }
        .rp-col-programa { min-width: 210px; }
        .rp-planes-linea { display: block; font-size: 11px; color: var(--rp-texto-2); margin-top: 4px; white-space: nowrap; }
        .rp-planes-linea b { color: var(--rp-primario); font-weight: 600; }
        .rp-col-avance { min-width: 140px; }
        .rp-prog-nombre { font-weight: 600; line-height: 1.3; }
        .rp-prog-meta { font-size: 11.5px; color: var(--rp-texto-2); margin-top: 2px; }
        .rp-sub { display: block; font-size: 11px; color: var(--rp-texto-2); font-weight: 400; margin-top: 1px; }
        .rp-inscritos { font-size: 16px; font-weight: 600; }
        .rp-muted { color: var(--rp-texto-2); }
        .rp-cobrado { color: var(--rp-exito); font-weight: 600; }
        .rp-saldo { color: var(--rp-peligro); font-weight: 600; }
        .rp-clinica-monto { color: var(--rp-clinica); font-weight: 600; }
        .rp-na { font-size: 11.5px; color: #9ca3af; font-style: italic; }
        .rp-avance { display: flex; align-items: center; gap: 8px; }
        .rp-avance .rp-barra { flex: 1; margin-top: 0; min-width: 60px; }
        .rp-avance small { font-size: 11.5px; color: var(--rp-texto-2); width: 34px; text-align: right; font-variant-numeric: tabular-nums; }

        .rp-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 600;
            background: var(--rp-fondo);
            color: var(--rp-texto-2);
            white-space: nowrap;
        }
        .rp-chip + .rp-chip { margin-left: 4px; }
        .rp-chip.is-exito { background: var(--rp-exito-suave); color: var(--rp-exito); }
        .rp-chip.is-alerta { background: var(--rp-alerta-suave); color: var(--rp-alerta); }
        .rp-chip.is-peligro { background: var(--rp-peligro-suave); color: var(--rp-peligro); }
        .rp-chip.is-info { background: var(--rp-primario-suave); color: var(--rp-primario); }
        .rp-chip.is-clinica { background: var(--rp-clinica-suave); color: var(--rp-clinica); }
        .rp-planes { display: flex; gap: 4px; justify-content: center; flex-wrap: nowrap; }
        .rp-estado { display: flex; gap: 4px; justify-content: center; margin-top: 4px; flex-wrap: nowrap; }
        .rp-estado .rp-chip + .rp-chip { margin-left: 0; }
        td.text-right .rp-avance { margin-top: 5px; justify-content: flex-end; }
        td.text-right .rp-avance .rp-barra { max-width: 90px; }
        .rp-planes .rp-chip + .rp-chip { margin-left: 0; }

        .rp-tabla tfoot td {
            background: var(--rp-fondo);
            border-top: 2px solid var(--rp-borde);
            font-weight: 600;
            padding: 10px 10px;
        }
        .rp-vacio { text-align: center; color: var(--rp-texto-2); padding: 28px !important; }
        .rp .rp-btn-ver {
            border: 1px solid var(--rp-borde);
            background: #fff;
            color: var(--rp-primario);
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            padding: 4px 10px;
            white-space: nowrap;
        }
        .rp .rp-btn-ver:hover { background: var(--rp-primario-suave); }
        .rp-fila-abandono td { background: #fffafa; }
        .rp-fila-vacia td { color: #9ca3af; }

        .rp-leyenda {
            display: flex;
            flex-wrap: wrap;
            gap: 6px 16px;
            margin-top: 12px;
            font-size: 11.5px;
            color: var(--rp-texto-2);
        }
        .rp-leyenda b { color: var(--rp-texto); }
        .rp-leyenda-sep { color: #d1d5db; }

        /* Modal de detalle */
        .rp .modal-content { border: 0; border-radius: 12px; overflow: hidden; }
        .rp-modal-header { background: var(--rp-primario); color: #fff; border: 0; padding: 16px 22px; }
        .rp .modal-content .rp-modal-header .modal-title { color: #fff; font-size: 17px; font-weight: 600; }
        .rp-eyebrow { display: block; font-size: 11px; letter-spacing: .08em; text-transform: uppercase; color: rgba(255, 255, 255, .65); }
        .rp-modal-header .close { color: #fff; opacity: .7; text-shadow: none; }
        .rp-modal-header .close:hover { opacity: 1; }
        .rp-modal-footer { border-top: 1px solid var(--rp-borde); padding: 10px 22px; }

        @media (max-width: 1199.98px) {
            .rp-kpis { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        @media (max-width: 767.98px) {
            .rp-head, .rp-body { padding-left: 16px; padding-right: 16px; }
            .rp-kpis, .rp-kpis-compacto { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 479.98px) {
            .rp-kpis, .rp-kpis-compacto { grid-template-columns: 1fr; }
        }
    </style>
</body>
