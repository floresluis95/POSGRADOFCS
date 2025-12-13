/**
 * Sistema de Registro de Calificaciones - Vista para Docentes
 * Auto-carga la información del docente logueado
 */

// Variables globales
let docenteSeleccionado = null;
let asignacionSeleccionada = null;
let estudiantesActuales = [];

$(document).ready(function() {
    // Auto-cargar información del docente logueado
    cargarDocenteLogueado();

    // Event Listeners - Usando event delegation para elementos dinámicos
    $(document).on('click', '#btn-volver-asignaciones', volverPaso1);
    $(document).on('click', '#btn-guardar-notas', function() {
        console.log('¡Botón Guardar Calificaciones clickeado!');
        guardarCalificaciones();
    });
    $(document).on('click', '#btn-generar-pdf', function() {
        generarPDF();
    });

    // Event listener para confirmar PDF con fechas
    $(document).on('click', '#btn-confirmar-pdf', function() {
        console.log('>>> Botón Confirmar PDF clickeado');
        generarPDFConFechas();
    });
});

/**
 * ================================================================
 * CARGAR DOCENTE LOGUEADO
 * ================================================================
 */

function cargarDocenteLogueado() {
    console.log('=== CARGAR DOCENTE LOGUEADO ===');

    $.ajax({
        url: 'ajax/calificacion.ajax.php',
        method: 'POST',
        data: {
            accion: 'obtenerDocente'
        },
        dataType: 'json',
        success: function(response) {
            console.log('Respuesta docente logueado:', response);

            if (response.status === 'success' && response.data) {
                const docente = response.data;

                docenteSeleccionado = {
                    id: docente.DocenteID,
                    nombre: docente.NombreCompleto,
                    ci: docente.CICompleto,
                    especialidad: docente.Especialidad || 'No especificada'
                };

                console.log('Docente cargado:', docenteSeleccionado);

                // Cargar asignaciones del docente
                cargarAsignacionesDocente(docente.DocenteID);
            } else {
                $('#loading-inicial').html(`
                    <div class="card-body text-center py-5">
                        <i class="fa fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                        <h4 class="mt-3">No se pudo cargar la información</h4>
                        <p class="text-muted">${response.message || 'Error desconocido'}</p>
                        <button class="btn btn-primary" onclick="location.reload()">
                            <i class="la la-refresh"></i> Reintentar
                        </button>
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.log('ERROR al cargar docente');
            console.log('XHR:', xhr);
            console.log('Status:', status);
            console.log('Error:', error);

            $('#loading-inicial').html(`
                <div class="card-body text-center py-5">
                    <i class="fa fa-times-circle text-danger" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">Error de conexión</h4>
                    <p class="text-muted">No se pudo conectar con el servidor</p>
                    <button class="btn btn-primary" onclick="location.reload()">
                        <i class="la la-refresh"></i> Reintentar
                    </button>
                </div>
            `);
        }
    });
}

/**
 * ================================================================
 * PASO 1: ASIGNACIONES DEL DOCENTE
 * ================================================================
 */

function cargarAsignacionesDocente(docenteID) {
    console.log('=== CARGAR ASIGNACIONES ===');
    console.log('DocenteID:', docenteID);

    $.ajax({
        url: 'ajax/calificacion.ajax.php',
        method: 'POST',
        data: {
            accion: 'obtenerAsignaciones',
            docenteID: docenteID
        },
        dataType: 'json',
        success: function(response) {
            console.log('Respuesta asignaciones:', response);
            console.log('Data length:', response.data ? response.data.length : 0);

            // Ocultar loading
            $('#loading-inicial').slideUp();

            if (response.status === 'success' && response.data && response.data.length > 0) {
                console.log('Mostrando asignaciones...');
                mostrarAsignaciones(response.data);
            } else {
                console.log('No hay asignaciones');
                $('#paso1-container').html(`
                    <div class="card-body text-center py-5">
                        <i class="fa fa-info-circle text-info" style="font-size: 3rem;"></i>
                        <h4 class="mt-3">Sin asignaciones</h4>
                        <p class="text-muted">No tiene módulos asignados actualmente</p>
                    </div>
                `).slideDown();
            }
        },
        error: function(xhr, status, error) {
            console.log('ERROR en AJAX');
            console.log('XHR:', xhr);
            console.log('Status:', status);
            console.log('Error:', error);

            $('#loading-inicial').slideUp();
            $('#paso1-container').html(`
                <div class="card-body text-center py-5">
                    <i class="fa fa-times-circle text-danger" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">Error al cargar asignaciones</h4>
                    <p class="text-muted">Error: ${error}</p>
                    <button class="btn btn-primary" onclick="location.reload()">
                        <i class="la la-refresh"></i> Reintentar
                    </button>
                </div>
            `).slideDown();
        }
    });
}

function mostrarAsignaciones(asignaciones) {
    // Mostrar paso 1
    $('#paso1-container').slideDown();

    // Mostrar información del docente
    $('#docente-nombre').text(docenteSeleccionado.nombre);
    $('#docente-ci').text(docenteSeleccionado.ci);
    $('#docente-especialidad').text(docenteSeleccionado.especialidad);
    $('#total-asignaciones').text(asignaciones.length);

    // Categorizar asignaciones por grado académico
    const asignacionesPorGrado = {};
    asignaciones.forEach(function(asig) {
        const grado = asig.GradoAcademico || 'Sin Grado';
        if (!asignacionesPorGrado[grado]) {
            asignacionesPorGrado[grado] = [];
        }
        asignacionesPorGrado[grado].push(asig);
    });

    // Renderizar tablas categorizadas por grado académico
    let html = '';

    Object.keys(asignacionesPorGrado).sort().forEach(function(grado) {
        const asignacionesGrado = asignacionesPorGrado[grado];

        html += `
            <div class="card mb-4" style="border-left: 4px solid #5867dd;">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="fa fa-graduation-cap"></i> ${grado}
                        <span class="kt-badge kt-badge--light kt-badge--inline ml-2">${asignacionesGrado.length} módulos</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-calificaciones mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th>Programa</th>
                                    <th>Módulo</th>
                                    <th style="width: 10%;">Código</th>
                                    <th style="width: 10%;">Estudiantes</th>
                                    <th style="width: 12%;">Estado Módulo</th>
                                    <th style="width: 12%;">Calificaciones</th>
                                    <th style="width: 15%;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
        `;

        asignacionesGrado.forEach(function(asig, index) {
            // Determinar estado de calificación
            const totalCalificados = parseInt(asig.TotalCalificados) || 0;
            const totalEstudiantes = parseInt(asig.TotalEstudiantes) || 0;

            let estadoBadge = '';
            let estadoTexto = '';
            let estadoIcono = '';

            if (totalCalificados === 0) {
                estadoBadge = 'kt-badge--secondary';
                estadoTexto = 'SIN CALIFICAR';
                estadoIcono = 'la-clock-o';
            } else if (totalCalificados < totalEstudiantes) {
                estadoBadge = 'kt-badge--warning';
                estadoTexto = `PARCIAL (${totalCalificados}/${totalEstudiantes})`;
                estadoIcono = 'la-exclamation-triangle';
            } else {
                estadoBadge = 'kt-badge--success';
                estadoTexto = 'CALIFICADO';
                estadoIcono = 'la-check-circle';
            }

            // Determinar estado del módulo (ACTIVO, VALIDADO, CERRADO)
            const estadoModulo = asig.EstadoModulo || 'ACTIVO';
            let moduloBadge = '';
            let moduloTexto = '';
            let moduloIcono = '';
            let botonValidar = '';

            if (estadoModulo === 'ACTIVO') {
                moduloBadge = 'kt-badge--success';
                moduloTexto = 'ABIERTO';
                moduloIcono = 'la-unlock';
                // Mostrar botón de validar solo si hay calificaciones
                if (totalCalificados > 0) {
                    botonValidar = `
                        <button class="btn btn-sm btn-warning btn-validar-modulo mt-1"
                                data-modulo-id="${asig.Idmodulo}"
                                data-modulo-nombre="${asig.nombremodulo}"
                                title="Validar y cerrar módulo">
                            <i class="la la-lock"></i> Validar y Cerrar
                        </button>
                    `;
                }
            } else if (estadoModulo === 'VALIDADO' || estadoModulo === 'CERRADO') {
                moduloBadge = 'kt-badge--danger';
                moduloTexto = 'VALIDADO';
                moduloIcono = 'la-lock';

                // Información de quién validó
                let infoValidacion = '';
                if (asig.NombreValidador) {
                    infoValidacion = `<br><small class="text-muted">Por: ${asig.NombreValidador}</small>`;
                }
                if (asig.FechaValidacion) {
                    infoValidacion += `<br><small class="text-muted">${asig.FechaValidacion}</small>`;
                }

                botonValidar = `
                    <button class="btn btn-sm btn-info btn-reabrir-modulo mt-1"
                            data-modulo-id="${asig.Idmodulo}"
                            data-modulo-nombre="${asig.nombremodulo}"
                            title="Reabrir módulo (Solo Admin)">
                        <i class="la la-unlock"></i> Reabrir
                    </button>
                    ${infoValidacion}
                `;
            }

            html += `
                <tr class="asignacion-row">
                    <td class="text-center">${index + 1}</td>
                    <td>
                        <strong>${asig.NombrePrograma}</strong>
                        <br><small class="text-muted">${asig.CodigoPrograma}</small>
                    </td>
                    <td>${asig.nombremodulo}</td>
                    <td class="text-center">
                        <span class="kt-badge kt-badge--dark kt-badge--inline">${asig.codigomodulo}</span>
                    </td>
                    <td class="text-center">
                        <span class="kt-badge kt-badge--${asig.TotalEstudiantes > 0 ? 'success' : 'secondary'} kt-badge--inline kt-badge--bold">
                            ${asig.TotalEstudiantes}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="kt-badge ${moduloBadge} kt-badge--inline kt-badge--bold">
                            <i class="la ${moduloIcono}"></i> ${moduloTexto}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="kt-badge ${estadoBadge} kt-badge--inline kt-badge--bold">
                            <i class="la ${estadoIcono}"></i> ${estadoTexto}
                        </span>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-brand btn-evaluar"
                                data-modulo-id="${asig.Idmodulo}"
                                data-programa-id="${asig.ProgramaID}"
                                data-modulo-nombre="${asig.nombremodulo}"
                                data-modulo-codigo="${asig.codigomodulo}"
                                data-programa-nombre="${asig.NombrePrograma}"
                                data-grado="${asig.GradoAcademico}"
                                data-estado-modulo="${estadoModulo}">
                            <i class="la la-edit"></i> ${estadoModulo === 'ACTIVO' ? 'Registrar' : 'Ver'}
                        </button>
                        <button class="btn btn-sm btn-danger btn-imprimir-pdf ml-1"
                                data-modulo-id="${asig.Idmodulo}"
                                data-programa-id="${asig.ProgramaID}"
                                data-modulo-nombre="${asig.nombremodulo}"
                                data-modulo-codigo="${asig.codigomodulo}"
                                data-programa-nombre="${asig.NombrePrograma}"
                                data-grado="${asig.GradoAcademico}"
                                title="Imprimir PDF">
                            <i class="fa fa-print"></i>
                        </button>
                        ${botonValidar}
                    </td>
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
    });

    $('#asignaciones-container').html(html);

    // Event delegation para botones que se crean dinámicamente
    $(document).off('click', '.btn-evaluar').on('click', '.btn-evaluar', function() {
        console.log('>>> Botón Evaluar clickeado');
        const moduloID = $(this).data('modulo-id');
        const programaID = $(this).data('programa-id');
        const estadoModulo = $(this).data('estado-modulo');

        asignacionSeleccionada = {
            moduloID: moduloID,
            programaID: programaID,
            moduloNombre: $(this).data('modulo-nombre'),
            moduloCodigo: $(this).data('modulo-codigo'),
            programaNombre: $(this).data('programa-nombre'),
            grado: $(this).data('grado'),
            estadoModulo: estadoModulo
        };

        console.log('>>> Asignación establecida:', asignacionSeleccionada);

        cargarEstudiantes(moduloID, programaID);
    });

    // Event delegation para botón de imprimir PDF
    $(document).off('click', '.btn-imprimir-pdf').on('click', '.btn-imprimir-pdf', function(e) {
        e.preventDefault();
        e.stopPropagation();

        console.log('>>> Botón Imprimir PDF clickeado');

        // Guardar datos del módulo para el PDF
        const moduloData = {
            moduloID: $(this).data('modulo-id'),
            programaID: $(this).data('programa-id'),
            moduloNombre: $(this).data('modulo-nombre'),
            moduloCodigo: $(this).data('modulo-codigo'),
            programaNombre: $(this).data('programa-nombre'),
            grado: $(this).data('grado')
        };

        console.log('>>> Datos del módulo para PDF:', moduloData);

        // Abrir modal para seleccionar fechas
        abrirModalFechasPDF(moduloData);
    });

    // Event handler para validar y cerrar módulo
    $(document).off('click', '.btn-validar-modulo').on('click', '.btn-validar-modulo', function(e) {
        e.stopPropagation();
        const moduloID = $(this).data('modulo-id');
        const moduloNombre = $(this).data('modulo-nombre');
        validarCerrarModulo(moduloID, moduloNombre);
    });

    // Event handler para reabrir módulo
    $(document).off('click', '.btn-reabrir-modulo').on('click', '.btn-reabrir-modulo', function(e) {
        e.stopPropagation();
        const moduloID = $(this).data('modulo-id');
        const moduloNombre = $(this).data('modulo-nombre');
        reabrirModulo(moduloID, moduloNombre);
    });
}

/**
 * ================================================================
 * PASO 2: CARGAR ESTUDIANTES Y CALIFICACIONES
 * ================================================================
 */

function cargarEstudiantes(moduloID, programaID) {
    $.ajax({
        url: 'ajax/calificacion.ajax.php',
        method: 'POST',
        data: {
            accion: 'obtenerEstudiantes',
            moduloID: moduloID,
            programaID: programaID
        },
        dataType: 'json',
        beforeSend: function() {
            Swal.fire({
                title: 'Cargando estudiantes...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                onOpen: () => {
                    Swal.showLoading();
                }
            });
        },
        success: function(response) {
            Swal.close();

            if (response.status === 'success') {
                estudiantesActuales = response.data;
                mostrarFormularioCalificaciones(response.data);
            } else {
                Swal.fire({
                    type: 'error',
                    title: 'Error',
                    text: 'Error al cargar los estudiantes'
                });
            }
        },
        error: function() {
            Swal.close();
            Swal.fire({
                type: 'error',
                title: 'Error',
                text: 'Error al conectar con el servidor'
            });
        }
    });
}

function mostrarFormularioCalificaciones(estudiantes) {
    // Ocultar paso 1 y mostrar paso 2
    $('#paso1-container').slideUp();
    $('#paso2-container').slideDown();

    // Mostrar información del módulo
    $('#modulo-nombre').text(asignacionSeleccionada.moduloNombre);
    $('#modulo-codigo').text(asignacionSeleccionada.moduloCodigo);
    $('#programa-nombre').text(asignacionSeleccionada.programaNombre);
    $('#grado-nombre').text(asignacionSeleccionada.grado);

    // Verificar permisos de edición
    verificarPermisoEdicion(asignacionSeleccionada.moduloID, function(permisoData) {
        const puedeEditar = permisoData.permitido;
        const esModuloCerrado = asignacionSeleccionada.estadoModulo !== 'ACTIVO';

        // Mostrar advertencia si el módulo está cerrado
        if (esModuloCerrado) {
            const mensaje = puedeEditar
                ? '<div class="alert alert-warning mt-3"><i class="la la-warning"></i> <strong>Módulo Validado:</strong> Este módulo está cerrado. Usted puede editarlo porque tiene permisos de administrador.</div>'
                : '<div class="alert alert-danger mt-3"><i class="la la-lock"></i> <strong>Módulo Validado:</strong> Este módulo está cerrado y no puede ser editado. Solo un administrador puede modificar las calificaciones.</div>';

            $('.alert.alert-info').after(mensaje);
        }

        mostrarFormularioEstudiantesInterno(estudiantes, puedeEditar);
    });
}

function mostrarFormularioEstudiantesInterno(estudiantes, puedeEditar) {

    // Renderizar tabla de estudiantes
    if (estudiantes.length === 0) {
        $('#estudiantes-container').html(`
            <div class="alert alert-warning text-center">
                <i class="flaticon-warning"></i> No hay estudiantes inscritos en este módulo
            </div>
        `);
        $('#footer-guardar').hide();
        return;
    }

    const inputDisabled = !puedeEditar ? 'disabled readonly' : '';
    const inputStyle = !puedeEditar ? 'background-color: #f4f5f8; cursor: not-allowed;' : '';

    let html = `
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-calificaciones">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 10%;">C.I.</th>
                        <th>Estudiante</th>
                        <th style="width: 15%;">Nota Final</th>
                        <th style="width: 15%;">Estado</th>
                    </tr>
                </thead>
                <tbody>
    `;

    estudiantes.forEach(function(estudiante, index) {
        const nombreCompleto = `${estudiante.Nombre} ${estudiante.Apaterno} ${estudiante.Amaterno}`;
        const notaActual = estudiante.Nota !== null ? parseFloat(estudiante.Nota) : '';
        const estadoClass = getEstadoClass(notaActual);
        const estadoTexto = getEstadoTexto(notaActual);
        const notaInputClass = getNotaInputClass(notaActual);

        html += `
            <tr>
                <td class="text-center">${index + 1}</td>
                <td class="text-center">${estudiante.Ci}</td>
                <td>${nombreCompleto}</td>
                <td>
                    <input
                        type="number"
                        class="form-control input-nota ${notaInputClass}"
                        value="${notaActual}"
                        min="0"
                        max="100"
                        step="0.01"
                        data-estudiante-id="${estudiante.EstudianteID}"
                        onchange="validarNota(this)"
                        placeholder="0.00"
                        style="${inputStyle}"
                        ${inputDisabled}
                        required
                    >
                </td>
                <td class="text-center">
                    <span class="kt-badge kt-badge--xl ${estadoClass}" data-estudiante-id="${estudiante.EstudianteID}">
                        ${estadoTexto}
                    </span>
                </td>
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    $('#estudiantes-container').html(html);

    // Mostrar u ocultar botón de guardar según permisos
    if (puedeEditar) {
        $('#footer-guardar').slideDown();
    } else {
        $('#footer-guardar').hide();
    }

    $('#pdf-section').slideDown();
}

/**
 * Verificar permisos de edición para un módulo
 */
function verificarPermisoEdicion(moduloID, callback) {
    $.ajax({
        url: 'ajax/calificacion.ajax.php',
        method: 'POST',
        data: {
            accion: 'verificarPermisoEdicion',
            moduloID: moduloID
        },
        dataType: 'json',
        success: function(response) {
            if (callback) {
                callback(response);
            }
        },
        error: function() {
            // En caso de error, denegar permiso por seguridad
            if (callback) {
                callback({
                    permitido: false,
                    mensaje: 'Error al verificar permisos'
                });
            }
        }
    });
}

/**
 * ================================================================
 * UTILIDADES Y VALIDACIONES
 * ================================================================
 */

function getEstadoClass(nota) {
    if (nota === '' || nota === null) return 'kt-badge--secondary';
    return parseFloat(nota) >= 76 ? 'kt-badge--success' : 'kt-badge--danger';
}

function getEstadoTexto(nota) {
    if (nota === '' || nota === null) return 'Pendiente';
    return parseFloat(nota) >= 76 ? 'Aprobado' : 'Reprobado';
}

function getNotaInputClass(nota) {
    if (nota === '' || nota === null) return '';
    return parseFloat(nota) >= 76 ? 'is-valid' : 'is-invalid';
}

function validarNota(input) {
    const nota = parseFloat(input.value);
    const estudianteID = $(input).data('estudiante-id');
    const estadoBadge = $(`.kt-badge[data-estudiante-id="${estudianteID}"]`);

    // Limpiar clases
    $(input).removeClass('is-valid is-invalid');

    // Validar rango
    if (isNaN(nota) || nota < 0 || nota > 100) {
        $(input).addClass('is-invalid');
        estadoBadge.removeClass('kt-badge--success kt-badge--danger kt-badge--secondary')
                   .addClass('kt-badge--warning')
                   .text('Inválida');
        return;
    }

    // Validar aprobado/reprobado (nota mínima 76)
    if (nota >= 76) {
        $(input).addClass('is-valid');
        estadoBadge.removeClass('kt-badge--danger kt-badge--warning kt-badge--secondary')
                   .addClass('kt-badge--success')
                   .text('Aprobado');
    } else {
        $(input).addClass('is-invalid');
        estadoBadge.removeClass('kt-badge--success kt-badge--warning kt-badge--secondary')
                   .addClass('kt-badge--danger')
                   .text('Reprobado');
    }
}

/**
 * ================================================================
 * GUARDAR CALIFICACIONES
 * ================================================================
 */

function guardarCalificaciones() {
    console.log('>>> Iniciando guardarCalificaciones()');

    // Recolectar calificaciones
    const calificaciones = [];
    let hayErrores = false;

    console.log('>>> Buscando inputs de notas...');
    const inputs = $('.input-nota');
    console.log('>>> Total de inputs encontrados:', inputs.length);

    inputs.each(function(index) {
        const estudianteID = $(this).data('estudiante-id');
        const valorNota = $(this).val();
        const nota = parseFloat(valorNota);

        console.log(`>>> Input ${index + 1}: EstudianteID=${estudianteID}, Valor="${valorNota}", Nota=${nota}`);

        // Validar que tenga nota
        if (valorNota === '' || isNaN(nota)) {
            console.log(`>>> Error en input ${index + 1}: vacío o NaN`);
            hayErrores = true;
            $(this).addClass('is-invalid');
            return;
        }

        // Validar rango
        if (nota < 0 || nota > 100) {
            console.log(`>>> Error en input ${index + 1}: fuera de rango`);
            hayErrores = true;
            $(this).addClass('is-invalid');
            return;
        }

        calificaciones.push({
            estudianteID: estudianteID,
            nota: nota
        });
        console.log(`>>> Calificación agregada: EstudianteID=${estudianteID}, Nota=${nota}`);
    });

    console.log('>>> Total de calificaciones recolectadas:', calificaciones.length);
    console.log('>>> Hay errores:', hayErrores);

    if (hayErrores) {
        console.log('>>> Mostrando alerta de error de validación');
        Swal.fire({
            type: 'error',
            title: 'Error de validación',
            text: 'Hay calificaciones inválidas o vacías. Por favor, corrija los errores antes de guardar.'
        });
        return;
    }

    if (calificaciones.length === 0) {
        console.log('>>> Mostrando alerta de sin datos');
        Swal.fire({
            type: 'warning',
            title: 'Sin datos',
            text: 'No hay calificaciones para guardar'
        });
        return;
    }

    console.log('>>> Mostrando popup de confirmación');
    console.log('>>> Asignación seleccionada:', asignacionSeleccionada);

    // Confirmar guardado - usando sintaxis compatible
    if (confirm(`¿Guardar ${calificaciones.length} calificación(es) para el módulo ${asignacionSeleccionada.moduloNombre}?`)) {
        console.log('>>> Usuario confirmó, llamando enviarCalificaciones()');
        enviarCalificaciones(calificaciones);
    } else {
        console.log('>>> Usuario canceló');
    }
}

function enviarCalificaciones(calificaciones) {
    console.log('=== ENVIAR CALIFICACIONES ===');
    console.log('Asignación seleccionada:', asignacionSeleccionada);
    console.log('Calificaciones a enviar:', calificaciones);
    console.log('Datos que se enviarán:', {
        accion: 'guardarCalificaciones',
        programaID: asignacionSeleccionada.programaID,
        moduloID: asignacionSeleccionada.moduloID,
        calificaciones: JSON.stringify(calificaciones)
    });

    $.ajax({
        url: 'ajax/calificacion.ajax.php',
        method: 'POST',
        data: {
            accion: 'guardarCalificaciones',
            programaID: asignacionSeleccionada.programaID,
            moduloID: asignacionSeleccionada.moduloID,
            calificaciones: JSON.stringify(calificaciones)
        },
        dataType: 'json',
        beforeSend: function() {
            Swal.fire({
                title: 'Guardando calificaciones...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                onOpen: () => {
                    Swal.showLoading();
                }
            });
        },
        success: function(response) {
            console.log('Respuesta del servidor:', response);

            if (response.status === 'success') {
                Swal.fire({
                    type: 'success',
                    title: 'Éxito',
                    text: 'Calificaciones guardadas correctamente',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    // Cerrar el panel de registro de notas (paso 2)
                    $('#paso2-container').slideUp(300, function() {
                        // Después de cerrar, volver al paso 1
                        $('#paso1-container').slideDown(300);

                        // Recargar las asignaciones para mostrar el estado actualizado
                        if (docenteSeleccionado && docenteSeleccionado.id) {
                            cargarAsignacionesDocente(docenteSeleccionado.id);
                        }
                    });

                    // Limpiar datos del módulo actual
                    asignacionSeleccionada = null;
                    estudiantesActuales = [];
                });
            } else {
                console.error('Error del servidor:', response.message);
                Swal.fire({
                    type: 'error',
                    title: 'Error',
                    text: response.message || 'Error al guardar las calificaciones'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', {
                status: status,
                error: error,
                responseText: xhr.responseText
            });
            Swal.fire({
                type: 'error',
                title: 'Error de conexión',
                text: 'Error al conectar con el servidor. Por favor, revise la consola del navegador para más detalles.'
            });
        }
    });
}

/**
 * ================================================================
 * NAVEGACIÓN ENTRE PASOS
 * ================================================================
 */

function volverPaso1() {
    $('#paso2-container').slideUp();
    $('#paso1-container').slideDown();
    asignacionSeleccionada = null;
    estudiantesActuales = [];
}

/**
 * ================================================================
 * VALIDAR/CERRAR Y REABRIR MÓDULOS
 * ================================================================
 */

function validarCerrarModulo(moduloID, moduloNombre) {
    Swal.fire({
        title: '¿Validar y cerrar módulo?',
        html: `
            <p>Está a punto de <strong>validar y cerrar</strong> el módulo:</p>
            <p class="text-primary"><strong>${moduloNombre}</strong></p>
            <p class="text-danger">
                <i class="la la-warning"></i> Una vez cerrado, solo un administrador podrá modificar las calificaciones.
            </p>
            <p>¿Desea continuar?</p>
        `,
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, validar y cerrar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#f4516c',
        cancelButtonColor: '#34bfa3'
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: 'ajax/calificacion.ajax.php',
                method: 'POST',
                data: {
                    accion: 'validarCerrarModulo',
                    moduloID: moduloID
                },
                dataType: 'json',
                beforeSend: function() {
                    Swal.fire({
                        title: 'Procesando...',
                        text: 'Validando y cerrando módulo',
                        allowOutsideClick: false,
                        onOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            type: 'success',
                            title: 'Módulo validado',
                            text: response.message,
                            timer: 2000
                        }).then(() => {
                            // Recargar asignaciones para mostrar el nuevo estado
                            if (docenteSeleccionado && docenteSeleccionado.id) {
                                cargarAsignacionesDocente(docenteSeleccionado.id);
                            }
                        });
                    } else {
                        Swal.fire({
                            type: response.status === 'warning' ? 'warning' : 'error',
                            title: response.status === 'warning' ? 'Advertencia' : 'Error',
                            text: response.message
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        type: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo validar el módulo. Por favor, intente nuevamente.'
                    });
                }
            });
        }
    });
}

function reabrirModulo(moduloID, moduloNombre) {
    Swal.fire({
        title: '¿Reabrir módulo?',
        html: `
            <p>Está a punto de <strong>reabrir</strong> el módulo:</p>
            <p class="text-primary"><strong>${moduloNombre}</strong></p>
            <p class="text-warning">
                <i class="la la-info-circle"></i> Esta acción solo está disponible para administradores.
            </p>
            <p>El módulo volverá a estar abierto para edición.</p>
        `,
        type: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, reabrir',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#5867dd',
        cancelButtonColor: '#f4516c'
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: 'ajax/calificacion.ajax.php',
                method: 'POST',
                data: {
                    accion: 'reabrirModulo',
                    moduloID: moduloID
                },
                dataType: 'json',
                beforeSend: function() {
                    Swal.fire({
                        title: 'Procesando...',
                        text: 'Reabriendo módulo',
                        allowOutsideClick: false,
                        onOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            type: 'success',
                            title: 'Módulo reabierto',
                            text: response.message,
                            timer: 2000
                        }).then(() => {
                            // Recargar asignaciones para mostrar el nuevo estado
                            if (docenteSeleccionado && docenteSeleccionado.id) {
                                cargarAsignacionesDocente(docenteSeleccionado.id);
                            }
                        });
                    } else {
                        Swal.fire({
                            type: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        type: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo reabrir el módulo. Por favor, intente nuevamente.'
                    });
                }
            });
        }
    });
}

/**
 * ================================================================
 * GENERACIÓN DE PDF CON MODAL DE FECHAS
 * ================================================================
 */

// Variable temporal para almacenar datos del módulo durante la generación del PDF
let moduloParaPDF = null;

/**
 * Abrir modal para seleccionar fechas del PDF
 */
function abrirModalFechasPDF(moduloData) {
    console.log('>>> Abriendo modal de fechas para PDF');
    console.log('>>> Datos del módulo:', moduloData);

    // Guardar datos del módulo
    moduloParaPDF = moduloData;

    // Limpiar campos del modal
    $('#sigla-modulo-pdf').val('');
    $('#fecha-inicio-pdf').val('');
    $('#fecha-fin-pdf').val('');

    // Abrir modal
    $('#modalFechasPDF').modal('show');
}

/**
 * Generar PDF con fechas seleccionadas
 */
function generarPDFConFechas() {
    console.log('>>> Generando PDF con fechas');

    // Validar que hay datos del módulo
    if (!moduloParaPDF) {
        Swal.fire({
            type: 'error',
            title: 'Error',
            text: 'No hay datos del módulo seleccionado'
        });
        return;
    }

    // Obtener datos del formulario
    const siglaModulo = $('#sigla-modulo-pdf').val().trim();
    const fechaInicio = $('#fecha-inicio-pdf').val();
    const fechaFin = $('#fecha-fin-pdf').val();

    // Validar fechas
    if (!fechaInicio || !fechaFin) {
        Swal.fire({
            type: 'warning',
            title: 'Fechas requeridas',
            text: 'Por favor ingrese ambas fechas (inicio y fin)'
        });
        return;
    }

    // Validar que fecha inicio sea menor o igual a fecha fin
    if (new Date(fechaInicio) > new Date(fechaFin)) {
        Swal.fire({
            type: 'error',
            title: 'Fechas inválidas',
            text: 'La fecha de inicio debe ser menor o igual a la fecha de fin'
        });
        return;
    }

    console.log('>>> Datos para el PDF:');
    console.log('>>> Módulo:', moduloParaPDF);
    console.log('>>> Docente:', docenteSeleccionado);
    console.log('>>> Sigla:', siglaModulo);
    console.log('>>> Fecha Inicio:', fechaInicio);
    console.log('>>> Fecha Fin:', fechaFin);

    // Cerrar modal
    $('#modalFechasPDF').modal('hide');

    // Crear formulario dinámico para POST
    const form = $('<form>', {
        action: 'tcpdf/pdf/generar-calificaciones-pdf.php',
        method: 'POST',
        target: '_blank'
    });

    // Agregar campos al formulario
    form.append($('<input>', { type: 'hidden', name: 'programaNombre', value: moduloParaPDF.programaNombre }));
    form.append($('<input>', { type: 'hidden', name: 'moduloNombre', value: moduloParaPDF.moduloNombre }));
    form.append($('<input>', { type: 'hidden', name: 'moduloCodigo', value: moduloParaPDF.moduloCodigo }));
    form.append($('<input>', { type: 'hidden', name: 'docenteNombre', value: docenteSeleccionado.nombre }));
    form.append($('<input>', { type: 'hidden', name: 'siglaModulo', value: siglaModulo }));
    form.append($('<input>', { type: 'hidden', name: 'fechaInicio', value: fechaInicio }));
    form.append($('<input>', { type: 'hidden', name: 'fechaFin', value: fechaFin }));
    form.append($('<input>', { type: 'hidden', name: 'moduloID', value: moduloParaPDF.moduloID }));
    form.append($('<input>', { type: 'hidden', name: 'programaID', value: moduloParaPDF.programaID }));
    form.append($('<input>', { type: 'hidden', name: 'grado', value: moduloParaPDF.grado }));

    // Agregar formulario al body, enviarlo y eliminarlo
    form.appendTo('body').submit().remove();

    console.log('>>> Formulario enviado para generar PDF con fechas y sigla');

    // Mostrar mensaje de éxito
    Swal.fire({
        type: 'success',
        title: 'PDF generado',
        text: 'El PDF se está generando en una nueva pestaña',
        timer: 2000,
        showConfirmButton: false
    });

    // Limpiar datos temporales
    moduloParaPDF = null;
}

/**
 * Función legacy de generación de PDF (por compatibilidad)
 */
function generarPDF() {
    console.log('>>> Iniciando generarPDF()');

    // Validar que haya una asignación seleccionada
    if (!asignacionSeleccionada) {
        Swal.fire({
            type: 'error',
            title: 'Error',
            text: 'No hay un módulo seleccionado'
        });
        return;
    }

    // Usar el nuevo sistema de modal
    abrirModalFechasPDF({
        moduloID: asignacionSeleccionada.moduloID,
        programaID: asignacionSeleccionada.programaID,
        moduloNombre: asignacionSeleccionada.moduloNombre,
        moduloCodigo: asignacionSeleccionada.moduloCodigo,
        programaNombre: asignacionSeleccionada.programaNombre,
        grado: asignacionSeleccionada.grado
    });
}
