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

    // Fecha de hoy (Y-m-d): fecha de la orden por defecto y tope (no se aceptan futuras)
    function fechaHoy() {
        const fecha = new Date();
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

    // Vuelve a cargar la pestaña con el mismo estudiante (p. ej. después de validar un
    // voucher en "Cuotas del programa", para que la orden ya no figure como pendiente)
    window.recargarOrdenPagoModulos = function() {
        if (opContexto.idInscripcion) {
            window.cargarOrdenPagoModulos(opContexto);
        }
    };

    function renderOrdenPago(r, orden) {
        // Cuota que corresponde: la próxima orden es la N+1, siendo N las cuotas pagadas
        // más las órdenes con voucher extraviado (ya pagadas, falta su voucher). Es la
        // misma posición que usa el servidor (PagoModuloModelo::NumeroCuotaDeOrdenModelo).
        opContexto.numeroCuotaActual = (parseInt(r.cuotasPagadasCount, 10) || 0) + (parseInt(r.cuotasEnEspera, 10) || 0) + 1;
        const montoEnEspera = parseFloat(r.montoEnEspera) || 0;
        const notaEspera = montoEnEspera > 0
            ? '<div class="gp-nota is-neutra mb-2"><i class="fa fa-search-dollar"></i><span>' +
                  (parseInt(r.cuotasEnEspera, 10) === 1 ? '1 orden' : r.cuotasEnEspera + ' órdenes') +
                  ' con <strong>voucher por validar</strong> (' + window.formatoBs(montoEnEspera) + '): ya se pagó y no se vuelve a pedir. ' +
                  'Registre su voucher en <a href="#" class="btn-ir-cuotas-programa">Cuotas del programa</a> cuando Tesoro dé el código.</span></div>'
            : '';
        // Total de cuotas del plan (definido en Pre-Registro), para mostrar "1/12"
        opContexto.numeroCuotasPlan = parseInt(r.numeroCuotasPlan, 10) || 0;
        const bs = window.formatoBs;

        const esc = function(t) { return $('<div>').text(t == null ? '' : String(t)).html(); };
        const nota = function(clase, icono, texto) {
            return '<div class="gp-nota ' + clase + ' mb-0"><i class="fa ' + icono + '"></i><span>' + texto + '</span></div>';
        };

        if (!r.tienePlan) {
            // Normalmente Plan Grupal en Pre-Registro: se arma con el botón "Plan Grupal" de la barra superior
            $('#ordenPagoContenido').html(nota('is-alerta', 'fa-exclamation-triangle',
                'Sin plan de pago del programa. Use <strong>Plan Grupal</strong> en la barra superior para armarlo.'));
            return;
        }

        if (r.completado) {
            $('#ordenPagoContenido').html(nota('is-exito', 'fa-check-circle', 'Programa pagado en su totalidad.'));
            return;
        }

        if (orden) {
            // "2026-10-14 11:25:49" → "14/10/2026"
            const f = String(orden.FechaGeneracion || '').match(/^(\d{4})-(\d{2})-(\d{2})/);
            const metaOrden = ['Generada ' + (f ? f[3] + '/' + f[2] + '/' + f[1] : esc(orden.FechaGeneracion))];
            if (orden.NumeroCuentaDeposito) metaOrden.push('Cuenta ' + esc(orden.NumeroCuentaDeposito));
            if (orden.ModulosCubiertos) metaOrden.push(esc(orden.ModulosCubiertos));

            $('#ordenPagoContenido').html(
                '<div class="op-form">' +
                    '<div class="op-cab is-pendiente">' +
                        '<div>' +
                            '<div class="op-cab-titulo">Orden N° ' + esc(orden.NumeroOrden) + ' ' + window.chipEstadoPago('PENDIENTE') + '</div>' +
                            '<div class="op-cab-meta">' + metaOrden.join(' &middot; ') + '</div>' +
                        '</div>' +
                        '<div class="op-cab-monto">' + bs(orden.MontoTotal) + '</div>' +
                    '</div>' +
                    '<div class="op-pie">' +
                        '<button type="button" class="btn btn-link text-danger" id="btnAnularOrdenCuota" ' +
                            'data-id="' + orden.IdOrdenPago + '" data-confirmando="0">Anular orden</button>' +
                        // El estudiante pagó pero perdió el voucher: se aparta para poder generar la siguiente
                        '<button type="button" class="btn btn-link mr-auto btn-abrir-extraviado" data-caja="#cajaExtraviadoCuota">Voucher por validar</button>' +
                        '<button type="button" class="btn btn-outline-secondary" id="btnReimprimirOrdenCuota" ' +
                            'data-id="' + orden.IdOrdenPago + '" data-monto="' + orden.MontoTotal + '" ' +
                            'data-nombre-factura="' + esc(orden.NombreFactura || '') + '" data-nit-ci="' + esc(orden.NitCiFactura || '') + '" ' +
                            'data-numero-cuenta="' + esc(orden.NumeroCuentaDeposito || '') + '" data-firma="' + esc(orden.Firma || '') + '" ' +
                            'data-numero-orden="' + esc(orden.NumeroOrden || '') + '">' +
                            '<i class="fa fa-print"></i> Reimprimir' +
                        '</button>' +
                        '<button type="button" class="btn btn-primary btn-ir-cuotas-programa">Registrar voucher</button>' +
                    '</div>' +
                    '<div id="avisoAnularOrdenCuota" class="small text-danger px-3 pb-2" style="display:none;"></div>' +
                    window.cajaVoucherExtraviado('cajaExtraviadoCuota', orden.IdOrdenPago) +
                '</div>'
            );
            if (notaEspera) $('#ordenPagoContenido').prepend(notaEspera);
            return;
        }

        // Tope de la orden: el saldo menos lo que está en órdenes con voucher extraviado
        const saldoPendiente = parseFloat(r.disponibleOrden != null ? r.disponibleOrden : r.saldoPendiente) || 0;
        if (saldoPendiente <= 0) {
            $('#ordenPagoContenido').html(notaEspera + nota('is-neutra', 'fa-info-circle',
                'Todo el saldo del programa está en órdenes con voucher por validar: no hay otra cuota que generar.'));
            return;
        }

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
        // Atajos de monto: solo si la cuota sugerida no es ya todo el saldo
        const atajos = montoSugerido < saldoPendiente
            ? '<button type="button" class="op-atajo" data-monto="' + montoSugerido.toFixed(2) + '">Cuota ' + bs(montoSugerido) + '</button>' +
              '<button type="button" class="op-atajo" data-monto="' + saldoPendiente.toFixed(2) + '">Todo el saldo</button>'
            : '';

        // Línea con el valor actual y "Cambiar" para abrir los campos (ya vienen llenos)
        const plegable = function(etiqueta, idResumen, resumen, idCuerpo, cuerpo) {
            return '<div class="op-plegable">' +
                '<button type="button" class="op-plegable-cab" data-target="#' + idCuerpo + '">' +
                    '<span>' + etiqueta + ': <strong id="' + idResumen + '">' + resumen + '</strong></span>' +
                    '<span class="op-plegable-accion">Cambiar</span>' +
                '</button>' +
                '<div class="op-plegable-cuerpo" id="' + idCuerpo + '" style="display:none;">' + cuerpo + '</div>' +
            '</div>';
        };

        const html =
            '<div class="op-form">' +
                '<div class="op-cab">' +
                    '<div class="op-cab-titulo">Nueva orden &middot; ' + textoCuota + '</div>' +
                    // Con órdenes de voucher extraviado, el tope es lo que falta pedir (no el saldo)
                    '<div class="op-cab-meta">' + (montoEnEspera > 0 ? 'Por pedir ' : 'Saldo ') + bs(saldoPendiente) + '</div>' +
                '</div>' +

                '<div class="op-cuerpo">' +
                    '<div class="row">' +
                        '<div class="col-md-6 form-group">' +
                            '<label class="gp-label" for="opMontoCuota">Monto</label>' +
                            '<div class="gp-monto-input">' +
                                '<span>Bs.</span>' +
                                '<input type="number" id="opMontoCuota" min="0.01" max="' + saldoPendiente.toFixed(2) + '" step="0.01" value="' + montoSugerido.toFixed(2) + '">' +
                            '</div>' +
                            (atajos ? '<div class="op-atajos">' + atajos + '</div>' : '') +
                            '<div id="opMontoAviso" class="op-aviso-monto" style="display:none;"></div>' +
                        '</div>' +
                        '<div class="col-md-6 form-group">' +
                            '<label class="gp-label" for="opCuentaPago">Cuenta de depósito</label>' +
                            '<select class="form-control" id="opCuentaPago">' +
                                '<option value="">Cargando cuentas...</option>' +
                            '</select>' +
                        '</div>' +
                        // Fecha de la orden: hoy y bloqueada; con la casilla se puede cambiar
                        '<div class="col-md-6 form-group">' +
                            '<label class="gp-label" for="opFechaOrden">Fecha de la orden</label>' +
                            '<input type="date" class="form-control" id="opFechaOrden" value="' + fechaHoy() + '" max="' + fechaHoy() + '" readonly>' +
                            '<div class="custom-control custom-checkbox mt-1">' +
                                '<input type="checkbox" class="custom-control-input" id="opChkModificarFecha">' +
                                '<label class="custom-control-label small" for="opChkModificarFecha">Modificar fecha</label>' +
                            '</div>' +
                        '</div>' +
                        '<div class="col-md-6 form-group">' +
                            '<label class="gp-label" for="opFechaVencimiento">Fecha límite</label>' +
                            '<input type="date" class="form-control" id="opFechaVencimiento" value="' + fechaPorDefectoVencimiento() + '">' +
                        '</div>' +
                        '<div class="col-md-6 form-group">' +
                            '<label class="gp-label" for="opFirma">Responsable de la firma <span class="gp-req">*</span></label>' +
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

                    // Factura y módulos ya vienen llenos; solo se abren para cambiarlos
                    plegable('Factura', 'opFacturaResumen',
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
                    // Solo para control: no cambia el monto ni la orden impresa
                    plegable('Módulo(s)', 'opModulosResumen', 'cargando...', 'opModulosCuerpo',
                        '<div id="opModulosCuota" class="op-modulos-cuota"><span class="text-muted"><i class="fa fa-spinner fa-spin"></i> Cargando módulos...</span></div>') +
                '</div>' +

                '<div class="op-pie">' +
                    '<button type="button" class="btn btn-primary" id="opBtnGenerarOrden">Generar orden de pago</button>' +
                '</div>' +
            '</div>';

        $('#ordenPagoContenido').html(notaEspera + html);
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

    // Atajo activo y aviso si el monto no es válido
    $(document).on('input', '#opMontoCuota', function() {
        const monto = parseFloat($(this).val()) || 0;
        const saldo = opContexto.saldoPendiente || 0;
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
    $(document).on('change', '#opFirma', function() {
        $(this).toggleClass('is-invalid', !$(this).val());
    });

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

    // Al desmarcar "Modificar fecha" la fecha de la orden vuelve a hoy
    $(document).on('change', '#opChkModificarFecha', function() {
        $('#opFechaOrden').prop('readonly', !this.checked);
        if (this.checked) {
            $('#opFechaOrden').trigger('focus');
        } else {
            $('#opFechaOrden').val(fechaHoy());
        }
    });

    $(document).on('click', '#opBtnGenerarOrden', function() {
        const firma = $('#opFirma').val() || '';
        const nombreFactura = ($('#opNombreFactura').val() || '').trim();
        const nitCiFactura = ($('#opNitCiFactura').val() || '').trim();
        const programaCuentaID = $('#opCuentaPago').val() || '';
        const numeroCuentaDeposito = $('#opCuentaPago option:selected').data('numero') || '';
        const fechaVencimiento = $('#opFechaVencimiento').val() || '';
        const monto = parseFloat($('#opMontoCuota').val());
        const fechaOrden = $('#opFechaOrden').val() || '';

        if (!monto || monto <= 0) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Ingrese un monto válido para la cuota' });
            return;
        }
        if (!fechaOrden || fechaOrden > fechaHoy()) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Ingrese una fecha de orden válida (no puede ser posterior a hoy)' });
            return;
        }

        // Sin responsable de la firma se pregunta antes: elegirlo o generar sin nombre
        // (la línea de firma sale con "FIRMA COORDINADOR(A) / RESPONSABLE")
        if (!firma && !$(this).data('sinFirmaAceptada')) {
            const $boton = $(this);
            $('#opFirma').addClass('is-invalid');
            Swal.fire({
                type: 'warning', // SweetAlert2 v8
                title: 'Seleccione el responsable de la firma',
                text: 'La orden no tiene un nombre para la línea de firma.',
                showCancelButton: true,
                confirmButtonText: 'Seleccionar responsable',
                cancelButtonText: 'Generar de todas formas',
                reverseButtons: true
            }).then(function(result) {
                if (result.value) {
                    $('#opFirma').trigger('focus');
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    $boton.data('sinFirmaAceptada', true).trigger('click');
                }
            });
            return;
        }
        $(this).removeData('sinFirmaAceptada');

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
            fila('Fecha', esc(fechaOrden.split('-').reverse().join('/'))) +
            fila('Programa', esc(programaTexto)) +
            fila('Cuenta', cuentaTexto
                ? esc(cuentaTexto)
                : '<span style="color:#b7791f;">Sin cuenta de cuotas elegida' +
                  (opContexto.numeroTramite ? ': se imprimirá la cuenta auxiliar ' + esc(opContexto.numeroTramite) : '') + '</span>') +
            fila('Firma', firma ? esc(firma) : '<span style="color:#b7791f;">Sin responsable de la firma</span>') +
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
                fechaGeneracion: fechaOrden,
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
                                firma: firma,
                                numeroOrden: resp.numeroOrden
                            });
                            window.cargarOrdenPagoModulos(opContexto);
                            if (typeof window.cargarCuotasPrograma === 'function') {
                                window.cargarCuotasPrograma(opContexto.programaID);
                            }
                        });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: resp.mensaje || 'No se pudo generar la orden de pago' });
                    $btn.prop('disabled', false).html('Generar orden de pago');
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo generar la orden de pago' });
                $btn.prop('disabled', false).html('Generar orden de pago');
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
            firma: $this.data('firma'),
            numeroOrden: $this.data('numero-orden')
        });
    });

    // Botón "Registrar voucher" en la tarjeta de orden pendiente: salta a la pestaña de cuotas
    $(document).on('click', '.btn-ir-cuotas-programa', function(e) {
        e.preventDefault(); // también es un enlace dentro del aviso de voucher extraviado
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
            $btn.data('confirmando', 1).html('¿Confirmar? Clic de nuevo');
            $aviso.text('Esta acción no se puede deshacer. Podrá generar una nueva orden después.').show();

            clearTimeout(anularOrdenTimeout);
            anularOrdenTimeout = setTimeout(function() {
                $btn.data('confirmando', 0).html('Anular orden');
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
                    $btn.prop('disabled', false).data('confirmando', 0).html('Anular orden');
                }
            },
            error: function() {
                $aviso.text('No se pudo anular la orden').show();
                $btn.prop('disabled', false).data('confirmando', 0).html('Anular orden');
            }
        });
    });

    // ========================================
    // VOUCHER EXTRAVIADO (cuotas y clínicas): el estudiante pagó pero perdió el voucher
    // y aún no se tiene el código. La orden se aparta (estado EXTRAVIADO) con su motivo
    // y se puede generar la siguiente; el voucher se registra cuando Tesoro dé el código.
    // Caja en línea, no SweetAlert: un <textarea> en un popup sobre el modal de
    // Bootstrap no recibe el foco del teclado (igual que en "Agregar firmante").
    // ========================================
    window.cajaVoucherExtraviado = function(idCaja, idOrdenPago) {
        return '<div class="vx-caja" id="' + idCaja + '" style="display:none;">' +
            '<div class="vx-caja-cab"><i class="fa fa-file-alt"></i> Marcar voucher por validar</div>' +
            '<div class="vx-caja-cuerpo">' +
                '<label class="gp-label" for="' + idCaja + 'Motivo">Motivo <span class="gp-req">*</span></label>' +
                '<textarea class="form-control vx-motivo" id="' + idCaja + 'Motivo" rows="2" maxlength="255" ' +
                    'placeholder="Ej.: Pagó en el banco pero no tiene el voucher; se solicitó el código a Tesorería"></textarea>' +
                '<div class="vx-nota">La orden quedará registrada como <strong>voucher por validar</strong>. El monto no se considera ' +
                    'cobrado hasta registrar el voucher, pero no se vuelve a cobrar y se habilita la orden siguiente.</div>' +
                '<div class="vx-aviso" style="display:none;"></div>' +
                '<div class="vx-pie">' +
                    '<button type="button" class="btn btn-sm btn-outline-secondary btn-cerrar-extraviado">Cancelar</button>' +
                    '<button type="button" class="btn btn-sm btn-primary btn-confirmar-extraviado" data-id="' + idOrdenPago + '">Confirmar</button>' +
                '</div>' +
            '</div>' +
        '</div>';
    };

    // "Anular orden" y "Voucher extraviado" no se mezclan: abrir uno cancela el otro
    function cancelarAnulacionEnCurso() {
        $('#btnAnularOrdenCuota, #clBtnAnular').each(function() {
            if ($(this).data('confirmando') === 1) {
                $(this).data('confirmando', 0).html('Anular orden');
            }
        });
        $('#avisoAnularOrdenCuota, #clAvisoAnular').hide();
    }
    $(document).on('click', '#btnAnularOrdenCuota, #clBtnAnular', function() {
        $('.vx-caja:visible').slideUp(150);
    });

    $(document).on('click', '.btn-abrir-extraviado', function() {
        cancelarAnulacionEnCurso();
        const $caja = $($(this).data('caja'));
        $caja.slideDown(150);
        $caja.find('.vx-motivo').trigger('focus');
    });
    $(document).on('click', '.btn-cerrar-extraviado', function() {
        $(this).closest('.vx-caja').slideUp(150);
    });
    $(document).on('click', '.btn-confirmar-extraviado', function() {
        const $btn = $(this);
        const $caja = $btn.closest('.vx-caja');
        const motivo = ($caja.find('.vx-motivo').val() || '').trim();
        const $aviso = $caja.find('.vx-aviso');
        if (motivo.length < 5) {
            $aviso.text('Escriba el motivo.').show();
            $caja.find('.vx-motivo').trigger('focus');
            return;
        }
        $aviso.hide();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            dataType: 'json',
            data: { accion: 'marcarVoucherExtraviado', idOrdenPago: $btn.data('id'), motivo: motivo },
            success: function(resp) {
                if (!resp.success) {
                    $aviso.text(resp.mensaje || 'No se pudo marcar la orden').show();
                    $btn.prop('disabled', false).html('Confirmar');
                    return;
                }
                // Cambian las tres pestañas (orden, cuotas y clínicas)
                window.cargarOrdenPagoModulos(opContexto);
                if (typeof window.cargarCuotasPrograma === 'function') window.cargarCuotasPrograma(opContexto.programaID);
                if (typeof window.cargarClinica === 'function') window.cargarClinica(opContexto);
                Swal.fire({ type: 'success', title: 'Voucher por validar', text: resp.mensaje, timer: 2200, showConfirmButton: false });
            },
            error: function() {
                $aviso.text('No se pudo marcar la orden').show();
                $btn.prop('disabled', false).html('Confirmar');
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
                    firma: params.firma || '',
                    numeroOrden: params.numeroOrden || ''
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
