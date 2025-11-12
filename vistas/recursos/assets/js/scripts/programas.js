      
          $(document).on('click', '#btnDetallePrograma', function() {
              var idprograma = $(this).attr("idcod");

              $.ajax({
                  url: "ajax/programa.ajax.php",
                  method: "POST",
                  data: { idprograma: idprograma },
                  dataType: "json",
                  success: function(respuesta) {
                      // Mostrar en el panel o modal
                      $("#detalleNombre").text(respuesta.NombrePrograma);
                      $("#detalleGrado").text(respuesta.GradoAcademico);
                      $("#detalleDuracion").text(respuesta.DuracionMeses);
                      $("#detalleModulos").text(respuesta.Modulos);
                      $("#detalleFecha").text(respuesta.FechaInicio);
                      $("#detalleSede").text(respuesta.Sede);
                      $("#detalleCosto").text(respuesta.Costo);
                      $("#detalleDetalle").text(respuesta.Detalle);
                      
                      // Abrir modal si no se abre automáticamente
                      $("#DetallePrograma").modal("show");
                  }
              });
          });
          // subir programa
$(document).ready(function() {
  $(document).on('click', '.btnSubir', function() {
    let id = $(this).data('idprograma');
    $('#idProgramaSubir').val(id);
  });
});



 // bajar programa
$(document).ready(function() {

  // Cuando se hace clic en el botón rojo (papelera)
  $(document).on('click', '.btnDarBaja', function() {
    let id = $(this).data('idprograma');
    $('#idProgramaBaja').val(id); // se pasa el ID al input oculto
  });
});
 // eliminar programa
$(document).ready(function() {
  $(document).on('click', '.btnEliminarprograma', function() {
    let id = $(this).data('idprograma');
    $('#idProgramaEliminar').val(id); // se pasa el ID al input oculto
  });
});


