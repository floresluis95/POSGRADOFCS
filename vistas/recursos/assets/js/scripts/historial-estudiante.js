/**
 * JavaScript para Historial de Calificaciones del Estudiante
 * Vista: historialnotaestudiante.php
 */

let estudianteData = null;

$(document).ready(function() {
    // Cargar información del estudiante logueado automáticamente
    cargarEstudianteLogueado();

    // Evento al cambiar programa seleccionado
    $('#select_programa_calificaciones').on('change', function() {
        const programaID = $(this).val();
        if (programaID) {
            cargarCalificacionesPrograma(programaID);
        } else {
            limpiarTablaCalificaciones();
        }
    });
});

/**
 * Cargar información del estudiante logueado
 */
function cargarEstudianteLogueado() {
    $.ajax({
        url: 'ajax/calificacion.ajax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            accion: 'obtenerProgramasEstudiante'
        },
        beforeSend: function() {
            // Mostrar indicador de carga en el select
            $('#select_programa_calificaciones').html(
                '<option value="">Cargando programas...</option>'
            ).prop('disabled', true);
        },
        success: function(response) {
            if (response.status === 'success') {
                estudianteData = response.estudiante;
                cargarProgramasSelect(response.data);

                // Mostrar mensaje de bienvenida
                if (response.data.length > 0) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Bienvenido!',
                        html: `<p><strong>${response.estudiante.NombreCompleto}</strong></p>
                               <p>Tiene <strong>${response.data.length}</strong> programa(s) inscrito(s)</p>`,
                        timer: 3000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: 'Sin programas',
                        text: 'No tiene programas inscritos actualmente',
                        confirmButtonColor: '#5867dd'
                    });
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'No se pudo cargar la información del estudiante'
                });
                $('#select_programa_calificaciones').html(
                    '<option value="">Error al cargar programas</option>'
                );
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar estudiante:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor'
            });
            $('#select_programa_calificaciones').html(
                '<option value="">Error de conexión</option>'
            );
        }
    });
}

/**
 * Cargar programas en el select
 */
function cargarProgramasSelect(programas) {
    const $select = $('#select_programa_calificaciones');
    $select.empty();
    $select.append('<option value="">-- Seleccione un programa --</option>');

    if (programas.length === 0) {
        $select.append('<option value="">No tiene programas inscritos</option>');
        $select.prop('disabled', true);
        return;
    }

    programas.forEach(function(programa) {
        const notasInfo = programa.TotalModulosConNota > 0
            ? ` (${programa.TotalModulosConNota} calificaciones)`
            : ' (Sin calificaciones)';

        $select.append(
            `<option value="${programa.ProgramaID}">
                ${programa.NombrePrograma} - ${programa.GradoAcademico}${notasInfo}
            </option>`
        );
    });

    $select.prop('disabled', false);
}

/**
 * Cargar calificaciones por programa
 */
function cargarCalificacionesPrograma(programaID) {
    $.ajax({
        url: 'ajax/calificacion.ajax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            accion: 'obtenerCalificacionesEstudiantePrograma',
            programaID: programaID
        },
        beforeSend: function() {
            const $tbody = $('#contenido_calificaciones');
            $tbody.html(`
                <tr>
                    <td colspan="5" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando calificaciones...</p>
                    </td>
                </tr>
            `);
        },
        success: function(response) {
            if (response.status === 'success') {
                mostrarCalificaciones(response.data, response.resumen);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'No se pudieron cargar las calificaciones'
                });
                limpiarTablaCalificaciones();
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar calificaciones:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo cargar las calificaciones'
            });
            limpiarTablaCalificaciones();
        }
    });
}

/**
 * Mostrar calificaciones en la tabla
 */
function mostrarCalificaciones(calificaciones, resumen) {
    const $tbody = $('#contenido_calificaciones');
    $tbody.empty();

    if (calificaciones.length === 0) {
        $tbody.html(`
            <tr>
                <td colspan="5" class="text-center text-muted">
                    <i class="flaticon-info"></i> No hay calificaciones registradas para este programa
                </td>
            </tr>
        `);
        return;
    }

    // Mostrar resumen
    mostrarResumenCalificaciones(resumen);

    // Construir filas de la tabla
    calificaciones.forEach(function(calificacion) {
        const nota = parseFloat(calificacion.Nota);
        const docenteNombre = calificacion.DocenteNombre
            ? `${calificacion.DocenteNombre} ${calificacion.DocenteApaterno || ''} ${calificacion.DocenteAmaterno || ''}`.trim()
            : 'No asignado';

        let estadoHTML = '';
        let notaClass = '';

        if (nota >= 51) {
            estadoHTML = '<span class="kt-badge kt-badge--success kt-badge--inline kt-badge--pill">APROBADO</span>';
            notaClass = 'text-success font-weight-bold';
        } else {
            estadoHTML = '<span class="kt-badge kt-badge--danger kt-badge--inline kt-badge--pill">REPROBADO</span>';
            notaClass = 'text-danger font-weight-bold';
        }

        const fila = `
            <tr>
                <td>
                    <div>
                        <strong>${calificacion.NombreModulo}</strong><br>
                        <small class="text-muted">Código: ${calificacion.CodigoModulo}</small>
                    </div>
                </td>
                <td>
                    <i class="flaticon2-user"></i> ${docenteNombre}
                </td>
                <td class="text-center ${notaClass}" style="font-size: 1.3rem;">
                    ${nota}
                </td>
                <td class="text-center">
                    100
                </td>
                <td class="text-center">
                    ${estadoHTML}
                </td>
            </tr>
        `;

        $tbody.append(fila);
    });
}

/**
 * Mostrar resumen de calificaciones
 */
function mostrarResumenCalificaciones(resumen) {
    if (!resumen || resumen.TotalModulos == 0) return;

    const promedioGeneral = parseFloat(resumen.PromedioGeneral || 0).toFixed(2);
    const modulosAprobados = parseInt(resumen.ModulosAprobados || 0);
    const modulosReprobados = parseInt(resumen.ModulosReprobados || 0);
    const totalModulos = parseInt(resumen.TotalModulos || 0);
    const porcentajeAprobacion = totalModulos > 0
        ? ((modulosAprobados / totalModulos) * 100).toFixed(1)
        : 0;

    let estadoPromedioClass = promedioGeneral >= 51 ? 'success' : 'danger';

    const resumenHTML = `
        <tr class="table-info">
            <td colspan="5">
                <div class="row text-center py-3">
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">Promedio General</h6>
                        <h3 class="kt-font-${estadoPromedioClass} mb-0">${promedioGeneral}</h3>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">Módulos Cursados</h6>
                        <h3 class="kt-font-brand mb-0">${totalModulos}</h3>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">Aprobados / Reprobados</h6>
                        <h3 class="mb-0">
                            <span class="kt-font-success">${modulosAprobados}</span> /
                            <span class="kt-font-danger">${modulosReprobados}</span>
                        </h3>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">Porcentaje de Aprobación</h6>
                        <h3 class="kt-font-brand mb-0">${porcentajeAprobacion}%</h3>
                    </div>
                </div>
            </td>
        </tr>
    `;

    $('#contenido_calificaciones').before(resumenHTML);
}

/**
 * Limpiar tabla de calificaciones
 */
function limpiarTablaCalificaciones() {
    const $tbody = $('#contenido_calificaciones');
    $tbody.html(`
        <tr>
            <td colspan="5" class="text-center text-muted">
                <i class="flaticon-file-2"></i> Seleccione un programa en el menú superior.
            </td>
        </tr>
    `);

    // Eliminar resumen si existe
    $('tr.table-info').remove();
}

/**
 * Actualizar fecha en tiempo real
 */
function actualizarFecha() {
    const opciones = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    };
    const fecha = new Date().toLocaleDateString('es-ES', opciones);
    $('#lafecha').text(fecha.charAt(0).toUpperCase() + fecha.slice(1));
}

// Inicializar actualización de fecha
if ($('#lafecha').length) {
    actualizarFecha();
    setInterval(actualizarFecha, 1000);
}
