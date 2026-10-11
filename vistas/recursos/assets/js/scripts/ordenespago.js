/**
 * Órdenes de pago (personal: ADM, SEC, AUX): todas las órdenes ya generadas
 * (matrícula, cuotas, clínica y módulos), con filtros y botón para volver a
 * imprimir. Sirve también de control de lo que ya se emitió y quién lo generó.
 *
 * El rango de fechas se pide al servidor; búsqueda, tipo, estado y programa se
 * filtran aquí sobre lo cargado, sin volver a pedirlo.
 */
(function () {
    var ETIQUETAS_TIPO = { MATRICULA: 'Matrícula', CUOTA: 'Cuota', CLINICA: 'Clínica', MODULOS: 'Módulos' };
    var POR_PAGINA = 50;

    var todas = [];
    var filtradas = [];
    var mostradas = 0;

    function esc(texto) {
        return $('<div>').text(texto == null ? '' : String(texto)).html();
    }

    function formatoFecha(valor) {
        if (!valor) return '-';
        var p = String(valor).match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}))?/);
        if (!p) return '-';
        return p[3] + '/' + p[2] + '/' + p[1] + (p[4] ? ' ' + p[4] + ':' + p[5] : '');
    }

    function formatoMoneda(valor) {
        var n = parseFloat(valor) || 0;
        return 'Bs. ' + n.toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Estado que se muestra: PAGADA (confirmada), ANULADA, VENCIDA (pendiente con la
    // fecha límite pasada) o PENDIENTE
    function estadoDe(o) {
        if (o.estado === 'CONFIRMADO') return 'PAGADA';
        if (o.estado === 'ANULADO') return 'ANULADO';
        if (o.vencida) return 'VENCIDA';
        return 'PENDIENTE';
    }

    var INSIGNIAS = {
        PAGADA: '<span class="op-estado op-estado-pagada">Pagada</span>',
        ANULADO: '<span class="op-estado op-estado-anulada">Anulada</span>',
        VENCIDA: '<span class="op-estado op-estado-vencida">Vencida</span>',
        PENDIENTE: '<span class="op-estado op-estado-pendiente">Pendiente</span>'
    };

    function fila(o) {
        var estado = estadoDe(o);
        var tipo = ETIQUETAS_TIPO[o.tipo] || o.tipo;
        if (o.tipo === 'MATRICULA' && o.pagoCompleto) tipo += ' <span class="op-sub">Programa completo</span>';

        // Una orden anulada no sirve para pagar: no se ofrece imprimirla
        var accion = estado === 'ANULADO'
            ? '<span class="op-sub">Sin validez</span>'
            : '<a href="vistas/componentes/orden-pago-pdf.php?idordenpago=' + o.idOrdenPago + '" target="_blank" class="op-imprimir">' +
              '<i class="fa fa-print"></i> Imprimir</a>';

        return '<tr' + (estado === 'ANULADO' ? ' class="op-anulada"' : '') + '>' +
            '<td data-label="N° Orden"><span class="op-numero">' + esc(o.numeroOrden || '-') + '</span></td>' +
            '<td data-label="Estudiante"><span>' + esc(o.estudiante) + '<span class="op-sub">C.I. ' + esc(o.ci) + '</span></span></td>' +
            '<td data-label="Programa"><span>' + esc(o.nombrePrograma) + '<span class="op-sub">' + esc(o.gradoAcademico) + ' · ' + esc(o.sede) + '</span></span></td>' +
            '<td data-label="Tipo"><span>' + tipo + '</span></td>' +
            '<td data-label="Monto" class="text-right"><span class="op-monto">' + formatoMoneda(o.monto) + '</span></td>' +
            '<td data-label="Generada"><span><span class="op-fecha">' + formatoFecha(o.fechaGeneracion) + '</span>' +
                (o.responsable ? '<span class="op-sub">por ' + esc(o.responsable) + '</span>' : '') + '</span></td>' +
            '<td data-label="Estado" class="text-center">' + INSIGNIAS[estado] + '</td>' +
            '<td class="text-center op-celda-accion">' + accion + '</td>' +
        '</tr>';
    }

    function normalizar(texto) {
        return String(texto || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
    }

    function aplicarFiltros() {
        var buscar = normalizar($('#op_buscar').val().trim());
        var tipo = $('#op_tipo').val();
        var estado = $('#op_estado').val();
        var programa = $('#op_programa').val();

        filtradas = todas.filter(function (o) {
            if (tipo && o.tipo !== tipo) return false;
            if (estado && estadoDe(o) !== estado) return false;
            if (programa && String(o.programaID) !== programa) return false;
            if (buscar) {
                var texto = normalizar(o.estudiante + ' ' + o.ci + ' ' + o.numeroOrden);
                if (texto.indexOf(buscar) === -1) return false;
            }
            return true;
        });

        actualizarContadores();
        mostradas = 0;
        $('#op_cuerpo').empty();
        mostrarMas();
    }

    function actualizarContadores() {
        var c = { PENDIENTE: 0, VENCIDA: 0, PAGADA: 0, ANULADO: 0 };
        filtradas.forEach(function (o) { c[estadoDe(o)]++; });
        $('#op_total').text(filtradas.length);
        $('#op_pendientes').text(c.PENDIENTE);
        $('#op_vencidas').text(c.VENCIDA);
        $('#op_pagadas').text(c.PAGADA);
        $('#op_anuladas').text(c.ANULADO);
    }

    function mostrarMas() {
        if (!filtradas.length) {
            $('#op_cuerpo').html('<tr><td colspan="8" class="op-vacio">' +
                (todas.length ? 'Ninguna orden coincide con los filtros.' : 'Todavía no hay órdenes de pago generadas en este periodo.') +
                '</td></tr>');
            $('#op_mostrando').text('');
            $('#op_mas').hide();
            return;
        }

        var siguiente = filtradas.slice(mostradas, mostradas + POR_PAGINA);
        $('#op_cuerpo').append(siguiente.map(fila).join(''));
        mostradas += siguiente.length;

        $('#op_mostrando').text('Mostrando ' + mostradas + ' de ' + filtradas.length + ' órdenes');
        $('#op_mas').toggle(mostradas < filtradas.length);
    }

    function llenarProgramas() {
        var $select = $('#op_programa');
        var actual = $select.val();
        var vistos = {};
        var programas = [];
        todas.forEach(function (o) {
            if (vistos[o.programaID]) return;
            vistos[o.programaID] = true;
            programas.push(o);
        });
        programas.sort(function (a, b) { return a.nombrePrograma.localeCompare(b.nombrePrograma); });

        $select.find('option:not(:first)').remove();
        programas.forEach(function (o) {
            $select.append('<option value="' + o.programaID + '">' + esc(o.nombrePrograma + ' (' + o.codigoPrograma + ')') + '</option>');
        });
        if (actual && vistos[actual]) $select.val(actual);
    }

    function cargar() {
        $('#op_cuerpo').html('<tr><td colspan="8" class="op-vacio">Cargando órdenes de pago...</td></tr>');
        $('#op_mas').hide();

        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            method: 'POST',
            dataType: 'json',
            data: { accion: 'listarOrdenesGeneradas', desde: $('#op_desde').val(), hasta: $('#op_hasta').val() },
            success: function (resp) {
                if (!resp || !resp.success) {
                    $('#op_cuerpo').html('<tr><td colspan="8" class="op-vacio">No se pudieron cargar las órdenes de pago.</td></tr>');
                    return;
                }
                todas = resp.ordenes || [];
                llenarProgramas();
                aplicarFiltros();
            },
            error: function () {
                $('#op_cuerpo').html('<tr><td colspan="8" class="op-vacio">No se pudieron cargar las órdenes de pago.</td></tr>');
            }
        });
    }

    function actualizarFecha() {
        var opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        var fecha = new Date().toLocaleDateString('es-ES', opciones);
        $('#lafecha').text(fecha.charAt(0).toUpperCase() + fecha.slice(1));
    }

    function iniciar() {
        actualizarFecha();

        var temporizador = null;
        $('#op_buscar').on('input', function () {
            clearTimeout(temporizador);
            temporizador = setTimeout(aplicarFiltros, 200);
        });
        $('#op_tipo, #op_estado, #op_programa').on('change', aplicarFiltros);
        $('#op_desde, #op_hasta').on('change', cargar);
        $('#op_mas').on('click', mostrarMas);
        $('#op_limpiar').on('click', function () {
            $('#op_buscar').val('');
            $('#op_tipo, #op_estado, #op_programa').val('');
            var recargar = $('#op_desde').val() || $('#op_hasta').val();
            $('#op_desde, #op_hasta').val('');
            if (recargar) cargar(); else aplicarFiltros();
        });

        cargar();
    }

    // jQuery se carga al final de la plantilla: esperar a que esté
    if (document.readyState === 'complete') {
        iniciar();
    } else {
        window.addEventListener('load', iniciar);
    }
})();
