<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Debug - Vista de Inscripción</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; }
        .debug { background: #f4f4f4; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 5px solid #2196F3; }
        .error { border-left-color: #f44336; background: #ffebee; }
        .success { border-left-color: #4CAF50; background: #e8f5e9; }
        h2 { color: #1976D2; }
        button { padding: 10px 20px; margin: 5px; background: #2196F3; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #1976D2; }
        #console { background: #1e1e1e; color: #00ff00; padding: 15px; border-radius: 5px; font-family: monospace; max-height: 400px; overflow-y: auto; }
    </style>
</head>
<body>
    <h1>🔍 Debug - Sistema de Inscripción</h1>

    <div class="debug">
        <h3>1. Verificar Elementos HTML</h3>
        <button onclick="checkElements()">Verificar Elementos</button>
        <div id="elements-result"></div>
    </div>

    <div class="debug">
        <h3>2. Test AJAX - Obtener Programas por Grado</h3>
        <select id="testGrado">
            <option value="">Selecciona un grado</option>
            <option value="DIPLOMADO">DIPLOMADO</option>
            <option value="MAESTRIA">MAESTRÍA</option>
            <option value="ESPECIALIDAD">ESPECIALIDAD</option>
        </select>
        <button onclick="testProgramasPorGrado()">Test AJAX Programas</button>
        <div id="programas-result"></div>
    </div>

    <div class="debug">
        <h3>3. Test AJAX - Obtener Detalle de Programa</h3>
        <input type="number" id="testProgramaId" placeholder="ID del Programa (ej: 1)">
        <button onclick="testDetallePrograma()">Test AJAX Detalle</button>
        <div id="detalle-result"></div>
    </div>

    <div class="debug">
        <h3>4. Test Completo - Cargar y Mostrar</h3>
        <button onclick="testCompleto()">Ejecutar Test Completo</button>
        <div id="test-completo-result"></div>
    </div>

    <h2>📋 Consola de Debug</h2>
    <div id="console"></div>

    <script>
        function log(message, type = 'info') {
            const console = document.getElementById('console');
            const time = new Date().toLocaleTimeString();
            const color = type === 'error' ? '#ff5252' : type === 'success' ? '#4CAF50' : '#00ff00';
            console.innerHTML += `<div style="color: ${color}">[${time}] ${message}</div>`;
            console.scrollTop = console.scrollHeight;
        }

        function checkElements() {
            log('=== Verificando Elementos HTML ===');
            const elements = [
                '#gradoAcademico',
                '#programa',
                '#detalle-programa',
                '#detalle-nombre-programa',
                '#detalle-codigo',
                '#detalle-duracion',
                '#detalle-modulos',
                '#detalle-costo-total',
                '#detalle-costo-matricula',
                '#detalle-sede',
                '#detalle-inicio',
                '#detalle-tipo',
                'input[name="montoMatricula"]'
            ];

            let html = '<table border="1" cellpadding="5" style="width:100%; margin-top:10px;">';
            html += '<tr><th>Elemento</th><th>Existe</th><th>Estado</th></tr>';

            elements.forEach(selector => {
                const element = document.querySelector(selector);
                const exists = element !== null;
                const state = exists ? (element.style.display === 'none' ? 'Oculto' : 'Visible') : 'N/A';
                const color = exists ? 'green' : 'red';

                html += `<tr>
                    <td><code>${selector}</code></td>
                    <td style="color: ${color}; font-weight: bold;">${exists ? '✓' : '✗'}</td>
                    <td>${state}</td>
                </tr>`;

                log(`${selector}: ${exists ? '✓ Existe' : '✗ No existe'}`, exists ? 'success' : 'error');
            });

            html += '</table>';
            document.getElementById('elements-result').innerHTML = html;
        }

        function testProgramasPorGrado() {
            const grado = document.getElementById('testGrado').value;
            if (!grado) {
                alert('Selecciona un grado académico');
                return;
            }

            log(`=== Test AJAX: Programas por Grado (${grado}) ===`);

            $.ajax({
                url: 'ajax/programa.ajax.php',
                type: 'POST',
                data: { grado: grado },
                dataType: 'json',
                success: function(response) {
                    log('✓ AJAX exitoso', 'success');
                    log(`Programas encontrados: ${response.length}`);

                    let html = `<h4>Respuesta (${response.length} programas):</h4>`;
                    html += '<pre style="background: #f4f4f4; padding: 10px; border-radius: 5px; max-height: 200px; overflow-y: auto;">';
                    html += JSON.stringify(response, null, 2);
                    html += '</pre>';

                    document.getElementById('programas-result').innerHTML = html;
                },
                error: function(xhr, status, error) {
                    log('✗ Error AJAX', 'error');
                    log(`Status: ${status}`, 'error');
                    log(`Error: ${error}`, 'error');

                    document.getElementById('programas-result').innerHTML =
                        `<div style="color: red;">Error: ${error}<br>Status: ${xhr.status}<br>Response: ${xhr.responseText}</div>`;
                }
            });
        }

        function testDetallePrograma() {
            const programaId = document.getElementById('testProgramaId').value;
            if (!programaId) {
                alert('Ingresa un ID de programa');
                return;
            }

            log(`=== Test AJAX: Detalle Programa (ID: ${programaId}) ===`);

            $.ajax({
                url: 'ajax/programa.ajax.php',
                type: 'POST',
                data: { idprograma: programaId },
                dataType: 'json',
                success: function(respuesta) {
                    log('✓ AJAX exitoso', 'success');
                    log(`Programa: ${respuesta.NombrePrograma}`);

                    let html = '<h4>Respuesta del servidor:</h4>';
                    html += '<table border="1" cellpadding="5" style="width:100%;">';

                    Object.keys(respuesta).forEach(key => {
                        const highlight = key === 'CostoMatricula' ? 'background: #ffeb3b;' : '';
                        html += `<tr style="${highlight}">
                            <td><strong>${key}</strong></td>
                            <td>${respuesta[key]}</td>
                        </tr>`;
                    });

                    html += '</table>';

                    // Verificar si tiene CostoMatricula
                    if (respuesta.CostoMatricula) {
                        log(`✓ CostoMatricula encontrado: ${respuesta.CostoMatricula}`, 'success');
                    } else {
                        log('✗ CostoMatricula NO encontrado', 'error');
                    }

                    document.getElementById('detalle-result').innerHTML = html;
                },
                error: function(xhr, status, error) {
                    log('✗ Error AJAX', 'error');
                    log(`Status: ${status}`, 'error');
                    log(`Error: ${error}`, 'error');

                    document.getElementById('detalle-result').innerHTML =
                        `<div style="color: red;">Error: ${error}<br>Response: ${xhr.responseText}</div>`;
                }
            });
        }

        function testCompleto() {
            log('=== INICIANDO TEST COMPLETO ===');
            const resultDiv = document.getElementById('test-completo-result');
            resultDiv.innerHTML = '<p>Ejecutando test...</p>';

            // Paso 1: Obtener programas
            log('Paso 1: Obteniendo programas de MAESTRIA...');
            $.ajax({
                url: 'ajax/programa.ajax.php',
                type: 'POST',
                data: { grado: 'MAESTRIA' },
                dataType: 'json',
                success: function(programas) {
                    if (programas.length === 0) {
                        log('✗ No se encontraron programas', 'error');
                        resultDiv.innerHTML = '<p style="color:red;">No se encontraron programas de MAESTRIA</p>';
                        return;
                    }

                    log(`✓ ${programas.length} programas encontrados`, 'success');
                    const primerPrograma = programas[0];
                    log(`Primer programa: ${primerPrograma.NombrePrograma} (ID: ${primerPrograma.ProgramaID})`);

                    // Paso 2: Obtener detalle del primer programa
                    log('Paso 2: Obteniendo detalle del programa...');
                    $.ajax({
                        url: 'ajax/programa.ajax.php',
                        type: 'POST',
                        data: { idprograma: primerPrograma.ProgramaID },
                        dataType: 'json',
                        success: function(detalle) {
                            log('✓ Detalle obtenido', 'success');

                            let html = '<div style="background: #e8f5e9; padding: 15px; border-radius: 5px;">';
                            html += '<h3 style="color: #2e7d32;">✓ Test Completo Exitoso</h3>';
                            html += '<h4>Datos del Programa:</h4>';
                            html += '<ul>';
                            html += `<li><strong>Programa:</strong> ${detalle.NombrePrograma}</li>`;
                            html += `<li><strong>Código:</strong> ${detalle.Codigo}</li>`;
                            html += `<li><strong>Costo Total:</strong> Bs. ${parseFloat(detalle.Costo).toFixed(2)}</li>`;

                            if (detalle.CostoMatricula) {
                                html += `<li style="background: #ffeb3b; padding: 5px;"><strong>Costo Matrícula:</strong> Bs. ${parseFloat(detalle.CostoMatricula).toFixed(2)} ✓</li>`;
                                log(`✓ CostoMatricula: ${detalle.CostoMatricula}`, 'success');
                            } else {
                                html += `<li style="background: #ffebee; padding: 5px;"><strong>Costo Matrícula:</strong> NO ENCONTRADO ✗</li>`;
                                log('✗ CostoMatricula no está en la respuesta', 'error');
                            }

                            html += `<li><strong>Sede:</strong> ${detalle.Sede}</li>`;
                            html += `<li><strong>Duración:</strong> ${detalle.DuracionMeses} meses</li>`;
                            html += '</ul></div>';

                            resultDiv.innerHTML = html;
                            log('=== TEST COMPLETO FINALIZADO ===', 'success');
                        },
                        error: function(xhr, status, error) {
                            log('✗ Error al obtener detalle', 'error');
                            resultDiv.innerHTML = '<p style="color:red;">Error al obtener detalle del programa</p>';
                        }
                    });
                },
                error: function(xhr, status, error) {
                    log('✗ Error al obtener programas', 'error');
                    resultDiv.innerHTML = '<p style="color:red;">Error al obtener programas</p>';
                }
            });
        }

        // Ejecutar verificación al cargar
        window.onload = function() {
            log('Sistema de Debug cargado');
            log('Haz clic en los botones para ejecutar los tests');
        };
    </script>

    <hr style="margin: 30px 0;">
    <p><a href="index.php?ruta=inscripcion">← Volver a Inscripción</a></p>
</body>
</html>
