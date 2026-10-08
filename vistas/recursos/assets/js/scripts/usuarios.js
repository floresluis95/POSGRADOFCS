
$('.TablaUsuarios tbody').on('click', 'button.btnEditarUsuario', function(){
    let CedulaIdentidad = $(this).attr("CedulaIdentidad");
    let ApellidoPaterno = $(this).attr("ApellidoPaterno");
    let ApellidoMaterno = $(this).attr("ApellidoMaterno");
    let Nombres = $(this).attr("Nombres");
    let Direccion = $(this).attr("Direccion");
    let Celular = $(this).attr("Celular");
    let Telefono = $(this).attr("Telefono");
    let Correo = $(this).attr("Correo");


    $('#UEIdPersonal').val($(this).attr("IdPersonal"));
    $('#UECedulaIdentidad').val(CedulaIdentidad);
    $('#UEApellidoPaterno').val(ApellidoPaterno);
    $('#UEApellidoMaterno').val(ApellidoMaterno);
    $('#UENombres').val(Nombres);
    $('#UEDireccion').val(Direccion);
    $('#UECelular').val(Celular);
    $('#UETelefono').val(Telefono);
    $('#UECorreo').val(Correo);

});



$('.TablaUsuarios tbody').on('click', 'button.btncambiar', function(){
    let idtrabajo = $(this).attr("idtrabajo");
    $('#idtrabajo').val(idtrabajo);
});

$('.TablaUsuarios tbody').on('click', 'button.btnusr', function(){
    let idusr = $(this).attr("idusr");
    $('#idusr').val(idusr);
});

$('.TablaUsuarios tbody').on('click', 'button.btnsubir', function(){
    let idusrs = $(this).attr("idusrs");
    $('#idusrs').val(idusrs);
});


$('.TablaUsuarios tbody').on('click', 'button.btntec', function(){
    let idusrs = $(this).attr("idusrs");
    $('#idusrs').val(idusrs);
});
// Misma regla que UsuarioControladores::ValidarPasswordPersonal (el servidor la vuelve a validar)
function errorPasswordPersonal(password, confirmacion) {
    if (password.length < 8) return 'La contraseña debe tener al menos 8 caracteres';
    if (!/[A-Za-z]/.test(password) || !/[0-9]/.test(password)) return 'La contraseña debe tener al menos una letra y un número';
    if (password !== confirmacion) return 'Las contraseñas no coinciden';
    return null;
}

// Registrar personal: las dos contraseñas deben coincidir antes de enviar
$(document).on('input', '#UIPassword, #UIPasswordConfirmar', function() {
    const confirmacion = $('#UIPasswordConfirmar').val();
    $('#UIPasswordAviso').toggle(confirmacion !== '' && confirmacion !== $('#UIPassword').val());
});

$(document).on('submit', '#ModalInsertarUsuario form', function(e) {
    if ($('#UIPassword').val() !== $('#UIPasswordConfirmar').val()) {
        e.preventDefault();
        $('#UIPasswordAviso').show();
        $('#UIPasswordConfirmar').focus();
    }
});

// Restablecer contraseña de un usuario del personal (solo ADM ve el botón y el
// servidor vuelve a validar el rol). El administrador escribe la contraseña nueva.
$(document).on('click', '.TablaUsuarios button.btnRestablecerPasswordPersonal', function(){
    const $btn = $(this);
    const idPersonal = $btn.attr('data-id-personal');
    const nombre = $btn.attr('data-nombre-completo');

    Swal.fire({
        title: 'Restablecer contraseña',
        html:
            '<p class="mb-3">Nueva contraseña para <strong>' + $('<div>').text(nombre).html() + '</strong>. La anterior dejará de funcionar.</p>' +
            '<input type="password" id="swalPassword" class="swal2-input" placeholder="Nueva contraseña" autocomplete="new-password">' +
            '<input type="password" id="swalPasswordConfirmar" class="swal2-input" placeholder="Confirmar contraseña" autocomplete="new-password">' +
            '<small class="text-muted">Mínimo 8 caracteres, con al menos una letra y un número</small>',
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Guardar contraseña',
        cancelButtonText: 'Cancelar',
        onOpen: function() { $('#swalPassword').focus(); },
        preConfirm: function() {
            const password = $('#swalPassword').val();
            const confirmacion = $('#swalPasswordConfirmar').val();
            const error = errorPasswordPersonal(password, confirmacion);
            if (error) {
                Swal.showValidationMessage(error);
                return false;
            }
            return { password: password, confirmacion: confirmacion };
        }
    }).then(function(resultado) {
        if (!resultado.value) return; // SweetAlert2 v8: no tiene isConfirmed

        $.ajax({
            url: 'ajax/restablecerpassword.ajax.php',
            type: 'POST',
            data: { accion: 'personal', id: idPersonal, password: resultado.value.password, confirmacion: resultado.value.confirmacion },
            dataType: 'json',
            success: function(resp) {
                if (!resp.success) {
                    Swal.fire({ type: 'error', title: 'Error', text: resp.mensaje || 'No se pudo restablecer la contraseña' });
                    return;
                }

                Swal.fire({ type: 'success', title: 'Contraseña restablecida', text: 'Entregue la nueva contraseña a ' + nombre + '.' });
            },
            error: function() {
                Swal.fire({ type: 'error', title: 'Error', text: 'No se pudo restablecer la contraseña' });
            }
        });
    });
});
