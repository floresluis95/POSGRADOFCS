/**
 * Script para Reportes de Módulos
 * Gestiona la carga de datos y generación de reportes
 */

console.log('=== Script de Reportes de Módulos Cargado ===');

$(document).ready(function() {
    // Inicializar Select2
    $('.select2-basic').select2({
        placeholder: 'Seleccione una opción',
        allowClear: true
    });

    // Cargar grados académicos al iniciar
    cargarGradosAcademicos();

    // Event listeners
    $('#grado-select').on('change', function() {
        cargarProgramas();
    });

    $('#programa-select').on('change', function() {
        cargarModulos();
    });

    $('#btn-buscar').on('click', function() {
        generarReporte();
    });

    $('#btn-imprimir').on('click', function() {
        imprimirReporte();
    });

    // Event listener para botones "Ver Módulos" en la tabla de programas
    $(document).on('click', '.ver-modulos-programa', function() {
        const programaID = $(this).data('programa-id');
        const programaNombre = $(this).data('programa-nombre');
        mostrarModulosPrograma(programaID, programaNombre);
    });

    // Event listener para hacer clic en una tarjeta de módulo
    $(document).on('click', '.modulo-card', function() {
        const moduloID = $(this).data('modulo-id');
        const programaID = $(this).data('programa-id');
        generarReporteDesdeModal(programaID, moduloID);
    });
});

/**
 * Cargar grados académicos
 */
function cargarGradosAcademicos() {
    console.log('Cargando grados académicos...');

    $.ajax({
        url: 'ajax/reportemodulos.ajax.php',
        method: 'POST',
        data: { accion: 'obtenerGrados' },
        dataType: 'json',
        success: function(response) {
            console.log('Grados obtenidos:', response);

            const selectGrado = $('#grado-select');
            selectGrado.html('<option value="">-- Seleccione Grado --</option>');

            if (response && response.length > 0) {
                response.forEach(function(item) {
                    selectGrado.append(
                        `<option value="${item.GradoAcademico}">${item.GradoAcademico}</option>`
                    );
                });
            }

            selectGrado.trigger('change.select2');
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar grados:', error);
            alert('Error al cargar grados académicos');
        }
    });
}

/**
 * Cargar programas por grado
 */
function cargarProgramas() {
    const grado = $('#grado-select').val();

    // Limpiar y deshabilitar dependientes
    $('#programa-select').html('<option value="">-- Seleccione Programa --</option>').prop('disabled', true).trigger('change.select2');
    $('#modulo-select').html('<option value="">-- Todos los Módulos --</option>').prop('disabled', true).trigger('change.select2');
    limpiarResultados();

    if (!grado) {
        return;
    }

    console.log('Cargando programas para grado:', grado);

    $.ajax({
        url: 'ajax/reportemodulos.ajax.php',
        method: 'POST',
        data: {
            accion: 'obtenerProgramas',
            grado: grado
        },
        dataType: 'json',
        success: function(response) {
            console.log('Programas obtenidos:', response);

            const selectPrograma = $('#programa-select');
            selectPrograma.html('<option value="">-- Seleccione Programa --</option>');

            if (response && response.length > 0) {
                response.forEach(function(item) {
                    selectPrograma.append(
                        `<option value="${item.ProgramaID}">${item.NombrePrograma} (${item.Codigo})</option>`
                    );
                });
                selectPrograma.prop('disabled', false);
            } else {
                alert('No hay programas disponibles para este grado');
            }

            selectPrograma.trigger('change.select2');
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar programas:', error);
            alert('Error al cargar programas');
        }
    });
}

/**
 * Cargar módulos por programa
 */
function cargarModulos() {
    const programaID = $('#programa-select').val();

    // Limpiar y deshabilitar
    $('#modulo-select').html('<option value="">-- Todos los Módulos --</option>').prop('disabled', true).trigger('change.select2');
    limpiarResultados();

    if (!programaID) {
        return;
    }

    console.log('Cargando módulos para programa:', programaID);

    $.ajax({
        url: 'ajax/reportemodulos.ajax.php',
        method: 'POST',
        data: {
            accion: 'obtenerModulos',
            programaID: programaID
        },
        dataType: 'json',
        success: function(response) {
            console.log('Módulos obtenidos:', response);

            const selectModulo = $('#modulo-select');
            selectModulo.html('<option value="">-- Todos los Módulos --</option>');

            if (response && response.length > 0) {
                response.forEach(function(item) {
                    selectModulo.append(
                        `<option value="${item.Idmodulo}">${item.codigomodulo} - ${item.nombremodulo}</option>`
                    );
                });
                selectModulo.prop('disabled', false);
            }

            selectModulo.trigger('change.select2');
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar módulos:', error);
            alert('Error al cargar módulos');
        }
    });
}

/**
 * Generar reporte
 */
function generarReporte() {
    const programaID = $('#programa-select').val();
    const moduloID = $('#modulo-select').val();

    if (!programaID) {
        alert('Por favor, seleccione un Grado Académico y un Programa');
        return;
    }

    console.log('Generando reporte para programa:', programaID, 'módulo:', moduloID);

    // Mostrar loading
    $('#resultados-reporte').html(`
        <div class="text-center p-5">
            <i class="fa fa-spinner fa-spin fa-3x text-primary"></i>
            <h5 class="mt-3">Generando reporte...</h5>
        </div>
    `);

    $.ajax({
        url: 'ajax/reportemodulos.ajax.php',
        method: 'POST',
        data: {
            accion: 'obtenerInscritos',
            programaID: programaID,
            moduloID: moduloID || ''
        },
        dataType: 'json',
        success: function(response) {
            console.log('Inscritos obtenidos:', response);
            mostrarReporte(response);
        },
        error: function(xhr, status, error) {
            console.error('Error al generar reporte:', error);
            $('#resultados-reporte').html(`
                <div class="alert alert-danger text-center">
                    <i class="fa fa-exclamation-circle fa-2x"></i>
                    <h5 class="mt-2">Error al generar el reporte</h5>
                    <p>Por favor, intente nuevamente</p>
                </div>
            `);
        }
    });
}

/**
 * Mostrar reporte en pantalla
 */
function mostrarReporte(datos) {
    if (!datos || datos.length === 0) {
        $('#resultados-reporte').html(`
            <div class="alert alert-warning text-center">
                <i class="fa fa-exclamation-triangle fa-2x"></i>
                <h5 class="mt-2">No se encontraron inscritos</h5>
                <p>No hay estudiantes inscritos con los filtros seleccionados</p>
            </div>
        `);
        $('#contenedor-imprimir').hide();
        return;
    }

    // Obtener información del programa y módulo
    const programaNombre = $('#programa-select option:selected').text();
    const moduloNombre = $('#modulo-select').val() ? $('#modulo-select option:selected').text() : 'Todos los Módulos';

    let html = `
        <div class="kt-portlet">
            <div class="kt-portlet__head" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="kt-portlet__head-label">
                    <h4 style="color: white; margin: 0;">
                        <i class="fa fa-users"></i> Reporte de Inscritos
                    </h4>
                </div>
            </div>
            <div class="kt-portlet__body">
                <div class="mb-3">
                    <strong>Programa:</strong> ${programaNombre}<br>
                    <strong>Módulo:</strong> ${moduloNombre}<br>
                    <strong>Total de Registros:</strong> <span class="badge badge-primary">${datos.length}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered tabla-reporte">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>NOMBRE COMPLETO</th>
                                <th style="width: 120px;">C.I.</th>
                                <th style="width: 150px;">MÓDULO</th>
                                <th style="width: 120px;">COSTO (Bs.)</th>
                                <th style="width: 120px;">FECHA PAGO</th>
                                <th style="width: 100px;">ESTADO</th>
                            </tr>
                        </thead>
                        <tbody>
    `;

    datos.forEach((item, index) => {
        const estadoBadge = item.EstadoPago === 'PAGADO'
            ? '<span class="badge-estado-pagado">PAGADO</span>'
            : '<span class="badge-estado-pendiente">PENDIENTE</span>';

        const fechaPago = item.fechapago ? new Date(item.fechapago).toLocaleDateString('es-BO') : '-';
        const costo = item.costomodulo ? parseFloat(item.costomodulo).toFixed(2) : '-';

        html += `
            <tr>
                <td class="text-center"><strong>${index + 1}</strong></td>
                <td>${item.NombreCompleto}</td>
                <td class="text-center">${item.Ci}</td>
                <td><span class="badge-modulo">${item.codigomodulo}</span></td>
                <td class="text-right">${costo}</td>
                <td class="text-center">${fechaPago}</td>
                <td class="text-center">${estadoBadge}</td>
            </tr>
        `;
    });

    html += `
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;

    $('#resultados-reporte').html(html);
    $('#contenedor-imprimir').show();
}

/**
 * Imprimir reporte
 */
function imprimirReporte() {
    const contenido = document.getElementById('resultados-reporte').innerHTML;
    const ventana = window.open('', '_blank');

    ventana.document.write(`
        <html>
        <head>
            <title>Reporte de Inscritos por Módulo</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 20px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                th, td {
                    border: 1px solid #000;
                    padding: 8px;
                    text-align: left;
                }
                th {
                    background: #667eea;
                    color: white;
                    font-weight: bold;
                }
                .badge-modulo {
                    background: #667eea;
                    color: white;
                    padding: 4px 8px;
                    border-radius: 10px;
                    font-size: 10px;
                }
                .badge-estado-pagado {
                    background: #28a745;
                    color: white;
                    padding: 3px 8px;
                    border-radius: 10px;
                    font-size: 9px;
                }
                .badge-estado-pendiente {
                    background: #ffc107;
                    color: #000;
                    padding: 3px 8px;
                    border-radius: 10px;
                    font-size: 9px;
                }
                @media print {
                    .no-print {
                        display: none;
                    }
                }
            </style>
        </head>
        <body>
            <h1 style="text-align: center;">REPORTE DE INSCRITOS POR MÓDULO</h1>
            <p style="text-align: center; color: #666;">Fecha de Impresión: ${new Date().toLocaleDateString('es-BO')}</p>
            <hr>
            ${contenido}
        </body>
        </html>
    `);

    ventana.document.close();
    ventana.print();
}

/**
 * Limpiar resultados
 */
function limpiarResultados() {
    $('#resultados-reporte').html(`
        <div class="alert alert-info text-center">
            <i class="fa fa-info-circle fa-2x"></i>
            <h5 class="mt-2">Use los filtros de arriba y presione <strong>Buscar</strong> para generar el reporte</h5>
        </div>
    `);
    $('#contenedor-imprimir').hide();
}

/**
 * Mostrar módulos del programa en modal
 */
function mostrarModulosPrograma(programaID, programaNombre) {
    console.log('Mostrando módulos del programa:', programaID, programaNombre);

    // Actualizar título del modal
    $('#modalModulosProgramaLabel').html(`<i class="flaticon2-layers"></i> Módulos de: ${programaNombre}`);

    // Mostrar modal
    $('#modalModulosPrograma').modal('show');

    // Resetear contenido con spinner
    $('#contenido-modulos-programa').html(`
        <div class="text-center p-5">
            <div class="kt-spinner kt-spinner--lg kt-spinner--brand"></div>
            <p class="mt-3" style="color: #667eea; font-weight: 600;">Cargando módulos...</p>
        </div>
    `);

    // Cargar módulos con inscritos
    $.ajax({
        url: 'ajax/reportemodulos.ajax.php',
        method: 'POST',
        data: {
            accion: 'obtenerConteoInscritosPorModulo',
            programaID: programaID
        },
        dataType: 'json',
        success: function(response) {
            console.log('Módulos con inscritos:', response);
            mostrarTarjetasModulos(response, programaID);
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar módulos:', error);
            $('#contenido-modulos-programa').html(`
                <div class="alert alert-danger text-center">
                    <i class="fa fa-exclamation-circle fa-2x"></i>
                    <h5 class="mt-2">Error al cargar módulos</h5>
                    <p>Por favor, intente nuevamente</p>
                </div>
            `);
        }
    });
}

/**
 * Mostrar tarjetas de módulos
 */
function mostrarTarjetasModulos(modulos, programaID) {
    if (!modulos || modulos.length === 0) {
        $('#contenido-modulos-programa').html(`
            <div class="alert alert-warning text-center">
                <i class="fa fa-exclamation-triangle fa-2x"></i>
                <h5 class="mt-2">No hay módulos disponibles</h5>
                <p>Este programa no tiene módulos registrados</p>
            </div>
        `);
        return;
    }

    let html = `
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i>
            <strong>Haga clic en cualquier módulo</strong> para generar el reporte de inscritos
        </div>
    `;

    modulos.forEach(function(modulo) {
        const inscritos = parseInt(modulo.TotalInscritos || 0);
        const costo = parseFloat(modulo.costomodulo || 0).toFixed(2);

        html += `
            <div class="modulo-card" data-modulo-id="${modulo.Idmodulo}" data-programa-id="${programaID}">
                <div class="modulo-card-header">
                    <span class="modulo-codigo">
                        <i class="flaticon2-file-1"></i> ${modulo.codigomodulo}
                    </span>
                    <span class="modulo-inscritos">
                        <i class="flaticon2-group"></i> ${inscritos} inscrito${inscritos !== 1 ? 's' : ''}
                    </span>
                </div>
                <div class="modulo-nombre">
                    ${modulo.nombremodulo}
                </div>
                <div class="modulo-card-footer">
                    <div>
                        <small style="color: #B5B5C3; text-transform: uppercase; font-size: 11px;">Costo del Módulo</small>
                        <div class="modulo-costo">Bs. ${costo}</div>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-primary" style="border-radius: 20px; pointer-events: none;">
                            <i class="fa fa-chart-bar"></i> Ver Reporte
                        </button>
                    </div>
                </div>
            </div>
        `;
    });

    $('#contenido-modulos-programa').html(html);
}

/**
 * Generar reporte desde el modal (al hacer clic en módulo)
 */
function generarReporteDesdeModal(programaID, moduloID) {
    console.log('Generando reporte desde modal - Programa:', programaID, 'Módulo:', moduloID);

    // Cerrar el modal
    $('#modalModulosPrograma').modal('hide');

    // Esperar a que el modal se cierre completamente
    setTimeout(function() {
        // Scroll al área de resultados
        $('html, body').animate({
            scrollTop: $('#resultados-reporte').offset().top - 100
        }, 500);

        // Mostrar loading
        $('#resultados-reporte').html(`
            <div class="text-center p-5">
                <i class="fa fa-spinner fa-spin fa-3x text-primary"></i>
                <h5 class="mt-3">Generando reporte del módulo seleccionado...</h5>
            </div>
        `);

        // Generar reporte
        $.ajax({
            url: 'ajax/reportemodulos.ajax.php',
            method: 'POST',
            data: {
                accion: 'obtenerInscritos',
                programaID: programaID,
                moduloID: moduloID
            },
            dataType: 'json',
            success: function(response) {
                console.log('Reporte generado:', response);
                mostrarReporte(response);
            },
            error: function(xhr, status, error) {
                console.error('Error al generar reporte:', error);
                $('#resultados-reporte').html(`
                    <div class="alert alert-danger text-center">
                        <i class="fa fa-exclamation-circle fa-2x"></i>
                        <h5 class="mt-2">Error al generar el reporte</h5>
                        <p>Por favor, intente nuevamente</p>
                    </div>
                `);
            }
        });
    }, 500);
}
