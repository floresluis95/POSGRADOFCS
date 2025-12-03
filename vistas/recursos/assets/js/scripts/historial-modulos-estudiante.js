/**
 * Script para vista de historial de módulos del estudiante
 * Muestra módulos pagados y pendientes de pago
 */

$(document).ready(function() {
    // Cargar programas del estudiante al iniciar
    cargarProgramasEstudiante();

    // Evento al cambiar el programa seleccionado
    $('#select_programa_modulos').on('change', function() {
        const programaID = $(this).val();
        if (programaID) {
            cargarDetalleModulos(programaID);
        } else {
            ocultarResultados();
        }
    });

    // Mostrar fecha actual
    mostrarFechaActual();
});

/**
 * Cargar programas inscritos del estudiante
 */
function cargarProgramasEstudiante() {
    $.ajax({
        url: 'ajax/pagomodulo.ajax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            accion: 'obtenerProgramasEstudiante'
        },
        beforeSend: function() {
            $('#select_programa_modulos')
                .prop('disabled', true)
                .html('<option value="">Cargando programas...</option>');
        },
        success: function(response) {
            if (response.success) {
                const programas = response.programas;
                let opciones = '<option value="">-- Seleccione un programa --</option>';

                if (programas && programas.length > 0) {
                    programas.forEach(function(programa) {
                        const grado = programa.GradoAcademico || '';
                        const nombre = programa.NombrePrograma || '';
                        opciones += `<option value="${programa.ProgramaID}">${grado} - ${nombre}</option>`;
                    });

                    // Obtener nombre del estudiante si está disponible
                    if (programas.length > 0) {
                        // El nombre del estudiante vendría de la sesión
                        $('#estudiante_nombre').text('Estudiante Registrado');
                    }
                } else {
                    opciones = '<option value="">No hay programas inscritos</option>';
                }

                $('#select_programa_modulos')
                    .html(opciones)
                    .prop('disabled', false);
            } else {
                console.error('Error al cargar programas:', response.error);
                swal({
                    title: 'Error',
                    text: response.error || 'No se pudieron cargar los programas',
                    icon: 'error',
                    button: 'Aceptar'
                });

                $('#select_programa_modulos')
                    .html('<option value="">Error al cargar programas</option>')
                    .prop('disabled', true);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', error);
            swal({
                title: 'Error de Conexión',
                text: 'No se pudo conectar con el servidor. Por favor, intente nuevamente.',
                icon: 'error',
                button: 'Aceptar'
            });

            $('#select_programa_modulos')
                .html('<option value="">Error de conexión</option>')
                .prop('disabled', true);
        }
    });
}

/**
 * Cargar detalle de módulos (pagados y pendientes) del programa seleccionado
 */
function cargarDetalleModulos(programaID) {
    $.ajax({
        url: 'ajax/pagomodulo.ajax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            accion: 'obtenerDetalleModulos',
            programaID: programaID
        },
        beforeSend: function() {
            // Mostrar loading
            $('#tbody_modulos_pagados').html(`
                <tr>
                    <td colspan="7" class="text-center">
                        <div class="kt-spinner kt-spinner--sm kt-spinner--brand"></div>
                        Cargando módulos...
                    </td>
                </tr>
            `);
            $('#tbody_modulos_pendientes').html(`
                <tr>
                    <td colspan="5" class="text-center">
                        <div class="kt-spinner kt-spinner--sm kt-spinner--brand"></div>
                        Cargando módulos...
                    </td>
                </tr>
            `);
            $('#area_resultados_modulos').show();
        },
        success: function(response) {
            if (response.success && response.detalle) {
                const detalle = response.detalle;

                // Actualizar resumen
                actualizarResumen(detalle.resumen);

                // Actualizar tabla de módulos pagados
                actualizarTablaPagados(detalle.modulosPagados);

                // Actualizar tabla de módulos pendientes
                actualizarTablaPendientes(detalle.modulosPendientes);

                // Mostrar área de resultados
                $('#area_resultados_modulos').fadeIn();

            } else {
                console.error('Error en la respuesta:', response.error);
                swal({
                    title: 'Error',
                    text: response.error || 'No se pudo cargar el detalle de módulos',
                    icon: 'error',
                    button: 'Aceptar'
                });
                ocultarResultados();
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', error);
            swal({
                title: 'Error de Conexión',
                text: 'No se pudo cargar el detalle. Por favor, intente nuevamente.',
                icon: 'error',
                button: 'Aceptar'
            });
            ocultarResultados();
        }
    });
}

/**
 * Actualizar el resumen de pagos
 */
function actualizarResumen(resumen) {
    $('#total_programa').text(parseFloat(resumen.costoTotal).toFixed(2));
    $('#total_pagado').text(parseFloat(resumen.montoPagado).toFixed(2));
    $('#total_pendiente').text(parseFloat(resumen.montoPendiente).toFixed(2));
    $('#cantidad_pagados').text(resumen.modulosPagados);
    $('#cantidad_pendientes').text(resumen.modulosPendientes);
}

/**
 * Actualizar tabla de módulos pagados
 */
function actualizarTablaPagados(modulos) {
    let html = '';

    if (modulos && modulos.length > 0) {
        modulos.forEach(function(modulo, index) {
            const fechaPago = modulo.FechaPago ? formatearFecha(modulo.FechaPago) : 'N/A';
            const voucher = modulo.NumeroVaucher || 'N/A';
            const costo = parseFloat(modulo.CostoPagado || 0).toFixed(2);

            html += `
                <tr>
                    <td class="text-center"><strong>${index + 1}</strong></td>
                    <td>${modulo.NombreModulo || 'N/A'}</td>
                    <td>${modulo.Codigo || 'N/A'}</td>
                    <td class="text-center"><strong class="kt-font-success">Bs. ${costo}</strong></td>
                    <td class="text-center">${fechaPago}</td>
                    <td class="text-center">${voucher}</td>
                    <td class="text-center">
                        <span class="kt-badge kt-badge--success kt-badge--inline kt-badge--pill">
                            ${modulo.EstadoPago || 'PAGADO'}
                        </span>
                    </td>
                </tr>
            `;
        });
    } else {
        html = `
            <tr>
                <td colspan="7" class="text-center text-muted">
                    <i class="flaticon2-information"></i> No hay módulos pagados
                </td>
            </tr>
        `;
    }

    $('#tbody_modulos_pagados').html(html);
}

/**
 * Actualizar tabla de módulos pendientes
 */
function actualizarTablaPendientes(modulos) {
    let html = '';

    if (modulos && modulos.length > 0) {
        modulos.forEach(function(modulo, index) {
            const docente = modulo.NombreDocente || 'Por asignar';
            const costo = parseFloat(modulo.Costo || 0).toFixed(2);

            html += `
                <tr>
                    <td class="text-center"><strong>${index + 1}</strong></td>
                    <td>${modulo.NombreModulo || 'N/A'}</td>
                    <td>${modulo.Codigo || 'N/A'}</td>
                    <td>${docente}</td>
                    <td class="text-center">
                        <strong class="kt-font-danger" style="font-size: 1.1rem;">Bs. ${costo}</strong>
                    </td>
                </tr>
            `;
        });
    } else {
        html = `
            <tr>
                <td colspan="5" class="text-center text-success">
                    <i class="flaticon2-check-mark kt-font-success"></i>
                    <strong>¡Felicidades! No tiene módulos pendientes de pago</strong>
                </td>
            </tr>
        `;
    }

    $('#tbody_modulos_pendientes').html(html);
}

/**
 * Ocultar área de resultados
 */
function ocultarResultados() {
    $('#area_resultados_modulos').fadeOut();

    // Resetear tablas
    $('#tbody_modulos_pagados').html(`
        <tr>
            <td colspan="7" class="text-center text-muted">
                <i class="flaticon2-information"></i> No hay módulos pagados
            </td>
        </tr>
    `);

    $('#tbody_modulos_pendientes').html(`
        <tr>
            <td colspan="5" class="text-center text-muted">
                <i class="flaticon2-information"></i> No hay módulos pendientes
            </td>
        </tr>
    `);

    // Resetear resumen
    $('#total_programa').text('0.00');
    $('#total_pagado').text('0.00');
    $('#total_pendiente').text('0.00');
    $('#cantidad_pagados').text('0');
    $('#cantidad_pendientes').text('0');
}

/**
 * Formatear fecha de DD/MM/YYYY
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
 * Mostrar fecha actual en el header
 */
function mostrarFechaActual() {
    const diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio',
                   'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

    const ahora = new Date();
    const diaSemana = diasSemana[ahora.getDay()];
    const dia = ahora.getDate();
    const mes = meses[ahora.getMonth()];
    const anio = ahora.getFullYear();

    const fechaFormateada = `${diaSemana}, ${dia} de ${mes} de ${anio}`;
    $('#lafecha').text(fechaFormateada);
}
