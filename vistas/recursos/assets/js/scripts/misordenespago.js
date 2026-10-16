/**
 * Mis Órdenes de Pago (Estudiante): lo que tiene que pagar. Arriba, las órdenes por
 * pagar (pendientes, vencidas o con voucher por validar) con su monto y fecha límite;
 * abajo, todas sus órdenes con el botón para imprimir cada una. Lo ya pagado, sus
 * comprobantes y el recibo de pagos están en Historial de pagos.
 * Filtros por Programa y por Concepto (solo los conceptos que el estudiante tiene).
 * Las órdenes ANULADAS no se muestran en la tabla principal: quedan en un modal
 * aparte ("Anuladas"), solo como referencia.
 */
(function () {
    // MODULOS: órdenes del sistema anterior (cobro por módulo); hoy ya no se generan
    var ETIQUETAS_TIPO = { MATRICULA: 'Matrícula', CUOTA: 'Cuota del programa', CLINICA: 'Clínica', MODULOS: 'Módulos (sistema anterior)' };

    // Todas las órdenes cargadas del servidor (sin filtrar, incluye anuladas); los
    // selects de Programa y Concepto filtran sobre esta lista, sin volver a pedirlas.
    var todasLasOrdenes = [];

    function escapar(texto) {
        var div = document.createElement('div');
        div.textContent = texto == null ? '' : String(texto);
        return div.innerHTML.replace(/"/g, '&quot;');
    }

    function formatoFecha(iso) {
        if (!iso) return '-';
        var p = String(iso).match(/^(\d{4})-(\d{2})-(\d{2})/);
        return p ? p[3] + '/' + p[2] + '/' + p[1] : '-';
    }

    function formatoMoneda(valor) {
        var n = parseFloat(valor);
        if (isNaN(n)) return '-';
        return 'Bs. ' + n.toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Matrícula al Contado sin matrícula: la orden es por el programa completo
    function concepto(o) {
        if (o.tipo === 'MATRICULA' && o.pagoCompleto) return 'Programa completo';
        return ETIQUETAS_TIPO[o.tipo] || o.tipo;
    }

    function badgeEstado(o) {
        if (o.estado === 'CONFIRMADO') return '<span class="badge-orden badge-confirmado">Pagada</span>';
        // Pagó pero perdió el voucher: Posgrado lo registra cuando Tesoro dé el código
        if (o.estado === 'EXTRAVIADO') return '<span class="badge-orden badge-extraviado" title="Pagada; falta validar el voucher">Voucher por validar</span>';
        if (o.vencida) return '<span class="badge-orden badge-vencido">Vencida</span>';
        return '<span class="badge-orden badge-pendiente">Pendiente de pago</span>';
    }

    function actualizarFecha() {
        var opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        var fecha = new Date().toLocaleDateString('es-ES', opciones);
        var el = document.getElementById('lafecha');
        if (el) el.textContent = fecha.charAt(0).toUpperCase() + fecha.slice(1);
    }

    // Programas y conceptos distintos entre las órdenes cargadas (sin anuladas)
    function llenarFiltros(ordenes) {
        var programas = {};
        var tipos = {};
        ordenes.forEach(function (o) {
            programas[o.programaID] = o.nombrePrograma;
            tipos[o.tipo] = true;
        });

        var $programa = $('#filtro_programa');
        $programa.find('option:not(:first)').remove();
        Object.keys(programas).forEach(function (id) {
            $programa.append($('<option></option>').val(id).text(programas[id]));
        });

        var $tipo = $('#filtro_tipo');
        $tipo.find('option:not(:first)').remove();
        Object.keys(ETIQUETAS_TIPO).forEach(function (tipo) {
            if (tipos[tipo]) $tipo.append($('<option></option>').val(tipo).text(ETIQUETAS_TIPO[tipo]));
        });
    }

    // Por pagar: una tarjeta por orden pendiente, vencida o con voucher por validar
    function renderPorPagar(ordenes) {
        var lista = ordenes.filter(function (o) { return o.estado === 'PENDIENTE' || o.estado === 'VENCIDO' || o.estado === 'EXTRAVIADO'; });
        if (!lista.length) {
            $('#por_pagar').hide();
            return;
        }
        $('#lista_por_pagar').html(lista.map(function (o) {
            var clase = o.estado === 'EXTRAVIADO' ? ' is-espera' : (o.vencida ? ' is-vencida' : '');
            var nota = o.estado === 'EXTRAVIADO'
                ? 'Ya pagada: Posgrado registrará su voucher'
                : (o.fechaVencimiento ? (o.vencida ? 'Venció el ' : 'Pagar hasta el ') + formatoFecha(o.fechaVencimiento) : 'Sin fecha límite');
            return '<div class="mop-pp' + clase + '">' +
                '<div class="mop-pp-cab"><span class="mop-pp-concepto">' + escapar(concepto(o)) + '</span>' + badgeEstado(o) + '</div>' +
                '<span class="mop-pp-monto">' + formatoMoneda(o.monto) + '</span>' +
                '<small>' + escapar(o.nombrePrograma) + '</small>' +
                '<small>' + escapar(nota) + ' · Orden ' + escapar(o.numeroOrden || '-') + '</small>' +
                (o.estado === 'EXTRAVIADO' ? '' : '<a href="vistas/componentes/orden-pago-pdf.php?idordenpago=' + o.idOrdenPago + '" target="_blank" class="btn-imprimir-orden"><i class="fa fa-print"></i> Imprimir orden</a>') +
            '</div>';
        }).join(''));
        $('#por_pagar').show();
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
                '<td data-label="N° de orden"><strong>' + escapar(o.numeroOrden || '-') + '</strong></td>' +
                '<td data-label="Programa"><span>' + escapar(o.nombrePrograma) + '<br><small>' + escapar(o.gradoAcademico) + ' · ' + escapar(o.sede) + '</small></span></td>' +
                '<td data-label="Concepto">' + escapar(concepto(o)) + '</td>' +
                '<td data-label="Monto" class="text-right">' + formatoMoneda(o.monto) + '</td>' +
                '<td data-label="Generada">' + formatoFecha(o.fechaGeneracion) + '</td>' +
                '<td data-label="Fecha límite">' + (o.fechaVencimiento ? formatoFecha(o.fechaVencimiento) : '-') + '</td>' +
                '<td data-label="Estado" class="text-center">' + badgeEstado(o) + '</td>' +
                '<td class="text-center celda-accion"><span class="mop-acciones">' +
                    '<a href="vistas/componentes/orden-pago-pdf.php?idordenpago=' + o.idOrdenPago + '" ' +
                    'target="_blank" class="btn-imprimir-orden"' +
                    // Pagada: antes de imprimir se avisa que ya se pagó (se puede imprimir igual)
                    (o.estado === 'CONFIRMADO' ? ' data-pagada="1" data-fecha-pago="' + escapar(o.fechaConfirmacion || '') + '" data-orden="' + escapar(o.numeroOrden || '') + '"' : '') +
                    '><i class="fa fa-print"></i> Orden</a>' +
                '</span></td>' +
                '</tr>';
            $tbody.append(fila);
        });
    }

    // Tabla del modal "Anuladas": sin acciones (no tienen validez para el pago)
    function renderTablaAnuladas(ordenes) {
        var $tbody = $('#contenido_ordenes_anuladas');
        $tbody.empty();

        if (!ordenes.length) {
            $tbody.html('<tr><td colspan="5" class="text-center text-muted py-3">Sin órdenes anuladas.</td></tr>');
            return;
        }

        ordenes.forEach(function (o) {
            $tbody.append('<tr>' +
                '<td>' + escapar(o.numeroOrden || '-') + '</td>' +
                '<td>' + escapar(o.nombrePrograma) + '</td>' +
                '<td>' + escapar(concepto(o)) + '</td>' +
                '<td class="text-right">' + formatoMoneda(o.monto) + '</td>' +
                '<td>' + formatoFecha(o.fechaGeneracion) + '</td>' +
                '</tr>');
        });
    }

    function aplicarFiltros() {
        var programaID = $('#filtro_programa').val();
        var tipo = $('#filtro_tipo').val();

        renderTabla(todasLasOrdenes.filter(function (o) {
            if (o.estado === 'ANULADO') return false;
            if (programaID && String(o.programaID) !== String(programaID)) return false;
            if (tipo && o.tipo !== tipo) return false;
            return true;
        }));
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
                var vigentes = todasLasOrdenes.filter(function (o) { return o.estado !== 'ANULADO'; });
                var anuladas = todasLasOrdenes.filter(function (o) { return o.estado === 'ANULADO'; });

                llenarFiltros(vigentes);
                renderPorPagar(vigentes);
                aplicarFiltros();
                renderTablaAnuladas(anuladas);

                if (anuladas.length) {
                    $('#conteo_anuladas').text(anuladas.length);
                    $('#btn_ver_anuladas').show();
                }

                $('#ordenes_filtros').show();
                $('#titulo_todas').show();
                $('#ordenes_tabla_wrap').show();
            },
            error: function () {
                $('#ordenes_cargando').hide();
                Swal.fire({ type: 'error', title: 'Error', text: 'No se pudieron cargar sus órdenes de pago' });
            }
        });
    }

    // ========================================
    // SIGUIENTE CUOTA: el estudiante genera su orden. El sistema calcula todo (monto
    // del plan, N° de cuota, cuenta, fecha límite); solo aparece si tiene plan, saldo
    // y ninguna orden de cuota por pagar.
    // ========================================
    function etiquetaCuota(c) {
        return 'Cuota ' + c.numeroCuota + (c.totalCuotas ? ' de ' + c.totalCuotas : '');
    }

    function cargarSiguientesCuotas() {
        $.post('ajax/pagomodulo.ajax.php', { accion: 'obtenerProgramasEstudiante' }, function (resp) {
            var programas = (resp && resp.success && resp.programas) || [];
            var $lista = $('#lista_generar_cuota').empty();
            var pendientes = programas.length;
            if (!pendientes) return;
            programas.forEach(function (p) {
                $.post('ajax/ordenpago.ajax.php', { accion: 'calcularMiCuota', idinscripcion: p.idInscripcion }, function (r) {
                    if (r && r.success && r.cuota) {
                        var c = r.cuota;
                        $lista.append('<div class="mop-pp is-nueva">' +
                            '<div class="mop-pp-cab"><span class="mop-pp-concepto">' + escapar(etiquetaCuota(c)) + '</span></div>' +
                            '<span class="mop-pp-monto">' + formatoMoneda(c.monto) + '</span>' +
                            '<small>' + escapar(c.programa) + '</small>' +
                            '<small>Saldo del programa: ' + formatoMoneda(c.saldo) + '</small>' +
                            '<button type="button" class="btn-generar-cuota" data-inscripcion="' + p.idInscripcion + '" ' +
                                'data-cuota="' + escapar(JSON.stringify(c)) + '"><i class="fa fa-file-invoice"></i> Generar orden de pago</button>' +
                        '</div>');
                        $('#generar_cuota').show();
                    }
                }, 'json');
            });
        }, 'json');
    }

    function generarCuota() {
        var $boton = $(this);
        var c = $boton.data('cuota');
        var fila = function (etiqueta, valor) {
            return '<tr><th style="text-align:left;padding:5px 10px;color:#64748b;font-weight:600;">' + etiqueta + '</th>' +
                '<td style="text-align:left;padding:5px 10px;">' + valor + '</td></tr>';
        };
        Swal.fire({
            type: 'question', // SweetAlert2 v8
            title: 'Generar orden de pago',
            html: '<table style="width:100%;font-size:14px;border-collapse:collapse;">' +
                (c.maxCantidad > 1 ? fila('Cuotas a pagar', '<select id="cantidad_cuotas" class="form-control form-control-sm" style="max-width:120px;">' +
                    Array.from({ length: c.maxCantidad }, function (_, i) { return '<option value="' + (i + 1) + '">' + (i + 1) + '</option>'; }).join('') +
                    '</select><small style="color:#64748b;">Se genera una orden por cada cuota</small>') : '') +
                fila('Concepto', escapar(etiquetaCuota(c))) +
                fila('Monto', '<strong style="font-size:18px;color:#1e3c72;">' + formatoMoneda(c.monto) + '</strong>') +
                fila('Programa', escapar(c.programa)) +
                fila('Pagar hasta', formatoFecha(c.fechaVencimiento)) +
                '</table>' +
                '<p style="margin:12px 0 0;font-size:13px;color:#475569;">Imprima la orden y preséntela en <strong>Tesoro Universitario</strong>.</p>',
            showCancelButton: true,
            confirmButtonText: 'Sí, generar',
            // La cantidad se lee antes de que se cierre la ventana (luego ya no existe)
            preConfirm: function () { return $('#cantidad_cuotas').val() || 1; },
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then(function (r) {
            if (!r.value) return;
            $boton.prop('disabled', true);
            $.post('ajax/ordenpago.ajax.php', { accion: 'generarMiCuota', idinscripcion: $boton.data('inscripcion'), cantidad: r.value }, function (resp) {
                if (!resp || !resp.success) {
                    $boton.prop('disabled', false);
                    Swal.fire({ type: 'warning', title: 'No se generó la orden', text: (resp && resp.mensaje) || 'Intente de nuevo' });
                    return;
                }
                var ordenes = resp.ordenes || [{ idOrdenPago: resp.idOrdenPago, numeroOrden: resp.numeroOrden }];
                Swal.fire({
                    type: 'success',
                    title: resp.mensaje || 'Orden generada',
                    html: ordenes.length === 1
                        ? 'N° <strong>' + escapar(resp.numeroOrden) + '</strong>. Imprímala y preséntela en Tesoro Universitario.'
                        : 'Imprima cada orden y preséntelas en Tesoro Universitario:<br>' + ordenes.map(function (o) {
                            return '<a href="vistas/componentes/orden-pago-pdf.php?idordenpago=' + o.idOrdenPago + '" target="_blank">Cuota ' + o.numeroCuota + ' · ' + formatoMoneda(o.monto) + '</a>';
                        }).join('<br>'),
                    confirmButtonText: '<i class="fa fa-print"></i> Imprimir orden',
                    showCancelButton: true,
                    cancelButtonText: 'Después'
                }).then(function (r2) {
                    if (r2.value) window.open('vistas/componentes/orden-pago-pdf.php?idordenpago=' + resp.idOrdenPago, '_blank');
                });
                $('#generar_cuota').hide();
                cargarOrdenes();
                cargarSiguientesCuotas();
            }, 'json').fail(function () {
                $boton.prop('disabled', false);
                Swal.fire({ type: 'error', title: 'Error', text: 'No se pudo conectar con el servidor' });
            });
        });
    }

    // Orden ya pagada: se avisa antes de imprimirla, para que no se vuelva a pagar;
    // igual se puede imprimir (p. ej. como respaldo del trámite)
    function avisarOrdenPagada(e) {
        e.preventDefault();
        var $enlace = $(this);
        var fecha = $enlace.data('fecha-pago') ? formatoFecha(String($enlace.data('fecha-pago'))) : '';
        Swal.fire({
            type: 'info', // SweetAlert2 v8
            title: 'Esta orden ya fue pagada',
            html: 'La orden <strong>' + escapar($enlace.data('orden')) + '</strong> ya está pagada' +
                (fecha ? ' (registrada el ' + fecha + ')' : '') + '.<br>No debe volver a pagarla.',
            showCancelButton: true,
            confirmButtonText: '<i class="fa fa-print"></i> Imprimir de todas formas',
            cancelButtonText: 'Cerrar',
            reverseButtons: true
        }).then(function (r) {
            if (r.value) window.open($enlace.attr('href'), '_blank');
        });
    }

    function iniciar() {
        actualizarFecha();

        // Los eventos se enlazan aquí (en 'load'), cuando jQuery ya está cargado
        if (typeof $ !== 'undefined') {
            $(document).on('change', '#filtro_programa, #filtro_tipo', aplicarFiltros);
            $(document).on('click', '.btn-imprimir-orden[data-pagada]', avisarOrdenPagada);
            $(document).on('click', '.btn-generar-cuota', generarCuota);
        }

        cargarOrdenes();
        cargarSiguientesCuotas();
    }

    if (document.readyState === 'complete') {
        iniciar();
    } else {
        window.addEventListener('load', iniciar);
    }
})();
