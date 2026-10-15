/**
 * Historial de módulos (Estudiante): estado de pagos del programa elegido.
 * Muestra el resumen (costo, pagado, saldo y avance), la matrícula y las cuotas.
 * Si el estudiante tiene un solo programa, se elige solo.
 */

$(document).ready(function() {
    // Select2 (mismo formato de select que el resto del sistema)
    $('#select_programa_modulos').select2({ width: '100%' });

    cargarProgramasEstudiante();

    // Ver la foto del recibo/voucher de una cuota pagada. Se usa un modal de
    // Bootstrap propio (no SweetAlert) con tamaño de imagen fijo por CSS
    // (.foto-recibo-tamano-fijo en historialmodulosestudiante.php), igual que ya
    // funciona en Historial de Vouchers, para que todas las fotos se vean del
    // mismo tamaño sin importar su resolución original.
    $(document).on('click', 'button.btn-ver-foto-cuota-estudiante:not(.btn-ver-foto-clinica-estudiante)', function() {
        const idpago = $(this).data('idpago');
        $('#imagen_recibo_grande').attr('src', 'ajax/obtener-voucher-imagen.php?id=' + idpago);
        $('#modalFotoRecibo').modal('show');
    });

    // Foto del voucher de una clínica (un PDF se abre con un enlace, ver actualizarTablaClinicas)
    $(document).on('click', '.btn-ver-foto-clinica-estudiante', function() {
        $('#imagen_recibo_grande').attr('src', 'ajax/obtener-voucher-clinica.php?id=' + $(this).data('idpago'));
        $('#modalFotoRecibo').modal('show');
    });

    $('#select_programa_modulos').on('change', function() {
        const programaID = $(this).val();
        if (programaID) {
            cargarDetalleModulos(programaID);
        } else {
            ocultarResultados();
        }
    });

    mostrarFechaActual();
});

const ETIQUETAS_ESTADO_CUOTA = { PAGADO: 'Pagada', PENDIENTE: 'Pendiente', RECHAZADO: 'Rechazada', ANULADO: 'Anulada', EXTRAVIADO: 'Voucher por validar' };

function mostrarError(titulo, texto) {
    Swal.fire({ icon: 'error', title: titulo, text: texto });
}

/**
 * Cargar programas inscritos del estudiante
 */
function cargarProgramasEstudiante() {
    const $select = $('#select_programa_modulos');

    $.ajax({
        url: 'ajax/pagomodulo.ajax.php',
        type: 'POST',
        dataType: 'json',
        data: { accion: 'obtenerProgramasEstudiante' },
        beforeSend: function() {
            $select.prop('disabled', true).html('<option value="">Cargando programas...</option>');
        },
        success: function(response) {
            if (!response.success) {
                mostrarError('Error', response.error || 'No se pudieron cargar los programas');
                $select.html('<option value="">Error al cargar programas</option>');
                return;
            }

            const programas = response.programas || [];
            if (!programas.length) {
                $select.html('<option value="">No hay programas inscritos</option>');
                $('#aviso_elegir_programa').show().find('p').text('Todavía no está inscrito en ningún programa.');
                return;
            }

            let opciones = '<option value="">-- Seleccione un programa --</option>';
            programas.forEach(function(programa) {
                const grado = programa.GradoAcademico || '';
                const nombre = programa.NombrePrograma || '';
                opciones += `<option value="${programa.ProgramaID}">${grado} - ${nombre}</option>`;
            });
            $select.html(opciones).prop('disabled', false);

            if (programas.length === 1) {
                $select.val(String(programas[0].ProgramaID)).trigger('change');
            } else {
                $('#aviso_elegir_programa').show();
            }
        },
        error: function() {
            mostrarError('Error de conexión', 'No se pudo conectar con el servidor. Por favor, intente nuevamente.');
            $select.html('<option value="">Error de conexión</option>');
        }
    });
}

/**
 * Cargar el detalle de pagos (resumen, matrícula y cuotas) del programa elegido
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
            $('#aviso_elegir_programa').hide();
            $('#tbody_modulos_pagados').html(`
                <tr>
                    <td colspan="6" class="text-center" style="padding: 2rem;">
                        <div class="kt-spinner kt-spinner--lg kt-spinner--brand"></div>
                        <p class="mt-4 text-muted">Cargando cuotas...</p>
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

                actualizarResumen(detalle.resumen, cuotasPagadas);
                actualizarMatricula(detalle.matricula);
                actualizarTablaPagados(cuotas, detalle.cuotasPorPagar || []);
                actualizarTablaClinicas(detalle.clinicas || []);
            } else {
                mostrarError('Error', response.error || 'No se pudo cargar el detalle de pagos');
                ocultarResultados();
            }
        },
        error: function() {
            mostrarError('Error de conexión', 'No se pudo cargar el detalle. Por favor, intente nuevamente.');
            ocultarResultados();
        }
    });
}

/**
 * Resumen: costo, pagado, saldo y barra de avance
 */
function actualizarResumen(resumen, cuotasPagadas) {
    const costo = parseFloat(resumen.costoTotal) || 0;
    const pagado = parseFloat(resumen.totalPagado) || 0;
    const porcentaje = costo > 0 ? Math.min(100, Math.round((pagado / costo) * 100)) : 0;

    $('#total_programa').text(formatoMoneda(costo));
    $('#total_pagado').text(formatoMoneda(pagado));
    $('#total_pendiente').text(formatoMoneda(resumen.saldoPendiente));
    $('#cantidad_pagados').text(cuotasPagadas || 0);
    $('#barra_avance').css('width', porcentaje + '%');
    $('#porcentaje_avance').text(porcentaje + '%');

    if (resumen.completado) {
        $('#estado_plan_texto').html('<i class="flaticon2-check-mark text-success"></i> Programa pagado en su totalidad');
    } else if (!resumen.tienePlan) {
        $('#estado_plan_texto').text('Sin plan de pago definido aún');
    } else {
        $('#estado_plan_texto').text('Saldo por cancelar');
    }
}

/**
 * Estado de la matrícula del programa elegido
 */
function actualizarMatricula(matricula) {
    const $cont = $('#matricula_cuerpo');

    if (!matricula) {
        $cont.html('<span class="text-muted">Sin información de matrícula</span>');
        return;
    }

    const monto = formatoMoneda(matricula.costo);

    if (matricula.pagada) {
        const detalle = [];
        if (matricula.fechaOrden) detalle.push('Orden del ' + formatearFecha(matricula.fechaOrden));
        if (matricula.fecha) detalle.push('Registrada el ' + formatearFecha(matricula.fecha));
        if (matricula.voucher) detalle.push('Voucher N° ' + matricula.voucher);

        $cont.html(`
            <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 0.75rem;">
                <div>
                    <strong>${monto}</strong>
                    <div class="text-muted" style="font-size: 0.85rem;">${detalle.join(' · ')}</div>
                </div>
                <span class="hm-insignia hm-insignia-pagado">Pagada</span>
            </div>
        `);
    } else {
        $cont.html(`
            <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 0.75rem;">
                <strong>${monto}</strong>
                <span class="hm-insignia hm-insignia-pendiente">Pendiente</span>
            </div>
        `);
    }
}

/**
 * Tabla de cuotas (en celular cada fila se muestra como tarjeta, ver CSS)
 */
function actualizarTablaPagados(cuotas, porPagar) {
    porPagar = porPagar || [];
    cuotas = cuotas || [];
    if (!cuotas.length && !porPagar.length) {
        $('#tbody_modulos_pagados').html(filaSinCuotas());
        return;
    }

    // Pagadas y pendientes juntas, en orden correlativo por N° de cuota (1, 2, 3...)
    const filas = [];
    const numero = function(n) { return parseInt(n, 10) || 9999; };
    cuotas.forEach(function(cuota) {
        const estado = cuota.Estado || 'PENDIENTE';
        const voucher = cuota.nvaucher || '-';

        // Foto del voucher (recibo) subida al registrar el pago de esta cuota,
        // si tiene una: mismo endpoint que usa Matriculados del lado del
        // administrador.
        const btnFoto = cuota.TieneImagen === 'SI'
            ? `<button type="button" class="btn-ver-foto-cuota-estudiante" data-idpago="${cuota.Idpagomodulo}" title="Ver foto del recibo">
                   <i class="flaticon2-photograph"></i>
               </button>`
            : '';

        filas.push({ n: numero(cuota.NumeroCuota), html: `
            <tr>
                <td data-label="N° cuota"><strong>${cuota.NumeroCuota || '-'}</strong></td>
                <td data-label="Monto" class="text-right">${formatoMoneda(cuota.montocuota)}</td>
                <td data-label="Generada">${cuota.FechaSolicitud ? formatearFecha(cuota.FechaSolicitud) : '-'}</td>
                <td data-label="Pagada">${cuota.fechapago ? formatearFecha(cuota.fechapago) : '-'}</td>
                <td data-label="N° voucher"><span>${voucher}${btnFoto}</span></td>
                <td data-label="Estado" class="text-center">
                    <span class="hm-insignia hm-insignia-${estado.toLowerCase()}">${ETIQUETAS_ESTADO_CUOTA[estado] || estado}</span>
                </td>
            </tr>
        ` });
    });

    // Cuotas con orden de pago pero sin voucher validado todavía: Pendiente
    porPagar.forEach(function(o) {
        const estado = o.Estado === 'EXTRAVIADO' ? 'EXTRAVIADO' : 'PENDIENTE';
        filas.push({ n: numero(o.NumeroCuota), html: `
            <tr>
                <td data-label="N° cuota"><strong>${o.NumeroCuota || '-'}</strong></td>
                <td data-label="Monto" class="text-right">${formatoMoneda(o.MontoTotal)}</td>
                <td data-label="Generada">${o.FechaGeneracion ? formatearFecha(o.FechaGeneracion) : '-'}</td>
                <td data-label="Pagada">-</td>
                <td data-label="N° voucher"><span class="text-muted">${estado === 'EXTRAVIADO' ? 'Voucher por validar' : 'Pendiente de pago'}</span></td>
                <td data-label="Estado" class="text-center">
                    <span class="hm-insignia hm-insignia-pendiente">${ETIQUETAS_ESTADO_CUOTA[estado] || estado}</span>
                </td>
            </tr>
        ` });
    });

    filas.sort(function(a, b) { return a.n - b.n; });
    $('#tbody_modulos_pagados').html(filas.map(function(f) { return f.html; }).join(''));
}

/**
 * Tabla de clínicas: solo se muestra si tiene alguna. Una orden CONFIRMADA es una
 * clínica pagada (su voucher está en pagoclinica).
 */
function actualizarTablaClinicas(clinicas) {
    if (!clinicas.length) {
        $('#tbody_clinicas').html('');
        $('#clinicas_container').hide();
        return;
    }

    let html = '';
    clinicas.forEach(function(cl) {
        const estado = cl.Estado === 'CONFIRMADO' ? 'PAGADO' : (cl.Estado || 'PENDIENTE');
        let btnFoto = '';
        if (cl.IdPagoClinica && cl.TieneFoto == 1) {
            btnFoto = cl.EsPdf == 1
                ? `<a href="ajax/obtener-voucher-clinica.php?id=${cl.IdPagoClinica}" target="_blank" class="btn-ver-foto-cuota-estudiante" title="Ver recibo (PDF)"><i class="fa fa-file-pdf"></i></a>`
                : `<button type="button" class="btn-ver-foto-cuota-estudiante btn-ver-foto-clinica-estudiante" data-idpago="${cl.IdPagoClinica}" title="Ver foto del recibo"><i class="flaticon2-photograph"></i></button>`;
        }
        html += `
            <tr>
                <td data-label="Monto" class="text-right">${formatoMoneda(cl.MontoTotal)}</td>
                <td data-label="Generada">${cl.FechaGeneracion ? formatearFecha(cl.FechaGeneracion) : '-'}</td>
                <td data-label="Pagada">${cl.FechaPago ? formatearFecha(cl.FechaPago) : '-'}</td>
                <td data-label="N° voucher"><span>${$('<div>').text(cl.NumeroVoucher || '-').html()}${btnFoto}</span></td>
                <td data-label="Estado" class="text-center">
                    <span class="hm-insignia hm-insignia-${estado.toLowerCase()}">${ETIQUETAS_ESTADO_CUOTA[estado] || estado}</span>
                </td>
            </tr>
        `;
    });

    $('#tbody_clinicas').html(html);
    $('#clinicas_container').show();
}

function filaSinCuotas() {
    return `
        <tr>
            <td colspan="6" class="text-center text-muted" style="padding: 1.5rem;">
                Todavía no tiene cuotas registradas.
            </td>
        </tr>
    `;
}

/**
 * Ocultar resultados (sin programa elegido)
 */
function ocultarResultados() {
    $('#area_resultados_modulos').hide();
    $('#aviso_elegir_programa').show();
    $('#matricula_cuerpo').html('<span class="text-muted">Sin información de matrícula</span>');
    $('#tbody_modulos_pagados').html(filaSinCuotas());
    actualizarTablaClinicas([]);
}

function formatoMoneda(valor) {
    const n = parseFloat(valor) || 0;
    return 'Bs. ' + n.toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

/**
 * Fecha a DD/MM/AAAA. Las fechas "AAAA-MM-DD" se leen como fecha local: con
 * new Date('2026-10-10') el navegador la toma como UTC y en Bolivia (UTC-4)
 * mostraba el día anterior.
 */
function formatearFecha(fecha) {
    if (!fecha) return '-';

    const partes = String(fecha).match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (partes) return `${partes[3]}/${partes[2]}/${partes[1]}`;

    const date = new Date(fecha);
    if (isNaN(date.getTime())) return '-';
    return String(date.getDate()).padStart(2, '0') + '/' + String(date.getMonth() + 1).padStart(2, '0') + '/' + date.getFullYear();
}

/**
 * Mostrar fecha actual en el header
 */
function mostrarFechaActual() {
    const diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio',
                   'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

    const ahora = new Date();
    const fechaFormateada = `${diasSemana[ahora.getDay()]}, ${ahora.getDate()} de ${meses[ahora.getMonth()]} de ${ahora.getFullYear()}`;
    $('#lafecha').text(fechaFormateada);
}
