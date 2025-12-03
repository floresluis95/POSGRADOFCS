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
                    <td colspan="8" class="text-center" style="padding: 3rem;">
                        <div class="kt-spinner kt-spinner--lg kt-spinner--brand"></div>
                        <p style="margin-top: 1.5rem; color: #667eea; font-weight: 600; font-size: 1.1rem;">Cargando módulos pagados...</p>
                    </td>
                </tr>
            `);
            $('#tbody_modulos_pendientes').html(`
                <tr>
                    <td colspan="7" class="text-center" style="padding: 3rem;">
                        <div class="kt-spinner kt-spinner--lg kt-spinner--brand"></div>
                        <p style="margin-top: 1.5rem; color: #ee0979; font-weight: 600; font-size: 1.1rem;">Cargando módulos pendientes...</p>
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
            const docente = modulo.NombreDocente || 'Por asignar';
            const especialidad = modulo.EspecialidadDocente || 'N/A';

            html += `
                <tr style="border-bottom: 1px solid #f0f0f0; transition: all 0.3s;">
                    <td class="text-center" style="padding: 1.2rem; vertical-align: middle;">
                        <span style="background: #11998e; color: white; border-radius: 50%; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700;">${index + 1}</span>
                    </td>
                    <td style="padding: 1.2rem; vertical-align: middle;">
                        <strong style="color: #464E5F; font-size: 1rem;">${modulo.NombreModulo || 'N/A'}</strong>
                    </td>
                    <td style="padding: 1.2rem; vertical-align: middle;">
                        <span style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 6px 12px; border-radius: 8px; font-weight: 600; font-size: 0.85rem;">${modulo.Codigo || 'N/A'}</span>
                    </td>
                    <td style="padding: 1.2rem; vertical-align: middle; color: #464E5F; font-weight: 500;">${docente}</td>
                    <td style="padding: 1.2rem; vertical-align: middle;">
                        <small style="color: #B5B5C3; font-style: italic;">${especialidad}</small>
                    </td>
                    <td class="text-center" style="padding: 1.2rem; vertical-align: middle;">
                        <strong style="color: #11998e; font-size: 1.1rem; font-weight: 700;">Bs. ${costo}</strong>
                    </td>
                    <td class="text-center" style="padding: 1.2rem; vertical-align: middle; color: #464E5F; font-weight: 500;">${fechaPago}</td>
                    <td class="text-center" style="padding: 1.2rem; vertical-align: middle;">
                        <span style="background: #f3f6f9; color: #667eea; padding: 8px 16px; border-radius: 20px; font-weight: 600; font-size: 0.9rem;">
                            ${voucher}
                        </span>
                    </td>
                </tr>
            `;
        });
    } else {
        html = `
            <tr>
                <td colspan="8" class="text-center" style="padding: 3rem; color: #B5B5C3;">
                    <i class="flaticon2-information" style="font-size: 3rem; color: #E4E6EF;"></i>
                    <p style="margin-top: 1rem; font-size: 1.1rem; font-weight: 500;">No hay módulos pagados</p>
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
            const especialidad = modulo.EspecialidadDocente || 'N/A';
            const costo = parseFloat(modulo.Costo || 0).toFixed(2);
            const estadoModulo = modulo.estadomodulo || 'ACTIVO';

            // Badge de estado según el estado del módulo
            let estadoBadge = 'kt-badge--success';
            if (estadoModulo === 'INACTIVO') {
                estadoBadge = 'kt-badge--dark';
            } else if (estadoModulo === 'PENDIENTE') {
                estadoBadge = 'kt-badge--warning';
            }

            // Color del badge según estado
            let badgeColor = '#38ef7d';
            if (estadoModulo === 'INACTIVO') {
                badgeColor = '#6c757d';
            } else if (estadoModulo === 'PENDIENTE') {
                badgeColor = '#ffa800';
            }

            html += `
                <tr style="border-bottom: 1px solid #f0f0f0; transition: all 0.3s;">
                    <td class="text-center" style="padding: 1.2rem; vertical-align: middle;">
                        <span style="background: #ee0979; color: white; border-radius: 50%; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700;">${index + 1}</span>
                    </td>
                    <td style="padding: 1.2rem; vertical-align: middle;">
                        <strong style="color: #464E5F; font-size: 1rem;">${modulo.NombreModulo || 'N/A'}</strong>
                    </td>
                    <td style="padding: 1.2rem; vertical-align: middle;">
                        <span style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 6px 12px; border-radius: 8px; font-weight: 600; font-size: 0.85rem;">${modulo.Codigo || 'N/A'}</span>
                    </td>
                    <td style="padding: 1.2rem; vertical-align: middle; color: #464E5F; font-weight: 500;">${docente}</td>
                    <td style="padding: 1.2rem; vertical-align: middle;">
                        <small style="color: #B5B5C3; font-style: italic;">${especialidad}</small>
                    </td>
                    <td class="text-center" style="padding: 1.2rem; vertical-align: middle;">
                        <strong style="color: #ee0979; font-size: 1.2rem; font-weight: 700;">Bs. ${costo}</strong>
                    </td>
                    <td class="text-center" style="padding: 1.2rem; vertical-align: middle;">
                        <span style="background: ${badgeColor}; color: white; padding: 8px 16px; border-radius: 20px; font-weight: 600; font-size: 0.85rem;">
                            ${estadoModulo}
                        </span>
                    </td>
                </tr>
            `;
        });
    } else {
        html = `
            <tr>
                <td colspan="7" class="text-center" style="padding: 3rem; color: #B5B5C3;">
                    <i class="flaticon2-check-mark" style="font-size: 3rem; color: #38ef7d;"></i>
                    <p style="margin-top: 1rem; font-size: 1.1rem; font-weight: 600; color: #38ef7d;">¡Felicidades! No tiene módulos pendientes de pago</p>
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
            <td colspan="8" class="text-center" style="padding: 3rem; color: #B5B5C3;">
                <i class="flaticon2-information" style="font-size: 3rem; color: #E4E6EF;"></i>
                <p style="margin-top: 1rem; font-size: 1.1rem; font-weight: 500;">No hay módulos pagados</p>
            </td>
        </tr>
    `);

    $('#tbody_modulos_pendientes').html(`
        <tr>
            <td colspan="7" class="text-center" style="padding: 3rem; color: #B5B5C3;">
                <i class="flaticon2-check-mark" style="font-size: 3rem; color: #38ef7d;"></i>
                <p style="margin-top: 1rem; font-size: 1.1rem; font-weight: 600; color: #38ef7d;">¡Felicidades! No tiene módulos pendientes de pago</p>
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
