/**
 * Plan de Pago del Programa (posgrado)
 * Permite ver/definir el plan (Regular, Descuento, Grupal) y sus cuotas desde
 * Matriculados, y generar la Orden de Pago o registrar el pago de cada cuota.
 */

$(document).ready(function() {

    let planContexto = {}; // idInscripcion, estudianteID, programaID, costoPrograma

    function badgeTipoPlan(tipoPlan) {
        const textos = {
            REGULAR: 'PLAN REGULAR',
            DESCUENTO: 'PLAN AL CONTADO (DESCUENTO)',
            GRUPAL: 'PLAN GRUPAL (VARIOS INSCRITOS)'
        };
        return '<span class="badge badge-info">' + (textos[tipoPlan] || tipoPlan) + '</span>';
    }

    function badgeEstadoCuota(estado) {
        if (estado === 'PAGADO') return '<span class="badge badge-success">PAGADO</span>';
        if (estado === 'VENCIDO') return '<span class="badge badge-danger">VENCIDO</span>';
        if (estado === 'ANULADO') return '<span class="badge badge-secondary">ANULADO</span>';
        return '<span class="badge badge-warning">PENDIENTE</span>';
    }

    // ========================================
    // ABRIR MODAL: cargar plan existente o mostrar formulario para crearlo
    // ========================================
    $(document).on('click', '.btn-plan-pago-programa', function(e) {
        e.preventDefault();

        planContexto = {
            idInscripcion: $(this).data('idinscripcion'),
            estudianteID: $(this).data('estudiante-id'),
            estudianteNombre: $(this).data('estudiante-nombre'),
            estudianteCi: $(this).data('estudiante-ci'),
            programaID: $(this).data('programa-id'),
            programaNombre: $(this).data('programa-nombre'),
            costoPrograma: parseFloat($(this).data('costo-programa')) || 0
        };

        $('#planEstudianteNombre').text(planContexto.estudianteNombre);
        $('#planPagoContenido').html('<div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-2x text-primary"></i></div>');
        $('#modalPlanPago').modal('show');

        cargarPlanPago();
    });

    function cargarPlanPago() {
        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: { accion: 'obtenerPlanPagoPrograma', idInscripcion: planContexto.idInscripcion },
            dataType: 'json',
            success: function(resp) {
                if (!resp.success) {
                    $('#planPagoContenido').html('<div class="alert alert-danger">' + (resp.mensaje || 'No se pudo cargar el plan de pago') + '</div>');
                    return;
                }
                if (resp.tienePlan) {
                    renderPlanExistente(resp.plan);
                } else {
                    renderFormularioNuevoPlan();
                }
            },
            error: function() {
                $('#planPagoContenido').html('<div class="alert alert-danger">No se pudo cargar el plan de pago</div>');
            }
        });
    }

    // ========================================
    // VISTA: plan ya definido (resumen + cuotas)
    // ========================================
    function renderPlanExistente(plan) {
        const montoPagado = plan.Cuotas
            .filter(c => c.Estado === 'PAGADO')
            .reduce((acc, c) => acc + parseFloat(c.MontoCuota), 0);
        const saldo = parseFloat(plan.MontoTotalPagar) - montoPagado;

        let html = '' +
            '<div class="chip-summary mb-3" style="padding:10px 14px;background:#f4f6fb;border-left:3px solid #667eea;border-radius:6px;">' +
                '<div style="display:flex;flex-wrap:wrap;gap:16px;font-size:13px;">' +
                    '<span>' + badgeTipoPlan(plan.TipoPlan) + '</span>' +
                    '<span>Costo del programa: <strong>Bs. ' + parseFloat(plan.CostoTotalPrograma).toFixed(2) + '</strong></span>' +
                    '<span>Descuento: <strong class="text-success">' + parseFloat(plan.PorcentajeDescuento).toFixed(2) + '% (Bs. ' + parseFloat(plan.MontoDescuento).toFixed(2) + ')</strong></span>' +
                    '<span>Total a pagar: <strong class="text-primary">Bs. ' + parseFloat(plan.MontoTotalPagar).toFixed(2) + '</strong></span>' +
                    '<span>Pagado: <strong class="text-success">Bs. ' + montoPagado.toFixed(2) + '</strong></span>' +
                    '<span>Saldo: <strong class="' + (saldo > 0 ? 'text-danger' : 'text-success') + '">Bs. ' + saldo.toFixed(2) + '</strong></span>' +
                '</div>' +
                (plan.TipoPlan === 'GRUPAL' ? '<div class="mt-2" style="font-size:13px;">Grupo: <strong>' + (plan.CodigoGrupo || '-') + '</strong> (' + plan.CantidadInscritosGrupo + ' inscritos)</div>' : '') +
            '</div>' +
            '<div class="table-responsive">' +
                '<table class="table table-sm table-bordered">' +
                    '<thead class="thead-light"><tr>' +
                        '<th class="text-center">N°</th><th>Monto (Bs.)</th><th>Vencimiento</th>' +
                        '<th class="text-center">Estado</th><th>Pago</th><th class="text-center">Acciones</th>' +
                    '</tr></thead><tbody>';

        plan.Cuotas.forEach(function(c) {
            const pendiente = c.Estado === 'PENDIENTE' || c.Estado === 'VENCIDO';
            html += '<tr>' +
                '<td class="text-center">' + c.NumeroCuota + '</td>' +
                '<td>' + parseFloat(c.MontoCuota).toFixed(2) + '</td>' +
                '<td>' + (c.FechaVencimiento || '<span class="text-muted"><i>Antes de iniciar el módulo</i></span>') + '</td>' +
                '<td class="text-center">' + badgeEstadoCuota(c.Estado) + '</td>' +
                '<td>' + (c.Estado === 'PAGADO' ? ('Voucher ' + (c.NumeroVoucher || '-') + ' (' + (c.FechaPago || '') + ')') : '-') + '</td>' +
                '<td class="text-center">' +
                    '<button type="button" class="btn btn-xs btn-outline-success btn-generar-orden-cuota" data-id-cuota="' + c.IdCuota + '" title="Generar Orden de Pago"><i class="fa fa-file-invoice"></i></button> ' +
                    (pendiente ? '<button type="button" class="btn btn-xs btn-outline-primary btn-pagar-cuota" data-id-cuota="' + c.IdCuota + '" title="Registrar Pago"><i class="fa fa-check"></i></button>' : '') +
                '</td>' +
            '</tr>';
        });

        html += '</tbody></table></div>';

        $('#planPagoContenido').html(html);
    }

    // ========================================
    // VISTA: formulario para crear el plan (aún no existe)
    // ========================================
    function renderFormularioNuevoPlan() {
        const html = '' +
            '<p class="text-muted"><i class="fa fa-info-circle"></i> Este estudiante todavía no tiene un plan de pago del programa. Costo total del programa: ' +
            '<strong class="text-primary">Bs. ' + planContexto.costoPrograma.toFixed(2) + '</strong></p>' +
            '<div class="row">' +
                '<div class="col-lg-4 form-group">' +
                    '<label class="small font-weight-bold mb-1">Tipo de Plan</label>' +
                    '<select class="form-control form-control-sm" id="mpTipoPago">' +
                        '<option value="REGULAR">Plan Regular (cuotas)</option>' +
                        '<option value="DESCUENTO">Plan al Contado (con descuento)</option>' +
                    '</select>' +
                '</div>' +
                '<div class="col-lg-4 form-group" id="mpGrupoNumeroCuotas">' +
                    '<label class="small font-weight-bold mb-1">N° de Cuotas</label>' +
                    '<input type="number" class="form-control form-control-sm" id="mpNumeroCuotas" min="1" max="36" value="1">' +
                '</div>' +
                '<div class="col-lg-4 form-group">' +
                    '<label class="small font-weight-bold mb-1">% Descuento</label>' +
                    '<input type="number" class="form-control form-control-sm" id="mpPorcentajeDescuento" min="0" max="100" step="0.01" value="0">' +
                '</div>' +
            '</div>' +
            '<small class="text-muted d-block mb-2"><i class="fa fa-users"></i> Para un <strong>Plan Grupal</strong> (varios inscritos), use el botón "Plan Grupal" en la barra superior para seleccionar a todos los integrantes del grupo a la vez.</small>' +
            '<div class="text-right mb-2">' +
                '<button type="button" class="btn btn-xs btn-outline-primary" id="mpBtnGenerarCuotas"><i class="fa fa-list-ol"></i> Generar Cuotas</button>' +
            '</div>' +
            '<div class="table-responsive">' +
                '<table class="table table-sm table-bordered mb-1">' +
                    '<thead class="thead-light"><tr><th class="text-center" style="width:40px;">N°</th><th>Monto (Bs.)</th><th style="width:170px;">Fecha de Vencimiento</th></tr></thead>' +
                    '<tbody id="mpCuotasBody"></tbody>' +
                '</table>' +
            '</div>' +
            '<small class="d-block text-right mb-3">Total cuotas: <strong>Bs. <span id="mpTotalCuotas">0.00</span></strong> <span id="mpEstadoSuma" class="ml-1"></span></small>' +
            '<div class="text-right">' +
                '<button type="button" class="btn btn-sm btn-success" id="mpBtnGuardarPlan"><i class="fa fa-save"></i> Guardar Plan de Pago</button>' +
            '</div>';

        $('#planPagoContenido').html(html);

        $('#mpTipoPago').on('change', function() {
            const tipoPlan = $(this).val();
            $('#mpGrupoNumeroCuotas').toggle(tipoPlan === 'REGULAR');
            if (tipoPlan !== 'REGULAR') $('#mpNumeroCuotas').val(1);
            generarCuotasNuevoPlan();
        });
        $('#mpNumeroCuotas, #mpPorcentajeDescuento').on('change', generarCuotasNuevoPlan);
        $('#mpBtnGenerarCuotas').on('click', generarCuotasNuevoPlan);
        $(document).on('input', '.mp-cuota-monto', recalcularSumaNuevoPlan);
        $('#mpBtnGuardarPlan').on('click', guardarNuevoPlan);

        generarCuotasNuevoPlan();
    }

    function generarCuotasNuevoPlan() {
        const tipoPlan = $('#mpTipoPago').val();
        let porcentaje = parseFloat($('#mpPorcentajeDescuento').val()) || 0;
        if (porcentaje < 0) porcentaje = 0;
        if (porcentaje > 100) porcentaje = 100;

        const montoTotalPagar = Math.round((planContexto.costoPrograma * (1 - porcentaje / 100)) * 100) / 100;
        const numeroCuotas = (tipoPlan === 'REGULAR') ? (parseInt($('#mpNumeroCuotas').val(), 10) || 1) : 1;
        const fechaBase = new Date();

        let html = '';
        let acumulado = 0;
        for (let i = 1; i <= numeroCuotas; i++) {
            let monto;
            if (i < numeroCuotas) {
                monto = Math.round((montoTotalPagar / numeroCuotas) * 100) / 100;
                acumulado += monto;
            } else {
                monto = Math.round((montoTotalPagar - acumulado) * 100) / 100;
            }

            const fechaCuota = new Date(fechaBase);
            fechaCuota.setMonth(fechaCuota.getMonth() + (i - 1));
            const fechaStr = fechaCuota.toISOString().split('T')[0];

            html += '<tr>' +
                '<td class="text-center">' + i + '</td>' +
                '<td><input type="number" class="form-control form-control-sm mp-cuota-monto" step="0.01" min="0" value="' + monto.toFixed(2) + '"></td>' +
                '<td><input type="date" class="form-control form-control-sm mp-cuota-fecha" value="' + fechaStr + '"></td>' +
            '</tr>';
        }

        $('#mpCuotasBody').html(html);
        recalcularSumaNuevoPlan();
    }

    function recalcularSumaNuevoPlan() {
        let suma = 0;
        $('.mp-cuota-monto').each(function() {
            suma += parseFloat($(this).val()) || 0;
        });

        let porcentaje = parseFloat($('#mpPorcentajeDescuento').val()) || 0;
        const montoTotalPagar = Math.round((planContexto.costoPrograma * (1 - porcentaje / 100)) * 100) / 100;

        $('#mpTotalCuotas').text(suma.toFixed(2));

        if (Math.abs(suma - montoTotalPagar) <= 0.5) {
            $('#mpEstadoSuma').html('<span class="text-success"><i class="fa fa-check-circle"></i> Coincide (Bs. ' + montoTotalPagar.toFixed(2) + ')</span>');
        } else {
            $('#mpEstadoSuma').html('<span class="text-danger"><i class="fa fa-exclamation-circle"></i> Debe sumar Bs. ' + montoTotalPagar.toFixed(2) + '</span>');
        }
    }

    function guardarNuevoPlan() {
        const cuotas = [];
        let valido = true;

        $('#mpCuotasBody tr').each(function() {
            const monto = parseFloat($(this).find('.mp-cuota-monto').val()) || 0;
            const fecha = $(this).find('.mp-cuota-fecha').val();
            if (monto <= 0 || !fecha) valido = false;
            cuotas.push({ monto: monto, fecha: fecha });
        });

        let porcentaje = parseFloat($('#mpPorcentajeDescuento').val()) || 0;
        const montoTotalPagar = Math.round((planContexto.costoPrograma * (1 - porcentaje / 100)) * 100) / 100;
        const suma = cuotas.reduce((acc, c) => acc + c.monto, 0);

        if (!valido || cuotas.length === 0 || Math.abs(suma - montoTotalPagar) > 0.5) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Revise las cuotas: deben tener monto y fecha, y su suma debe coincidir con el monto a pagar' });
            return;
        }

        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: {
                accion: 'registrarPlanPagoPrograma',
                idInscripcion: planContexto.idInscripcion,
                estudianteID: planContexto.estudianteID,
                programaID: planContexto.programaID,
                costoTotalPrograma: planContexto.costoPrograma,
                tipoPlan: $('#mpTipoPago').val(),
                porcentajeDescuento: porcentaje,
                cuotas: JSON.stringify(cuotas)
            },
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    Swal.fire({ icon: 'success', title: 'Plan registrado', text: resp.mensaje, timer: 1800, showConfirmButton: false });
                    cargarPlanPago();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: resp.mensaje || 'No se pudo registrar el plan' });
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo registrar el plan de pago' });
            }
        });
    }

    // ========================================
    // GENERAR ORDEN DE PAGO DE UNA CUOTA (PDF)
    // ========================================
    $(document).on('click', '.btn-generar-orden-cuota', function() {
        const idCuota = $(this).data('id-cuota');

        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: { accion: 'obtenerCuotaPrograma', idCuota: idCuota },
            dataType: 'json',
            success: function(resp) {
                if (!resp.success) {
                    Swal.fire({ icon: 'error', title: 'Error', text: resp.mensaje || 'No se pudo cargar la cuota' });
                    return;
                }

                const c = resp.cuota;
                $('#ordenCuotaApaterno').val(c.Apaterno || '');
                $('#ordenCuotaAmaterno').val(c.Amaterno || '');
                $('#ordenCuotaNombres').val(c.Nombre || '');
                $('#ordenCuotaCorreo').val(c.Correo || '');
                $('#ordenCuotaCi').val(c.CiCompleto || '');
                $('#ordenCuotaCelular').val(c.Celular || '');
                $('#ordenCuotaPrograma').val(c.ProgramaTexto || '');
                $('#ordenCuotaMontoNumeral').val(parseFloat(c.MontoCuota).toFixed(2));
                $('#ordenCuotaMontoLiteral').val(c.MontoLiteral || '');
                $('#ordenCuotaVersion').val(c.Version || '');
                $('#ordenCuotaNumeroTramite').val(c.NumeroTramite || '');
                $('#ordenCuotaNombreFactura').val(((c.Apaterno || '') + ' ' + (c.Amaterno || '') + ' ' + (c.Nombre || '')).trim());
                $('#ordenCuotaNitCiFactura').val(c.CiCompleto || '');

                $('#modalGenerarOrdenCuota').modal('show');
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar la cuota' });
            }
        });
    });

    // ========================================
    // REGISTRAR PAGO DE UNA CUOTA
    // ========================================
    $(document).on('click', '.btn-pagar-cuota', function() {
        $('#formPagoCuota')[0].reset();
        $('#pagoCuotaId').val($(this).data('id-cuota'));
        $('#pagoCuotaFecha').val(new Date().toISOString().split('T')[0]);
        $('#modalPagoCuota').modal('show');
    });

    $('#formPagoCuota').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData();
        formData.append('accion', 'registrarPagoCuota');
        formData.append('idCuota', $('#pagoCuotaId').val());
        formData.append('numeroVoucher', $('#pagoCuotaVoucher').val().trim());
        formData.append('fechaPago', $('#pagoCuotaFecha').val());

        const archivoFoto = $('#pagoCuotaFoto')[0].files[0];
        if (archivoFoto) {
            formData.append('fotoVoucher', archivoFoto);
        }

        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    $('#modalPagoCuota').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Pago registrado', text: resp.mensaje, timer: 1800, showConfirmButton: false });
                    cargarPlanPago();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: resp.mensaje || 'No se pudo registrar el pago' });
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo registrar el pago de la cuota' });
            }
        });
    });

    // ========================================
    // PLAN GRUPAL: seleccionar a los estudiantes matriculados que entran en el plan
    // ========================================
    let pgCandidatos = []; // últimos candidatos cargados (idInscripcion -> costoPrograma)

    $('#btnAbrirPlanGrupal').on('click', function() {
        $('#pgFiltroPrograma').val('');
        $('#pgPorcentajeDescuento').val(0);
        $('#pgFechaVencimiento').val(new Date().toISOString().split('T')[0]);
        $('#modalPlanGrupal').modal('show');
        cargarCandidatosPlanGrupal();
    });

    $('#pgFiltroPrograma').on('change', cargarCandidatosPlanGrupal);

    function cargarCandidatosPlanGrupal() {
        $('#pgListaEstudiantes').html('<tr><td colspan="5" class="text-center text-muted py-3"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>');

        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: { accion: 'listarMatriculadosSinPlan', programaID: $('#pgFiltroPrograma').val() },
            dataType: 'json',
            success: function(resp) {
                pgCandidatos = (resp.success && resp.matriculados) ? resp.matriculados : [];

                if (pgCandidatos.length === 0) {
                    $('#pgListaEstudiantes').html('<tr><td colspan="5" class="text-center text-muted py-3">No hay estudiantes matriculados sin plan de pago del programa</td></tr>');
                    actualizarResumenPlanGrupal();
                    return;
                }

                let html = '';
                pgCandidatos.forEach(function(m) {
                    html += '<tr>' +
                        '<td class="text-center"><input type="checkbox" class="pg-check" value="' + m.idInscripcion + '" data-costo="' + (parseFloat(m.CostoPrograma) || 0) + '"></td>' +
                        '<td>' + m.NombreCompleto + '</td>' +
                        '<td>' + m.CiCompleto + '</td>' +
                        '<td>' + m.NombrePrograma + ' (' + m.CodigoPrograma + ')</td>' +
                        '<td class="text-center">Bs. ' + (parseFloat(m.CostoPrograma) || 0).toFixed(2) + '</td>' +
                    '</tr>';
                });
                $('#pgListaEstudiantes').html(html);
                actualizarResumenPlanGrupal();
            },
            error: function() {
                $('#pgListaEstudiantes').html('<tr><td colspan="5" class="text-center text-danger py-3">No se pudo cargar la lista de estudiantes</td></tr>');
            }
        });
    }

    $(document).on('change', '.pg-check', actualizarResumenPlanGrupal);
    $('#pgPorcentajeDescuento').on('input', actualizarResumenPlanGrupal);

    function actualizarResumenPlanGrupal() {
        const seleccionados = $('.pg-check:checked');
        $('#pgContadorSeleccionados').text(seleccionados.length);

        let porcentaje = parseFloat($('#pgPorcentajeDescuento').val()) || 0;
        if (porcentaje < 0) porcentaje = 0;
        if (porcentaje > 100) porcentaje = 100;

        if (seleccionados.length > 0) {
            // Referencia: costo del primer seleccionado (normalmente el mismo programa para todo el grupo)
            const costoRef = parseFloat($(seleccionados[0]).data('costo')) || 0;
            const montoPorEstudiante = Math.round((costoRef * (1 - porcentaje / 100)) * 100) / 100;
            $('#pgMontoPorEstudiante').text(montoPorEstudiante.toFixed(2));
        } else {
            $('#pgMontoPorEstudiante').text('0.00');
        }
    }

    $('#pgBtnCrearPlan').on('click', function() {
        const idsInscripcion = $('.pg-check:checked').map(function() { return $(this).val(); }).get();
        const porcentaje = parseFloat($('#pgPorcentajeDescuento').val()) || 0;
        const fechaVencimiento = $('#pgFechaVencimiento').val();

        if (idsInscripcion.length < 2) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Seleccione al menos 2 estudiantes para el plan grupal' });
            return;
        }
        if (!fechaVencimiento) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Seleccione la fecha de vencimiento' });
            return;
        }

        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: {
                accion: 'registrarPlanGrupal',
                idsInscripcion: JSON.stringify(idsInscripcion),
                porcentajeDescuento: porcentaje,
                fechaVencimiento: fechaVencimiento
            },
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    $('#modalPlanGrupal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Plan grupal creado', text: resp.mensaje, timer: 2200, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: resp.mensaje || 'No se pudo crear el plan grupal' });
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo crear el plan grupal' });
            }
        });
    });

});
