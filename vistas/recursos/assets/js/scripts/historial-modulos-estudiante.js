/**
 * Script para vista de historial de módulos del estudiante
 * Muestra módulos pagados y pendientes de pago
 * Versión actualizada con columnas adicionales
 */

console.log('=== HISTORIAL MODULOS ESTUDIANTE V2.0 CARGADO ===');

$(document).ready(function() {
    console.log('jQuery ready - Iniciando carga de programas...');

    // Select2 (mismo formato de select que el resto del sistema)
    $('#select_programa_modulos').select2({ width: '100%' });

    // Cargar programas del estudiante al iniciar
    cargarProgramasEstudiante();

    // Ver la foto del recibo/voucher de una cuota pagada. Se usa un modal de
    // Bootstrap propio (no SweetAlert) con tamaño de imagen fijo por CSS
    // (.foto-recibo-tamano-fijo en historialmodulosestudiante.php), igual que ya
    // funciona en Historial de Vouchers, para que todas las fotos se vean del
    // mismo tamaño sin importar su resolución original.
    $(document).on('click', '.btn-ver-foto-cuota-estudiante', function() {
        const idpago = $(this).data('idpago');
        $('#imagen_recibo_grande').attr('src', 'ajax/obtener-voucher-imagen.php?id=' + idpago);
        $('#modalFotoRecibo').modal('show');
    });

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

                // Actualizar estado de matrícula
                actualizarMatricula(detalle.matricula);

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
 * Actualizar el estado de la matrícula del programa seleccionado
 */
function actualizarMatricula(matricula) {
    const $cont = $('#matricula_cuerpo');

    if (!matricula) {
        $cont.html('<span class="text-muted">Sin información de matrícula</span>');
        return;
    }

    const monto = parseFloat(matricula.costo || 0).toFixed(2);
    const fecha = matricula.fecha ? formatearFecha(matricula.fecha) : 'N/A';
    const voucher = matricula.voucher || 'N/A';

    if (matricula.pagada) {
        $cont.html(`
            <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 0.75rem;">
                <div class="d-flex align-items-center">
                    <i class="flaticon2-check-mark" style="font-size: 1.3rem; color: #27ae60; margin-right: 0.6rem;"></i>
                    <div>
                        <strong style="color: #464E5F;">Matrícula cancelada</strong> &mdash; Bs. ${monto}
                        <div class="text-muted" style="font-size: 0.85rem;">Registrada el ${fecha} &middot; Voucher N° ${voucher}</div>
                    </div>
                </div>
                <span style="background: #11998e; color: white; padding: 4px 12px; border-radius: 3px; font-weight: 600; font-size: 0.75rem;">PAGADA</span>
            </div>
        `);
    } else {
        $cont.html(`
            <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 0.75rem;">
                <div class="d-flex align-items-center">
                    <i class="flaticon2-warning" style="font-size: 1.3rem; color: #dc3545; margin-right: 0.6rem;"></i>
                    <div>
                        <strong style="color: #464E5F;">Matrícula pendiente</strong> &mdash; Bs. ${monto}
                    </div>
                </div>
                <span style="background: #dc3545; color: white; padding: 4px 12px; border-radius: 3px; font-weight: 600; font-size: 0.75rem;">PENDIENTE</span>
            </div>
        `);
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
            const fechaSolicitud = cuota.FechaSolicitud ? formatearFecha(cuota.FechaSolicitud) : 'N/A';
            const fechaPago = cuota.fechapago ? formatearFecha(cuota.fechapago) : 'N/A';
            const voucher = cuota.nvaucher || 'N/A';
            const monto = parseFloat(cuota.montocuota || 0).toFixed(2);
            const numeroCuota = cuota.NumeroCuota || '-';
            const estado = cuota.Estado || 'PENDIENTE';
            const colorEstado = badgeColorPorEstado[estado] || '#6c757d';

            // Foto del voucher (recibo) subida al registrar el pago de esta cuota,
            // si tiene una: mismo endpoint que usa Matriculados del lado del
            // administrador.
            const tieneImagen = cuota.TieneImagen === 'SI';
            const btnFoto = tieneImagen
                ? `<button type="button" class="btn-ver-foto-cuota-estudiante" data-idpago="${cuota.Idpagomodulo}"
                       style="background:none; border:none; margin-left:6px; cursor:pointer; color:#667eea;" title="Ver foto del recibo">
                       <i class="flaticon2-photograph"></i>
                   </button>`
                : '';

            html += `
                <tr>
                    <td class="text-center" style="padding: 0.6rem; vertical-align: middle; color: #464E5F; font-weight: 600;">${numeroCuota}</td>
                    <td class="text-center" style="padding: 0.6rem; vertical-align: middle; color: #464E5F; font-weight: 600;">Bs. ${monto}</td>
                    <td class="text-center" style="padding: 0.6rem; vertical-align: middle; color: #6c757d;">${fechaSolicitud}</td>
                    <td class="text-center" style="padding: 0.6rem; vertical-align: middle; color: #6c757d;">${fechaPago}</td>
                    <td class="text-center" style="padding: 0.6rem; vertical-align: middle; color: #6c757d;">
                        ${voucher}${btnFoto}
                    </td>
                    <td class="text-center" style="padding: 0.6rem; vertical-align: middle;">
                        <span style="background: ${colorEstado}; color: white; padding: 4px 10px; border-radius: 3px; font-weight: 600; font-size: 0.75rem;">
                            ${estado}
                        </span>
                    </td>
                </tr>
            `;
        });
    } else {
        html = `
            <tr>
                <td colspan="6" class="text-center" style="padding: 2rem; color: #B5B5C3;">
                    <i class="flaticon2-information" style="font-size: 2rem; color: #E4E6EF;"></i>
                    <p style="margin-top: 0.5rem; font-size: 1rem; font-weight: 500;">No hay cuotas registradas</p>
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

    // Resetear estado de matrícula
    $('#matricula_cuerpo').html('<span class="text-muted">Sin información de matrícula</span>');

    // Resetear tabla
    $('#tbody_modulos_pagados').html(`
        <tr>
            <td colspan="6" class="text-center" style="padding: 2rem; color: #B5B5C3;">
                <i class="flaticon2-information" style="font-size: 2rem; color: #E4E6EF;"></i>
                <p style="margin-top: 0.5rem; font-size: 1rem; font-weight: 500;">No hay cuotas registradas</p>
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
