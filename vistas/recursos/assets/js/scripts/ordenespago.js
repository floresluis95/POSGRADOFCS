/**
 * Órdenes de pago (personal: ADM, SEC, AUX): todas las órdenes ya generadas
 * (matrícula, cuotas, clínica y módulos), con filtros y botón para volver a
 * imprimir. Sirve también de control de lo que ya se emitió y quién lo generó.
 *
 * El rango de fechas se pide al servidor; búsqueda, tipo, estado y programa se
 * filtran aquí sobre lo cargado, sin volver a pedirlo.
 *
 * Desde la barra lateral la tabla empieza vacía, esperando que se busque un
 * estudiante. Desde Matriculados (?ci=...&programa=...) se muestran directo las
 * órdenes de ese registro: ese C.I. exacto en ese programa.
 */
(function () {
    var ETIQUETAS_TIPO = { MATRICULA: 'Matrícula', CUOTA: 'Cuota', CLINICA: 'Clínica', MODULOS: 'Módulos' };
    var POR_PAGINA = 50;

    var todas = [];
    // Con "Buscar" (o al elegir un filtro) se lista aunque no se haya escrito un
    // estudiante: p. ej. todas las matrículas de un programa, solo pagadas o solo clínicas
    var buscarPorFiltros = false;
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
        // Pagada, pero el estudiante perdió el voucher: falta registrarlo
        if (o.estado === 'EXTRAVIADO') return 'EXTRAVIADO';
        if (o.vencida) return 'VENCIDA';
        return 'PENDIENTE';
    }

    var INSIGNIAS = {
        PAGADA: '<span class="op-estado op-estado-pagada">Pagada</span>',
        ANULADO: '<span class="op-estado op-estado-anulada">Anulada</span>',
        VENCIDA: '<span class="op-estado op-estado-vencida">Vencida</span>',
        PENDIENTE: '<span class="op-estado op-estado-pendiente">Pendiente</span>',
        EXTRAVIADO: '<span class="op-estado op-estado-extraviado">Voucher por validar</span>'
    };

    // "Cuota 3 de 10" (o "Cuota 3" sin plan); el resto, su etiqueta
    function textoTipo(o) {
        if (o.tipo === 'CUOTA' && o.numeroCuota) {
            return 'Cuota ' + o.numeroCuota + (o.cuotasPlan ? ' de ' + o.cuotasPlan : '');
        }
        if (o.tipo === 'MATRICULA' && o.pagoCompleto) return 'Matrícula · Programa completo';
        return ETIQUETAS_TIPO[o.tipo] || o.tipo;
    }

    function fila(o, numero) {
        var estado = estadoDe(o);
        // Cada tipo con su color: matrícula, cuota, clínica, módulos
        var tipo = '<span class="op-tipo op-tipo-' + String(o.tipo || '').toLowerCase() + '">' + esc(ETIQUETAS_TIPO[o.tipo] || o.tipo) + '</span>';
        if (o.tipo === 'CUOTA' && o.numeroCuota) {
            tipo += '<span class="op-sub">N° ' + o.numeroCuota + (o.cuotasPlan ? ' de ' + o.cuotasPlan : '') + '</span>';
        }
        if (o.tipo === 'MATRICULA' && o.pagoCompleto) tipo += '<span class="op-sub">Programa completo</span>';

        // Una orden anulada no sirve para pagar: no se ofrece imprimirla
        var accion = estado === 'ANULADO'
            ? '<span class="op-sub">Sin validez</span>'
            : '<a href="vistas/componentes/orden-pago-pdf.php?idordenpago=' + o.idOrdenPago + '" target="_blank" class="op-imprimir"' +
              (estado === 'PAGADA' ? ' data-pagada="1" data-numero="' + esc(o.numeroOrden || '') + '"' : '') + '>' +
              '<i class="fa fa-print"></i> Imprimir</a>';
        // Validar el voucher de una orden pendiente: la matrícula en Inscripción (como
        // desde Pre-Registro); cuota y clínica aquí mismo, en la ventana "Validar voucher"
        var validar = '';
        var cuotaOClinica = (o.tipo === 'CUOTA' || o.tipo === 'CLINICA') && o.idInscripcion;
        if (o.estado === 'PENDIENTE') {
            if (o.tipo === 'MATRICULA') {
                validar = '<a href="inscripcion?orden=' + o.idOrdenPago + '" class="op-validar" title="Validar el voucher en Inscripción">' +
                    '<i class="fa fa-check-circle"></i> Validar</a>';
            } else if (cuotaOClinica) {
                validar = '<button type="button" class="op-validar op-btn-validar" data-id="' + o.idOrdenPago + '" title="Registrar el voucher de esta orden">' +
                    '<i class="fa fa-check-circle"></i> Validar</button>' +
                    // El estudiante pagó pero perdió el voucher: se aparta para generar la siguiente
                    '<button type="button" class="op-extraviado op-btn-extraviado" data-id="' + o.idOrdenPago + '" title="El estudiante pagó pero falta el código del voucher">' +
                    '<i class="fa fa-search-dollar"></i> Voucher por validar</button>';
            }
        } else if (o.estado === 'EXTRAVIADO' && cuotaOClinica) {
            // Cuando Tesoro da el código, se registra el voucher de esta misma orden
            validar = '<button type="button" class="op-validar op-btn-validar" data-id="' + o.idOrdenPago + '" title="Registrar el voucher recuperado">' +
                '<i class="fa fa-check-circle"></i> Registrar voucher</button>';
        }

        // Ir a Matriculados filtrado por el C.I. del estudiante (matriculados?buscar=CI)
        accion = '<div class="op-acciones">' + validar + accion +
            '<a href="matriculados?buscar=' + encodeURIComponent(o.ci || '') + '" class="op-ir-matriculado" title="Ver en Matriculados">' +
            '<i class="fa fa-user-graduate"></i></a></div>';

        return '<tr' + (estado === 'ANULADO' ? ' class="op-anulada"' : '') + '>' +
            '<td data-label="N°" class="op-correlativo">' + numero + '</td>' +
            '<td data-label="N° Orden"><span class="op-numero">' + esc(o.numeroOrden || '-') + '</span></td>' +
            '<td data-label="Estudiante"><span>' + esc(o.estudiante) + '<span class="op-sub">C.I. ' + esc(o.ci) + '</span></span></td>' +
            '<td data-label="Programa"><span>' + esc(o.nombrePrograma) + '<span class="op-sub">' + esc(o.gradoAcademico) + ' · ' + esc(o.sede) + '</span></span></td>' +
            '<td data-label="Tipo"><span>' + tipo + '</span></td>' +
            '<td data-label="Monto" class="text-right"><span class="op-monto">' + formatoMoneda(o.monto) + '</span></td>' +
            '<td data-label="Generada"><span><span class="op-fecha">' + formatoFecha(o.fechaGeneracion) + '</span>' +
                (o.responsable ? '<span class="op-sub">por ' + esc(o.responsable) + '</span>' : '') + '</span></td>' +
            '<td data-label="Estado" class="text-center">' + INSIGNIAS[estado] +
                (estado === 'EXTRAVIADO' && o.motivoExtraviado
                    ? '<span class="op-sub op-motivo" title="' + esc(o.motivoExtraviado) + '">' + esc(o.motivoExtraviado) + '</span>'
                    : '') + '</td>' +
            '<td class="text-center op-celda-accion">' + accion + '</td>' +
        '</tr>';
    }

    function normalizar(texto) {
        return String(texto || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
    }

    // Tipo, estado y programa elegidos (sin el texto de búsqueda)
    function cumpleFiltros(o) {
        var tipo = $('#op_tipo').val();
        var estado = $('#op_estado').val();
        var programa = $('#op_programa').val();
        if (tipo && o.tipo !== tipo) return false;
        if (estado && estadoDe(o) !== estado) return false;
        if (programa && String(o.programaID) !== programa) return false;
        return true;
    }

    // ========================================
    // EXPORTAR A EXCEL: lo que se ve en la tabla (con búsqueda) o, sin búsqueda, todas
    // las órdenes del periodo que cumplen los filtros de tipo, estado y programa
    // ========================================
    var ETIQUETAS_ESTADO = { PAGADA: 'Pagada', ANULADO: 'Anulada', VENCIDA: 'Vencida', PENDIENTE: 'Pendiente', EXTRAVIADO: 'Voucher por validar' };

    function exportarExcel() {
        var buscar = $('#op_buscar').val().trim();
        var lista = (buscar || buscarPorFiltros) ? filtradas : todas.filter(cumpleFiltros);
        if (!lista.length) {
            Swal.fire({ type: 'info', title: 'Sin órdenes', text: 'No hay órdenes para exportar con estos filtros' });
            return;
        }
        var filtrosTexto = [];
        if (buscar) filtrosTexto.push('Búsqueda: ' + buscar);
        ['#op_tipo', '#op_estado', '#op_programa'].forEach(function (sel) {
            if ($(sel).val()) filtrosTexto.push($('label[for="' + sel.slice(1) + '"]').text() + ': ' + $(sel + ' option:selected').text());
        });
        var desde = $('#op_desde').val();
        var hasta = $('#op_hasta').val();
        if (desde || hasta) filtrosTexto.push('Generadas ' + (desde ? 'desde ' + formatoFecha(desde) : '') + (desde && hasta ? ' ' : '') + (hasta ? 'hasta ' + formatoFecha(hasta) : ''));

        var total = 0;
        var filas = lista.map(function (o, i) {
            if (estadoDe(o) !== 'ANULADO') total += parseFloat(o.monto) || 0;
            return [
                i + 1, o.numeroOrden || '', formatoFecha(o.fechaGeneracion), o.estudiante || '', o.ci || '',
                o.nombrePrograma || '', o.sede || '', textoTipo(o), parseFloat(o.monto) || 0,
                ETIQUETAS_ESTADO[estadoDe(o)] || estadoDe(o), o.fechaVencimiento ? formatoFecha(o.fechaVencimiento) : '',
                o.responsable || ''
            ];
        });
        var hoy = new Date();
        var dos = function (n) { return String(n).padStart(2, '0'); };
        window.exportarExcel({
            archivo: 'ordenes-de-pago-' + hoy.getFullYear() + dos(hoy.getMonth() + 1) + dos(hoy.getDate()),
            hoja: 'Órdenes de pago',
            titulo: 'ÓRDENES DE PAGO · POSGRADO FCS',
            subtitulo: (filtrosTexto.length ? filtrosTexto.join(' · ') + ' · ' : '') + lista.length + ' orden(es) · Exportado el ' +
                dos(hoy.getDate()) + '/' + dos(hoy.getMonth() + 1) + '/' + hoy.getFullYear(),
            columnas: [
                { titulo: 'N°', ancho: 6, tipo: 'numero' },
                { titulo: 'N° Orden', ancho: 30 },
                { titulo: 'Generada', ancho: 12 },
                { titulo: 'Estudiante', ancho: 34 },
                { titulo: 'C.I.', ancho: 14 },
                { titulo: 'Programa', ancho: 42 },
                { titulo: 'Sede', ancho: 14 },
                { titulo: 'Tipo', ancho: 20 },
                { titulo: 'Monto (Bs.)', ancho: 14, tipo: 'moneda' },
                { titulo: 'Estado', ancho: 18 },
                { titulo: 'Fecha límite', ancho: 13 },
                { titulo: 'Generada por', ancho: 26 }
            ],
            filas: filas,
            totales: ['', 'Total (sin anuladas)', '', '', '', '', '', '', total, '', '', '']
        });
    }

    function aplicarFiltros() {
        var textoBuscar = $('#op_buscar').val().trim();
        var buscar = normalizar(textoBuscar);
        // Mientras siga el C.I. que vino de Matriculados, solo ese C.I. exacto
        // (no otros que lo contengan)
        var ciExacto = ciInicial && textoBuscar === ciInicial ? ciInicial : '';

        // Sin texto ni "Buscar" no se lista nada: se espera a que se busque un estudiante
        // o se elijan filtros. Con texto, además se filtra por estudiante/C.I./N° de orden.
        filtradas = (!buscar && !buscarPorFiltros) ? [] : todas.filter(function (o) {
            if (!cumpleFiltros(o)) return false;
            if (!buscar) return true;
            if (ciExacto) return o.ciBase === ciExacto;
            var texto = normalizar(o.estudiante + ' ' + o.ci + ' ' + o.numeroOrden);
            return texto.indexOf(buscar) !== -1;
        });

        actualizarContadores();
        mostradas = 0;
        $('#op_cuerpo').empty();
        mostrarMas();
    }

    function actualizarContadores() {
        var c = { PENDIENTE: 0, VENCIDA: 0, PAGADA: 0, ANULADO: 0, EXTRAVIADO: 0 };
        filtradas.forEach(function (o) { c[estadoDe(o)]++; });
        $('#op_total').text(filtradas.length);
        $('#op_pendientes').text(c.PENDIENTE);
        $('#op_vencidas').text(c.VENCIDA);
        $('#op_pagadas').text(c.PAGADA);
        $('#op_anuladas').text(c.ANULADO);
        $('#op_extraviadas').text(c.EXTRAVIADO);
    }

    function mostrarMas() {
        if (!filtradas.length) {
            var mensaje = (!$('#op_buscar').val().trim() && !buscarPorFiltros)
                ? '<i class="fa fa-search"></i> Busque un estudiante por nombre, C.I. o N° de orden, o elija los filtros (tipo, estado, programa, fechas) y pulse <strong>Buscar</strong>.'
                : (todas.length ? 'Ninguna orden coincide con los filtros.' : 'Todavía no hay órdenes de pago generadas en este periodo.');
            $('#op_cuerpo').html('<tr><td colspan="9" class="op-vacio">' + mensaje + '</td></tr>');
            $('#op_mostrando').text('');
            $('#op_mas').hide();
            return;
        }

        var siguiente = filtradas.slice(mostradas, mostradas + POR_PAGINA);
        var desde = mostradas;
        $('#op_cuerpo').append(siguiente.map(function (o, i) { return fila(o, desde + i + 1); }).join(''));
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
        $('#op_cuerpo').html('<tr><td colspan="9" class="op-vacio">Cargando órdenes de pago...</td></tr>');
        $('#op_mas').hide();

        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            method: 'POST',
            dataType: 'json',
            data: { accion: 'listarOrdenesGeneradas', desde: $('#op_desde').val(), hasta: $('#op_hasta').val() },
            success: function (resp) {
                if (!resp || !resp.success) {
                    $('#op_cuerpo').html('<tr><td colspan="9" class="op-vacio">No se pudieron cargar las órdenes de pago.</td></tr>');
                    return;
                }
                todas = resp.ordenes || [];
                llenarProgramas();
                // Desde Matriculados se llega con ?programa=ID: se elige una sola vez,
                // cuando el select ya tiene los programas
                if (programaInicial) {
                    if ($('#op_programa option[value="' + programaInicial + '"]').length) {
                        $('#op_programa').val(programaInicial);
                        buscarPorFiltros = true; // ya lista las órdenes de ese programa
                    }
                    programaInicial = '';
                }
                aplicarFiltros();
            },
            error: function () {
                $('#op_cuerpo').html('<tr><td colspan="9" class="op-vacio">No se pudieron cargar las órdenes de pago.</td></tr>');
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

        // Antes de guardar: lo que se va a registrar, para revisarlo
        var fila = function (etiqueta, valor) {
            return '<tr><th style="padding:6px 10px 6px 0;color:#6b7280;font-weight:500;white-space:nowrap;">' + etiqueta + '</th>' +
                '<td style="padding:6px 0;border-bottom:1px solid #edf0f4;">' + valor + '</td></tr>';
        };
        var $foto = $('#opv_preview img');
        Swal.fire({
            type: 'question', // SweetAlert2 v8
            title: 'Revise el pago',
            html: '<table style="width:100%;text-align:left;font-size:14px;border-collapse:collapse;">' +
                    fila('Orden', esc(o.numeroOrden || '-') + ' · ' + esc(ETIQUETAS_TIPO[o.tipo] || o.tipo)) +
                    fila('Estudiante', esc(o.estudiante)) +
                    fila('Monto', '<strong>' + formatoMoneda(o.tipo === 'CUOTA' ? o.montoTotal : o.monto) + '</strong>') +
                    fila('N° de voucher', '<strong style="font-size:16px;">' + esc(numero) + '</strong>') +
                    fila('Fecha de pago', esc(fecha.split('-').reverse().join('/'))) +
                  '</table>' +
                  ($foto.length ? '<img src="' + $foto.attr('src') + '" alt="Voucher" style="display:block;max-width:100%;max-height:200px;margin:12px auto 0;border:1px solid #e3e7ee;border-radius:6px;">' : ''),
            showCancelButton: true,
            confirmButtonText: 'Sí, validar',
            cancelButtonText: 'Revisar',
            reverseButtons: true
        }).then(function (resultado) {
            if (resultado.value) enviarValidacion(o, fecha, numero, archivo);
        });
    }

    function enviarValidacion(o, fecha, numero, archivo) {
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
            datos.append('idOrdenPago', o.idOrdenPago);
        } else {
            url = 'ajax/pagoclinica.ajax.php';
            datos.append('accion', 'registrarPago');
            datos.append('numeroVoucher', numero);
            datos.append('fotoVoucher', archivo);
            datos.append('idOrdenPago', o.idOrdenPago);
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

    // ========================================
    // VOUCHER EXTRAVIADO: el estudiante pagó pero perdió el voucher. La orden se aparta
    // con su motivo (obligatorio) y se puede generar la siguiente; el voucher se registra
    // después en esta misma orden ("Registrar voucher").
    // ========================================
    function marcarExtraviado(idOrden) {
        var o = todas.filter(function (x) { return x.idOrdenPago === idOrden; })[0];
        if (!o) return;
        Swal.fire({
            type: 'question', // SweetAlert2 v8
            title: 'Marcar voucher por validar',
            html: '<div style="text-align:left;font-size:14px;">' +
                    '<div><strong>' + esc(o.numeroOrden || '-') + '</strong> · ' + esc(ETIQUETAS_TIPO[o.tipo] || o.tipo) + ' · ' + formatoMoneda(o.monto) + '</div>' +
                    '<div style="color:#6b7280;">' + esc(o.estudiante) + ' · C.I. ' + esc(o.ci) + '</div>' +
                    '<div style="margin-top:10px;font-size:13px;color:#1f2937;background:#eef2f9;border-left:3px solid #1e3c72;border-radius:4px;padding:8px 10px;">' +
                        'La orden quedará registrada como <strong>voucher por validar</strong>. El monto no se considera cobrado hasta ' +
                        'registrar el voucher, pero no se vuelve a cobrar y se habilita la orden siguiente.' +
                    '</div>' +
                  '</div>',
            input: 'textarea',
            inputPlaceholder: 'Motivo (obligatorio). Ej.: Pagó en el banco pero no tiene el voucher; se solicitó el código a Tesorería',
            inputAttributes: { maxlength: 255 },
            showCancelButton: true,
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            inputValidator: function (valor) {
                if (!valor || valor.trim().length < 5) return 'Escriba el motivo';
            }
        }).then(function (resultado) {
            if (!resultado.value) return;
            $.ajax({
                url: 'ajax/ordenpago.ajax.php',
                method: 'POST',
                dataType: 'json',
                data: { accion: 'marcarVoucherExtraviado', idOrdenPago: idOrden, motivo: resultado.value.trim() },
                success: function (resp) {
                    Swal.fire({ type: resp && resp.success ? 'success' : 'warning', title: resp && resp.success ? 'Listo' : 'Atención',
                                text: (resp && resp.mensaje) || 'No se pudo marcar la orden', timer: resp && resp.success ? 2200 : undefined,
                                showConfirmButton: !(resp && resp.success) });
                    if (resp && resp.success) cargar();
                },
                error: function () {
                    Swal.fire({ type: 'error', title: 'Error', text: 'No se pudo marcar la orden' });
                }
            });
        });
    }

    // Filtro inicial desde la URL (botón "Órdenes de Pago" de Matriculados)
    var parametros = new URLSearchParams(window.location.search);
    var programaInicial = /^\d+$/.test(parametros.get('programa') || '') ? parametros.get('programa') : '';
    var ciInicial = (parametros.get('ci') || '').trim();

    function iniciar() {
        actualizarFecha();
        if (ciInicial) $('#op_buscar').val(ciInicial);
        else $('#op_buscar').trigger('focus');

        var temporizador = null;
        $('#op_buscar').on('input', function () {
            clearTimeout(temporizador);
            temporizador = setTimeout(aplicarFiltros, 200);
        });
        // Elegir un filtro ya lista con él (no hace falta escribir un estudiante)
        $('#op_tipo, #op_estado, #op_programa').on('change', function () {
            buscarPorFiltros = true;
            aplicarFiltros();
        });
        $('#op_btn_buscar').on('click', function () {
            buscarPorFiltros = true;
            aplicarFiltros();
        });
        $('#op_desde, #op_hasta').on('change', function () {
            buscarPorFiltros = true;
            cargar();
        });
        $('#op_mas').on('click', mostrarMas);
        $('#op_exportar').on('click', exportarExcel);

        $('#op_cuerpo').on('click', '.op-btn-validar', function () { abrirValidar($(this).data('id')); });
        $('#op_cuerpo').on('click', '.op-btn-extraviado', function () { marcarExtraviado($(this).data('id')); });
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
            buscarPorFiltros = false;
            $('#op_buscar').val('');
            $('#op_tipo, #op_estado, #op_programa').val('');
            var recargar = $('#op_desde').val() || $('#op_hasta').val();
            $('#op_desde, #op_hasta').val('');
            if (recargar) cargar(); else aplicarFiltros();
            $('#op_buscar').trigger('focus');
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
