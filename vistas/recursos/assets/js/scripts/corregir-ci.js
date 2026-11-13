/**
 * Botón "Corregir C.I." (solo ADM) de los formularios de edición de Estudiantes, Docentes
 * y Usuarios (personal). Va en el HTML así:
 *   <button type="button" class="btnCorregirCi" data-tipo="estudiante|docente|personal"
 *           data-id="#inputConElId" data-ci="#inputConElCi">
 * Abre su propia ventana (Bootstrap, no SweetAlert: Docentes usa SweetAlert v1 y el resto v2),
 * guarda con ajax/correccionci.ajax.php y recarga la página para que la lista muestre el C.I. nuevo.
 */
document.addEventListener('DOMContentLoaded', function () {
    var $ = window.jQuery;
    if (!$) return;

    var html =
        '<div class="modal fade" id="modalCorregirCi" tabindex="-1" role="dialog" aria-hidden="true" style="z-index:2050;">' +
        '  <div class="modal-dialog modal-dialog-centered" role="document"><div class="modal-content">' +
        '    <form id="formCorregirCi" autocomplete="off">' +
        '      <div class="modal-header" style="background:#f0ad4e;">' +
        '        <h5 class="modal-title text-white"><i class="fas fa-id-card mr-1"></i> Corregir C.I.</h5>' +
        '        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>' +
        '      </div>' +
        '      <div class="modal-body">' +
        '        <p class="mb-2">C.I. actual: <strong id="corregirCiActual"></strong></p>' +
        '        <div class="form-group mb-2">' +
        '          <label for="corregirCiNuevo">C.I. correcto <span class="text-danger">*</span></label>' +
        '          <input type="text" class="form-control" id="corregirCiNuevo" maxlength="20" required>' +
        '        </div>' +
        '        <small class="text-muted d-block">Solo el número (el complemento va en su campo). Si inicia sesión con su C.I., ' +
        '          su usuario también cambia al nuevo; la contraseña no cambia.</small>' +
        '        <div class="alert mt-3 mb-0" id="corregirCiMensaje" style="display:none;"></div>' +
        '      </div>' +
        '      <div class="modal-footer">' +
        '        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>' +
        '        <button type="submit" class="btn btn-warning" id="btnGuardarCorregirCi"><i class="fas fa-save"></i> Corregir</button>' +
        '      </div>' +
        '    </form>' +
        '  </div></div>' +
        '</div>';
    $('body').append(html);

    var actual = null; // { tipo, id }

    function mensaje(texto, clase) {
        $('#corregirCiMensaje').attr('class', 'alert mt-3 mb-0 alert-' + clase).text(texto).show();
    }

    $(document).on('click', '.btnCorregirCi', function () {
        var $b = $(this);
        var id = $($b.data('id')).val();
        var ci = $($b.data('ci')).val();
        if (!id) return;
        actual = { tipo: $b.data('tipo'), id: id };
        $('#corregirCiActual').text(ci || '—');
        $('#corregirCiNuevo').val(ci || '');
        $('#corregirCiMensaje').hide();
        $('#btnGuardarCorregirCi').prop('disabled', false);
        // Se cierra el formulario de edición: al corregir se recarga la página
        $b.closest('.modal').modal('hide');
        $('#modalCorregirCi').modal('show');
    });

    $('#modalCorregirCi').on('shown.bs.modal', function () {
        $('#corregirCiNuevo').trigger('focus').select();
    });

    $('#formCorregirCi').on('submit', function (e) {
        e.preventDefault();
        if (!actual) return;
        var $btn = $('#btnGuardarCorregirCi').prop('disabled', true);
        $.post('ajax/correccionci.ajax.php', {
            accion: 'corregir', tipo: actual.tipo, id: actual.id, ci: $('#corregirCiNuevo').val()
        }, function (resp) {
            if (resp && resp.success) {
                mensaje(resp.mensaje, 'success');
                setTimeout(function () { location.reload(); }, 2200);
            } else {
                mensaje((resp && resp.mensaje) || 'No se pudo corregir', 'warning');
                $btn.prop('disabled', false);
            }
        }, 'json').fail(function (xhr) {
            var r = xhr.responseJSON;
            mensaje((r && r.mensaje) || 'No se pudo conectar con el servidor', 'danger');
            $btn.prop('disabled', false);
        });
    });
});
