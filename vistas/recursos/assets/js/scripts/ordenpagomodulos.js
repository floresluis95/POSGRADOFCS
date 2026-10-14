/**
 * Orden de Pago de Cuotas del Programa
 * Pestaña "Orden de Pago" del modal unificado de Gestión de Pagos del Programa (Matriculados).
 * Las cuotas ya NO están ligadas a módulos: su monto es libre (Plan Regular) o el saldo
 * total (Plan Contado/Grupal). Solo puede existir una orden pendiente a la vez por inscripción.
 *
 * Se emite ANTES de pagar: se genera un comprobante (PDF) con el monto a cancelar, y recién
 * después se registra el pago (con voucher) en la pestaña "Pago de Módulos" (Cuotas del
 * Programa). Al registrarse ese pago, la orden pendiente se confirma automáticamente.
 *
 * El PDF usa el MISMO formato oficial (UTO - Coordinación de Posgrado) que la orden de
 * pago de matrícula/preregistro: se genera enviando los datos por POST a
 * vistas/componentes/generar-orden-pago-pdf.php (igual que hace ordenpago.js).
 */

$(document).ready(function() {

    // idInscripcion, estudianteID, estudianteNombre, estudianteCi, programaID,
    // programaNombre, version, numeroTramite
    let opContexto = {};

    // Fecha sugerida por defecto para el vencimiento de la cuota (hoy + 5 días),
    // en formato YYYY-MM-DD para el input type="date".
    function fechaPorDefectoVencimiento() {
        const fecha = new Date();
        fecha.setDate(fecha.getDate() + 5);
        const pad = (n) => String(n).padStart(2, '0');
        return fecha.getFullYear() + '-' + pad(fecha.getMonth() + 1) + '-' + pad(fecha.getDate());
    }

    // ========================================
    // CARGAR PESTAÑA: resumen de pago + orden pendiente (si existe)
    // Invocado desde inscripcionmodulo.js al abrir el modal unificado de Gestión de Pagos
    // ========================================
    window.cargarOrdenPagoModulos = function(datos) {
        opContexto = datos;

        $('#ordenPagoContenido').html('<div class="gp-cargando"><i class="fa fa-spinner fa-spin"></i> Cargando...</div>');

        $.when(
            $.ajax({
                url: 'ajax/pagomodulo.ajax.php',
                method: 'POST',
                data: { accion: 'obtenerResumenCuotas', idinscripcion: opContexto.idInscripcion, programaID: opContexto.programaID },
                dataType: 'json'
            }),
            $.ajax({
                url: 'ajax/ordenpago.ajax.php',
                method: 'POST',
                data: { accion: 'obtenerOrdenPendienteCuotaPrograma', idinscripcion: opContexto.idInscripcion },
                dataType: 'json'
            })
        ).done(function(respResumen, respOrden) {
            const resp = respResumen[0];
            if (!resp || !resp.success) {
                $('#ordenPagoContenido').html('<div class="gp-aviso">No se pudo cargar el resumen de pago</div>');
                return;
            }

            const ordenResp = respOrden[0];
            const orden = (ordenResp && ordenResp.success) ? ordenResp.orden : null;

            renderOrdenPago(resp.resumen, orden);
        }).fail(function() {
            $('#ordenPagoContenido').html('<div class="gp-aviso">No se pudieron cargar los datos de la orden de pago</div>');
        });
    };

    function renderOrdenPago(r, orden) {
        // Cuota que corresponde según lo ya cancelado (no un plan fijo): la próxima
        // orden que se genere es la cuota N+1, siendo N las cuotas ya pagadas.
        opContexto.numeroCuotaActual = (parseInt(r.cuotasPagadasCount, 10) || 0) + 1;
        // Total de cuotas del plan (definido en Pre-Registro), para mostrar "1/12"
        opContexto.numeroCuotasPlan = parseInt(r.numeroCuotasPlan, 10) || 0;
        const bs = window.formatoBs;

        if (!r.tienePlan) {
            $('#ordenPagoContenido').html(
                '<div class="gp-section gp-estado-vacio is-alerta">' +
                    '<i class="fa fa-exclamation-triangle"></i>' +
                    '<h6>Sin plan de pago del programa</h6>' +
                    '<p class="mb-0">Normalmente porque eligió Plan Grupal en Pre-Registro. Use el botón "Plan Grupal" de la barra superior para armarle uno.</p>' +
                '</div>'
            );
            return;
        }

        if (r.completado) {
            $('#ordenPagoContenido').html(
                '<div class="gp-section gp-estado-vacio is-exito">' +
                    '<i class="fa fa-check-circle"></i>' +
                    '<h6>Programa pagado en su totalidad</h6>' +
                    '<p class="mb-0">No queda saldo pendiente para este estudiante.</p>' +
                '</div>'
            );
            return;
        }

        if (orden) {
            $('#ordenPagoContenido').html(
                '<div class="gp-section gp-orden">' +
                    '<div class="gp-orden-fila">' +
                        '<div>' +
                            '<div class="gp-section-title mb-1"><i class="fa fa-file-invoice"></i> Orden de pago pendiente</div>' +
                            '<div class="gp-orden-num">N° ' + orden.NumeroOrden + '</div>' +
                            '<div class="gp-orden-meta">Generada el ' + orden.FechaGeneracion +
                                (orden.NumeroCuentaDeposito ? ' &middot; Cuenta ' + orden.NumeroCuentaDeposito : '') + '</div>' +
                            (orden.ModulosCubiertos
                                ? '<div class="gp-orden-meta"><i class="fa fa-book"></i> Cubre: ' + $('<div>').text(orden.ModulosCubiertos).html() + '</div>'
                                : '') +
                        '</div>' +
                        '<div>' +
                            '<div class="gp-orden-monto">' + bs(orden.MontoTotal) + '</div>' +
                            '<div class="text-right mt-1">' + window.chipEstadoPago('PENDIENTE') + '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="gp-ayuda mt-3">' +
                        'El estudiante debe cancelar este monto; luego registre su voucher en la pestaña "Cuotas del programa" para confirmar el pago.' +
                    '</div>' +
                    '<div class="gp-actions">' +
                        '<button type="button" class="btn btn-primary btn-ir-cuotas-programa">' +
                            '<i class="fa fa-upload"></i> Registrar voucher' +
                        '</button>' +
                        '<button type="button" class="btn btn-outline-secondary" id="btnReimprimirOrdenCuota" ' +
                            'data-id="' + orden.IdOrdenPago + '" data-monto="' + orden.MontoTotal + '" ' +
                            'data-nombre-factura="' + (orden.NombreFactura || '') + '" data-nit-ci="' + (orden.NitCiFactura || '') + '" ' +
                            'data-numero-cuenta="' + (orden.NumeroCuentaDeposito || '') + '" data-firma="' + (orden.Firma || '') + '">' +
                            '<i class="fa fa-print"></i> Reimprimir' +
                        '</button>' +
                        '<button type="button" class="btn btn-link text-danger ml-auto" id="btnAnularOrdenCuota" ' +
                            'data-id="' + orden.IdOrdenPago + '" data-confirmando="0">' +
                            '<i class="fa fa-times"></i> Anular orden' +
                        '</button>' +
                    '</div>' +
                    '<div id="avisoAnularOrdenCuota" class="small text-danger mt-2 text-right" style="display:none;"></div>' +
                '</div>'
            );
            return;
        }

        const saldoPendiente = parseFloat(r.saldoPendiente) || 0;
        const esc = function(t) { return $('<div>').text(t == null ? '' : String(t)).html(); };

        // Monto sugerido: la cuota pactada en Pre-Registro (la última cuota, todo el
        // saldo); en planes sin monto pactado, el saldo repartido entre las cuotas que
        // faltan; sin número de cuotas definido, el saldo completo
        const cuotasPlan = parseInt(r.numeroCuotasPlan, 10) || 0;
        const cuotasRestantes = cuotasPlan > 0 ? cuotasPlan - (opContexto.numeroCuotaActual - 1) : 0;
        const montoCuotaPlan = parseFloat(r.montoCuotaPlan) || 0;
        const montoSugerido = cuotasRestantes <= 1
            ? saldoPendiente
            : (montoCuotaPlan > 0
                ? Math.min(montoCuotaPlan, saldoPendiente)
                : Math.round((saldoPendiente / cuotasRestantes) * 100) / 100);
        opContexto.saldoPendiente = saldoPendiente;

        const textoCuota = 'Cuota ' + opContexto.numeroCuotaActual + (cuotasPlan ? ' de ' + cuotasPlan : '');
        const atajos =
            (montoSugerido < saldoPendiente
                ? '<button type="button" class="op-atajo" data-monto="' + montoSugerido.toFixed(2) + '">Cuota sugerida ' + bs(montoSugerido) + '</button>'
                : '') +
            '<button type="button" class="op-atajo" data-monto="' + saldoPendiente.toFixed(2) + '">Todo el saldo ' + bs(saldoPendiente) + '</button>';

        // Plegable: una línea con el valor actual y "Cambiar" para abrir los campos
        const plegable = function(icono, etiqueta, idResumen, resumen, idCuerpo, cuerpo) {
            return '<div class="op-plegable">' +
                '<button type="button" class="op-plegable-cab" data-target="#' + idCuerpo + '">' +
                    '<span><i class="fa ' + icono + '"></i> ' + etiqueta + ': <strong id="' + idResumen + '">' + resumen + '</strong></span>' +
                    '<span class="op-plegable-accion">Cambiar <i class="fa fa-chevron-down"></i></span>' +
                '</button>' +
                '<div class="op-plegable-cuerpo" id="' + idCuerpo + '" style="display:none;">' + cuerpo + '</div>' +
            '</div>';
        };

        const html =
            '<div class="op-form">' +

                // Paso 1: monto
                '<div class="op-paso">' +
                    '<div class="op-paso-num">1</div>' +
                    '<div class="op-paso-cuerpo">' +
                        '<div class="op-paso-titulo">¿Cuánto va a pagar? <span class="op-paso-ayuda"><strong>' + textoCuota + '</strong> &middot; saldo ' + bs(saldoPendiente) + '</span></div>' +
                        '<div class="op-monto-fila">' +
                            '<div class="gp-monto-input">' +
                                '<span>Bs.</span>' +
                                '<input type="number" id="opMontoCuota" min="0.01" max="' + saldoPendiente.toFixed(2) + '" step="0.01" value="' + montoSugerido.toFixed(2) + '">' +
                            '</div>' +
                            '<div class="op-atajos">' + atajos + '</div>' +
                        '</div>' +
                        '<div id="opMontoAviso" class="op-aviso-monto" style="display:none;"></div>' +
                    '</div>' +
                '</div>' +

                // Paso 2: cuenta, fecha límite y firma
                '<div class="op-paso">' +
                    '<div class="op-paso-num">2</div>' +
                    '<div class="op-paso-cuerpo">' +
                        '<div class="op-paso-titulo">¿Dónde y hasta cuándo deposita?</div>' +
                        '<div class="row op-fila-datos">' +
                            '<div class="col-md-5 form-group">' +
                                '<label class="gp-label" for="opCuentaPago">Cuenta de depósito</label>' +
                                '<select class="form-control" id="opCuentaPago">' +
                                    '<option value="">Cargando cuentas...</option>' +
                                '</select>' +
                            '</div>' +
                            '<div class="col-md-3 form-group">' +
                                '<label class="gp-label" for="opFechaVencimiento">Fecha límite</label>' +
                                '<input type="date" class="form-control" id="opFechaVencimiento" value="' + fechaPorDefectoVencimiento() + '">' +
                            '</div>' +
                            '<div class="col-md-4 form-group">' +
                                '<label class="gp-label" for="opFirma">Firma</label>' +
                                '<div class="input-group">' +
                                    '<select class="form-control" id="opFirma">' +
                                        '<option value="">Cargando...</option>' +
                                    '</select>' +
                                    '<div class="input-group-append">' +
                                        '<button type="button" class="btn btn-outline-secondary" id="opBtnAgregarFirmante" title="Agregar nuevo nombre">' +
                                            '<i class="fa fa-plus"></i>' +
                                        '</button>' +
                                    '</div>' +
                                '</div>' +
                                // Caja en línea (no SweetAlert): un popup con <input> lanzado sobre un
                                // modal de Bootstrap ya abierto queda visible pero no recibe el foco del
                                // teclado (el modal se lo roba de vuelta), igual que en "Anular orden".
                                '<div id="opNuevoFirmanteBox" class="input-group input-group-sm mt-1" style="display:none;">' +
                                    '<input type="text" class="form-control form-control-sm" id="opNuevoFirmanteNombre" placeholder="Nombre completo">' +
                                    '<div class="input-group-append">' +
                                        '<button type="button" class="btn btn-success" id="opBtnGuardarFirmante" title="Guardar"><i class="fa fa-check"></i></button>' +
                                        '<button type="button" class="btn btn-outline-secondary" id="opBtnCancelarFirmante" title="Cancelar"><i class="fa fa-times"></i></button>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +

                // Paso 3: factura y módulos ya vienen llenos; solo se abren para cambiarlos
                '<div class="op-paso">' +
                    '<div class="op-paso-num">3</div>' +
                    '<div class="op-paso-cuerpo">' +
                        '<div class="op-paso-titulo">Revise <span class="op-opcional">(ya viene lleno)</span></div>' +
                        '<div class="op-plegables">' +
                        plegable('fa-file-invoice-dollar', 'Factura a nombre de', 'opFacturaResumen',
                            esc(opContexto.estudianteNombre || '') + ' &middot; ' + esc(opContexto.estudianteCi || ''), 'opFacturaCuerpo',
                            '<div class="row">' +
                                '<div class="col-md-7 form-group mb-md-0">' +
                                    '<label class="gp-label" for="opNombreFactura">Nombre / Razón social</label>' +
                                    '<input type="text" class="form-control" id="opNombreFactura" value="' + esc(opContexto.estudianteNombre || '') + '">' +
                                '</div>' +
                                '<div class="col-md-5 form-group mb-0">' +
                                    '<label class="gp-label" for="opNitCiFactura">NIT / C.I.</label>' +
                                    '<input type="text" class="form-control" id="opNitCiFactura" value="' + esc(opContexto.estudianteCi || '') + '">' +
                                '</div>' +
                            '</div>') +
                        plegable('fa-book', 'Módulo(s) que cubre', 'opModulosResumen', 'cargando...', 'opModulosCuerpo',
                            '<div class="gp-ayuda mt-0 mb-2">Solo para control; no cambia el monto ni la orden impresa. Puede marcar más de uno.</div>' +
                            '<div id="opModulosCuota" class="op-modulos-cuota"><span class="text-muted"><i class="fa fa-spinner fa-spin"></i> Cargando módulos...</span></div>') +
                        '</div>' +
                    '</div>' +
                '</div>' +

                // Barra final: total y un solo botón
                '<div class="op-barra-final">' +
                    '<div>' +
                        '<div class="op-barra-etiqueta">' + textoCuota + '</div>' +
                        '<div class="op-barra-total" id="opTotalResumen">' + bs(montoSugerido) + '</div>' +
                    '</div>' +
                    '<button type="button" class="btn btn-primary btn-lg" id="opBtnGenerarOrden">' +
                        '<i class="fa fa-file-invoice"></i> Generar orden de pago' +
                    '</button>' +
                '</div>' +
            '</div>';

        $('#ordenPagoContenido').html(html);
        cargarOpcionesCuentaPago();
        // Se preselecciona la última firma usada (en este navegador)
        cargarOpcionesFirmante(localStorage.getItem('opUltimaFirma') || '');
        cargarModulosCuota();
    }

    // ---- Interacción del formulario nuevo ----

    // Atajos de monto (cuota sugerida / todo el saldo)
    $(document).on('click', '#ordenPagoContenido .op-atajo', function() {
        $('#opMontoCuota').val($(this).data('monto')).trigger('input');
    });

    // Total de la barra final y aviso si el monto no es válido
    $(document).on('input', '#opMontoCuota', function() {
        const monto = parseFloat($(this).val()) || 0;
        const saldo = opContexto.saldoPendiente || 0;
        $('#opTotalResumen').text(window.formatoBs(monto));
        $('#ordenPagoContenido .op-atajo').each(function() {
            $(this).toggleClass('is-activo', Math.abs(parseFloat($(this).data('monto')) - monto) < 0.005);
        });
        const aviso = monto <= 0 ? 'Ingrese un monto mayor a 0'
            : (monto > saldo + 0.005 ? 'El monto supera el saldo pendiente (' + window.formatoBs(saldo) + ')' : '');
        $('#opMontoAviso').text(aviso).toggle(!!aviso);
        $('#opBtnGenerarOrden').prop('disabled', !!aviso);
    });

    // Abrir/cerrar los plegables del paso 3
    $(document).on('click', '#ordenPagoContenido .op-plegable-cab', function() {
        const $cab = $(this);
        $($cab.data('target')).slideToggle(150);
        $cab.toggleClass('is-abierto');
    });

    // El resumen de la factura sigue lo que se escribe
    $(document).on('input', '#opNombreFactura, #opNitCiFactura', function() {
        const esc = function(t) { return $('<div>').text(t).html(); };
        $('#opFacturaResumen').html(esc(($('#opNombreFactura').val() || '').trim() || '—') + ' &middot; ' + esc(($('#opNitCiFactura').val() || '').trim() || '—'));
    });

    // El resumen de módulos sigue las casillas marcadas
    function actualizarResumenModulos() {
        const codigos = $('#opModulosCuota .op-chk-modulo:checked:not(:disabled)').map(function() {
            return $(this).data('codigo');
        }).get();
        $('#opModulosResumen').text(codigos.length ? codigos.join(', ') : 'ninguno');
    }
    $(document).on('change', '#opModulosCuota .op-chk-modulo', actualizarResumenModulos);

    // ========================================
    // Módulos del programa para marcar a cuál(es) corresponde la cuota. Los que ya
    // tienen una orden vigente (pagada o pendiente) se muestran marcados y bloqueados.
    // Se sugiere el siguiente módulo sin cuota.
    // ========================================
    function cargarModulosCuota() {
        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            method: 'POST',
            dataType: 'json',
            data: { accion: 'modulosParaCuota', idinscripcion: opContexto.idInscripcion, programaID: opContexto.programaID },
            success: function(resp) {
                const $cont = $('#opModulosCuota');
                if (!resp || !resp.success || !resp.modulos.length) {
                    $cont.html('<span class="text-muted">El programa no tiene módulos registrados.</span>');
                    $('#opModulosResumen').text('sin módulos');
                    return;
                }
                const esc = function(t) { return $('<div>').text(t == null ? '' : String(t)).html(); };
                let sugeridoUno = false;
                let html = '';
                resp.modulos.forEach(function(m) {
                    const cubierto = !!m.NumeroOrden;
                    let marcar = cubierto;
                    if (!cubierto && !sugeridoUno) {
                        marcar = true;
                        sugeridoUno = true;
                    }
                    const estado = !cubierto ? ''
                        : ' <small class="' + (m.EstadoOrden === 'CONFIRMADO' ? 'text-success' : 'text-warning') + '">(' +
                            (m.EstadoOrden === 'CONFIRMADO' ? 'pagada' : (m.EstadoOrden === 'VENCIDO' ? 'vencida' : 'pendiente')) +
                            ' · ' + esc(m.NumeroOrden) + ')</small>';
                    html += '<label class="op-modulo' + (cubierto ? ' is-cubierto' : '') + '" title="' + esc(m.Nombre) + '">' +
                        '<input type="checkbox" class="op-chk-modulo" value="' + parseInt(m.Idmodulo, 10) + '" data-codigo="' + esc(m.Codigo) + '"' +
                            (marcar ? ' checked' : '') + (cubierto ? ' disabled' : '') + '>' +
                        '<span><strong>' + esc(m.Codigo) + '</strong> ' + esc(m.Nombre) + estado + '</span>' +
                    '</label>';
                });
                $cont.html(html);
                actualizarResumenModulos();
            },
            error: function() {
                $('#opModulosCuota').html('<span class="text-muted">No se pudieron cargar los módulos (la orden se puede generar igual).</span>');
                $('#opModulosResumen').text('no se pudieron cargar');
            }
        });
    }

    // ========================================
    // Cuenta bancaria a la que el estudiante debe depositar: cuentas del propio
    // programa de tipo CUOTAS (catálogo administrado desde Programas > Cuentas).
    // Se imprime en la sección "DATOS PARA DEPÓSITO EN BANCO" de la orden en PDF.
    // ========================================
    function cargarOpcionesCuentaPago() {
        $.ajax({
            url: 'ajax/programacuenta.ajax.php',
            type: 'POST',
            data: { accion: 'listarOptionsHtml', ProgramaID: opContexto.programaID, TipoCuenta: 'CUOTAS' },
            success: function(html) {
                $('#opCuentaPago').html(html);
            },
            error: function() {
                $('#opCuentaPago').html('<option value="">No se pudieron cargar las cuentas</option>');
            }
        });
    }

    // ========================================
    // Responsable de la Firma: catálogo de nombres (no es el usuario que generó la
    // orden en el sistema, ese se captura solo del lado del servidor). El nombre
    // elegido aquí es el que se imprime junto a la línea de firma en el PDF.
    // ========================================
    function cargarOpcionesFirmante(seleccionar) {
        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: { accion: 'listarFirmantes' },
            dataType: 'json',
            success: function(resp) {
                const firmantes = (resp && resp.success) ? resp.firmantes : [];
                let opciones = '<option value="">-- Seleccione --</option>';
                firmantes.forEach(function(f) {
                    opciones += '<option value="' + f.Nombre + '">' + f.Nombre + '</option>';
                });
                $('#opFirma').html(opciones);
                if (seleccionar) {
                    $('#opFirma').val(seleccionar);
                }
            },
            error: function() {
                $('#opFirma').html('<option value="">No se pudieron cargar los nombres</option>');
            }
        });
    }

    // Agregar un nuevo nombre al catálogo de firmantes: se abre una caja en línea
    // (no un popup de SweetAlert) para no chocar con el foco del modal de Bootstrap
    // ya abierto (ver comentario más arriba, junto al HTML de #opNuevoFirmanteBox).
    $(document).on('click', '#opBtnAgregarFirmante', function() {
        $('#opNuevoFirmanteBox').show();
        $('#opNuevoFirmanteNombre').val('').focus();
    });

    $(document).on('click', '#opBtnCancelarFirmante', function() {
        $('#opNuevoFirmanteBox').hide();
        $('#opNuevoFirmanteNombre').val('');
    });

    $(document).on('keydown', '#opNuevoFirmanteNombre', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            $('#opBtnGuardarFirmante').click();
        } else if (e.key === 'Escape') {
            $('#opBtnCancelarFirmante').click();
        }
    });

    $(document).on('click', '#opBtnGuardarFirmante', function() {
        const nombre = ($('#opNuevoFirmanteNombre').val() || '').trim();
        if (!nombre) {
            $('#opNuevoFirmanteNombre').focus();
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true);

        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: { accion: 'registrarFirmante', nombre: nombre },
            dataType: 'json',
            success: function(resp) {
                $btn.prop('disabled', false);
                if (resp.success) {
                    cargarOpcionesFirmante(resp.nombre);
                    $('#opNuevoFirmanteBox').hide();
                    $('#opNuevoFirmanteNombre').val('');
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: resp.mensaje || 'No se pudo agregar el nombre' });
                }
            },
            error: function() {
                $btn.prop('disabled', false);
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo agregar el nombre' });
            }
        });
    });

    $(document).on('click', '#opBtnGenerarOrden', function() {
        const firma = $('#opFirma').val() || '';
        const nombreFactura = ($('#opNombreFactura').val() || '').trim();
        const nitCiFactura = ($('#opNitCiFactura').val() || '').trim();
        const programaCuentaID = $('#opCuentaPago').val() || '';
        const numeroCuentaDeposito = $('#opCuentaPago option:selected').data('numero') || '';
        const fechaVencimiento = $('#opFechaVencimiento').val() || '';
        const monto = parseFloat($('#opMontoCuota').val());

        if (!monto || monto <= 0) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Ingrese un monto válido para la cuota' });
            return;
        }

        // Antes de generar: resumen para verificar monto, N° de cuota, programa y cuenta
        const esc = function(t) { return $('<div>').text(t == null ? '' : String(t)).html(); };
        const cuentaTexto = programaCuentaID ? $('#opCuentaPago option:selected').text().trim() : '';
        const programaTexto = (opContexto.programaNombre || '') +
            (opContexto.version && String(opContexto.version).toUpperCase() !== 'V-1' ? ' (' + opContexto.version + ')' : '');
        const fila = function(etiqueta, valor) {
            return '<tr><th style="padding:6px 10px;color:#64748b;font-weight:600;white-space:nowrap;vertical-align:top;">' + etiqueta + '</th>' +
                '<td style="padding:6px 10px;">' + valor + '</td></tr>';
        };
        const resumen = '<table style="width:100%;text-align:left;font-size:14px;border-collapse:collapse;">' +
            fila('Monto', '<strong style="font-size:20px;color:#1e3c72;">' + window.formatoBs(monto) + '</strong>') +
            fila('Cuota', '<strong>' + esc(etiquetaCuota(opContexto.numeroCuotaActual, opContexto.numeroCuotasPlan)) + '</strong> (N° ' + opContexto.numeroCuotaActual + ')') +
            fila('Programa', esc(programaTexto)) +
            fila('Cuenta', cuentaTexto
                ? esc(cuentaTexto)
                : '<span style="color:#b7791f;">Sin cuenta de cuotas elegida' +
                  (opContexto.numeroTramite ? ': se imprimirá la cuenta auxiliar ' + esc(opContexto.numeroTramite) : '') + '</span>') +
            '</table>';

        Swal.fire({
            type: 'question', // SweetAlert2 v8
            title: 'Verifique la orden de pago',
            html: resumen,
            showCancelButton: true,
            confirmButtonText: '<i class="fa fa-file-invoice"></i> Sí, generar',
            cancelButtonText: 'Revisar',
            reverseButtons: true
        }).then(function(result) {
            if (!result.value) return; // SweetAlert2 v8: no tiene isConfirmed
            generarOrdenCuota();
        });

        function generarOrdenCuota() {
        if (firma) localStorage.setItem('opUltimaFirma', firma);
        const $btn = $('#opBtnGenerarOrden');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Generando...');

        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: {
                accion: 'registrarOrdenPagoCuotaPrograma',
                estudianteID: opContexto.estudianteID,
                idinscripcion: opContexto.idInscripcion,
                programaID: opContexto.programaID,
                programaCuentaID: programaCuentaID,
                montoTotal: monto,
                firma: firma,
                nombreFactura: nombreFactura,
                nitCiFactura: nitCiFactura,
                fechaVencimiento: fechaVencimiento,
                // Solo los módulos elegidos ahora (los bloqueados ya tienen su cuota)
                modulos: $('#opModulosCuota .op-chk-modulo:checked:not(:disabled)').map(function() { return this.value; }).get()
            },
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    Swal.fire({ icon: 'success', title: 'Orden generada', text: 'N° ' + resp.numeroOrden, timer: 1800, showConfirmButton: false })
                        .then(function() {
                            imprimirOrdenPagoOficial({
                                estudianteID: opContexto.estudianteID,
                                programaNombre: opContexto.programaNombre,
                                version: opContexto.version,
                                numeroTramite: opContexto.numeroTramite,
                                montoTotal: monto,
                                nombreFactura: nombreFactura,
                                nitCiFactura: nitCiFactura,
                                numeroCuentaDeposito: numeroCuentaDeposito,
                                numeroCuota: opContexto.numeroCuotaActual,
                                totalCuotas: opContexto.numeroCuotasPlan,
                                firma: firma
                            });
                            window.cargarOrdenPagoModulos(opContexto);
                            if (typeof window.cargarCuotasPrograma === 'function') {
                                window.cargarCuotasPrograma(opContexto.programaID);
                            }
                        });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: resp.mensaje || 'No se pudo generar la orden de pago' });
                    $btn.prop('disabled', false).html('<i class="fa fa-file-invoice"></i> Generar orden de pago');
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo generar la orden de pago' });
                $btn.prop('disabled', false).html('<i class="fa fa-file-invoice"></i> Generar orden de pago');
            }
        });
        }
    });

    $(document).on('click', '#btnReimprimirOrdenCuota', function() {
        const $this = $(this);
        imprimirOrdenPagoOficial({
            estudianteID: opContexto.estudianteID,
            programaNombre: opContexto.programaNombre,
            version: opContexto.version,
            numeroTramite: opContexto.numeroTramite,
            montoTotal: $this.data('monto'),
            nombreFactura: $this.data('nombre-factura'),
            nitCiFactura: $this.data('nit-ci'),
            numeroCuentaDeposito: $this.data('numero-cuenta'),
            numeroCuota: opContexto.numeroCuotaActual,
            totalCuotas: opContexto.numeroCuotasPlan,
            firma: $this.data('firma')
        });
    });

    // Botón "Registrar voucher" en la tarjeta de orden pendiente: salta a la pestaña de cuotas
    $(document).on('click', '.btn-ir-cuotas-programa', function() {
        $('#tab-pago-modulos-link').tab('show');
    });

    // Botón "Anular orden": cancela la orden de pago pendiente (baja lógica, Estado=ANULADO)
    // para poder generar una nueva con el monto/cuenta correctos. Reutiliza la misma acción
    // que usa Pre-Registro para anular una orden de matrícula: solo filtra por IdOrdenPago +
    // Estado=PENDIENTE, así que aplica igual a las órdenes de cuota del programa.
    //
    // La confirmación es EN LÍNEA (clic dos veces), sin SweetAlert2: un popup con botones
    // propios lanzado desde DENTRO de un modal de Bootstrap ya abierto puede quedar visible
    // pero no responder al clic (conflicto conocido entre el backdrop/foco del modal y el
    // popup). Como esta acción se dispara desde el modal "Gestión de Pagos" que ya está
    // abierto, se evita ese riesgo por completo con un confirm inline.
    let anularOrdenTimeout = null;

    $(document).on('click', '#btnAnularOrdenCuota', function() {
        const $btn = $(this);
        const idOrdenPago = $btn.data('id');
        const $aviso = $('#avisoAnularOrdenCuota');

        if ($btn.data('confirmando') !== 1) {
            $btn.data('confirmando', 1).html('<i class="fa fa-exclamation-triangle"></i> ¿Confirmar? Clic de nuevo');
            $aviso.text('Esta acción no se puede deshacer. Podrá generar una nueva orden después.').show();

            clearTimeout(anularOrdenTimeout);
            anularOrdenTimeout = setTimeout(function() {
                $btn.data('confirmando', 0).html('<i class="fa fa-times"></i> Anular orden');
                $aviso.hide();
            }, 4000);
            return;
        }

        clearTimeout(anularOrdenTimeout);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Anulando...');
        $aviso.hide();

        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: { accion: 'anularPreregistro', idOrdenPago: idOrdenPago },
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    window.cargarOrdenPagoModulos(opContexto);
                    if (typeof window.cargarCuotasPrograma === 'function') {
                        window.cargarCuotasPrograma(opContexto.programaID);
                    }
                } else {
                    $aviso.text(resp.mensaje || 'No se pudo anular la orden').show();
                    $btn.prop('disabled', false).data('confirmando', 0).html('<i class="fa fa-times"></i> Anular orden');
                }
            },
            error: function() {
                $aviso.text('No se pudo anular la orden').show();
                $btn.prop('disabled', false).data('confirmando', 0).html('<i class="fa fa-times"></i> Anular orden');
            }
        });
    });

    /**
     * Texto de la cuota que corresponde según lo ya cancelado (no un plan fijo):
     * "Primera cuota", "Segunda cuota"... y desde la 11ª en adelante, "Cuota N".
     * Con el total de cuotas del plan se agrega "(1/12)".
     * @param {number} numeroCuota
     * @param {number} [totalCuotas]
     */
    function etiquetaCuota(numeroCuota, totalCuotas) {
        const ordinales = ['', 'Primera', 'Segunda', 'Tercera', 'Cuarta', 'Quinta',
            'Sexta', 'Séptima', 'Octava', 'Novena', 'Décima'];
        const n = parseInt(numeroCuota, 10) || 1;
        const total = parseInt(totalCuotas, 10) || 0;
        return ((n > 0 && n < ordinales.length)
            ? (ordinales[n] + ' cuota del programa')
            : ('Cuota ' + n + ' del programa'))
            + (total > 0 ? ' (' + n + '/' + total + ')' : '');
    }

    /**
     * Genera el PDF de la orden de pago de la cuota con el mismo formato oficial
     * (UTO - Coordinación de Posgrado, doble copia) que la orden de pago de matrícula:
     * envía los datos por POST a generar-orden-pago-pdf.php, igual que ordenpago.js.
     * @param {Object} params estudianteID, programaNombre, version, numeroTramite,
     *        montoTotal, nombreFactura, nitCiFactura, numeroCuentaDeposito (cuenta CUOTAS elegida)
     */
    function imprimirOrdenPagoOficial(params) {
        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            method: 'POST',
            dataType: 'json',
            data: { accion: 'obtenerDatosEstudiante', estudianteID: params.estudianteID },
            success: function(resp) {
                const est = (resp && resp.success && resp.estudiante) ? resp.estudiante : {};

                let ciCompleto = est.Ci || '';
                if (est.Complemento) ciCompleto += '-' + est.Complemento;
                if (est.Exp) ciCompleto += ' ' + est.Exp;

                const montoTotal = parseFloat(params.montoTotal) || 0;

                const datos = {
                    apaterno: est.Apaterno || '',
                    amaterno: est.Amaterno || '',
                    nombres: est.Nombre || '',
                    correo: est.Correo || '',
                    ci: ciCompleto,
                    celular: est.Celular || '',
                    programa: (params.programaNombre || '') + '\n' + etiquetaCuota(params.numeroCuota, params.totalCuotas),
                    montoNumeral: 'Bs. ' + montoTotal.toFixed(2),
                    montoLiteral: numeroALetras(montoTotal),
                    version: params.version || 'V-1',
                    // "Cuenta auxiliar" del PDF: se prioriza la cuenta CUOTAS elegida por el
                    // usuario; si el programa no tiene ninguna cuenta CUOTAS registrada, se cae
                    // al N° de trámite del programa (igual que en la orden de pago de matrícula).
                    numeroTramite: params.numeroCuentaDeposito || params.numeroTramite || '',
                    nombreFactura: params.nombreFactura || '',
                    nitCiFactura: params.nitCiFactura || '',
                    firma: params.firma || ''
                };

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'vistas/componentes/generar-orden-pago-pdf.php';
                form.target = '_blank';

                for (const key in datos) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = datos[key];
                    form.appendChild(input);
                }

                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron obtener los datos del estudiante para la orden de pago' });
            }
        });
    }

    /**
     * Convertir número a letras (español - bolivianos).
     */
    function numeroALetras(numero) {
        const unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
        const decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        const especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
        const centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

        function convertirGrupo(n) {
            if (n === 0) return '';
            if (n === 100) return 'CIEN';

            let output = '';

            if (n >= 100) {
                output += centenas[Math.floor(n / 100)] + ' ';
                n %= 100;
            }

            if (n >= 10 && n < 20) {
                output += especiales[n - 10];
            } else {
                if (n >= 20) {
                    output += decenas[Math.floor(n / 10)];
                    n %= 10;
                    if (n > 0) output += ' Y ';
                }
                if (n > 0 && n < 10) {
                    output += unidades[n];
                }
            }

            return output.trim();
        }

        if (numero === 0) return 'CERO BOLIVIANOS CON 00/100';

        const partes = numero.toFixed(2).split('.');
        const entero = parseInt(partes[0]);
        const centavos = partes[1];

        let resultado = '';

        if (entero >= 1000000) {
            const millones = Math.floor(entero / 1000000);
            if (millones === 1) {
                resultado += 'UN MILLÓN ';
            } else {
                resultado += convertirGrupo(millones) + ' MILLONES ';
            }
        }

        const miles = Math.floor((entero % 1000000) / 1000);
        if (miles > 0) {
            if (miles === 1) {
                resultado += 'MIL ';
            } else {
                resultado += convertirGrupo(miles) + ' MIL ';
            }
        }

        const unidadesNum = entero % 1000;
        if (unidadesNum > 0) {
            resultado += convertirGrupo(unidadesNum) + ' ';
        }

        resultado += 'BOLIVIANOS CON ' + centavos + '/100';

        return resultado.trim();
    }

});
