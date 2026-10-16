/**
 * Historial de pagos (Estudiante): estado de cuenta del programa elegido.
 * Resumen (total, pagado, saldo y avance), aviso si hay órdenes por pagar (esas se
 * ven e imprimen en Mis órdenes de pago) y la lista de pagos realizados —matrícula,
 * cuotas y clínicas— con el comprobante de cada uno y el recibo de pagos.
 * Si el estudiante tiene un solo programa, se elige solo.
 */

$(document).ready(function() {
    cargarProgramasEstudiante();

    $('#select_programa_modulos').on('change', function() {
        const programaID = $(this).val();
        if (programaID) {
            cargarDetalleModulos(programaID);
        } else {
            ocultarResultados();
        }
    });

    // Comprobante de un pago: imagen con su N° de voucher, fecha y monto
    $(document).on('click', '.btn-comprobante[data-url]', function() {
        const $b = $(this);
        $('#comprobante_titulo').text($b.data('concepto'));
        $('#comprobante_datos').html(
            '<span>N° de voucher: <strong>' + escapar($b.data('voucher') || '-') + '</strong></span>' +
            '<span>Fecha de pago: <strong>' + escapar($b.data('fecha')) + '</strong></span>' +
            '<span>Monto: <strong>' + escapar($b.data('monto')) + '</strong></span>'
        );
        $('#imagen_recibo_grande').attr('src', $b.data('url'));
        $('#modalFotoRecibo').modal('show');
    });

    mostrarFechaActual();
});

const NOMBRES_PLAN = { REGULAR: 'Plan Regular', DESCUENTO: 'Plan al Contado', GRUPAL: 'Plan Grupal' };

function escapar(texto) {
    return $('<div>').text(texto == null ? '' : String(texto)).html().replace(/"/g, '&quot;');
}

function mostrarError(titulo, texto) {
    Swal.fire({ type: 'error', title: titulo, text: texto });
}

/**
 * Programas en los que está inscrito el estudiante
 */
function cargarProgramasEstudiante() {
    const $select = $('#select_programa_modulos');

    $.ajax({
        url: 'ajax/pagomodulo.ajax.php',
        type: 'POST',
        dataType: 'json',
        data: { accion: 'obtenerProgramasEstudiante' },
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
                opciones += '<option value="' + programa.ProgramaID + '">' + escapar(programa.NombrePrograma || '') + '</option>';
            });
            $select.html(opciones).prop('disabled', false);

            if (programas.length === 1) {
                // Con un solo programa no hace falta el selector
                $('#grupo_programa').hide();
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
 * Estado de cuenta del programa elegido
 */
function cargarDetalleModulos(programaID) {
    $.ajax({
        url: 'ajax/pagomodulo.ajax.php',
        type: 'POST',
        dataType: 'json',
        data: { accion: 'obtenerDetalleModulos', programaID: programaID },
        beforeSend: function() {
            $('#aviso_elegir_programa').hide();
            $('#tbody_pagos').html('<tr><td colspan="5" class="text-center text-muted py-4"><span class="kt-spinner kt-spinner--sm kt-spinner--brand"></span> Cargando pagos...</td></tr>');
            $('#area_resultados_modulos').show();
        },
        success: function(response) {
            if (!response.success || !response.detalle || !response.detalle.resumen) {
                mostrarError('Error', response.error || 'No se pudo cargar el estado de cuenta');
                ocultarResultados();
                return;
            }
            const detalle = response.detalle;
            const cuotas = detalle.cuotas || [];
            actualizarResumen(detalle.resumen, cuotas.filter(function(c) { return c.Estado === 'PAGADO'; }).length);
            actualizarPorPagar(detalle.cuotasPorPagar || [], detalle.clinicas || []);
            actualizarPagos(detalle, cuotas);

            if (detalle.idInscripcion) {
                $('#btn_recibo_pagos').attr('href', 'vistas/componentes/recibo-pago-modulos.php?idinscripcion=' + detalle.idInscripcion).show();
            }
        },
        error: function() {
            mostrarError('Error de conexión', 'No se pudo cargar el estado de cuenta. Por favor, intente nuevamente.');
            ocultarResultados();
        }
    });
}

/**
 * Resumen: total del programa (con su plan), pagado, saldo y avance
 */
function actualizarResumen(resumen, cuotasPagadas) {
    const costo = parseFloat(resumen.costoTotal) || 0;
    const pagado = parseFloat(resumen.totalPagado) || 0;
    const porcentaje = costo > 0 ? Math.min(100, Math.round((pagado / costo) * 100)) : 0;

    $('#total_programa').text(formatoMoneda(costo));
    $('#total_pagado').text(formatoMoneda(pagado));
    $('#total_pendiente').text(formatoMoneda(resumen.saldoPendiente));
    $('#total_pendiente').closest('.hp-dato').toggleClass('is-al-dia', (parseFloat(resumen.saldoPendiente) || 0) <= 0);
    $('#cantidad_pagados').text(cuotasPagadas || 0);
    $('#barra_avance').css('width', porcentaje + '%');
    $('#porcentaje_avance').text(porcentaje + '%');

    const plan = [];
    if (resumen.tipoPlan) plan.push(NOMBRES_PLAN[resumen.tipoPlan] || resumen.tipoPlan);
    if (resumen.numeroCuotasPlan) plan.push(resumen.numeroCuotasPlan + (resumen.numeroCuotasPlan === 1 ? ' cuota' : ' cuotas'));
    $('#plan_texto').text(plan.join(' · '));

    if (resumen.completado) {
        $('#estado_plan_texto').text('Programa pagado en su totalidad');
    } else if (!resumen.tienePlan) {
        $('#estado_plan_texto').text('Sin plan de pago definido aún');
    } else {
        $('#estado_plan_texto').text('Por cancelar');
    }
}

/**
 * Órdenes generadas que todavía no se pagan (cuotas y clínicas): solo un aviso con
 * enlace a Mis órdenes de pago, que es donde se ven e imprimen
 */
function actualizarPorPagar(cuotasPorPagar, clinicas) {
    const pendientes = cuotasPorPagar.filter(function(o) { return o.Estado !== 'EXTRAVIADO'; })
        .concat(clinicas.filter(function(cl) { return cl.Estado === 'PENDIENTE' || cl.Estado === 'VENCIDO'; }));
    const enEspera = cuotasPorPagar.filter(function(o) { return o.Estado === 'EXTRAVIADO'; })
        .concat(clinicas.filter(function(cl) { return cl.Estado === 'EXTRAVIADO'; }));

    const partes = [];
    if (pendientes.length) {
        const monto = pendientes.reduce(function(s, o) { return s + (parseFloat(o.MontoTotal) || 0); }, 0);
        partes.push(pendientes.length + (pendientes.length === 1 ? ' orden por pagar' : ' órdenes por pagar') + ' (' + formatoMoneda(monto) + ')');
    }
    if (enEspera.length) {
        partes.push(enEspera.length + (enEspera.length === 1 ? ' pago con voucher por validar' : ' pagos con voucher por validar'));
    }
    $('#aviso_por_pagar')
        .html(partes.length ? '<i class="fas fa-file-invoice"></i> <span>' + escapar(partes.join(' · ')) + '</span><span class="hp-ir">Ver mis órdenes <i class="fas fa-arrow-right"></i></span>' : '')
        .toggle(partes.length > 0);
}

/**
 * Pagos realizados (matrícula, cuotas y clínicas), del más reciente al más antiguo
 */
function actualizarPagos(detalle, cuotas) {
    const pagos = [];
    const m = detalle.matricula;
    if (m && m.pagada && !m.sinMatricula) {
        pagos.push({
            fecha: m.fecha, concepto: 'Matrícula', voucher: m.voucher, monto: m.costo,
            url: m.tieneFoto ? 'ajax/obtener-foto-matricula.php?idInscripcion=' + detalle.idInscripcion : null
        });
    }
    cuotas.filter(function(c) { return c.Estado === 'PAGADO'; }).forEach(function(c) {
        pagos.push({
            fecha: c.fechapago, voucher: c.nvaucher, monto: c.montocuota,
            concepto: c.NumeroCuota ? 'Cuota N° ' + c.NumeroCuota : 'Cuota del programa',
            url: c.TieneImagen === 'SI' ? 'ajax/obtener-voucher-imagen.php?id=' + c.Idpagomodulo : null
        });
    });
    (detalle.clinicas || []).filter(function(cl) { return cl.Estado === 'CONFIRMADO' && cl.IdPagoClinica; }).forEach(function(cl) {
        const url = cl.TieneFoto == 1 ? 'ajax/obtener-voucher-clinica.php?id=' + cl.IdPagoClinica : null;
        pagos.push({
            fecha: cl.FechaPago, voucher: cl.NumeroVoucher, monto: cl.MontoTotal, concepto: 'Clínica', clinica: true,
            url: url, pdf: url && cl.EsPdf == 1
        });
    });

    if (!pagos.length) {
        $('#tbody_pagos').html('<tr><td colspan="5" class="text-center text-muted py-4">Todavía no tiene pagos registrados.</td></tr>');
        return;
    }

    pagos.sort(function(a, b) { return String(b.fecha || '').localeCompare(String(a.fecha || '')); });
    $('#tbody_pagos').html(pagos.map(function(p) {
        let comprobante = '<span class="text-muted small">Sin imagen</span>';
        if (p.url && p.pdf) {
            comprobante = '<a href="' + p.url + '" target="_blank" class="btn-comprobante"><i class="fa fa-file-pdf"></i> Ver PDF</a>';
        } else if (p.url) {
            comprobante = '<button type="button" class="btn-comprobante" data-url="' + p.url + '" data-concepto="' + escapar(p.concepto) + '" ' +
                'data-voucher="' + escapar(p.voucher) + '" data-fecha="' + formatearFecha(p.fecha) + '" data-monto="' + escapar(formatoMoneda(p.monto)) + '">' +
                '<i class="fa fa-receipt"></i> Ver</button>';
        }
        return '<tr>' +
            '<td data-label="Fecha de pago">' + formatearFecha(p.fecha) + '</td>' +
            '<td data-label="Concepto"' + (p.clinica ? ' class="hp-concepto-clinica"' : '') + '>' + escapar(p.concepto) + '</td>' +
            '<td data-label="N° de voucher">' + escapar(p.voucher || '-') + '</td>' +
            '<td data-label="Monto" class="text-right">' + formatoMoneda(p.monto) + '</td>' +
            '<td data-label="Comprobante" class="text-center">' + comprobante + '</td>' +
            '</tr>';
    }).join(''));
}

function ocultarResultados() {
    $('#area_resultados_modulos').hide();
    $('#btn_recibo_pagos').hide();
    $('#aviso_elegir_programa').show();
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
    return partes ? partes[3] + '/' + partes[2] + '/' + partes[1] : '-';
}

function mostrarFechaActual() {
    const fecha = new Date().toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    $('#lafecha').text(fecha.charAt(0).toUpperCase() + fecha.slice(1));
}
