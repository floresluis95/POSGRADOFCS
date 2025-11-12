
$(document).ready(function() {
  $('#gradoAcademico').on('change', function() {
    var grado = $(this).val();

    if (grado !== '') {
      $.ajax({
        url: 'ajax/programa.ajax.php', // archivo que procesará la solicitud
        type: 'POST',
        data: { grado: grado },
        dataType: 'json',
        
        success: function(response) {
          $('#programa').empty();
          $('#programa').append('<option value="">Seleccione un programa</option>');

          if (response.length > 0) {
            response.forEach(function(p) {
             $('#programa').append('<option value="' + p.ProgramaID + '">' + p.Codigo + ' - ' + p.NombrePrograma + '</option>');
            });
          } else {
            $('#programa').append('<option value="">No hay programas disponibles</option>');
          }
        },
        error: function() {
          alert('Error al obtener los programas.');
        }
      });
    } else {
      $('#programa').empty();
      $('#programa').append('<option value="">Seleccione un grado académico primero</option>');
    }
  });
});
