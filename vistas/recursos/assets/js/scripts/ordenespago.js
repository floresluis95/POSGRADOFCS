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
            : '<a href="vistas/componentes/orden-pago-pdf.php?idordenpago=' + o.idOrdenPago + '" target="_blank" class="op-imprimir"' +
              (estado === 'PAGADA' ? ' data-pagada="1" data-numero="' + esc(o.numeroOrden || '') + '"' : '') + '>' +
              '<i class="fa fa-print"></i> Imprimir</a>';
        // Validar el voucher de una orden pendiente: la matrícula en Inscripción (como
        // desde Pre-Registro); cuota y clínica aquí mismo, en la ventana "Validar voucher"
        var validar = '';
        if (o.estado === 'PENDIENTE') {
            if (o.tipo === 'MATRICULA') {
                validar = '<a href="inscripcion?orden=' + o.idOrdenPago + '" class="op-validar" title="Validar el voucher en Inscripción">' +
                    '<i class="fa fa-check-circle"></i> Validar</a>';
            } else if ((o.tipo === 'CUOTA' || o.tipo === 'CLINICA') && o.idInscripcion) {
                validar = '<button type="button" class="op-validar op-btn-validar" data-id="' + o.idOrdenPago + '" title="Registrar el voucher de esta orden">' +
                    '<i class="fa fa-check-circle"></i> Validar</button>';
            }
        }

        // Ir a Matriculados filtrado por el C.I. del estudiante (matriculados?buscar=CI)
        accion = '<div class="op-acciones">' + validar + accion +
            '<a href="matriculados?buscar=' + encodeURIComponent(o.ci || '') + '" class="op-ir-matriculado" title="Ver en Matriculados">' +
            '<i class="fa fa-user-graduate"></i></a></div>';

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
                // Desde Matriculados se llega con ?programa=ID: se elige una sola vez,
                // cuando el select ya tiene los programas
                if (programaInicial) {
                    if ($('#op_programa option[value="' + programaInicial + '"]').length) $('#op_programa').val(programaInicial);
                    programaInicial = '';
                }
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

    // ========================================
    // VALIDAR VOUCHER (cuota o clínica pendiente). Usa los mismos endpoints que
    // la ventana "Gestión de Pagos" de Matriculados: confirman la orden pendiente
    // de la inscripción (la cuota exige además el monto exacto de la orden).
    // ========================================
    var ordenValidando = null;

    function pintarNumeroVoucher() {
        var $numero = $('#opv_preview .opv-numero');
        if (!$numero.length) return;
        var numero = ($('#opv_numero').val() || '').trim();
        $numero.toggleClass('is-vacio', !numero).html(numero
            ? '<span>N° de voucher registrado:</span> <strong>' + esc(numero) + '</strong>'
            : '<i class="fa fa-exclamation-triangle"></i> <span>Aún no se escribió el N° de voucher</span>');
    }

    function abrirValidar(idOrden) {
        ordenValidando = todas.filter(function (o) { return o.idOrdenPago === idOrden; })[0] || null;
        if (!ordenValidando) return;
        var o = ordenValidando;

        $('#opv_resumen').html(
            '<dt>Orden</dt><dd><strong>' + esc(o.numeroOrden || '-') + '</strong> · ' + esc(ETIQUETAS_TIPO[o.tipo] || o.tipo) + '</dd>' +
            '<dt>Estudiante</dt><dd>' + esc(o.estudiante) + ' <span class="op-sub">C.I. ' + esc(o.ci) + '</span></dd>' +
            '<dt>Programa</dt><dd>' + esc(o.nombrePrograma) + '</dd>' +
            '<dt>Monto</dt><dd class="opv-monto">' + formatoMoneda(o.tipo === 'CUOTA' ? o.montoTotal : o.monto) + '</dd>'
        );
        $('#op_form_validar')[0].reset();
        $('#opv_fecha').val(new Date().toLocaleDateString('sv-SE'));
        $('#opv_preview').html('');
        $('#opv_guardar').prop('disabled', false).html('<i class="fa fa-save"></i> Validar pago');
        $('#op_modal_validar').modal('show');
    }

    // Vista previa con el N° de voucher arriba, para comparar con la foto
    function mostrarVistaPrevia() {
        var archivo = this.files && this.files[0];
        var $preview = $('#opv_preview');
        if (!archivo) { $preview.html(''); return; }
        if (archivo.type === 'application/pdf') {
            $preview.html('<div class="opv-numero"></div><span class="op-sub"><i class="fa fa-file-pdf"></i> ' + esc(archivo.name) + '</span>');
            pintarNumeroVoucher();
            return;
        }
        if (archivo.type.indexOf('image/') !== 0) { $preview.html(''); return; }
        var lector = new FileReader();
        lector.onload = function (e) {
            $preview.html('<div class="opv-numero"></div><img src="' + e.target.result + '" alt="Vista previa del voucher">');
            pintarNumeroVoucher();
        };
        lector.readAsDataURL(archivo);
    }

    function guardarValidacion(e) {
        e.preventDefault();
        var o = ordenValidando;
        if (!o) return;

        var fecha = $('#opv_fecha').val();
        var numero = ($('#opv_numero').val() || '').trim();
        var archivo = $('#opv_archivo')[0].files[0];
        if (!fecha || !numero || !archivo) {
            Swal.fire({ type: 'warning', title: 'Atención', text: 'Complete la fecha, el N° de voucher y el archivo del voucher' });
            return;
        }

        var datos = new FormData();
        datos.append('idinscripcion', o.idInscripcion);
        datos.append('fechaPago', fecha);
        var url;
        if (o.tipo === 'CUOTA') {
            url = 'ajax/pagomodulo.ajax.php';
            datos.append('accion', 'registrarPagoModulo');
            datos.append('montoCuota', o.montoTotal);
            datos.append('numeroVaucher', numero);
            datos.append('fmodulo', archivo);
        } else {
            url = 'ajax/pagoclinica.ajax.php';
            datos.append('accion', 'registrarPago');
            datos.append('numeroVoucher', numero);
            datos.append('fotoVoucher', archivo);
        }

        var $btn = $('#opv_guardar');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: url,
            method: 'POST',
            data: datos,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (resp) {
                // pagomodulo responde { estado: 'exitoso' }, pagoclinica { success: true }
                var ok = resp && (resp.estado === 'exitoso' || resp.success === true);
                if (ok) {
                    $('#op_modal_validar').modal('hide');
                    Swal.fire({ type: 'success', title: 'Pago validado', text: resp.mensaje || 'El voucher se registró correctamente', timer: 1800, showConfirmButton: false });
                    cargar();
                } else {
                    Swal.fire({ type: 'warning', title: 'Atención', text: (resp && (resp.mensaje || resp.error)) || 'No se pudo validar el pago' });
                    $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Validar pago');
                }
            },
            error: function () {
                Swal.fire({ type: 'error', title: 'Error', text: 'No se pudo validar el pago' });
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Validar pago');
            }
        });
    }

    // Filtro inicial desde la URL (botón "Órdenes de Pago" de Matriculados)
    var parametros = new URLSearchParams(window.location.search);
    var programaInicial = /^\d+$/.test(parametros.get('programa') || '') ? parametros.get('programa') : '';

    function iniciar() {
        actualizarFecha();
        if (parametros.get('ci')) $('#op_buscar').val(parametros.get('ci'));

        var temporizador = null;
        $('#op_buscar').on('input', function () {
            clearTimeout(temporizador);
            temporizador = setTimeout(aplicarFiltros, 200);
        });
        $('#op_tipo, #op_estado, #op_programa').on('change', aplicarFiltros);
        $('#op_desde, #op_hasta').on('change', cargar);
        $('#op_mas').on('click', mostrarMas);

        $('#op_cuerpo').on('click', '.op-btn-validar', function () { abrirValidar($(this).data('id')); });
        $('#opv_archivo').on('change', mostrarVistaPrevia);
        $('#opv_numero').on('input', pintarNumeroVoucher);
        $('#op_form_validar').on('submit', guardarValidacion);

        // Orden ya pagada: confirmar antes de imprimirla (no debe volver a cobrarse).
        // Las pendientes o vencidas se imprimen directo con el enlace.
        $('#op_cuerpo').on('click', '.op-imprimir[data-pagada]', function (e) {
            e.preventDefault();
            var url = this.href;
            var numero = $(this).data('numero');
            Swal.fire({
                type: 'warning',
                title: 'Esta orden ya fue pagada',
                text: 'La orden' + (numero ? ' N° ' + numero : '') + ' ya está pagada. ¿Desea imprimirla de todas formas?',
                showCancelButton: true,
                confirmButtonText: 'Sí, imprimir',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (!result.value) return; // SweetAlert2 v8: no tiene isConfirmed
                window.open(url, '_blank');
            });
        });
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
