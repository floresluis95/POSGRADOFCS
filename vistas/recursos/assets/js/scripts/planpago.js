/**
 * Plan de Pago del Programa (posgrado)
 * Permite ver/definir el TIPO de plan (Regular, Contado con descuento, Grupal) y su
 * % de descuento desde Matriculados. El pago en si de cada modulo se registra siempre
 * desde "Registrar Pago de Modulo" (pagomodulo); el descuento definido aqui se aplica
 * automaticamente al costo de cada modulo pendiente.
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
    // VISTA: plan ya definido (resumen; el pago va por modulo)
    // ========================================
    function renderPlanExistente(plan) {
        let html = '' +
            '<div class="chip-summary mb-3" style="padding:10px 14px;background:#f4f6fb;border-left:3px solid #667eea;border-radius:6px;">' +
                '<div style="display:flex;flex-wrap:wrap;gap:16px;font-size:13px;">' +
                    '<span>' + badgeTipoPlan(plan.TipoPlan) + '</span>' +
                    '<span>Costo del programa: <strong>Bs. ' + parseFloat(plan.CostoTotalPrograma).toFixed(2) + '</strong></span>' +
                    '<span>Descuento: <strong class="text-success">' + parseFloat(plan.PorcentajeDescuento).toFixed(2) + '% (Bs. ' + parseFloat(plan.MontoDescuento).toFixed(2) + ')</strong></span>' +
                    '<span>Total a pagar: <strong class="text-primary">Bs. ' + parseFloat(plan.MontoTotalPagar).toFixed(2) + '</strong></span>' +
                '</div>' +
                (plan.TipoPlan === 'GRUPAL' ? '<div class="mt-2" style="font-size:13px;">Grupo: <strong>' + (plan.CodigoGrupo || '-') + '</strong> (' + plan.CantidadInscritosGrupo + ' inscritos)</div>' : '') +
            '</div>' +
            '<div class="alert alert-light border" style="font-size:13px;">' +
                '<i class="fa fa-info-circle text-primary"></i> El descuento de este plan ya se aplica automáticamente al costo de cada módulo. ' +
                'Para registrar los pagos, use <strong>Inscribir a Módulo</strong> en el menú de Acciones de este estudiante.' +
            '</div>';

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
                '<div class="col-lg-6 form-group">' +
                    '<label class="small font-weight-bold mb-1">Tipo de Plan</label>' +
                    '<select class="form-control form-control-sm" id="mpTipoPago">' +
                        '<option value="REGULAR">Plan Regular (paga cada módulo al iniciarlo)</option>' +
                        '<option value="DESCUENTO">Plan al Contado (con descuento)</option>' +
                    '</select>' +
                '</div>' +
                '<div class="col-lg-6 form-group">' +
                    '<label class="small font-weight-bold mb-1">% Descuento</label>' +
                    '<input type="number" class="form-control form-control-sm" id="mpPorcentajeDescuento" min="0" max="100" step="0.01" value="0">' +
                '</div>' +
            '</div>' +
            '<small class="text-muted d-block mb-2"><i class="fa fa-users"></i> Para un <strong>Plan Grupal</strong> (varios inscritos), use el botón "Plan Grupal" en la barra superior para seleccionar a todos los integrantes del grupo a la vez.</small>' +
            '<small class="text-muted d-block mb-3"><i class="fa fa-money"></i> El pago de cada módulo se registra después, desde <strong>Inscribir a Módulo</strong>, aplicando este descuento automáticamente.</small>' +
            '<div class="text-right">' +
                '<button type="button" class="btn btn-sm btn-success" id="mpBtnGuardarPlan"><i class="fa fa-save"></i> Guardar Plan de Pago</button>' +
            '</div>';

        $('#planPagoContenido').html(html);

        $('#mpTipoPago').on('change', function() {
            if ($(this).val() !== 'DESCUENTO') {
                $('#mpPorcentajeDescuento').val(0);
            }
        });
        $('#mpBtnGuardarPlan').on('click', guardarNuevoPlan);
    }

    function guardarNuevoPlan() {
        const porcentaje = parseFloat($('#mpPorcentajeDescuento').val()) || 0;

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
                porcentajeDescuento: porcentaje
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
    // PLAN GRUPAL: seleccionar a los estudiantes matriculados que entran en el plan
    // ========================================
    let pgCandidatos = []; // últimos candidatos cargados (idInscripcion -> costoPrograma)

    $('#btnAbrirPlanGrupal').on('click', function() {
        $('#pgFiltroPrograma').val('');
        $('#pgPorcentajeDescuento').val(0);
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

        if (idsInscripcion.length < 2) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Seleccione al menos 2 estudiantes para el plan grupal' });
            return;
        }

        $.ajax({
            url: 'ajax/ordenpago.ajax.php',
            type: 'POST',
            data: {
                accion: 'registrarPlanGrupal',
                idsInscripcion: JSON.stringify(idsInscripcion),
                porcentajeDescuento: porcentaje
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
