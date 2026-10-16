/**
 * Mis Órdenes de Pago (Estudiante): lo que tiene que pagar. Arriba, las órdenes por
 * pagar (pendientes, vencidas o con voucher por validar) con su monto y fecha límite;
 * luego la matrícula, aparte; abajo, la tabla de cuotas y clínicas numerada, con el
 * botón para imprimir cada una. Lo ya pagado, sus
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
    // Cuota con su número: "Cuota 3 de 10"
    function concepto(o) {
        if (o.tipo === 'MATRICULA' && o.pagoCompleto) return 'Programa completo';
        if (o.tipo === 'CUOTA' && o.numeroCuota) return 'Cuota ' + o.numeroCuota + (o.cuotasPlan ? ' de ' + o.cuotasPlan : '');
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
            if (o.tipo !== 'MATRICULA') tipos[o.tipo] = true; // la matrícula va en su sección
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

    // Botón para imprimir la orden; si ya está pagada, avisa antes (se imprime igual)
    function botonImprimir(o, texto) {
        return '<a href="vistas/componentes/orden-pago-pdf.php?idordenpago=' + o.idOrdenPago + '" target="_blank" class="btn-imprimir-orden"' +
            (o.estado === 'CONFIRMADO' ? ' data-pagada="1" data-fecha-pago="' + escapar(o.fechaConfirmacion || '') + '" data-orden="' + escapar(o.numeroOrden || '') + '"' : '') +
            '><i class="fa fa-print"></i> ' + texto + '</a>';
    }

    // Fila de una orden; con número cuando va en la tabla numerada (cuotas y clínicas)
    function filaOrden(o, numero) {
        return '<tr>' +
            (numero != null ? '<td data-label="N°" class="text-center mop-numero">' + numero + '</td>' : '') +
            '<td data-label="N° de orden"><strong>' + escapar(o.numeroOrden || '-') + '</strong></td>' +
            '<td data-label="Programa"><span>' + escapar(o.nombrePrograma) + '<br><small>' + escapar(o.gradoAcademico) + ' · ' + escapar(o.sede) + '</small></span></td>' +
            '<td data-label="Concepto">' + escapar(concepto(o)) + '</td>' +
            '<td data-label="Monto" class="text-right">' + formatoMoneda(o.monto) + '</td>' +
            '<td data-label="Generada">' + formatoFecha(o.fechaGeneracion) + '</td>' +
            '<td data-label="Fecha límite">' + (o.fechaVencimiento ? formatoFecha(o.fechaVencimiento) : '-') + '</td>' +
            '<td data-label="Estado" class="text-center">' + badgeEstado(o) + '</td>' +
            '<td class="text-center celda-accion"><span class="mop-acciones">' + botonImprimir(o, 'Orden') + '</span></td>' +
            '</tr>';
    }

    // Matrícula: tabla aparte, sin numerar (no entra en la numeración de las cuotas)
    function renderMatricula(ordenes) {
        if (!ordenes.length) {
            $('#matricula').hide();
            return;
        }
        $('#contenido_matricula').html(ordenes.map(function (o) { return filaOrden(o, null); }).join(''));
        $('#matricula').show();
    }

    function renderTabla(ordenes) {
        var $tbody = $('#contenido_ordenes_pago');
        $tbody.empty();

        if (!ordenes.length) {
            $tbody.html('<tr><td colspan="9" class="text-center text-muted py-4">' +
                (todasLasOrdenes.some(function (o) { return o.tipo !== 'MATRICULA' && o.estado !== 'ANULADO'; })
                    ? 'Ninguna orden coincide con el filtro seleccionado.'
                    : 'Todavía no tiene órdenes de cuotas ni de clínicas.') + '</td></tr>');
            return;
        }

        // N° correlativo según el orden en que se generaron (1 = la primera); la última
        // generada va arriba con el número más alto (el servidor ya las manda así).
        // La matrícula no entra: va en su propia tabla.
        $tbody.html(ordenes.map(function (o, i) { return filaOrden(o, ordenes.length - i); }).join(''));
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

        var delPrograma = todasLasOrdenes.filter(function (o) {
            return o.estado !== 'ANULADO' && (!programaID || String(o.programaID) === String(programaID));
        });
        renderMatricula(delPrograma.filter(function (o) { return o.tipo === 'MATRICULA'; }));
        renderTabla(delPrograma.filter(function (o) {
            return o.tipo !== 'MATRICULA' && (!tipo || o.tipo === tipo);
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

    // Detalle de las cuotas elegidas (una orden por cuota) y el total a pagar
    function detalleCuotas(c, cantidad) {
        var lista = (c.posibles && c.posibles.length ? c.posibles : [{ numeroCuota: c.numeroCuota, monto: c.monto }]).slice(0, cantidad);
        var total = lista.reduce(function (s, q) { return s + (parseFloat(q.monto) || 0); }, 0);
        return '<table class="mop-detalle-cuotas">' +
            lista.map(function (q, i) {
                return '<tr><td>Orden ' + (i + 1) + ' · <strong>' + escapar(etiquetaCuota({ numeroCuota: q.numeroCuota, totalCuotas: c.totalCuotas })) + '</strong></td>' +
                    '<td class="text-right">' + formatoMoneda(q.monto) + '</td></tr>';
            }).join('') +
            '<tr class="mop-detalle-total"><td>Total a pagar' + (lista.length > 1 ? ' (' + lista.length + ' órdenes)' : '') + '</td>' +
                '<td class="text-right">' + formatoMoneda(total) + '</td></tr>' +
            '</table>';
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
                fila('Programa', escapar(c.programa)) +
                fila('Pagar hasta', formatoFecha(c.fechaVencimiento)) +
                '</table>' +
                '<div id="detalle_cuotas">' + detalleCuotas(c, 1) + '</div>' +
                '<p style="margin:12px 0 0;font-size:13px;color:#475569;">Imprima la orden y preséntela en <strong>Tesoro Universitario</strong>.</p>',
            // Al cambiar la cantidad se ve al momento cada cuota y el total
            onOpen: function () {
                $('#cantidad_cuotas').on('change', function () {
                    $('#detalle_cuotas').html(detalleCuotas(c, parseInt($(this).val(), 10) || 1));
                });
            },
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
