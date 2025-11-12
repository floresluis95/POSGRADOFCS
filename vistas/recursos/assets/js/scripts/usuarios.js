
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