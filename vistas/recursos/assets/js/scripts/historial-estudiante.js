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
                    <td colspan="5" class="text-center" style="padding: 3rem;">
                        <div class="kt-spinner kt-spinner--lg kt-spinner--brand"></div>
                        <p style="margin-top: 1.5rem; color: #667eea; font-weight: 600; font-size: 1.1rem;">Cargando calificaciones...</p>
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
                <td colspan="5" class="text-center" style="padding: 3rem; color: #B5B5C3;">
                    <i class="flaticon2-file" style="font-size: 3rem; color: #E4E6EF;"></i>
                    <p style="margin-top: 1rem; font-size: 1.1rem; font-weight: 500;">No hay calificaciones registradas para este programa</p>
                </td>
            </tr>
        `);
        return;
    }

    // Mostrar resumen
    mostrarResumenCalificaciones(resumen);

    // Construir filas de la tabla
    calificaciones.forEach(function(calificacion, index) {
        const nota = parseFloat(calificacion.Nota);
        const docenteNombre = calificacion.DocenteNombre
            ? `${calificacion.DocenteNombre} ${calificacion.DocenteApaterno || ''} ${calificacion.DocenteAmaterno || ''}`.trim()
            : 'No asignado';

        let estadoBadge = '';
        let notaClasses = '';

        if (nota >= 51) {
            estadoBadge = '<span class="badge-estado badge-aprobado">APROBADO</span>';
            notaClasses = 'nota-destacada nota-aprobado';
        } else {
            estadoBadge = '<span class="badge-estado badge-reprobado">REPROBADO</span>';
            notaClasses = 'nota-destacada nota-reprobado';
        }

        const fila = `
            <tr style="border-bottom: 1px solid #f0f0f0; transition: all 0.3s;">
                <td style="padding: 1.2rem;">
                    <div>
                        <strong style="color: #464E5F; font-size: 1rem;">${calificacion.NombreModulo}</strong><br>
                        <small style="color: #B5B5C3;">
                            <i class="flaticon2-tag"></i> Código: ${calificacion.CodigoModulo}
                        </small>
                    </div>
                </td>
                <td style="padding: 1.2rem; color: #464E5F; font-weight: 500;">
                    <i class="flaticon2-user" style="color: #667eea; margin-right: 5px;"></i> ${docenteNombre}
                </td>
                <td class="text-center" style="padding: 1.2rem;">
                    <span class="${notaClasses}">${nota}</span>
                </td>
                <td class="text-center" style="padding: 1.2rem; color: #464E5F; font-weight: 600; font-size: 1.1rem;">
                    100
                </td>
                <td class="text-center" style="padding: 1.2rem;">
                    ${estadoBadge}
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

    let gradientePromedio = promedioGeneral >= 51
        ? 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)'
        : 'linear-gradient(135deg, #ee0979 0%, #ff6a00 100%)';

    const resumenHTML = `
        <tr>
            <td colspan="5" style="padding: 0; border: none;">
                <div style="background: #f8f9fa; padding: 2rem; margin-bottom: 1rem; border-radius: 10px;">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div style="background: ${gradientePromedio}; border-radius: 12px; padding: 1.5rem; text-align: center; height: 100%; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                                <div style="background: rgba(255,255,255,0.2); border-radius: 10px; width: 50px; height: 50px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                                    <i class="flaticon2-chart-2" style="font-size: 1.5rem; color: white;"></i>
                                </div>
                                <h6 style="color: rgba(255,255,255,0.9); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem;">Promedio General</h6>
                                <h2 style="color: white; font-weight: 700; font-size: 2.5rem; margin: 0;">${promedioGeneral}</h2>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 1.5rem; text-align: center; height: 100%; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                                <div style="background: rgba(255,255,255,0.2); border-radius: 10px; width: 50px; height: 50px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                                    <i class="flaticon2-layers" style="font-size: 1.5rem; color: white;"></i>
                                </div>
                                <h6 style="color: rgba(255,255,255,0.9); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem;">Módulos Cursados</h6>
                                <h2 style="color: white; font-weight: 700; font-size: 2.5rem; margin: 0;">${totalModulos}</h2>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div style="background: white; border-radius: 12px; padding: 1.5rem; text-align: center; height: 100%; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                                <h6 style="color: #B5B5C3; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">Aprobados / Reprobados</h6>
                                <div style="display: flex; justify-content: center; align-items: center; gap: 10px;">
                                    <div style="text-align: center;">
                                        <div style="background: #e8f5f3; border-radius: 10px; padding: 10px 15px; margin-bottom: 5px;">
                                            <span style="color: #11998e; font-weight: 700; font-size: 1.8rem;">${modulosAprobados}</span>
                                        </div>
                                        <small style="color: #11998e; font-weight: 600;">Aprobados</small>
                                    </div>
                                    <span style="color: #E4E6EF; font-size: 1.5rem;">/</span>
                                    <div style="text-align: center;">
                                        <div style="background: #fee5ed; border-radius: 10px; padding: 10px 15px; margin-bottom: 5px;">
                                            <span style="color: #ee0979; font-weight: 700; font-size: 1.8rem;">${modulosReprobados}</span>
                                        </div>
                                        <small style="color: #ee0979; font-weight: 600;">Reprobados</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div style="background: linear-gradient(135deg, #ffa800 0%, #ffcd00 100%); border-radius: 12px; padding: 1.5rem; text-align: center; height: 100%; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                                <div style="background: rgba(255,255,255,0.2); border-radius: 10px; width: 50px; height: 50px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                                    <i class="flaticon2-percentage" style="font-size: 1.5rem; color: white;"></i>
                                </div>
                                <h6 style="color: rgba(255,255,255,0.9); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem;">% Aprobación</h6>
                                <h2 style="color: white; font-weight: 700; font-size: 2.5rem; margin: 0;">${porcentajeAprobacion}%</h2>
                            </div>
                        </div>
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
            <td colspan="5" class="text-center" style="padding: 3rem; color: #B5B5C3;">
                <i class="flaticon2-list-3" style="font-size: 3rem; color: #E4E6EF;"></i>
                <p style="margin-top: 1rem; font-size: 1.1rem; font-weight: 500;">Seleccione un programa en el menú superior</p>
            </td>
        </tr>
    `);

    // Eliminar resumen si existe
    $('#contenido_calificaciones').prev('tr').remove();
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
