/**
 * Panel principal: administración de "Noticias e Informaciones" y
 * "Cronograma de Defensa" (solo Administrador/Secretaría).
 *
 * Se difiere todo a window 'load' porque este script se imprime ANTES que
 * jQuery/SweetAlert2 en el HTML final de la página (el layout los agrega al
 * final del documento); para cuando el evento 'load' dispara, ya están
 * disponibles.
 */
(function () {
    function iniciar() {
        if (typeof $ === 'undefined') return;

        // ============================================
        // NOTICIAS
        // ============================================
        function abrirModalNuevaNoticia() {
            $('#formNoticia')[0].reset();
            $('#noticiaIdNoticia').val('');
            $('#noticiaFecha').val(new Date().toISOString().slice(0, 10));
            $('#modalNoticiaTitulo').html('<i class="fa fa-newspaper"></i> Nueva Noticia');
            $('#btnEliminarNoticia').hide();
            $('#modalNoticia').modal('show');
        }

        function abrirModalEditarNoticia($btn) {
            $('#formNoticia')[0].reset();
            $('#noticiaIdNoticia').val($btn.data('id'));
            $('#noticiaTitulo').val($btn.data('titulo'));
            $('#noticiaDescripcion').val($btn.data('descripcion'));
            $('#noticiaAutor').val($btn.data('autor'));
            $('#noticiaEtiqueta').val($btn.data('etiqueta'));
            $('#noticiaEnlace').val($btn.data('enlace'));
            $('#noticiaOrden').val($btn.data('orden'));
            $('#noticiaFecha').val($btn.data('fecha'));
            $('#modalNoticiaTitulo').html('<i class="fa fa-edit"></i> Editar Noticia');
            $('#btnEliminarNoticia').show().data('id', $btn.data('id'));
            $('#modalNoticia').modal('show');
        }

        function eliminarNoticia(idNoticia) {
            Swal.fire({
                icon: 'warning',
                title: '¿Eliminar esta noticia?',
                text: 'Esta acción no se puede deshacer.',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: 'ajax/noticiapanel.ajax.php',
                    method: 'POST',
                    data: { accion: 'anular', idNoticia: idNoticia },
                    dataType: 'json',
                    success: function (resp) {
                        if (resp.success) {
                            Swal.fire({ icon: 'success', title: 'Eliminada', timer: 1200, showConfirmButton: false })
                                .then(function () { location.reload(); });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: resp.mensaje || 'No se pudo eliminar' });
                        }
                    },
                    error: function () {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo eliminar la noticia' });
                    }
                });
            });
        }

        $('#btnNuevaNoticia').on('click', abrirModalNuevaNoticia);

        $(document).on('click', '.btn-editar-noticia', function () {
            abrirModalEditarNoticia($(this));
        });

        $(document).on('click', '.btn-eliminar-noticia', function () {
            eliminarNoticia($(this).data('id'));
        });

        $('#btnEliminarNoticia').on('click', function () {
            eliminarNoticia($(this).data('id'));
        });

        $('#formNoticia').on('submit', function (e) {
            e.preventDefault();

            var idNoticia = $('#noticiaIdNoticia').val();
            var datos = {
                accion: idNoticia ? 'actualizar' : 'registrar',
                idNoticia: idNoticia,
                titulo: $('#noticiaTitulo').val().trim(),
                descripcion: $('#noticiaDescripcion').val().trim(),
                autor: $('#noticiaAutor').val().trim(),
                etiqueta: $('#noticiaEtiqueta').val(),
                enlace: $('#noticiaEnlace').val().trim(),
                orden: $('#noticiaOrden').val(),
                fechaPublicacion: $('#noticiaFecha').val()
            };

            var $btnSubmit = $(this).find('button[type="submit"]');
            $btnSubmit.prop('disabled', true);

            $.ajax({
                url: 'ajax/noticiapanel.ajax.php',
                method: 'POST',
                data: datos,
                dataType: 'json',
                success: function (resp) {
                    $btnSubmit.prop('disabled', false);
                    if (resp.success) {
                        Swal.fire({ icon: 'success', title: 'Listo', text: resp.mensaje, timer: 1400, showConfirmButton: false })
                            .then(function () { location.reload(); });
                    } else {
                        Swal.fire({ icon: 'warning', title: 'Atención', text: resp.mensaje || 'No se pudo guardar la noticia' });
                    }
                },
                error: function () {
                    $btnSubmit.prop('disabled', false);
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo guardar la noticia' });
                }
            });
        });

        // ============================================
        // CRONOGRAMA DE DEFENSA
        // ============================================
        function abrirModalNuevaDefensa() {
            $('#formDefensa')[0].reset();
            $('#defensaIdDefensa').val('');
            $('#modalDefensaTitulo').html('<i class="fa fa-user-graduate"></i> Nueva Defensa');
            $('#btnEliminarDefensa').hide();
            $('#modalDefensa').modal('show');
        }

        function abrirModalEditarDefensa($btn) {
            $('#formDefensa')[0].reset();
            $('#defensaIdDefensa').val($btn.data('id'));
            $('#defensaNombre').val($btn.data('nombre'));
            $('#defensaPrograma').val($btn.data('programa') || '');
            $('#defensaFecha').val($btn.data('fecha'));
            $('#defensaHora').val($btn.data('hora'));
            $('#defensaLugar').val($btn.data('lugar'));
            $('#defensaDetalle').val($btn.data('detalle'));
            $('#modalDefensaTitulo').html('<i class="fa fa-edit"></i> Editar Defensa');
            $('#btnEliminarDefensa').show().data('id', $btn.data('id'));
            $('#modalDefensa').modal('show');
        }

        function eliminarDefensa(idDefensa) {
            Swal.fire({
                icon: 'warning',
                title: '¿Eliminar esta defensa del cronograma?',
                text: 'Esta acción no se puede deshacer.',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: 'ajax/cronogramadefensa.ajax.php',
                    method: 'POST',
                    data: { accion: 'anular', idDefensa: idDefensa },
                    dataType: 'json',
                    success: function (resp) {
                        if (resp.success) {
                            Swal.fire({ icon: 'success', title: 'Eliminada', timer: 1200, showConfirmButton: false })
                                .then(function () { location.reload(); });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: resp.mensaje || 'No se pudo eliminar' });
                        }
                    },
                    error: function () {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo eliminar la defensa' });
                    }
                });
            });
        }

        $('#btnNuevaDefensa').on('click', abrirModalNuevaDefensa);

        $(document).on('click', '.btn-editar-defensa', function () {
            abrirModalEditarDefensa($(this));
        });

        $(document).on('click', '.btn-eliminar-defensa', function () {
            eliminarDefensa($(this).data('id'));
        });

        $('#btnEliminarDefensa').on('click', function () {
            eliminarDefensa($(this).data('id'));
        });

        $('#formDefensa').on('submit', function (e) {
            e.preventDefault();

            var idDefensa = $('#defensaIdDefensa').val();
            var datos = {
                accion: idDefensa ? 'actualizar' : 'registrar',
                idDefensa: idDefensa,
                nombreEstudiante: $('#defensaNombre').val().trim(),
                programaID: $('#defensaPrograma').val(),
                fechaDefensa: $('#defensaFecha').val(),
                horaDefensa: $('#defensaHora').val(),
                lugar: $('#defensaLugar').val().trim(),
                detalle: $('#defensaDetalle').val().trim()
            };

            var $btnSubmit = $(this).find('button[type="submit"]');
            $btnSubmit.prop('disabled', true);

            $.ajax({
                url: 'ajax/cronogramadefensa.ajax.php',
                method: 'POST',
                data: datos,
                dataType: 'json',
                success: function (resp) {
                    $btnSubmit.prop('disabled', false);
                    if (resp.success) {
                        Swal.fire({ icon: 'success', title: 'Listo', text: resp.mensaje, timer: 1400, showConfirmButton: false })
                            .then(function () { location.reload(); });
                    } else {
                        Swal.fire({ icon: 'warning', title: 'Atención', text: resp.mensaje || 'No se pudo guardar la defensa' });
                    }
                },
                error: function () {
                    $btnSubmit.prop('disabled', false);
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo guardar la defensa' });
                }
            });
        });

        // ============================================
        // CARRUSEL DEL PANEL (diapositivas por programa)
        // ============================================
        function cargarCarrusel() {
            $.ajax({
                url: 'ajax/carruselpanel.ajax.php',
                method: 'POST',
                data: { accion: 'listar' },
                dataType: 'json',
                success: function (resp) {
                    var $tbody = $('#tablaCarruselBody');
                    $tbody.empty();

                    if (!resp.success || !resp.data || !resp.data.length) {
                        $tbody.html('<tr><td colspan="5" class="text-center text-muted">No hay diapositivas registradas</td></tr>');
                        return;
                    }

                    resp.data.forEach(function (s) {
                        var titulo = s.TituloPersonalizado || s.NombrePrograma;
                        var imagen = s.ImagenURL || 'vistas/recursos/assets/media/co/c1.png';

                        var fila = $('<tr></tr>')
                            .append($('<td></td>').html('<img src="' + imagen + '" style="width:60px;height:40px;object-fit:cover;border-radius:4px;">'))
                            .append($('<td></td>').text(s.NombrePrograma + ' (' + s.GradoAcademico + ')'))
                            .append($('<td></td>').text(titulo))
                            .append($('<td class="text-center"></td>').text(s.Orden))
                            .append(
                                $('<td class="text-right"></td>').append(
                                    $('<button type="button" class="btn btn-xs btn-clean btn-icon" title="Editar"><i class="la la-edit"></i></button>')
                                        .on('click', function () { abrirModalEdicionSlide(s); }),
                                    ' ',
                                    $('<button type="button" class="btn btn-xs btn-clean btn-icon text-danger" title="Eliminar"><i class="la la-trash"></i></button>')
                                        .on('click', function () { eliminarSlide(s.IdSlide); })
                                )
                            );
                        $tbody.append(fila);
                    });
                },
                error: function () {
                    $('#tablaCarruselBody').html('<tr><td colspan="5" class="text-center text-danger">Error al cargar el carrusel</td></tr>');
                }
            });
        }

        function abrirModalNuevaSlide() {
            $('#formCarruselSlide')[0].reset();
            $('#slideIdSlide').val('');
            $('#slideImagenPreview').hide().attr('src', '');
            $('#modalCarruselSlideTitulo').html('<i class="fa fa-image"></i> Nueva Diapositiva');
            $('#btnEliminarDiapositiva').hide();
            $('#modalListaCarrusel').modal('hide');
            $('#modalCarruselSlide').modal('show');
        }

        function abrirModalEdicionSlide(s) {
            $('#formCarruselSlide')[0].reset();
            $('#slideIdSlide').val(s.IdSlide);
            $('#slidePrograma').val(s.ProgramaID);
            $('#slideTitulo').val(s.TituloPersonalizado || '');
            $('#slideDescripcion').val(s.DescripcionPersonalizada || '');
            $('#slideOrden').val(s.Orden);
            if (s.ImagenURL) {
                $('#slideImagenPreview').attr('src', s.ImagenURL).show();
            } else {
                $('#slideImagenPreview').hide().attr('src', '');
            }
            $('#modalCarruselSlideTitulo').html('<i class="fa fa-edit"></i> Editar Diapositiva');
            $('#btnEliminarDiapositiva').show().data('id', s.IdSlide);
            $('#modalListaCarrusel').modal('hide');
            $('#modalCarruselSlide').modal('show');
        }

        function eliminarSlide(idSlide) {
            Swal.fire({
                icon: 'warning',
                title: '¿Eliminar esta diapositiva?',
                text: 'Esta acción no se puede deshacer.',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: 'ajax/carruselpanel.ajax.php',
                    method: 'POST',
                    data: { accion: 'anular', idSlide: idSlide },
                    dataType: 'json',
                    success: function (resp) {
                        if (resp.success) {
                            $('#modalCarruselSlide').modal('hide');
                            cargarCarrusel();
                            Swal.fire({ icon: 'success', title: 'Eliminada', timer: 1200, showConfirmButton: false })
                                .then(function () { location.reload(); });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: resp.mensaje || 'No se pudo eliminar' });
                        }
                    },
                    error: function () {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo eliminar la diapositiva' });
                    }
                });
            });
        }

        $('#btnGestionarCarrusel').on('click', function () {
            $('#modalListaCarrusel').modal('show');
        });
        $('#modalListaCarrusel').on('show.bs.modal', cargarCarrusel);

        $('#btnNuevaDiapositiva').on('click', abrirModalNuevaSlide);

        $('#modalCarruselSlide').on('hidden.bs.modal', function () {
            $('#modalListaCarrusel').modal('show');
        });

        $('#slideImagen').on('change', function () {
            var archivo = this.files && this.files[0];
            if (!archivo) return;
            var lector = new FileReader();
            lector.onload = function (e) {
                $('#slideImagenPreview').attr('src', e.target.result).show();
            };
            lector.readAsDataURL(archivo);
        });

        $('#formCarruselSlide').on('submit', function (e) {
            e.preventDefault();

            var idSlide = $('#slideIdSlide').val();
            var formData = new FormData(this);
            formData.append('accion', idSlide ? 'actualizar' : 'registrar');
            formData.append('idSlide', idSlide);
            formData.append('programaID', $('#slidePrograma').val());
            formData.append('titulo', $('#slideTitulo').val().trim());
            formData.append('descripcion', $('#slideDescripcion').val().trim());
            formData.append('orden', $('#slideOrden').val());
            var archivoImagen = $('#slideImagen')[0].files[0];
            if (archivoImagen) {
                formData.append('imagen', archivoImagen);
            }

            var $btnSubmit = $(this).find('button[type="submit"]');
            $btnSubmit.prop('disabled', true);

            $.ajax({
                url: 'ajax/carruselpanel.ajax.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (resp) {
                    $btnSubmit.prop('disabled', false);
                    if (resp.success) {
                        Swal.fire({ icon: 'success', title: 'Listo', text: resp.mensaje, timer: 1400, showConfirmButton: false })
                            .then(function () { location.reload(); });
                    } else {
                        Swal.fire({ icon: 'warning', title: 'Atención', text: resp.mensaje || 'No se pudo guardar la diapositiva' });
                    }
                },
                error: function () {
                    $btnSubmit.prop('disabled', false);
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo guardar la diapositiva' });
                }
            });
        });

        $('#btnEliminarDiapositiva').on('click', function () {
            eliminarSlide($(this).data('id'));
        });
    }

    if (document.readyState === 'complete') {
        iniciar();
    } else {
        window.addEventListener('load', iniciar);
    }
})();
