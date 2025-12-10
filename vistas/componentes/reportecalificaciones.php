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
                                    <p class="mt-3">Cargando m�dulos asignados...</p>
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
            mostrarError('Error al cargar los m�dulos asignados');
        }
    });
}

function mostrarTablaModulos(modulos) {
    let html = `
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-modulos">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 5%;">N�</th>
                        <th style="width: 15%;">C�digo</th>
                        <th style="width: 25%;">M�dulo</th>
                        <th style="width: 20%;">Programa</th>
                        <th class="text-center" style="width: 10%;">Inscritos</th>
                        <th class="text-center" style="width: 10%;">Calificados</th>
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
    Swal.fire({
        title: 'Imprimir Planilla de Calificaciones',
        html: '<p>Modulo: <strong>' + moduloNombre + '</strong></p>' +
              '<label for="fechaPlanillaPDF" style="font-weight: bold; display: block; margin-top: 15px;">Fecha de la planilla:</label>' +
              '<input type="date" id="fechaPlanillaPDF" class="swal2-input" style="width: 80%; margin-top: 5px;" required>',
        type: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fa fa-print"></i> Generar PDF',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
            const fecha = document.getElementById('fechaPlanillaPDF').value;
            if (!fecha) {
                Swal.showValidationMessage('Por favor seleccione una fecha');
                return false;
            }
            return fecha;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const fecha = result.value;

            // Crear formulario para enviar por POST
            const form = $('<form>', {
                action: 'tcpdf/pdf/generar-calificaciones-pdf.php',
                method: 'POST',
                target: '_blank'
            });

            form.append($('<input>', { type: 'hidden', name: 'programaNombre', value: programaNombre }));
            form.append($('<input>', { type: 'hidden', name: 'moduloNombre', value: moduloNombre }));
            form.append($('<input>', { type: 'hidden', name: 'moduloCodigo', value: moduloCodigo }));
            form.append($('<input>', { type: 'hidden', name: 'docenteNombre', value: docenteData.NombreCompleto }));
            form.append($('<input>', { type: 'hidden', name: 'fechaPlanilla', value: fecha }));
            form.append($('<input>', { type: 'hidden', name: 'moduloID', value: moduloID }));
            form.append($('<input>', { type: 'hidden', name: 'programaID', value: programaID }));
            form.append($('<input>', { type: 'hidden', name: 'grado', value: grado }));

            form.appendTo('body').submit().remove();
        }
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
