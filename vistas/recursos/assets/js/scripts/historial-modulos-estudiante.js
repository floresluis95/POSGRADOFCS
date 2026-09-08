/**
 * Script para vista de historial de módulos del estudiante
 * Muestra módulos pagados y pendientes de pago
 * Versión actualizada con columnas adicionales
 */

console.log('=== HISTORIAL MODULOS ESTUDIANTE V2.0 CARGADO ===');

$(document).ready(function() {
    console.log('jQuery ready - Iniciando carga de programas...');

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
                    <td colspan="5" class="text-center" style="padding: 3rem;">
                        <div class="kt-spinner kt-spinner--lg kt-spinner--brand"></div>
                        <p style="margin-top: 1.5rem; color: #667eea; font-weight: 600; font-size: 1.1rem;">Cargando cuotas...</p>
                    </td>
                </tr>
            `);
            $('#area_resultados_modulos').show();
        },
        success: function(response) {
            if (response.success && response.detalle && response.detalle.resumen) {
                const detalle = response.detalle;

                const cuotas = detalle.cuotas || [];
                const cuotasPagadas = cuotas.filter(function(c) { return c.Estado === 'PAGADO'; }).length;

                // Actualizar resumen
                actualizarResumen(detalle.resumen, cuotasPagadas);

                // Actualizar tabla de cuotas
                actualizarTablaPagados(cuotas);

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
function actualizarResumen(resumen, cuotasPagadas) {
    $('#total_programa').text(parseFloat(resumen.costoTotal).toFixed(2));
    $('#total_pagado').text(parseFloat(resumen.totalPagado).toFixed(2));
    $('#total_pendiente').text(parseFloat(resumen.saldoPendiente).toFixed(2));
    $('#cantidad_pagados').text(cuotasPagadas || 0);

    if (resumen.completado) {
        $('#estado_plan_texto').html('<i class="flaticon2-check-mark"></i> Programa pagado en su totalidad');
    } else if (!resumen.tienePlan) {
        $('#estado_plan_texto').html('<i class="flaticon2-warning"></i> Sin plan de pago definido aún');
    } else {
        $('#estado_plan_texto').html('<i class="flaticon2-warning"></i> Saldo por cancelar');
    }
}

/**
 * Actualizar tabla de historial de cuotas
 */
function actualizarTablaPagados(cuotas) {
    let html = '';

    const badgeColorPorEstado = { PAGADO: '#11998e', PENDIENTE: '#ffa800', RECHAZADO: '#dc3545' };

    if (cuotas && cuotas.length > 0) {
        cuotas.forEach(function(cuota) {
            const fechaPago = cuota.fechapago ? formatearFecha(cuota.fechapago) : 'N/A';
            const voucher = cuota.nvaucher || 'N/A';
            const monto = parseFloat(cuota.montocuota || 0).toFixed(2);
            const numeroCuota = cuota.NumeroCuota || '-';
            const estado = cuota.Estado || 'PENDIENTE';
            const colorEstado = badgeColorPorEstado[estado] || '#6c757d';

            html += `
                <tr style="border-bottom: 1px solid #f0f0f0;">
                    <td class="text-center" style="padding: 1.2rem; vertical-align: middle;">
                        <span style="background: #11998e; color: white; border-radius: 50%; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700;">${numeroCuota}</span>
                    </td>
                    <td class="text-center" style="padding: 1.2rem; vertical-align: middle;">
                        <strong style="color: #11998e; font-size: 1.1rem; font-weight: 700;">Bs. ${monto}</strong>
                    </td>
                    <td class="text-center" style="padding: 1.2rem; vertical-align: middle; color: #464E5F; font-weight: 500;">${fechaPago}</td>
                    <td class="text-center" style="padding: 1.2rem; vertical-align: middle;">
                        <span style="background: #f3f6f9; color: #667eea; padding: 8px 16px; border-radius: 20px; font-weight: 600; font-size: 0.9rem;">
                            ${voucher}
                        </span>
                    </td>
                    <td class="text-center" style="padding: 1.2rem; vertical-align: middle;">
                        <span style="background: ${colorEstado}; color: white; padding: 8px 16px; border-radius: 20px; font-weight: 600; font-size: 0.85rem;">
                            ${estado}
                        </span>
                    </td>
                </tr>
            `;
        });
    } else {
        html = `
            <tr>
                <td colspan="5" class="text-center" style="padding: 3rem; color: #B5B5C3;">
                    <i class="flaticon2-information" style="font-size: 3rem; color: #E4E6EF;"></i>
                    <p style="margin-top: 1rem; font-size: 1.1rem; font-weight: 500;">No hay cuotas registradas</p>
                </td>
            </tr>
        `;
    }

    $('#tbody_modulos_pagados').html(html);
}

/**
 * Ocultar área de resultados
 */
function ocultarResultados() {
    $('#area_resultados_modulos').fadeOut();

    // Resetear tabla
    $('#tbody_modulos_pagados').html(`
        <tr>
            <td colspan="5" class="text-center" style="padding: 3rem; color: #B5B5C3;">
                <i class="flaticon2-information" style="font-size: 3rem; color: #E4E6EF;"></i>
                <p style="margin-top: 1rem; font-size: 1.1rem; font-weight: 500;">No hay cuotas registradas</p>
            </td>
        </tr>
    `);

    // Resetear resumen
    $('#total_programa').text('0.00');
    $('#total_pagado').text('0.00');
    $('#total_pendiente').text('0.00');
    $('#cantidad_pagados').text('0');
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
