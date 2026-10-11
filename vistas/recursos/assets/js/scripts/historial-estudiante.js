/**
 * JavaScript para Historial de Calificaciones del Estudiante
 * Vista: historialnotaestudiante.php
 */

let estudianteData = null;

$(document).ready(function() {
    // Select2 (mismo formato de select que el resto del sistema)
    $('#select_programa_calificaciones').select2({ width: '100%' });

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

                if (response.data.length === 0) {
                    mostrarMensajeTabla('Todavía no está inscrito en ningún programa.');
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
        // Programa con pago vencido: su historial queda desactivado (no se puede elegir)
        if (programa.Bloqueado) {
            $select.append(
                `<option value="${programa.ProgramaID}" disabled>
                    🔒 ${programa.NombrePrograma} - ${programa.GradoAcademico} (bloqueado por pago vencido)
                </option>`
            );
            return;
        }

        const notasInfo = programa.TotalModulosConNota > 0
            ? ` (${programa.TotalModulosConNota} calificaciones)`
            : ' (Sin calificaciones)';

        $select.append(
            `<option value="${programa.ProgramaID}">
                ${programa.NombrePrograma} - ${programa.GradoAcademico}${notasInfo}
            </option>`
        );
    });

    const bloqueados = programas.filter(function(p) { return p.Bloqueado; });
    mostrarAvisoBloqueos(bloqueados);

    // Si todos sus programas están bloqueados, el historial queda desactivado por completo
    if (bloqueados.length === programas.length) {
        $select.prop('disabled', true);
        mostrarBloqueoCalificaciones(null, false);
        return;
    }

    $select.prop('disabled', false);

    // Con un solo programa disponible se elige solo
    const disponibles = programas.filter(function(p) { return !p.Bloqueado; });
    if (disponibles.length === 1) {
        $select.val(String(disponibles[0].ProgramaID)).trigger('change');
    }
}

/**
 * Aviso fijo sobre la tabla con los programas cuyo historial está desactivado
 * por tener un pago vencido.
 */
function mostrarAvisoBloqueos(bloqueados) {
    $('#avisoBloqueoHistorial').remove();
    if (!bloqueados.length) return;

    const escapar = function(t) { return $('<div>').text(t == null ? '' : String(t)).html(); };
    const lista = bloqueados.map(function(p) {
        const fecha = p.FechaVencida ? new Date(p.FechaVencida + 'T00:00:00').toLocaleDateString('es-BO', { day: '2-digit', month: 'long', year: 'numeric' }) : '';
        return `<li><strong>${escapar(p.NombrePrograma)}</strong>` +
               (fecha ? ` — el pago venció el ${fecha} (${p.DiasVencido} día(s) de atraso)` : '') + `</li>`;
    }).join('');

    const $aviso = $(`
        <div id="avisoBloqueoHistorial" role="alert" style="display:flex; gap:14px; align-items:flex-start; margin-top:16px; padding:14px 16px;
             border-radius:12px; border:1px solid #fecaca; background:#fef2f2; color:#7f1d1d;">
            <i class="fa fa-lock" style="font-size:20px; color:#b91c1c; margin-top:2px;"></i>
            <div style="flex:1;">
                <div style="font-weight:700; color:#b91c1c; margin-bottom:4px;">Historial de calificaciones desactivado</div>
                <ul style="margin:0 0 6px; padding-left:18px; font-size:13px;">${lista}</ul>
                <div style="font-size:13px;">Regulariza tu pago para volver a ver tus calificaciones de ${bloqueados.length === 1 ? 'este programa' : 'estos programas'}.</div>
            </div>
            <a href="misordenespago" class="btn btn-sm" style="background:#b91c1c; color:#fff; white-space:nowrap; font-weight:600;">Ver mis órdenes de pago</a>
        </div>
    `);
    $('#grupo_programa').after($aviso);
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
            $('#resumen_calificaciones').empty();
            mostrarMensajeTabla('Cargando calificaciones...');
        },
        success: function(response) {
            if (response.status === 'success') {
                mostrarCalificaciones(response.data, response.resumen);
            } else if (response.status === 'bloqueado') {
                mostrarBloqueoCalificaciones(response.message);
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
        mostrarMensajeTabla('Todavía no tiene calificaciones registradas en este programa.');
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

        const aprobado = nota >= (window.NOTA_APROBACION || 76);
        const clase = aprobado ? 'aprobado' : 'reprobado';
        const escapar = function(t) { return $('<div>').text(t == null ? '' : String(t)).html(); };

        const fila = `
            <tr>
                <td class="celda-modulo">
                    <span class="hn-modulo">${escapar(calificacion.NombreModulo)}</span>
                    <span class="hn-codigo">${escapar(calificacion.CodigoModulo)}</span>
                </td>
                <td data-label="Docente">${escapar(docenteNombre)}</td>
                <td data-label="Nota" class="text-center"><span class="hn-nota hn-nota-${clase}">${nota}</span></td>
                <td data-label="Ponderación" class="text-center">100</td>
                <td data-label="Estado" class="text-center"><span class="hn-estado hn-estado-${clase}">${aprobado ? 'Aprobado' : 'Reprobado'}</span></td>
            </tr>
        `;

        $tbody.append(fila);
    });
}

/**
 * Resumen de calificaciones: una franja con promedio, módulos, aprobados/reprobados
 * y porcentaje de aprobación
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

    $('#resumen_calificaciones').html(`
        <div class="hn-resumen">
            <div class="hn-resumen-dato"><span>Promedio</span><strong>${promedioGeneral}</strong></div>
            <div class="hn-resumen-dato"><span>Módulos</span><strong>${totalModulos}</strong></div>
            <div class="hn-resumen-dato"><span>Aprobados / Reprobados</span><strong>${modulosAprobados} / ${modulosReprobados}</strong></div>
            <div class="hn-resumen-dato"><span>Aprobación</span><strong>${porcentajeAprobacion}%</strong></div>
        </div>
    `);
}

/**
 * Mostrar aviso de bloqueo por cuota vencida en lugar de las calificaciones
 */
function mostrarBloqueoCalificaciones(mensaje, conAlerta) {
    // Eliminar resumen si existe
    $('#resumen_calificaciones').empty();

    const texto = mensaje || 'Tienes un pago vencido. Regulariza tu pago para ver tus calificaciones.';
    const $tbody = $('#contenido_calificaciones');
    $tbody.html(`
        <tr>
            <td colspan="5" class="hn-vacio">
                <i class="fa fa-lock" style="font-size: 1.5rem; color: #b02a1e;"></i>
                <p style="margin: 0.5rem 0 0; font-weight: 600; color: #b02a1e;">Calificaciones no disponibles</p>
                <p style="font-size: 0.85rem; color: #6b7280;">${$('<div>').text(texto).html()}</p>
                <a href="misordenespago" class="btn btn-sm btn-outline-danger">Ver mis órdenes de pago</a>
            </td>
        </tr>
    `);

    if (conAlerta !== false) {
        Swal.fire({
            icon: 'warning',
            title: 'Pago vencido',
            text: texto,
            confirmButtonColor: '#b91c1c'
        });
    }
}

/**
 * Limpiar tabla de calificaciones
 */
function limpiarTablaCalificaciones() {
    $('#resumen_calificaciones').empty();
    mostrarMensajeTabla('Elija un programa para ver sus calificaciones.');
}

/**
 * Fila única con un mensaje (cargando, sin datos, sin programa elegido)
 */
function mostrarMensajeTabla(texto) {
    $('#contenido_calificaciones').html(`<tr><td colspan="5" class="hn-vacio">${texto}</td></tr>`);
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
