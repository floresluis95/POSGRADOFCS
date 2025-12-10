/**
 * Script para Reporte de Notas
 * Maneja la visualización de módulos, generación de PDF y panel de auditoría
 */

$(document).ready(function() {
    // Inicializar DataTables
    $('#tablaProgramas').DataTable({
        language: {
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            },
            emptyTable: "No hay programas disponibles",
            zeroRecords: "No se encontraron resultados"
        }
    });

    // Evento: Ver módulos del programa
    $(document).on('click', '.btnVerModulos', function() {
        const programaID = $(this).data('programa-id');
        const programaNombre = $(this).data('programa-nombre');

        $('#nombreProgramaModal').text(programaNombre);
        $('#loadingModulos').show();
        $('#contenedorModulos').html('');

        // Petición AJAX para obtener módulos
        $.ajax({
            url: 'ajax/reportenotas.ajax.php',
            type: 'POST',
            data: {
                action: 'obtenerModulos',
                programaID: programaID
            },
            dataType: 'json',
            success: function(response) {
                $('#loadingModulos').hide();

                if (response.status === 'success' && response.data.length > 0) {
                    let html = '<div class="table-responsive"><table class="table table-striped table-bordered">';
                    html += '<thead class="thead-light">';
                    html += '<tr>';
                    html += '<th class="text-center" style="width: 5%;">Nº</th>';
                    html += '<th>Código</th>';
                    html += '<th>Módulo</th>';
                    html += '<th>Docente</th>';
                    html += '<th class="text-center">Inscritos</th>';
                    html += '<th class="text-center">Calificados</th>';
                    html += '<th class="text-center">Estado</th>';
                    html += '<th class="text-center">Acciones</th>';
                    html += '</tr>';
                    html += '</thead><tbody>';

                    let contador = 0;
                    response.data.forEach(function(modulo) {
                        contador++;
                        const inscritos = parseInt(modulo.TotalInscritos) || 0;
                        const calificados = parseInt(modulo.TotalCalificados) || 0;
                        const porcentaje = inscritos > 0 ? Math.round((calificados / inscritos) * 100) : 0;

                        let estadoBadge = '';
                        if (calificados === 0) {
                            estadoBadge = '<span class="badge badge-secondary">Sin calificar</span>';
                        } else if (calificados < inscritos) {
                            estadoBadge = '<span class="badge badge-warning">Parcial (' + porcentaje + '%)</span>';
                        } else {
                            estadoBadge = '<span class="badge badge-success">Completo (100%)</span>';
                        }

                        html += '<tr>';
                        html += '<td class="text-center">' + contador + '</td>';
                        html += '<td><strong>' + (modulo.codigomodulo || 'N/A') + '</strong></td>';
                        html += '<td>' + modulo.nombremodulo + '</td>';
                        html += '<td>' + (modulo.NombreDocente || '<span class="text-muted">Sin docente</span>') + '</td>';
                        html += '<td class="text-center"><span class="badge badge-info">' + inscritos + '</span></td>';
                        html += '<td class="text-center"><span class="badge badge-primary">' + calificados + '</span></td>';
                        html += '<td class="text-center">' + estadoBadge + '</td>';
                        html += '<td class="text-center">';

                        if (calificados > 0) {
                            html += '<div class="btn-group" role="group">';
                            html += '<button type="button" class="btn btn-danger btn-sm btnGenerarPDF" ' +
                                   'data-modulo-id="' + modulo.Idmodulo + '" ' +
                                   'data-programa-id="' + (modulo.ProgramaId || programaID) + '" ' +
                                   'data-modulo-nombre="' + modulo.nombremodulo + '" ' +
                                   'data-modulo-codigo="' + (modulo.codigomodulo || '') + '" ' +
                                   'data-programa-nombre="' + (modulo.NombrePrograma || programaNombre) + '" ' +
                                   'data-docente-nombre="' + (modulo.NombreDocente || '') + '" ' +
                                   'data-grado="' + (modulo.GradoAcademico || '') + '" ' +
                                   'title="Imprimir Planilla de Calificaciones">' +
                                   '<i class="fas fa-print"></i> IMPRIMIR' +
                                   '</button>';
                            html += '<button type="button" class="btn btn-info btn-sm btnVerAuditoria" ' +
                                   'data-modulo-id="' + modulo.Idmodulo + '" ' +
                                   'data-programa-id="' + (modulo.ProgramaId || programaID) + '" ' +
                                   'data-modulo-nombre="' + modulo.nombremodulo + '" ' +
                                   'title="Ver Panel de Auditoría">' +
                                   '<i class="fas fa-history"></i> AUDITORÍA' +
                                   '</button>';
                            html += '</div>';
                        } else {
                            html += '<button type="button" class="btn btn-secondary btn-sm" disabled>' +
                                   '<i class="fas fa-ban"></i> Sin notas' +
                                   '</button>';
                        }

                        html += '</td>';
                        html += '</tr>';
                    });

                    html += '</tbody></table></div>';
                    $('#contenedorModulos').html(html);
                } else {
                    $('#contenedorModulos').html('<div class="alert alert-warning text-center">' +
                        '<i class="fas fa-exclamation-triangle"></i> Este programa no tiene módulos registrados.' +
                        '</div>');
                }
            },
            error: function() {
                $('#loadingModulos').hide();
                $('#contenedorModulos').html('<div class="alert alert-danger text-center">' +
                    '<i class="fas fa-times-circle"></i> Error al cargar los módulos.' +
                    '</div>');
            }
        });
    });

    // Evento: Generar PDF con selección de fecha
    $(document).on('click', '.btnGenerarPDF', function() {
        const moduloID = $(this).data('modulo-id');
        const programaID = $(this).data('programa-id');
        const moduloNombre = $(this).data('modulo-nombre');
        const moduloCodigo = $(this).data('modulo-codigo');
        const programaNombre = $(this).data('programa-nombre');
        const docenteNombre = $(this).data('docente-nombre');
        const grado = $(this).data('grado');

        // Crear modal para seleccionar fecha
        Swal.fire({
            title: 'Generar Planilla de Calificaciones',
            html: '<p>Módulo: <strong>' + moduloNombre + '</strong></p>' +
                  '<label for="fechaPlanillaPDF" style="font-weight: bold; display: block; margin-top: 15px;">Fecha de la planilla:</label>' +
                  '<input type="date" id="fechaPlanillaPDF" class="swal2-input" style="width: 80%; margin-top: 5px;" required>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fa fa-print"></i> Generar PDF',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
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

                // Crear formulario dinámico para enviar datos por POST
                const form = $('<form>', {
                    action: 'tcpdf/pdf/generar-calificaciones-pdf.php',
                    method: 'POST',
                    target: '_blank'
                });

                form.append($('<input>', { type: 'hidden', name: 'programaNombre', value: programaNombre || '' }));
                form.append($('<input>', { type: 'hidden', name: 'moduloNombre', value: moduloNombre }));
                form.append($('<input>', { type: 'hidden', name: 'moduloCodigo', value: moduloCodigo || '' }));
                form.append($('<input>', { type: 'hidden', name: 'docenteNombre', value: docenteNombre || '' }));
                form.append($('<input>', { type: 'hidden', name: 'fechaPlanilla', value: fecha }));
                form.append($('<input>', { type: 'hidden', name: 'moduloID', value: moduloID }));
                form.append($('<input>', { type: 'hidden', name: 'programaID', value: programaID }));
                form.append($('<input>', { type: 'hidden', name: 'grado', value: grado || '' }));

                form.appendTo('body').submit().remove();
            }
        });
    });

    // Evento: Ver Panel de Auditoría
    $(document).on('click', '.btnVerAuditoria', function() {
        const moduloID = $(this).data('modulo-id');
        const programaID = $(this).data('programa-id');
        const moduloNombre = $(this).data('modulo-nombre');

        $('#nombreModuloAuditoria').text(moduloNombre);
        $('#loadingAuditoria').show();
        $('#resumenAuditoria').html('');
        $('#contenedorAuditoria').html('');

        // Mostrar el modal
        $('#ModalAuditoria').modal('show');

        // Petición AJAX para obtener auditoría
        $.ajax({
            url: 'ajax/reportenotas.ajax.php',
            type: 'POST',
            data: {
                action: 'obtenerAuditoria',
                moduloID: moduloID,
                programaID: programaID
            },
            dataType: 'json',
            success: function(response) {
                $('#loadingAuditoria').hide();

                if (response.status === 'success') {
                    // Mostrar resumen
                    if (response.resumen) {
                        let resumenHTML = '<div class="card mb-4" style="border-left: 4px solid #f5576c;">';
                        resumenHTML += '<div class="card-body">';
                        resumenHTML += '<h5 class="card-title"><i class="fas fa-chart-bar"></i> Resumen de Auditoría</h5>';
                        resumenHTML += '<div class="row">';
                        resumenHTML += '<div class="col-md-3">';
                        resumenHTML += '<p class="mb-1"><strong>Total Calificaciones:</strong></p>';
                        resumenHTML += '<h4 class="text-primary">' + (response.resumen.TotalCalificaciones || 0) + '</h4>';
                        resumenHTML += '</div>';
                        resumenHTML += '<div class="col-md-3">';
                        resumenHTML += '<p class="mb-1"><strong>Modificadas:</strong></p>';
                        resumenHTML += '<h4 class="text-warning">' + (response.resumen.TotalModificadas || 0) + '</h4>';
                        resumenHTML += '</div>';
                        resumenHTML += '<div class="col-md-3">';
                        resumenHTML += '<p class="mb-1"><strong>Primera Fecha Registro:</strong></p>';
                        resumenHTML += '<p>' + (response.resumen.PrimeraFechaRegistro || 'N/A') + '</p>';
                        resumenHTML += '</div>';
                        resumenHTML += '<div class="col-md-3">';
                        resumenHTML += '<p class="mb-1"><strong>Última Modificación:</strong></p>';
                        resumenHTML += '<p>' + (response.resumen.UltimaModificacion || 'N/A') + '</p>';
                        resumenHTML += '</div>';
                        resumenHTML += '</div></div></div>';
                        $('#resumenAuditoria').html(resumenHTML);
                    }

                    // Mostrar tabla de auditoría
                    if (response.data && response.data.length > 0) {
                        let html = '<div class="table-responsive"><table class="table table-striped table-bordered table-hover" id="tablaAuditoria">';
                        html += '<thead class="thead-dark">';
                        html += '<tr>';
                        html += '<th class="text-center" style="width: 5%;">Nº</th>';
                        html += '<th>Estudiante</th>';
                        html += '<th class="text-center">Nota</th>';
                        html += '<th>Registrado Por</th>';
                        html += '<th class="text-center">Fecha Registro</th>';
                        html += '<th>Modificado Por</th>';
                        html += '<th class="text-center">Fecha Modificación</th>';
                        html += '<th class="text-center">Estado</th>';
                        html += '</tr>';
                        html += '</thead><tbody>';

                        let contador = 0;
                        response.data.forEach(function(item) {
                            contador++;

                            let badgeModificado = item.FueModificado === 'SÍ'
                                ? '<span class="badge badge-warning">MODIFICADO</span>'
                                : '<span class="badge badge-success">ORIGINAL</span>';

                            html += '<tr>';
                            html += '<td class="text-center">' + contador + '</td>';
                            html += '<td>' + item.NombreEstudiante + '</td>';
                            html += '<td class="text-center"><strong>' + (item.Nota || 'N/A') + '</strong></td>';
                            html += '<td>' + (item.UsuarioRegistro || '<span class="text-muted">No registrado</span>');
                            if (item.TipoUsuarioRegistro) {
                                html += ' <span class="badge badge-secondary badge-sm">' + item.TipoUsuarioRegistro + '</span>';
                            }
                            html += '</td>';
                            html += '<td class="text-center">' + (item.FechaRegistro || 'N/A') + '</td>';
                            html += '<td>' + (item.UsuarioModificacion || '<span class="text-muted">No modificado</span>');
                            if (item.TipoUsuarioModificacion) {
                                html += ' <span class="badge badge-secondary badge-sm">' + item.TipoUsuarioModificacion + '</span>';
                            }
                            html += '</td>';
                            html += '<td class="text-center">' + (item.FechaModificacion || '<span class="text-muted">-</span>') + '</td>';
                            html += '<td class="text-center">' + badgeModificado + '</td>';
                            html += '</tr>';
                        });

                        html += '</tbody></table></div>';
                        $('#contenedorAuditoria').html(html);

                        // Inicializar DataTable
                        $('#tablaAuditoria').DataTable({
                            language: {
                                search: "Buscar:",
                                lengthMenu: "Mostrar _MENU_ registros",
                                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                                paginate: {
                                    first: "Primero",
                                    last: "Último",
                                    next: "Siguiente",
                                    previous: "Anterior"
                                },
                                emptyTable: "No hay datos de auditoría",
                                zeroRecords: "No se encontraron resultados"
                            },
                            order: [[6, 'desc']], // Ordenar por fecha de modificación descendente
                            pageLength: 10
                        });
                    } else {
                        $('#contenedorAuditoria').html('<div class="alert alert-info text-center">' +
                            '<i class="fas fa-info-circle"></i> No hay datos de auditoría para este módulo.' +
                            '</div>');
                    }
                } else {
                    $('#contenedorAuditoria').html('<div class="alert alert-danger text-center">' +
                        '<i class="fas fa-times-circle"></i> ' + (response.message || 'Error al cargar la auditoría') +
                        '</div>');
                }
            },
            error: function() {
                $('#loadingAuditoria').hide();
                $('#contenedorAuditoria').html('<div class="alert alert-danger text-center">' +
                    '<i class="fas fa-times-circle"></i> Error al cargar la auditoría.' +
                    '</div>');
            }
        });
    });

    // Actualizar fecha
    function actualizarFecha() {
        const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const fecha = new Date().toLocaleDateString('es-ES', opciones);
        $('#lafecha').text(fecha.charAt(0).toUpperCase() + fecha.slice(1));
    }
    actualizarFecha();
    setInterval(actualizarFecha, 60000);
});
