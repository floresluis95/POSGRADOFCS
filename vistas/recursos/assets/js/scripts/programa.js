
$(document).ready(function() {
  $('#gradoAcademico').on('change', function() {
    var grado = $(this).val();

    // Limpiar y ocultar detalles del programa anterior
    $('#detalle-programa').slideUp();
    $('input[name="montoMatricula"]').val('');

    if (grado !== '') {
      $.ajax({
        url: 'ajax/programa.ajax.php',
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
        error: function(xhr, status, error) {
          console.error('Error al obtener programas:', error);
          console.error('Respuesta del servidor:', xhr.responseText);
          alert('Error al obtener los programas. Por favor, revisa la consola para más detalles.');
        }
      });
    } else {
      $('#programa').empty();
      $('#programa').append('<option value="">Seleccione un grado académico primero</option>');
    }
  });

  // Cargar detalles del programa cuando se selecciona
  $('#programa').on('change', function() {
    var programaId = $(this).val();

    if (programaId !== '') {
      $.ajax({
        url: 'ajax/programa.ajax.php',
        type: 'POST',
        data: { idprograma: programaId },
        dataType: 'json',
        success: function(respuesta) {
          // Mostrar detalles del programa
          $('#detalle-nombre-programa').text(respuesta.NombrePrograma);
          $('#detalle-codigo').text(respuesta.Codigo);
          $('#detalle-duracion').text(respuesta.DuracionMeses + ' meses');
          $('#detalle-modulos').text(respuesta.Modulos);
          $('#detalle-costo-total').text('Bs. ' + parseFloat(respuesta.Costo).toFixed(2));

          // Mostrar y autocompletar costo de matrícula
          var costoMatricula = respuesta.CostoMatricula ? parseFloat(respuesta.CostoMatricula) : 0;
          $('#detalle-costo-matricula').text('Bs. ' + costoMatricula.toFixed(2));

          // Autocompletar el campo de pago matrícula
          $('input[name="montoMatricula"]').val(costoMatricula.toFixed(2));

          $('#detalle-sede').text(respuesta.Sede);
          $('#detalle-inicio').text(respuesta.FechaInicio);
          $('#detalle-tipo').text(respuesta.GradoAcademico);

          // Mostrar el panel de detalles
          $('#detalle-programa').slideDown();
        },
        error: function(xhr, status, error) {
          console.error('Error al obtener detalles:', error);
          $('#detalle-programa').slideUp();
        }
      });
    } else {
      $('#detalle-programa').slideUp();
      // Limpiar campo de monto matrícula si no hay programa seleccionado
      $('input[name="montoMatricula"]').val('');
    }
  });
});
