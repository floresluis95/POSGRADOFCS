/**
 * JavaScript para Búsqueda de Calificaciones
 * Vista: bnotaestudiante.php
 *
 * Un solo buscador (C.I., nombre o apellido, en cualquier orden) que busca
 * mientras se escribe. Los resultados se agrupan por estudiante y programa,
 * con un resumen (módulos, promedio, aprobados): así no hace falta elegir el
 * programa antes de buscar.
 */

// Nota mínima de aprobación (la misma del registro de notas y los reportes)
const NOTA_APROBACION = 76;
const MIN_CARACTERES = 3;

let temporizadorBusqueda = null;
let busquedaEnCurso = null;

$(document).ready(function() {
    // Buscar mientras se escribe (con una pausa para no consultar en cada tecla)
    $('#input_filtro_valor').on('input', function() {
        clearTimeout(temporizadorBusqueda);
        const valor = $(this).val().trim();
        if (valor.length < MIN_CARACTERES) {
            if (valor.length === 0) limpiarResultados();
            return;
        }
        temporizadorBusqueda = setTimeout(buscarCalificaciones, 450);
    });

    // Enter o botón: buscar ya
    $('#input_filtro_valor').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            clearTimeout(temporizadorBusqueda);
            buscarCalificaciones(true);
        }
    });
    $('#btn_buscar_calificaciones').on('click', function() {
        clearTimeout(temporizadorBusqueda);
        buscarCalificaciones(true);
    });

    $('#input_filtro_valor').trigger('focus');
});

function escaparHtml(texto) {
    return $('<div>').text(texto == null ? '' : String(texto)).html();
}

function limpiarResultados() {
    $('#results_counter').html('<i class="flaticon-list-2"></i> 0 resultados');
    $('#contenido_calificaciones').html(`
        <tr class="empty-state">
            <td colspan="5">
                <div class="empty-state__content">
                    <div class="empty-state__icon"><i class="flaticon2-search"></i></div>
                    <h4>No hay resultados para mostrar</h4>
                    <p>Escriba el C.I., nombre o apellido del estudiante para ver sus calificaciones</p>
                </div>
            </td>
        </tr>
    `);
}

/**
 * Buscar calificaciones. "avisar" = true cuando se pidió con Enter/botón
 * (entonces sí se avisa si falta texto).
 */
function buscarCalificaciones(avisar) {
    const valorBusqueda = $('#input_filtro_valor').val().trim();

    if (valorBusqueda.length < MIN_CARACTERES) {
        if (avisar) {
            Swal.fire({
                type: 'warning',
                title: 'Escriba un poco más',
                text: 'Ingrese al menos ' + MIN_CARACTERES + ' caracteres del C.I., nombre o apellido',
                confirmButtonColor: '#5867dd'
            });
            $('#input_filtro_valor').focus();
        }
        return;
    }

    if (busquedaEnCurso) busquedaEnCurso.abort();

    $('#contenido_calificaciones').html(`
        <tr>
            <td colspan="5" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Buscando...</span>
                </div>
                <p class="mt-2">Buscando calificaciones...</p>
            </td>
        </tr>
    `);

    busquedaEnCurso = $.ajax({
        url: 'ajax/calificacion.ajax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            accion: 'buscarCalificaciones',
            tipoBusqueda: 'general',
            valorBusqueda: valorBusqueda
        },
        success: function(response) {
            // Si mientras tanto se cambió el texto, esta respuesta ya no sirve
            if ($('#input_filtro_valor').val().trim() !== valorBusqueda) return;

            if (response.status === 'success') {
                mostrarResultados(response.data);
            } else {
                $('#contenido_calificaciones').html(`
                    <tr><td colspan="5" class="text-center text-danger">
                        <i class="flaticon-warning"></i> ${escaparHtml(response.message || 'Error al buscar calificaciones')}
                    </td></tr>
                `);
            }
        },
        error: function(xhr, status) {
            if (status === 'abort') return;
            $('#contenido_calificaciones').html(`
                <tr><td colspan="5" class="text-center text-danger">
                    <i class="flaticon-warning"></i> Error al realizar la búsqueda
                </td></tr>
            `);
        }
    });
}

/**
 * Mostrar resultados agrupados: una cabecera por estudiante + programa con
 * su resumen, y debajo sus módulos.
 */
function mostrarResultados(data) {
    const $tbody = $('#contenido_calificaciones');
    $tbody.empty();

    if (data.length === 0) {
        $('#results_counter').html('<i class="flaticon-list-2"></i> 0 resultados');
        $tbody.html(`
            <tr>
                <td colspan="5" class="text-center text-muted">
                    <i class="flaticon-file-2"></i> No se encontraron estudiantes con calificaciones para esa búsqueda
                </td>
            </tr>
        `);
        return;
    }

    // Agrupar por estudiante + programa (la consulta ya viene ordenada así)
    const grupos = [];
    const indice = {};
    data.forEach(function(c) {
        const clave = c.EstudianteID + '-' + c.ProgramaID;
        if (!(clave in indice)) {
            indice[clave] = grupos.length;
            grupos.push({ datos: c, notas: [] });
        }
        grupos[indice[clave]].notas.push(c);
    });

    const estudiantes = new Set(data.map(function(c) { return c.EstudianteID; })).size;
    $('#results_counter').html(
        '<i class="flaticon-list-2"></i> ' + estudiantes + (estudiantes === 1 ? ' estudiante' : ' estudiantes') +
        ' · ' + data.length + (data.length === 1 ? ' calificación' : ' calificaciones')
    );

    grupos.forEach(function(g) {
        const e = g.datos;
        let ci = e.Ci + (e.Complemento ? '-' + e.Complemento : '') + (e.Exp ? ' ' + e.Exp : '');
        const nombre = [e.Apaterno, e.Amaterno, e.Nombre].filter(Boolean).join(' ');

        const notas = g.notas.map(function(c) { return parseFloat(c.Nota); }).filter(function(n) { return !isNaN(n); });
        const aprobados = notas.filter(function(n) { return n >= NOTA_APROBACION; }).length;
        const promedio = notas.length ? (notas.reduce(function(a, b) { return a + b; }, 0) / notas.length) : 0;

        // Cabecera del grupo
        $tbody.append(`
            <tr class="fila-grupo-estudiante" style="background:#f3f6fb;">
                <td colspan="5" style="border-top:2px solid #c9d6ee;">
                    <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap:8px;">
                        <div>
                            <strong style="font-size:1.05rem;">${escaparHtml(nombre)}</strong>
                            <span class="text-muted ml-2">C.I. ${escaparHtml(ci)}</span><br>
                            <span class="kt-badge kt-badge--primary kt-badge--inline kt-badge--pill mt-1">
                                ${escaparHtml(e.NombrePrograma)}${e.GradoAcademico ? ' · ' + escaparHtml(e.GradoAcademico) : ''}
                            </span>
                        </div>
                        <div class="text-right" style="font-size:.9rem;">
                            <span class="mr-3"><strong>${notas.length}</strong> módulos</span>
                            <span class="mr-3">Promedio <strong>${promedio.toFixed(1)}</strong></span>
                            <span class="kt-badge ${aprobados === notas.length ? 'kt-badge--success' : 'kt-badge--warning'} kt-badge--inline">
                                ${aprobados} de ${notas.length} aprobados
                            </span>
                        </div>
                    </div>
                </td>
            </tr>
        `);

        // Módulos del estudiante en ese programa
        g.notas.forEach(function(c) {
            const nota = parseFloat(c.Nota);
            const aprobado = nota >= NOTA_APROBACION;
            $tbody.append(`
                <tr>
                    <td class="text-muted" style="padding-left:24px;">${escaparHtml(c.CodigoModulo)}</td>
                    <td><strong>${escaparHtml(c.NombreModulo)}</strong></td>
                    <td class="text-center ${aprobado ? 'text-success' : 'text-danger'} font-weight-bold" style="font-size:1.2rem;">
                        ${isNaN(nota) ? '-' : nota}
                    </td>
                    <td class="text-center">
                        <span class="kt-badge ${aprobado ? 'kt-badge--success' : 'kt-badge--danger'} kt-badge--inline">
                            ${aprobado ? 'APROBADO' : 'REPROBADO'}
                        </span>
                    </td>
                    <td class="text-center">${c.FechaRegistro ? formatearFecha(c.FechaRegistro) : 'N/A'}</td>
                </tr>
            `);
        });
    });
}

/**
 * Formatear fecha al formato dd/mm/yyyy (sin desfase de zona horaria)
 */
function formatearFecha(fecha) {
    if (!fecha) return 'N/A';
    const partes = String(fecha).substring(0, 10).split('-');
    if (partes.length !== 3) return 'N/A';
    return partes[2] + '/' + partes[1] + '/' + partes[0];
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

// Inicializar actualización de fecha si existe el elemento
if ($('#lafecha').length) {
    actualizarFecha();
    setInterval(actualizarFecha, 1000);
}
