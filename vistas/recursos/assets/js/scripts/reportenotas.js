/**
 * Script para Reporte de Notas
 * Maneja la visualización de módulos, generación de PDF y panel de auditoría
 */

$(document).ready(function() {
    // Select2 (mismo tamaño/estilo en Sede y Programa)
    $('.select2-uniforme').select2({ width: '100%' });

    // Inicializar DataTables
    const tablaProgramas = $('#tablaProgramas').DataTable({
        language: {
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            },
            emptyTable: "No hay programas disponibles",
            zeroRecords: "No se encontraron resultados"
        }
    });

    // Filtros por Sede (columna 2) y Programa (columna 1): coincidencia exacta
    // sobre el texto de la celda, para no mezclar programas con nombres parecidos.
    function escaparRegex(texto) {
        return texto.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function aplicarFiltrosReporte() {
        const sede = $('#filtroSedeReporte').val();
        const programa = $('#filtroProgramaReporte').val();
        const codigo = $('#filtroCodigoReporte').val();

        tablaProgramas.column(2).search(sede ? '^' + escaparRegex(sede) + '$' : '', true, false);
        tablaProgramas.column(1).search(programa ? '^' + escaparRegex(programa) + '$' : '', true, false);
        tablaProgramas.column(4).search(codigo ? '^' + escaparRegex(codigo) + '$' : '', true, false);
        tablaProgramas.draw();
    }

    // El select de Código depende del Programa seleccionado: solo muestra
    // los códigos que pertenecen al programa elegido (o todos si no hay filtro).
    const $filtroCodigoReporte = $('#filtroCodigoReporte');
    const relacionProgramaCodigo = Array.isArray(window.programasParaFiltroCodigo) ? window.programasParaFiltroCodigo : [];

    function actualizarOpcionesCodigoSegunPrograma(mantenerSeleccion) {
        const programaSeleccionado = $('#filtroProgramaReporte').val();
        const codigoActual = mantenerSeleccion ? $filtroCodigoReporte.val() : '';

        let codigosDisponibles = relacionProgramaCodigo
            .filter(function(item) { return !programaSeleccionado || item.nombre === programaSeleccionado; })
            .map(function(item) { return item.codigo; });
        codigosDisponibles = Array.from(new Set(codigosDisponibles)).sort();

        $filtroCodigoReporte.empty().append('<option value="">Todos los códigos...</option>');
        codigosDisponibles.forEach(function(codigo) {
            $filtroCodigoReporte.append($('<option></option>').val(codigo).text(codigo));
        });

        // Mantiene la selección previa si sigue siendo válida para el programa actual.
        if (codigoActual && codigosDisponibles.indexOf(codigoActual) !== -1) {
            $filtroCodigoReporte.val(codigoActual);
        } else {
            $filtroCodigoReporte.val('');
        }
        $filtroCodigoReporte.trigger('change.select2');
    }

    $('#filtroSedeReporte, #filtroCodigoReporte').on('change', aplicarFiltrosReporte);

    $('#filtroProgramaReporte').on('change', function() {
        actualizarOpcionesCodigoSegunPrograma(true);
        aplicarFiltrosReporte();
    });

    $('#btnLimpiarFiltroReporte').on('click', function() {
        $('#filtroSedeReporte').val('').trigger('change');
        $('#filtroProgramaReporte').val('').trigger('change');
        $('#filtroCodigoReporte').val('').trigger('change');
    });

    // Evento: Ver módulos del programa
    // Escapar texto de la BD para usarlo dentro de HTML o de un atributo data-*
    // (un nombre con comillas rompía el botón de imprimir)
    function escAttr(valor) {
        return $('<div>').text(valor == null ? '' : String(valor)).html().replace(/"/g, '&quot;');
    }

    // Último "Ver Módulos" pulsado: para recargar la lista tras resetear notas
    let $ultimoVerModulos = null;

    $(document).on('click', '.btnVerModulos', function() {
        $ultimoVerModulos = $(this);
        const programaID = $(this).data('programa-id');
        const programaNombre = $(this).data('programa-nombre');

        $('#nombreProgramaModal').text(programaNombre);
        $('#loadingModulos').show();
        $('#contenedorModulos').html('');

        // Petición AJAX para obtener módulos
        $.ajax({
            url: 'ajax/reportenotas.ajax.php',
            type: 'POST',
            data: {
                action: 'obtenerModulos',
                programaID: programaID
            },
            dataType: 'json',
            success: function(response) {
                $('#loadingModulos').hide();

                if (response.status === 'success' && response.data.length > 0) {
                    let html = '<div class="table-responsive"><table class="table table-striped table-bordered">';
                    html += '<thead class="thead-light">';
                    html += '<tr>';
                    html += '<th class="text-center" style="width: 5%;">Nº</th>';
                    html += '<th>Código</th>';
                    html += '<th>Módulo</th>';
                    html += '<th>Docente</th>';
                    html += '<th class="text-center">Inscritos</th>';
                    html += '<th class="text-center">Calificados</th>';
                    html += '<th class="text-center">Estado</th>';
                    html += '<th class="text-center">Acciones</th>';
                    html += '</tr>';
                    html += '</thead><tbody>';

                    let contador = 0;
                    response.data.forEach(function(modulo) {
                        contador++;
                        const inscritos = parseInt(modulo.TotalInscritos) || 0;
                        const calificados = parseInt(modulo.TotalCalificados) || 0;
                        const porcentaje = inscritos > 0 ? Math.round((calificados / inscritos) * 100) : 0;

                        let estadoBadge = '';
                        if (calificados === 0) {
                            estadoBadge = '<span class="badge badge-secondary">Sin calificar</span>';
                        } else if (calificados < inscritos) {
                            estadoBadge = '<span class="badge badge-warning">Parcial (' + porcentaje + '%)</span>';
                        } else {
                            estadoBadge = '<span class="badge badge-success">Completo (100%)</span>';
                        }

                        html += '<tr>';
                        html += '<td class="text-center">' + contador + '</td>';
                        html += '<td><strong>' + escAttr(modulo.codigomodulo || 'N/A') + '</strong></td>';
                        html += '<td>' + escAttr(modulo.nombremodulo) + '</td>';
                        html += '<td>' + (modulo.NombreDocente ? escAttr(modulo.NombreDocente) : '<span class="text-muted">Sin docente</span>') + '</td>';
                        html += '<td class="text-center"><span class="badge badge-info">' + inscritos + '</span></td>';
                        html += '<td class="text-center"><span class="badge badge-primary">' + calificados + '</span></td>';
                        html += '<td class="text-center">' + estadoBadge + '</td>';
                        html += '<td class="text-center">';

                        if (calificados > 0) {
                            html += '<div class="btn-group" role="group">';
                            html += '<button type="button" class="btn btn-danger btn-sm btnGenerarPDF" ' +
                                   'data-modulo-id="' + modulo.Idmodulo + '" ' +
                                   'data-programa-id="' + (modulo.ProgramaId || programaID) + '" ' +
                                   'data-modulo-nombre="' + escAttr(modulo.nombremodulo) + '" ' +
                                   'data-modulo-codigo="' + escAttr((modulo.codigomodulo || '')) + '" ' +
                                   'data-programa-nombre="' + escAttr((modulo.NombrePrograma || programaNombre)) + '" ' +
                                   'data-docente-nombre="' + escAttr((modulo.NombreDocente || '')) + '" ' +
                                   'data-menciones-docente="' + escAttr((modulo.MencionesDocente || '')) + '" ' +
                                   'data-grado="' + escAttr((modulo.GradoAcademico || '')) + '" ' +
                                   'data-fecha-inicio="' + (modulo.FechaInicio || '') + '" ' +
                                   'data-fecha-final="' + (modulo.FechaFinal || '') + '" ' +
                                   'data-sigla="' + escAttr((modulo.Sigla || '')) + '" ' +
                                   'title="Imprimir Planilla de Calificaciones">' +
                                   '<i class="fas fa-print"></i> Imprimir' +
                                   '</button>';
                            html += '<button type="button" class="btn btn-info btn-sm btnVerAuditoria" ' +
                                   'data-modulo-id="' + modulo.Idmodulo + '" ' +
                                   'data-programa-id="' + (modulo.ProgramaId || programaID) + '" ' +
                                   'data-modulo-nombre="' + escAttr(modulo.nombremodulo) + '" ' +
                                   'title="Ver Panel de Auditoría">' +
                                   '<i class="fas fa-history"></i> Auditoría' +
                                   '</button>';
                            if (window.esAdminReporteNotas) {
                                html += '<button type="button" class="btn btn-outline-danger btn-sm btnResetearNotas" ' +
                                       'data-modulo-id="' + modulo.Idmodulo + '" ' +
                                       'data-programa-id="' + (modulo.ProgramaId || programaID) + '" ' +
                                       'data-modulo-nombre="' + escAttr(modulo.nombremodulo) + '" ' +
                                       'data-calificados="' + calificados + '" ' +
                                       'title="Borrar todas las notas del módulo para cargarlas de nuevo">' +
                                       '<i class="fas fa-undo-alt"></i> Resetear' +
                                       '</button>';
                            }
                            html += '</div>';
                        } else {
                            html += '<button type="button" class="btn btn-secondary btn-sm" disabled>' +
                                   '<i class="fas fa-ban"></i> Sin notas' +
                                   '</button>';
                        }

                        html += '</td>';
                        html += '</tr>';
                    });

                    html += '</tbody></table></div>';
                    $('#contenedorModulos').html(html);
                } else {
                    $('#contenedorModulos').html('<div class="rnt-vacio">' +
                        '<i class="fas fa-book"></i> Este programa no tiene módulos registrados.' +
                        '</div>');
                }
            },
            error: function() {
                $('#loadingModulos').hide();
                $('#contenedorModulos').html('<div class="alert alert-danger text-center">' +
                    '<i class="fas fa-times-circle"></i> Error al cargar los módulos.' +
                    '</div>');
            }
        });
    });

    // Evento: Generar PDF con selección de fecha. Usa el mismo generador
    // (vistas/componentes/generar-pdf-calificaciones-tcpdf.php, por GET) y la
    // misma secuencia de 3 fechas que "Registro de Calificaciones" y
    // "Reporte de Calificaciones" del docente.
    //
    // El formulario se muestra en un modal de SweetAlert2 EN ESTA MISMA
    // pantalla (no en una pestaña aparte). Al confirmar, se navega ESTA
    // MISMA pestaña al PDF (en vez de abrir una nueva con window.open):
    // abrir una ventana nueva después de que se resuelve una Promesa
    // (el .then() del SweetAlert) queda bloqueado sin aviso en algunos
    // navegadores, mientras que navegar la pestaña actual nunca se bloquea.
    $(document).on('click', '.btnGenerarPDF', function() {
        const moduloID = $(this).data('modulo-id');
        const programaID = $(this).data('programa-id');
        const moduloNombre = $(this).data('modulo-nombre');
        const moduloCodigo = $(this).data('modulo-codigo');
        const programaNombre = $(this).data('programa-nombre');
        const docenteNombre = $(this).data('docente-nombre');
        const mencionesDocente = $(this).data('menciones-docente');
        const grado = $(this).data('grado');
        const fechaInicioDB = $(this).data('fecha-inicio');
        const fechaFinalDB = $(this).data('fecha-final');
        const sigla = $(this).data('sigla');

        // Nombre del docente con sus menciones/títulos académicos adelante
        // (ej. "Esp. Lic. JUAN PÉREZ"), igual que en el PDF de las otras
        // dos planillas.
        const docenteNombreParaPDF = mencionesDocente
            ? mencionesDocente + ' ' + docenteNombre
            : docenteNombre;

        // Fecha de hoy en formato YYYY-MM-DD, para dejarla precargada como
        // sugerencia en "Fecha de impresión" (editable igual).
        const hoyISO = new Date().toLocaleDateString('sv-SE');

        // Este botón vive dentro del modal de Bootstrap "Módulos del
        // Programa" (ya abierto). Mientras ese modal sigue abierto, Bootstrap
        // atrapa el foco dentro de él y le pelea el foco al cuadro de
        // SweetAlert2 (que se agrega en el <body>), así que los clics y el
        // tipeo dentro del cuadro de fechas dejaban de registrarse ("no pasa
        // nada" al confirmar). Esto solo pasa en esta pantalla porque acá el
        // botón está DENTRO de un modal ya abierto (en las otras dos
        // pantallas no lo está). La solución: cerrar el modal de módulos y
        // ESPERAR a que termine de cerrarse del todo (su evento
        // "hidden.bs.modal", después de la animación de ~300ms y de que
        // Bootstrap limpie el backdrop) antes de mostrar el cuadro de
        // fechas; mostrarlo de inmediato hacía que los dos overlays se
        // pisaran mientras Bootstrap todavía estaba a mitad de la animación
        // de cierre. Si cancela, se vuelve a abrir el modal de módulos.
        const $modalModulos = $('#ModalModulosPrograma');

        $modalModulos.one('hidden.bs.modal', function mostrarDialogoFechas() {
            Swal.fire({
            title: '<i class="la la-calendar"></i> Fechas del Módulo',
            html: `
                <div style="text-align: left; padding: 20px;">
                    <h5 style="color: #5867dd; margin-bottom: 15px;">
                        <i class="la la-book"></i> ${moduloNombre}
                    </h5>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="fechaInicioPDF" style="font-weight: bold; color: #333; display: block; margin-bottom: 5px;">
                            <i class="la la-calendar-check-o"></i> Fecha de Inicio:
                        </label>
                        <input type="date" id="fechaInicioPDF" class="swal2-input" style="width: 100%; padding: 10px; margin: 0;" value="${fechaInicioDB || ''}">
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="fechaFinPDF" style="font-weight: bold; color: #333; display: block; margin-bottom: 5px;">
                            <i class="la la-calendar-times-o"></i> Fecha de Finalización:
                        </label>
                        <input type="date" id="fechaFinPDF" class="swal2-input" style="width: 100%; padding: 10px; margin: 0;" value="${fechaFinalDB || ''}">
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="fechaImpresionPDF" style="font-weight: bold; color: #333; display: block; margin-bottom: 5px;">
                            <i class="fa fa-print"></i> Fecha de impresión del documento (para la firma "Oruro, ..."):
                        </label>
                        <input type="date" id="fechaImpresionPDF" class="swal2-input" style="width: 100%; padding: 10px; margin: 0;" value="${hoyISO}">
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="fa fa-print"></i> Generar PDF',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            focusConfirm: false,
            preConfirm: () => {
                const fechaInicio = document.getElementById('fechaInicioPDF').value;
                const fechaFin = document.getElementById('fechaFinPDF').value;
                const fechaImpresion = document.getElementById('fechaImpresionPDF').value;

                if (!fechaInicio || !fechaFin || !fechaImpresion) {
                    Swal.showValidationMessage('Por favor, complete las tres fechas');
                    return false;
                }
                if (fechaInicio > fechaFin) {
                    Swal.showValidationMessage('La fecha de inicio debe ser anterior o igual a la fecha de finalización');
                    return false;
                }
                return { fechaInicio, fechaFin, fechaImpresion };
            }
            }).then((result) => {
                if (!result.value) { // SweetAlert2 v8: no tiene isConfirmed (value trae las fechas)
                    // Canceló: volver a abrir el modal de módulos donde estaba.
                    $modalModulos.modal('show');
                    return;
                }

                const { fechaInicio, fechaFin, fechaImpresion } = result.value;

                const url = 'vistas/componentes/generar-pdf-calificaciones-tcpdf.php?' +
                    'moduloID=' + moduloID +
                    '&programaID=' + programaID +
                    '&moduloNombre=' + encodeURIComponent(moduloNombre) +
                    '&moduloCodigo=' + encodeURIComponent(moduloCodigo) +
                    '&programaNombre=' + encodeURIComponent(programaNombre) +
                    '&gradoAcademico=' + encodeURIComponent(grado || '') +
                    '&docenteNombre=' + encodeURIComponent(docenteNombreParaPDF || '') +
                    '&fechaInicio=' + encodeURIComponent(fechaInicio) +
                    '&fechaFin=' + encodeURIComponent(fechaFin) +
                    '&fechaGeneracion=' + encodeURIComponent(fechaImpresion) +
                    '&sigla=' + encodeURIComponent(sigla || '');

                // Se muestra en ESTA MISMA pantalla (navega la pestaña actual
                // al PDF) en vez de abrir una pestaña nueva.
                window.location.href = url;
            });
        });

        $modalModulos.modal('hide');
    });

    // Evento: Resetear (borrar) las notas de un módulo para cargarlas de nuevo.
    // Solo ADM. Igual que al imprimir, se cierra primero el modal de módulos
    // para que Bootstrap no le robe el foco al cuadro del motivo.
    $(document).on('click', '.btnResetearNotas', function() {
        const moduloID = $(this).data('modulo-id');
        const programaID = $(this).data('programa-id');
        const moduloNombre = $(this).data('modulo-nombre');
        const calificados = $(this).data('calificados');
        const $modalModulos = $('#ModalModulosPrograma');

        $modalModulos.one('hidden.bs.modal', function() {
            Swal.fire({
                type: 'warning',
                title: 'Resetear calificaciones',
                html: 'Se borrarán las <strong>' + calificados + ' nota(s)</strong> del módulo<br><strong>' + escAttr(moduloNombre) + '</strong>' +
                      '<br><small class="text-muted">El docente podrá cargarlas de nuevo. Queda una copia con el motivo.</small>',
                input: 'textarea',
                inputPlaceholder: 'Motivo del reseteo (obligatorio)',
                inputValidator: function(valor) {
                    if (!valor || valor.trim().length < 5) return 'Indique el motivo del reseteo';
                },
                showCancelButton: true,
                confirmButtonText: 'Sí, borrar notas',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#d33',
                showLoaderOnConfirm: true,
                preConfirm: function(motivo) {
                    return $.ajax({
                        url: 'ajax/reportenotas.ajax.php',
                        type: 'POST',
                        dataType: 'json',
                        data: { action: 'resetearCalificaciones', moduloID: moduloID, programaID: programaID, motivo: motivo.trim() }
                    }).then(function(resp) {
                        if (!resp || resp.status !== 'success') {
                            Swal.showValidationMessage((resp && resp.message) || 'No se pudieron resetear las calificaciones');
                            return false;
                        }
                        return resp;
                    }, function() {
                        Swal.showValidationMessage('No se pudieron resetear las calificaciones');
                        return false;
                    });
                },
                allowOutsideClick: function() { return !Swal.isLoading(); }
            }).then(function(result) {
                if (result.value) {
                    Swal.fire({ type: 'success', title: 'Listo', text: result.value.message, timer: 1800, showConfirmButton: false });
                }
                // Volver al modal de módulos con la lista recargada
                if ($ultimoVerModulos) {
                    $ultimoVerModulos.trigger('click');
                } else {
                    $modalModulos.modal('show');
                }
            });
        });

        $modalModulos.modal('hide');
    });

    // Evento: Ver Panel de Auditoría
    $(document).on('click', '.btnVerAuditoria', function() {
        const moduloID = $(this).data('modulo-id');
        const programaID = $(this).data('programa-id');
        const moduloNombre = $(this).data('modulo-nombre');

        $('#nombreModuloAuditoria').text(moduloNombre);
        $('#loadingAuditoria').show();
        $('#resumenAuditoria').html('');
        $('#contenedorAuditoria').html('');

        // Mostrar el modal
        $('#ModalAuditoria').modal('show');

        // Petición AJAX para obtener auditoría
        $.ajax({
            url: 'ajax/reportenotas.ajax.php',
            type: 'POST',
            data: {
                action: 'obtenerAuditoria',
                moduloID: moduloID,
                programaID: programaID
            },
            dataType: 'json',
            success: function(response) {
                $('#loadingAuditoria').hide();

                if (response.status === 'success') {
                    // Mostrar resumen
                    if (response.resumen) {
                        const r = response.resumen;
                        const modificadas = parseInt(r.TotalModificadas) || 0;
                        let resumenHTML = '<div class="rnt-resumen">' +
                            '<div class="rnt-resumen-item"><span>Calificaciones</span><strong>' + (parseInt(r.TotalCalificaciones) || 0) + '</strong></div>' +
                            '<div class="rnt-resumen-item' + (modificadas > 0 ? ' is-alerta' : '') + '"><span>Modificadas</span><strong>' + modificadas + '</strong></div>' +
                            '<div class="rnt-resumen-item"><span>Primer registro</span><strong class="is-texto">' + escAttr(r.PrimeraFechaRegistro || 'N/A') + '</strong></div>' +
                            '<div class="rnt-resumen-item"><span>Última modificación</span><strong class="is-texto">' + escAttr(r.UltimaModificacion || 'N/A') + '</strong></div>' +
                        '</div>';
                        $('#resumenAuditoria').html(resumenHTML);
                    }

                    // Mostrar tabla de auditoría
                    if (response.data && response.data.length > 0) {
                        let html = '<div class="table-responsive"><table class="table table-striped table-bordered table-hover" id="tablaAuditoria">';
                        html += '<thead>';
                        html += '<tr>';
                        html += '<th class="text-center" style="width: 5%;">Nº</th>';
                        html += '<th>Estudiante</th>';
                        html += '<th class="text-center">Nota</th>';
                        html += '<th>Registrado Por</th>';
                        html += '<th class="text-center">Fecha Registro</th>';
                        html += '<th>Modificado Por</th>';
                        html += '<th class="text-center">Fecha Modificación</th>';
                        html += '<th class="text-center">Estado</th>';
                        html += '</tr>';
                        html += '</thead><tbody>';

                        let contador = 0;
                        response.data.forEach(function(item) {
                            contador++;

                            let badgeModificado = item.FueModificado === 'SÍ'
                                ? '<span class="badge badge-warning">MODIFICADO</span>'
                                : '<span class="badge badge-success">ORIGINAL</span>';

                            html += '<tr>';
                            html += '<td class="text-center">' + contador + '</td>';
                            html += '<td>' + escAttr(item.NombreEstudiante) + '</td>';
                            html += '<td class="text-center"><strong>' + escAttr(item.Nota || 'N/A') + '</strong></td>';
                            html += '<td>' + (item.UsuarioRegistro ? escAttr(item.UsuarioRegistro) : '<span class="text-muted">No registrado</span>');
                            if (item.TipoUsuarioRegistro) {
                                html += ' <span class="badge badge-secondary badge-sm">' + item.TipoUsuarioRegistro + '</span>';
                            }
                            html += '</td>';
                            html += '<td class="text-center">' + (item.FechaRegistro || 'N/A') + '</td>';
                            html += '<td>' + (item.UsuarioModificacion ? escAttr(item.UsuarioModificacion) : '<span class="text-muted">No modificado</span>');
                            if (item.TipoUsuarioModificacion) {
                                html += ' <span class="badge badge-secondary badge-sm">' + item.TipoUsuarioModificacion + '</span>';
                            }
                            html += '</td>';
                            html += '<td class="text-center">' + (item.FechaModificacion || '<span class="text-muted">-</span>') + '</td>';
                            html += '<td class="text-center">' + badgeModificado + '</td>';
                            html += '</tr>';
                        });

                        html += '</tbody></table></div>';
                        $('#contenedorAuditoria').html(html);

                        // Inicializar DataTable
                        $('#tablaAuditoria').DataTable({
                            language: {
                                search: "Buscar:",
                                lengthMenu: "Mostrar _MENU_ registros",
                                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                                paginate: {
                                    first: "Primero",
                                    last: "Último",
                                    next: "Siguiente",
                                    previous: "Anterior"
                                },
                                emptyTable: "No hay datos de auditoría",
                                zeroRecords: "No se encontraron resultados"
                            },
                            order: [[6, 'desc']], // Ordenar por fecha de modificación descendente
                            pageLength: 10
                        });
                    } else {
                        $('#contenedorAuditoria').html('<div class="rnt-vacio">' +
                            '<i class="fas fa-history"></i> No hay datos de auditoría para este módulo.' +
                            '</div>');
                    }
                } else {
                    $('#contenedorAuditoria').html('<div class="alert alert-danger text-center">' +
                        '<i class="fas fa-times-circle"></i> ' + (response.message || 'Error al cargar la auditoría') +
                        '</div>');
                }
            },
            error: function() {
                $('#loadingAuditoria').hide();
                $('#contenedorAuditoria').html('<div class="alert alert-danger text-center">' +
                    '<i class="fas fa-times-circle"></i> Error al cargar la auditoría.' +
                    '</div>');
            }
        });
    });

    // Actualizar fecha
    function actualizarFecha() {
        const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const fecha = new Date().toLocaleDateString('es-ES', opciones);
        $('#lafecha').text(fecha.charAt(0).toUpperCase() + fecha.slice(1));
    }
    actualizarFecha();
    setInterval(actualizarFecha, 60000);
});
