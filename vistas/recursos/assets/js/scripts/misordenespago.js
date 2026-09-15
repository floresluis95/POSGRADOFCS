/**
 * Mis Órdenes de Pago (Estudiante): lista sus propias órdenes de matrícula,
 * cuotas del programa y pago de módulos, con botón para imprimir cada una.
 */
(function () {
    var ETIQUETAS_TIPO = { MATRICULA: 'Matrícula / Cuota', MODULOS: 'Pago de Módulos' };

    function formatoFecha(iso) {
        if (!iso) return '-';
        var d = new Date(iso.slice(0, 10) + 'T00:00:00');
        if (isNaN(d.getTime())) return '-';
        return d.toLocaleDateString('es-BO', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function formatoMoneda(valor) {
        var n = parseFloat(valor);
        if (isNaN(n)) return '-';
        return 'Bs. ' + n.toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function badgeEstado(o) {
        if (o.estado === 'ANULADO') return '<span class="badge-orden badge-anulado">Anulada</span>';
        if (o.estado === 'CONFIRMADO') return '<span class="badge-orden badge-confirmado">Pagada</span>';
        if (o.vencida) return '<span class="badge-orden badge-vencido">Vencida</span>';
        return '<span class="badge-orden badge-pendiente">Pendiente</span>';
    }

    function actualizarFecha() {
        var opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
        var fecha = new Date().toLocaleDateString('es-ES', opciones);
        var el = document.getElementById('lafecha');
        if (el) el.textContent = fecha.charAt(0).toUpperCase() + fecha.slice(1);
    }

    function cargarOrdenes() {
        if (typeof $ === 'undefined') return;

        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            method: 'POST',
            data: { accion: 'misOrdenesPago' },
            dataType: 'json',
            success: function (resp) {
                $('#ordenes_cargando').hide();

                if (!resp.success || !resp.ordenes || !resp.ordenes.length) {
                    $('#ordenes_vacio').show();
                    return;
                }

                var $tbody = $('#contenido_ordenes_pago');
                $tbody.empty();

                resp.ordenes.forEach(function (o) {
                    var puedeImprimir = o.estado !== 'ANULADO';
                    var botonImprimir = puedeImprimir
                        ? '<a href="vistas/componentes/orden-pago-pdf.php?idordenpago=' + o.idOrdenPago + '" ' +
                          'target="_blank" class="btn-imprimir-orden"><i class="fa fa-print"></i> Imprimir</a>'
                        : '<span class="text-muted small">No disponible</span>';

                    var fila = '<tr>' +
                        '<td><strong>' + (o.numeroOrden || '-') + '</strong></td>' +
                        '<td>' + o.nombrePrograma + '<br><small class="text-muted">' + o.gradoAcademico + ' - ' + o.sede + '</small></td>' +
                        '<td>' + (ETIQUETAS_TIPO[o.tipo] || o.tipo) + '</td>' +
                        '<td class="text-right">' + formatoMoneda(o.monto) + '</td>' +
                        '<td>' + formatoFecha(o.fechaGeneracion) + '</td>' +
                        '<td>' + (o.fechaVencimiento ? formatoFecha(o.fechaVencimiento) : '-') + '</td>' +
                        '<td class="text-center">' + badgeEstado(o) + '</td>' +
                        '<td class="text-center">' + botonImprimir + '</td>' +
                        '</tr>';
                    $tbody.append(fila);
                });

                $('#ordenes_tabla_wrap').show();
            },
            error: function () {
                $('#ordenes_cargando').hide();
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar sus órdenes de pago' });
            }
        });
    }

    function iniciar() {
        actualizarFecha();
        setInterval(actualizarFecha, 1000);
        cargarOrdenes();
    }

    if (document.readyState === 'complete') {
        iniciar();
    } else {
        window.addEventListener('load', iniciar);
    }
})();
