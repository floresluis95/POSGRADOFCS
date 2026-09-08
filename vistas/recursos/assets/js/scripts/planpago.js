/**
 * Plan de Pago del Programa (posgrado) — Plan Grupal
 * El plan Regular/Contado se define en Pre-Registro y queda definitivo al validar el
 * voucher de matrícula (ver ordenpagoprincipal.modelo.php); ya no se administra desde
 * Matriculados. Lo único que sigue armándose aquí es el Plan Grupal, para los
 * estudiantes que eligieron "Grupal (definir después)" en Pre-Registro.
 */

$(document).ready(function() {

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
