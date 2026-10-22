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
 * Usa los mismos estilos que la orden y la validación de cuotas (op-*, vc-* en
 * matriculados.php). El PDF se arma desde la base con vistas/componentes/orden-pago-pdf.php
 * (mismo formato oficial UTO que las demás órdenes), así la reimpresión siempre sale igual.
 */

$(document).ready(function() {

    // idInscripcion, estudianteID, estudianteNombre, estudianteCi, programaID, programaNombre
    let clContexto = {};
    // Orden mostrada (para el resumen antes de validar el voucher); si es una con voucher
    // extraviado elegida en el historial, se envía su id al registrar el pago
    let clOrdenPendiente = null;
    let clOrdenExtraviada = false;
    let clHistorial = [];

    function escapar(texto) {
        return $('<div>').text(texto == null ? '' : String(texto)).html();
    }

    function fechaHoyMas(dias) {
        const fecha = new Date();
        fecha.setDate(fecha.getDate() + dias);
        const pad = (n) => String(n).padStart(2, '0');
        return fecha.getFullYear() + '-' + pad(fecha.getMonth() + 1) + '-' + pad(fecha.getDate());
    }

    // "2026-10-13 17:39:30" o "2026-10-13" → "13/10/2026"
    function fechaCorta(valor) {
        const p = String(valor || '').match(/^(\d{4})-(\d{2})-(\d{2})/);
        return p ? p[3] + '/' + p[2] + '/' + p[1] : (valor || '-');
    }

    function filaResumen(etiqueta, valor) {
        return '<tr><th>' + etiqueta + '</th><td>' + valor + '</td></tr>';
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
        clOrdenPendiente = orden || null;
        clOrdenExtraviada = false;
        if (orden) {
            renderOrdenPendiente(orden);
        } else {
            renderFormularioOrden();
        }
    }

    // Sin orden pendiente: formulario para generar una nueva (monto libre)
    function renderFormularioOrden() {
        const hoy = fechaHoyMas(0);
        const html =
            '<div class="op-form mb-3">' +
                '<div class="op-cab">' +
                    '<div class="op-cab-titulo">Nueva orden de clínica</div>' +
                '</div>' +

                // Tres pasos en el orden en que se llenan: qué se cobra, cuándo y quién firma
                '<div class="op-cuerpo">' +
                    '<div class="cl-paso">' +
                        '<div class="cl-paso-titulo"><span class="cl-paso-num">1</span> Monto y cuenta</div>' +
                        '<div class="row">' +
                            '<div class="col-md-6 form-group">' +
                                '<label class="gp-label" for="clMonto">Monto de la clínica <span class="gp-req">*</span></label>' +
                                '<div class="gp-monto-input">' +
                                    '<span>Bs.</span>' +
                                    '<input type="number" id="clMonto" min="0.01" step="0.01" placeholder="0.00">' +
                                '</div>' +
                            '</div>' +
                            '<div class="col-md-6 form-group">' +
                                '<label class="gp-label" for="clCuentaPago">Cuenta de depósito</label>' +
                                '<select class="form-control" id="clCuentaPago"><option value="">Cargando cuentas...</option></select>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +

                    // Primero la fecha de la orden (se genera hoy) y después hasta cuándo pagar
                    '<div class="cl-paso">' +
                        '<div class="cl-paso-titulo"><span class="cl-paso-num">2</span> Fechas</div>' +
                        '<div class="row">' +
                            '<div class="col-md-6 form-group">' +
                                '<label class="gp-label" for="clFechaOrden">Fecha de la orden</label>' +
                                '<input type="date" class="form-control" id="clFechaOrden" value="' + hoy + '" max="' + hoy + '" readonly>' +
                                '<div class="custom-control custom-checkbox mt-1">' +
                                    '<input type="checkbox" class="custom-control-input" id="clChkModificarFecha">' +
                                    '<label class="custom-control-label small" for="clChkModificarFecha">Modificar fecha</label>' +
                                '</div>' +
                            '</div>' +
                            '<div class="col-md-6 form-group">' +
                                '<label class="gp-label" for="clFechaVencimiento">Fecha límite de pago</label>' +
                                '<input type="date" class="form-control" id="clFechaVencimiento" value="' + fechaHoyMas(5) + '">' +
                                '<small class="cl-ayuda">Por defecto, 5 días después de hoy</small>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +

                    '<div class="cl-paso">' +
                        '<div class="cl-paso-titulo"><span class="cl-paso-num">3</span> Firma</div>' +
                        '<div class="form-group">' +
                            '<label class="gp-label" for="clFirma">Responsable de la firma <span class="gp-req">*</span></label>' +
                            '<select class="form-control" id="clFirma"><option value="">Cargando...</option></select>' +
                        '</div>' +
                    '</div>' +

                    // Factura: ya viene llena con los datos del estudiante; solo se abre para cambiarla
                    '<div class="op-plegable">' +
                        '<button type="button" class="op-plegable-cab" data-target="#clFacturaCuerpo">' +
                            '<span>Factura: <strong id="clFacturaResumen">' + escapar(clContexto.estudianteNombre) + ' &middot; ' + escapar(clContexto.estudianteCi) + '</strong></span>' +
                            '<span class="op-plegable-accion">Cambiar</span>' +
                        '</button>' +
                        '<div class="op-plegable-cuerpo" id="clFacturaCuerpo" style="display:none;">' +
                            '<div class="row">' +
                                '<div class="col-md-7 form-group mb-md-0">' +
                                    '<label class="gp-label" for="clNombreFactura">Nombre / Razón social</label>' +
                                    '<input type="text" class="form-control" id="clNombreFactura" value="' + escapar(clContexto.estudianteNombre) + '">' +
                                '</div>' +
                                '<div class="col-md-5 form-group mb-0">' +
                                    '<label class="gp-label" for="clNitCiFactura">NIT / C.I.</label>' +
                                    '<input type="text" class="form-control" id="clNitCiFactura" value="' + escapar(clContexto.estudianteCi) + '">' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +

                '<div class="op-pie">' +
                    '<button type="button" class="btn btn-primary" id="clBtnGenerarOrden">Generar orden de clínica</button>' +
                '</div>' +
            '</div>';

        $('#clinicaContenido').html(html);
        cargarCuentasClinica();
        cargarFirmantes();
    }

    // Con orden pendiente: franja con la orden y debajo el voucher que la confirma.
    // Con extraviada = true es una orden con voucher extraviado elegida en el historial.
    function renderOrdenPendiente(orden, extraviada) {
        clOrdenPendiente = orden;
        clOrdenExtraviada = !!extraviada;
        const meta = ['Orden ' + escapar(orden.NumeroOrden), 'Generada ' + fechaCorta(orden.FechaGeneracion)];
        if (orden.NumeroCuentaDeposito) meta.push('Cuenta ' + escapar(orden.NumeroCuentaDeposito));
        if (orden.FechaVencimiento) meta.push('Vence ' + fechaCorta(orden.FechaVencimiento));

        // Pendiente: anular, voucher extraviado, reimprimir. Extraviada: solo cancelar
        const acciones = extraviada
            ? '<button type="button" class="btn btn-link mr-auto px-0" id="clBtnCancelarExtraviada">Cancelar</button>'
            : '<button type="button" class="btn btn-link text-danger px-0 btn-abrir-anular" id="clBtnAnular" data-caja="#clCajaAnular">Anular orden</button>' +
              '<button type="button" class="btn btn-link mr-auto btn-abrir-extraviado" data-caja="#clCajaExtraviado">Voucher por validar</button>' +
              '<button type="button" class="btn btn-outline-secondary" id="clBtnReimprimir" data-id="' + orden.IdOrdenPago + '"><i class="fa fa-print"></i> Reimprimir</button>';

        const html =
            '<div class="vc-orden">' +
                '<div class="vc-orden-cab' + (extraviada ? ' is-extraviada' : '') + '">' +
                    '<div>' +
                        '<div class="vc-orden-titulo">' + (extraviada
                            ? 'Clínica ' + window.chipEstadoPago('EXTRAVIADO')
                            : 'Orden de clínica pendiente') + '</div>' +
                        '<div class="vc-orden-meta">' + meta.join(' &middot; ') + '</div>' +
                    '</div>' +
                    '<div class="vc-orden-monto">' + window.formatoBs(orden.MontoTotal) + '</div>' +
                '</div>' +
            '</div>' +

            '<div class="vc-validar">' +
                '<form id="clFormPago" enctype="multipart/form-data" novalidate>' +
                    '<div class="vc-campos">' +
                        '<div class="vc-campo">' +
                            '<label class="gp-label" for="clNumeroVoucher">N° de voucher</label>' +
                            '<input type="text" class="form-control vc-input-numero" id="clNumeroVoucher" autocomplete="off" required data-voucher-partes>' +
                        '</div>' +
                        '<div class="vc-campo vc-campo-foto">' +
                            '<label class="gp-label">Voucher</label>' +
                            // Miniatura (clic = ver en grande) + Archivo; captura-foto.js agrega "Cámara"
                            '<div class="vc-foto">' +
                                '<div class="vc-miniatura" id="clPreviewFoto" title="Sin foto"><i class="fa fa-image"></i></div>' +
                                '<label class="btn btn-sm btn-outline-secondary mb-0" for="clFotoVoucher"><i class="fa fa-upload"></i> Archivo</label>' +
                                '<input type="file" class="d-none" id="clFotoVoucher" accept="image/*,.pdf" data-captura-foto required>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="vc-pie" style="gap:8px;flex-wrap:wrap;">' +
                        acciones +
                        '<button type="submit" id="clBtnGuardarPago" class="btn btn-primary">' + textoBotonValidar(orden) + '</button>' +
                    '</div>' +
                '</form>' +
                (extraviada ? '' : window.cajaAnularOrden('clCajaAnular', orden.IdOrdenPago) + window.cajaVoucherExtraviado('clCajaExtraviado', orden.IdOrdenPago)) +
            '</div>';

        $('#clinicaContenido').html(html);
    }

    function textoBotonValidar(orden) {
        return 'Validar pago de ' + window.formatoBs(orden.MontoTotal);
    }

    function renderHistorial(historial) {
        // Total pagado en clínicas: se muestra aparte, nunca junto al saldo del programa
        const totalPagado = historial
            .filter(function(h) { return h.Estado === 'CONFIRMADO'; })
            .reduce(function(suma, h) { return suma + (parseFloat(h.MontoTotal) || 0); }, 0);
        $('#clinicaTotalPagado').html(totalPagado > 0 ? 'Pagado: <span style="color:var(--gp-exito)">' + window.formatoBs(totalPagado) + '</span>' : '');

        clHistorial = historial;
        if (!historial.length) {
            $('#tablaClinicasBody').html('<tr><td colspan="7" class="gp-vacio">Sin clínicas registradas</td></tr>');
            return;
        }

        let html = '';
        historial.forEach(function(h) {
            // Igual que en el historial de cuotas: la foto se ve en grande junto al N°;
            // un PDF se abre en otra pestaña
            let btnFoto = '';
            if (h.IdPagoClinica && h.TieneFoto == 1) {
                const url = 'ajax/pagoclinica.ajax.php?verVoucher=' + h.IdPagoClinica;
                btnFoto = h.EsPdf == 1
                    ? ' <a href="' + url + '" target="_blank" class="vc-ver-foto" title="Ver voucher (PDF)"><i class="fa fa-file-pdf"></i></a>'
                    : ' <a href="#" class="cl-ver-foto vc-ver-foto" data-url="' + url + '" data-nvoucher="' + escapar(h.NumeroVoucher || '') + '" title="Ver foto del voucher"><i class="fa fa-image"></i></a>';
            }
            html +=
                '<tr>' +
                    '<td>' + escapar(h.NumeroOrden) + '</td>' +
                    '<td class="text-right">' + window.formatoBs(h.MontoTotal) + '</td>' +
                    '<td class="text-center">' + fechaCorta(h.FechaGeneracion) + '</td>' +
                    '<td class="text-center">' + (h.FechaPago ? fechaCorta(h.FechaPago) : '-') + '</td>' +
                    '<td>' + (h.Estado === 'EXTRAVIADO'
                        // Voucher extraviado: se registra cuando Tesoro dé el código
                        ? '<button type="button" class="btn btn-sm btn-outline-primary cl-btn-registrar-extraviada" data-id="' + h.IdOrdenPago + '">Registrar voucher</button>'
                        : escapar(h.NumeroVoucher || '-') + btnFoto) + '</td>' +
                    '<td class="text-center"' + (h.MotivoExtraviado ? ' title="' + escapar(h.MotivoExtraviado) + '"' : '') + '>' + window.chipEstadoPago(h.Estado) + '</td>' +
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

    // Mismo catálogo de firmantes que la orden de cuotas; se preselecciona la última
    // firma usada en este navegador (igual que en cuotas)
    function cargarFirmantes() {
        const ultima = localStorage.getItem('opUltimaFirma') || '';
        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            dataType: 'json',
            data: { accion: 'listarFirmantes' },
            success: function(resp) {
                let opciones = '<option value="">-- Seleccione --</option>';
                ((resp && resp.success) ? resp.firmantes : []).forEach(function(f) {
                    opciones += '<option value="' + escapar(f.Nombre) + '"' + (f.Nombre === ultima ? ' selected' : '') + '>' + escapar(f.Nombre) + '</option>';
                });
                $('#clFirma').html(opciones);
            },
            error: function() { $('#clFirma').html('<option value="">No se pudieron cargar los nombres</option>'); }
        });
    }

    function imprimirOrden(idOrdenPago) {
        window.open('vistas/componentes/orden-pago-pdf.php?idordenpago=' + encodeURIComponent(idOrdenPago), '_blank');
    }

    // ---- Interacción del formulario nuevo ----

    $(document).on('click', '#clinicaContenido .op-plegable-cab', function() {
        const $cab = $(this);
        $($cab.data('target')).slideToggle(150);
        $cab.toggleClass('is-abierto');
    });

    $(document).on('input', '#clNombreFactura, #clNitCiFactura', function() {
        $('#clFacturaResumen').html(escapar(($('#clNombreFactura').val() || '').trim() || '—') + ' &middot; ' + escapar(($('#clNitCiFactura').val() || '').trim() || '—'));
    });

    // Al desmarcar "Modificar fecha" la fecha de la orden vuelve a hoy
    $(document).on('change', '#clChkModificarFecha', function() {
        $('#clFechaOrden').prop('readonly', !this.checked);
        if (this.checked) {
            $('#clFechaOrden').trigger('focus');
        } else {
            $('#clFechaOrden').val(fechaHoyMas(0));
        }
    });

    // ========================================
    // GENERAR ORDEN: se valida, se muestra el resumen y recién al confirmar se guarda
    // ========================================
    $(document).on('click', '#clBtnGenerarOrden', function() {
        const monto = parseFloat($('#clMonto').val());
        const firma = $('#clFirma').val() || '';
        const fechaOrden = $('#clFechaOrden').val() || '';
        const faltan = [];
        if (!monto || monto <= 0) faltan.push('el monto');
        if (!firma) faltan.push('el responsable de la firma');
        if (!fechaOrden || fechaOrden > fechaHoyMas(0)) faltan.push('una fecha de orden válida (no posterior a hoy)');
        if (faltan.length) {
            Swal.fire({ type: 'warning', title: 'Faltan datos', text: 'Complete ' + faltan.join(', ') + '.' });
            return;
        }

        const datos = {
            accion: 'registrarOrden',
            estudianteID: clContexto.estudianteID,
            idinscripcion: clContexto.idInscripcion,
            programaID: clContexto.programaID,
            programaCuentaID: $('#clCuentaPago').val() || '',
            montoTotal: monto,
            firma: firma,
            nombreFactura: ($('#clNombreFactura').val() || '').trim(),
            nitCiFactura: ($('#clNitCiFactura').val() || '').trim(),
            fechaVencimiento: $('#clFechaVencimiento').val() || '',
            fechaGeneracion: fechaOrden
        };

        const cuentaTexto = datos.programaCuentaID ? $('#clCuentaPago option:selected').text().trim() : '';
        Swal.fire({
            type: 'question', // SweetAlert2 v8
            title: 'Verifique la orden de clínica',
            customClass: { popup: 'vc-confirmar' },
            html:
                '<table class="vc-confirmar-tabla">' +
                    filaResumen('Monto', '<strong style="font-size:18px;color:#1e3c72;">' + window.formatoBs(monto) + '</strong>') +
                    filaResumen('Programa', escapar(clContexto.programaNombre)) +
                    filaResumen('Cuenta', cuentaTexto ? escapar(cuentaTexto) : '<span style="color:#b7791f;">Cuenta CLINICA principal del programa</span>') +
                    filaResumen('Fecha de la orden', fechaCorta(fechaOrden)) +
                    filaResumen('Fecha límite', datos.fechaVencimiento ? fechaCorta(datos.fechaVencimiento) : '-') +
                    filaResumen('Firma', escapar(firma)) +
                    filaResumen('Factura', escapar(datos.nombreFactura || '-') + ' &middot; ' + escapar(datos.nitCiFactura || '-')) +
                '</table>',
            showCancelButton: true,
            confirmButtonText: 'Sí, generar',
            cancelButtonText: 'Revisar',
            reverseButtons: true
        }).then(function(result) {
            if (result.value) generarOrden(datos);
        });
    });

    function generarOrden(datos) {
        localStorage.setItem('opUltimaFirma', datos.firma);
        const $btn = $('#clBtnGenerarOrden');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Generando...');

        $.ajax({
            url: 'ajax/pagoclinica.ajax.php',
            type: 'POST',
            dataType: 'json',
            data: datos,
            success: function(resp) {
                if (resp.success) {
                    Swal.fire({ type: 'success', title: 'Orden generada', text: 'N° ' + resp.numeroOrden, timer: 1800, showConfirmButton: false })
                        .then(function() {
                            imprimirOrden(resp.idOrdenPago);
                            recargar();
                        });
                } else {
                    Swal.fire({ type: 'error', title: 'Error', text: resp.mensaje || 'No se pudo generar la orden' });
                    $btn.prop('disabled', false).html('Generar orden de clínica');
                }
            },
            error: function() {
                Swal.fire({ type: 'error', title: 'Error', text: 'No se pudo generar la orden' });
                $btn.prop('disabled', false).html('Generar orden de clínica');
            }
        });
    }

    // Clínica con voucher extraviado: el formulario de arriba pasa a esa orden
    $(document).on('click', '.cl-btn-registrar-extraviada', function() {
        const id = String($(this).data('id'));
        const orden = clHistorial.filter(function(h) { return String(h.IdOrdenPago) === id; })[0];
        if (!orden) return;
        renderOrdenPendiente(orden, true);
        $('#clinicaContenido')[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
        $('#clNumeroVoucher_largo').trigger('focus');
    });
    $(document).on('click', '#clBtnCancelarExtraviada', function() {
        recargar();
    });

    $(document).on('click', '#clBtnReimprimir, .cl-btn-imprimir', function() {
        imprimirOrden($(this).data('id'));
    });

    // Foto del voucher del historial, en grande junto a su N° (modal de cuotas)
    $(document).on('click', '.cl-ver-foto', function(e) {
        e.preventDefault();
        window.verFotoVoucherGrande($(this).data('url'), String($(this).attr('data-nvoucher') || ''));
    });

    // ========================================
    // REGISTRAR VOUCHER (confirma la orden pendiente)
    // ========================================

    // Miniatura del voucher apenas se elige el archivo
    $(document).on('change', '#clFotoVoucher', function() {
        const archivo = this.files && this.files[0];
        const $preview = $('#clPreviewFoto');
        const vaciar = function() { $preview.removeClass('is-lleno').attr('title', 'Sin foto').html('<i class="fa fa-image"></i>'); };

        if (!archivo) { vaciar(); return; }
        if (archivo.type === 'application/pdf') {
            $preview.addClass('is-lleno').attr('title', archivo.name).html('<i class="fa fa-file-pdf"></i>');
            return;
        }
        if (!archivo.type.startsWith('image/')) { vaciar(); return; }

        const lector = new FileReader();
        lector.onload = function(e) {
            $preview.addClass('is-lleno').attr('title', 'Ver en grande').html(
                '<img src="' + e.target.result + '" alt="Voucher" ' +
                'onclick="window.verFotoVoucherGrande(this.src, $(\'#clNumeroVoucher\').val())">'
            );
        };
        lector.readAsDataURL(archivo);
    });

    // Antes de guardar: resumen con la fecha del depósito (igual que en cuotas)
    $(document).on('submit', '#clFormPago', function(e) {
        e.preventDefault();

        const archivo = $('#clFotoVoucher')[0].files[0];
        const numeroVoucher = ($('#clNumeroVoucher').val() || '').trim();
        const faltan = [];
        if (!numeroVoucher) faltan.push('el N° de voucher');
        if (!archivo) faltan.push('la foto del voucher');
        if (faltan.length) {
            Swal.fire({ type: 'warning', title: 'Faltan datos', text: 'Complete ' + faltan.join(' y ') + '.' });
            return;
        }

        const hoy = fechaHoyMas(0);
        const $foto = $('#clPreviewFoto img');
        Swal.fire({
            title: 'Confirmar pago de clínica',
            customClass: { popup: 'vc-confirmar' },
            html:
                '<table class="vc-confirmar-tabla">' +
                    filaResumen('Orden', escapar(clOrdenPendiente ? clOrdenPendiente.NumeroOrden : '-')) +
                    filaResumen('Monto', '<strong>' + window.formatoBs(clOrdenPendiente ? clOrdenPendiente.MontoTotal : 0) + '</strong>') +
                    filaResumen('N° de voucher', '<strong>' + escapar(numeroVoucher) + '</strong>') +
                    filaResumen('<label for="clConfirmarFecha" class="mb-0">Fecha del depósito</label>',
                        '<input type="date" class="form-control vc-confirmar-input" id="clConfirmarFecha" max="' + hoy + '" value="' + hoy + '">') +
                '</table>' +
                '<div class="vc-confirmar-fecha" id="clConfirmarAviso"></div>' +
                ($foto.length ? '<img class="vc-confirmar-foto" src="' + $foto.attr('src') + '" alt="Voucher">' : ''),
            showCancelButton: true,
            confirmButtonText: 'Validar y guardar',
            cancelButtonText: 'Volver',
            reverseButtons: true,
            onOpen: function() {
                const $fecha = $('#clConfirmarFecha');
                const avisar = function() {
                    const esHoy = $fecha.val() === hoy;
                    $('#clConfirmarAviso').toggleClass('is-hoy', esHoy).html(
                        '<i class="fa ' + (esHoy ? 'fa-exclamation-triangle' : 'fa-calendar-check') + '"></i><span>' +
                        (esHoy
                            ? 'Está puesta la fecha de <strong>hoy</strong>. Si el depósito se hizo otro día, cámbiela por la del voucher.'
                            : 'Verifique que coincida con la fecha del voucher.') +
                        '</span>');
                };
                $fecha.on('input change', function() {
                    Swal.resetValidationMessage();
                    avisar();
                });
                avisar();
                $fecha.trigger('focus');
            },
            preConfirm: function() {
                const fecha = $('#clConfirmarFecha').val();
                if (!fecha) {
                    Swal.showValidationMessage('Elija la fecha del depósito');
                    return false;
                }
                if (fecha > hoy) {
                    Swal.showValidationMessage('La fecha del depósito no puede ser posterior a hoy');
                    return false;
                }
                return fecha;
            }
        }).then(function(resultado) {
            if (resultado.value) enviarPago(resultado.value, numeroVoucher, archivo);
        });
    });

    function enviarPago(fechaPago, numeroVoucher, archivo) {
        const formData = new FormData();
        formData.append('accion', 'registrarPago');
        formData.append('idinscripcion', clContexto.idInscripcion);
        formData.append('fechaPago', fechaPago);
        formData.append('numeroVoucher', numeroVoucher);
        formData.append('fotoVoucher', archivo);
        if (clOrdenExtraviada && clOrdenPendiente) {
            formData.append('idOrdenPago', clOrdenPendiente.IdOrdenPago);
        }

        const $btn = $('#clBtnGuardarPago');
        const textoBoton = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Validando...');

        $.ajax({
            url: 'ajax/pagoclinica.ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    Swal.fire({ type: 'success', title: 'Pago validado', text: resp.mensaje, timer: 1800, showConfirmButton: false });
                    recargar();
                } else {
                    Swal.fire({ type: 'warning', title: 'Atención', text: resp.mensaje || 'No se pudo registrar el pago' });
                    $btn.prop('disabled', false).html(textoBoton);
                }
            },
            error: function() {
                Swal.fire({ type: 'error', title: 'Error', text: 'No se pudo registrar el pago' });
                $btn.prop('disabled', false).html(textoBoton);
            }
        });
    }

    // ANULAR ORDEN PENDIENTE: la caja con el motivo y el envío están en ordenpagomodulos.js
    // (window.cajaAnularOrden); aquí solo se recarga la pestaña de clínicas
    $(document).on('orden-anulada', function(e, idCaja) {
        if (idCaja === 'clCajaAnular') recargar();
    });

});
