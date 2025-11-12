




$("#tabladetalle tbody").on("click", "button#btndetallekit", function(){
  var id = $(this).attr("idcod");
  var datos = new FormData();
  datos.append('id', id);
  $('#mitabla tbody').empty();
  $.ajax({
      url: "ajax/detallenota.ajax.php",
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
              
                  '<td>' + response[index]['seriekit'] + '</td>' +
                  '<td>' + response[index]['descripcion'] + '</td>' +
                  '<td>' + response[index]['tipo'] + '</td>' +
                  '<td>' + response[index]['notadeentrega'] + '</td>' +
                  
                  '</tr>';
              $(".detallenotakit").append(material);
          }

      } 
  })

});
//cilindros
$("#tabladetallec tbody").on("click", "button#btndetallecil", function(){
    $('#notac').val('hola');
    var id = $(this).attr("idcodc");
    var datos = new FormData();
    datos.append('id', id);
    $('#mitablac tbody').empty();
    $.ajax({
        url: "ajax/detallenotac.ajax.php",
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
                
                    '<td>' + response[index]['seriecilindro'] + '</td>' +
                    '<td>' + response[index]['descripcioncil'] + '</td>' +
                    '<td>' + response[index]['capacidad'] + '</td>' +
                    '<td>' + response[index]['añofab'] + '</td>' +
                    '<td>' + response[index]['notadeentrega'] + '</td>' +

                    '</tr>';
                $(".detallenotacil").append(material);
            }
  
        } 
    })
  
  });


