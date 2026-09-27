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
                        <?php
                        // Indicadores generales y programas por grado (ReporteModulosControlador::DatosResumenControlador)
                        $resumenReporte = ReporteModulosControlador::DatosResumenControlador();
                        $st = $resumenReporte['stats'];
                        ?>
                        <div class="container rp rp-pagina">

                            <!-- Indicadores generales: una sola franja, cada uno con su color de identidad y detalle -->
                            <div class="rp-resumen">
                                <div class="rp-res is-azul">
                                    <span class="rp-res-icono"><i class="fa fa-graduation-cap"></i></span>
                                    <div>
                                        <strong><?php echo (int)$st['totalProgramas']; ?></strong>
                                        <small>Programas activos</small>
                                        <em><?php echo htmlspecialchars($resumenReporte['detalleGrados'] ?: 'Sin programas'); ?></em>
                                    </div>
                                </div>
                                <div class="rp-res is-turquesa">
                                    <span class="rp-res-icono"><i class="fa fa-map-marker-alt"></i></span>
                                    <div>
                                        <strong><?php echo (int)$resumenReporte['totalSedes']; ?></strong>
                                        <small><?php echo (int)$resumenReporte['totalSedes'] === 1 ? 'Sede' : 'Sedes'; ?></small>
                                        <em><?php
                                            $textoSedes = [];
                                            foreach ($resumenReporte['sedes'] as $nombreSede => $cantidadSede) {
                                                $textoSedes[] = mb_convert_case($nombreSede, MB_CASE_TITLE, 'UTF-8') . ' (' . $cantidadSede . ')';
                                            }
                                            echo htmlspecialchars($textoSedes ? implode(' · ', $textoSedes) : 'Sin sedes');
                                        ?></em>
                                    </div>
                                </div>
                                <div class="rp-res is-violeta">
                                    <span class="rp-res-icono"><i class="fa fa-user-graduate"></i></span>
                                    <div>
                                        <strong><?php echo (int)$st['totalEstudiantes']; ?></strong>
                                        <small>Estudiantes</small>
                                        <em>En <?php echo (int)$resumenReporte['programasConInscritos']; ?> programa(s) con inscritos</em>
                                    </div>
                                </div>
                                <div class="rp-res is-naranja">
                                    <span class="rp-res-icono"><i class="fa fa-chalkboard-teacher"></i></span>
                                    <div>
                                        <strong><?php echo (int)$st['totalDocentes']; ?></strong>
                                        <small>Docentes</small>
                                        <em><?php echo (int)$st['docentesAsignados']; ?> con módulos asignados</em>
                                    </div>
                                </div>
                            </div>

                            <!-- Tarjeta única con pestañas -->
                            <div class="kt-portlet rp-card" id="reportePagos">
                                <div class="rp-tabs-head">
                                    <ul class="nav rp-tabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-toggle="tab" href="#rpTabPagos" role="tab" id="rpTabPagosLink">
                                                <i class="fa fa-money-bill-wave"></i> Pagos por programa
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#rpTabProgramas" role="tab" id="rpTabProgramasLink">
                                                <i class="fa fa-th-list"></i> Programas y módulos
                                            </a>
                                        </li>
                                    </ul>
                                    <div class="rp-acciones" id="rpAccionesPagos">
                                        <button type="button" class="btn btn-sm rp-btn" id="btnActualizarReportePagos" title="Actualizar">
                                            <i class="fa fa-sync"></i> Actualizar
                                        </button>
                                        <button type="button" class="btn btn-sm rp-btn" id="btnImprimirReportePagos" title="Imprimir">
                                            <i class="fa fa-print"></i> Imprimir
                                        </button>
                                    </div>
                                </div>

                                <div class="tab-content">
                                    <!-- ===== Pestaña: Pagos por programa (contenido en reportemodulos.js) ===== -->
                                    <div class="tab-pane fade show active rp-body" id="rpTabPagos" role="tabpanel">
                                        <div class="rp-filtros">
                                            <div class="rp-filtro">
                                                <select class="form-control select2-uniforme" id="reportePagosGrado" aria-label="Grado académico">
                                                    <option value="">Todos los grados</option>
                                                </select>
                                            </div>
                                            <div class="rp-filtro rp-filtro-busqueda">
                                                <div class="rp-buscar">
                                                    <i class="fa fa-search"></i>
                                                    <input type="search" class="form-control" id="reportePagosBuscar" placeholder="Buscar programa por nombre, código o sede..." aria-label="Buscar programa">
                                                </div>
                                            </div>
                                            <span class="rp-nota" title="Cobrado incluye la matrícula ya cancelada y las cuotas pagadas. Las clínicas se muestran aparte y no se suman a matrícula ni cuotas.">
                                                <i class="fa fa-info-circle"></i> Las clínicas no se suman a matrícula ni cuotas
                                            </span>
                                        </div>

                                        <!-- Indicadores de cobranza (según el filtro) -->
                                        <div class="rp-kpis" id="reportePagosKpis"></div>
                                        <div class="rp-composicion" id="reportePagosComposicion"></div>

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
                                            <span><b>C</b> Contado</span>
                                            <span><b>G</b> Grupal</span>
                                            <span><b>S</b> Sin plan</span>
                                            <span class="rp-leyenda-sep">&middot;</span>
                                            <span><i class="fa fa-check-circle" style="color:#1e3c72"></i> Completaron el pago</span>
                                            <span><i class="fa fa-clock" style="color:#64748b"></i> Con saldo pendiente</span>
                                        </div>
                                    </div>

                                    <!-- ===== Pestaña: Programas y módulos ===== -->
                                    <div class="tab-pane fade rp-body" id="rpTabProgramas" role="tabpanel">
                                        <?php if (empty($resumenReporte['programasPorGrado'])): ?>
                                            <div class="rp-vacio">No hay programas registrados.</div>
                                        <?php else: ?>
                                        <?php
                                            $mesesCortos = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
                                            $fechaCorta = function ($fecha) use ($mesesCortos) {
                                                $ts = strtotime($fecha);
                                                return $mesesCortos[(int)date('n', $ts) - 1] . ' ' . date('Y', $ts);
                                            };
                                            $hoyTexto = date('Y-m-d');
                                        ?>
                                        <div class="rp-tabla-wrap table-responsive">
                                            <table class="table rp-tabla mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Programa</th>
                                                        <th>Periodo</th>
                                                        <th class="rp-col-avance-mod">Avance académico</th>
                                                        <th class="text-center">Docentes</th>
                                                        <th class="text-center">Inscritos</th>
                                                        <th class="rp-no-print"></th>
                                                    </tr>
                                                </thead>
                                                <?php foreach ($resumenReporte['programasPorGrado'] as $grado => $programasGrado): ?>
                                                <tbody>
                                                    <tr class="rp-fila-grupo">
                                                        <td colspan="6"><?php echo htmlspecialchars($grado); ?> <span><?php echo count($programasGrado); ?> programa(s)</span></td>
                                                    </tr>
                                                    <?php foreach ($programasGrado as $pg): ?>
                                                    <?php
                                                        $totalMod = (int)$pg['TotalModulosRegistrados'];
                                                        $finalizados = (int)$pg['ModulosFinalizados'];
                                                        $enCurso = (int)$pg['ModulosEnCurso'];
                                                        $pctFin = $totalMod > 0 ? round($finalizados / $totalMod * 100) : 0;
                                                        $pctCurso = $totalMod > 0 ? round($enCurso / $totalMod * 100) : 0;
                                                        if ($totalMod === 0) {
                                                            $estadoProg = ['Sin módulos', ''];
                                                        } elseif ($finalizados >= $totalMod) {
                                                            $estadoProg = ['Concluido', 'is-exito'];
                                                        } elseif (!empty($pg['FechaInicioPrograma']) && $pg['FechaInicioPrograma'] > $hoyTexto) {
                                                            $estadoProg = ['Por iniciar', 'is-alerta'];
                                                        } else {
                                                            $estadoProg = ['En desarrollo', 'is-info'];
                                                        }
                                                    ?>
                                                    <tr class="<?php echo (int)$pg['TotalInscritos'] === 0 ? 'rp-fila-vacia' : ''; ?>">
                                                        <td>
                                                            <div class="rp-prog-nombre"><?php echo htmlspecialchars($pg['NombrePrograma']); ?></div>
                                                            <div class="rp-prog-meta"><?php echo htmlspecialchars($pg['Codigo']); ?> &middot; <?php echo htmlspecialchars($pg['Sede'] ?: '—'); ?></div>
                                                        </td>
                                                        <td style="white-space:nowrap">
                                                            <?php if (!empty($pg['FechaInicioPrograma'])): ?>
                                                                <?php echo $fechaCorta($pg['FechaInicioPrograma']); ?> – <?php echo $fechaCorta($pg['FechaFinPrograma']); ?>
                                                            <?php else: ?>
                                                                <span class="rp-muted">Sin fechas</span>
                                                            <?php endif; ?>
                                                            <span class="rp-sub"><span class="rp-chip <?php echo $estadoProg[1]; ?>"><?php echo $estadoProg[0]; ?></span></span>
                                                        </td>
                                                        <td>
                                                            <?php if ($totalMod > 0): ?>
                                                            <div class="rp-avance-mod" title="<?php echo $finalizados; ?> finalizado(s), <?php echo $enCurso; ?> en curso, <?php echo $totalMod - $finalizados - $enCurso; ?> por iniciar">
                                                                <div class="rp-barra-mod">
                                                                    <?php if ($pctFin > 0): ?><span class="is-fin" style="width: <?php echo $pctFin; ?>%"></span><?php endif; ?>
                                                                    <?php if ($pctCurso > 0): ?><span class="is-curso" style="width: <?php echo $pctCurso; ?>%"></span><?php endif; ?>
                                                                </div>
                                                                <small><strong><?php echo $finalizados; ?></strong> de <?php echo $totalMod; ?> módulos finalizados<?php echo $enCurso ? ' · ' . $enCurso . ' en curso' : ''; ?></small>
                                                            </div>
                                                            <?php else: ?>
                                                                <span class="rp-muted">Sin módulos registrados</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-center"><strong><?php echo (int)$pg['TotalDocentes']; ?></strong></td>
                                                        <td class="text-center"><strong><?php echo (int)$pg['TotalInscritos']; ?></strong></td>
                                                        <td class="text-center rp-no-print">
                                                            <button type="button" class="btn rp-btn-ver ver-modulos-programa"
                                                                data-programa-id="<?php echo (int)$pg['ProgramaID']; ?>"
                                                                data-programa-nombre="<?php echo htmlspecialchars($pg['NombrePrograma'], ENT_QUOTES); ?>"
                                                                <?php echo $totalMod === 0 ? 'disabled' : ''; ?>>
                                                                Módulos <i class="fa fa-chevron-right" style="font-size:10px"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <?php endforeach; ?>
                                            </table>
                                        </div>
                                        <div class="rp-leyenda">
                                            <span><i class="rp-dot is-fin"></i> Módulos finalizados</span>
                                            <span><i class="rp-dot is-curso"></i> En curso</span>
                                            <span><i class="rp-dot"></i> Por iniciar</span>
                                        </div>
                                        <?php endif; ?>
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
    <div class="modal fade rp" id="modalModulosPrograma" tabindex="-1" role="dialog" aria-labelledby="modalModulosProgramaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header rp-modal-header">
                    <div>
                        <span class="rp-eyebrow">Módulos del programa</span>
                        <h5 class="modal-title" id="modalModulosProgramaLabel">Programa</h5>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body rp-body" style="padding:0;">
                    <div id="contenido-modulos-programa">
                        <div class="rp-vacio"><i class="fa fa-spinner fa-spin"></i> Cargando módulos...</div>
                    </div>
                </div>
                <div class="modal-footer rp-modal-footer">
                    <button type="button" class="btn btn-sm rp-btn" data-dismiss="modal">Cerrar</button>
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

        /* ===== Página compacta: franja de indicadores + tarjeta con pestañas ===== */
        .rp-pagina { padding-top: 4px; padding-bottom: 24px; }
        .rp-resumen {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }
        .rp-res {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff;
            border: 1px solid var(--rp-borde);
            border-radius: 12px;
            padding: 12px 14px;
        }
        .rp-res-icono {
            flex-shrink: 0;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--rp-primario-suave);
            color: var(--rp-primario);
            font-size: 15px;
        }
        .rp-res strong { display: block; font-size: 20px; font-weight: 700; line-height: 1.1; color: var(--rp-texto); }
        .rp-res small { display: block; font-size: 12px; color: var(--rp-texto-2); }

        .kt-portlet.rp-card { border: 1px solid var(--rp-borde); border-radius: 12px; overflow: hidden; box-shadow: none; margin-bottom: 0; }
        .rp-tabs-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            padding: 0 18px;
            background: #fff;
            border-bottom: 1px solid var(--rp-borde);
        }
        .rp-tabs { border: 0; gap: 4px; }
        .rp-tabs .nav-link {
            border: 0;
            border-bottom: 2px solid transparent;
            border-radius: 0;
            padding: 14px 12px;
            font-size: 13.5px;
            font-weight: 500;
            color: var(--rp-texto-2);
            background: transparent;
        }
        .rp-tabs .nav-link i { margin-right: 6px; }
        .rp-tabs .nav-link:hover { color: var(--rp-texto); }
        .rp-tabs .nav-link.active { color: var(--rp-primario); border-bottom-color: var(--rp-primario); font-weight: 600; }
        .rp-card .rp-body { padding: 16px 18px 18px; }
        .rp-card .rp-filtros { align-items: center; margin-bottom: 14px; gap: 12px; }
        .rp-card .rp-filtro { flex: 0 1 220px; min-width: 180px; }
        .rp-card .rp-filtro-busqueda { flex: 1 1 260px; }
        .rp-nota { font-size: 12px; color: var(--rp-texto-2); white-space: nowrap; cursor: help; }
        .rp-nota i { color: var(--rp-clinica); }

        /* Indicadores de cobranza más compactos (la franja general ya va arriba) */
        .rp-card .rp-kpis { gap: 10px; margin-bottom: 14px; }
        .rp-card .rp-kpi { padding: 10px 12px; }
        .rp-card .rp-kpi-valor { font-size: 17px; }
        .rp-card .rp-kpi-sub { font-size: 11.5px; }

        /* Tabla de programas y módulos */
        .rp-tabla td { color: var(--rp-texto); }
        .rp-fila-grupo td {
            background: var(--rp-primario-suave) !important;
            color: var(--rp-primario);
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
            padding: 7px 12px !important;
        }
        .rp-fila-grupo td span { font-weight: 500; letter-spacing: 0; text-transform: none; color: var(--rp-texto-2); margin-left: 6px; }
        .rp .rp-btn-ver:disabled { opacity: .45; cursor: not-allowed; }

        /* Modal de módulos: lista compacta */
        .rp-modulos { list-style: none; margin: 0; padding: 0; }
        .rp-modulos li { display: flex; gap: 12px; align-items: flex-start; padding: 12px 20px; border-bottom: 1px solid var(--rp-borde); }
        .rp-modulo-num {
            flex-shrink: 0;
            width: 26px;
            height: 26px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--rp-fondo);
            color: var(--rp-texto-2);
            font-size: 12px;
            font-weight: 700;
        }
        .rp-modulo-nombre { font-size: 13.5px; font-weight: 500; color: var(--rp-texto); margin-top: 4px; line-height: 1.35; }
        .rp-modulos-total { padding: 10px 20px; font-size: 12px; color: var(--rp-texto-2); background: var(--rp-fondo); }

        @media (max-width: 991.98px) {
            .rp-resumen { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 575.98px) {
            .rp-resumen { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .rp-tabs .nav-link { padding: 12px 8px; font-size: 12.5px; }
            .rp-nota { white-space: normal; }
        }

        /* ===== Color y detalle (paleta validada para daltonismo con el validador de dataviz) =====
           Identidad: azul #2a5298 · turquesa #0d9488 · violeta #7c3aed · naranja #c2410c
           Cobro:     cobrado azul #2a5298 · saldo pendiente ámbar #d97706
           Los números van en color de texto; el color va en marcas (acentos, íconos, barras). */
        .rp {
            --rp-azul: #2a5298;
            --rp-turquesa: #0d9488;
            --rp-violeta: #7c3aed;
            --rp-naranja: #c2410c;
            --rp-ambar: #d97706;
            --rp-pizarra: #475569;
        }
        .rp-pagina { background: transparent; }

        /* Franja de indicadores: acento superior + ícono en su color + línea de detalle */
        .rp-res { position: relative; overflow: hidden; border-top: 3px solid var(--rp-acento, var(--rp-azul)); padding: 14px 16px; align-items: flex-start; }
        .rp-res::after {
            content: '';
            position: absolute;
            right: -22px;
            bottom: -22px;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--rp-acento, var(--rp-azul));
            opacity: .07;
        }
        .rp-res.is-azul { --rp-acento: var(--rp-azul); }
        .rp-res.is-turquesa { --rp-acento: var(--rp-turquesa); }
        .rp-res.is-violeta { --rp-acento: var(--rp-violeta); }
        .rp-res.is-naranja { --rp-acento: var(--rp-naranja); }
        .rp-res .rp-res-icono { background: var(--rp-acento); color: #fff; box-shadow: 0 6px 14px -6px var(--rp-acento); }
        .rp-res strong { font-size: 22px; }
        .rp-res small { font-weight: 600; color: var(--rp-texto); }
        .rp-res em { display: block; font-style: normal; font-size: 11.5px; color: var(--rp-texto-2); margin-top: 2px; }

        /* Indicadores de cobranza: fondo blanco, acento lateral e ícono de color; valor en tinta */
        .rp-card .rp-kpi {
            background: #fff;
            border: 1px solid var(--rp-borde);
            border-left: 4px solid var(--rp-acento, var(--rp-azul));
        }
        .rp-card .rp-kpi.is-violeta { --rp-acento: var(--rp-violeta); }
        .rp-card .rp-kpi.is-pizarra { --rp-acento: var(--rp-pizarra); }
        .rp-card .rp-kpi.is-cobrado { --rp-acento: var(--rp-azul); }
        .rp-card .rp-kpi.is-saldo { --rp-acento: var(--rp-ambar); }
        .rp-card .rp-kpi.is-clinica { --rp-acento: var(--rp-turquesa); background: #fff; }
        .rp-card .rp-kpi .rp-kpi-valor,
        .rp-card .rp-kpi.is-cobrado .rp-kpi-valor,
        .rp-card .rp-kpi.is-saldo .rp-kpi-valor,
        .rp-card .rp-kpi.is-clinica .rp-kpi-valor { color: var(--rp-texto); }
        .rp-card .rp-kpi .rp-kpi-label { color: var(--rp-texto-2); }
        .rp-kpi-icono { color: var(--rp-acento, var(--rp-azul)); margin-right: 6px; }

        /* Composición del total programado */
        .rp-composicion:empty { display: none; }
        .rp-composicion {
            background: #fff;
            border: 1px solid var(--rp-borde);
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 14px;
        }
        .rp-comp-cabecera { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 8px; }
        .rp-comp-titulo { font-size: 12px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: var(--rp-texto-2); }
        .rp-comp-leyenda { display: flex; gap: 16px; flex-wrap: wrap; font-size: 12.5px; color: var(--rp-texto-2); }
        .rp-comp-leyenda b { color: var(--rp-texto); }
        .rp-comp-barra { display: flex; gap: 2px; height: 14px; }
        .rp-comp-seg { height: 100%; min-width: 4px; border-radius: 2px; transition: filter .15s; cursor: default; }
        .rp-comp-seg:first-child { border-radius: 4px 2px 2px 4px; }
        .rp-comp-seg:last-child { border-radius: 2px 4px 4px 2px; }
        .rp-comp-seg:only-child { border-radius: 4px; }
        .rp-comp-seg:hover { filter: brightness(1.12); }
        .rp-comp-seg.is-cobrado { background: var(--rp-azul); }
        .rp-comp-seg.is-saldo { background: var(--rp-ambar); }

        /* Puntos de leyenda */
        .rp-dot { display: inline-block; width: 9px; height: 9px; border-radius: 50%; background: #d7dce4; margin-right: 4px; vertical-align: 0; }
        .rp-dot.is-cobrado, .rp-dot.is-fin { background: var(--rp-azul); }
        .rp-dot.is-saldo { background: var(--rp-ambar); }
        .rp-dot.is-curso { background: #8fb0e6; }

        /* Barras de avance de cobro: azul cobrado sobre pista ámbar (lo pendiente) */
        .rp-barra { background: rgba(217, 119, 6, .28); height: 7px; }
        .rp-barra span, .rp-barra span.is-completo { background: var(--rp-azul); }

        /* Montos en la tabla: tinta + marca de color */
        .rp-cobrado, .rp-saldo { color: var(--rp-texto); font-weight: 700; }
        .rp-cobrado::before, .rp-saldo::before {
            content: '';
            display: inline-block;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            margin-right: 5px;
            vertical-align: 1px;
        }
        .rp-cobrado::before { background: var(--rp-azul); }
        .rp-saldo::before { background: var(--rp-ambar); }
        .rp-clinica-monto { color: var(--rp-texto); font-weight: 700; }
        .rp-clinica-monto::before {
            content: '';
            display: inline-block;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            margin-right: 5px;
            vertical-align: 1px;
            background: var(--rp-turquesa);
        }

        /* Encabezados de grupo de la tabla con el color de su dato */
        .rp-tabla thead th.rp-grupo-programa { box-shadow: inset 0 -2px 0 var(--rp-azul); color: var(--rp-azul); }
        .rp-tabla thead th.rp-grupo-clinica { box-shadow: inset 0 -2px 0 var(--rp-turquesa); color: #0f766e; background: #f0fdfa; }

        /* Avance académico (Programas y módulos) */
        .rp-col-avance-mod { min-width: 220px; }
        .rp-avance-mod small { display: block; font-size: 12px; color: var(--rp-texto-2); margin-top: 5px; }
        .rp-avance-mod small strong { color: var(--rp-texto); }
        .rp-barra-mod { display: flex; gap: 2px; height: 8px; border-radius: 4px; background: #e7ebf1; overflow: hidden; }
        .rp-barra-mod span { height: 100%; }
        .rp-barra-mod .is-fin { background: var(--rp-azul); }
        .rp-barra-mod .is-curso { background: #8fb0e6; }
        .rp-fila-grupo td { border-left: 3px solid var(--rp-azul); }

        /* ===== Estilo formal: un solo tono institucional (azul marino + grises) =====
           El color queda solo donde codifica información (cobrado vs pendiente). */
        .rp {
            --rp-azul: #1e3c72;
            --rp-ambar: #cbd5e1;      /* "pendiente" pasa a gris azulado */
            --rp-turquesa: #64748b;   /* clínicas en gris pizarra */
            --rp-violeta: #1e3c72;
            --rp-naranja: #1e3c72;
            --rp-pizarra: #1e3c72;
        }
        /* Indicadores generales: sin barra de color ni círculo decorativo */
        .rp-res { border-top: 1px solid var(--rp-borde); }
        .rp-res::after { display: none; }
        .rp-res .rp-res-icono { background: var(--rp-primario-suave); color: var(--rp-primario); box-shadow: none; }
        /* Indicadores de cobranza: borde neutro, íconos en azul marino */
        .rp-card .rp-kpi { border-left: 1px solid var(--rp-borde); }
        .rp-kpi-icono { color: var(--rp-primario); }
        /* Barras y puntos */
        .rp-barra { background: #e2e8f0; }
        .rp-dot.is-curso, .rp-barra-mod .is-curso { background: #94a3b8; }
        .rp-comp-seg.is-saldo { background: #cbd5e1; }
        .rp-dot.is-saldo, .rp-saldo::before { background: #94a3b8; }
        /* Encabezados de grupo sobrios */
        .rp-tabla thead th.rp-grupo-programa { color: var(--rp-primario); box-shadow: inset 0 -2px 0 var(--rp-primario); }
        .rp-tabla thead th.rp-grupo-clinica { color: #475569; background: #f8fafc; box-shadow: inset 0 -2px 0 #94a3b8; }
        .rp-tabla td.rp-celda-clinica, .rp-tabla tbody tr:hover td.rp-celda-clinica { background: transparent; }
        .rp-nota i { color: var(--rp-texto-2); }
        /* Etiquetas de estado apagadas */
        .rp-chip.is-exito, .rp-chip.is-alerta, .rp-chip.is-info, .rp-chip.is-clinica { background: #f1f5f9; color: #334155; }
        .rp-chip.is-peligro { background: #fef2f2; color: #991b1b; }
        .rp-chip.is-exito { background: var(--rp-primario-suave); color: var(--rp-primario); }
    </style>
</body>
