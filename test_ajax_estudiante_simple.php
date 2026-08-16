<!DOCTYPE html>
<html>
<head>
    <title>Test AJAX - Estudiante Simple</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { font-family: Arial; padding: 20px; }
        .resultado { margin: 20px 0; padding: 15px; background: #f0f0f0; border-radius: 5px; }
        .error { background: #ffcccc; }
        .success { background: #ccffcc; }
        button { padding: 10px 20px; margin: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Test AJAX - Obtener Datos de Estudiante</h1>

    <h3>Seleccione un estudiante para probar:</h3>
    <select id="selectEstudiante" style="padding: 5px; width: 400px;">
        <option value="">Seleccione un estudiante...</option>
        <?php
        require_once 'modelos/conexion.modelo.php';
        require_once 'controladores/estudiantes.controlador.php';
        require_once 'modelos/estudiantes.modelo.php';

        $Lista = new EstudiantesControladores();
        $Lista->EstudianteActivoControlador();
        ?>
    </select>

    <button onclick="probarAjax()">Probar AJAX Directo</button>
    <button onclick="limpiar()">Limpiar</button>

    <div id="resultado"></div>

    <script>
    function log(mensaje, tipo = 'info') {
        const div = $('<div class="resultado"></div>');
        if (tipo === 'error') div.addClass('error');
        if (tipo === 'success') div.addClass('success');

        div.html('<strong>' + new Date().toLocaleTimeString() + '</strong><br>' + mensaje);
        $('#resultado').append(div);
    }

    function limpiar() {
        $('#resultado').empty();
    }

    function probarAjax() {
        const estudianteID = $('#selectEstudiante').val();

        if (!estudianteID) {
            log('⚠️ Debe seleccionar un estudiante primero', 'error');
            return;
        }

        log('📤 Enviando petición AJAX...<br>Estudiante ID: ' + estudianteID);

        $.ajax({
            url: 'ajax/estudiantes.ajax.php',
            type: 'POST',
            data: { idestudiante: estudianteID },
            dataType: 'json',
            beforeSend: function() {
                log('⏳ Esperando respuesta del servidor...');
            },
            success: function(response) {
                log('✅ <strong>RESPUESTA RECIBIDA:</strong><br>' +
                    '<pre>' + JSON.stringify(response, null, 2) + '</pre>', 'success');

                if (response && !response.error) {
                    // Construir datos como en ordenpago.php
                    const nombreCompleto = (response.Apaterno || '') + ' ' +
                                         (response.Amaterno || '') + ' ' +
                                         (response.Nombre || '');
                    const ci = (response.Ci || '') +
                              (response.Complemento ? '-' + response.Complemento : '') +
                              ' ' + (response.Exp || '');

                    log('📋 <strong>DATOS PROCESADOS:</strong><br>' +
                        'Nombre completo: <strong>' + nombreCompleto.trim() + '</strong><br>' +
                        'CI: <strong>' + ci + '</strong><br>' +
                        'Correo: ' + (response.Correo || '-') + '<br>' +
                        'Celular: ' + (response.Celular || '-'), 'success');
                } else {
                    log('❌ Error en la respuesta: ' + (response.error || 'Error desconocido'), 'error');
                }
            },
            error: function(xhr, status, error) {
                log('❌ <strong>ERROR AJAX:</strong><br>' +
                    'Status: ' + status + '<br>' +
                    'Error: ' + error + '<br>' +
                    '<strong>Respuesta del servidor:</strong><br>' +
                    '<pre>' + xhr.responseText + '</pre>', 'error');
            }
        });
    }

    // También probar con evento change del select
    $('#selectEstudiante').on('change', function() {
        const id = $(this).val();
        if (id) {
            log('🔄 Evento CHANGE detectado - ID: ' + id);
        }
    });
    </script>
</body>
</html>
