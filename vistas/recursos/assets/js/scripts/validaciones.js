var campo = $('#txtnotak').val();
if(campo === ''){
    alert("El campo esta vacío");
   return false;
   }else{
    //Las validaciones que necesitas hacer
   }

   function validaNumericos(event) {
    if(event.charCode >= 48 && event.charCode <= 57){
      return true;
     }
     return false;        
}

document.getElementById('#fechatrabajo').value = new Date().toDateInputValue();
$(document).ready( function() {
  $('#fechatrabajo').val(new Date().toDateInputValue());
});​