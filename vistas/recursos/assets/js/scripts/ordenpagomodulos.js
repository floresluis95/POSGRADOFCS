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

        $('#ordenPagoContenido').html('<div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-2x text-primary"></i></div>');

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
                $('#ordenPagoContenido').html('<div class="alert alert-danger">No se pudo cargar el resumen de pago</div>');
                return;
            }

            const ordenResp = respOrden[0];
            const orden = (ordenResp && ordenResp.success) ? ordenResp.orden : null;

            renderOrdenPago(resp.resumen, orden);
        }).fail(function() {
            $('#ordenPagoContenido').html('<div class="alert alert-danger">No se pudieron cargar los datos de la orden de pago</div>');
        });
    };

    function renderOrdenPago(r, orden) {
        // Cuota que corresponde según lo ya cancelado (no un plan fijo): la próxima
        // orden que se genere es la cuota N+1, siendo N las cuotas ya pagadas.
        opContexto.numeroCuotaActual = (parseInt(r.cuotasPagadasCount, 10) || 0) + 1;

        if (!r.tienePlan) {
            $('#ordenPagoContenido').html(
                '<div class="alert alert-warning text-center">' +
                    '<i class="fa fa-exclamation-triangle fa-2x mb-2"></i>' +
                    '<h6>Sin Plan de Pago del Programa</h6>' +
                    '<p class="mb-0">Normalmente porque eligió Plan Grupal en Pre-Registro. Use el botón "Plan Grupal" en la barra superior para armarle uno.</p>' +
                '</div>'
            );
            return;
        }

        if (r.completado) {
            $('#ordenPagoContenido').html(
                '<div class="alert alert-success text-center">' +
                    '<i class="fa fa-check-circle fa-2x mb-2"></i>' +
                    '<h6>Programa pagado en su totalidad</h6>' +
                    '<p class="mb-0">No queda saldo pendiente para este estudiante.</p>' +
                '</div>'
            );
            return;
        }

        if (orden) {
            $('#ordenPagoContenido').html(
                '<div class="card border-warning mb-0">' +
                    '<div class="card-body text-center py-4">' +
                        '<i class="fa fa-file-invoice fa-2x text-warning mb-2"></i>' +
                        '<h6 class="mb-1">Orden de pago pendiente</h6>' +
                        '<div class="h4 font-weight-bold mb-1">Bs. ' + parseFloat(orden.MontoTotal).toFixed(2) + '</div>' +
                        '<div class="text-muted mb-3">N° ' + orden.NumeroOrden + ' &middot; generada el ' + orden.FechaGeneracion + '</div>' +
                        '<p class="small text-muted mb-3">' +
                            '<i class="fa fa-info-circle"></i> El estudiante debe cancelar este monto y luego usted registra el voucher ' +
                            'en la pestaña <strong>"Cuotas del Programa"</strong> para confirmar el pago.' +
                        '</p>' +
                        '<button type="button" class="btn btn-outline-secondary" id="btnReimprimirOrdenCuota" ' +
                            'data-id="' + orden.IdOrdenPago + '" data-monto="' + orden.MontoTotal + '" ' +
                            'data-nombre-factura="' + (orden.NombreFactura || '') + '" data-nit-ci="' + (orden.NitCiFactura || '') + '" ' +
                            'data-numero-cuenta="' + (orden.NumeroCuentaDeposito || '') + '" data-firma="' + (orden.Firma || '') + '">' +
                            '<i class="fa fa-print"></i> Reimprimir orden' +
                        '</button> ' +
                        '<button type="button" class="btn btn-primary ml-1 btn-ir-cuotas-programa">' +
                            '<i class="fa fa-upload"></i> Registrar voucher' +
                        '</button> ' +
                        '<button type="button" class="btn btn-outline-danger ml-1" id="btnAnularOrdenCuota" ' +
                            'data-id="' + orden.IdOrdenPago + '" data-confirmando="0">' +
                            '<i class="fa fa-times"></i> Anular orden' +
                        '</button>' +
                        '<div id="avisoAnularOrdenCuota" class="small text-danger mt-2" style="display:none;"></div>' +
                    '</div>' +
                '</div>'
            );
            return;
        }

        const saldoPendiente = parseFloat(r.saldoPendiente) || 0;
        const campoMonto = r.cuotaUnica
            ? '<input type="hidden" id="opMontoCuota" value="' + saldoPendiente.toFixed(2) + '">' +
              '<div class="h2 font-weight-bold text-primary mb-0">Bs. ' + saldoPendiente.toFixed(2) + '</div>' +
              '<small class="text-muted">Saldo total del programa</small>'
            : '<div class="input-group input-group-lg justify-content-center">' +
              '<div class="input-group-prepend"><span class="input-group-text bg-white border-0 font-weight-bold text-primary">Bs.</span></div>' +
              '<input type="number" class="form-control text-primary font-weight-bold border-0 text-center" style="max-width:160px; font-size:1.75rem;" ' +
              'id="opMontoCuota" min="0.01" max="' + saldoPendiente.toFixed(2) + '" step="0.01" value="' + saldoPendiente.toFixed(2) + '">' +
              '</div><small class="text-muted">Monto de esta cuota &middot; saldo pendiente Bs. ' + saldoPendiente.toFixed(2) + '</small>';

        const html =
            // Monto a generar: lo primero y más visible, estilo "checkout"
            '<div class="card border-primary mb-3">' +
                '<div class="card-body text-center py-3">' +
                    '<small class="text-uppercase text-muted d-block mb-1"><i class="fa fa-tag"></i> Monto de la orden a generar</small>' +
                    campoMonto +
                '</div>' +
            '</div>' +

            // Datos de facturación
            '<h6 class="text-muted small text-uppercase mb-2"><i class="fa fa-file-invoice-dollar"></i> Datos de facturación</h6>' +
            '<div class="row">' +
                '<div class="col-lg-6 form-group">' +
                    '<label class="small font-weight-bold mb-1">Nombre / Razón Social</label>' +
                    '<input type="text" class="form-control form-control-sm" id="opNombreFactura" value="' + (opContexto.estudianteNombre || '') + '">' +
                '</div>' +
                '<div class="col-lg-6 form-group">' +
                    '<label class="small font-weight-bold mb-1">NIT / C.I.</label>' +
                    '<input type="text" class="form-control form-control-sm" id="opNitCiFactura" value="' + (opContexto.estudianteCi || '') + '">' +
                '</div>' +
            '</div>' +

            // Datos de la orden
            '<h6 class="text-muted small text-uppercase mb-2 mt-1"><i class="fa fa-cog"></i> Datos de la orden</h6>' +
            '<div class="row">' +
                '<div class="col-lg-4 form-group">' +
                    '<label class="small font-weight-bold mb-1">Responsable de la Firma</label>' +
                    '<div class="input-group input-group-sm">' +
                        '<select class="form-control form-control-sm" id="opFirma">' +
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
                    // teclado (el modal se lo roba de vuelta), igual que el caso ya
                    // documentado de "Anular orden" más abajo en este archivo.
                    '<div id="opNuevoFirmanteBox" class="input-group input-group-sm mt-1" style="display:none;">' +
                        '<input type="text" class="form-control form-control-sm" id="opNuevoFirmanteNombre" placeholder="Nombre completo">' +
                        '<div class="input-group-append">' +
                            '<button type="button" class="btn btn-success" id="opBtnGuardarFirmante" title="Guardar"><i class="fa fa-check"></i></button>' +
                            '<button type="button" class="btn btn-outline-secondary" id="opBtnCancelarFirmante" title="Cancelar"><i class="fa fa-times"></i></button>' +
                        '</div>' +
                    '</div>' +
                    '<small class="form-text text-muted">Nombre que aparecerá en la línea de firma de la orden impresa</small>' +
                '</div>' +
                '<div class="col-lg-4 form-group">' +
                    '<label class="small font-weight-bold mb-1">Cuenta a la que debe depositar</label>' +
                    '<select class="form-control form-control-sm" id="opCuentaPago">' +
                        '<option value="">Cargando cuentas...</option>' +
                    '</select>' +
                '</div>' +
                '<div class="col-lg-4 form-group">' +
                    '<label class="small font-weight-bold mb-1">Fecha límite de pago</label>' +
                    '<input type="date" class="form-control form-control-sm" id="opFechaVencimiento" value="' + fechaPorDefectoVencimiento() + '">' +
                    '<small class="form-text text-muted">Para el control de mora</small>' +
                '</div>' +
            '</div>' +

            '<div class="text-right mt-2">' +
                '<button type="button" class="btn btn-success" id="opBtnGenerarOrden">' +
                    '<i class="fa fa-file-invoice"></i> Generar Orden de Pago' +
                '</button>' +
            '</div>';

        $('#ordenPagoContenido').html(html);
        cargarOpcionesCuentaPago();
        cargarOpcionesFirmante();
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
                fechaVencimiento: fechaVencimiento
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
                                firma: firma
                            });
                            window.cargarOrdenPagoModulos(opContexto);
                            if (typeof window.cargarCuotasPrograma === 'function') {
                                window.cargarCuotasPrograma(opContexto.programaID);
                            }
                        });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: resp.mensaje || 'No se pudo generar la orden de pago' });
                    $btn.prop('disabled', false).html('<i class="fa fa-file-invoice"></i> Generar Orden de Pago');
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo generar la orden de pago' });
                $btn.prop('disabled', false).html('<i class="fa fa-file-invoice"></i> Generar Orden de Pago');
            }
        });
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
     * @param {number} numeroCuota
     */
    function etiquetaCuota(numeroCuota) {
        const ordinales = ['', 'Primera', 'Segunda', 'Tercera', 'Cuarta', 'Quinta',
            'Sexta', 'Séptima', 'Octava', 'Novena', 'Décima'];
        const n = parseInt(numeroCuota, 10) || 1;
        return (n > 0 && n < ordinales.length)
            ? (ordinales[n] + ' cuota del programa')
            : ('Cuota ' + n + ' del programa');
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
                    programa: (params.programaNombre || '') + '\n' + etiquetaCuota(params.numeroCuota),
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
