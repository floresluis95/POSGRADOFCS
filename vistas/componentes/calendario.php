<?php
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();
date_default_timezone_set("America/La_Paz");

$tipoSesion = $_SESSION['Tipo'] ?? '';
$puedeEscribir = in_array($tipoSesion, ['ADM', 'SEC', 'DOC'], true);
$esAdminOSec = in_array($tipoSesion, ['ADM', 'SEC'], true);
$veVencimientosPago = in_array($tipoSesion, ['ADM', 'SEC', 'EST'], true);

$sedesDisponibles = ProgramasModelos::ListarSedesModelo();
$programasDisponibles = ProgramasModelos::ListaProgramaModelo();

// Para Estudiante: sus propios programas, para preseleccionar el filtro por defecto
// (no se restringe la vista, solo se usa como valor inicial del select).
$programaIDsEstudiante = [];
if ($tipoSesion === 'EST' && !empty($_SESSION['EstudianteID'])) {
    $programaIDsEstudiante = CalendarioEventoModelo::ObtenerProgramaIDsDeEstudianteModelo($_SESSION['EstudianteID']);
}
?>
<link rel="stylesheet" href="vistas/recursos/assets/vendors/custom/fullcalendar/fullcalendar.bundle.css">
<link rel="stylesheet" href="vistas/recursos/assets/vendors/custom/fullcalendar/fullcalendar-daygrid.min.css">
<link rel="stylesheet" href="vistas/recursos/assets/vendors/custom/fullcalendar/fullcalendar-timegrid.min.css">
<link rel="stylesheet" href="vistas/recursos/assets/vendors/custom/fullcalendar/fullcalendar-list.min.css">
<link rel="stylesheet" href="vistas/recursos/assets/css/calendario.css">

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
                    <div class="kt-content kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

                        <!-- Subheader -->
                        <div class="kt-subheader kt-grid__item" id="kt_subheader">
                            <div class="kt-container">
                                <div class="kt-subheader__main">
                                    <h2>CALENDARIO ACADÉMICO</h2>
                                    <span class="kt-subheader__separator kt-hidden"></span>
                                    <div class="kt-subheader__breadcrumbs">
                                        <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                                        <span class="kt-subheader__breadcrumbs-separator"></span>
                                        <h4>CLASES, ACTIVIDADES Y CLÍNICAS POR PROGRAMA Y SEDE</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="kt-container kt-container--fluid calendario-wrapper">

                            <!-- Barra de filtros y acciones -->
                            <div class="calendario-toolbar">
                                <div class="calendario-filtros">
                                    <div class="form-group mb-0">
                                        <label>Sede</label>
                                        <select class="form-control form-control-sm" id="filtroSedeCalendario">
                                            <option value="">Todas las sedes</option>
                                            <?php foreach ($sedesDisponibles as $s): ?>
                                                <option value="<?php echo htmlspecialchars($s, ENT_QUOTES); ?>"><?php echo htmlspecialchars($s); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label>Programa</label>
                                        <select class="form-control form-control-sm" id="filtroProgramaCalendario" style="min-width: 260px;">
                                            <option value="">Todos los programas</option>
                                            <?php foreach ($programasDisponibles as $p): ?>
                                                <option value="<?php echo (int)$p['ProgramaID']; ?>" data-sede="<?php echo htmlspecialchars($p['Sede'], ENT_QUOTES); ?>">
                                                    <?php echo htmlspecialchars($p['NombrePrograma'] . ' - ' . $p['GradoAcademico'] . ' (' . $p['Codigo'] . ')'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <?php if ($esAdminOSec): ?>
                                    <div class="form-group mb-0">
                                        <label>Docente</label>
                                        <select class="form-control form-control-sm" id="filtroDocenteCalendario" style="min-width: 220px;">
                                            <option value="">Todos los docentes</option>
                                        </select>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <div class="calendario-leyenda">
                                    <span><i class="calendario-punto" style="background:#2a5298;"></i> Clase</span>
                                    <span><i class="calendario-punto" style="background:#159895;"></i> Actividad</span>
                                    <span><i class="calendario-punto" style="background:#6b2737;"></i> Clínica</span>
                                    <?php if ($veVencimientosPago): ?>
                                    <span><i class="calendario-punto" style="background:#d4a017;"></i> Vence cuota</span>
                                    <span><i class="calendario-punto" style="background:#c0392b;"></i> Cuota vencida</span>
                                    <span><i class="calendario-punto" style="background:#8e44ad;"></i> Plazo de cuota (programa)</span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($puedeEscribir): ?>
                                <div class="calendario-acciones-nuevo">
                                    <button type="button" class="btn btn-sm btn-nuevo-evento" data-tipo="CLASE" style="background:#2a5298; color:#fff;">
                                        <i class="fa fa-plus"></i> Clase
                                    </button>
                                    <button type="button" class="btn btn-sm btn-nuevo-evento" data-tipo="ACTIVIDAD" style="background:#159895; color:#fff;">
                                        <i class="fa fa-plus"></i> Actividad
                                    </button>
                                    <button type="button" class="btn btn-sm btn-nuevo-evento" data-tipo="CLINICA" style="background:#6b2737; color:#fff;">
                                        <i class="fa fa-plus"></i> Clínica
                                    </button>
                                    <?php if ($esAdminOSec): ?>
                                    <button type="button" class="btn btn-sm" id="btnAbrirPlazosCuota" style="background:#8e44ad; color:#fff;">
                                        <i class="fa fa-hourglass-half"></i> Plazos de Cuota
                                    </button>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Calendario a pantalla completa -->
                            <div class="calendario-card">
                                <div id="calendario-academico"></div>
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

    <!-- Modal: Registrar / Editar evento (ADM, SEC, DOC) -->
    <div class="modal fade" id="modalEventoCalendario" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff;">
                    <h5 class="modal-title" id="modalEventoCalendarioTitulo"><i class="fa fa-calendar-plus"></i> Nuevo Evento</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="formEventoCalendario">
                    <input type="hidden" id="eventoIdEvento" value="">
                    <div class="modal-body">
                        <input type="hidden" id="eventoTipo" value="CLASE">
                        <div class="form-group">
                            <label class="font-weight-bold">Tipo de Evento</label>
                            <select class="form-control" id="eventoTipoSelect" required>
                                <option value="CLASE">Clase</option>
                                <option value="ACTIVIDAD">Actividad</option>
                                <option value="CLINICA">Clínica</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Título <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="eventoTitulo" maxlength="150" required placeholder="Ej: Módulo I - Clase teórica">
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Programa <span class="text-danger">*</span></label>
                            <select class="form-control" id="eventoPrograma" required>
                                <option value="">Seleccione un programa...</option>
                                <?php foreach ($programasDisponibles as $p): ?>
                                    <option value="<?php echo (int)$p['ProgramaID']; ?>">
                                        <?php echo htmlspecialchars($p['NombrePrograma'] . ' - ' . $p['GradoAcademico'] . ' (' . $p['Codigo'] . ') - ' . $p['Sede']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if ($esAdminOSec): ?>
                        <div class="form-group">
                            <label class="font-weight-bold">Docente</label>
                            <select class="form-control" id="eventoDocente">
                                <option value="">Sin asignar</option>
                            </select>
                        </div>
                        <?php else: ?>
                        <input type="hidden" id="eventoDocente" value="">
                        <?php endif; ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Inicio <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" id="eventoFechaInicio" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Fin <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" id="eventoFechaFin" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Descripción</label>
                            <textarea class="form-control" id="eventoDescripcion" rows="3" placeholder="Detalle opcional del evento"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger mr-auto" id="btnEliminarEvento" style="display:none;">
                            <i class="fa fa-trash"></i> Eliminar
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Detalle de evento (solo lectura, Estudiante o eventos ajenos) -->
    <div class="modal fade" id="modalDetalleEvento" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #52616e 0%, #37424a 100%); color: #fff;">
                    <h5 class="modal-title"><i class="fa fa-info-circle"></i> Detalle del Evento</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <span class="badge mb-2" id="detalleEventoTipoBadge" style="font-size:12px;"></span>
                    <h5 id="detalleEventoTitulo" class="font-weight-bold"></h5>
                    <p class="mb-1"><i class="fa fa-graduation-cap text-muted"></i> <span id="detalleEventoPrograma"></span></p>
                    <p class="mb-1"><i class="fa fa-map-marker-alt text-muted"></i> <span id="detalleEventoSede"></span></p>
                    <p class="mb-1"><i class="fa fa-chalkboard-teacher text-muted"></i> <span id="detalleEventoDocente"></span></p>
                    <p class="mb-1"><i class="fa fa-clock text-muted"></i> <span id="detalleEventoHorario"></span></p>
                    <p class="mb-0" id="detalleEventoDescripcionWrap"><i class="fa fa-align-left text-muted"></i> <span id="detalleEventoDescripcion"></span></p>
                </div>
                <div class="modal-footer">
                    <a href="#" target="_blank" class="btn mr-auto" id="detalleEventoWhatsapp" style="display:none; background:#25D366; color:#fff;">
                        <i class="fab fa-whatsapp"></i> Contactar por WhatsApp
                    </a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <?php if ($esAdminOSec): ?>
    <!-- Modal: Listado de Plazos de Cuota por Programa (ADM, SEC) -->
    <div class="modal fade" id="modalListaPlazosCuota" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #6b2737 0%, #8e44ad 100%); color: #fff;">
                    <h5 class="modal-title"><i class="fa fa-hourglass-half"></i> Plazo de Cuota por Programa</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3" style="font-size: 13px;">
                        <i class="fa fa-info-circle"></i>
                        Defina la fecha límite de pago de la cuota vigente por programa. Si un estudiante llega a esa
                        fecha con una orden de pago pendiente, verá un aviso al ingresar al sistema y no podrá consultar
                        sus calificaciones hasta regularizar su pago.
                    </p>
                    <div class="text-right mb-2">
                        <button type="button" class="btn btn-sm btn-danger" id="btnNuevoPlazoCuota">
                            <i class="fa fa-plus"></i> Nuevo Plazo
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm" id="tablaPlazosCuota">
                            <thead>
                                <tr>
                                    <th>Programa</th>
                                    <th>Sede</th>
                                    <th>Fecha Plazo</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaPlazosCuotaBody">
                                <tr><td colspan="6" class="text-center text-muted">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Registrar / Editar Plazo de Cuota por Programa (ADM, SEC) -->
    <div class="modal fade" id="modalPlazoCuota" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #6b2737 0%, #8e44ad 100%); color: #fff;">
                    <h5 class="modal-title" id="modalPlazoCuotaTitulo"><i class="fa fa-hourglass-half"></i> Nuevo Plazo de Cuota</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="formPlazoCuota">
                    <input type="hidden" id="plazoIdPlazo" value="">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-bold">Programa <span class="text-danger">*</span></label>
                            <select class="form-control" id="plazoPrograma" required>
                                <option value="">Seleccione un programa...</option>
                                <?php foreach ($programasDisponibles as $p): ?>
                                    <option value="<?php echo (int)$p['ProgramaID']; ?>">
                                        <?php echo htmlspecialchars($p['NombrePrograma'] . ' - ' . $p['GradoAcademico'] . ' (' . $p['Codigo'] . ') - ' . $p['Sede']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Fecha Plazo <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="plazoFecha" required>
                            <small class="form-text text-muted">Fecha límite para que los estudiantes de este programa cancelen su cuota vigente.</small>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Descripción</label>
                            <textarea class="form-control" id="plazoDescripcion" rows="2" maxlength="255" placeholder="Ej: Cuota de septiembre 2026"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger mr-auto" id="btnEliminarPlazoCuota" style="display:none;">
                            <i class="fa fa-trash"></i> Eliminar
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="vistas/recursos/assets/vendors/general/jquery/dist/jquery.js"></script>
    <script src="vistas/recursos/assets/vendors/custom/fullcalendar/fullcalendar.bundle.js"></script>
    <script src="vistas/recursos/assets/vendors/custom/fullcalendar/fullcalendar-daygrid.min.js"></script>
    <script src="vistas/recursos/assets/vendors/custom/fullcalendar/fullcalendar-timegrid.min.js"></script>
    <script src="vistas/recursos/assets/vendors/custom/fullcalendar/fullcalendar-list.min.js"></script>
    <script src="vistas/recursos/assets/vendors/custom/fullcalendar/fullcalendar-interaction.min.js"></script>
    <script src="vistas/recursos/assets/vendors/custom/fullcalendar/fullcalendar-es-locale.js"></script>
    <script>
        window.CALENDARIO_CONTEXTO = {
            tipoSesion: <?php echo json_encode($tipoSesion); ?>,
            puedeEscribir: <?php echo json_encode($puedeEscribir); ?>,
            esAdminOSec: <?php echo json_encode($esAdminOSec); ?>,
            programaIDsEstudiante: <?php echo json_encode($programaIDsEstudiante); ?>,
            programasPorSede: <?php
                $mapa = [];
                foreach ($programasDisponibles as $p) {
                    $mapa[$p['Sede']][] = (int)$p['ProgramaID'];
                }
                echo json_encode($mapa);
            ?>
        };
    </script>
    <script src="vistas/recursos/assets/js/scripts/calendario.js?v=<?php echo time(); ?>"></script>
</body>
