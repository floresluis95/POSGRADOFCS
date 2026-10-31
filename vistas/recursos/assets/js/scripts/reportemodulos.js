/**
 * Script para Reportes de Módulos y Pagos
 * Gestiona la carga de datos y la generación del reporte de pagos por programa
 */

console.log('=== Script de Reportes Cargado ===');

$(document).ready(function() {
    // Select2 (mismo tamaño/estilo que los demás filtros del sistema)
    $('.select2-uniforme').select2({ width: '100%' });

    // Cargar el filtro de grados académicos y el reporte de pagos al iniciar
    cargarGradosParaReportePagos();
    cargarReportePagos();

    // Los filtros trabajan sobre los datos ya cargados (no vuelven a pedirlos al servidor)
    $('#reportePagosGrado, #reportePagosSede, #reportePagosSituacion, #reportePagosOrden').on('change', filtrarReportePagos);
    $('#reportePagosBuscar').on('input', filtrarReportePagos);
    $('#reportePagosLimpiar').on('click', function() {
        $('#reportePagosBuscar').val('');
        $('#reportePagosGrado, #reportePagosSede, #reportePagosSituacion, #reportePagosOrden').val('').trigger('change.select2');
        filtrarReportePagos();
    });

    // Programas y módulos: la tabla ya viene completa; los filtros ocultan filas
    $('#rpProgGrado, #rpProgSede, #rpProgEstado, #rpProgInscritos').on('change', filtrarProgramas);
    $('#rpProgBuscar').on('input', filtrarProgramas);
    $('#rpProgLimpiar').on('click', function() {
        $('#rpProgBuscar').val('');
        $('#rpProgGrado, #rpProgSede, #rpProgEstado, #rpProgInscritos').val('').trigger('change.select2');
        filtrarProgramas();
    });
    filtrarProgramas();

    // Detalle por estudiante (modal)
    $('#detallePagosEstado, #detallePagosPlan, #detallePagosOrden').on('change', filtrarDetallePagos);
    $('#detallePagosBuscar').on('input', filtrarDetallePagos);

    // Actualizar / Imprimir solo aplican a la pestaña de pagos
    $('#rpTabPagosLink, #rpTabProgramasLink, #rpTabGraficosLink').on('shown.bs.tab', function() {
        $('#rpAccionesPagos').toggle(this.id === 'rpTabPagosLink');
    });
    // Enlace directo a la pestaña: reportemodulos#programas
    if (window.location.hash === '#programas') {
        $('#rpTabProgramasLink').tab('show');
    }
    $('#btnActualizarReportePagos').on('click', cargarReportePagos);
    $('#btnImprimirReportePagos').on('click', imprimirReportePagos);
    $('#btnImprimirDetallePagos').on('click', imprimirDetallePagos);

    // Event listener para botones "Ver Módulos" en la tabla de programas
    $(document).on('click', '.ver-modulos-programa', function() {
        const programaID = $(this).data('programa-id');
        const programaNombre = $(this).data('programa-nombre');
        mostrarModulosPrograma(programaID, programaNombre);
    });

    // Event listener para el botón "Detalle" del reporte de pagos por programa
    $(document).on('click', '.btn-detalle-pagos-programa', function() {
        const programaID = $(this).data('programa-id');
        const programaNombre = $(this).data('programa-nombre');
        verDetallePagosPrograma(programaID, programaNombre, $(this).data('tiene-clinica') == 1);
    });
});

/**
 * Cargar los grados académicos disponibles, para el filtro del reporte de pagos
 */
function cargarGradosParaReportePagos() {
    $.ajax({
        url: 'ajax/reportemodulos.ajax.php',
        method: 'POST',
        data: { accion: 'obtenerGrados' },
        dataType: 'json',
        success: function(response) {
            const select = $('#reportePagosGrado');
            select.html('<option value="">Todos los grados</option>');

            if (response && response.length > 0) {
                response.forEach(function(item) {
                    select.append(`<option value="${item.GradoAcademico}">${item.GradoAcademico}</option>`);
                });
            }
        },
        error: function() {
            console.error('No se pudieron cargar los grados académicos');
        }
    });
}

/**
 * Formatear un número como moneda (Bs.)
 */
function formatoMoneda(valor) {
    return 'Bs. ' + (parseFloat(valor) || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function escaparHtml(texto) {
    return $('<div>').text(texto == null ? '' : String(texto)).html();
}

function porcentaje(parte, total) {
    return total > 0 ? Math.min(100, Math.round((parte / total) * 100)) : 0;
}

function barraAvance(pct) {
    return '<div class="rp-barra"><span class="' + (pct >= 100 ? 'is-completo' : '') + '" style="width:' + pct + '%"></span></div>';
}

function kpi(clase, etiqueta, valor, sub, extra) {
    return '<div class="rp-kpi ' + clase + '">' +
        '<div class="rp-kpi-label">' + etiqueta + '</div>' +
        '<div class="rp-kpi-valor" title="' + $('<div>').html(valor).text() + '">' + valor + '</div>' +
        (sub ? '<div class="rp-kpi-sub">' + sub + '</div>' : '') +
        (extra || '') +
    '</div>';
}

const NOMBRES_PLAN_REPORTE = { REGULAR: 'Regular', DESCUENTO: 'Contado (Desc.)', GRUPAL: 'Grupal' };

// Último resultado del servidor: la búsqueda por texto filtra sobre esto sin volver a pedirlo
let datosReportePagos = [];

/**
 * Cargar y renderizar el reporte de pagos por programa
 */
function cargarReportePagos() {
    $('#tablaReportePagosBody').html('<tr><td colspan="8" class="rp-vacio"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>');
    $('#tablaReportePagosFooter').html('');
    $('#reportePagosKpis').html('');

    $.ajax({
        url: 'ajax/reportemodulos.ajax.php',
        method: 'POST',
        data: { accion: 'obtenerReportePagosPrograma', grado: '' },
        dataType: 'json',
        success: function(response) {
            datosReportePagos = Array.isArray(response) ? response : [];
            llenarSedesReportePagos();
            filtrarReportePagos();
        },
        error: function() {
            $('#tablaReportePagosBody').html('<tr><td colspan="8" class="rp-vacio" style="color:#b91c1c">No se pudo cargar el reporte de pagos</td></tr>');
        }
    });
}

// Sedes de los programas cargados (conserva la elegida al actualizar)
function llenarSedesReportePagos() {
    const $sede = $('#reportePagosSede');
    const elegida = $sede.val();
    const sedes = [...new Set(datosReportePagos.map(function(p) { return p.Sede; }).filter(Boolean))].sort();
    $sede.html('<option value="">Todas las sedes</option>' + sedes.map(function(sd) {
        return '<option value="' + escaparHtml(sd) + '">' + escaparHtml(sd) + '</option>';
    }).join(''));
    $sede.val(sedes.indexOf(elegida) !== -1 ? elegida : '').trigger('change.select2');
}

// Situación de cobro de un programa, para el filtro
function situacionCobro(p) {
    if ((parseInt(p.TotalInscritos, 10) || 0) === 0) return 'sin';
    return (parseFloat(p.SaldoPendiente) || 0) > 0 ? 'saldo' : 'aldia';
}

// Filtros locales: búsqueda, grado, sede, situación de cobro y orden
function filtrarReportePagos() {
    const texto = ($('#reportePagosBuscar').val() || '').trim().toLowerCase();
    const grado = $('#reportePagosGrado').val();
    const sede = $('#reportePagosSede').val();
    const situacion = $('#reportePagosSituacion').val();
    const orden = $('#reportePagosOrden').val();

    const filtrados = datosReportePagos.filter(function(p) {
        if (texto && [p.NombrePrograma, p.Codigo, p.Sede, p.GradoAcademico].join(' ').toLowerCase().indexOf(texto) === -1) return false;
        if (grado && p.GradoAcademico !== grado) return false;
        if (sede && p.Sede !== sede) return false;
        if (situacion === 'clinica') return (parseFloat(p.ClinicaPorCobrar) || 0) > 0;
        if (situacion && situacionCobro(p) !== situacion) return false;
        return true;
    });

    const m = function(v) { return parseFloat(v) || 0; };
    const criterios = {
        saldo: function(a, b) { return m(b.SaldoPendiente) - m(a.SaldoPendiente); },
        avance: function(a, b) { return porcentaje(m(a.TotalPagado), m(a.CostoTotalProgramado)) - porcentaje(m(b.TotalPagado), m(b.CostoTotalProgramado)); },
        inscritos: function(a, b) { return (parseInt(b.TotalInscritos, 10) || 0) - (parseInt(a.TotalInscritos, 10) || 0); }
    };
    if (criterios[orden]) filtrados.sort(criterios[orden]);

    const activos = [texto, grado, sede, situacion].filter(Boolean).length;
    $('#reportePagosResultado').html(
        'Mostrando <b>' + filtrados.length + '</b> de ' + datosReportePagos.length + ' programa(s)' +
        (activos ? ' &middot; ' + activos + ' filtro(s) aplicado(s); los totales son de lo que se muestra' : '')
    );
    renderTablaReportePagos(filtrados);
}

// Programas y módulos: oculta las filas que no cumplen los filtros y los grupos que quedan vacíos
function filtrarProgramas() {
    const texto = ($('#rpProgBuscar').val() || '').trim().toLowerCase();
    const grado = $('#rpProgGrado').val();
    const sede = $('#rpProgSede').val();
    const estado = $('#rpProgEstado').val();
    const inscritos = $('#rpProgInscritos').val();
    let total = 0;
    let visibles = 0;

    $('#rpTablaProgramas tbody.rp-grupo-programas').each(function() {
        let enGrupo = 0;
        $(this).find('tr.rp-fila-programa').each(function() {
            const $f = $(this);
            const n = parseInt($f.data('inscritos'), 10) || 0;
            const ok = (!texto || String($f.data('texto')).indexOf(texto) !== -1) &&
                (!grado || $f.data('grado') === grado) &&
                (!sede || $f.data('sede') === sede) &&
                (!estado || $f.data('estado') === estado) &&
                (!inscritos || (inscritos === 'con' ? n > 0 : n === 0));
            $f.toggle(ok);
            total++;
            if (ok) enGrupo++;
        });
        $(this).toggle(enGrupo > 0).find('.rp-grupo-cuenta').text(enGrupo + ' programa(s)');
        visibles += enGrupo;
    });
    $('#rpProgSinResultados').toggle(total > 0 && visibles === 0);
    $('#rpProgResultado').html('Mostrando <b>' + visibles + '</b> de ' + total + ' programa(s)');
}

function renderTablaReportePagos(datos) {
    const t = {
        inscritos: 0, completaron: 0, pendientes: 0, abandonos: 0, conDescuento: 0,
        matriculas: 0, programado: 0, pagado: 0, montoDescuentos: 0, saldo: 0,
        clinicaCobrado: 0, clinicaPorCobrar: 0, clinicaPendientes: 0, programasClinica: 0
    };

    if (!datos || datos.length === 0) {
        $('#tablaReportePagosBody').html('<tr><td colspan="8" class="rp-vacio">No hay programas para mostrar</td></tr>');
        $('#tablaReportePagosFooter').html('');
        renderKpisReportePagos(t);
        return;
    }

    let html = '';

    datos.forEach(function(p) {
        const n = function(v) { return parseInt(v, 10) || 0; };
        const m = function(v) { return parseFloat(v) || 0; };

        const inscritos = n(p.TotalInscritos);
        const completaron = n(p.CountCompletados);
        const pendientes = n(p.CountPendientes);
        const abandonos = n(p.CountAbandono);
        const conDescuento = n(p.CountConDescuento);
        const matriculas = m(p.TotalMatriculas);
        const programado = m(p.CostoTotalProgramado);
        const pagado = m(p.TotalPagado);
        const saldo = m(p.SaldoPendiente);
        const montoDescuentos = m(p.MontoTotalDescuentos);
        const tieneClinica = n(p.TieneClinica) === 1;
        const clinicaCobrado = m(p.ClinicaCobrado);
        const clinicaPorCobrar = m(p.ClinicaPorCobrar);
        const clinicaPendientes = n(p.ClinicaNumPendientes);
        const pct = porcentaje(pagado, programado);

        t.inscritos += inscritos;
        t.completaron += completaron;
        t.pendientes += pendientes;
        t.abandonos += abandonos;
        t.conDescuento += conDescuento;
        t.matriculas += matriculas;
        t.programado += programado;
        t.pagado += pagado;
        t.montoDescuentos += montoDescuentos;
        t.saldo += saldo;
        t.clinicaCobrado += clinicaCobrado;
        t.clinicaPorCobrar += clinicaPorCobrar;
        t.clinicaPendientes += clinicaPendientes;
        if (tieneClinica) t.programasClinica++;

        // Planes: una línea "R 12 · C 3 · G 2" (solo los que tienen estudiantes; letras en la leyenda)
        const planes = [
            ['R', n(p.CountRegular), 'Plan Regular'],
            ['C', n(p.CountDescuento), 'Plan Contado (descuento)'],
            ['G', n(p.CountGrupal), 'Plan Grupal'],
            ['S', n(p.CountSinPlan), 'Sin plan de pago']
        ].filter(function(x) { return x[1] > 0; })
         .map(function(x) { return '<span title="' + x[2] + '"><b>' + x[0] + '</b> ' + x[1] + '</span>'; })
         .join(' &middot; ');
        const lineaPlanes = planes
            ? '<span class="rp-planes-linea">' + planes + (conDescuento > 0 ? ' &middot; ' + conDescuento + ' c/desc.' : '') + '</span>'
            : '';

        // Estudiantes: total + completaron / con saldo / abandonos debajo
        const estadoPago = inscritos === 0 ? '' :
            '<div class="rp-estado">' +
                '<span class="rp-chip is-exito" title="Completaron el pago"><i class="fa fa-check-circle"></i> ' + completaron + '</span>' +
                '<span class="rp-chip is-alerta" title="Con saldo pendiente"><i class="fa fa-clock"></i> ' + pendientes + '</span>' +
                (abandonos > 0 ? '<span class="rp-chip is-peligro" title="Abandonos">' + abandonos + ' aband.</span>' : '') +
            '</div>';

        // Clínicas: "No aplica" si el programa no maneja clínicas y no tiene movimientos
        const celdasClinica = (!tieneClinica && clinicaCobrado === 0 && clinicaPorCobrar === 0)
            ? '<td colspan="2" class="text-center rp-celda-clinica"><span class="rp-na">No aplica</span></td>'
            : '<td class="text-right rp-celda-clinica"><span class="' + (clinicaCobrado > 0 ? 'rp-clinica-monto' : 'rp-muted') + '">' + formatoMoneda(clinicaCobrado) + '</span>' +
                    (n(p.ClinicaNumPagos) > 0 ? '<span class="rp-sub">' + n(p.ClinicaNumPagos) + ' pago(s)</span>' : '') + '</td>' +
              '<td class="text-right rp-celda-clinica"><span class="' + (clinicaPorCobrar > 0 ? '' : 'rp-muted') + '">' + formatoMoneda(clinicaPorCobrar) + '</span>' +
                    (clinicaPendientes > 0 ? '<span class="rp-sub">' + clinicaPendientes + ' orden(es)</span>' : '') + '</td>';

        const meta = [p.Codigo, p.GradoAcademico, p.Sede].filter(Boolean).map(escaparHtml).join(' &middot; ');

        html += '<tr class="' + (inscritos === 0 ? 'rp-fila-vacia' : '') + '">' +
            '<td><div class="rp-prog-nombre">' + escaparHtml(p.NombrePrograma) + '</div><div class="rp-prog-meta">' + meta + '</div></td>' +
            '<td class="text-center"><span class="rp-inscritos">' + inscritos + '</span>' + estadoPago + lineaPlanes + '</td>' +
            '<td class="text-right"><span class="' + (programado > 0 ? '' : 'rp-muted') + '">' + formatoMoneda(programado) + '</span>' +
                (montoDescuentos > 0 ? '<span class="rp-sub">desc. ' + formatoMoneda(montoDescuentos) + '</span>' : '') + '</td>' +
            '<td class="text-right"><span class="' + (pagado > 0 ? 'rp-cobrado' : 'rp-muted') + '">' + formatoMoneda(pagado) + '</span>' +
                (programado > 0 ? '<div class="rp-avance">' + barraAvance(pct) + '<small>' + pct + '%</small></div>' : '') +
                (matriculas > 0 ? '<span class="rp-sub" title="Matrícula ya cancelada, incluida en Cobrado">incl. matr. ' + formatoMoneda(matriculas) + '</span>' : '') + '</td>' +
            '<td class="text-right"><span class="' + (saldo > 0 ? 'rp-saldo' : 'rp-muted') + '">' + formatoMoneda(saldo) + '</span></td>' +
            celdasClinica +
            '<td class="text-center rp-no-print">' +
                '<button type="button" class="btn rp-btn-ver btn-detalle-pagos-programa" ' +
                    'data-programa-id="' + p.ProgramaID + '" data-programa-nombre="' + escaparHtml(p.NombrePrograma) + '" ' +
                    'data-tiene-clinica="' + (tieneClinica || clinicaCobrado > 0 || clinicaPorCobrar > 0 ? 1 : 0) + '">' +
                    'Detalle <i class="fa fa-chevron-right" style="font-size:10px"></i>' +
                '</button>' +
            '</td>' +
        '</tr>';
    });

    $('#tablaReportePagosBody').html(html);

    const pctTotal = porcentaje(t.pagado, t.programado);
    $('#tablaReportePagosFooter').html(
        '<tr>' +
            '<td>Totales <span class="rp-sub">' + datos.length + ' programa(s)</span></td>' +
            '<td class="text-center">' + t.inscritos +
                '<div class="rp-estado">' +
                    '<span class="rp-chip is-exito"><i class="fa fa-check-circle"></i> ' + t.completaron + '</span>' +
                    '<span class="rp-chip is-alerta"><i class="fa fa-clock"></i> ' + t.pendientes + '</span>' +
                '</div>' +
            '</td>' +
            '<td class="text-right">' + formatoMoneda(t.programado) + '</td>' +
            '<td class="text-right"><span class="rp-cobrado">' + formatoMoneda(t.pagado) + '</span>' +
                '<div class="rp-avance">' + barraAvance(pctTotal) + '<small>' + pctTotal + '%</small></div></td>' +
            '<td class="text-right rp-saldo">' + formatoMoneda(t.saldo) + '</td>' +
            '<td class="text-right rp-clinica-monto">' + formatoMoneda(t.clinicaCobrado) + '</td>' +
            '<td class="text-right">' + formatoMoneda(t.clinicaPorCobrar) + '</td>' +
            '<td class="rp-no-print"></td>' +
        '</tr>'
    );

    renderKpisReportePagos(t);
}

// Indicadores generales, según los programas visibles (filtro de grado + búsqueda)
function renderKpisReportePagos(t) {
    const pct = porcentaje(t.pagado, t.programado);
    const icono = function(fa) { return '<i class="fa ' + fa + ' rp-kpi-icono"></i>'; };
    $('#reportePagosKpis').html(
        kpi('is-violeta', icono('fa-user-graduate') + 'Estudiantes inscritos', t.inscritos,
            t.completaron + ' completaron &middot; ' + t.pendientes + ' con saldo' + (t.abandonos ? ' &middot; ' + t.abandonos + ' aband.' : '')) +
        kpi('is-pizarra', icono('fa-file-invoice-dollar') + 'Total programado', formatoMoneda(t.programado),
            t.montoDescuentos > 0 ? 'Descuentos ' + formatoMoneda(t.montoDescuentos) : 'Matrícula + cuotas') +
        kpi('is-cobrado', icono('fa-check-circle') + 'Cobrado', formatoMoneda(t.pagado),
            'Incluye matrículas ' + formatoMoneda(t.matriculas)) +
        kpi('is-saldo', icono('fa-hourglass-half') + 'Saldo pendiente', formatoMoneda(t.saldo),
            (100 - pct) + '% del total programado') +
        kpi('is-clinica', icono('fa-stethoscope') + 'Clínicas cobradas', formatoMoneda(t.clinicaCobrado),
            t.clinicaPorCobrar > 0
                ? 'Por cobrar ' + formatoMoneda(t.clinicaPorCobrar) + ' (' + t.clinicaPendientes + ')'
                : (t.programasClinica > 0 ? 'Sin órdenes pendientes' : 'Ningún programa con clínica'))
    );
    renderComposicionCobro(t);
}

/**
 * Composición del total programado: una barra apilada (cobrado / saldo pendiente) con
 * montos y porcentajes rotulados, leyenda y detalle al pasar el mouse. Colores
 * validados para daltonismo (azul institucional / ámbar).
 */
function renderComposicionCobro(t) {
    const $c = $('#reportePagosComposicion');
    if (!(t.programado > 0)) {
        $c.html('');
        return;
    }
    const pctCobrado = porcentaje(t.pagado, t.programado);
    const pctSaldo = Math.max(0, 100 - pctCobrado);
    const segmento = function(clase, pctSeg, etiqueta, monto) {
        if (pctSeg <= 0) return '';
        return '<span class="rp-comp-seg ' + clase + '" style="width:' + pctSeg + '%" ' +
            'title="' + etiqueta + ': ' + formatoMoneda(monto) + ' (' + pctSeg + '%)"></span>';
    };
    $c.html(
        '<div class="rp-comp-cabecera">' +
            '<span class="rp-comp-titulo">Composición del total programado</span>' +
            '<span class="rp-comp-leyenda">' +
                '<span><i class="rp-dot is-cobrado"></i> Cobrado <b>' + formatoMoneda(t.pagado) + '</b> (' + pctCobrado + '%)</span>' +
                '<span><i class="rp-dot is-saldo"></i> Saldo pendiente <b>' + formatoMoneda(t.saldo) + '</b> (' + pctSaldo + '%)</span>' +
            '</span>' +
        '</div>' +
        '<div class="rp-comp-barra" role="img" aria-label="Cobrado ' + pctCobrado + ' por ciento, saldo pendiente ' + pctSaldo + ' por ciento">' +
            segmento('is-cobrado', pctCobrado, 'Cobrado', t.pagado) +
            segmento('is-saldo', pctSaldo, 'Saldo pendiente', t.saldo) +
        '</div>'
    );
}

/**
 * Ver el detalle de pagos por estudiante de un programa (modal)
 */
// Datos del detalle abierto (los filtros del modal trabajan sobre ellos)
let detallePagos = { datos: [], tieneClinica: false };

function verDetallePagosPrograma(programaID, programaNombre, tieneClinica) {
    $('#detallePagosBuscar').val('');
    $('#detallePagosEstado, #detallePagosPlan, #detallePagosOrden').val('');
    $('#detallePagosProgramaNombre').text(programaNombre || '');
    $('#detallePagosKpis').html('');
    $('#tablaDetallePagosEstudianteHead').html('');
    $('#tablaDetallePagosEstudianteFooter').html('');
    $('#tablaDetallePagosEstudianteBody').html('<tr><td colspan="8" class="rp-vacio"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>');
    $('#modalDetallePagosPrograma').modal('show');

    $.ajax({
        url: 'ajax/reportemodulos.ajax.php',
        method: 'POST',
        data: { accion: 'obtenerReportePagosEstudiantes', programaID: programaID },
        dataType: 'json',
        success: function(response) {
            detallePagos = { datos: Array.isArray(response) ? response : [], tieneClinica: tieneClinica };
            filtrarDetallePagos();
        },
        error: function() {
            $('#tablaDetallePagosEstudianteBody').html('<tr><td colspan="8" class="rp-vacio" style="color:#b91c1c">No se pudo cargar el detalle</td></tr>');
        }
    });
}

// Situación de pago de un estudiante, para el filtro del detalle
function situacionEstudiante(e) {
    if (e.EstadoInscripcion === 'ABANDONO') return 'abandono';
    if (!e.TipoPlan) return 'sinplan';
    return (parseFloat(e.SaldoPendiente) || 0) > 0 ? 'saldo' : 'completado';
}

function filtrarDetallePagos() {
    const texto = ($('#detallePagosBuscar').val() || '').trim().toLowerCase();
    const estado = $('#detallePagosEstado').val();
    const plan = $('#detallePagosPlan').val();
    const orden = $('#detallePagosOrden').val();
    const m = function(v) { return parseFloat(v) || 0; };

    const lista = detallePagos.datos.filter(function(e) {
        if (texto && [e.NombreCompleto, e.Ci].join(' ').toLowerCase().indexOf(texto) === -1) return false;
        if (estado && situacionEstudiante(e) !== estado) return false;
        if (plan && e.TipoPlan !== plan) return false;
        return true;
    });
    if (orden === 'saldo') lista.sort(function(a, b) { return m(b.SaldoPendiente) - m(a.SaldoPendiente); });
    if (orden === 'avance') lista.sort(function(a, b) { return porcentaje(m(a.TotalPagado), m(a.CostoTotal)) - porcentaje(m(b.TotalPagado), m(b.CostoTotal)); });

    renderDetallePagosEstudiante(lista, detallePagos.tieneClinica, detallePagos.datos.length > 0);
}

function renderDetallePagosEstudiante(datos, tieneClinica, hayEstudiantes) {
    $('#tablaDetallePagosEstudianteHead').html(
        '<tr>' +
            '<th>Estudiante</th>' +
            '<th class="text-center">Plan</th>' +
            // Costo desglosado: matrícula + programa (cada uno con su descuento) = costo total
            '<th class="text-right">Matrícula</th>' +
            '<th class="text-right">Costo del programa</th>' +
            '<th class="text-right" title="Matrícula + costo del programa">Costo total</th>' +
            '<th class="text-right">Cobrado</th>' +
            '<th class="text-right">Saldo</th>' +
            '<th style="min-width:110px">Avance</th>' +
            (tieneClinica ? '<th class="text-right rp-grupo-clinica">Clínica cobrada</th><th class="text-right rp-grupo-clinica">Clínica por cobrar</th>' : '') +
        '</tr>'
    );
    const columnas = tieneClinica ? 10 : 8;

    if (!datos || datos.length === 0) {
        $('#detallePagosKpis').html('');
        $('#tablaDetallePagosEstudianteFooter').html('');
        $('#tablaDetallePagosEstudianteBody').html('<tr><td colspan="' + columnas + '" class="rp-vacio">' +
            (hayEstudiantes ? 'Ningún estudiante coincide con los filtros' : 'Este programa no tiene estudiantes inscritos') + '</td></tr>');
        return;
    }

    const t = { matricula: 0, programa: 0, costo: 0, pagado: 0, saldo: 0, clinicaCobrado: 0, clinicaPorCobrar: 0, completaron: 0 };
    // Descuento (positivo) o aumento (negativo) bajo el monto
    const notaAjuste = function(monto) {
        if (Math.abs(monto) < 0.005) return '';
        return '<span class="rp-sub">' + (monto > 0 ? 'desc. ' : 'aum. ') + formatoMoneda(Math.abs(monto)) + '</span>';
    };
    let html = '';

    datos.forEach(function(e) {
        const costoMatricula = parseFloat(e.CostoMatricula) || 0;
        const costoPrograma = parseFloat(e.CostoPrograma) || 0;
        const costoTotal = parseFloat(e.CostoTotal) || 0;
        const pagado = parseFloat(e.TotalPagado) || 0;
        const cuotasCobrado = parseFloat(e.CuotasCobrado) || 0;
        const saldo = parseFloat(e.SaldoPendiente) || 0;
        const clinicaCobrado = parseFloat(e.ClinicaCobrado) || 0;
        const clinicaPorCobrar = parseFloat(e.ClinicaPorCobrar) || 0;
        const abandono = e.EstadoInscripcion === 'ABANDONO';
        const tienePlan = !!e.TipoPlan;
        const completado = tienePlan && saldo <= 0;
        const pct = porcentaje(pagado, costoTotal);

        if (!abandono) {
            t.matricula += costoMatricula;
            t.programa += costoPrograma;
            t.costo += costoTotal;
            t.pagado += pagado;
            t.saldo += saldo;
            if (completado) t.completaron++;
        }
        t.clinicaCobrado += clinicaCobrado;
        t.clinicaPorCobrar += clinicaPorCobrar;

        const cuotasTexto = tienePlan
            ? (e.NumeroCuotas ? (e.CuotasPagadas || 0) + ' de ' + e.NumeroCuotas + ' cuotas' : (e.CuotasPagadas || 0) + ' cuota(s) pagada(s)')
            : '';

        let estado;
        if (abandono) {
            estado = '<span class="rp-chip is-peligro">Abandonó</span>';
        } else if (!tienePlan) {
            estado = '<span class="rp-chip">Sin plan</span>';
        } else if (completado) {
            estado = '<span class="rp-chip is-exito"><i class="fa fa-check-circle"></i> Completado</span>';
        } else {
            estado = '<div class="rp-avance">' + barraAvance(pct) + '<small>' + pct + '%</small></div>';
        }

        html += '<tr class="' + (abandono ? 'rp-fila-abandono' : '') + '">' +
            '<td><div class="rp-prog-nombre">' + escaparHtml(e.NombreCompleto) + '</div>' +
                '<div class="rp-prog-meta">C.I. ' + escaparHtml(e.Ci || '-') + (e.Celular ? ' &middot; Cel. ' + escaparHtml(e.Celular) : '') + '</div></td>' +
            '<td class="text-center">' + (tienePlan ? '<span class="rp-chip is-info">' + (NOMBRES_PLAN_REPORTE[e.TipoPlan] || escaparHtml(e.TipoPlan)) + '</span>' : '<span class="rp-muted">—</span>') +
                (cuotasTexto ? '<span class="rp-sub">' + cuotasTexto + '</span>' : '') + '</td>' +
            '<td class="text-right">' + (costoMatricula > 0 ? formatoMoneda(costoMatricula) : '<span class="rp-muted">Sin matrícula</span>') +
                notaAjuste(parseFloat(e.DescuentoMatricula) || 0) + '</td>' +
            '<td class="text-right">' + (tienePlan ? formatoMoneda(costoPrograma) : '<span class="rp-muted">Sin plan</span>') +
                (tienePlan ? notaAjuste(parseFloat(e.DescuentoPrograma) || 0) : '') + '</td>' +
            '<td class="text-right"><strong>' + formatoMoneda(costoTotal) + '</strong></td>' +
            '<td class="text-right"><span class="rp-cobrado">' + formatoMoneda(pagado) + '</span>' +
                '<span class="rp-sub">matr. ' + formatoMoneda(costoMatricula) + '</span>' +
                '<span class="rp-sub">cuotas ' + formatoMoneda(cuotasCobrado) + '</span></td>' +
            '<td class="text-right"><span class="' + (saldo > 0 ? 'rp-saldo' : 'rp-muted') + '">' + formatoMoneda(saldo) + '</span></td>' +
            '<td>' + estado + '</td>' +
            (tieneClinica
                ? '<td class="text-right rp-celda-clinica"><span class="' + (clinicaCobrado > 0 ? 'rp-clinica-monto' : 'rp-muted') + '">' + formatoMoneda(clinicaCobrado) + '</span></td>' +
                  '<td class="text-right rp-celda-clinica"><span class="' + (clinicaPorCobrar > 0 ? '' : 'rp-muted') + '">' + formatoMoneda(clinicaPorCobrar) + '</span></td>'
                : '') +
        '</tr>';
    });

    $('#tablaDetallePagosEstudianteBody').html(html);

    // Fila de totales (sin los que abandonaron, igual que los indicadores)
    $('#tablaDetallePagosEstudianteFooter').html('<tr>' +
        '<td colspan="2">Totales <span class="rp-sub">sin los que abandonaron</span></td>' +
        '<td class="text-right">' + formatoMoneda(t.matricula) + '</td>' +
        '<td class="text-right">' + formatoMoneda(t.programa) + '</td>' +
        '<td class="text-right"><strong>' + formatoMoneda(t.costo) + '</strong></td>' +
        '<td class="text-right"><span class="rp-cobrado">' + formatoMoneda(t.pagado) + '</span></td>' +
        '<td class="text-right"><span class="' + (t.saldo > 0 ? 'rp-saldo' : 'rp-muted') + '">' + formatoMoneda(t.saldo) + '</span></td>' +
        '<td></td>' +
        (tieneClinica
            ? '<td class="text-right rp-celda-clinica">' + formatoMoneda(t.clinicaCobrado) + '</td>' +
              '<td class="text-right rp-celda-clinica">' + formatoMoneda(t.clinicaPorCobrar) + '</td>'
            : '') +
    '</tr>');

    const pct = porcentaje(t.pagado, t.costo);
    $('#detallePagosKpis').html(
        kpi('', 'Estudiantes', datos.length, t.completaron + ' completaron el pago') +
        kpi('is-cobrado', 'Cobrado', formatoMoneda(t.pagado), 'de ' + formatoMoneda(t.costo)) +
        kpi('is-saldo', 'Saldo pendiente', formatoMoneda(t.saldo), pct + '% cobrado', barraAvance(pct)) +
        (tieneClinica
            ? kpi('is-clinica', '<i class="fa fa-stethoscope"></i> Clínicas cobradas', formatoMoneda(t.clinicaCobrado),
                  'Por cobrar ' + formatoMoneda(t.clinicaPorCobrar))
            : kpi('', 'Clínicas', '<span class="rp-na" style="font-size:14px">No aplica</span>', 'El programa no maneja clínicas'))
    );
}

/**
 * Imprimir en una ventana nueva: cabecera institucional (la misma de los demás
 * formatos PDF: logos UTO/FCS, universidad, facultad, coordinación, dirección,
 * fecha y línea azul; ver ReportesControladores::Header) + indicadores + tabla,
 * en A4 horizontal y sin la columna de acciones.
 */
function imprimirTabla(titulo, contenidoHtml) {
    // Datos y logos de la institución (Configuración), los mismos de los reportes PDF
    const inst = window.INSTITUCION || {};
    const esc = function(t) { return String(t || '').replace(/[&<>"]/g, function(c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
    const logoUTO = new URL(inst.logoIzquierdo || 'extensiones/imagenespdf/logouto.png', document.baseURI).href;
    const logoFCS = new URL(inst.logoDerecho || 'extensiones/imagenespdf/logofcs.png', document.baseURI).href;
    const ahora = new Date();
    const pad = function(n) { return String(n).padStart(2, '0'); };
    const fecha = pad(ahora.getDate()) + '/' + pad(ahora.getMonth() + 1) + '/' + ahora.getFullYear() +
        ' ' + pad(ahora.getHours()) + ':' + pad(ahora.getMinutes());

    const ventana = window.open('', '_blank');

    ventana.document.write(`
        <!doctype html>
        <html lang="es">
        <head>
            <meta charset="utf-8">
            <title>${titulo}</title>
            <style>
                @page { size: A4 landscape; margin: 10mm 12mm 14mm; }
                @page { @bottom-center { content: "Página " counter(page) " de " counter(pages); font: italic 8px Helvetica, Arial, sans-serif; color: #808080; } }
                body { font-family: Helvetica, Arial, sans-serif; margin: 0; font-size: 10.5px; color: #1f2937; }
                * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

                /* Cabecera institucional (igual a los reportes PDF) */
                .cabecera { position: relative; text-align: center; min-height: 68px; }
                .cabecera img { position: absolute; top: 0; width: 68px; height: 68px; object-fit: contain; }
                .cabecera .logo-izq { left: 0; }
                .cabecera .logo-der { right: 0; }
                .cabecera .uni { font-size: 16px; font-weight: bold; color: #1e3c72; margin: 0; }
                .cabecera .fac { font-size: 12px; font-weight: bold; color: #1e3c72; margin: 2px 0 0; }
                .cabecera .dir { font-size: 9px; color: #646464; margin: 4px 0 0; }
                .cabecera-fecha { text-align: right; font-size: 10px; color: #646464; margin-top: 4px; }
                .cabecera-linea { border: 0; border-top: 1.5px solid #1e3c72; margin: 3px 0 12px; }
                h2 { text-align: center; margin: 0 0 12px; font-size: 14px; color: #333333; }

                .rp-kpis { display: flex; gap: 8px; margin-bottom: 12px; }
                .rp-kpi { flex: 1; border: 1px solid #e5e7eb; border-radius: 6px; padding: 7px 10px; }
                .rp-kpi-label { font-size: 8.5px; text-transform: uppercase; color: #6b7280; font-weight: bold; }
                .rp-kpi-valor { font-size: 13px; font-weight: bold; margin-top: 2px; }
                .rp-kpi-sub { font-size: 8.5px; color: #6b7280; }
                .rp-barra { display: none; }
                table { width: 100%; border-collapse: collapse; }
                thead { display: table-header-group; }
                tr { page-break-inside: avoid; }
                th, td { border: 1px solid #d1d5db; padding: 4px 6px; text-align: left; vertical-align: middle; }
                th { background: #1e3c72; color: #fff; font-size: 8.5px; text-transform: uppercase; }
                th.rp-grupo-clinica { background: #0f766e; }
                tfoot td { background: #f3f4f6; font-weight: bold; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                .rp-sub, .rp-prog-meta, .rp-planes-linea { display: block; font-size: 8.5px; color: #6b7280; font-weight: normal; }
                .rp-prog-nombre { font-weight: bold; }
                .rp-chip { display: inline-block; padding: 0 5px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 8.5px; margin: 1px; }
                .rp-estado .rp-chip.is-exito::before { content: "Pagaron "; }
                .rp-estado .rp-chip.is-alerta::before { content: "Con saldo "; }
                .rp-cobrado { color: #15803d; font-weight: bold; }
                .rp-saldo { color: #b91c1c; font-weight: bold; }
                .rp-clinica-monto { color: #0f766e; font-weight: bold; }
                .rp-na, .rp-muted { color: #9ca3af; }
                .rp-avance small { font-size: 9px; }
                .rp-no-print, .fa { display: none; }

                /* En pantalla (al cancelar la impresión o volver a la pestaña): hoja
                   A4 horizontal centrada sobre fondo gris, con barra para imprimir */
                .barra-vista { display: none; }
                @media screen {
                    html { background: #e9ecf2; }
                    body { padding: 64px 16px 32px; }
                    .hoja {
                        max-width: 297mm; margin: 0 auto; background: #fff;
                        padding: 12mm 14mm; box-sizing: border-box;
                        border-radius: 6px; box-shadow: 0 4px 24px rgba(15, 23, 42, .15);
                    }
                    .barra-vista {
                        display: flex; align-items: center; justify-content: space-between; gap: 12px;
                        position: fixed; top: 0; left: 0; right: 0; z-index: 10;
                        padding: 10px 20px; background: #1e3c72; color: #fff;
                        box-shadow: 0 2px 8px rgba(15, 23, 42, .2);
                    }
                    .barra-vista strong { font-size: 13px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
                    .barra-vista div { display: flex; gap: 8px; flex-shrink: 0; }
                    .barra-vista button {
                        font: 600 13px Helvetica, Arial, sans-serif; cursor: pointer;
                        border-radius: 6px; padding: 7px 16px; border: 1px solid rgba(255, 255, 255, .5);
                        background: transparent; color: #fff;
                    }
                    .barra-vista button.principal { background: #fff; color: #1e3c72; border-color: #fff; }
                    .barra-vista button:hover { opacity: .9; }
                }
                @media print { .hoja { padding: 0; } }
            </style>
        </head>
        <body>
            <div class="barra-vista">
                <strong>${titulo}</strong>
                <div>
                    <button type="button" class="principal" onclick="window.print()">&#128424; Imprimir</button>
                    <button type="button" onclick="window.close()">Cerrar</button>
                </div>
            </div>
            <div class="hoja">
            <div class="cabecera">
                <img class="logo-izq" src="${logoUTO}" alt="">
                <img class="logo-der" src="${logoFCS}" alt="">
                <p class="uni">${esc(inst.universidad)}</p>
                <p class="fac">${esc(inst.facultad)}</p>
                <p class="fac">${esc(inst.unidad)}</p>
                <p class="dir">${esc(inst.direccionCompleta)}</p>
            </div>
            <div class="cabecera-fecha">Fecha: ${fecha}</div>
            <hr class="cabecera-linea">
            <h2>${titulo}</h2>
            ${contenidoHtml}
            </div>
            <script>
                // Imprimir recién cuando los logos terminaron de cargar
                window.onload = function() { window.focus(); window.print(); };
            <\/script>
        </body>
        </html>
    `);

    ventana.document.close();
}

function imprimirReportePagos() {
    const kpis = document.getElementById('reportePagosKpis').outerHTML;
    const tabla = document.getElementById('tablaReportePagos').outerHTML;
    const filtros = [$('#reportePagosGrado').val(), $('#reportePagosSede').val(),
        $('#reportePagosSituacion').val() ? $('#reportePagosSituacion option:selected').text() : ''].filter(Boolean);
    imprimirTabla('REPORTE DE PAGOS POR PROGRAMA' + (filtros.length ? ' - ' + filtros.join(' - ').toUpperCase() : ''), kpis + tabla);
}

function imprimirDetallePagos() {
    const titulo = 'DETALLE DE PAGOS: ' + $('#detallePagosProgramaNombre').text();
    const kpis = document.getElementById('detallePagosKpis').outerHTML;
    const tabla = document.getElementById('tablaDetallePagosEstudiante').outerHTML;
    imprimirTabla(titulo, kpis + tabla);
}

/**
 * Mostrar módulos del programa en modal (información académica: código, nombre y
 * costo de referencia). El pago ya no está ligado a módulos individuales, así que
 * aquí solo se lista el contenido curricular del programa.
 */
function mostrarModulosPrograma(programaID, programaNombre) {
    $('#modalModulosProgramaLabel').text(programaNombre || 'Programa');
    $('#contenido-modulos-programa').html('<div class="rp-vacio"><i class="fa fa-spinner fa-spin"></i> Cargando módulos...</div>');
    $('#modalModulosPrograma').modal('show');

    $.ajax({
        url: 'ajax/reportemodulos.ajax.php',
        method: 'POST',
        data: { accion: 'obtenerModulos', programaID: programaID },
        dataType: 'json',
        success: function(response) {
            mostrarTarjetasModulos(response);
        },
        error: function() {
            $('#contenido-modulos-programa').html('<div class="rp-vacio" style="color:#b91c1c">No se pudieron cargar los módulos</div>');
        }
    });
}

function mostrarTarjetasModulos(modulos) {
    if (!Array.isArray(modulos) || modulos.length === 0) {
        $('#contenido-modulos-programa').html('<div class="rp-vacio">Este programa no tiene módulos activos registrados.</div>');
        return;
    }

    let html = '<ul class="rp-modulos">';
    modulos.forEach(function(modulo, i) {
        html += '<li>' +
            '<span class="rp-modulo-num">' + (i + 1) + '</span>' +
            '<div><span class="rp-chip is-info">' + escaparHtml(modulo.codigomodulo) + '</span>' +
            '<div class="rp-modulo-nombre">' + escaparHtml(modulo.nombremodulo) + '</div></div>' +
        '</li>';
    });
    html += '</ul><div class="rp-modulos-total">' + modulos.length + ' módulo(s) activo(s)</div>';

    $('#contenido-modulos-programa').html(html);
}
