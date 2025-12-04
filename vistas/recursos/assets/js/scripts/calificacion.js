/**
 * Sistema de Registro de Calificaciones - Flujo por pasos
 * 1. Seleccionar docente
 * 2. Mostrar asignaciones del docente
 * 3. Seleccionar módulo e ingresar calificaciones
 */

// Variables globales
let docenteSeleccionado = null;
let asignacionSeleccionada = null;
let estudiantesActuales = [];

$(document).ready(function() {
    // Inicializar Select2
    $('.select2-docente').select2({
        placeholder: '-- Seleccione un docente --',
        allowClear: true,
        width: '100%'
    });

    // Event Listeners
    $('#select-docente').on('change', seleccionarDocente);
    $('#btn-cambiar-docente').on('click', volverPaso1);
    $('#btn-volver-asignaciones').on('click', volverPaso2);
    $('#btn-guardar-notas').on('click', guardarCalificaciones);

    // Usar delegación de eventos para el botón de lista de asistencia
    $(document).on('click', '#btn-generar-lista-asistencia', generarListaAsistencia);

    console.log('Event listeners registrados correctamente');
});

/**
 * ================================================================
 * PASO 1: SELECCIÓN DE DOCENTE
 * ================================================================
 */

function seleccionarDocente() {
    const docenteID = $(this).val();

    if (!docenteID) {
        return;
    }

    // Obtener datos del option seleccionado
    const option = $(this).find('option:selected');
    const nombreDocente = option.data('nombre');
    const ciDocente = option.data('ci');
    const especialidadDocente = option.data('especialidad');

    docenteSeleccionado = {
        id: docenteID,
        nombre: nombreDocente,
        ci: ciDocente,
        especialidad: especialidadDocente
    };

    // Cargar asignaciones del docente
    cargarAsignacionesDocente(docenteID);
}

/**
 * ================================================================
 * PASO 2: ASIGNACIONES DEL DOCENTE
 * ================================================================
 */

function cargarAsignacionesDocente(docenteID) {
    console.log('=== CARGAR ASIGNACIONES ===');
    console.log('DocenteID:', docenteID);

    $.ajax({
        url: 'ajax/calificacion.ajax.php',
        method: 'POST',
        data: {
            accion: 'obtenerAsignaciones',
            docenteID: docenteID
        },
        dataType: 'json',
        beforeSend: function() {
            console.log('Enviando petición AJAX...');
            Swal.fire({
                title: 'Cargando asignaciones...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                onOpen: function() {
                    Swal.showLoading();
                }
            });
        },
        success: function(response) {
            console.log('Respuesta recibida:', response);
            console.log('Status:', response.status);
            console.log('Data:', response.data);
            console.log('Data length:', response.data ? response.data.length : 0);

            Swal.close();

            if (response.status === 'success' && response.data && response.data.length > 0) {
                console.log('Mostrando asignaciones...');
                mostrarAsignaciones(response.data);
            } else {
                console.log('No hay asignaciones');
                Swal.fire({
                    type: 'info',
                    title: 'Sin asignaciones',
                    text: 'Este docente no tiene módulos asignados',
                    html: 'DocenteID: ' + docenteID + '<br>Response: ' + JSON.stringify(response)
                });
            }
        },
        error: function(xhr, status, error) {
            console.log('ERROR en AJAX');
            console.log('XHR:', xhr);
            console.log('Status:', status);
            console.log('Error:', error);
            console.log('Response Text:', xhr.responseText);

            Swal.close();
            Swal.fire({
                type: 'error',
                title: 'Error',
                text: 'Error al cargar las asignaciones',
                html: 'Error: ' + error + '<br>Status: ' + status
            });
        }
    });
}

function mostrarAsignaciones(asignaciones) {
    // Ocultar paso 1 y mostrar paso 2
    $('#paso1-container').slideUp();
    $('#paso2-container').slideDown();

    // Mostrar información del docente
    $('#docente-nombre').text(docenteSeleccionado.nombre);
    $('#docente-ci').text(docenteSeleccionado.ci);
    $('#docente-especialidad').text(docenteSeleccionado.especialidad);
    $('#total-asignaciones').text(asignaciones.length);

    // Renderizar tabla de asignaciones
    let html = `
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-calificaciones">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 12%;">Grado Académico</th>
                        <th>Programa</th>
                        <th>Módulo</th>
                        <th style="width: 10%;">Código</th>
                        <th style="width: 8%;">Estudiantes</th>
                        <th style="width: 15%;">Estado Calificación</th>
                        <th style="width: 10%;">Acción</th>
                    </tr>
                </thead>
                <tbody>
    `;

    asignaciones.forEach(function(asig, index) {
        // Determinar estado de calificación
        var totalEstudiantes = parseInt(asig.TotalEstudiantes) || 0;
        var totalCalificados = parseInt(asig.TotalCalificados) || 0;
        var porcentaje = totalEstudiantes > 0 ? Math.round((totalCalificados / totalEstudiantes) * 100) : 0;

        var estadoBadgeClass = '';
        var estadoTexto = '';
        var estadoIcono = '';

        if (totalCalificados === 0) {
            estadoBadgeClass = 'kt-badge--secondary';
            estadoTexto = 'Sin calificar';
            estadoIcono = 'la la-clock-o';
        } else if (totalCalificados < totalEstudiantes) {
            estadoBadgeClass = 'kt-badge--warning';
            estadoTexto = 'Parcial (' + totalCalificados + '/' + totalEstudiantes + ')';
            estadoIcono = 'la la-hourglass-half';
        } else {
            estadoBadgeClass = 'kt-badge--success';
            estadoTexto = 'Completo (' + totalCalificados + '/' + totalEstudiantes + ')';
            estadoIcono = 'la la-check-circle';
        }

        var fechaInfo = '';
        if (asig.UltimaCalificacion) {
            fechaInfo = '<br><small class="text-muted"><i class="la la-calendar"></i> ' + asig.UltimaCalificacion + '</small>';
        }

        // Información de quién registró
        if (asig.RegistradoPor) {
            var tipoUsuario = asig.TipoUsuarioRegistro || '';
            var iconoTipo = '';
            if (tipoUsuario === 'ADM' || tipoUsuario === 'Administrador') {
                iconoTipo = '<i class="la la-user-shield" style="color: #fd397a;"></i> ';
            } else if (tipoUsuario === 'DOC' || tipoUsuario === 'Docente') {
                iconoTipo = '<i class="la la-user" style="color: #5867dd;"></i> ';
            } else if (tipoUsuario === 'EST' || tipoUsuario === 'Estudiante') {
                iconoTipo = '<i class="la la-graduation-cap" style="color: #1dc9b7;"></i> ';
            }
            fechaInfo += '<br><small class="text-muted">' + iconoTipo + 'Por: ' + asig.RegistradoPor + '</small>';
        }

        html += '<tr class="asignacion-row">';
        html += '<td class="text-center">' + (index + 1) + '</td>';
        html += '<td><span class="kt-badge kt-badge--inline kt-badge--primary">' + asig.GradoAcademico + '</span></td>';
        html += '<td><strong>' + asig.NombrePrograma + '</strong><br><small class="text-muted">' + asig.CodigoPrograma + '</small></td>';
        html += '<td>' + asig.nombremodulo + '</td>';
        html += '<td class="text-center"><span class="kt-badge kt-badge--dark kt-badge--inline">' + asig.codigomodulo + '</span></td>';
        html += '<td class="text-center"><span class="kt-badge kt-badge--' + (totalEstudiantes > 0 ? 'success' : 'secondary') + ' kt-badge--inline kt-badge--bold">' + totalEstudiantes + '</span></td>';
        html += '<td class="text-center">';
        html += '<span class="kt-badge kt-badge--inline ' + estadoBadgeClass + ' kt-badge--bold">';
        html += '<i class="' + estadoIcono + '"></i> ' + estadoTexto;
        html += '</span>' + fechaInfo;
        html += '</td>';
        html += '<td class="text-center">';
        html += '<div class="btn-group" role="group">';
        html += '<button class="btn btn-sm btn-brand btn-evaluar" ';
        html += 'data-modulo-id="' + asig.Idmodulo + '" ';
        html += 'data-programa-id="' + asig.ProgramaID + '" ';
        html += 'data-modulo-nombre="' + asig.nombremodulo + '" ';
        html += 'data-modulo-codigo="' + asig.codigomodulo + '" ';
        html += 'data-programa-nombre="' + asig.NombrePrograma + '" ';
        html += 'data-grado="' + asig.GradoAcademico + '" ';
        html += 'title="' + (totalCalificados > 0 ? 'Editar calificaciones' : 'Evaluar estudiantes') + '">';
        html += '<i class="la la-edit"></i> ' + (totalCalificados > 0 ? 'Editar' : 'Evaluar');
        html += '</button>';
        html += '<button class="btn btn-sm btn-success btn-imprimir-reporte" ';
        html += 'data-modulo-id="' + asig.Idmodulo + '" ';
        html += 'data-programa-id="' + asig.ProgramaID + '" ';
        html += 'data-modulo-nombre="' + asig.nombremodulo + '" ';
        html += 'data-modulo-codigo="' + asig.codigomodulo + '" ';
        html += 'data-programa-nombre="' + asig.NombrePrograma + '" ';
        html += 'data-grado="' + asig.GradoAcademico + '" ';
        html += 'data-docente-nombre="' + docenteSeleccionado.nombre + '" ';
        html += 'title="Imprimir reporte de calificaciones">';
        html += '<i class="la la-print"></i>';
        html += '</button>';
        html += '</div>';
        html += '</td>';
        html += '</tr>';
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    $('#asignaciones-container').html(html);

    // Agregar evento click a los botones de evaluar
    $('.btn-evaluar').on('click', function() {
        const moduloID = $(this).data('modulo-id');
        const programaID = $(this).data('programa-id');

        asignacionSeleccionada = {
            moduloID: moduloID,
            programaID: programaID,
            moduloNombre: $(this).data('modulo-nombre'),
            moduloCodigo: $(this).data('modulo-codigo'),
            programaNombre: $(this).data('programa-nombre'),
            grado: $(this).data('grado')
        };

        cargarEstudiantes(moduloID, programaID);
    });

    // Agregar evento click a los botones de imprimir reporte
    $('.btn-imprimir-reporte').on('click', function() {
        const moduloID = $(this).data('modulo-id');
        const programaID = $(this).data('programa-id');
        const moduloNombre = $(this).data('modulo-nombre');
        const moduloCodigo = $(this).data('modulo-codigo');
        const programaNombre = $(this).data('programa-nombre');
        const gradoAcademico = $(this).data('grado');
        const docenteNombre = $(this).data('docente-nombre');

        imprimirReporteCalificaciones(moduloID, programaID, moduloNombre, moduloCodigo, programaNombre, gradoAcademico, docenteNombre);
    });
}

/**
 * ================================================================
 * PASO 3: CARGAR ESTUDIANTES Y CALIFICACIONES
 * ================================================================
 */

function cargarEstudiantes(moduloID, programaID) {
    $.ajax({
        url: 'ajax/calificacion.ajax.php',
        method: 'POST',
        data: {
            accion: 'obtenerEstudiantes',
            moduloID: moduloID,
            programaID: programaID
        },
        dataType: 'json',
        beforeSend: function() {
            Swal.fire({
                title: 'Cargando estudiantes...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                onOpen: function() {
                    Swal.showLoading();
                }
            });
        },
        success: function(response) {
            Swal.close();

            if (response.status === 'success') {
                estudiantesActuales = response.data;
                mostrarFormularioCalificaciones(response.data);
            } else {
                Swal.fire({
                    type: 'error',
                    title: 'Error',
                    text: 'Error al cargar los estudiantes'
                });
            }
        },
        error: function() {
            Swal.close();
            Swal.fire({
                type: 'error',
                title: 'Error',
                text: 'Error al conectar con el servidor'
            });
        }
    });
}

function mostrarFormularioCalificaciones(estudiantes) {
    // Ocultar paso 2 y mostrar paso 3
    $('#paso2-container').slideUp();
    $('#paso3-container').slideDown();

    // Mostrar información del módulo
    $('#modulo-nombre').text(asignacionSeleccionada.moduloNombre);
    $('#modulo-codigo').text(asignacionSeleccionada.moduloCodigo);
    $('#programa-nombre').text(asignacionSeleccionada.programaNombre);
    $('#grado-nombre').text(asignacionSeleccionada.grado);

    // Renderizar tabla de estudiantes
    if (estudiantes.length === 0) {
        $('#estudiantes-container').html(`
            <div class="alert alert-warning text-center">
                <i class="flaticon-warning"></i> No hay estudiantes inscritos en este módulo
            </div>
        `);
        $('#footer-guardar').hide();
        return;
    }

    let html = `
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-calificaciones">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 10%;">C.I.</th>
                        <th>Estudiante</th>
                        <th style="width: 15%;">Nota Final</th>
                        <th style="width: 15%;">Estado</th>
                    </tr>
                </thead>
                <tbody>
    `;

    estudiantes.forEach(function(estudiante, index) {
        const nombreCompleto = `${estudiante.Nombre} ${estudiante.Apaterno} ${estudiante.Amaterno}`;
        const notaActual = estudiante.Nota !== null ? parseFloat(estudiante.Nota) : '';
        const estadoClass = getEstadoClass(notaActual);
        const estadoTexto = getEstadoTexto(notaActual);
        const notaInputClass = getNotaInputClass(notaActual);

        html += `
            <tr>
                <td class="text-center">${index + 1}</td>
                <td class="text-center">${estudiante.Ci}</td>
                <td>${nombreCompleto}</td>
                <td>
                    <input
                        type="number"
                        class="form-control input-nota ${notaInputClass}"
                        value="${notaActual}"
                        min="0"
                        max="100"
                        step="0.01"
                        data-estudiante-id="${estudiante.EstudianteID}"
                        onchange="validarNota(this)"
                        placeholder="0.00"
                        required
                    >
                </td>
                <td class="text-center">
                    <span class="kt-badge kt-badge--xl ${estadoClass}" data-estudiante-id="${estudiante.EstudianteID}">
                        ${estadoTexto}
                    </span>
                </td>
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    $('#estudiantes-container').html(html);
    $('#footer-guardar').slideDown();
}

/**
 * ================================================================
 * UTILIDADES Y VALIDACIONES
 * ================================================================
 */

function getEstadoClass(nota) {
    if (nota === '' || nota === null) return 'kt-badge--secondary';
    return parseFloat(nota) >= 51 ? 'kt-badge--success' : 'kt-badge--danger';
}

function getEstadoTexto(nota) {
    if (nota === '' || nota === null) return 'Pendiente';
    return parseFloat(nota) >= 51 ? 'Aprobado' : 'Reprobado';
}

function getNotaInputClass(nota) {
    if (nota === '' || nota === null) return '';
    return parseFloat(nota) >= 51 ? 'is-valid' : 'is-invalid';
}

function validarNota(input) {
    const nota = parseFloat(input.value);
    const estudianteID = $(input).data('estudiante-id');
    const estadoBadge = $(`.kt-badge[data-estudiante-id="${estudianteID}"]`);

    // Limpiar clases
    $(input).removeClass('is-valid is-invalid');

    // Validar rango
    if (isNaN(nota) || nota < 0 || nota > 100) {
        $(input).addClass('is-invalid');
        estadoBadge.removeClass('kt-badge--success kt-badge--danger kt-badge--secondary')
                   .addClass('kt-badge--warning')
                   .text('Inválida');
        return;
    }

    // Validar aprobado/reprobado (nota mínima 51)
    if (nota >= 51) {
        $(input).addClass('is-valid');
        estadoBadge.removeClass('kt-badge--danger kt-badge--warning kt-badge--secondary')
                   .addClass('kt-badge--success')
                   .text('Aprobado');
    } else {
        $(input).addClass('is-invalid');
        estadoBadge.removeClass('kt-badge--success kt-badge--warning kt-badge--secondary')
                   .addClass('kt-badge--danger')
                   .text('Reprobado');
    }
}

/**
 * ================================================================
 * GUARDAR CALIFICACIONES
 * ================================================================
 */

function guardarCalificaciones() {
    // Recolectar calificaciones
    const calificaciones = [];
    let hayErrores = false;

    $('.input-nota').each(function() {
        const estudianteID = $(this).data('estudiante-id');
        const nota = parseFloat($(this).val());

        // Validar que tenga nota
        if ($(this).val() === '' || isNaN(nota)) {
            hayErrores = true;
            $(this).addClass('is-invalid');
            return;
        }

        // Validar rango
        if (nota < 0 || nota > 100) {
            hayErrores = true;
            $(this).addClass('is-invalid');
            return;
        }

        calificaciones.push({
            estudianteID: estudianteID,
            nota: nota
        });
    });

    if (hayErrores) {
        Swal.fire({
            type: 'error',
            title: 'Error de validación',
            text: 'Hay calificaciones inválidas o vacías. Por favor, corrija los errores antes de guardar.'
        });
        return;
    }

    if (calificaciones.length === 0) {
        Swal.fire({
            type: 'warning',
            title: 'Sin datos',
            text: 'No hay calificaciones para guardar'
        });
        return;
    }

    // Confirmar guardado
    Swal.fire({
        title: '¿Guardar calificaciones?',
        html: 'Se guardarán <strong>' + calificaciones.length + '</strong> calificaciones para el módulo:<br><strong>' + asignacionSeleccionada.moduloNombre + '</strong>',
        type: 'question',
        showCancelButton: true,
        confirmButtonColor: '#1dc9b7',
        cancelButtonColor: '#fd397a',
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar'
    }).then(function(result) {
        if (result.value) {
            enviarCalificaciones(calificaciones);
        }
    });
}

function enviarCalificaciones(calificaciones) {
    $.ajax({
        url: 'ajax/calificacion.ajax.php',
        method: 'POST',
        data: {
            accion: 'guardarCalificaciones',
            programaID: asignacionSeleccionada.programaID,
            moduloID: asignacionSeleccionada.moduloID,
            calificaciones: JSON.stringify(calificaciones)
        },
        dataType: 'json',
        beforeSend: function() {
            Swal.fire({
                title: 'Guardando calificaciones...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                onOpen: function() {
                    Swal.showLoading();
                }
            });
        },
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire({
                    type: 'success',
                    title: 'Éxito',
                    text: 'Calificaciones guardadas correctamente',
                    timer: 1500,
                    showConfirmButton: false
                }).then(function() {
                    // Cerrar el panel de registro de notas (paso 3)
                    $('#paso3-container').slideUp(300, function() {
                        // Después de cerrar, volver al paso 2
                        $('#paso2-container').slideDown(300);

                        // Recargar las asignaciones para mostrar el estado actualizado
                        if (docenteSeleccionado && docenteSeleccionado.id) {
                            cargarAsignacionesDocente(docenteSeleccionado.id);
                        }
                    });

                    // Limpiar datos del módulo actual
                    asignacionSeleccionada = null;
                    estudiantesActuales = [];
                });
            } else {
                Swal.fire({
                    type: 'error',
                    title: 'Error',
                    text: response.message || 'Error al guardar las calificaciones'
                });
            }
        },
        error: function() {
            Swal.fire({
                type: 'error',
                title: 'Error',
                text: 'Error al conectar con el servidor'
            });
        }
    });
}

/**
 * ================================================================
 * NAVEGACIÓN ENTRE PASOS
 * ================================================================
 */

function volverPaso1() {
    $('#paso2-container').slideUp();
    $('#paso1-container').slideDown();
    $('#select-docente').val('').trigger('change');
    docenteSeleccionado = null;
}

function volverPaso2() {
    $('#paso3-container').slideUp();
    $('#paso2-container').slideDown();
    asignacionSeleccionada = null;
    estudiantesActuales = [];
}

/**
 * ================================================================
 * IMPRIMIR REPORTE DE CALIFICACIONES
 * ================================================================
 */

function imprimirReporteCalificaciones(moduloID, programaID, moduloNombre, moduloCodigo, programaNombre, gradoAcademico, docenteNombre) {
    // Abrir el reporte en una nueva ventana
    const url = 'vistas/componentes/reporte-calificaciones-pdf.php?' +
        'moduloID=' + moduloID +
        '&programaID=' + programaID +
        '&moduloNombre=' + encodeURIComponent(moduloNombre) +
        '&moduloCodigo=' + encodeURIComponent(moduloCodigo) +
        '&programaNombre=' + encodeURIComponent(programaNombre) +
        '&gradoAcademico=' + encodeURIComponent(gradoAcademico) +
        '&docenteNombre=' + encodeURIComponent(docenteNombre);

    window.open(url, '_blank');
}

/**
 * ================================================================
 * GENERAR LISTA DE ASISTENCIA
 * ================================================================
 */

function generarListaAsistencia() {
    console.log('');
    console.log('========================================');
    console.log('=== GENERAR LISTA DE ASISTENCIA ===');
    console.log('========================================');
    console.log('Función llamada correctamente');
    console.log('asignacionSeleccionada:', asignacionSeleccionada);
    console.log('docenteSeleccionado:', docenteSeleccionado);
    console.log('estudiantesActuales:', estudiantesActuales);
    console.log('estudiantesActuales.length:', estudiantesActuales ? estudiantesActuales.length : 0);

    // Verificar que hay una asignación seleccionada
    if (!asignacionSeleccionada) {
        console.log('Error: No hay asignación seleccionada');
        Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: 'Por favor, seleccione primero un módulo'
        });
        return;
    }

    // Verificar que hay estudiantes cargados
    if (!estudiantesActuales || estudiantesActuales.length === 0) {
        console.log('Error: No hay estudiantes');
        Swal.fire({
            icon: 'warning',
            title: 'Sin estudiantes',
            text: 'No hay estudiantes inscritos en este módulo para generar la lista de asistencia'
        });
        return;
    }

    // Generar el PDF con los datos actuales
    const url = 'vistas/componentes/generar-lista-asistencia-pdf.php?' +
        'moduloID=' + asignacionSeleccionada.moduloID +
        '&programaID=' + asignacionSeleccionada.programaID +
        '&moduloNombre=' + encodeURIComponent(asignacionSeleccionada.moduloNombre) +
        '&moduloCodigo=' + encodeURIComponent(asignacionSeleccionada.moduloCodigo) +
        '&programaNombre=' + encodeURIComponent(asignacionSeleccionada.programaNombre) +
        '&grado=' + encodeURIComponent(asignacionSeleccionada.grado) +
        '&docenteNombre=' + encodeURIComponent(docenteSeleccionado.nombre);

    console.log('URL generada:', url);

    // Abrir en nueva ventana
    console.log('Abriendo ventana...');
    const ventana = window.open(url, '_blank');

    if (!ventana) {
        console.error('El navegador bloqueó la ventana emergente');
        Swal.fire({
            icon: 'error',
            title: 'Ventana bloqueada',
            html: 'El navegador bloqueó la ventana emergente.<br>Por favor, permita ventanas emergentes para este sitio.<br><br>URL: <small>' + url + '</small>',
            confirmButtonText: 'Entendido'
        });
    } else {
        console.log('Ventana abierta exitosamente');
    }
}
