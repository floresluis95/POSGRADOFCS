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
                            <div class="kt-portlet__head" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 10px 10px 0 0;">
                                <div class="kt-portlet__head-label">
                                    <h3 class="kt-portlet__head-title text-white">
                                        <i class="fa fa-chart-bar"></i> Mis Modulos Asignados - Estado de Calificaciones
                                    </h3>
                                </div>
                                <div class="kt-portlet__head-toolbar">
                                    <button type="button" class="btn btn-danger btn-lg mr-3" id="btn-reporte-completo" style="font-weight: 600;">
                                        <i class="fa fa-file-pdf"></i> IMPRIMIR REPORTE COMPLETO
                                    </button>
                                    <button type="button" class="btn btn-light btn-sm" onclick="location.reload()">
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
                                <div id="info-docente" class="alert alert-light mb-4" style="display: none; border-left: 4px solid #1e3c72;">
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

                                <!-- TABLA DE M�DULOS -->
                                <div id="tabla-modulos-container"></div>

                            </div>
                        </div>
                    </div>
                    <!-- FIN DEL CONTENIDO PRINCIPAL -->

<!-- MODAL PARA SELECCIONAR FECHAS DEL PDF -->
<div class="modal fade" id="modalFechasPDF" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                <h5 class="modal-title text-white">
                    <i class="fa fa-calendar"></i> Seleccionar Fechas para el PDF
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3">
                    <i class="fa fa-book"></i> <strong>Módulo:</strong> <span id="modal-modulo-nombre"></span>
                </div>
                <div class="form-group">
                    <label for="sigla-modulo-pdf-reporte" class="font-weight-bold">
                        <i class="fa fa-tag"></i> Sigla del Módulo:
                    </label>
                    <input type="text" class="form-control" id="sigla-modulo-pdf-reporte" placeholder="Ej: ODO-01, EST-02, etc." maxlength="20">
                    <small class="form-text text-muted">Opcional: Identificador corto del módulo</small>
                </div>
                <div class="form-group">
                    <label for="fecha-inicio-pdf-reporte" class="font-weight-bold">
                        <i class="fa fa-calendar-alt"></i> Fecha de Inicio:
                    </label>
                    <input type="date" class="form-control" id="fecha-inicio-pdf-reporte" required>
                </div>
                <div class="form-group">
                    <label for="fecha-fin-pdf-reporte" class="font-weight-bold">
                        <i class="fa fa-calendar-check"></i> Fecha de Fin:
                    </label>
                    <input type="date" class="form-control" id="fecha-fin-pdf-reporte" required>
                </div>
                <div class="alert alert-light mt-3">
                    <i class="fa fa-info-circle"></i> Las fechas y la sigla se incluirán en el PDF generado.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btn-confirmar-pdf-reporte">
                    <i class="fa fa-print"></i> Generar PDF
                </button>
            </div>
        </div>
    </div>
</div>

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
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
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
                            <strong><i class="fa fa-code"></i> Código:</strong> <span id="modal-calificar-codigo"></span>
                        </div>
                        <div class="col-md-6">
                            <strong><i class="fa fa-university"></i> Programa:</strong> <span id="modal-calificar-programa"></span><br>
                            <strong><i class="fa fa-graduation-cap"></i> Grado:</strong> <span id="modal-calificar-grado"></span>
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
.table-modulos {
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.table-modulos thead {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    color: white;
}

.table-modulos tbody tr:hover {
    background-color: #f8f9fa;
    transform: scale(1.002);
    transition: all 0.2s ease;
}

.btn-print-planilla {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    border: none;
    color: white;
    font-weight: 600;
    padding: 8px 20px;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.btn-print-planilla:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
    color: white;
}

.badge-modulo {
    padding: 8px 15px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 20px;
}
</style>

<script>
let docenteData = null;
let moduloParaPDF = null;
let moduloSeleccionado = null;

$(document).ready(function() {
    // Cargar módulos del docente
    cargarModulosDocente();

    // Actualizar fecha
    actualizarFecha();
    setInterval(actualizarFecha, 60000);

    // Botón reporte completo
    $('#btn-reporte-completo').on('click', function() {
        generarReporteCompleto();
    });

    // Event listener para confirmar PDF con fechas
    $('#btn-confirmar-pdf-reporte').on('click', function() {
        generarPDFConFechas();
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
            estadoModulo: $(this).data('estado-modulo')
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
                $('#total-modulos').text(response.data.length);
                mostrarTablaModulos(response.data);
            } else {
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
                        <th class="text-center" style="width: 5%;">Nro</th>
                        <th style="width: 12%;">Codigo</th>
                        <th style="width: 20%;">Modulo</th>
                        <th style="width: 18%;">Programa</th>
                        <th class="text-center" style="width: 8%;">Inscritos</th>
                        <th class="text-center" style="width: 10%;">Calificados</th>
                        <th class="text-center" style="width: 12%;">Estado Modulo</th>
                        <th class="text-center" style="width: 20%;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
    `;

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

        html += `
            <tr>
                <td class="text-center">${index + 1}</td>
                <td><strong>${modulo.codigomodulo}</strong></td>
                <td>${modulo.nombremodulo}</td>
                <td>${modulo.NombrePrograma}<br><small class="text-muted">${modulo.GradoAcademico}</small></td>
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
                    <br><button type="button" class="btn btn-info btn-xs mt-1 btn-reabrir"
                            data-modulo-id="${modulo.Idmodulo}" data-modulo-nombre="${escaparHtml(modulo.nombremodulo)}">
                        <i class="la la-unlock"></i> Reabrir
                    </button>
            `;
        } else if (calificados > 0) {
            html += `
                    <br><button type="button" class="btn btn-warning btn-xs mt-1 btn-validar"
                            data-modulo-id="${modulo.Idmodulo}" data-modulo-nombre="${escaparHtml(modulo.nombremodulo)}">
                        <i class="la la-lock"></i> Validar y Cerrar
                    </button>
            `;
        }

        html += `
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-brand btn-sm mb-1 btn-calificar"
                            data-modulo-id="${modulo.Idmodulo}" data-programa-id="${modulo.ProgramaID}"
                            data-modulo-nombre="${escaparHtml(modulo.nombremodulo)}" data-modulo-codigo="${escaparHtml(modulo.codigomodulo)}"
                            data-programa-nombre="${escaparHtml(modulo.NombrePrograma)}" data-grado="${escaparHtml(modulo.GradoAcademico)}"
                            data-estado-modulo="${estadoModulo}">
                        <i class="la la-edit"></i> ${moduloCerrado ? 'Ver Notas' : 'Poner Notas'}
                    </button>
        `;

        if (calificados > 0) {
            html += `
                <button type="button" class="btn btn-print-planilla btn-sm mb-1"
                        onclick="generarPlanillaPDF(${modulo.Idmodulo}, ${modulo.ProgramaID}, '${modulo.nombremodulo}', '${modulo.codigomodulo}', '${modulo.NombrePrograma}', '${modulo.GradoAcademico}')">
                    <i class="fa fa-print"></i> IMPRIMIR
                </button>
            `;
        }

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

function generarPlanillaPDF(moduloID, programaID, moduloNombre, moduloCodigo, programaNombre, grado) {
    console.log('>>> Abrir modal para generar PDF');

    // Guardar datos del módulo
    moduloParaPDF = {
        moduloID: moduloID,
        programaID: programaID,
        moduloNombre: moduloNombre,
        moduloCodigo: moduloCodigo,
        programaNombre: programaNombre,
        grado: grado
    };

    // Mostrar nombre del módulo en el modal
    $('#modal-modulo-nombre').text(moduloNombre);

    // Limpiar campos del formulario
    $('#sigla-modulo-pdf-reporte').val('');
    $('#fecha-inicio-pdf-reporte').val('');
    $('#fecha-fin-pdf-reporte').val('');

    // Abrir modal
    $('#modalFechasPDF').modal('show');
}

function generarPDFConFechas() {
    console.log('>>> Generando PDF con fechas');

    // Validar que hay datos del módulo
    if (!moduloParaPDF) {
        Swal.fire({
            type: 'error',
            title: 'Error',
            text: 'No hay datos del módulo seleccionado'
        });
        return;
    }

    // Obtener datos del formulario
    const siglaModulo = $('#sigla-modulo-pdf-reporte').val().trim();
    const fechaInicio = $('#fecha-inicio-pdf-reporte').val();
    const fechaFin = $('#fecha-fin-pdf-reporte').val();

    // Validar fechas
    if (!fechaInicio || !fechaFin) {
        Swal.fire({
            type: 'warning',
            title: 'Fechas requeridas',
            text: 'Por favor ingrese ambas fechas (inicio y fin)'
        });
        return;
    }

    // Validar que fecha inicio sea menor o igual a fecha fin
    if (new Date(fechaInicio) > new Date(fechaFin)) {
        Swal.fire({
            type: 'error',
            title: 'Fechas inválidas',
            text: 'La fecha de inicio debe ser menor o igual a la fecha de fin'
        });
        return;
    }

    console.log('>>> Datos para el PDF:');
    console.log('>>> Módulo:', moduloParaPDF);
    console.log('>>> Docente:', docenteData);
    console.log('>>> Sigla:', siglaModulo);
    console.log('>>> Fecha Inicio:', fechaInicio);
    console.log('>>> Fecha Fin:', fechaFin);

    // Cerrar modal
    $('#modalFechasPDF').modal('hide');

    // Crear formulario dinámico para POST
    const form = $('<form>', {
        action: 'tcpdf/pdf/generar-calificaciones-pdf.php',
        method: 'POST',
        target: '_blank'
    });

    // Agregar campos al formulario
    form.append($('<input>', { type: 'hidden', name: 'programaNombre', value: moduloParaPDF.programaNombre }));
    form.append($('<input>', { type: 'hidden', name: 'moduloNombre', value: moduloParaPDF.moduloNombre }));
    form.append($('<input>', { type: 'hidden', name: 'moduloCodigo', value: moduloParaPDF.moduloCodigo }));
    form.append($('<input>', { type: 'hidden', name: 'docenteNombre', value: docenteData.NombreCompleto }));
    form.append($('<input>', { type: 'hidden', name: 'siglaModulo', value: siglaModulo }));
    form.append($('<input>', { type: 'hidden', name: 'fechaInicio', value: fechaInicio }));
    form.append($('<input>', { type: 'hidden', name: 'fechaFin', value: fechaFin }));
    form.append($('<input>', { type: 'hidden', name: 'moduloID', value: moduloParaPDF.moduloID }));
    form.append($('<input>', { type: 'hidden', name: 'programaID', value: moduloParaPDF.programaID }));
    form.append($('<input>', { type: 'hidden', name: 'grado', value: moduloParaPDF.grado }));

    // Agregar formulario al body, enviarlo y eliminarlo
    form.appendTo('body').submit().remove();

    console.log('>>> Formulario enviado para generar PDF con fechas y sigla');

    // Mostrar mensaje de éxito
    Swal.fire({
        type: 'success',
        title: 'PDF generado',
        text: 'El PDF se está generando en una nueva pestaña',
        timer: 2000,
        showConfirmButton: false
    });

    // Limpiar datos temporales
    moduloParaPDF = null;
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
        const estadoClass = notaActual === '' ? 'kt-badge--secondary' : (parseFloat(notaActual) >= 76 ? 'kt-badge--success' : 'kt-badge--danger');
        const estadoTexto = notaActual === '' ? 'Pendiente' : (parseFloat(notaActual) >= 76 ? 'Aprobado' : 'Reprobado');
        const notaInputClass = notaActual === '' ? '' : (parseFloat(notaActual) >= 76 ? 'is-valid' : 'is-invalid');

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

    if (nota >= 76) {
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
              '<p class="text-muted mt-2">Este reporte incluirá una página por cada módulo con su respectiva planilla de calificaciones.</p>',
        type: 'info',
        showCancelButton: true,
        confirmButtonText: '<i class="fa fa-print"></i> Generar Reporte PDF',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            // Abrir el PDF en nueva pestaña
            window.open('tcpdf/pdf/reporte-completo-docente.php', '_blank');

            Swal.fire({
                type: 'success',
                title: 'Generando reporte...',
                text: 'El PDF se abrirá en una nueva pestaña',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
}
</script>

</body>
