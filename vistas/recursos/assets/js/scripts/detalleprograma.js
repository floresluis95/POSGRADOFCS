$("#tabladetalle tbody").on("click", "button#btnDetallePrograma", function(){
  var id = $(this).attr("idcod");
  var datos = new FormData();
  datos.append('id', id);
  $('#mitabla tbody').empty();
  $.ajax({
      url: "ajax/detalleprograma.ajax.php",
      method: "POST",
      data: datos,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function(response) {
          console.log(response);
          
              for (let index = 0; index < response.length; index++) {
              var material = '<tr>' +
              
                  '<td>' + response[index]['ProgramaID'] + '</td>' +
                  '<td>' + response[index]['NombrePrograma'] + '</td>' +
                  '<td>' + response[index]['GradoAcademico'] + '</td>' +
                  '<td>' + response[index]['DuracionMeses'] + '</td>' +
                    '<td>' + response[index]['Modulos'] + '</td>' +
                    '<td>' + response[index]['FechaInicio'] + '</td>' +
                    '<td>' + response[index]['Costo'] + '</td>' +
                  
                  '</tr>';
              $(".detalleprograma").append(material);
          }

      } 
  })

});