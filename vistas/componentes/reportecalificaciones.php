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
                            <div class="kt-portlet__head" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px 10px 0 0;">
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
                                <div id="info-docente" class="alert alert-light mb-4" style="display: none; border-left: 4px solid #1dc9b7;">
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
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
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

                    <?php $Footer = new FuncionesControladores(); $Footer->FooterControlador(); ?>
                </div>
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
});

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
                        <th class="text-center" style="width: 15%;">Acciones</th>
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
        let estadoModuloBadge = '';
        let estadoModuloIcono = '';

        if (estadoModulo === 'VALIDADO' || estadoModulo === 'CERRADO') {
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
                </td>
                <td class="text-center">
        `;

        if (calificados > 0) {
            html += `
                <button type="button" class="btn btn-print-planilla btn-sm"
                        onclick="generarPlanillaPDF(${modulo.Idmodulo}, ${modulo.ProgramaID}, '${modulo.nombremodulo}', '${modulo.codigomodulo}', '${modulo.NombrePrograma}', '${modulo.GradoAcademico}')">
                    <i class="fa fa-print"></i> IMPRIMIR PLANILLA
                </button>
            `;
        } else {
            html += `
                <button type="button" class="btn btn-secondary btn-sm" disabled>
                    <i class="fa fa-ban"></i> Sin calificaciones
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
