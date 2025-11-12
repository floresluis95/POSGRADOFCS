function FlitrarCliente(elemento) {
    let Valor = elemento.value;
    datos = new FormData();
    datos.append('IdPropietario', Valor);

    $.ajax({
        url: "ajax/cliente.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(response) {
            if (response)
            {
                console.log(response);
                $('#SNombres').val(response['nombre']);
            
                $('#SApellidop').val(response['paterno']);
                
                $('#SApellidom').val(response['materno']);
                
                $('#STelefono').val(response['telefono']);   
            }
            else {
                $('#SNombres').val('');
                $('#SApellidop').val('');
                $('#SApellidom').val('');
                $('#STelefono').val('');
            }
        }
    })
}
$('#SelectMarca').change(function(){
    // alert('Hola');
    const IdMarca = $(this).val();
    // imprimir en consola el valor, si tiene valor
    console.log(IdMarca);
    $('#TIIdMarca').val(IdMarca);
    // realizar peticion ajax
    DatosMarca = new FormData();
    DatosMarca.append('IdMarca', IdMarca);

    $.ajax({
        // Don de se enviaran los datos
        url: "ajax/filtrar.ajax.php",
        // metodo de envio GET - POST
        method: "POST",
        // envio los datos
        data: DatosMarca,
        cache: false,
        contentType: false,
        processData: false,
        // tipo de datos que se resivira
        dataType: "json",
        success: function(response){
            $('#SelectTipo').children().remove();
            for (let i = 0; i < response.length; i++) {

                const DescripcionTipo = response[i]['desctipo'];
                const IdTipo = response[i]['idtipo'];
                
                let SelectTipo = '<option value="'+ IdTipo +'">'+ DescripcionTipo +'</option>';
                
                console.log(SelectTipo);
                
                $('#SelectTipo').append(SelectTipo);
                
            }           
        }
    });
    
});

$('#idsolicitud').change(function(){
    // alert('Hola');
    const Idsol = $(this).val();
    
    // imprimir en consola el valor, si tiene valor
    // realizar peticion ajax
    DatosSol = new FormData();
    DatosSol.append('Idsol', Idsol);

    $.ajax({
        // Don de se enviaran los datos
        url: "ajax/filtrarsolicitud.ajax.php",
        // metodo de envio GET - POST
        method: "POST",
        // envio los datos
        data: DatosSol,
        cache: false,
        contentType: false,
        processData: false,
        // tipo de datos que se resivira
        dataType: "json",
        success: function(response){
            $('#cisol').val(response['ci']);
            $('#nombresol').val(response['nombre']);
            $('#maternosol').val(response['paterno']);
            $('#paternosol').val(response['materno']);
            $('#placasol').val(response['nroplaca']);
            $('#marcasol').val(response['descmarca']);
            $('#tipomsol').val(response['tipomotor']);
            $('#fechasol').val(response['fechasolicitud']);
            
        }
    });
    
});





