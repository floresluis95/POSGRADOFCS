<!DOCTYPE html>
<html>
<head>
    <title>Test OrdenPago - Selección de Estudiante</title>
    <link href="vistas/recursos/assets/vendors/general/select2/dist/css/select2.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="vistas/recursos/assets/vendors/general/select2/dist/js/select2.full.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
        .hidden { display: none; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        table th { background-color: #f8f9fa; }
        #console { background: #000; color: #0f0; padding: 10px; font-family: monospace; height: 300px; overflow-y: auto; }
    </style>
</head>
<body>
    <h1>Test - Selección de Estudiante en Orden de Pago</h1>

    <div class="section">
        <h3>1. Seleccionar Estudiante</h3>
        <label>Buscar Estudiante por CI:</label>
        <select class="form-control kt-select2-general" name="idcliente" id="selectEstudiante" style="width: 100%; padding: 5px;">
            <option value="">Buscar por cédula de identidad...</option>
            <?php
            require_once 'modelos/conexion.modelo.php';

            try {
                $conexion = Conexion::Conectar();
                $stmt = $conexion->query("
                    SELECT EstudianteID, Ci, Complemento, Exp, Nombre, Apaterno, Amaterno
                    FROM estudiante
                    WHERE Estado = 1
                    ORDER BY Apaterno, Amaterno, Nombre
                ");

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $nombreCompleto = trim($row['Apaterno'] . ' ' . $row['Amaterno'] . ' ' . $row['Nombre']);
                    $ci = $row['Ci'];
                    if (!empty($row['Complemento'])) $ci .= '-' . $row['Complemento'];
                    if (!empty($row['Exp'])) $ci .= ' ' . $row['Exp'];

                    echo "<option value='{$row['EstudianteID']}'>$nombreCompleto - CI: $ci</option>";
                }
            } catch (Exception $e) {
                echo "<option value=''>Error al cargar estudiantes</option>";
            }
            ?>
        </select>
    </div>

    <div class="section" id="tablaEstudiante" style="display: none;">
        <h3>2. Datos del Estudiante Seleccionado</h3>
        <table>
            <tr>
                <th width="25%">Nombre Completo:</th>
                <td id="datosNombre">-</td>
                <th width="20%">CI:</th>
                <td id="datosCI">-</td>
            </tr>
            <tr>
                <th>Correo:</th>
                <td id="datosCorreo">-</td>
                <th>Celular:</th>
                <td id="datosCelular">-</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>3. Console Log</h3>
        <div id="console"></div>
    </div>

    <script>
    $(document).ready(function() {
        // Función para escribir en consola
        function log(mensaje, datos = null) {
            const timestamp = new Date().toLocaleTimeString();
            let texto = `[${timestamp}] ${mensaje}`;
            if (datos) {
                texto += ': ' + JSON.stringify(datos, null, 2);
            }
            $('#console').append(texto + '\n');
            $('#console').scrollTop($('#console')[0].scrollHeight);
            console.log(mensaje, datos || '');
        }

        log('=== INICIANDO TEST ===');

        // Inicializar Select2
        try {
            $('.kt-select2-general').select2({
                placeholder: "Buscar por cédula de identidad...",
                allowClear: true
            });
            log('✓ Select2 inicializado correctamente');
        } catch (e) {
            log('✗ Error al inicializar Select2', e.message);
        }

        // Event listener para el select
        $('#selectEstudiante').on('select2:select change', function(e) {
            const estudianteID = $(this).val();

            log('=== EVENTO DISPARADO ===');
            log('Tipo de evento', e.type);
            log('Estudiante ID seleccionado', estudianteID);

            if (estudianteID) {
                log('Iniciando llamada AJAX...');

                $.ajax({
                    url: 'ajax/estudiantes.ajax.php',
                    type: 'POST',
                    data: { idestudiante: estudianteID },
                    dataType: 'json',
                    beforeSend: function() {
                        log('→ Enviando petición AJAX');
                    },
                    success: function(response) {
                        log('✓ Respuesta recibida del servidor', response);

                        if (response && !response.error) {
                            // Construir nombre completo
                            const nombreCompleto = (response.Apaterno || '') + ' ' +
                                                  (response.Amaterno || '') + ' ' +
                                                  (response.Nombre || '');

                            // Construir CI
                            const ci = (response.Ci || '') +
                                      (response.Complemento ? '-' + response.Complemento : '') +
                                      ' ' + (response.Exp || '');

                            log('Nombre completo construido', nombreCompleto.trim());
                            log('CI construido', ci);

                            // Llenar tabla de datos
                            $('#datosNombre').text(nombreCompleto.trim());
                            $('#datosCI').text(ci);
                            $('#datosCorreo').text(response.Correo || '-');
                            $('#datosCelular').text(response.Celular || '-');

                            log('✓ Datos llenados en la tabla');

                            // Mostrar tabla
                            $('#tablaEstudiante').slideDown();
                            log('✓ Tabla mostrada con slideDown()');

                        } else {
                            log('✗ Error en la respuesta', response.error || 'Error desconocido');
                            alert('Error: ' + (response.error || 'No se encontraron datos del estudiante'));
                        }
                    },
                    error: function(xhr, status, error) {
                        log('✗ Error AJAX', {
                            status: status,
                            error: error,
                            responseText: xhr.responseText
                        });
                        alert('Error al obtener datos del estudiante: ' + error);
                    }
                });
            } else {
                log('Campo vacío - ocultando tabla');
                $('#tablaEstudiante').slideUp();
            }
        });

        log('✓ Event listeners registrados');
        log('=== TEST LISTO ===');
    });
    </script>
</body>
</html>
