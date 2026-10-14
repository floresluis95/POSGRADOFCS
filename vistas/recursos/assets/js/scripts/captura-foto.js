/**
 * Foto del voucher: tomar con cámara y recortar antes de subir.
 *
 * Se activa en cualquier <input type="file" data-captura-foto>, también en los que se
 * crean después por JavaScript (p. ej. la pestaña Clínicas). Agrega un botón "Cámara"
 * junto al campo y, cada vez que se elige o se toma una imagen, abre una ventana para
 * recortarla y girarla. El resultado (JPEG) reemplaza el archivo del propio input y se
 * dispara su evento "change", así cada pantalla sigue con su vista previa y su envío
 * de siempre. Los PDF se dejan tal cual.
 *
 * La cámara del navegador (getUserMedia) solo está permitida en HTTPS o en localhost.
 * Sin eso, en un celular se abre la cámara del teléfono (input con capture) y en una
 * PC se avisa que hay que usar HTTPS o elegir un archivo.
 */
(function ($) {
    'use strict';

    const SELECTOR = 'input[type="file"][data-captura-foto]';
    // Lado mayor de la imagen final: suficiente para leer un voucher y liviana (< 5 MB)
    const LADO_MAXIMO = 2000;
    const CALIDAD_JPEG = 0.9;

    let cropper = null;
    let inputDestino = null;
    let nombreOriginal = 'voucher';
    let streamCamara = null;

    const esCelular = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
    const camaraDisponible = !!(window.isSecureContext && navigator.mediaDevices && navigator.mediaDevices.getUserMedia);

    // ------------------------------------------------------------------
    // Ventanas (se crean una sola vez para toda la página)
    // ------------------------------------------------------------------
    function crearModales() {
        if ($('#modalCapturaRecorte').length) return;

        $('body').append(
            '<div class="modal fade captura-foto-modal" id="modalCapturaCamara" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">' +
              '<div class="modal-dialog modal-lg modal-dialog-centered" role="document">' +
                '<div class="modal-content">' +
                  '<div class="modal-header"><h5 class="modal-title"><i class="fa fa-camera"></i> Tomar foto del voucher</h5>' +
                    '<button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button></div>' +
                  '<div class="modal-body">' +
                    '<div class="form-group mb-2" id="capturaCamaraElegir">' +
                      '<label for="capturaCamaraLista" class="mb-1 small font-weight-bold">Cámara</label>' +
                      '<div class="input-group input-group-sm">' +
                        '<select class="form-control" id="capturaCamaraLista"><option value="">Buscando cámaras...</option></select>' +
                        '<div class="input-group-append">' +
                          '<button type="button" class="btn btn-outline-secondary" id="capturaCamaraBuscar" title="Volver a buscar cámaras (p. ej. después de conectar una por USB)">' +
                            '<i class="fa fa-sync-alt"></i> Buscar</button>' +
                        '</div>' +
                      '</div>' +
                      '<small class="text-muted" id="capturaCamaraCantidad"></small>' +
                    '</div>' +
                    '<div class="captura-foto-video"><video id="capturaCamaraVideo" autoplay playsinline muted></video></div>' +
                    '<small class="text-muted d-block mt-2">Ponga el voucher sobre una superficie lisa y con buena luz. Después podrá recortarlo.</small>' +
                  '</div>' +
                  '<div class="modal-footer">' +
                    '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>' +
                    '<button type="button" class="btn btn-primary" id="capturaCamaraTomar"><i class="fa fa-camera"></i> Tomar foto</button>' +
                  '</div>' +
                '</div>' +
              '</div>' +
            '</div>' +

            '<div class="modal fade captura-foto-modal" id="modalCapturaRecorte" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">' +
              '<div class="modal-dialog modal-lg modal-dialog-centered" role="document">' +
                '<div class="modal-content">' +
                  '<div class="modal-header"><h5 class="modal-title"><i class="fa fa-crop"></i> Recortar imagen</h5>' +
                    '<button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button></div>' +
                  '<div class="modal-body">' +
                    '<div class="captura-foto-lienzo"><img id="capturaRecorteImagen" alt="Imagen a recortar"></div>' +
                    '<div class="d-flex flex-wrap mt-2" style="gap:6px;">' +
                      '<button type="button" class="btn btn-sm btn-outline-secondary" data-captura-accion="girar-izq" title="Girar a la izquierda"><i class="fa fa-undo"></i> Girar</button>' +
                      '<button type="button" class="btn btn-sm btn-outline-secondary" data-captura-accion="girar-der" title="Girar a la derecha"><i class="fa fa-redo"></i> Girar</button>' +
                      '<button type="button" class="btn btn-sm btn-outline-secondary" data-captura-accion="todo" title="Seleccionar toda la imagen"><i class="fa fa-expand"></i> Toda la imagen</button>' +
                    '</div>' +
                    '<small class="text-muted d-block mt-2">Arrastre las esquinas del recuadro para dejar solo el voucher.</small>' +
                  '</div>' +
                  '<div class="modal-footer">' +
                    '<button type="button" class="btn btn-outline-secondary mr-auto" id="capturaRecorteSinCambios">Usar sin recortar</button>' +
                    '<button type="button" class="btn btn-primary" id="capturaRecorteUsar"><i class="fa fa-check"></i> Recortar y usar</button>' +
                  '</div>' +
                '</div>' +
              '</div>' +
            '</div>'
        );

        // Varias ventanas abiertas a la vez (esta sobre la del formulario): al cerrar la
        // de arriba, el body debe seguir con "modal-open" para que la de abajo haga scroll.
        $('.captura-foto-modal').on('hidden.bs.modal', function () {
            if ($('.modal.show').length) $('body').addClass('modal-open');
        });
        $('.captura-foto-modal').on('shown.bs.modal', function () {
            $('.modal-backdrop').last().addClass('captura-foto-backdrop');
        });

        $('#modalCapturaCamara').on('hidden.bs.modal', detenerCamara);
        $('#capturaCamaraLista').on('change', function () {
            if (!this.value) return;
            guardarCamaraElegida(this.value);
            iniciarCamara(this.value);
        });
        $('#capturaCamaraBuscar').on('click', function () {
            iniciarCamara($('#capturaCamaraLista').val() || null);
        });
        // Cámara conectada o desconectada con la ventana abierta: se actualiza la lista
        if (navigator.mediaDevices && navigator.mediaDevices.addEventListener) {
            navigator.mediaDevices.addEventListener('devicechange', function () {
                if (streamCamara) listarCamaras();
            });
        }
        $('#capturaCamaraTomar').on('click', tomarFoto);

        $('#modalCapturaRecorte').on('shown.bs.modal', iniciarRecorte);
        $('#modalCapturaRecorte').on('hidden.bs.modal', function () {
            if (cropper) { cropper.destroy(); cropper = null; }
        });
        $('[data-captura-accion]').on('click', function () {
            if (!cropper) return;
            const accion = $(this).data('captura-accion');
            if (accion === 'girar-izq') cropper.rotate(-90);
            if (accion === 'girar-der') cropper.rotate(90);
            if (accion === 'todo') cropper.setCropBoxData(cropper.getCanvasData());
        });
        $('#capturaRecorteUsar').on('click', function () { guardarResultado(true); });
        $('#capturaRecorteSinCambios').on('click', function () { guardarResultado(false); });
    }

    // ------------------------------------------------------------------
    // Botón "Cámara" junto a cada campo
    // ------------------------------------------------------------------
    function prepararInput(input) {
        if (input.dataset.capturaListo) return;
        input.dataset.capturaListo = '1';

        const $boton = $('<button type="button" class="btn btn-sm btn-outline-primary captura-foto-boton mt-1">' +
            '<i class="fa fa-camera"></i> Cámara</button>');
        // En un "custom-file" de Bootstrap el botón va debajo del recuadro, no dentro
        const $ancla = $(input).closest('.custom-file').length ? $(input).closest('.custom-file') : $(input);
        $ancla.after($boton);
        $boton.on('click', function () { abrirCamara(input); });
    }

    function prepararTodos(contexto) {
        $(contexto || document).find(SELECTOR).addBack(SELECTOR).each(function () { prepararInput(this); });
    }

    // ------------------------------------------------------------------
    // Cámara
    // ------------------------------------------------------------------
    function abrirCamara(input) {
        inputDestino = input;

        if (!camaraDisponible) {
            if (esCelular) {
                // Cámara del teléfono: funciona también sin HTTPS
                const $tmp = $('<input type="file" accept="image/*" capture="environment" style="display:none;">').appendTo('body');
                $tmp.on('change', function () {
                    if (this.files[0]) abrirRecorte(input, this.files[0]);
                    $tmp.remove();
                });
                $tmp.trigger('click');
                return;
            }
            avisar('Cámara no disponible',
                'El navegador solo permite usar la cámara si el sistema se abre con HTTPS (o en el mismo equipo del servidor). ' +
                'Mientras tanto, tome la foto o escanee el voucher y elíjalo con el botón de archivo.');
            return;
        }

        $('#modalCapturaCamara').modal('show');
        // Se abre con la última cámara elegida en este navegador (si sigue conectada)
        iniciarCamara(camaraGuardada());
    }

    function camaraGuardada() {
        try { return localStorage.getItem('capturaFotoCamara') || null; } catch (e) { return null; }
    }

    function guardarCamaraElegida(idDispositivo) {
        try { localStorage.setItem('capturaFotoCamara', idDispositivo); } catch (e) { /* sin almacenamiento */ }
    }

    function iniciarCamara(idDispositivo, esReintento) {
        detenerCamara();
        const video = idDispositivo
            ? { deviceId: { exact: idDispositivo }, width: { ideal: 1920 }, height: { ideal: 1080 } }
            : { facingMode: 'environment', width: { ideal: 1920 }, height: { ideal: 1080 } };

        navigator.mediaDevices.getUserMedia({ video: video, audio: false })
            .then(function (stream) {
                streamCamara = stream;
                document.getElementById('capturaCamaraVideo').srcObject = stream;
                return listarCamaras();
            })
            .catch(function (error) {
                const nombre = error && error.name;
                // La cámara guardada ya no está (desconectada): se prueba con la predeterminada
                if (idDispositivo && !esReintento && (nombre === 'OverconstrainedError' || nombre === 'NotFoundError')) {
                    iniciarCamara(null, true);
                    return;
                }
                $('#modalCapturaCamara').modal('hide');
                let texto = 'No se encontró una cámara conectada a este equipo.';
                if (nombre === 'NotAllowedError' || nombre === 'SecurityError') {
                    texto = 'El navegador no dio permiso para usar la cámara. Revise el ícono de la cámara junto a la dirección y permítalo.';
                } else if (nombre === 'NotReadableError' || nombre === 'AbortError') {
                    texto = 'La cámara está siendo usada por otro programa (Zoom, Teams, WhatsApp, etc.). Ciérrelo e intente de nuevo.';
                }
                avisar('No se pudo abrir la cámara', texto);
            });
    }

    // Llena el selector con todas las cámaras del equipo y marca la que está en uso
    function listarCamaras() {
        return navigator.mediaDevices.enumerateDevices().then(function (dispositivos) {
            const camaras = dispositivos.filter(function (d) { return d.kind === 'videoinput'; });
            const actual = streamCamara ? streamCamara.getVideoTracks()[0].getSettings().deviceId : '';
            $('#capturaCamaraLista').html(camaras.map(function (c, i) {
                return '<option value="' + c.deviceId + '"' + (c.deviceId === actual ? ' selected' : '') + '>' +
                    $('<div>').text(c.label || ('Cámara ' + (i + 1))).html() + '</option>';
            }).join(''));
            $('#capturaCamaraCantidad').text(camaras.length === 1
                ? 'Se encontró 1 cámara.'
                : 'Se encontraron ' + camaras.length + ' cámaras. Elija la que quiera usar.');
        });
    }

    function detenerCamara() {
        if (streamCamara) {
            streamCamara.getTracks().forEach(function (t) { t.stop(); });
            streamCamara = null;
        }
    }

    function tomarFoto() {
        const video = document.getElementById('capturaCamaraVideo');
        if (!video.videoWidth) return;
        const lienzo = document.createElement('canvas');
        lienzo.width = video.videoWidth;
        lienzo.height = video.videoHeight;
        lienzo.getContext('2d').drawImage(video, 0, 0);
        lienzo.toBlob(function (blob) {
            $('#modalCapturaCamara').one('hidden.bs.modal', function () {
                abrirRecorte(inputDestino, new File([blob], 'voucher-camara.jpg', { type: 'image/jpeg' }));
            }).modal('hide');
        }, 'image/jpeg', CALIDAD_JPEG);
    }

    // ------------------------------------------------------------------
    // Recorte
    // ------------------------------------------------------------------
    function abrirRecorte(input, archivo) {
        inputDestino = input;
        nombreOriginal = (archivo.name || 'voucher').replace(/\.[^.]+$/, '');
        const lector = new FileReader();
        lector.onload = function (e) {
            $('#capturaRecorteImagen').attr('src', e.target.result);
            $('#modalCapturaRecorte').modal('show');
        };
        lector.readAsDataURL(archivo);
    }

    function iniciarRecorte() {
        if (cropper) cropper.destroy();
        cropper = new Cropper(document.getElementById('capturaRecorteImagen'), {
            viewMode: 1,
            autoCropArea: 1,
            background: false,
            responsive: true,
        });
    }

    function guardarResultado(recortar) {
        if (!cropper || !inputDestino) return;
        const opciones = { maxWidth: LADO_MAXIMO, maxHeight: LADO_MAXIMO, imageSmoothingQuality: 'high', fillColor: '#fff' };
        if (!recortar) {
            // Sin recortar: toda la imagen, pero con el giro aplicado
            cropper.setCropBoxData(cropper.getCanvasData());
        }
        const lienzo = cropper.getCroppedCanvas(opciones);
        if (!lienzo) return;

        lienzo.toBlob(function (blob) {
            const archivo = new File([blob], nombreOriginal + '.jpg', { type: 'image/jpeg' });
            const transferencia = new DataTransfer();
            transferencia.items.add(archivo);
            inputDestino.files = transferencia.files;
            // La pantalla actualiza su vista previa con su propio "change"; esta marca evita
            // volver a abrir el recorte por ese mismo evento.
            inputDestino.dataset.capturaProcesado = '1';
            inputDestino.dispatchEvent(new Event('change', { bubbles: true }));
            $('#modalCapturaRecorte').modal('hide');
        }, 'image/jpeg', CALIDAD_JPEG);
    }

    function avisar(titulo, texto) {
        if (window.Swal && Swal.fire) {
            Swal.fire({ type: 'info', icon: 'info', title: titulo, text: texto });
        } else if (window.swal) {
            swal(titulo, texto, 'info');
        } else {
            alert(titulo + '\n\n' + texto);
        }
    }

    // ------------------------------------------------------------------
    // Arranque
    // ------------------------------------------------------------------
    $(function () {
        if (typeof window.Cropper === 'undefined') return;
        crearModales();
        prepararTodos(document);

        // Al elegir un archivo de imagen, se ofrece recortarlo (los PDF pasan tal cual)
        $(document).on('change', SELECTOR, function () {
            if (this.dataset.capturaProcesado) {
                delete this.dataset.capturaProcesado;
                return;
            }
            const archivo = this.files && this.files[0];
            if (archivo && /^image\//.test(archivo.type)) {
                abrirRecorte(this, archivo);
            }
        });

        // Campos que se crean después (contenido armado por JavaScript)
        new MutationObserver(function (cambios) {
            cambios.forEach(function (cambio) {
                cambio.addedNodes.forEach(function (nodo) {
                    if (nodo.nodeType === 1) prepararTodos(nodo);
                });
            });
        }).observe(document.body, { childList: true, subtree: true });
    });
})(jQuery);
