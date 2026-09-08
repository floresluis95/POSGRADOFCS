/**
 * Script para Reportes de Módulos y Pagos
 * Gestiona la carga de datos y la generación del reporte de pagos por programa
 */

console.log('=== Script de Reportes Cargado ===');

$(document).ready(function() {
    // Cargar el filtro de grados académicos y el reporte de pagos al iniciar
    cargarGradosParaReportePagos();
    cargarReportePagos();

    $('#reportePagosGrado').on('change', cargarReportePagos);
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
        verDetallePagosPrograma(programaID, programaNombre);
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

const NOMBRES_PLAN_REPORTE = { REGULAR: 'Regular', DESCUENTO: 'Contado (Desc.)', GRUPAL: 'Grupal' };

/**
 * Cargar y renderizar el reporte de pagos por programa
 */
function cargarReportePagos() {
    const grado = $('#reportePagosGrado').val();

    $('#tablaReportePagosBody').html('<tr><td colspan="13" class="text-center text-muted py-4"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>');
    $('#tablaReportePagosFooter').html('');

    $.ajax({
        url: 'ajax/reportemodulos.ajax.php',
        method: 'POST',
        data: { accion: 'obtenerReportePagosPrograma', grado: grado },
        dataType: 'json',
        success: function(response) {
            renderTablaReportePagos(response || []);
        },
        error: function() {
            $('#tablaReportePagosBody').html('<tr><td colspan="13" class="text-center text-danger py-4">No se pudo cargar el reporte de pagos</td></tr>');
        }
    });
}

function renderTablaReportePagos(datos) {
    if (!datos || datos.length === 0) {
        $('#tablaReportePagosBody').html('<tr><td colspan="13" class="text-center text-muted py-4">No hay programas para mostrar</td></tr>');
        $('#tablaReportePagosFooter').html('');
        return;
    }

    let html = '';
    const totales = {
        inscritos: 0, regular: 0, descuento: 0, grupal: 0, sinPlan: 0,
        completaron: 0, pendientes: 0, programado: 0, pagado: 0, saldo: 0
    };

    datos.forEach(function(p) {
        const inscritos = parseInt(p.TotalInscritos) || 0;
        const programado = parseFloat(p.CostoTotalProgramado) || 0;
        const pagado = parseFloat(p.TotalPagado) || 0;
        const saldo = parseFloat(p.SaldoPendiente) || 0;

        totales.inscritos += inscritos;
        totales.regular += parseInt(p.CountRegular) || 0;
        totales.descuento += parseInt(p.CountDescuento) || 0;
        totales.grupal += parseInt(p.CountGrupal) || 0;
        totales.sinPlan += parseInt(p.CountSinPlan) || 0;
        totales.completaron += parseInt(p.CountCompletados) || 0;
        totales.pendientes += parseInt(p.CountPendientes) || 0;
        totales.programado += programado;
        totales.pagado += pagado;
        totales.saldo += saldo;

        html += '<tr>' +
            '<td>' + (p.NombrePrograma || '') + ' <small class="text-muted">(' + (p.Codigo || '') + ')</small></td>' +
            '<td class="text-center">' + (p.GradoAcademico || '') + '</td>' +
            '<td class="text-center"><strong>' + inscritos + '</strong></td>' +
            '<td class="text-center">' + (parseInt(p.CountRegular) || 0) + '</td>' +
            '<td class="text-center">' + (parseInt(p.CountDescuento) || 0) + '</td>' +
            '<td class="text-center">' + (parseInt(p.CountGrupal) || 0) + '</td>' +
            '<td class="text-center">' + (parseInt(p.CountSinPlan) || 0) + '</td>' +
            '<td class="text-center"><span class="badge badge-success">' + (parseInt(p.CountCompletados) || 0) + '</span></td>' +
            '<td class="text-center"><span class="badge badge-warning">' + (parseInt(p.CountPendientes) || 0) + '</span></td>' +
            '<td class="text-right">' + formatoMoneda(programado) + '</td>' +
            '<td class="text-right text-success">' + formatoMoneda(pagado) + '</td>' +
            '<td class="text-right text-danger"><strong>' + formatoMoneda(saldo) + '</strong></td>' +
            '<td class="text-center">' +
                '<button type="button" class="btn btn-xs btn-outline-primary btn-detalle-pagos-programa" ' +
                    'data-programa-id="' + p.ProgramaID + '" data-programa-nombre="' + (p.NombrePrograma || '').replace(/"/g, '&quot;') + '">' +
                    '<i class="fa fa-list"></i> Ver' +
                '</button>' +
            '</td>' +
        '</tr>';
    });

    $('#tablaReportePagosBody').html(html);

    $('#tablaReportePagosFooter').html(
        '<tr class="font-weight-bold" style="background:#f4f4f4;">' +
            '<td colspan="2">TOTALES</td>' +
            '<td class="text-center">' + totales.inscritos + '</td>' +
            '<td class="text-center">' + totales.regular + '</td>' +
            '<td class="text-center">' + totales.descuento + '</td>' +
            '<td class="text-center">' + totales.grupal + '</td>' +
            '<td class="text-center">' + totales.sinPlan + '</td>' +
            '<td class="text-center">' + totales.completaron + '</td>' +
            '<td class="text-center">' + totales.pendientes + '</td>' +
            '<td class="text-right">' + formatoMoneda(totales.programado) + '</td>' +
            '<td class="text-right">' + formatoMoneda(totales.pagado) + '</td>' +
            '<td class="text-right">' + formatoMoneda(totales.saldo) + '</td>' +
            '<td></td>' +
        '</tr>'
    );
}

/**
 * Ver el detalle de pagos por estudiante de un programa (modal)
 */
function verDetallePagosPrograma(programaID, programaNombre) {
    $('#detallePagosProgramaNombre').text(programaNombre || '');
    $('#tablaDetallePagosEstudianteBody').html('<tr><td colspan="8" class="text-center text-muted py-3"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>');
    $('#modalDetallePagosPrograma').modal('show');

    $.ajax({
        url: 'ajax/reportemodulos.ajax.php',
        method: 'POST',
        data: { accion: 'obtenerReportePagosEstudiantes', programaID: programaID },
        dataType: 'json',
        success: function(response) {
            renderDetallePagosEstudiante(response || []);
        },
        error: function() {
            $('#tablaDetallePagosEstudianteBody').html('<tr><td colspan="8" class="text-center text-danger py-3">No se pudo cargar el detalle</td></tr>');
        }
    });
}

function renderDetallePagosEstudiante(datos) {
    if (!datos || datos.length === 0) {
        $('#tablaDetallePagosEstudianteBody').html('<tr><td colspan="8" class="text-center text-muted py-3">Este programa no tiene estudiantes inscritos</td></tr>');
        return;
    }

    let html = '';
    datos.forEach(function(e) {
        const costoTotal = parseFloat(e.CostoTotal) || 0;
        const pagado = parseFloat(e.TotalPagado) || 0;
        const saldo = parseFloat(e.SaldoPendiente) || 0;
        const tienePlan = !!e.TipoPlan;
        const planTexto = tienePlan ? (NOMBRES_PLAN_REPORTE[e.TipoPlan] || e.TipoPlan) : '<span class="text-muted">Sin plan</span>';
        const cuotasTexto = tienePlan
            ? (e.NumeroCuotas ? (e.CuotasPagadas || 0) + ' de ' + e.NumeroCuotas : (e.CuotasPagadas || 0) + ' pagada(s)')
            : '-';
        const completado = tienePlan && saldo <= 0;
        const estadoBadge = !tienePlan
            ? '<span class="badge badge-secondary">SIN PLAN</span>'
            : (completado
                ? '<span class="badge badge-success">COMPLETADO</span>'
                : '<span class="badge badge-warning">PENDIENTE</span>');

        html += '<tr>' +
            '<td>' + (e.NombreCompleto || '') + '</td>' +
            '<td>' + (e.Ci || '') + '</td>' +
            '<td class="text-center">' + planTexto + '</td>' +
            '<td class="text-center">' + cuotasTexto + '</td>' +
            '<td class="text-right">' + formatoMoneda(costoTotal) + '</td>' +
            '<td class="text-right text-success">' + formatoMoneda(pagado) + '</td>' +
            '<td class="text-right text-danger">' + formatoMoneda(saldo) + '</td>' +
            '<td class="text-center">' + estadoBadge + '</td>' +
        '</tr>';
    });

    $('#tablaDetallePagosEstudianteBody').html(html);
}

/**
 * Imprimir una tabla en una ventana nueva, con un título dado
 */
function imprimirTabla(titulo, contenidoHtml) {
    const ventana = window.open('', '_blank');

    ventana.document.write(`
        <html>
        <head>
            <title>${titulo}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; font-size: 12px; }
                table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                th, td { border: 1px solid #999; padding: 6px 8px; text-align: left; }
                th { background: #3b3f5c; color: white; }
                .badge { padding: 3px 8px; border-radius: 10px; font-size: 10px; color: white; }
                .badge-success { background: #28a745; }
                .badge-warning { background: #e0a800; }
                .badge-secondary { background: #6c757d; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                .text-success { color: #28a745; }
                .text-danger { color: #dc3545; }
            </style>
        </head>
        <body>
            <h2 style="text-align:center;">${titulo}</h2>
            <p style="text-align:center;color:#666;">Fecha de impresión: ${new Date().toLocaleDateString('es-BO')}</p>
            <hr>
            ${contenidoHtml}
        </body>
        </html>
    `);

    ventana.document.close();
    ventana.print();
}

function imprimirReportePagos() {
    const tabla = document.getElementById('tablaReportePagos').outerHTML;
    imprimirTabla('REPORTE DE PAGOS POR PROGRAMA', tabla);
}

function imprimirDetallePagos() {
    const titulo = 'DETALLE DE PAGOS: ' + $('#detallePagosProgramaNombre').text();
    const tabla = document.getElementById('tablaDetallePagosEstudiante').outerHTML;
    imprimirTabla(titulo, tabla);
}

/**
 * Mostrar módulos del programa en modal (información académica: código, nombre y
 * costo de referencia). El pago ya no está ligado a módulos individuales, así que
 * aquí solo se lista el contenido curricular del programa.
 */
function mostrarModulosPrograma(programaID, programaNombre) {
    $('#modalModulosProgramaLabel').html(`<i class="flaticon2-layers"></i> Módulos de: ${programaNombre}`);
    $('#modalModulosPrograma').modal('show');

    $('#contenido-modulos-programa').html(`
        <div class="text-center p-5">
            <div class="kt-spinner kt-spinner--lg kt-spinner--brand"></div>
            <p class="mt-3" style="color: #667eea; font-weight: 600;">Cargando módulos...</p>
        </div>
    `);

    $.ajax({
        url: 'ajax/reportemodulos.ajax.php',
        method: 'POST',
        data: { accion: 'obtenerModulos', programaID: programaID },
        dataType: 'json',
        success: function(response) {
            mostrarTarjetasModulos(response);
        },
        error: function() {
            $('#contenido-modulos-programa').html(`
                <div class="alert alert-danger text-center">
                    <i class="fa fa-exclamation-circle fa-2x"></i>
                    <h5 class="mt-2">Error al cargar módulos</h5>
                </div>
            `);
        }
    });
}

function mostrarTarjetasModulos(modulos) {
    if (!modulos || modulos.length === 0) {
        $('#contenido-modulos-programa').html(`
            <div class="alert alert-warning text-center">
                <i class="fa fa-exclamation-triangle fa-2x"></i>
                <h5 class="mt-2">No hay módulos disponibles</h5>
                <p>Este programa no tiene módulos registrados</p>
            </div>
        `);
        return;
    }

    let html = '';

    modulos.forEach(function(modulo) {
        const costo = parseFloat(modulo.costomodulo || 0).toFixed(2);

        html += `
            <div class="modulo-card" style="cursor:default;">
                <div class="modulo-card-header">
                    <span class="modulo-codigo">
                        <i class="flaticon2-file-1"></i> ${modulo.codigomodulo}
                    </span>
                </div>
                <div class="modulo-nombre">
                    ${modulo.nombremodulo}
                </div>
                <div class="modulo-card-footer">
                    <div>
                        <small style="color: #B5B5C3; text-transform: uppercase; font-size: 11px;">Costo Referencial</small>
                        <div class="modulo-costo">Bs. ${costo}</div>
                    </div>
                </div>
            </div>
        `;
    });

    $('#contenido-modulos-programa').html(html);
}
