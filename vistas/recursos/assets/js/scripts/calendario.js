/**
 * Calendario Académico: clases, actividades y clínicas por programa y sede.
 * Vista de solo lectura para Estudiantes; edición para Administrador,
 * Secretaría y Docentes (un Docente solo puede tocar sus propias clases,
 * lo que ya viene resuelto desde el servidor en cada evento: extendedProps.editable).
 */
(function () {
    var contexto = window.CALENDARIO_CONTEXTO || { tipoSesion: '', puedeEscribir: false, esAdminOSec: false, programaIDsEstudiante: [], programasPorSede: {} };
    var calendar = null;
    // Asignada dentro de inicializarPlazoCuota(); permite abrir el formulario
    // de edición del Plazo de Cuota desde un clic directo en el calendario.
    var abrirModalEdicionPlazoCuota = null;

    function etiquetaTipo(tipo) {
        return { CLASE: 'Clase', ACTIVIDAD: 'Actividad', CLINICA: 'Clínica', PAGO: 'Vencimiento de pago (individual)', PLAZOCUOTA: 'Plazo de cuota (todo el programa)' }[tipo] || tipo;
    }

    function colorTipo(tipo) {
        return { CLASE: 'badge-primary', ACTIVIDAD: 'badge-info', CLINICA: 'badge-dark', PAGO: 'badge-warning', PLAZOCUOTA: 'badge-danger' }[tipo] || 'badge-secondary';
    }

    function formatoFechaHora(iso) {
        var d = new Date(iso);
        if (isNaN(d.getTime())) return '';
        return d.toLocaleDateString('es-BO', { day: '2-digit', month: 'long', year: 'numeric' }) +
            ' · ' + d.toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit' });
    }

    // Convierte un objeto Date a "YYYY-MM-DDTHH:mm" para <input type="datetime-local">
    function aInputLocal(date) {
        var pad = function (n) { return String(n).padStart(2, '0'); };
        return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate()) +
            'T' + pad(date.getHours()) + ':' + pad(date.getMinutes());
    }

    // ============================================
    // PLAZO DE CUOTA POR PROGRAMA (panel ADM/SEC)
    // ============================================
    function inicializarPlazoCuota() {
        function formatoFecha(iso) {
            var d = new Date(iso + 'T00:00:00');
            if (isNaN(d.getTime())) return iso;
            return d.toLocaleDateString('es-BO', { day: '2-digit', month: 'long', year: 'numeric' });
        }

        function cargarPlazosCuota() {
            $.ajax({
                url: 'ajax/plazocuotaprograma.ajax.php',
                method: 'POST',
                data: { accion: 'listarPlazos' },
                dataType: 'json',
                success: function (resp) {
                    var $tbody = $('#tablaPlazosCuotaBody');
                    $tbody.empty();

                    if (!resp.success || !resp.data || !resp.data.length) {
                        $tbody.html('<tr><td colspan="6" class="text-center text-muted">No hay plazos de cuota registrados</td></tr>');
                        return;
                    }

                    var hoy = new Date().toISOString().slice(0, 10);

                    resp.data.forEach(function (p) {
                        var vencido = p.FechaPlazo < hoy;
                        var estadoBadge = vencido
                            ? '<span class="badge badge-danger">Vencido</span>'
                            : '<span class="badge badge-success">Vigente</span>';

                        var fila = $('<tr></tr>')
                            .append($('<td></td>').text(p.NombrePrograma + ' (' + p.CodigoPrograma + ')'))
                            .append($('<td></td>').text(p.Sede || '-'))
                            .append($('<td></td>').text(formatoFecha(p.FechaPlazo)))
                            .append($('<td></td>').text(p.Descripcion || '-'))
                            .append($('<td class="text-center"></td>').html(estadoBadge))
                            .append(
                                $('<td class="text-right"></td>').append(
                                    $('<button type="button" class="btn btn-sm btn-outline-secondary" title="Editar"><i class="la la-edit"></i> Editar</button>')
                                        .on('click', function () { abrirModalEdicionPlazo(p); }),
                                    ' ',
                                    $('<button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="la la-trash"></i> Eliminar</button>')
                                        .on('click', function () { eliminarPlazoCuota(p.IdPlazo); })
                                )
                            );
                        $tbody.append(fila);
                    });
                },
                error: function () {
                    $('#tablaPlazosCuotaBody').html('<tr><td colspan="6" class="text-center text-danger">Error al cargar los plazos</td></tr>');
                }
            });
        }

        function abrirModalNuevoPlazo() {
            $('#formPlazoCuota')[0].reset();
            $('#plazoIdPlazo').val('');
            $('#modalPlazoCuotaTitulo').html('<i class="fa fa-hourglass-half"></i> Nuevo Plazo de Cuota');
            $('#btnEliminarPlazoCuota').hide();
            $('#modalListaPlazosCuota').modal('hide');
            $('#modalPlazoCuota').modal('show');
        }

        function abrirModalEdicionPlazo(p) {
            $('#formPlazoCuota')[0].reset();
            $('#plazoIdPlazo').val(p.IdPlazo);
            $('#plazoPrograma').val(p.ProgramaID);
            $('#plazoFecha').val(p.FechaPlazo);
            $('#plazoDescripcion').val(p.Descripcion || '');
            $('#modalPlazoCuotaTitulo').html('<i class="fa fa-edit"></i> Editar Plazo de Cuota');
            $('#btnEliminarPlazoCuota').show().data('id', p.IdPlazo);
            $('#modalListaPlazosCuota').modal('hide');
            $('#modalPlazoCuota').modal('show');
        }

        function eliminarPlazoCuota(idPlazo) {
            Swal.fire({
                type: 'warning',
                title: '¿Eliminar este plazo de cuota?',
                text: 'Esta acción no se puede deshacer.',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (!result.value) return;

                $.ajax({
                    url: 'ajax/plazocuotaprograma.ajax.php',
                    method: 'POST',
                    data: { accion: 'anularPlazo', idPlazo: idPlazo },
                    dataType: 'json',
                    success: function (resp) {
                        if (resp.success) {
                            $('#modalPlazoCuota').modal('hide');
                            cargarPlazosCuota();
                            if (calendar) calendar.refetchEvents();
                            Swal.fire({ type: 'success', title: 'Eliminado', timer: 1400, showConfirmButton: false });
                        } else {
                            Swal.fire({ type: 'error', title: 'Error', text: resp.mensaje || 'No se pudo eliminar' });
                        }
                    },
                    error: function () {
                        Swal.fire({ type: 'error', title: 'Error', text: 'No se pudo eliminar el plazo' });
                    }
                });
            });
        }

        abrirModalEdicionPlazoCuota = abrirModalEdicionPlazo;

        // Abrir el listado (modal) de Plazos de Cuota desde el botón de la barra de herramientas
        $('#btnAbrirPlazosCuota').on('click', function () {
            $('#modalListaPlazosCuota').modal('show');
        });
        $('#modalListaPlazosCuota').on('show.bs.modal', cargarPlazosCuota);

        $('#btnNuevoPlazoCuota').on('click', abrirModalNuevoPlazo);

        // Al cerrar el formulario de nuevo/editar (guardado, cancelado o eliminado),
        // se vuelve a mostrar el listado para no perder el contexto.
        $('#modalPlazoCuota').on('hidden.bs.modal', function () {
            $('#modalListaPlazosCuota').modal('show');
        });

        $('#formPlazoCuota').on('submit', function (e) {
            e.preventDefault();

            var idPlazo = $('#plazoIdPlazo').val();
            var datos = {
                accion: idPlazo ? 'actualizarPlazo' : 'registrarPlazo',
                idPlazo: idPlazo,
                programaID: $('#plazoPrograma').val(),
                fechaPlazo: $('#plazoFecha').val(),
                descripcion: $('#plazoDescripcion').val().trim()
            };

            var $btnSubmit = $(this).find('button[type="submit"]');
            $btnSubmit.prop('disabled', true);

            $.ajax({
                url: 'ajax/plazocuotaprograma.ajax.php',
                method: 'POST',
                data: datos,
                dataType: 'json',
                success: function (resp) {
                    $btnSubmit.prop('disabled', false);
                    if (resp.success) {
                        $('#modalPlazoCuota').modal('hide');
                        cargarPlazosCuota();
                        if (calendar) calendar.refetchEvents();
                        Swal.fire({ type: 'success', title: 'Listo', text: resp.mensaje, timer: 1600, showConfirmButton: false });
                    } else {
                        Swal.fire({ type: 'warning', title: 'Atención', text: resp.mensaje || 'No se pudo guardar el plazo' });
                    }
                },
                error: function () {
                    $btnSubmit.prop('disabled', false);
                    Swal.fire({ type: 'error', title: 'Error', text: 'No se pudo guardar el plazo' });
                }
            });
        });

        $('#btnEliminarPlazoCuota').on('click', function () {
            eliminarPlazoCuota($(this).data('id'));
        });
    }

    $(document).ready(function () {

        if (contexto.esAdminOSec) {
            inicializarPlazoCuota();
        }

        // Select2 (mismo tamaño/estilo en Sede, Programa y Docente)
        $('.select2-uniforme').select2({ width: '100%' });

        // ============================================
        // FILTROS: Sede -> Programa (cascada), Docente
        // ============================================

        // Copia completa de las opciones de Programa (con su Sede), para
        // reconstruir el combo al filtrar por Sede: con Select2 no basta con
        // ocultar <option> por CSS como en un <select> nativo, hay que
        // quitarlas/ponerlas del DOM.
        var programasCache = [];
        $('#filtroProgramaCalendario option[value!=""]').each(function () {
            programasCache.push({
                value: $(this).val(),
                sede: $(this).data('sede') || '',
                // .html() conserva el escape que ya aplicó el servidor;
                // .text() lo decodificaría y reinyectarlo sería un riesgo XSS.
                html: $(this).html()
            });
        });

        function aplicarFiltroSedeSobrePrograma() {
            var sede = $('#filtroSedeCalendario').val();
            var $programa = $('#filtroProgramaCalendario');
            var seleccionActual = $programa.val();

            var opciones = '<option value="">Todos los programas</option>';
            programasCache.forEach(function (p) {
                if (!sede || p.sede === sede) {
                    opciones += '<option value="' + p.value + '" data-sede="' + p.sede + '">' + p.html + '</option>';
                }
            });
            $programa.html(opciones);

            if (seleccionActual && $programa.find('option[value="' + seleccionActual + '"]').length) {
                $programa.val(seleccionActual);
            }
            $programa.trigger('change');
        }

        $('#filtroSedeCalendario').on('change', function () {
            aplicarFiltroSedeSobrePrograma();
            if (calendar) calendar.refetchEvents();
        });

        $('#filtroProgramaCalendario, #filtroDocenteCalendario').on('change', function () {
            if (calendar) calendar.refetchEvents();
        });

        if (contexto.esAdminOSec) {
            $.ajax({
                url: 'ajax/calendarioevento.ajax.php',
                method: 'POST',
                data: { accion: 'listarDocentes' },
                dataType: 'json',
                success: function (docentes) {
                    var $filtroDocente = $('#filtroDocenteCalendario');
                    var $formDocente = $('#eventoDocente');
                    (docentes || []).forEach(function (doc) {
                        var opcion = '<option value="' + doc.DocenteID + '">' + doc.NombreCompleto + '</option>';
                        $filtroDocente.append(opcion);
                        $formDocente.append(opcion);
                    });
                }
            });
        }

        // Estudiante: el filtro solo lista sus programas inscritos (el servidor además
        // lo impone); si tiene uno solo, se preselecciona.
        if (contexto.tipoSesion === 'EST' && contexto.programaIDsEstudiante && contexto.programaIDsEstudiante.length === 1) {
            var programaIDInicial = String(contexto.programaIDsEstudiante[0]);
            $('#filtroProgramaCalendario').val(programaIDInicial);
            var sedeDelPrograma = $('#filtroProgramaCalendario').find('option:selected').data('sede');
            if (sedeDelPrograma) {
                // aplicarFiltroSedeSobrePrograma conserva la selección actual
                // de Programa si sigue perteneciendo a la sede filtrada.
                $('#filtroSedeCalendario').val(sedeDelPrograma).trigger('change');
            } else {
                $('#filtroProgramaCalendario').trigger('change');
            }
        }

        // ============================================
        // FULLCALENDAR
        // ============================================
        var el = document.getElementById('calendario-academico');

        calendar = new FullCalendar.Calendar(el, {
            plugins: [
                FullCalendarDayGrid.default,
                FullCalendarTimeGrid.default,
                FullCalendarList.default,
                FullCalendarInteraction.default
            ],
            locales: [FullCalendarLocales.es],
            locale: 'es',
            height: 'parent',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            initialView: 'dayGridMonth',
            editable: contexto.puedeEscribir,
            eventStartEditable: contexto.puedeEscribir,
            eventDurationEditable: contexto.puedeEscribir,
            navLinks: true,
            nowIndicator: true,
            slotMinTime: '07:00:00',
            slotMaxTime: '22:00:00',
            allDaySlot: false,
            events: function (fetchInfo, successCallback, failureCallback) {
                $.ajax({
                    url: 'ajax/calendarioevento.ajax.php',
                    method: 'POST',
                    data: {
                        accion: 'listarEventos',
                        sede: $('#filtroSedeCalendario').val() || '',
                        programaID: $('#filtroProgramaCalendario').val() || '',
                        docenteID: $('#filtroDocenteCalendario').val() || '',
                        desde: fetchInfo.startStr,
                        hasta: fetchInfo.endStr
                    },
                    dataType: 'json',
                    success: function (eventos) {
                        successCallback(eventos || []);
                    },
                    error: function () {
                        failureCallback();
                        Swal.fire({ type: 'error', title: 'Error', text: 'No se pudieron cargar los eventos del calendario' });
                    }
                });
            },
            eventClick: function (info) {
                var props = info.event.extendedProps;
                if (props.tipoEvento === 'PLAZOCUOTA' && contexto.esAdminOSec && abrirModalEdicionPlazoCuota) {
                    abrirModalEdicionPlazoCuota({
                        IdPlazo: props.idPlazo,
                        ProgramaID: props.programaID,
                        FechaPlazo: props.fechaPlazo,
                        Descripcion: props.descripcionOriginal
                    });
                    return;
                }
                if (props.editable) {
                    abrirModalEdicion(info.event);
                } else {
                    abrirModalDetalle(info.event);
                }
            },
            eventDrop: function (info) {
                guardarCambioRapido(info.event, info);
            },
            eventResize: function (info) {
                guardarCambioRapido(info.event, info);
            },
            dateClick: function (info) {
                if (!contexto.puedeEscribir) return;
                var tipoDefault = $('.btn-nuevo-evento').length ? 'CLASE' : 'CLASE';
                abrirModalNuevo(tipoDefault, info.date);
            }
        });

        calendar.render();

        // ============================================
        // Guardar cambios de arrastrar/redimensionar (drag & drop)
        // ============================================
        function guardarCambioRapido(evento, info) {
            $.ajax({
                url: 'ajax/calendarioevento.ajax.php',
                method: 'POST',
                data: {
                    accion: 'actualizarEvento',
                    idEvento: evento.id,
                    tipoEvento: evento.extendedProps.tipoEvento,
                    titulo: evento.title,
                    programaID: evento.extendedProps.programaID,
                    docenteID: evento.extendedProps.docenteID || '',
                    descripcion: evento.extendedProps.descripcion || '',
                    fechaInicio: evento.start.toISOString().slice(0, 19).replace('T', ' '),
                    fechaFin: (evento.end || evento.start).toISOString().slice(0, 19).replace('T', ' ')
                },
                dataType: 'json',
                success: function (resp) {
                    if (!resp.success) {
                        info.revert();
                        Swal.fire({ type: 'error', title: 'No se pudo mover el evento', text: resp.mensaje || '' });
                    }
                },
                error: function () {
                    info.revert();
                    Swal.fire({ type: 'error', title: 'Error', text: 'No se pudo actualizar el evento' });
                }
            });
        }

        // ============================================
        // BOTONES "+ Clase / Actividad / Clínica"
        // ============================================
        $('.btn-nuevo-evento').on('click', function () {
            abrirModalNuevo($(this).data('tipo'), new Date());
        });

        function abrirModalNuevo(tipo, fechaBase) {
            $('#formEventoCalendario')[0].reset();
            $('#eventoIdEvento').val('');
            $('#eventoTipoSelect').val(tipo);
            $('#modalEventoCalendarioTitulo').html('<i class="fa fa-calendar-plus"></i> Nueva ' + etiquetaTipo(tipo));
            $('#btnEliminarEvento').hide();

            var inicio = new Date(fechaBase);
            if (inicio.getHours() === 0 && inicio.getMinutes() === 0) {
                inicio.setHours(8, 0, 0, 0);
            }
            var fin = new Date(inicio.getTime() + 60 * 60 * 1000);
            $('#eventoFechaInicio').val(aInputLocal(inicio));
            $('#eventoFechaFin').val(aInputLocal(fin));

            // Preseleccionar programa si el filtro ya tiene uno elegido
            var programaFiltrado = $('#filtroProgramaCalendario').val();
            if (programaFiltrado) $('#eventoPrograma').val(programaFiltrado);

            $('#modalEventoCalendario').modal('show');
        }

        function abrirModalEdicion(evento) {
            var props = evento.extendedProps;
            $('#formEventoCalendario')[0].reset();
            $('#eventoIdEvento').val(evento.id);
            $('#eventoTipoSelect').val(props.tipoEvento);
            $('#eventoTitulo').val(evento.title);
            $('#eventoPrograma').val(props.programaID);
            if (contexto.esAdminOSec) {
                $('#eventoDocente').val(props.docenteID || '');
            }
            $('#eventoDescripcion').val(props.descripcion || '');
            $('#eventoFechaInicio').val(aInputLocal(evento.start));
            $('#eventoFechaFin').val(aInputLocal(evento.end || evento.start));
            $('#modalEventoCalendarioTitulo').html('<i class="fa fa-edit"></i> Editar ' + etiquetaTipo(props.tipoEvento));
            $('#btnEliminarEvento').show().data('id', evento.id);
            $('#modalEventoCalendario').modal('show');
        }

        function abrirModalDetalle(evento) {
            var props = evento.extendedProps;
            var esPago = props.tipoEvento === 'PAGO';
            var esPlazoCuota = props.tipoEvento === 'PLAZOCUOTA';

            $('#detalleEventoTipoBadge').text(etiquetaTipo(props.tipoEvento)).attr('class', 'badge mb-2 ' + colorTipo(props.tipoEvento));
            $('#detalleEventoTitulo').text(evento.title);
            $('#detalleEventoPrograma').text((props.nombrePrograma || '') + ' - ' + (props.gradoAcademico || '') + ' (' + (props.codigoPrograma || '') + ')');
            $('#detalleEventoSede').text(props.sede || '-');
            $('#detalleEventoDocente').text(esPago ? ('Estudiante: ' + (props.nombreEstudiante || '-')) : (esPlazoCuota ? 'Plazo general del programa' : (props.nombreDocente || 'Sin asignar')));
            $('#detalleEventoHorario').text((esPago || esPlazoCuota) ? ('Fecha límite: ' + formatoFechaHora(evento.startStr)) : (formatoFechaHora(evento.startStr) + '  a  ' + formatoFechaHora(evento.endStr)));

            if (props.descripcion) {
                $('#detalleEventoDescripcion').text(props.descripcion);
                $('#detalleEventoDescripcionWrap').show();
            } else {
                $('#detalleEventoDescripcionWrap').hide();
            }

            // Aclaración de a quién aplica cada tipo de aviso de cuota, para no
            // confundir el vencimiento individual (según la solicitud/orden de
            // pago de un estudiante) con el plazo general del programa (aplica a
            // todos los que están en Plan Regular y no cancelaron su cuota).
            if (props.alcance) {
                $('#detalleEventoAlcance').text(props.alcance);
                $('#detalleEventoAccion').text(props.accionSugerida || '');
                $('#detalleEventoAlcanceWrap').css('border-left-color', esPago ? '#b45309' : '#6d28d9').show();
            } else {
                $('#detalleEventoAlcanceWrap').hide();
            }

            var celular = (props.celular || '').replace(/\D/g, '');
            if (esPago && celular) {
                var mensaje = 'Hola ' + (props.nombreEstudiante || '') + ', le escribimos de la Coordinación de Posgrado FCS-UTO por su cuota pendiente.';
                $('#detalleEventoWhatsapp').attr('href', 'https://wa.me/591' + celular + '?text=' + encodeURIComponent(mensaje)).show();
            } else {
                $('#detalleEventoWhatsapp').hide();
            }

            $('#modalDetalleEvento').modal('show');
        }

        // ============================================
        // GUARDAR (crear / actualizar)
        // ============================================
        $('#formEventoCalendario').on('submit', function (e) {
            e.preventDefault();

            var idEvento = $('#eventoIdEvento').val();
            var fechaInicio = $('#eventoFechaInicio').val().replace('T', ' ') + ':00';
            var fechaFin = $('#eventoFechaFin').val().replace('T', ' ') + ':00';

            var datos = {
                accion: idEvento ? 'actualizarEvento' : 'registrarEvento',
                idEvento: idEvento,
                tipoEvento: $('#eventoTipoSelect').val(),
                titulo: $('#eventoTitulo').val().trim(),
                programaID: $('#eventoPrograma').val(),
                docenteID: $('#eventoDocente').val() || '',
                descripcion: $('#eventoDescripcion').val().trim(),
                fechaInicio: fechaInicio,
                fechaFin: fechaFin
            };

            var $btnSubmit = $(this).find('button[type="submit"]');
            $btnSubmit.prop('disabled', true);

            $.ajax({
                url: 'ajax/calendarioevento.ajax.php',
                method: 'POST',
                data: datos,
                dataType: 'json',
                success: function (resp) {
                    $btnSubmit.prop('disabled', false);
                    if (resp.success) {
                        $('#modalEventoCalendario').modal('hide');
                        calendar.refetchEvents();
                        Swal.fire({ type: 'success', title: 'Listo', text: resp.mensaje, timer: 1600, showConfirmButton: false });
                    } else {
                        Swal.fire({ type: 'warning', title: 'Atención', text: resp.mensaje || 'No se pudo guardar el evento' });
                    }
                },
                error: function () {
                    $btnSubmit.prop('disabled', false);
                    Swal.fire({ type: 'error', title: 'Error', text: 'No se pudo guardar el evento' });
                }
            });
        });

        // ============================================
        // ELIMINAR
        // ============================================
        $('#btnEliminarEvento').on('click', function () {
            var idEvento = $(this).data('id');

            Swal.fire({
                type: 'warning',
                title: '¿Eliminar este evento?',
                text: 'Esta acción no se puede deshacer.',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (!result.value) return;

                $.ajax({
                    url: 'ajax/calendarioevento.ajax.php',
                    method: 'POST',
                    data: { accion: 'anularEvento', idEvento: idEvento },
                    dataType: 'json',
                    success: function (resp) {
                        if (resp.success) {
                            $('#modalEventoCalendario').modal('hide');
                            calendar.refetchEvents();
                            Swal.fire({ type: 'success', title: 'Eliminado', timer: 1400, showConfirmButton: false });
                        } else {
                            Swal.fire({ type: 'error', title: 'Error', text: resp.mensaje || 'No se pudo eliminar' });
                        }
                    },
                    error: function () {
                        Swal.fire({ type: 'error', title: 'Error', text: 'No se pudo eliminar el evento' });
                    }
                });
            });
        });
    });
})();
