/**
 * JavaScript para Búsqueda de Calificaciones
 * Vista: bnotaestudiante.php
 */

$(document).ready(function() {
    // Cargar programas con calificaciones al iniciar
    cargarProgramas();

    // Evento al hacer clic en el botón de buscar
    $('#btn_buscar_calificaciones').on('click', function() {
        buscarCalificaciones();
    });

    // Permitir búsqueda al presionar Enter en el input
    $('#input_filtro_valor').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            buscarCalificaciones();
        }
    });

    // Evento para limpiar resultados al cambiar el programa
    $('#select_programa_calificaciones').on('change', function() {
        // Opcional: buscar automáticamente al cambiar programa si hay valor de búsqueda
        const valorBusqueda = $('#input_filtro_valor').val().trim();
        if (valorBusqueda !== '') {
            buscarCalificaciones();
        }
    });
});

/**
 * Cargar programas con calificaciones registradas
 */
function cargarProgramas() {
    $.ajax({
        url: 'ajax/calificacion.ajax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            accion: 'obtenerProgramasConCalificaciones'
        },
        success: function(response) {
            if (response.status === 'success') {
                const $select = $('#select_programa_calificaciones');
                $select.empty();
                $select.append('<option value="">-- Todos los Programas --</option>');

                response.data.forEach(function(programa) {
                    $select.append(
                        `<option value="${programa.ProgramaID}">
                            ${programa.NombrePrograma} (${programa.GradoAcademico})
                        </option>`
                    );
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar programas:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudieron cargar los programas disponibles'
            });
        }
    });
}

/**
 * Buscar calificaciones según los filtros
 */
function buscarCalificaciones() {
    const tipoBusqueda = $('#select_filtro_tipo').val();
    const valorBusqueda = $('#input_filtro_valor').val().trim();
    const programaID = $('#select_programa_calificaciones').val();

    // Validar que se haya ingresado un valor de búsqueda
    if (valorBusqueda === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Valor requerido',
            text: 'Por favor ingrese un valor para buscar',
            confirmButtonColor: '#5867dd'
        });
        $('#input_filtro_valor').focus();
        return;
    }

    // Mostrar indicador de carga
    const $tbody = $('#contenido_calificaciones');
    $tbody.html(`
        <tr>
            <td colspan="5" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Buscando...</span>
                </div>
                <p class="mt-2">Buscando calificaciones...</p>
            </td>
        </tr>
    `);

    // Realizar búsqueda
    $.ajax({
        url: 'ajax/calificacion.ajax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            accion: 'buscarCalificaciones',
            tipoBusqueda: tipoBusqueda,
            valorBusqueda: valorBusqueda,
            programaID: programaID
        },
        success: function(response) {
            if (response.status === 'success') {
                mostrarResultados(response.data);

                // Mostrar notificación con el número de resultados
                if (response.total > 0) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Búsqueda exitosa',
                        text: `Se encontraron ${response.total} calificacion(es)`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Error al buscar calificaciones'
                });
                $tbody.html(`
                    <tr>
                        <td colspan="5" class="text-center text-danger">
                            <i class="flaticon-warning"></i> ${response.message}
                        </td>
                    </tr>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error en la búsqueda:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al realizar la búsqueda'
            });
            $tbody.html(`
                <tr>
                    <td colspan="5" class="text-center text-danger">
                        <i class="flaticon-warning"></i> Error al realizar la búsqueda
                    </td>
                </tr>
            `);
        }
    });
}

/**
 * Mostrar resultados en la tabla
 */
function mostrarResultados(data) {
    const $tbody = $('#contenido_calificaciones');
    $tbody.empty();

    if (data.length === 0) {
        $tbody.html(`
            <tr>
                <td colspan="5" class="text-center text-muted">
                    <i class="flaticon-file-2"></i> No se encontraron resultados para la búsqueda
                </td>
            </tr>
        `);
        return;
    }

    // Construir filas de la tabla
    data.forEach(function(calificacion) {
        // Formatear CI completo
        let ciCompleto = calificacion.Ci;
        if (calificacion.Complemento) {
            ciCompleto += '-' + calificacion.Complemento;
        }
        ciCompleto += ' ' + calificacion.Exp;

        // Nombre completo del estudiante
        const nombreCompleto = `${calificacion.Nombre} ${calificacion.Apaterno} ${calificacion.Amaterno}`;

        // Formatear módulo con código
        const modulo = `${calificacion.NombreModulo} (${calificacion.CodigoModulo})`;

        // Determinar estado y clase CSS
        let estadoHTML = '';
        let notaClass = '';
        const nota = parseFloat(calificacion.Nota);

        if (nota >= 51) {
            estadoHTML = '<span class="kt-badge kt-badge--success kt-badge--inline">APROBADO</span>';
            notaClass = 'text-success font-weight-bold';
        } else {
            estadoHTML = '<span class="kt-badge kt-badge--danger kt-badge--inline">REPROBADO</span>';
            notaClass = 'text-danger font-weight-bold';
        }

        // Formatear fecha
        const fecha = calificacion.FechaRegistro ? formatearFecha(calificacion.FechaRegistro) : 'N/A';

        // Construir fila
        const fila = `
            <tr>
                <td>
                    <div class="kt-user-card-v2">
                        <div class="kt-user-card-v2__details">
                            <span class="kt-user-card-v2__name">${nombreCompleto}</span>
                            <span class="kt-user-card-v2__desc">${ciCompleto}</span>
                        </div>
                    </div>
                </td>
                <td>
                    <div>
                        <strong>${calificacion.NombreModulo}</strong><br>
                        <small class="text-muted">${calificacion.CodigoModulo}</small><br>
                        <small class="kt-badge kt-badge--primary kt-badge--inline kt-badge--pill">
                            ${calificacion.NombrePrograma}
                        </small>
                    </div>
                </td>
                <td class="text-center ${notaClass}" style="font-size: 1.2rem;">
                    ${nota}
                </td>
                <td class="text-center">
                    ${estadoHTML}
                </td>
                <td class="text-center">
                    ${fecha}
                </td>
            </tr>
        `;

        $tbody.append(fila);
    });
}

/**
 * Formatear fecha al formato dd/mm/yyyy
 */
function formatearFecha(fecha) {
    if (!fecha) return 'N/A';

    const date = new Date(fecha);
    const dia = String(date.getDate()).padStart(2, '0');
    const mes = String(date.getMonth() + 1).padStart(2, '0');
    const anio = date.getFullYear();

    return `${dia}/${mes}/${anio}`;
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
