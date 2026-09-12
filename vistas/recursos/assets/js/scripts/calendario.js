/**
 * Calendario Académico: clases, actividades y clínicas por programa y sede.
 * Vista de solo lectura para Estudiantes; edición para Administrador,
 * Secretaría y Docentes (un Docente solo puede tocar sus propias clases,
 * lo que ya viene resuelto desde el servidor en cada evento: extendedProps.editable).
 */
(function () {
    var contexto = window.CALENDARIO_CONTEXTO || { tipoSesion: '', puedeEscribir: false, esAdminOSec: false, programaIDsEstudiante: [], programasPorSede: {} };
    var calendar = null;

    function etiquetaTipo(tipo) {
        return { CLASE: 'Clase', ACTIVIDAD: 'Actividad', CLINICA: 'Clínica', PAGO: 'Vencimiento de pago' }[tipo] || tipo;
    }

    function colorTipo(tipo) {
        return { CLASE: 'badge-primary', ACTIVIDAD: 'badge-info', CLINICA: 'badge-dark', PAGO: 'badge-warning' }[tipo] || 'badge-secondary';
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

    $(document).ready(function () {

        // ============================================
        // FILTROS: Sede -> Programa (cascada), Docente
        // ============================================
        function aplicarFiltroSedeSobrePrograma() {
            var sede = $('#filtroSedeCalendario').val();
            var $programa = $('#filtroProgramaCalendario');
            var seleccionActual = $programa.val();

            $programa.find('option').each(function () {
                if (!$(this).val()) return;
                var perteneceSede = !sede || $(this).data('sede') === sede;
                $(this).toggle(perteneceSede);
            });

            if (seleccionActual) {
                var $opt = $programa.find('option[value="' + seleccionActual + '"]');
                if (sede && $opt.data('sede') !== sede) {
                    $programa.val('');
                }
            }
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

        // Estudiante: preseleccionar su propio programa en el filtro (no restringe, solo sugiere)
        if (contexto.tipoSesion === 'EST' && contexto.programaIDsEstudiante && contexto.programaIDsEstudiante.length === 1) {
            $('#filtroProgramaCalendario').val(String(contexto.programaIDsEstudiante[0]));
            var sedeDelPrograma = $('#filtroProgramaCalendario').find('option:selected').data('sede');
            if (sedeDelPrograma) {
                $('#filtroSedeCalendario').val(sedeDelPrograma);
                aplicarFiltroSedeSobrePrograma();
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
                        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar los eventos del calendario' });
                    }
                });
            },
            eventClick: function (info) {
                var props = info.event.extendedProps;
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
                        Swal.fire({ icon: 'error', title: 'No se pudo mover el evento', text: resp.mensaje || '' });
                    }
                },
                error: function () {
                    info.revert();
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo actualizar el evento' });
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

            $('#detalleEventoTipoBadge').text(etiquetaTipo(props.tipoEvento)).attr('class', 'badge mb-2 ' + colorTipo(props.tipoEvento));
            $('#detalleEventoTitulo').text(evento.title);
            $('#detalleEventoPrograma').text((props.nombrePrograma || '') + ' - ' + (props.gradoAcademico || '') + ' (' + (props.codigoPrograma || '') + ')');
            $('#detalleEventoSede').text(props.sede || '-');
            $('#detalleEventoDocente').text(esPago ? ('Estudiante: ' + (props.nombreEstudiante || '-')) : (props.nombreDocente || 'Sin asignar'));
            $('#detalleEventoHorario').text(esPago ? ('Fecha límite: ' + formatoFechaHora(evento.startStr)) : (formatoFechaHora(evento.startStr) + '  a  ' + formatoFechaHora(evento.endStr)));

            if (props.descripcion) {
                $('#detalleEventoDescripcion').text(props.descripcion);
                $('#detalleEventoDescripcionWrap').show();
            } else {
                $('#detalleEventoDescripcionWrap').hide();
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
                        Swal.fire({ icon: 'success', title: 'Listo', text: resp.mensaje, timer: 1600, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'warning', title: 'Atención', text: resp.mensaje || 'No se pudo guardar el evento' });
                    }
                },
                error: function () {
                    $btnSubmit.prop('disabled', false);
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo guardar el evento' });
                }
            });
        });

        // ============================================
        // ELIMINAR
        // ============================================
        $('#btnEliminarEvento').on('click', function () {
            var idEvento = $(this).data('id');

            Swal.fire({
                icon: 'warning',
                title: '¿Eliminar este evento?',
                text: 'Esta acción no se puede deshacer.',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: 'ajax/calendarioevento.ajax.php',
                    method: 'POST',
                    data: { accion: 'anularEvento', idEvento: idEvento },
                    dataType: 'json',
                    success: function (resp) {
                        if (resp.success) {
                            $('#modalEventoCalendario').modal('hide');
                            calendar.refetchEvents();
                            Swal.fire({ icon: 'success', title: 'Eliminado', timer: 1400, showConfirmButton: false });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: resp.mensaje || 'No se pudo eliminar' });
                        }
                    },
                    error: function () {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo eliminar el evento' });
                    }
                });
            });
        });
    });
})();
