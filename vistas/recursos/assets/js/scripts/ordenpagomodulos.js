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
                '<div class="alert alert-info">' +
                    '<i class="fa fa-file-invoice"></i> Ya existe una orden de pago pendiente: ' +
                    '<span class="badge badge-warning">N° ' + orden.NumeroOrden + '</span> ' +
                    'por <strong>Bs. ' + parseFloat(orden.MontoTotal).toFixed(2) + '</strong> ' +
                    '(generada el ' + orden.FechaGeneracion + ').' +
                '</div>' +
                '<small class="text-muted d-block mb-2">Registre el voucher en la pestaña "Pago de Módulos" para confirmarla, o reimprímala aquí.</small>' +
                '<button type="button" class="btn btn-sm btn-outline-secondary" id="btnReimprimirOrdenCuota" ' +
                    'data-id="' + orden.IdOrdenPago + '" data-monto="' + orden.MontoTotal + '" ' +
                    'data-nombre-factura="' + (orden.NombreFactura || '') + '" data-nit-ci="' + (orden.NitCiFactura || '') + '">' +
                    '<i class="fa fa-print"></i> Reimprimir orden' +
                '</button>'
            );
            return;
        }

        const saldoPendiente = parseFloat(r.saldoPendiente) || 0;
        const campoMonto = r.cuotaUnica
            ? '<input type="hidden" id="opMontoCuota" value="' + saldoPendiente.toFixed(2) + '">' +
              '<div class="form-group"><label class="small font-weight-bold mb-1">Monto a pagar (saldo total)</label>' +
              '<input type="text" class="form-control form-control-sm font-weight-bold" value="Bs. ' + saldoPendiente.toFixed(2) + '" readonly></div>'
            : '<div class="form-group"><label class="small font-weight-bold mb-1">Monto de esta cuota</label>' +
              '<div class="input-group input-group-sm">' +
              '<div class="input-group-prepend"><span class="input-group-text">Bs.</span></div>' +
              '<input type="number" class="form-control" id="opMontoCuota" min="0.01" max="' + saldoPendiente.toFixed(2) + '" ' +
              'step="0.01" value="' + saldoPendiente.toFixed(2) + '">' +
              '</div><small class="form-text text-muted">Saldo pendiente: Bs. ' + saldoPendiente.toFixed(2) + '</small></div>';

        const html =
            '<div class="row">' +
                '<div class="col-lg-6">' + campoMonto + '</div>' +
                '<div class="col-lg-3"><small class="text-muted d-block">Costo del programa</small><strong>Bs. ' + parseFloat(r.costoTotal).toFixed(2) + '</strong></div>' +
                '<div class="col-lg-3"><small class="text-muted d-block">Pagado</small><strong class="text-success">Bs. ' + parseFloat(r.totalPagado).toFixed(2) + '</strong></div>' +
            '</div>' +
            '<div class="row">' +
                '<div class="col-lg-6 form-group">' +
                    '<label class="small font-weight-bold mb-1">Nombre / Razón Social (Factura)</label>' +
                    '<input type="text" class="form-control form-control-sm" id="opNombreFactura" value="' + (opContexto.estudianteNombre || '') + '">' +
                '</div>' +
                '<div class="col-lg-6 form-group">' +
                    '<label class="small font-weight-bold mb-1">NIT / C.I.</label>' +
                    '<input type="text" class="form-control form-control-sm" id="opNitCiFactura" value="' + (opContexto.estudianteCi || '') + '">' +
                '</div>' +
            '</div>' +
            '<div class="row">' +
                '<div class="col-lg-6 form-group">' +
                    '<label class="small font-weight-bold mb-1">Responsable *</label>' +
                    '<input type="text" class="form-control form-control-sm" id="opResponsable" placeholder="Nombre de quien genera la orden">' +
                '</div>' +
                '<div class="col-lg-3 form-group">' +
                    '<label class="small font-weight-bold mb-1">Cuenta de pago</label>' +
                    '<select class="form-control form-control-sm" id="opCuentaPago">' +
                        '<option value="">Cargando cuentas...</option>' +
                    '</select>' +
                '</div>' +
                '<div class="col-lg-3 form-group">' +
                    '<label class="small font-weight-bold mb-1">Fecha límite de pago</label>' +
                    '<input type="date" class="form-control form-control-sm" id="opFechaVencimiento" value="' + fechaPorDefectoVencimiento() + '">' +
                    '<small class="form-text text-muted">Para el control de mora</small>' +
                '</div>' +
            '</div>' +
            '<div class="text-right mt-2">' +
                '<button type="button" class="btn btn-sm btn-success" id="opBtnGenerarOrden">' +
                    '<i class="fa fa-file-invoice"></i> Generar Orden de Pago' +
                '</button>' +
            '</div>';

        $('#ordenPagoContenido').html(html);
        cargarOpcionesCuentaPago();
    }

    // ========================================
    // Cuenta bancaria a la que el estudiante debe depositar (catálogo de "Cuentas",
    // administrado desde Programas). Se imprime en la sección "DATOS PARA DEPÓSITO
    // EN BANCO" de la orden de pago en PDF.
    // ========================================
    function cargarOpcionesCuentaPago() {
        $.ajax({
            url: 'ajax/cuenta.ajax.php',
            type: 'POST',
            data: { accion: 'listarActivasHtml' },
            success: function(html) {
                $('#opCuentaPago').html('<option value="">Cuenta por defecto (UTO)</option>' + html);
            },
            error: function() {
                $('#opCuentaPago').html('<option value="">Cuenta por defecto (UTO)</option>');
            }
        });
    }

    $(document).on('click', '#opBtnGenerarOrden', function() {
        const responsable = ($('#opResponsable').val() || '').trim();
        const nombreFactura = ($('#opNombreFactura').val() || '').trim();
        const nitCiFactura = ($('#opNitCiFactura').val() || '').trim();
        const numeroCuentaDeposito = $('#opCuentaPago').val() || '';
        const fechaVencimiento = $('#opFechaVencimiento').val() || '';
        const monto = parseFloat($('#opMontoCuota').val());

        if (!monto || monto <= 0) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Ingrese un monto válido para la cuota' });
            return;
        }
        if (!responsable) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Indique el responsable que genera la orden' });
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
                montoTotal: monto,
                responsable: responsable,
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
                                numeroCuota: opContexto.numeroCuotaActual
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
            numeroCuota: opContexto.numeroCuotaActual
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
     *        montoTotal, nombreFactura, nitCiFactura, numeroCuentaDeposito
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
                    numeroTramite: params.numeroTramite || '',
                    nombreFactura: params.nombreFactura || '',
                    nitCiFactura: params.nitCiFactura || '',
                    numeroCuentaDeposito: params.numeroCuentaDeposito || ''
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
