
$('.TablaUsuarios tbody').on('click', 'button.btnEditarUsuario', function(){
    let CedulaIdentidad = $(this).attr("CedulaIdentidad");
    let ApellidoPaterno = $(this).attr("ApellidoPaterno");
    let ApellidoMaterno = $(this).attr("ApellidoMaterno");
    let Nombres = $(this).attr("Nombres");
    let Direccion = $(this).attr("Direccion");
    let Celular = $(this).attr("Celular");
    let Telefono = $(this).attr("Telefono");
    

    $('#UECedulaIdentidad').val(CedulaIdentidad);
    $('#UEApellidoPaterno').val(ApellidoPaterno);
    $('#UEApellidoMaterno').val(ApellidoMaterno);
    $('#UENombres').val(Nombres);
    $('#UEDireccion').val(Direccion);
    $('#UECelular').val(Celular);
    $('#UETelefono').val(Telefono);

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
// Restablecer contraseña de un usuario del personal (solo ADM ve el botón y el
// servidor vuelve a validar el rol). Genera una nueva y la muestra para entregarla.
$(document).on('click', '.TablaUsuarios button.btnRestablecerPasswordPersonal', function(){
    const $btn = $(this);
    const idPersonal = $btn.attr('data-id-personal');
    const nombre = $btn.attr('data-nombre-completo');

    Swal.fire({
        type: 'warning',
        title: '¿Restablecer contraseña?',
        text: 'Se generará una nueva contraseña para ' + nombre + '. La anterior dejará de funcionar.',
        showCancelButton: true,
        confirmButtonText: 'Sí, restablecer',
        cancelButtonText: 'Cancelar'
    }).then(function(resultado) {
        if (!resultado.value) return; // SweetAlert2 v8: no tiene isConfirmed

        $.ajax({
            url: 'ajax/restablecerpassword.ajax.php',
            type: 'POST',
            data: { accion: 'personal', id: idPersonal },
            dataType: 'json',
            success: function(resp) {
                if (!resp.success) {
                    Swal.fire({ type: 'error', title: 'Error', text: resp.mensaje || 'No se pudo restablecer la contraseña' });
                    return;
                }

                $btn.closest('tr').find('.passwordPersonal').text(resp.password);
                Swal.fire({ type: 'success', title: 'Contraseña restablecida', text: 'Nueva contraseña: ' + resp.password });
            },
            error: function() {
                Swal.fire({ type: 'error', title: 'Error', text: 'No se pudo restablecer la contraseña' });
            }
        });
    });
});
