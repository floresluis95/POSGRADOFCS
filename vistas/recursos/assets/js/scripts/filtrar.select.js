// Filtrar material por categorias
$(document).ready(function(){
    recargarLista();
    $('#lista1').change(function(){
        recargarLista();
    });
})
function recargarLista(){
    $.ajax({
        type:"POST",
        url:"ajax/filtrar.ajax.php",
        data:"continente=" + $('#lista1').val(),
    success:function(r){
        $('#select2lista').html(r);
    }   
 });
}