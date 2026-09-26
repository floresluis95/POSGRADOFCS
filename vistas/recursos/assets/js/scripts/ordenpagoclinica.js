/**
 * Pago de Clínicas
 * Pestaña "Clínicas" del modal Gestión de Pagos (Matriculados). Solo aparece si el
 * programa tiene una cuenta CLINICA activa (no todos los programas tienen clínicas).
 *
 * Flujo (igual que las cuotas, pero independiente de ellas):
 *  1. Se genera una orden de pago de clínica con monto libre → se imprime el PDF oficial.
 *  2. El estudiante paga; se registra aquí mismo su voucher → la orden queda CONFIRMADA.
 * Solo puede haber una orden de clínica pendiente a la vez por inscripción.
 *
 * El PDF se arma desde la base con vistas/componentes/orden-pago-pdf.php (mismo formato
 * oficial UTO que las demás órdenes), así la reimpresión siempre sale igual.
 */

$(document).ready(function() {

    // idInscripcion, estudianteID, estudianteNombre, estudianteCi, programaID, programaNombre
    let clContexto = {};

    function escapar(texto) {
        return $('<div>').text(texto == null ? '' : String(texto)).html();
    }

    function fechaHoyMas(dias) {
        const fecha = new Date();
        fecha.setDate(fecha.getDate() + dias);
        const pad = (n) => String(n).padStart(2, '0');
        return fecha.getFullYear() + '-' + pad(fecha.getMonth() + 1) + '-' + pad(fecha.getDate());
    }

    // ========================================
    // CARGAR PESTAÑA (invocado desde inscripcionmodulo.js al abrir el modal)
    // ========================================
    window.cargarClinica = function(datos) {
        clContexto = datos;
        $('#tab-clinica-item').hide();
        $('#clinicaContenido').html('');

        $.ajax({
            url: 'ajax/pagoclinica.ajax.php',
            method: 'POST',
            dataType: 'json',
            data: { accion: 'obtenerEstado', idinscripcion: datos.idInscripcion, programaID: datos.programaID },
            success: function(resp) {
                if (!resp || !resp.success || !resp.tieneClinica) {
                    // Programa sin clínicas: la pestaña no se muestra
                    if ($('#tab-clinica-link').hasClass('active')) {
                        $('#tab-orden-pago-link').tab('show');
                    }
                    return;
                }
                $('#tab-clinica-item').show();
                renderClinica(resp.orden);
                renderHistorial(resp.historial || []);
            }
        });
    };

    function recargar() {
        window.cargarClinica(clContexto);
    }

    function renderClinica(orden) {
        if (orden) {
            renderOrdenPendiente(orden);
        } else {
            renderFormularioOrden();
        }
    }

    // Sin orden pendiente: formulario para generar una nueva (monto libre)
    function renderFormularioOrden() {
        const html =
            '<div class="gp-section">' +
                '<div class="gp-monto">' +
                    '<div>' +
                        '<div class="gp-section-title mb-1"><i class="fa fa-stethoscope"></i> Monto de la clínica</div>' +
                        '<div class="gp-ayuda mt-0">Monto variable, se deposita en la cuenta CLINICA del programa</div>' +
                    '</div>' +
                    '<div class="gp-monto-input">' +
                        '<span>Bs.</span>' +
                        '<input type="number" id="clMonto" min="0.01" step="0.01" placeholder="0.00">' +
                    '</div>' +
                '</div>' +
            '</div>' +

            '<div class="gp-section">' +
                '<div class="gp-section-title"><i class="fa fa-file-invoice-dollar"></i> Datos de facturación</div>' +
                '<div class="row">' +
                    '<div class="col-lg-6 form-group">' +
                        '<label class="gp-label" for="clNombreFactura">Nombre / Razón social</label>' +
                        '<input type="text" class="form-control" id="clNombreFactura" value="' + escapar(clContexto.estudianteNombre) + '">' +
                    '</div>' +
                    '<div class="col-lg-6 form-group">' +
                        '<label class="gp-label" for="clNitCiFactura">NIT / C.I.</label>' +
                        '<input type="text" class="form-control" id="clNitCiFactura" value="' + escapar(clContexto.estudianteCi) + '">' +
                    '</div>' +
                '</div>' +

                '<div class="gp-section-title mt-2"><i class="fa fa-cog"></i> Datos de la orden</div>' +
                '<div class="row">' +
                    '<div class="col-lg-4 form-group">' +
                        '<label class="gp-label" for="clFirma">Responsable de la firma</label>' +
                        '<select class="form-control" id="clFirma"><option value="">Cargando...</option></select>' +
                    '</div>' +
                    '<div class="col-lg-4 form-group">' +
                        '<label class="gp-label" for="clCuentaPago">Cuenta de depósito</label>' +
                        '<select class="form-control" id="clCuentaPago"><option value="">Cargando cuentas...</option></select>' +
                    '</div>' +
                    '<div class="col-lg-4 form-group">' +
                        '<label class="gp-label" for="clFechaVencimiento">Fecha límite de pago</label>' +
                        '<input type="date" class="form-control" id="clFechaVencimiento" value="' + fechaHoyMas(5) + '">' +
                    '</div>' +
                '</div>' +

                '<div class="gp-actions">' +
                    '<button type="button" class="btn btn-primary" id="clBtnGenerarOrden">' +
                        '<i class="fa fa-file-invoice"></i> Generar orden de clínica' +
                    '</button>' +
                '</div>' +
            '</div>';

        $('#clinicaContenido').html(html);
        cargarCuentasClinica();
        cargarFirmantes();
    }

    // Con orden pendiente: reimprimir / anular / registrar el voucher que la confirma
    function renderOrdenPendiente(orden) {
        const html =
            '<div class="gp-section gp-orden">' +
                '<div class="gp-orden-fila">' +
                    '<div>' +
                        '<div class="gp-section-title mb-1"><i class="fa fa-stethoscope"></i> Orden de clínica pendiente</div>' +
                        '<div class="gp-orden-num">N° ' + escapar(orden.NumeroOrden) + '</div>' +
                        '<div class="gp-orden-meta">Generada el ' + escapar(orden.FechaGeneracion) +
                            (orden.NumeroCuentaDeposito ? ' &middot; Cuenta ' + escapar(orden.NumeroCuentaDeposito) : '') + '</div>' +
                    '</div>' +
                    '<div>' +
                        '<div class="gp-orden-monto">' + window.formatoBs(orden.MontoTotal) + '</div>' +
                        '<div class="text-right mt-1">' + window.chipEstadoPago('PENDIENTE') + '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="gp-actions">' +
                    '<button type="button" class="btn btn-outline-secondary" id="clBtnReimprimir" data-id="' + orden.IdOrdenPago + '">' +
                        '<i class="fa fa-print"></i> Reimprimir' +
                    '</button>' +
                    '<button type="button" class="btn btn-link text-danger ml-auto" id="clBtnAnular" data-id="' + orden.IdOrdenPago + '" data-confirmando="0">' +
                        '<i class="fa fa-times"></i> Anular orden' +
                    '</button>' +
                '</div>' +
                '<div id="clAvisoAnular" class="small text-danger mt-2 text-right" style="display:none;"></div>' +
            '</div>' +

            '<div class="gp-section">' +
                '<div class="gp-section-title"><i class="fa fa-upload"></i> Registrar voucher del pago</div>' +
                '<form id="clFormPago" enctype="multipart/form-data">' +
                    '<div class="row">' +
                        '<div class="col-lg-4 form-group">' +
                            '<label class="gp-label" for="clFechaPago">Fecha de pago <span class="gp-req">*</span></label>' +
                            '<input type="date" class="form-control" id="clFechaPago" value="' + fechaHoyMas(0) + '" required>' +
                        '</div>' +
                        '<div class="col-lg-4 form-group">' +
                            '<label class="gp-label" for="clNumeroVoucher">N° de voucher / comprobante <span class="gp-req">*</span></label>' +
                            '<input type="text" class="form-control" id="clNumeroVoucher" placeholder="Ej: 123456" required>' +
                        '</div>' +
                        '<div class="col-lg-4 form-group">' +
                            '<label class="gp-label" for="clFotoVoucher">Foto o archivo del voucher <span class="gp-req">*</span></label>' +
                            '<input type="file" class="form-control-file gp-file" id="clFotoVoucher" accept="image/*,.pdf" required>' +
                        '</div>' +
                    '</div>' +
                    '<div class="gp-actions">' +
                        '<button type="submit" id="clBtnGuardarPago" class="btn btn-primary">' +
                            '<i class="fa fa-save"></i> Guardar pago' +
                        '</button>' +
                    '</div>' +
                '</form>' +
            '</div>';

        $('#clinicaContenido').html(html);
    }

    function renderHistorial(historial) {
        // Total pagado en clínicas: se muestra aparte, nunca junto al saldo del programa
        const totalPagado = historial
            .filter(function(h) { return h.Estado === 'CONFIRMADO'; })
            .reduce(function(suma, h) { return suma + (parseFloat(h.MontoTotal) || 0); }, 0);
        $('#clinicaTotalPagado').html(totalPagado > 0 ? 'Pagado en clínicas: <span style="color:var(--gp-exito)">' + window.formatoBs(totalPagado) + '</span>' : '');

        if (!historial.length) {
            $('#tablaClinicasBody').html('<tr><td colspan="6" class="gp-vacio">Sin clínicas registradas</td></tr>');
            return;
        }

        let html = '';
        historial.forEach(function(h) {
            const btnFoto = (h.IdPagoClinica && h.TieneFoto == 1)
                ? ' <a href="ajax/pagoclinica.ajax.php?verVoucher=' + h.IdPagoClinica + '" target="_blank" class="ml-1" title="Ver voucher"><i class="fa fa-image"></i></a>'
                : '';
            html +=
                '<tr>' +
                    '<td>' + escapar(h.NumeroOrden) + '</td>' +
                    '<td class="text-right">' + window.formatoBs(h.MontoTotal) + '</td>' +
                    '<td class="text-center">' + escapar(h.FechaPago || '-') + '</td>' +
                    '<td>' + escapar(h.NumeroVoucher || '-') + btnFoto + '</td>' +
                    '<td class="text-center">' + window.chipEstadoPago(h.Estado) + '</td>' +
                    '<td class="text-center">' +
                        '<button type="button" class="btn btn-sm btn-light cl-btn-imprimir" data-id="' + h.IdOrdenPago + '" title="Imprimir orden">' +
                            '<i class="fa fa-print"></i>' +
                        '</button>' +
                    '</td>' +
                '</tr>';
        });
        $('#tablaClinicasBody').html(html);
    }

    // Cuentas CLINICA del programa (catálogo de Programas > Cuentas)
    function cargarCuentasClinica() {
        $.ajax({
            url: 'ajax/programacuenta.ajax.php',
            type: 'POST',
            data: { accion: 'listarOptionsHtml', ProgramaID: clContexto.programaID, TipoCuenta: 'CLINICA' },
            success: function(html) { $('#clCuentaPago').html(html); },
            error: function() { $('#clCuentaPago').html('<option value="">No se pudieron cargar las cuentas</option>'); }
        });
    }

    // Mismo catálogo de firmantes que la orden de cuotas
    function cargarFirmantes() {
        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            dataType: 'json',
            data: { accion: 'listarFirmantes' },
            success: function(resp) {
                let opciones = '<option value="">-- Seleccione --</option>';
                ((resp && resp.success) ? resp.firmantes : []).forEach(function(f) {
                    opciones += '<option value="' + escapar(f.Nombre) + '">' + escapar(f.Nombre) + '</option>';
                });
                $('#clFirma').html(opciones);
            },
            error: function() { $('#clFirma').html('<option value="">No se pudieron cargar los nombres</option>'); }
        });
    }

    function imprimirOrden(idOrdenPago) {
        window.open('vistas/componentes/orden-pago-pdf.php?idordenpago=' + encodeURIComponent(idOrdenPago), '_blank');
    }

    // ========================================
    // GENERAR ORDEN
    // ========================================
    $(document).on('click', '#clBtnGenerarOrden', function() {
        const monto = parseFloat($('#clMonto').val());
        if (!monto || monto <= 0) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Ingrese el monto de la clínica' });
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Generando...');

        $.ajax({
            url: 'ajax/pagoclinica.ajax.php',
            type: 'POST',
            dataType: 'json',
            data: {
                accion: 'registrarOrden',
                estudianteID: clContexto.estudianteID,
                idinscripcion: clContexto.idInscripcion,
                programaID: clContexto.programaID,
                programaCuentaID: $('#clCuentaPago').val() || '',
                montoTotal: monto,
                firma: $('#clFirma').val() || '',
                nombreFactura: ($('#clNombreFactura').val() || '').trim(),
                nitCiFactura: ($('#clNitCiFactura').val() || '').trim(),
                fechaVencimiento: $('#clFechaVencimiento').val() || ''
            },
            success: function(resp) {
                if (resp.success) {
                    Swal.fire({ icon: 'success', title: 'Orden generada', text: 'N° ' + resp.numeroOrden, timer: 1800, showConfirmButton: false })
                        .then(function() {
                            imprimirOrden(resp.idOrdenPago);
                            recargar();
                        });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: resp.mensaje || 'No se pudo generar la orden' });
                    $btn.prop('disabled', false).html('<i class="fa fa-file-invoice"></i> Generar orden de clínica');
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo generar la orden' });
                $btn.prop('disabled', false).html('<i class="fa fa-file-invoice"></i> Generar orden de clínica');
            }
        });
    });

    $(document).on('click', '#clBtnReimprimir, .cl-btn-imprimir', function() {
        imprimirOrden($(this).data('id'));
    });

    // ========================================
    // REGISTRAR VOUCHER (confirma la orden pendiente)
    // ========================================
    $(document).on('submit', '#clFormPago', function(e) {
        e.preventDefault();

        const archivo = $('#clFotoVoucher')[0].files[0];
        const numeroVoucher = ($('#clNumeroVoucher').val() || '').trim();
        if (!$('#clFechaPago').val() || !numeroVoucher || !archivo) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Complete la fecha, el N° de voucher y el archivo del voucher' });
            return;
        }

        const formData = new FormData();
        formData.append('accion', 'registrarPago');
        formData.append('idinscripcion', clContexto.idInscripcion);
        formData.append('fechaPago', $('#clFechaPago').val());
        formData.append('numeroVoucher', numeroVoucher);
        formData.append('fotoVoucher', archivo);

        const $btn = $('#clBtnGuardarPago');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: 'ajax/pagoclinica.ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    Swal.fire({ icon: 'success', title: 'Pago registrado', text: resp.mensaje, timer: 1800, showConfirmButton: false });
                    recargar();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: resp.mensaje || 'No se pudo registrar el pago' });
                    $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar pago');
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo registrar el pago' });
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar pago');
            }
        });
    });

    // ========================================
    // ANULAR ORDEN PENDIENTE (confirmación en línea: un SweetAlert dentro del modal
    // de Bootstrap ya abierto puede no responder al clic, ver ordenpagomodulos.js)
    // ========================================
    let anularTimeout = null;

    $(document).on('click', '#clBtnAnular', function() {
        const $btn = $(this);
        const $aviso = $('#clAvisoAnular');

        if ($btn.data('confirmando') !== 1) {
            $btn.data('confirmando', 1).html('<i class="fa fa-exclamation-triangle"></i> ¿Confirmar? Clic de nuevo');
            $aviso.text('Esta acción no se puede deshacer. Podrá generar una nueva orden después.').show();
            clearTimeout(anularTimeout);
            anularTimeout = setTimeout(function() {
                $btn.data('confirmando', 0).html('<i class="fa fa-times"></i> Anular orden');
                $aviso.hide();
            }, 4000);
            return;
        }

        clearTimeout(anularTimeout);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Anulando...');
        $aviso.hide();

        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            dataType: 'json',
            data: { accion: 'anularPreregistro', idOrdenPago: $btn.data('id') },
            success: function(resp) {
                if (resp.success) {
                    recargar();
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

});
