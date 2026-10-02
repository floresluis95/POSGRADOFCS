<?php
/**
 * Vista: Reporte de Calificaciones para Docentes
 * Muestra los m�dulos asignados al docente con detalle de calificaciones
 */

// Validaci�n de sesi�n
$Validar = new FuncionesControladores();
$Validar->ValidarSessionControlador();

date_default_timezone_set("America/La_Paz");
?>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">

  <!-- Header móvil: botón para desplegar el menú lateral en pantallas pequeñas -->
  <div id="kt_header_mobile" class="kt-header-mobile kt-header-mobile--fixed">
    <div class="kt-header-mobile__logo">
      <a href="index.php">
        <img alt="Posgrado Odontología" src="vistas/recursos/assets/media/logos/logo.png" height="36" />
      </a>
    </div>
    <div class="kt-header-mobile__toolbar">
      <button class="kt-header-mobile__toolbar-toggler kt-header-mobile__toolbar-toggler--left" id="kt_aside_mobile_toggler">
        <span></span>
      </button>
      <button class="kt-header-mobile__toolbar-toggler" id="kt_header_mobile_toggler">
        <span></span>
      </button>
      <button class="kt-header-mobile__toolbar-topbar-toggler" id="kt_header_mobile_topbar_toggler">
        <i class="flaticon-more-1"></i>
      </button>
    </div>
  </div>

<div class="kt-grid kt-grid--hor kt-grid--root" style="background:#E0DEDE;">
    <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver kt-page">
        <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-wrapper" id="kt_wrapper">

            <?php $NavBar = new FuncionesControladores(); $NavBar->NavBarControlador(); ?>
            <button class="kt-aside-close " id="kt_aside_close_btn"><i class="la la-close"></i></button>
            <?php $Sidebar = new FuncionesControladores(); $Sidebar->SidebarControlador(); ?>

            <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
                <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

                    <!-- begin:: Subheader -->
                    <div class="kt-subheader kt-grid__item" id="kt_subheader">
                        <div class="kt-container ">
                            <div class="kt-subheader__main">
                                <h2 class="">REPORTE DE CALIFICACIONES</h2>
                                <span class="kt-subheader__separator kt-hidden"></span>
                                <div class="kt-subheader__breadcrumbs">
                                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                                    <span class="kt-subheader__breadcrumbs-separator"></span>
                                    <h4>MIS MODULOS Y CALIFICACIONES</h4>
                                </div>
                            </div>
                            <div class="kt-subheader__toolbar">
                                <div class="kt-subheader__wrapper">
                                    <div id="lafecha" style="font-size:13pt"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CONTENIDO PRINCIPAL -->
                    <div class="container-fluid">
                        <div class="kt-portlet kt-portlet--mobile">
                            <div class="kt-portlet__head" style="background-color: #34495e; border-radius: 4px 4px 0 0;">
                                <div class="kt-portlet__head-label">
                                    <h3 class="kt-portlet__head-title text-white">
                                        <i class="fa fa-chart-bar"></i> Mis Módulos Asignados - Estado de Calificaciones
                                    </h3>
                                </div>
                                <div class="kt-portlet__head-toolbar">
                                    <button type="button" class="btn btn-danger btn-sm btn-accion-uniforme mr-2" id="btn-reporte-completo">
                                        <i class="fa fa-file-pdf"></i> Reporte Completo
                                    </button>
                                    <button type="button" class="btn btn-light btn-sm btn-accion-uniforme" onclick="location.reload()">
                                        <i class="fa fa-sync-alt"></i> Actualizar
                                    </button>
                                </div>
                            </div>

                            <div class="kt-portlet__body" style="padding: 2rem;">

                                <!-- LOADING -->
                                <div id="loading-modulos" class="text-center py-5" style="display: none;">
                                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                        <span class="sr-only">Cargando...</span>
                                    </div>
                                    <p class="mt-3">Cargando modulos asignados...</p>
                                </div>

                                <!-- INFORMACI�N DEL DOCENTE -->
                                <div id="info-docente" class="alert alert-light mb-4" style="display: none; border-left: 4px solid #34495e;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong><i class="fa fa-user"></i> Docente:</strong> <span id="docente-nombre"></span></p>
                                            <p class="mb-0"><strong><i class="fa fa-id-card"></i> C.I.:</strong> <span id="docente-ci"></span></p>
                                        </div>
                                        <div class="col-md-6">
                                            
                                            <p class="mb-1"><strong><i class="fa fa-graduation-cap"></i> Especialidad:</strong> <span id="docente-especialidad"></span></p>
                                            <p class="mb-0"><strong><i class="fa fa-book"></i> Total Modulos:</strong> <span id="total-modulos" class="kt-badge kt-badge--brand kt-badge--inline"></span></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- FILTROS DE LA TABLA DE M�DULOS -->
                                <div class="row mb-3" id="filtros-modulos-container" style="display:none;">
                                    <div class="col-md-3">
                                        <label class="small font-weight-bold mb-1" for="filtro-sede-reporte">
                                            <i class="fa fa-map-marker-alt"></i> Sede
                                        </label>
                                        <select id="filtro-sede-reporte" class="form-control select2-uniforme">
                                            <option value="">Todas las sedes</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="small font-weight-bold mb-1" for="filtro-programa-reporte">
                                            <i class="fa fa-university"></i> Programa
                                        </label>
                                        <select id="filtro-programa-reporte" class="form-control select2-uniforme">
                                            <option value="">Todos los programas</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small font-weight-bold mb-1" for="filtro-modulo-reporte">
                                            <i class="fa fa-book"></i> Módulo
                                        </label>
                                        <select id="filtro-modulo-reporte" class="form-control select2-uniforme">
                                            <option value="">Todos los módulos</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- TABLA DE M�DULOS -->
                                <div id="tabla-modulos-container"></div>

                            </div>
                        </div>
                    </div>
                    <!-- FIN DEL CONTENIDO PRINCIPAL -->

                    <?php $Footer = new FuncionesControladores(); $Footer->FooterResumidoControlador(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PARA PONER / VER CALIFICACIONES -->
<div class="modal fade" id="modalCalificar" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #34495e;">
                <h5 class="modal-title text-white">
                    <i class="fa fa-graduation-cap"></i> <span id="modal-calificar-titulo">Calificaciones del Módulo</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <strong><i class="fa fa-book"></i> Módulo:</strong> <span id="modal-calificar-modulo"></span><br>
                            <strong><i class="fa fa-code"></i> Código:</strong> <span id="modal-calificar-codigo"></span><br>
                            <strong><i class="fa fa-tag"></i> Sigla:</strong> <span id="modal-calificar-sigla"></span>
                        </div>
                        <div class="col-md-6">
                            <strong><i class="fa fa-university"></i> Programa:</strong> <span id="modal-calificar-programa"></span><br>
                            <strong><i class="fa fa-graduation-cap"></i> Grado:</strong> <span id="modal-calificar-grado"></span><br>
                            <strong><i class="fa fa-calendar-check-o"></i> Fecha Inicio:</strong> <span id="modal-calificar-fecha-inicio"></span><br>
                            <strong><i class="fa fa-calendar-times-o"></i> Fecha Final:</strong> <span id="modal-calificar-fecha-final"></span>
                        </div>
                    </div>
                </div>
                <div id="modal-calificar-aviso"></div>
                <div id="modal-calificar-container">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" id="modal-calificar-footer" style="display:none;">
                <span class="text-danger small mr-auto">
                    <i class="fa fa-exclamation-triangle"></i> Verifique todas las notas antes de guardar.
                </span>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cerrar
                </button>
                <button type="button" class="btn btn-success" id="btn-guardar-notas-reporte">
                    <i class="fa fa-save"></i> Guardar Calificaciones
                </button>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPTS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
.table-modulos {
    box-shadow: 0 1px 4px rgba(0,0,0,0.08);
}

.table-modulos thead {
    background-color: #34495e;
    color: white;
}

.table-modulos tbody tr:hover {
    background-color: #f4f5f7;
}

.btn-print-planilla {
    background-color: #c0392b;
    border: none;
    color: white;
}

.btn-print-planilla:hover {
    background-color: #a93226;
    color: white;
}

.badge-modulo {
    padding: 6px 12px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 3px;
}

/* Todos los botones de la vista comparten un mismo ancho mínimo, para que no
   varíen de tamaño según el largo de su texto. */
.btn-accion-uniforme {
    min-width: 150px;
}

#tabla-modulos-container .btn {
    min-width: 110px;
    margin-bottom: 4px;
}

/* Badges puramente informativos (conteos, sigla): tono neutro; se conservan
   en color solo los que indican un estado real (calificado/pendiente,
   módulo abierto/cerrado), que sí aportan información útil de un vistazo. */
.kt-badge--brand,
.kt-badge--info,
.kt-badge--primary {
    background-color: #e9ecef !important;
    color: #34495e !important;
}
</style>

<script>
let docenteData = null;
let moduloSeleccionado = null;

// Copia completa (sin filtrar) de los módulos del docente, para poder
// filtrar por Sede/Programa en el cliente sin volver a pedirlos al servidor.
let modulosCompletos = [];

// Formatear una fecha "YYYY-MM-DD" (tal como viene de la BD) a "DD/MM/AAAA".
function formatearFechaDDMMAAAA(fecha) {
    if (!fecha) return 'No registrada';
    const partes = fecha.split('-');
    if (partes.length !== 3) return fecha;
    return partes[2] + '/' + partes[1] + '/' + partes[0];
}

$(document).ready(function() {
    // Cargar módulos del docente
    cargarModulosDocente();

    // Actualizar fecha
    actualizarFecha();
    setInterval(actualizarFecha, 60000);

    // Select2 (mismo tamaño/estilo en Sede y Programa)
    $('.select2-uniforme').select2({ width: '100%' });

    // Botón reporte completo
    $('#btn-reporte-completo').on('click', function() {
        generarReporteCompleto();
    });

    // Filtros de Sede / Programa / Módulo: se filtran los módulos ya
    // cargados, sin volver a pedirle nada al servidor.
    $(document).on('change', '#filtro-sede-reporte, #filtro-programa-reporte, #filtro-modulo-reporte', function() {
        const sede = $('#filtro-sede-reporte').val();
        const programaID = $('#filtro-programa-reporte').val();
        const moduloID = $('#filtro-modulo-reporte').val();

        const filtrados = modulosCompletos.filter(function(modulo) {
            if (sede && modulo.Sede !== sede) return false;
            if (programaID && String(modulo.ProgramaID) !== String(programaID)) return false;
            if (moduloID && String(modulo.Idmodulo) !== String(moduloID)) return false;
            return true;
        });

        mostrarTablaModulos(filtrados);
    });

    // Abrir modal de calificar (delegado porque la tabla se genera dinámicamente)
    $(document).on('click', '.btn-calificar', function() {
        moduloSeleccionado = {
            moduloID: $(this).data('modulo-id'),
            programaID: $(this).data('programa-id'),
            moduloNombre: $(this).data('modulo-nombre'),
            moduloCodigo: $(this).data('modulo-codigo'),
            programaNombre: $(this).data('programa-nombre'),
            grado: $(this).data('grado'),
            estadoModulo: $(this).data('estado-modulo'),
            fechaInicio: $(this).data('fecha-inicio'),
            fechaFinal: $(this).data('fecha-final'),
            sigla: $(this).data('sigla')
        };
        abrirModalCalificar();
    });

    $(document).on('click', '.btn-validar', function() {
        validarCerrarModuloReporte($(this).data('modulo-id'), $(this).data('modulo-nombre'));
    });

    $(document).on('click', '.btn-reabrir', function() {
        reabrirModuloReporte($(this).data('modulo-id'), $(this).data('modulo-nombre'));
    });

    $('#btn-guardar-notas-reporte').on('click', function() {
        guardarCalificacionesReporte();
    });
});

/**
 * Llenar los selects de Sede, Programa y Módulo con los valores presentes en
 * los módulos asignados al docente logueado.
 */
function poblarFiltrosModulosReporte(modulos) {
    const $filtroSede = $('#filtro-sede-reporte');
    const $filtroPrograma = $('#filtro-programa-reporte');
    const $filtroModulo = $('#filtro-modulo-reporte');

    if (modulos.length === 0) {
        $('#filtros-modulos-container').hide();
        return;
    }

    const sedeSeleccionada = $filtroSede.val();
    const programaSeleccionado = $filtroPrograma.val();
    const moduloSeleccionado = $filtroModulo.val();

    const sedes = [...new Set(modulos.map(m => m.Sede).filter(Boolean))].sort();
    const programas = [...new Map(modulos.map(m => [m.ProgramaID, m.NombrePrograma])).entries()]
        .sort((a, b) => a[1].localeCompare(b[1]));
    const modulosOrdenados = [...modulos].sort((a, b) =>
        (a.codigomodulo || '').localeCompare(b.codigomodulo || ''));

    $filtroSede.html('<option value="">Todas las sedes</option>' +
        sedes.map(s => `<option value="${s}">${s}</option>`).join(''));
    $filtroPrograma.html('<option value="">Todos los programas</option>' +
        programas.map(([id, nombre]) => `<option value="${id}">${nombre}</option>`).join(''));
    $filtroModulo.html('<option value="">Todos los módulos</option>' +
        modulosOrdenados.map(m =>
            `<option value="${m.Idmodulo}">${escaparHtml((m.codigomodulo || '') + ' - ' + m.nombremodulo)}</option>`
        ).join(''));

    // trigger('change') es necesario para que Select2 actualice el texto
    // visible (un .val() plano no repinta el widget).
    if (sedeSeleccionada) $filtroSede.val(sedeSeleccionada).trigger('change');
    if (programaSeleccionado) $filtroPrograma.val(programaSeleccionado).trigger('change');
    if (moduloSeleccionado) $filtroModulo.val(moduloSeleccionado).trigger('change');

    $('#filtros-modulos-container').show();
}

function escaparHtml(texto) {
    return $('<div>').text(texto == null ? '' : texto).html();
}

function actualizarFecha() {
    const opciones = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    const fecha = new Date().toLocaleDateString('es-ES', opciones);
    $('#lafecha').text(fecha.charAt(0).toUpperCase() + fecha.slice(1));
}

function cargarModulosDocente() {
    $('#loading-modulos').show();
    $('#info-docente').hide();
    $('#tabla-modulos-container').html('');

    $.ajax({
        url: 'ajax/calificacion.ajax.php',
        method: 'POST',
        data: {
            accion: 'obtenerDocente'
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success' && response.data) {
                docenteData = response.data;

                // Mostrar informaci�n del docente
                $('#docente-nombre').text(docenteData.NombreCompleto);
                $('#docente-ci').text(docenteData.CICompleto);
                $('#docente-especialidad').text(docenteData.Especialidad || 'No especificada');
                $('#info-docente').fadeIn();

                // Cargar asignaciones
                cargarAsignaciones(docenteData.DocenteID);
            } else {
                $('#loading-modulos').hide();
                mostrarError('No se pudo cargar la informaci�n del docente');
            }
        },
        error: function() {
            $('#loading-modulos').hide();
            mostrarError('Error al conectar con el servidor');
        }
    });
}

function cargarAsignaciones(docenteID) {
    $.ajax({
        url: 'ajax/calificacion.ajax.php',
        method: 'POST',
        data: {
            accion: 'obtenerAsignaciones',
            docenteID: docenteID
        },
        dataType: 'json',
        success: function(response) {
            $('#loading-modulos').hide();

            if (response.status === 'success' && response.data && response.data.length > 0) {
                modulosCompletos = response.data;
                $('#total-modulos').text(response.data.length);
                poblarFiltrosModulosReporte(modulosCompletos);
                mostrarTablaModulos(modulosCompletos);
            } else {
                $('#filtros-modulos-container').hide();
                mostrarSinModulos();
            }
        },
        error: function() {
            $('#loading-modulos').hide();
            mostrarError('Error al cargar los modulos asignados');
        }
    });
}

function mostrarTablaModulos(modulos) {
    let html = `
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-modulos">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 4%;">Nro</th>
                        <th style="width: 10%;">Codigo</th>
                        <th style="width: 16%;">Modulo</th>
                        <th style="width: 16%;">Programa</th>
                        <th class="text-center" style="width: 8%;">Sede</th>
                        <th class="text-center" style="width: 12%;">Fechas</th>
                        <th class="text-center" style="width: 7%;">Inscritos</th>
                        <th class="text-center" style="width: 9%;">Calificados</th>
                        <th class="text-center" style="width: 10%;">Estado Modulo</th>
                        <th class="text-center" style="width: 18%;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
    `;

    if (modulos.length === 0) {
        html += '<tr><td colspan="10" class="text-center text-muted py-3">Ningún módulo coincide con el filtro seleccionado</td></tr>';
    }

    modulos.forEach(function(modulo, index) {
        const inscritos = parseInt(modulo.TotalEstudiantes) || 0;
        const calificados = parseInt(modulo.TotalCalificados) || 0;
        const porcentaje = inscritos > 0 ? Math.round((calificados / inscritos) * 100) : 0;

        let estadoBadge = '';
        if (calificados === 0) {
            estadoBadge = '<span class="badge badge-secondary badge-modulo">Sin calificar</span>';
        } else if (calificados < inscritos) {
            estadoBadge = '<span class="badge badge-warning badge-modulo">Parcial ' + porcentaje + '%</span>';
        } else {
            estadoBadge = '<span class="badge badge-success badge-modulo">Completo 100%</span>';
        }

        // Determinar estado del módulo
        const estadoModulo = modulo.EstadoModulo || 'ACTIVO';
        const moduloCerrado = (estadoModulo === 'VALIDADO' || estadoModulo === 'CERRADO');
        let estadoModuloBadge = '';
        let estadoModuloIcono = '';

        if (moduloCerrado) {
            estadoModuloBadge = 'kt-badge--danger';
            estadoModuloIcono = 'la-lock';
        } else {
            estadoModuloBadge = 'kt-badge--success';
            estadoModuloIcono = 'la-unlock';
        }

        const sigla = modulo.Sigla || '';
        const fechaInicio = modulo.FechaInicio || '';
        const fechaFinal = modulo.FechaFinal || '';

        html += `
            <tr>
                <td class="text-center">${index + 1}</td>
                <td><strong>${modulo.codigomodulo}</strong></td>
                <td>${modulo.nombremodulo}</td>
                <td>${modulo.NombrePrograma}${sigla ? ' <span class="kt-badge kt-badge--brand kt-badge--inline">' + sigla + '</span>' : ''}<br><small class="text-muted">${modulo.GradoAcademico}</small></td>
                <td class="text-center"><small>${modulo.Sede || '-'}</small></td>
                <td class="text-center">
                    <small>
                        <i class="fa fa-calendar-check-o text-muted"></i> ${formatearFechaDDMMAAAA(fechaInicio)}<br>
                        <i class="fa fa-calendar-times-o text-muted"></i> ${formatearFechaDDMMAAAA(fechaFinal)}
                    </small>
                </td>
                <td class="text-center">
                    <span class="kt-badge kt-badge--info kt-badge--inline">${inscritos}</span>
                </td>
                <td class="text-center">
                    <span class="kt-badge kt-badge--primary kt-badge--inline">${calificados}</span>
                    ${estadoBadge}
                </td>
                <td class="text-center">
                    <span class="kt-badge ${estadoModuloBadge} kt-badge--inline kt-badge--bold">
                        <i class="la ${estadoModuloIcono}"></i> ${estadoModulo}
                    </span>
        `;

        if (moduloCerrado) {
            html += `
                    <br><button type="button" class="btn btn-info btn-sm btn-reabrir"
                            data-modulo-id="${modulo.Idmodulo}" data-modulo-nombre="${escaparHtml(modulo.nombremodulo)}">
                        <i class="la la-unlock"></i> Reabrir
                    </button>
            `;
        } else if (calificados > 0) {
            html += `
                    <br><button type="button" class="btn btn-warning btn-sm btn-validar"
                            data-modulo-id="${modulo.Idmodulo}" data-modulo-nombre="${escaparHtml(modulo.nombremodulo)}">
                        <i class="la la-lock"></i> Validar y Cerrar
                    </button>
            `;
        }

        html += `
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-brand btn-sm btn-calificar"
                            data-modulo-id="${modulo.Idmodulo}" data-programa-id="${modulo.ProgramaID}"
                            data-modulo-nombre="${escaparHtml(modulo.nombremodulo)}" data-modulo-codigo="${escaparHtml(modulo.codigomodulo)}"
                            data-programa-nombre="${escaparHtml(modulo.NombrePrograma)}" data-grado="${escaparHtml(modulo.GradoAcademico)}"
                            data-estado-modulo="${estadoModulo}"
                            data-fecha-inicio="${fechaInicio}" data-fecha-final="${fechaFinal}" data-sigla="${escaparHtml(sigla)}">
                        <i class="la la-edit"></i> ${moduloCerrado ? 'Ver Notas' : 'Poner Notas'}
                    </button>
        `;

        if (calificados > 0) {
            html += `
                <button type="button" class="btn btn-print-planilla btn-sm btn-imprimir-reporte"
                        data-modulo-id="${modulo.Idmodulo}" data-programa-id="${modulo.ProgramaID}"
                        data-modulo-nombre="${escaparHtml(modulo.nombremodulo)}" data-modulo-codigo="${escaparHtml(modulo.codigomodulo)}"
                        data-programa-nombre="${escaparHtml(modulo.NombrePrograma)}" data-grado="${escaparHtml(modulo.GradoAcademico)}"
                        data-fecha-inicio="${fechaInicio}" data-fecha-final="${fechaFinal}" data-sigla="${escaparHtml(sigla)}">
                    <i class="fa fa-print"></i> Imprimir
                </button>
            `;
        }

        html += `
                    <button type="button" class="btn btn-danger btn-sm btn-asistencia-reporte"
                            data-modulo-id="${modulo.Idmodulo}" data-programa-id="${modulo.ProgramaID}"
                            data-modulo-nombre="${escaparHtml(modulo.nombremodulo)}" data-modulo-codigo="${escaparHtml(modulo.codigomodulo)}"
                            data-programa-nombre="${escaparHtml(modulo.NombrePrograma)}" data-grado="${escaparHtml(modulo.GradoAcademico)}"
                            data-total-estudiantes="${inscritos}"
                            title="Lista de asistencia">
                        <i class="fa fa-file-pdf"></i> Asistencia
                    </button>
        `;

        html += `
                </td>
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    $('#tabla-modulos-container').html(html);
}

// Imprimir la planilla de un módulo: reutiliza el mismo generador de PDF ya
// corregido que usa "Registro de Calificaciones" (dirección, sigla, menciones
// y pie de página al fondo), precargando las fechas ya guardadas en el
// módulo para que el docente solo tenga que confirmarlas.
$(document).on('click', '.btn-imprimir-reporte', function() {
    const $btn = $(this);
    const moduloID = $btn.data('modulo-id');
    const programaID = $btn.data('programa-id');
    const moduloNombre = $btn.data('modulo-nombre');
    const moduloCodigo = $btn.data('modulo-codigo');
    const programaNombre = $btn.data('programa-nombre');
    const grado = $btn.data('grado');
    const fechaInicioDB = $btn.data('fecha-inicio');
    const fechaFinalDB = $btn.data('fecha-final');
    const sigla = $btn.data('sigla');

    generarPlanillaPDF(moduloID, programaID, moduloNombre, moduloCodigo, programaNombre, grado, fechaInicioDB, fechaFinalDB, sigla);
});

// Lista de asistencia directamente desde la tabla de módulos del reporte.
$(document).on('click', '.btn-asistencia-reporte', function() {
    const $btn = $(this);
    const totalEstudiantes = parseInt($btn.data('total-estudiantes'), 10) || 0;

    if (totalEstudiantes === 0) {
        Swal.fire({
            type: 'warning',
            title: 'Sin estudiantes',
            text: 'No hay estudiantes inscritos en este módulo para generar la lista de asistencia'
        });
        return;
    }

    const url = 'vistas/componentes/generar-lista-asistencia-pdf.php?' +
        'moduloID=' + $btn.data('modulo-id') +
        '&programaID=' + $btn.data('programa-id') +
        '&moduloNombre=' + encodeURIComponent($btn.data('modulo-nombre')) +
        '&moduloCodigo=' + encodeURIComponent($btn.data('modulo-codigo')) +
        '&programaNombre=' + encodeURIComponent($btn.data('programa-nombre')) +
        '&grado=' + encodeURIComponent($btn.data('grado')) +
        '&docenteNombre=' + encodeURIComponent(nombreDocenteParaImprimir());

    // Navegación en la misma pestaña: abrir con window.open() dentro de un
    // manejador de clic directo (sin pasar por un .then() de Swal) es seguro,
    // pero se usa window.location.href para evitar cualquier bloqueo de
    // pop-ups del navegador, igual que en el resto de generadores de PDF.
    window.location.href = url;
});

function nombreDocenteParaImprimir() {
    if (!docenteData) return '';
    return docenteData.Menciones
        ? docenteData.Menciones + ' ' + docenteData.NombreCompleto
        : docenteData.NombreCompleto;
}

function generarPlanillaPDF(moduloID, programaID, moduloNombre, moduloCodigo, programaNombre, grado, fechaInicioDB, fechaFinalDB, sigla) {
    Swal.fire({
        title: '<i class="la la-calendar"></i> Fechas del Módulo',
        html: `
            <div style="text-align: left; padding: 20px;">
                <h5 style="color: #5867dd; margin-bottom: 15px;">
                    <i class="la la-book"></i> ${moduloNombre}
                </h5>
                <p style="margin-bottom: 20px; color: #666;">
                    <strong>Código:</strong> ${moduloCodigo}<br>
                    <strong>Programa:</strong> ${programaNombre}${sigla ? ' (' + sigla + ')' : ''}
                </p>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="fechaInicioReporte" style="font-weight: bold; color: #333; display: block; margin-bottom: 5px;">
                        <i class="la la-calendar-check-o"></i> Fecha de Inicio:
                    </label>
                    <input type="date" id="fechaInicioReporte" class="swal2-input" style="width: 100%; padding: 10px; margin: 0;" value="${fechaInicioDB || ''}">
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="fechaFinReporte" style="font-weight: bold; color: #333; display: block; margin-bottom: 5px;">
                        <i class="la la-calendar-times-o"></i> Fecha de Finalización:
                    </label>
                    <input type="date" id="fechaFinReporte" class="swal2-input" style="width: 100%; padding: 10px; margin: 0;" value="${fechaFinalDB || ''}">
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="fechaImpresionReporte" style="font-weight: bold; color: #333; display: block; margin-bottom: 5px;">
                        <i class="la la-print"></i> Fecha de impresión del documento (para la firma "Oruro, ..."):
                    </label>
                    <input type="date" id="fechaImpresionReporte" class="swal2-input" style="width: 100%; padding: 10px; margin: 0;" value="${new Date().toLocaleDateString('sv-SE')}">
                </div>
                <p style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-left: 3px solid #5867dd; font-size: 13px; color: #555;">
                    <i class="la la-info-circle"></i> ${(fechaInicioDB && fechaFinalDB)
                        ? 'Se completaron con las fechas ya registradas para este módulo; puede ajustarlas si hace falta.'
                        : 'Estas fechas se mostrarán en el reporte PDF de calificaciones.'}
                </p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonColor: '#5867dd',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="la la-print"></i> Generar PDF',
        cancelButtonText: 'Cancelar',
        focusConfirm: false,
        preConfirm: () => {
            const fechaInicio = document.getElementById('fechaInicioReporte').value;
            const fechaFin = document.getElementById('fechaFinReporte').value;
            const fechaImpresion = document.getElementById('fechaImpresionReporte').value;

            if (!fechaInicio || !fechaFin || !fechaImpresion) {
                Swal.showValidationMessage('Por favor, complete las tres fechas');
                return false;
            }
            if (fechaInicio > fechaFin) {
                Swal.showValidationMessage('La fecha de inicio debe ser anterior o igual a la fecha de finalización');
                return false;
            }
            return { fechaInicio: fechaInicio, fechaFin: fechaFin, fechaImpresion: fechaImpresion };
        }
    }).then((result) => {
        if (!result.value || result.dismiss) return;

        const url = 'vistas/componentes/generar-pdf-calificaciones-tcpdf.php?' +
            'moduloID=' + moduloID +
            '&programaID=' + programaID +
            '&moduloNombre=' + encodeURIComponent(moduloNombre) +
            '&moduloCodigo=' + encodeURIComponent(moduloCodigo) +
            '&programaNombre=' + encodeURIComponent(programaNombre) +
            '&gradoAcademico=' + encodeURIComponent(grado) +
            '&docenteNombre=' + encodeURIComponent(nombreDocenteParaImprimir()) +
            '&fechaInicio=' + encodeURIComponent(result.value.fechaInicio) +
            '&fechaFin=' + encodeURIComponent(result.value.fechaFin) +
            '&fechaGeneracion=' + encodeURIComponent(result.value.fechaImpresion) +
            '&sigla=' + encodeURIComponent(sigla || '');

        // Abrir con window.open() DENTRO de un .then() de Swal es bloqueado
        // silenciosamente por varios navegadores (la ventana nunca se abre y
        // no muestra ningún aviso). Se navega en la misma pestaña, que es lo
        // que sí funciona de forma confiable en todos los navegadores.
        window.location.href = url;
    });
}

/**
 * ================================================================
 * PONER / VER CALIFICACIONES (desde el mismo reporte)
 * ================================================================
 */

function abrirModalCalificar() {
    $('#modal-calificar-titulo').text(moduloSeleccionado.estadoModulo === 'ACTIVO' ? 'Poner Calificaciones' : 'Ver Calificaciones');
    $('#modal-calificar-modulo').text(moduloSeleccionado.moduloNombre);
    $('#modal-calificar-codigo').text(moduloSeleccionado.moduloCodigo);
    $('#modal-calificar-programa').text(moduloSeleccionado.programaNombre);
    $('#modal-calificar-grado').text(moduloSeleccionado.grado);
    $('#modal-calificar-sigla').text(moduloSeleccionado.sigla || 'No registrada');
    $('#modal-calificar-fecha-inicio').text(formatearFechaDDMMAAAA(moduloSeleccionado.fechaInicio));
    $('#modal-calificar-fecha-final').text(formatearFechaDDMMAAAA(moduloSeleccionado.fechaFinal));
    $('#modal-calificar-aviso').html('');
    $('#modal-calificar-footer').hide();
    $('#modal-calificar-container').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>');

    $('#modalCalificar').modal('show');

    $.ajax({
        url: 'ajax/calificacion.ajax.php',
        method: 'POST',
        data: { accion: 'obtenerEstudiantes', moduloID: moduloSeleccionado.moduloID, programaID: moduloSeleccionado.programaID },
        dataType: 'json',
        success: function(response) {
            if (response.status !== 'success') {
                $('#modal-calificar-container').html('<div class="alert alert-danger">Error al cargar los estudiantes</div>');
                return;
            }
            verificarPermisoEdicionReporte(function(permisoData) {
                const puedeEditar = permisoData.permitido;
                const esModuloCerrado = moduloSeleccionado.estadoModulo !== 'ACTIVO';

                if (esModuloCerrado) {
                    const mensaje = puedeEditar
                        ? '<div class="alert alert-warning"><i class="la la-warning"></i> Este módulo está validado/cerrado. Puede editarlo porque tiene permisos de administrador.</div>'
                        : '<div class="alert alert-danger"><i class="la la-lock"></i> Este módulo está validado/cerrado y no puede editarse. Solo un administrador puede modificarlo.</div>';
                    $('#modal-calificar-aviso').html(mensaje);
                }

                renderizarFormularioCalificarReporte(response.data, puedeEditar);
            });
        },
        error: function() {
            $('#modal-calificar-container').html('<div class="alert alert-danger">Error al conectar con el servidor</div>');
        }
    });
}

function verificarPermisoEdicionReporte(callback) {
    $.ajax({
        url: 'ajax/calificacion.ajax.php',
        method: 'POST',
        data: { accion: 'verificarPermisoEdicion', moduloID: moduloSeleccionado.moduloID },
        dataType: 'json',
        success: function(response) { callback(response); },
        error: function() { callback({ permitido: false }); }
    });
}

function renderizarFormularioCalificarReporte(estudiantes, puedeEditar) {
    if (!estudiantes || estudiantes.length === 0) {
        $('#modal-calificar-container').html('<div class="alert alert-warning text-center"><i class="flaticon-warning"></i> No hay estudiantes inscritos en este módulo</div>');
        return;
    }

    const inputDisabled = !puedeEditar ? 'disabled readonly' : '';
    const inputStyle = !puedeEditar ? 'background-color: #f4f5f8; cursor: not-allowed;' : '';

    let html = `
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-modulos">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 12%;">C.I.</th>
                        <th>Estudiante</th>
                        <th style="width: 18%;">Nota Final</th>
                        <th style="width: 15%;">Estado</th>
                    </tr>
                </thead>
                <tbody>
    `;

    estudiantes.forEach(function(estudiante, index) {
        const nombreCompleto = escaparHtml(`${estudiante.Nombre} ${estudiante.Apaterno} ${estudiante.Amaterno}`);
        const notaActual = estudiante.Nota !== null ? parseFloat(estudiante.Nota) : '';
        const estadoClass = notaActual === '' ? 'kt-badge--secondary' : (parseFloat(notaActual) >= (window.NOTA_APROBACION || 76) ? 'kt-badge--success' : 'kt-badge--danger');
        const estadoTexto = notaActual === '' ? 'Pendiente' : (parseFloat(notaActual) >= (window.NOTA_APROBACION || 76) ? 'Aprobado' : 'Reprobado');
        const notaInputClass = notaActual === '' ? '' : (parseFloat(notaActual) >= (window.NOTA_APROBACION || 76) ? 'is-valid' : 'is-invalid');

        html += `
            <tr>
                <td class="text-center">${index + 1}</td>
                <td class="text-center">${escaparHtml(estudiante.Ci)}</td>
                <td>${nombreCompleto}</td>
                <td>
                    <input type="number" class="form-control input-nota-reporte ${notaInputClass}"
                        value="${notaActual}" min="0" max="100" step="0.01"
                        data-estudiante-id="${estudiante.EstudianteID}"
                        onchange="validarNotaReporte(this)"
                        placeholder="0.00" style="${inputStyle}" ${inputDisabled} required>
                </td>
                <td class="text-center">
                    <span class="kt-badge kt-badge--xl ${estadoClass}" data-estudiante-id="${estudiante.EstudianteID}">${estadoTexto}</span>
                </td>
            </tr>
        `;
    });

    html += '</tbody></table></div>';
    $('#modal-calificar-container').html(html);

    if (puedeEditar) {
        $('#modal-calificar-footer').show();
    }
}

function validarNotaReporte(input) {
    const nota = parseFloat(input.value);
    const estudianteID = $(input).data('estudiante-id');
    const estadoBadge = $(`#modal-calificar-container .kt-badge[data-estudiante-id="${estudianteID}"]`);

    $(input).removeClass('is-valid is-invalid');

    if (isNaN(nota) || nota < 0 || nota > 100) {
        $(input).addClass('is-invalid');
        estadoBadge.removeClass('kt-badge--success kt-badge--danger kt-badge--secondary').addClass('kt-badge--warning').text('Inválida');
        return;
    }

    if (nota >= (window.NOTA_APROBACION || 76)) {
        $(input).addClass('is-valid');
        estadoBadge.removeClass('kt-badge--danger kt-badge--warning kt-badge--secondary').addClass('kt-badge--success').text('Aprobado');
    } else {
        $(input).addClass('is-invalid');
        estadoBadge.removeClass('kt-badge--success kt-badge--warning kt-badge--secondary').addClass('kt-badge--danger').text('Reprobado');
    }
}

function guardarCalificacionesReporte() {
    const calificaciones = [];
    let hayErrores = false;

    $('.input-nota-reporte').each(function() {
        const estudianteID = $(this).data('estudiante-id');
        const valorNota = $(this).val();
        const nota = parseFloat(valorNota);

        if (valorNota === '' || isNaN(nota) || nota < 0 || nota > 100) {
            hayErrores = true;
            $(this).addClass('is-invalid');
            return;
        }

        calificaciones.push({ estudianteID: estudianteID, nota: nota });
    });

    if (hayErrores) {
        Swal.fire({ type: 'error', title: 'Error de validación', text: 'Hay calificaciones inválidas o vacías. Corrija antes de guardar.' });
        return;
    }

    if (calificaciones.length === 0) {
        Swal.fire({ type: 'warning', title: 'Sin datos', text: 'No hay calificaciones para guardar' });
        return;
    }

    Swal.fire({
        title: `¿Guardar ${calificaciones.length} calificación(es)?`,
        text: moduloSeleccionado.moduloNombre,
        type: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (!result.value) return;

        $.ajax({
            url: 'ajax/calificacion.ajax.php',
            method: 'POST',
            data: {
                accion: 'guardarCalificaciones',
                programaID: moduloSeleccionado.programaID,
                moduloID: moduloSeleccionado.moduloID,
                calificaciones: JSON.stringify(calificaciones)
            },
            dataType: 'json',
            beforeSend: function() {
                Swal.fire({ title: 'Guardando...', allowOutsideClick: false, onOpen: () => Swal.showLoading() });
            },
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({ type: 'success', title: 'Calificaciones guardadas', timer: 1500, showConfirmButton: false }).then(() => {
                        $('#modalCalificar').modal('hide');
                        cargarModulosDocente();
                    });
                } else {
                    Swal.fire({ type: 'error', title: 'Error', text: response.message || 'Error al guardar' });
                }
            },
            error: function() {
                Swal.fire({ type: 'error', title: 'Error de conexión', text: 'No se pudo guardar' });
            }
        });
    });
}

function validarCerrarModuloReporte(moduloID, moduloNombre) {
    Swal.fire({
        title: '¿Validar y cerrar módulo?',
        html: `<p>Está a punto de <strong>validar y cerrar</strong>:</p><p class="text-primary"><strong>${escaparHtml(moduloNombre)}</strong></p><p class="text-danger"><i class="la la-warning"></i> Una vez cerrado, solo un administrador podrá modificar las calificaciones.</p>`,
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, validar y cerrar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#f4516c'
    }).then((result) => {
        if (!result.value) return;
        $.ajax({
            url: 'ajax/calificacion.ajax.php',
            method: 'POST',
            data: { accion: 'validarCerrarModulo', moduloID: moduloID },
            dataType: 'json',
            beforeSend: function() { Swal.fire({ title: 'Procesando...', allowOutsideClick: false, onOpen: () => Swal.showLoading() }); },
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({ type: 'success', title: 'Módulo validado', text: response.message, timer: 2000 }).then(() => cargarModulosDocente());
                } else {
                    Swal.fire({ type: response.status === 'warning' ? 'warning' : 'error', title: response.status === 'warning' ? 'Advertencia' : 'Error', text: response.message });
                }
            },
            error: function() {
                Swal.fire({ type: 'error', title: 'Error de conexión', text: 'No se pudo validar el módulo' });
            }
        });
    });
}

function reabrirModuloReporte(moduloID, moduloNombre) {
    Swal.fire({
        title: '¿Reabrir módulo?',
        html: `<p>Está a punto de <strong>reabrir</strong>:</p><p class="text-primary"><strong>${escaparHtml(moduloNombre)}</strong></p><p class="text-warning"><i class="la la-info-circle"></i> Solo disponible para administradores.</p>`,
        type: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, reabrir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (!result.value) return;
        $.ajax({
            url: 'ajax/calificacion.ajax.php',
            method: 'POST',
            data: { accion: 'reabrirModulo', moduloID: moduloID },
            dataType: 'json',
            beforeSend: function() { Swal.fire({ title: 'Procesando...', allowOutsideClick: false, onOpen: () => Swal.showLoading() }); },
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({ type: 'success', title: 'Módulo reabierto', text: response.message, timer: 2000 }).then(() => cargarModulosDocente());
                } else {
                    Swal.fire({ type: 'error', title: 'Error', text: response.message });
                }
            },
            error: function() {
                Swal.fire({ type: 'error', title: 'Error de conexión', text: 'No se pudo reabrir el módulo' });
            }
        });
    });
}

function mostrarSinModulos() {
    $('#tabla-modulos-container').html(`
        <div class="alert alert-warning text-center py-5">
            <i class="fa fa-info-circle" style="font-size: 3rem;"></i>
            <h4 class="mt-3">No tiene modulos asignados</h4>
            <p class="text-muted">No se encontraron modulos asignados a su usuario</p>
        </div>
    `);
}

function mostrarError(mensaje) {
    $('#tabla-modulos-container').html(`
        <div class="alert alert-danger text-center py-5">
            <i class="fa fa-times-circle" style="font-size: 3rem;"></i>
            <h4 class="mt-3">Error</h4>
            <p>${mensaje}</p>
            <button class="btn btn-primary" onclick="location.reload()">
                <i class="fa fa-sync-alt"></i> Reintentar
            </button>
        </div>
    `);
}

function generarReporteCompleto() {
    if (!docenteData) {
        Swal.fire({
            type: 'error',
            title: 'Error',
            text: 'No se ha cargado la información del docente'
        });
        return;
    }

    Swal.fire({
        title: 'Generar Reporte Completo',
        html: '<p>Se generará un reporte PDF con <strong>TODOS</strong> sus módulos asignados y sus calificaciones.</p>' +
              '<p class="text-muted mt-2">Este reporte incluirá una página por cada módulo con su respectiva planilla de calificaciones.</p>' +
              '<div class="form-group text-left" style="margin-top: 15px;">' +
              '<label for="fechaImpresionReporteCompleto" style="font-weight: bold; color: #333; display: block; margin-bottom: 5px;">' +
              '<i class="fa fa-print"></i> Fecha de impresión del documento (para la firma "Oruro, ..."):</label>' +
              '<input type="date" id="fechaImpresionReporteCompleto" class="swal2-input" style="width: 100%; padding: 10px; margin: 0;" value="' + new Date().toLocaleDateString('sv-SE') + '">' +
              '</div>',
        type: 'info',
        showCancelButton: true,
        confirmButtonText: '<i class="fa fa-print"></i> Generar Reporte PDF',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
            const fechaImpresion = document.getElementById('fechaImpresionReporteCompleto').value;
            if (!fechaImpresion) {
                Swal.showValidationMessage('Por favor seleccione la fecha de impresión');
                return false;
            }
            return fechaImpresion;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // window.open() dentro de un .then() de Swal es bloqueado
            // silenciosamente por varios navegadores (por eso "no daba el
            // reporte"): se navega en la misma pestaña en su lugar, que
            // siempre funciona.
            window.location.href = 'tcpdf/pdf/reporte-completo-docente.php?fechaGeneracion=' + encodeURIComponent(result.value);
        }
    });
}
</script>

</body>
