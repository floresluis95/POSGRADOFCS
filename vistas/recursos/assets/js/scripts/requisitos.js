/**
 * Requisitos de matriculación (Matriculados).
 * Ventana "Requisitos": se abre desde la celda de requisitos de cada fila o
 * desde el menú de acciones. Marca qué documentos entregó el estudiante
 * (Trámite B, fotocopia de carnet, ...), con fecha y observación. No bloquea
 * nada: la tabla solo avisa lo que falta.
 */
$(document).ready(function() {

    let idInscripcionActual = null;
    const hoy = new Date().toLocaleDateString('sv-SE');

    function esc(texto) {
        return $('<div>').text(texto == null ? '' : String(texto)).html();
    }

    function renderLista(requisitos) {
        let html = '';
        requisitos.forEach(function(r) {
            const entregado = parseInt(r.Entregado) === 1;
            html +=
                '<div class="rq-item' + (entregado ? ' is-entregado' : '') + '" data-id="' + r.IdRequisito + '">' +
                    '<label class="rq-check">' +
                        '<input type="checkbox" class="rq-entregado"' + (entregado ? ' checked' : '') + '>' +
                        '<span class="rq-caja"><i class="fa fa-check"></i></span>' +
                        '<span class="rq-nombre">' + esc(r.Nombre) +
                            (parseInt(r.Obligatorio) === 1 ? '' : ' <small>(opcional)</small>') +
                        '</span>' +
                    '</label>' +
                    '<div class="rq-datos">' +
                        '<input type="date" class="form-control rq-fecha" max="' + hoy + '" value="' + esc(r.FechaEntrega || '') + '"' +
                            (entregado ? '' : ' disabled') + ' title="Fecha de entrega">' +
                        '<input type="text" class="form-control rq-obs" maxlength="255" placeholder="Observación (opcional)" value="' + esc(r.Observacion || '') + '">' +
                    '</div>' +
                    (r.ResponsableRegistro && entregado
                        ? '<small class="rq-registro">Registrado por ' + esc(r.ResponsableRegistro) + '</small>'
                        : '') +
                '</div>';
        });
        $('#rqLista').html(html || '<div class="rq-vacio">No hay requisitos configurados.</div>');
        actualizarContador();
    }

    function actualizarContador() {
        const total = $('#rqLista .rq-item').length;
        const entregados = $('#rqLista .rq-entregado:checked').length;
        $('#rqContador').html('<strong>' + entregados + ' de ' + total + '</strong> entregados')
            .toggleClass('is-completo', total > 0 && entregados === total);
    }

    // Abrir desde la celda de la tabla o desde el menú de acciones
    $(document).on('click', '.btn-requisitos', function(e) {
        e.preventDefault();
        idInscripcionActual = $(this).data('idinscripcion');
        $('#rqEstudiante').text($(this).data('estudiante-nombre'));
        $('#rqLista').html('<div class="rq-vacio"><i class="fa fa-spinner fa-spin"></i> Cargando requisitos...</div>');
        $('#rqContador').html('');
        $('#modalRequisitos').modal('show');

        $.ajax({
            url: 'ajax/requisito.ajax.php',
            type: 'POST',
            dataType: 'json',
            data: { accion: 'obtener', idInscripcion: idInscripcionActual },
            success: function(resp) {
                if (!resp.success) {
                    $('#rqLista').html('<div class="rq-vacio text-danger">' + esc(resp.mensaje || 'No se pudieron cargar los requisitos') + '</div>');
                    return;
                }
                renderLista(resp.requisitos || []);
            },
            error: function() {
                $('#rqLista').html('<div class="rq-vacio text-danger">No se pudieron cargar los requisitos</div>');
            }
        });
    });

    // Marcar/desmarcar: al marcar se propone la fecha de hoy
    $(document).on('change', '#rqLista .rq-entregado', function() {
        const $item = $(this).closest('.rq-item');
        const marcado = this.checked;
        const $fecha = $item.find('.rq-fecha');
        $item.toggleClass('is-entregado', marcado);
        $fecha.prop('disabled', !marcado);
        if (marcado && !$fecha.val()) {
            $fecha.val(hoy);
        }
        actualizarContador();
    });

    $('#rqMarcarTodos').on('click', function() {
        $('#rqLista .rq-entregado:not(:checked)').prop('checked', true).trigger('change');
    });

    $('#rqGuardar').on('click', function() {
        if (!idInscripcionActual) return;

        const datos = { accion: 'guardar', idInscripcion: idInscripcionActual };
        $('#rqLista .rq-item').each(function() {
            const id = $(this).data('id');
            datos['requisitos[' + id + '][entregado]'] = $(this).find('.rq-entregado').is(':checked') ? '1' : '0';
            datos['requisitos[' + id + '][fecha]'] = $(this).find('.rq-fecha').val() || '';
            datos['requisitos[' + id + '][observacion]'] = ($(this).find('.rq-obs').val() || '').trim();
        });

        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: 'ajax/requisito.ajax.php',
            type: 'POST',
            dataType: 'json',
            data: datos,
            success: function(resp) {
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar requisitos');
                if (!resp.success) {
                    Swal.fire({ type: 'error', title: 'Error', text: resp.mensaje || 'No se pudieron guardar los requisitos' });
                    return;
                }
                $('#modalRequisitos').modal('hide');
                Swal.fire({
                    type: 'success',
                    title: resp.completo ? 'Requisitos completos' : 'Requisitos guardados',
                    text: resp.completo
                        ? 'El estudiante entregó todos los requisitos.'
                        : 'Entregados ' + resp.entregados + ' de ' + resp.total + '. Quedan pendientes ' + (resp.total - resp.entregados) + '.',
                    timer: 2200,
                    showConfirmButton: false
                });
                if (typeof window.refrescarTablaMatriculados === 'function') {
                    window.refrescarTablaMatriculados();
                }
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar requisitos');
                Swal.fire({ type: 'error', title: 'Error', text: 'No se pudieron guardar los requisitos' });
            }
        });
    });
});
