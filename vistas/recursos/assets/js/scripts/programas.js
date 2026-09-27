// El detalle del programa (#btnDetallePrograma) lo maneja vistas/componentes/programas.php:
// aquí había una copia vieja que hacía una segunda consulta y sobrescribía los datos sin formato.

// subir programa
$(document).ready(function() {
  $(document).on('click', '.btnSubir', function() {
    let id = $(this).data('idprograma');
    $('#idProgramaSubir').val(id);
  });
});

// bajar programa
$(document).ready(function() {
  $(document).on('click', '.btnDarBaja', function() {
    let id = $(this).data('idprograma');
    $('#idProgramaBaja').val(id); // se pasa el ID al input oculto
  });
});
