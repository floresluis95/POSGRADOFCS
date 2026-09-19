/**
 * Mis Órdenes de Pago (Estudiante): lista sus propias órdenes de matrícula,
 * cuotas del programa y pago de módulos, con botón para imprimir cada una.
 * Incluye filtros por Programa y por Tipo (Matrícula / Cuota / Pago de Módulos).
 * Las órdenes ANULADAS no se muestran en la tabla principal: quedan aparte, en un
 * modal aparte ("Ver anuladas"), solo como referencia.
 */
(function () {
    var ETIQUETAS_TIPO = { MATRICULA: 'Matrícula', CUOTA: 'Cuota', MODULOS: 'Pago de Módulos' };

    // Todas las órdenes cargadas del servidor (sin filtrar, incluye anuladas); los
    // selects de Programa y Tipo filtran sobre esta lista, sin volver a pedirlas al
    // servidor. La tabla principal siempre excluye las ANULADAS.
    var todasLasOrdenes = [];

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

    // Llena el select de Programa con los programas distintos entre las órdenes cargadas.
    function llenarFiltroPrograma(ordenes) {
        var vistos = {};
        var $select = $('#filtro_programa');
        var valorActual = $select.val();

        $select.find('option:not(:first)').remove();

        ordenes.forEach(function (o) {
            if (vistos[o.programaID]) return;
            vistos[o.programaID] = true;
            $select.append('<option value="' + o.programaID + '">' + o.nombrePrograma + '</option>');
        });

        if (valorActual) {
            // trigger('change') es necesario para que Select2 actualice el
            // texto visible (un .val() plano no repinta el widget).
            $select.val(valorActual).trigger('change');
        }
    }

    function renderTabla(ordenes) {
        var $tbody = $('#contenido_ordenes_pago');
        $tbody.empty();

        if (!ordenes.length) {
            $tbody.html('<tr><td colspan="8" class="text-center text-muted py-4">Ninguna orden coincide con el filtro seleccionado.</td></tr>');
            return;
        }

        ordenes.forEach(function (o) {
            var fila = '<tr>' +
                '<td><strong>' + (o.numeroOrden || '-') + '</strong></td>' +
                '<td>' + o.nombrePrograma + '<br><small class="text-muted">' + o.gradoAcademico + ' - ' + o.sede + '</small></td>' +
                '<td>' + (ETIQUETAS_TIPO[o.tipo] || o.tipo) + '</td>' +
                '<td class="text-right">' + formatoMoneda(o.monto) + '</td>' +
                '<td>' + formatoFecha(o.fechaGeneracion) + '</td>' +
                '<td>' + (o.fechaVencimiento ? formatoFecha(o.fechaVencimiento) : '-') + '</td>' +
                '<td class="text-center">' + badgeEstado(o) + '</td>' +
                '<td class="text-center">' +
                    '<a href="vistas/componentes/orden-pago-pdf.php?idordenpago=' + o.idOrdenPago + '" ' +
                    'target="_blank" class="btn-imprimir-orden"><i class="fa fa-print"></i> Imprimir</a>' +
                '</td>' +
                '</tr>';
            $tbody.append(fila);
        });
    }

    // Tabla del modal "Órdenes Anuladas": más simple, sin acción de imprimir (no
    // tienen validez para el pago, se muestran solo como referencia).
    function renderTablaAnuladas(ordenes) {
        var $tbody = $('#contenido_ordenes_anuladas');
        $tbody.empty();

        if (!ordenes.length) {
            $tbody.html('<tr><td colspan="5" class="text-center text-muted py-3">Sin órdenes anuladas.</td></tr>');
            return;
        }

        ordenes.forEach(function (o) {
            var fila = '<tr>' +
                '<td>' + (o.numeroOrden || '-') + '</td>' +
                '<td>' + o.nombrePrograma + '</td>' +
                '<td>' + (ETIQUETAS_TIPO[o.tipo] || o.tipo) + '</td>' +
                '<td class="text-right">' + formatoMoneda(o.monto) + '</td>' +
                '<td>' + formatoFecha(o.fechaGeneracion) + '</td>' +
                '</tr>';
            $tbody.append(fila);
        });
    }

    function aplicarFiltros() {
        var programaID = $('#filtro_programa').val();
        var tipo = $('#filtro_tipo').val();

        var filtradas = todasLasOrdenes.filter(function (o) {
            if (o.estado === 'ANULADO') return false;
            if (programaID && String(o.programaID) !== String(programaID)) return false;
            if (tipo && o.tipo !== tipo) return false;
            return true;
        });

        renderTabla(filtradas);
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

                todasLasOrdenes = resp.ordenes;
                var anuladas = todasLasOrdenes.filter(function (o) { return o.estado === 'ANULADO'; });

                llenarFiltroPrograma(todasLasOrdenes);
                aplicarFiltros();
                renderTablaAnuladas(anuladas);

                if (anuladas.length) {
                    $('#conteo_anuladas').text(anuladas.length);
                    $('#btn_ver_anuladas').show();
                }

                $('#ordenes_filtros').show();
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

        // El binding de los filtros se hace aquí adentro (no a nivel superior del
        // script) porque recién en 'load' está garantizado que jQuery ya se cargó;
        // hacerlo antes rompía el resto del script si este archivo se ejecutaba
        // antes que jQuery, y con eso nunca se llegaba a llamar a cargarOrdenes().
        if (typeof $ !== 'undefined') {
            $('.select2-uniforme').select2({ width: '100%' });
            $(document).on('change', '#filtro_programa, #filtro_tipo', aplicarFiltros);
        }

        cargarOrdenes();
    }

    if (document.readyState === 'complete') {
        iniciar();
    } else {
        window.addEventListener('load', iniciar);
    }
})();
