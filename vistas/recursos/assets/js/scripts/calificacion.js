/**
 * Sistema de Registro de Calificaciones - Flujo por pasos
 * 1. Seleccionar docente
 * 2. Mostrar asignaciones del docente
 * 3. Seleccionar módulo e ingresar calificaciones
 */

// Variables globales
let docenteSeleccionado = null;
let asignacionSeleccionada = null;
let estudiantesActuales = [];
let modulosCerrados = [];

// Formatear una fecha "YYYY-MM-DD" (tal como viene de la BD) a "DD/MM/AAAA"
function formatearFechaDDMMAAAA(fecha) {
    if (!fecha) return 'No registrada';
    const partes = fecha.split('-');
    if (partes.length !== 3) return fecha;
    return partes[2] + '/' + partes[1] + '/' + partes[0];
}

// Nombre del docente con sus menciones/títulos académicos adelante (ej. "Esp.
// Lic. JUAN PÉREZ"), igual que se muestra en el listado de Docentes. Se usa
// solo para lo que se imprime (PDF), no para el "Docente:" que se ve en pantalla.
function nombreDocenteParaImprimir() {
    if (!docenteSeleccionado) return '';
    return docenteSeleccionado.menciones
        ? docenteSeleccionado.menciones + ' ' + docenteSeleccionado.nombre
        : docenteSeleccionado.nombre;
}

$(document).ready(function() {
    // Inicializar Select2 (mismo tamaño/estilo para Sede, Programa y Docente)
    $('.select2-uniforme').select2({
        width: '100%'
    });

    // Cargar sedes y programas con asignaciones para los filtros previos al
    // docente (la lista de docentes ya viene renderizada completa desde PHP).
    cargarSedesFiltroDocente();
    cargarProgramasFiltroDocente('', false);

    $('#filtro-sede-docente').on('change', function() {
        cargarProgramasFiltroDocente($(this).val(), true);
    });

    $('#filtro-programa-docente').on('change', function() {
        actualizarCodigosFiltroDocente();
        cargarDocentesFiltrados($('#filtro-sede-docente').val(), $(this).val(), '');
    });

    $('#filtro-codigo-docente').on('change', function() {
        cargarDocentesFiltrados(
            $('#filtro-sede-docente').val(),
            $('#filtro-programa-docente').val(),
            $(this).val()
        );
    });

    // Event Listeners
    $('#select-docente').on('change', seleccionarDocente);
    $('#btn-cambiar-docente').on('click', volverPaso1);
    $('#btn-volver-asignaciones').on('click', volverPaso2);
    $('#btn-guardar-notas').on('click', guardarCalificaciones);

    // Usar delegación de eventos para el botón de lista de asistencia
    $(document).on('click', '#btn-generar-lista-asistencia', generarListaAsistencia);

    // Imprimir calificaciones del módulo actualmente seleccionado (Paso 3),
    // sin tener que esperar a que el módulo se cierre.
    $(document).on('click', '#btn-imprimir-calificaciones', function() {
        if (!asignacionSeleccionada) return;

        imprimirReporteCalificaciones(
            asignacionSeleccionada.moduloID,
            asignacionSeleccionada.programaID,
            asignacionSeleccionada.moduloNombre,
            asignacionSeleccionada.moduloCodigo,
            asignacionSeleccionada.programaNombre,
            asignacionSeleccionada.grado,
            nombreDocenteParaImprimir(),
            asignacionSeleccionada.fechaInicio,
            asignacionSeleccionada.fechaFinal,
            asignacionSeleccionada.sigla
        );
    });

    console.log('Event listeners registrados correctamente');
});

/**
 * ================================================================
 * PASO 1: SELECCIÓN DE DOCENTE
 * ================================================================
 */

function seleccionarDocente() {
    const docenteID = $(this).val();

    if (!docenteID) {
        return;
    }

    // Obtener datos del option seleccionado
    const option = $(this).find('option:selected');
    const nombreDocente = option.data('nombre');
    const ciDocente = option.data('ci');
    const especialidadDocente = option.data('especialidad');
    const mencionesDocente = option.data('menciones');

    docenteSeleccionado = {
        id: docenteID,
        nombre: nombreDocente,
        ci: ciDocente,
        especialidad: especialidadDocente,
        menciones: mencionesDocente
    };

    // Cargar asignaciones del docente
    cargarAsignacionesDocente(docenteID);
}

// Escapar texto para insertarlo de forma segura en HTML armado por
// concatenación de strings (nombre/CI/especialidad del docente, etc.).
function escaparHtml(valor) {
    const div = document.createElement('div');
    div.textContent = valor === null || valor === undefined ? '' : String(valor);
    return div.innerHTML;
}

/**
 * Filtro previo a la selección de docente: Sede, Programa y Código de
 * Programa acotan el SELECT de Docente a solo aquellos con módulos asignados
 * que coincidan (el Programa se acota a la Sede elegida y el Código, al
 * Programa elegido).
 */
function cargarSedesFiltroDocente() {
    $.ajax({
        url: 'ajax/calificacion.ajax.php',
        method: 'POST',
        data: { accion: 'listarSedesAsignaciones' },
        dataType: 'json',
        success: function(response) {
            if (response.status !== 'success') return;

            const $sede = $('#filtro-sede-docente');
            let opciones = '<option value="">Todas las sedes</option>';
            response.data.forEach(function(fila) {
                opciones += '<option value="' + escaparHtml(fila.Sede) + '">' + escaparHtml(fila.Sede) + '</option>';
            });
            $sede.html(opciones);
        }
    });
}

// Programas (con su código) devueltos para la sede actual; de aquí se arman
// las opciones del filtro de Código sin volver a consultar al servidor.
let programasFiltroDocente = [];

function cargarProgramasFiltroDocente(sede, actualizarDocentes) {
    $.ajax({
        url: 'ajax/calificacion.ajax.php',
        method: 'POST',
        data: { accion: 'listarProgramasAsignaciones', sede: sede || '' },
        dataType: 'json',
        success: function(response) {
            const $programa = $('#filtro-programa-docente');
            let opciones = '<option value="">Todos los programas</option>';

            programasFiltroDocente = response.status === 'success' ? response.data : [];
            programasFiltroDocente.forEach(function(p) {
                opciones += '<option value="' + p.ProgramaID + '">' +
                    escaparHtml(p.NombrePrograma) + ' (' + escaparHtml(p.GradoAcademico) + ')</option>';
            });

            $programa.html(opciones).val('');
            actualizarCodigosFiltroDocente();

            if (actualizarDocentes) {
                cargarDocentesFiltrados(sede, '', '');
            }
        }
    });
}

// El filtro de Código depende del Programa elegido: muestra solo el código
// de ese programa, o los de todos los programas de la sede si no hay uno.
function actualizarCodigosFiltroDocente() {
    const programaID = $('#filtro-programa-docente').val();

    const codigos = [...new Set(
        programasFiltroDocente
            .filter(p => !programaID || String(p.ProgramaID) === String(programaID))
            .map(p => p.CodigoPrograma)
            .filter(Boolean)
    )].sort();

    $('#filtro-codigo-docente')
        .html('<option value="">Todos los códigos</option>' +
            codigos.map(c => '<option value="' + escaparHtml(c) + '">' + escaparHtml(c) + '</option>').join(''))
        .val('')
        .trigger('change.select2');
}

function cargarDocentesFiltrados(sede, programaID, codigoPrograma) {
    $.ajax({
        url: 'ajax/calificacion.ajax.php',
        method: 'POST',
        data: {
            accion: 'listarDocentesFiltrados',
            sede: sede || '',
            programaID: programaID || '',
            codigoPrograma: codigoPrograma || ''
        },
        dataType: 'json',
        success: function(response) {
            const $docente = $('#select-docente');
            const hayDocentes = response.status === 'success' && response.data.length > 0;
            let opciones = '<option value="">' +
                (hayDocentes ? '-- Seleccione un docente --' : 'Ningún docente tiene asignaciones con este filtro') +
                '</option>';

            if (hayDocentes) {
                response.data.forEach(function(d) {
                    opciones += '<option value="' + d.DocenteID + '"' +
                        ' data-nombre="' + escaparHtml(d.NombreCompleto) + '"' +
                        ' data-ci="' + escaparHtml(d.CI) + '"' +
                        ' data-especialidad="' + escaparHtml(d.Especialidad) + '"' +
                        ' data-menciones="' + escaparHtml(d.Menciones) + '">' +
                        escaparHtml(d.NombreCompleto) + ' - ' + escaparHtml(d.CI) +
                        '</option>';
                });
            }

            $docente.html(opciones).prop('disabled', !hayDocentes).val('').trigger('change');
        }
    });
}

/**
 * ================================================================
 * PASO 2: ASIGNACIONES DEL DOCENTE
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
        beforeSend: function() {
            console.log('Enviando petición AJAX...');
            Swal.fire({
                title: 'Cargando asignaciones...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });
        },
        success: function(response) {
            console.log('Respuesta recibida:', response);
            console.log('Status:', response.status);
            console.log('Data:', response.data);
            console.log('Data length:', response.data ? response.data.length : 0);

            Swal.close();

            if (response.status === 'success' && response.data && response.data.length > 0) {
                console.log('Mostrando asignaciones...');
                mostrarAsignaciones(response.data);
            } else {
                console.log('No hay asignaciones');
                Swal.fire({
                    icon: 'info',
                    title: 'Sin módulos asignados',
                    text: 'Este docente aún no tiene módulos asignados. Solicite al administrador que le asigne un módulo desde "Asignación de Docentes".',
                    confirmButtonText: 'Entendido'
                });
                volverPaso1();
            }
        },
        error: function(xhr, status, error) {
            console.log('ERROR en AJAX');
            console.log('XHR:', xhr);
            console.log('Status:', status);
            console.log('Error:', error);
            console.log('Response Text:', xhr.responseText);

            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudieron cargar las asignaciones del docente. Intente nuevamente.',
                confirmButtonText: 'Cerrar'
            });
        }
    });
}

function mostrarAsignaciones(asignaciones) {
    // Separar módulos abiertos y cerrados
    const modulosAbiertos = [];
    modulosCerrados = []; // Reset global variable

    asignaciones.forEach(function(asig) {
        // Considerar cerrado si EstadoModulo es 'VALIDADO' o 'CERRADO'
        if (asig.EstadoModulo === 'VALIDADO' || asig.EstadoModulo === 'CERRADO') {
            modulosCerrados.push(asig);
        } else {
            modulosAbiertos.push(asig);
        }
    });

    // Ocultar paso 1 y mostrar paso 2
    $('#paso1-container').slideUp();
    $('#paso2-container').slideDown();

    // Mostrar información del docente
    $('#docente-nombre').text(docenteSeleccionado.nombre);
    $('#docente-ci').text(docenteSeleccionado.ci);
    $('#docente-especialidad').text(docenteSeleccionado.especialidad);
    $('#total-asignaciones').text(modulosAbiertos.length);

    renderTablaAsignacionesAbiertas(modulosAbiertos);
}

function renderTablaAsignacionesAbiertas(modulosAbiertos) {
    // Renderizar tabla de asignaciones (solo módulos abiertos)
    let html = `
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-calificaciones">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 12%;">Grado Académico</th>
                        <th>Programa</th>
                        <th>Módulo</th>
                        <th style="width: 10%;">Código</th>
                        <th style="width: 8%;">Estudiantes</th>
                        <th style="width: 15%;">Estado Calificación</th>
                        <th style="width: 16%;">Acción</th>
                    </tr>
                </thead>
                <tbody>
    `;

    if (modulosAbiertos.length === 0) {
        html += '<tr><td colspan="8" class="text-center text-muted py-3">Este docente no tiene módulos abiertos</td></tr>';
    }

    modulosAbiertos.forEach(function(asig, index) {
        // Determinar estado de calificación
        var totalEstudiantes = parseInt(asig.TotalEstudiantes) || 0;
        var totalCalificados = parseInt(asig.TotalCalificados) || 0;
        var porcentaje = totalEstudiantes > 0 ? Math.round((totalCalificados / totalEstudiantes) * 100) : 0;

        var estadoBadgeClass = '';
        var estadoTexto = '';
        var estadoIcono = '';

        if (totalCalificados === 0) {
            estadoBadgeClass = 'kt-badge--secondary';
            estadoTexto = 'Sin calificar';
            estadoIcono = 'la la-clock-o';
        } else if (totalCalificados < totalEstudiantes) {
            estadoBadgeClass = 'kt-badge--warning';
            estadoTexto = 'Parcial (' + totalCalificados + '/' + totalEstudiantes + ')';
            estadoIcono = 'la la-hourglass-half';
        } else {
            estadoBadgeClass = 'kt-badge--success';
            estadoTexto = 'Completo (' + totalCalificados + '/' + totalEstudiantes + ')';
            estadoIcono = 'la la-check-circle';
        }

        var fechaInfo = '';
        if (asig.UltimaCalificacion) {
            fechaInfo = '<br><small class="text-muted"><i class="la la-calendar"></i> ' + asig.UltimaCalificacion + '</small>';
        }

        // Información de quién registró
        if (asig.RegistradoPor) {
            var tipoUsuario = asig.TipoUsuarioRegistro || '';
            var iconoTipo = '';
            if (tipoUsuario === 'ADM' || tipoUsuario === 'Administrador') {
                iconoTipo = '<i class="la la-user-shield" style="color: #fd397a;"></i> ';
            } else if (tipoUsuario === 'DOC' || tipoUsuario === 'Docente') {
                iconoTipo = '<i class="la la-user" style="color: #5867dd;"></i> ';
            } else if (tipoUsuario === 'EST' || tipoUsuario === 'Estudiante') {
                iconoTipo = '<i class="la la-graduation-cap" style="color: #1dc9b7;"></i> ';
            }
            fechaInfo += '<br><small class="text-muted">' + iconoTipo + 'Por: ' + asig.RegistradoPor + '</small>';
        }

        // Ya terminó y faltan notas: se marca (lo mismo que lista "Calificaciones pendientes")
        var hoy = new Date().toLocaleDateString('en-CA');
        var faltaCalificar = !!asig.FechaFinal && asig.FechaFinal < hoy && totalEstudiantes > 0 && totalCalificados < totalEstudiantes;
        if (faltaCalificar) {
            fechaInfo += '<br><span class="rn-marca-pendiente"><i class="fa fa-exclamation-circle"></i> Falta calificar · terminó el ' + formatearFechaDDMMAAAA(asig.FechaFinal) + '</span>';
        }

        html += '<tr class="asignacion-row' + (faltaCalificar ? ' rn-fila-pendiente' : '') + '">';
        html += '<td class="text-center">' + (index + 1) + '</td>';
        html += '<td><span class="kt-badge kt-badge--inline kt-badge--primary">' + asig.GradoAcademico + '</span></td>';
        html += '<td><strong>' + asig.NombrePrograma + '</strong><br><small class="text-muted">' + asig.CodigoPrograma + '</small></td>';
        html += '<td>' + asig.nombremodulo + '</td>';
        html += '<td class="text-center"><span class="kt-badge kt-badge--dark kt-badge--inline">' + asig.codigomodulo + '</span></td>';
        html += '<td class="text-center"><span class="kt-badge kt-badge--' + (totalEstudiantes > 0 ? 'success' : 'secondary') + ' kt-badge--inline kt-badge--bold">' + totalEstudiantes + '</span></td>';
        html += '<td class="text-center">';
        html += '<span class="kt-badge kt-badge--inline ' + estadoBadgeClass + ' kt-badge--bold">';
        html += '<i class="' + estadoIcono + '"></i> ' + estadoTexto;
        html += '</span>' + fechaInfo;
        html += '</td>';
        html += '<td class="text-center">';
        html += '<div class="btn-group" role="group">';

        // Botón de evaluar/editar
        html += '<button class="btn btn-sm btn-brand btn-evaluar" ';
        html += 'data-modulo-id="' + asig.Idmodulo + '" ';
        html += 'data-programa-id="' + asig.ProgramaID + '" ';
        html += 'data-modulo-nombre="' + asig.nombremodulo + '" ';
        html += 'data-modulo-codigo="' + asig.codigomodulo + '" ';
        html += 'data-programa-nombre="' + asig.NombrePrograma + '" ';
        html += 'data-grado="' + asig.GradoAcademico + '" ';
        html += 'data-fecha-inicio="' + (asig.FechaInicio || '') + '" ';
        html += 'data-fecha-final="' + (asig.FechaFinal || '') + '" ';
        html += 'data-sigla="' + (asig.Sigla || '') + '" ';
        html += 'title="' + (totalCalificados > 0 ? 'Editar calificaciones' : 'Evaluar estudiantes') + '">';
        html += '<i class="la la-edit"></i> ' + (totalCalificados > 0 ? 'Editar' : 'Evaluar');
        html += '</button>';

        // Botón de imprimir calificaciones (mismos datos que arriba, no hace
        // falta entrar al módulo para imprimir).
        html += '<button class="btn btn-sm btn-success btn-imprimir-asignacion" ';
        html += 'data-modulo-id="' + asig.Idmodulo + '" ';
        html += 'data-programa-id="' + asig.ProgramaID + '" ';
        html += 'data-modulo-nombre="' + asig.nombremodulo + '" ';
        html += 'data-modulo-codigo="' + asig.codigomodulo + '" ';
        html += 'data-programa-nombre="' + asig.NombrePrograma + '" ';
        html += 'data-grado="' + asig.GradoAcademico + '" ';
        html += 'data-fecha-inicio="' + (asig.FechaInicio || '') + '" ';
        html += 'data-fecha-final="' + (asig.FechaFinal || '') + '" ';
        html += 'data-sigla="' + (asig.Sigla || '') + '" ';
        html += 'title="Imprimir calificaciones">';
        html += '<i class="fa fa-print"></i>';
        html += '</button>';

        // Botón de lista de asistencia
        html += '<button class="btn btn-sm btn-danger btn-asistencia-asignacion" ';
        html += 'data-modulo-id="' + asig.Idmodulo + '" ';
        html += 'data-programa-id="' + asig.ProgramaID + '" ';
        html += 'data-modulo-nombre="' + asig.nombremodulo + '" ';
        html += 'data-modulo-codigo="' + asig.codigomodulo + '" ';
        html += 'data-programa-nombre="' + asig.NombrePrograma + '" ';
        html += 'data-grado="' + asig.GradoAcademico + '" ';
        html += 'data-total-estudiantes="' + totalEstudiantes + '" ';
        html += 'title="Lista de asistencia">';
        html += '<i class="fa fa-file-pdf"></i>';
        html += '</button>';

        html += '</div>';
        html += '</td>';
        html += '</tr>';
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    // Agregar botón para ver módulos cerrados si hay alguno
    if (modulosCerrados.length > 0) {
        html += `
            <div class="alert alert-light border mt-3 text-center">
                <button class="btn btn-outline-info btn-sm" id="btn-ver-modulos-cerrados">
                    <i class="la la-lock"></i> Ver Módulos Cerrados
                    <span class="kt-badge kt-badge--info kt-badge--inline kt-badge--bold">${modulosCerrados.length}</span>
                </button>
            </div>
        `;
    }

    $('#asignaciones-container').html(html);
}

// Delegados (la tabla de asignaciones se vuelve a renderizar completa cada vez
// que se aplica un filtro, así que estos manejadores se registran una sola vez
// sobre document en vez de re-atarse a mano en cada render).
$(document).on('click', '.btn-evaluar', function() {
    const moduloID = $(this).data('modulo-id');
    const programaID = $(this).data('programa-id');

    asignacionSeleccionada = {
        moduloID: moduloID,
        programaID: programaID,
        moduloNombre: $(this).data('modulo-nombre'),
        moduloCodigo: $(this).data('modulo-codigo'),
        programaNombre: $(this).data('programa-nombre'),
        grado: $(this).data('grado'),
        fechaInicio: $(this).data('fecha-inicio'),
        fechaFinal: $(this).data('fecha-final'),
        sigla: $(this).data('sigla')
    };

    cargarEstudiantes(moduloID, programaID);
});

$(document).on('click', '#btn-ver-modulos-cerrados', mostrarModalModulosCerrados);

// Imprimir calificaciones directamente desde la tabla de asignaciones, sin
// tener que entrar primero al módulo (Paso 3).
$(document).on('click', '.btn-imprimir-asignacion', function() {
    const $btn = $(this);
    imprimirReporteCalificaciones(
        $btn.data('modulo-id'),
        $btn.data('programa-id'),
        $btn.data('modulo-nombre'),
        $btn.data('modulo-codigo'),
        $btn.data('programa-nombre'),
        $btn.data('grado'),
        nombreDocenteParaImprimir(),
        $btn.data('fecha-inicio'),
        $btn.data('fecha-final'),
        $btn.data('sigla')
    );
});

// Lista de asistencia directamente desde la tabla de asignaciones.
$(document).on('click', '.btn-asistencia-asignacion', function() {
    const $btn = $(this);
    const totalEstudiantes = parseInt($btn.data('total-estudiantes'), 10) || 0;

    if (totalEstudiantes === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Sin estudiantes',
            text: 'No hay estudiantes inscritos en este módulo para generar la lista de asistencia'
        });
        return;
    }

    const url = 'vistas/componentes/generar-lista-asistencia-pdf.php?' +
        'moduloID=' + $btn.data('modulo-id') +
        '&programaID=' + $btn.data('programa-id') +
        '&moduloNombre=' + encodeURIComponent($btn.data('modulo-nombre')) +
        '&moduloCodigo=' + encodeURIComponent($btn.data('modulo-codigo')) +
        '&programaNombre=' + encodeURIComponent($btn.data('programa-nombre')) +
        '&grado=' + encodeURIComponent($btn.data('grado')) +
        '&docenteNombre=' + encodeURIComponent(nombreDocenteParaImprimir());

    const ventana = window.open(url, '_blank');
    if (!ventana) {
        Swal.fire({
            icon: 'error',
            title: 'Ventana bloqueada',
            html: 'El navegador bloqueó la ventana emergente.<br>Por favor, permita ventanas emergentes para este sitio.',
            confirmButtonText: 'Entendido'
        });
    }
});

/**
 * ================================================================
 * PASO 3: CARGAR ESTUDIANTES Y CALIFICACIONES
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
                onOpen: function() {
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
    // Ocultar paso 2 y mostrar paso 3
    $('#paso2-container').slideUp();
    $('#paso3-container').slideDown();

    // Mostrar información del módulo
    $('#modulo-nombre').text(asignacionSeleccionada.moduloNombre);
    $('#modulo-codigo').text(asignacionSeleccionada.moduloCodigo);
    $('#programa-nombre').text(asignacionSeleccionada.programaNombre);
    $('#grado-nombre').text(asignacionSeleccionada.grado);
    $('#modulo-fecha-inicio').text(formatearFechaDDMMAAAA(asignacionSeleccionada.fechaInicio));
    $('#modulo-fecha-final').text(formatearFechaDDMMAAAA(asignacionSeleccionada.fechaFinal));

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
        const notaActual = estudiante.Nota !== null ? parseInt(estudiante.Nota) : '';
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
                        step="1"
                        data-estudiante-id="${estudiante.EstudianteID}"
                        onchange="validarNota(this)"
                        placeholder="0"
                        required
                    >
                </td>
                <td class="text-center">
                    <span class="kt-badge kt-badge--inline kt-badge--pill kt-badge--bold ${estadoClass}" data-estudiante-id="${estudiante.EstudianteID}">
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
    $('#footer-guardar').slideDown();
}

/**
 * ================================================================
 * UTILIDADES Y VALIDACIONES
 * ================================================================
 */

function getEstadoClass(nota) {
    if (nota === '' || nota === null) return 'kt-badge--secondary';
    return parseInt(nota) >= (window.NOTA_APROBACION || 76) ? 'kt-badge--success' : 'kt-badge--danger';
}

function getEstadoTexto(nota) {
    if (nota === '' || nota === null) return 'Pendiente';
    return parseInt(nota) >= (window.NOTA_APROBACION || 76) ? 'Aprobado' : 'Reprobado';
}

function getNotaInputClass(nota) {
    if (nota === '' || nota === null) return '';
    return parseInt(nota) >= (window.NOTA_APROBACION || 76) ? 'is-valid' : 'is-invalid';
}

function validarNota(input) {
    const nota = parseInt(input.value);
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

    // Validar aprobado/reprobado (nota mínima configurable: window.NOTA_APROBACION)
    if (nota >= (window.NOTA_APROBACION || 76)) {
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
    // Recolectar calificaciones
    const calificaciones = [];
    let hayErrores = false;

    $('.input-nota').each(function() {
        const estudianteID = $(this).data('estudiante-id');
        const nota = parseInt($(this).val());

        // Validar que tenga nota
        if ($(this).val() === '' || isNaN(nota)) {
            hayErrores = true;
            $(this).addClass('is-invalid');
            return;
        }

        // Validar rango
        if (nota < 0 || nota > 100) {
            hayErrores = true;
            $(this).addClass('is-invalid');
            return;
        }

        calificaciones.push({
            estudianteID: estudianteID,
            nota: nota
        });
    });

    if (hayErrores) {
        Swal.fire({
            type: 'error',
            title: 'Error de validación',
            text: 'Hay calificaciones inválidas o vacías. Por favor, corrija los errores antes de guardar.'
        });
        return;
    }

    if (calificaciones.length === 0) {
        Swal.fire({
            type: 'warning',
            title: 'Sin datos',
            text: 'No hay calificaciones para guardar'
        });
        return;
    }

    // Confirmar guardado
    Swal.fire({
        title: '¿Guardar calificaciones?',
        html: 'Se guardarán <strong>' + calificaciones.length + '</strong> calificaciones para el módulo:<br><strong>' + asignacionSeleccionada.moduloNombre + '</strong>',
        type: 'question',
        showCancelButton: true,
        confirmButtonColor: '#1dc9b7',
        cancelButtonColor: '#fd397a',
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar'
    }).then(function(result) {
        if (result.value) {
            enviarCalificaciones(calificaciones);
        }
    });
}

function enviarCalificaciones(calificaciones) {
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
                onOpen: function() {
                    Swal.showLoading();
                }
            });
        },
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire({
                    type: 'success',
                    title: 'Éxito',
                    text: 'Calificaciones guardadas correctamente',
                    timer: 1500,
                    showConfirmButton: false
                }).then(function() {
                    // Cerrar el panel de registro de notas (paso 3)
                    $('#paso3-container').slideUp(300, function() {
                        // Después de cerrar, volver al paso 2
                        $('#paso2-container').slideDown(300);

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
                Swal.fire({
                    type: 'error',
                    title: 'Error',
                    text: response.message || 'Error al guardar las calificaciones'
                });
            }
        },
        error: function() {
            Swal.fire({
                type: 'error',
                title: 'Error',
                text: 'Error al conectar con el servidor'
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
    $('#select-docente').val('').trigger('change');
    docenteSeleccionado = null;
}

function volverPaso2() {
    $('#paso3-container').slideUp();
    $('#paso2-container').slideDown();
    asignacionSeleccionada = null;
    estudiantesActuales = [];
}

/**
 * ================================================================
 * IMPRIMIR REPORTE DE CALIFICACIONES
 * ================================================================
 */

function imprimirReporteCalificaciones(moduloID, programaID, moduloNombre, moduloCodigo, programaNombre, gradoAcademico, docenteNombre, fechaInicioDB, fechaFinDB, sigla) {
    console.log('=== IMPRIMIR REPORTE ===');
    console.log('moduloID:', moduloID);
    console.log('programaID:', programaID);
    console.log('moduloNombre:', moduloNombre);
    console.log('moduloCodigo:', moduloCodigo);
    console.log('programaNombre:', programaNombre);
    console.log('gradoAcademico:', gradoAcademico);
    console.log('docenteNombre:', docenteNombre);

    // Solicitar fechas del módulo antes de generar el PDF
    console.log('Mostrando formulario de fechas...');

    Swal.fire({
        title: '<i class="la la-calendar"></i> Fechas del Módulo',
        html: `
            <div style="text-align: left; padding: 20px;">
                <h5 style="color: #5867dd; margin-bottom: 15px;">
                    <i class="la la-book"></i> ${moduloNombre}
                </h5>
                <p style="margin-bottom: 20px; color: #666;">
                    <strong>Código:</strong> ${moduloCodigo}<br>
                    <strong>Programa:</strong> ${programaNombre}
                </p>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="fechaInicio" style="font-weight: bold; color: #333; display: block; margin-bottom: 5px;">
                        <i class="la la-calendar-check-o"></i> Fecha de Inicio:
                    </label>
                    <input type="date" id="fechaInicio" class="swal2-input" style="width: 100%; padding: 10px; margin: 0;" value="${fechaInicioDB || ''}">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="fechaFin" style="font-weight: bold; color: #333; display: block; margin-bottom: 5px;">
                        <i class="la la-calendar-times-o"></i> Fecha de Finalización:
                    </label>
                    <input type="date" id="fechaFin" class="swal2-input" style="width: 100%; padding: 10px; margin: 0;" value="${fechaFinDB || ''}">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="fechaGeneracion" style="font-weight: bold; color: #333; display: block; margin-bottom: 5px;">
                        <i class="la la-print"></i> Fecha de impresión del documento (para la firma "Oruro, ..."):
                    </label>
                    <input type="date" id="fechaGeneracion" class="swal2-input" style="width: 100%; padding: 10px; margin: 0;" value="${new Date().toLocaleDateString('sv-SE')}">
                </div>

                <p style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-left: 3px solid #5867dd; font-size: 13px; color: #555;">
                    <i class="la la-info-circle"></i> ${(fechaInicioDB && fechaFinDB)
                        ? 'Se completaron con las fechas ya registradas para este módulo; puede ajustarlas si hace falta.'
                        : 'Estas fechas se mostrarán en el reporte PDF de calificaciones.'}
                </p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonColor: '#5867dd',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="la la-print"></i> Generar PDF',
        cancelButtonText: 'Cancelar',
        focusConfirm: false,
        preConfirm: () => {
            console.log('preConfirm ejecutado');
            const fechaInicio = document.getElementById('fechaInicio').value;
            const fechaFin = document.getElementById('fechaFin').value;
            const fechaGeneracion = document.getElementById('fechaGeneracion').value;

            console.log('Fecha Inicio capturada:', fechaInicio);
            console.log('Fecha Fin capturada:', fechaFin);
            console.log('Fecha Generación capturada:', fechaGeneracion);

            // Validar que las tres fechas estén llenas
            if (!fechaInicio || !fechaFin || !fechaGeneracion) {
                console.log('Validación fallida: fechas vacías');
                Swal.showValidationMessage('Por favor, complete las tres fechas');
                return false;
            }

            // Validar que fecha inicio sea menor o igual a fecha fin
            if (fechaInicio > fechaFin) {
                console.log('Validación fallida: fecha inicio > fecha fin');
                Swal.showValidationMessage('La fecha de inicio debe ser anterior o igual a la fecha de finalización');
                return false;
            }

            console.log('Validación exitosa, retornando fechas');
            return { fechaInicio: fechaInicio, fechaFin: fechaFin, fechaGeneracion: fechaGeneracion };
        }
    }).then((result) => {
        console.log('Then ejecutado, result completo:', result);
        console.log('result.isConfirmed:', result.isConfirmed);
        console.log('result.value:', result.value);
        console.log('result.isDismissed:', result.isDismissed);
        console.log('result.dismiss:', result.dismiss);

        // Verificar si hay datos válidos (compatibilidad con diferentes versiones de SweetAlert2)
        if (result.value && !result.dismiss) {
            console.log('Formulario confirmado, generando PDF...');
            console.log('Fechas:', result.value);

            // Generar URL con las fechas (usando TCPDF)
            const url = 'vistas/componentes/generar-pdf-calificaciones-tcpdf.php?' +
                'moduloID=' + moduloID +
                '&programaID=' + programaID +
                '&moduloNombre=' + encodeURIComponent(moduloNombre) +
                '&moduloCodigo=' + encodeURIComponent(moduloCodigo) +
                '&programaNombre=' + encodeURIComponent(programaNombre) +
                '&gradoAcademico=' + encodeURIComponent(gradoAcademico) +
                '&docenteNombre=' + encodeURIComponent(docenteNombre) +
                '&fechaInicio=' + encodeURIComponent(result.value.fechaInicio) +
                '&fechaFin=' + encodeURIComponent(result.value.fechaFin) +
                '&fechaGeneracion=' + encodeURIComponent(result.value.fechaGeneracion) +
                '&sigla=' + encodeURIComponent(sigla || '');

            console.log('URL generada:', url);

            // Abrir PDF en nueva ventana
            const ventana = window.open(url, '_blank');

            if (!ventana) {
                console.error('El navegador bloqueó la ventana emergente');
                Swal.fire({
                    icon: 'error',
                    title: 'Ventana bloqueada',
                    html: 'El navegador bloqueó la ventana emergente.<br>Por favor, permita ventanas emergentes para este sitio.',
                    confirmButtonText: 'Entendido'
                });
            } else {
                console.log('PDF abierto en nueva ventana');
            }
        } else {
            console.log('Formulario cancelado o sin datos');
        }
    });
}

/**
 * ================================================================
 * MODAL DE MÓDULOS CERRADOS
 * ================================================================
 */

function mostrarModalModulosCerrados() {
    console.log('Mostrar modal de módulos cerrados:', modulosCerrados);

    if (!modulosCerrados || modulosCerrados.length === 0) {
        Swal.fire({
            type: 'info',
            title: 'Sin módulos cerrados',
            text: 'No hay módulos cerrados para mostrar'
        });
        return;
    }

    // Generar HTML de la tabla
    let html = `
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-bordered table-hover table-sm">
                <thead style="position: sticky; top: 0; background: white; z-index: 10;">
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 12%;">Grado</th>
                        <th>Programa</th>
                        <th>Módulo</th>
                        <th style="width: 10%;">Código</th>
                        <th style="width: 10%;">Estado</th>
                        <th style="width: 10%;">Acción</th>
                    </tr>
                </thead>
                <tbody>
    `;

    modulosCerrados.forEach(function(asig, index) {
        const totalEstudiantes = parseInt(asig.TotalEstudiantes) || 0;
        const totalCalificados = parseInt(asig.TotalCalificados) || 0;

        // Información de validación
        let validacionInfo = '';
        if (asig.FechaValidacion && asig.NombreValidador) {
            validacionInfo = `<br><small class="text-muted">
                <i class="la la-check-circle"></i> Cerrado por: ${asig.NombreValidador}
                <br><i class="la la-calendar"></i> ${asig.FechaValidacion}
            </small>`;
        }

        html += `<tr>`;
        html += `<td class="text-center">${index + 1}</td>`;
        html += `<td><span class="kt-badge kt-badge--inline kt-badge--primary">${asig.GradoAcademico}</span></td>`;
        html += `<td><strong>${asig.NombrePrograma}</strong><br><small class="text-muted">${asig.CodigoPrograma}</small></td>`;
        html += `<td>${asig.nombremodulo}${validacionInfo}</td>`;
        html += `<td class="text-center"><span class="kt-badge kt-badge--dark kt-badge--inline">${asig.codigomodulo}</span></td>`;
        html += `<td class="text-center">`;
        html += `<span class="kt-badge kt-badge--danger kt-badge--inline"><i class="la la-lock"></i> CERRADO</span>`;
        html += `<br><small class="text-muted">${totalCalificados}/${totalEstudiantes} calif.</small>`;
        html += `</td>`;
        html += `<td class="text-center">`;
        html += `<div class="btn-group" role="group">`;

        // Botón de imprimir (siempre visible)
        html += `<button class="btn btn-sm btn-success btn-imprimir-cerrado" `;
        html += `data-modulo-id="${asig.Idmodulo}" `;
        html += `data-programa-id="${asig.ProgramaID}" `;
        html += `data-modulo-nombre="${asig.nombremodulo}" `;
        html += `data-modulo-codigo="${asig.codigomodulo}" `;
        html += `data-programa-nombre="${asig.NombrePrograma}" `;
        html += `data-grado="${asig.GradoAcademico}" `;
        html += `data-docente-nombre="${nombreDocenteParaImprimir()}" `;
        html += `data-fecha-inicio="${asig.FechaInicio || ''}" `;
        html += `data-fecha-final="${asig.FechaFinal || ''}" `;
        html += `data-sigla="${asig.Sigla || ''}" `;
        html += `title="Imprimir calificaciones">`;
        html += `<i class="la la-print"></i> Imprimir`;
        html += `</button>`;

        // Botón de reabrir (solo para administradores)
        if (typeof esAdministrador !== 'undefined' && esAdministrador === true) {
            html += `<button class="btn btn-sm btn-warning btn-reabrir-modulo" `;
            html += `data-modulo-id="${asig.Idmodulo}" `;
            html += `data-modulo-nombre="${asig.nombremodulo}" `;
            html += `data-modulo-codigo="${asig.codigomodulo}" `;
            html += `title="Reabrir módulo (solo administradores)">`;
            html += `<i class="la la-unlock"></i> Reabrir`;
            html += `</button>`;
        }

        html += `</div>`;
        html += `</td>`;
        html += `</tr>`;
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    // Mostrar en modal de SweetAlert2
    Swal.fire({
        title: '<i class="la la-lock"></i> Módulos Cerrados',
        html: html,
        width: '90%',
        showCloseButton: true,
        showConfirmButton: false,
        customClass: {
            container: 'swal-wide'
        },
        onOpen: function() {
            // Agregar evento a los botones de imprimir
            $('.btn-imprimir-cerrado').on('click', function() {
                const moduloID = $(this).data('modulo-id');
                const programaID = $(this).data('programa-id');
                const moduloNombre = $(this).data('modulo-nombre');
                const moduloCodigo = $(this).data('modulo-codigo');
                const programaNombre = $(this).data('programa-nombre');
                const gradoAcademico = $(this).data('grado');
                const docenteNombre = $(this).data('docente-nombre');
                const fechaInicioDB = $(this).data('fecha-inicio');
                const fechaFinDB = $(this).data('fecha-final');
                const sigla = $(this).data('sigla');

                imprimirReporteCalificaciones(moduloID, programaID, moduloNombre, moduloCodigo, programaNombre, gradoAcademico, docenteNombre, fechaInicioDB, fechaFinDB, sigla);
            });

            // Agregar evento a los botones de reabrir (solo para administradores)
            $('.btn-reabrir-modulo').on('click', function() {
                const moduloID = $(this).data('modulo-id');
                const moduloNombre = $(this).data('modulo-nombre');
                const moduloCodigo = $(this).data('modulo-codigo');

                reabrirModuloDesdeEstudiante(moduloID, moduloNombre, moduloCodigo);
            });
        }
    });
}

/**
 * Reabrir módulo cerrado (solo administradores)
 */
function reabrirModuloDesdeEstudiante(moduloID, moduloNombre, moduloCodigo) {
    console.log('Reabrir módulo:', moduloID, moduloNombre);

    // Verificar que es administrador
    if (typeof esAdministrador === 'undefined' || !esAdministrador) {
        Swal.fire({
            type: 'error',
            title: 'Acceso denegado',
            text: 'Solo los administradores pueden reabrir módulos cerrados'
        });
        return;
    }

    // Confirmar reapertura
    Swal.fire({
        title: '¿Reabrir este módulo?',
        html: `
            <div style="text-align: left;">
                <p><strong>Módulo:</strong> ${moduloNombre}</p>
                <p><strong>Código:</strong> ${moduloCodigo}</p>
                <br>
                <p style="color: #fd397a;">
                    <i class="la la-warning"></i>
                    Al reabrir el módulo, los docentes podrán volver a editar las calificaciones.
                </p>
            </div>
        `,
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffb822',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="la la-unlock"></i> Sí, reabrir módulo',
        cancelButtonText: 'Cancelar'
    }).then(function(result) {
        if (result.value) {
            ejecutarReaperturaModulo(moduloID, moduloNombre);
        }
    });
}

/**
 * Ejecutar la reapertura del módulo via AJAX
 */
function ejecutarReaperturaModulo(moduloID, moduloNombre) {
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
                title: 'Reabriendo módulo...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                onOpen: function() {
                    Swal.showLoading();
                }
            });
        },
        success: function(response) {
            console.log('Respuesta de reapertura:', response);

            if (response.status === 'success') {
                Swal.fire({
                    type: 'success',
                    title: 'Módulo reabierto',
                    html: `
                        <p>El módulo <strong>${moduloNombre}</strong> ha sido reabierto exitosamente.</p>
                        <p>Los docentes ahora pueden editar las calificaciones.</p>
                    `,
                    timer: 2000,
                    showConfirmButton: false
                }).then(function() {
                    // Recargar las asignaciones para actualizar la vista
                    if (docenteSeleccionado && docenteSeleccionado.id) {
                        cargarAsignacionesDocente(docenteSeleccionado.id);
                    }
                });
            } else {
                Swal.fire({
                    type: 'error',
                    title: 'Error al reabrir',
                    text: response.message || 'No se pudo reabrir el módulo'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error en AJAX:', xhr, status, error);
            Swal.fire({
                type: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor. Por favor, intente nuevamente.'
            });
        }
    });
}

/**
 * ================================================================
 * GENERAR LISTA DE ASISTENCIA
 * ================================================================
 */

function generarListaAsistencia() {
    console.log('');
    console.log('========================================');
    console.log('=== GENERAR LISTA DE ASISTENCIA ===');
    console.log('========================================');
    console.log('Función llamada correctamente');
    console.log('asignacionSeleccionada:', asignacionSeleccionada);
    console.log('docenteSeleccionado:', docenteSeleccionado);
    console.log('estudiantesActuales:', estudiantesActuales);
    console.log('estudiantesActuales.length:', estudiantesActuales ? estudiantesActuales.length : 0);

    // Verificar que hay una asignación seleccionada
    if (!asignacionSeleccionada) {
        console.log('Error: No hay asignación seleccionada');
        Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: 'Por favor, seleccione primero un módulo'
        });
        return;
    }

    // Verificar que hay estudiantes cargados
    if (!estudiantesActuales || estudiantesActuales.length === 0) {
        console.log('Error: No hay estudiantes');
        Swal.fire({
            icon: 'warning',
            title: 'Sin estudiantes',
            text: 'No hay estudiantes inscritos en este módulo para generar la lista de asistencia'
        });
        return;
    }

    // Generar el PDF con los datos actuales
    const url = 'vistas/componentes/generar-lista-asistencia-pdf.php?' +
        'moduloID=' + asignacionSeleccionada.moduloID +
        '&programaID=' + asignacionSeleccionada.programaID +
        '&moduloNombre=' + encodeURIComponent(asignacionSeleccionada.moduloNombre) +
        '&moduloCodigo=' + encodeURIComponent(asignacionSeleccionada.moduloCodigo) +
        '&programaNombre=' + encodeURIComponent(asignacionSeleccionada.programaNombre) +
        '&grado=' + encodeURIComponent(asignacionSeleccionada.grado) +
        '&docenteNombre=' + encodeURIComponent(nombreDocenteParaImprimir());

    console.log('URL generada:', url);

    // Abrir en nueva ventana
    console.log('Abriendo ventana...');
    const ventana = window.open(url, '_blank');

    if (!ventana) {
        console.error('El navegador bloqueó la ventana emergente');
        Swal.fire({
            icon: 'error',
            title: 'Ventana bloqueada',
            html: 'El navegador bloqueó la ventana emergente.<br>Por favor, permita ventanas emergentes para este sitio.<br><br>URL: <small>' + url + '</small>',
            confirmButtonText: 'Entendido'
        });
    } else {
        console.log('Ventana abierta exitosamente');
    }
}
