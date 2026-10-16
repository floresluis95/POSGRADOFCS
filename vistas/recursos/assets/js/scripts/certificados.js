/**
 * Piezas comunes de las pantallas de certificados (Calificaciones, Historial
 * Académico, Aprobación, Medio Magnético y Revisión).
 *
 *  Certificado.buscador({...})   Buscador por C.I. o nombre: muestra los estudiantes
 *                                encontrados en un panel para elegir uno. Al elegir, deja
 *                                esa inscripción como única opción del <select> oculto de
 *                                la pantalla (#select-inscripcion) y dispara su "change",
 *                                así cada pantalla sigue leyendo sus datos como antes.
 *                                Si la pantalla tiene #input-monto, lo llena con el precio
 *                                del certificado para el grado académico del programa.
 *
 *  Certificado.confirmar({...})  Antes de generar: ventana con los datos que se van a
 *                                registrar, revisión ortográfica de los textos y la vista
 *                                previa del documento (el mismo PDF con marca de agua, sin
 *                                registrarlo). "Generar e imprimir" envía el formulario.
 *
 *  Certificado.textoGrado(g)     "MAESTRIA" → "MAESTRÍA" (la BD guarda el grado sin tilde)
 *  Certificado.cursoRealizado(grado, programa)  "ESPECIALIDAD EN ..." sin repetir el grado
 *
 * Las pantallas cargan su propia copia de jQuery antes que la plantilla; por eso aquí
 * jQuery se toma recién al llamar (window.jQuery es entonces la copia con Bootstrap).
 */
(function () {
    'use strict';

    // Términos de odontología que el corrector no conoce: no se marcan como error
    const TERMINOS_CORRECTOS = ['OROFACIAL', 'MAXILOFACIAL', 'ENDODONCIA', 'PERIODONCIA', 'ORTODONCIA', 'IMPLANTOLOGÍA',
        'ODONTOPEDIATRÍA', 'PERIIMPLANTARIA', 'ODONTOLÓGICA', 'ODONTOLÓGICO', 'BUCOMAXILOFACIAL', 'ESTOMATOLOGÍA'];

    const NOMBRES_GRADO = { DIPLOMADO: 'Diplomado', ESPECIALIDAD: 'Especialidad', MAESTRIA: 'Maestría', DOCTORADO: 'Doctorado' };

    function claveGrado(grado) {
        return String(grado || '').toLocaleUpperCase('es').trim()
            .replace(/Á/g, 'A').replace(/É/g, 'E').replace(/Í/g, 'I').replace(/Ó/g, 'O').replace(/Ú/g, 'U');
    }

    function textoGrado(grado) {
        const clave = claveGrado(grado);
        return NOMBRES_GRADO[clave] ? NOMBRES_GRADO[clave].toLocaleUpperCase('es') : String(grado || '').toLocaleUpperCase('es').trim();
    }

    // "Ha realizado el curso de:" ESPECIALIDAD EN ARMONIZACIÓN OROFACIAL. Si el nombre del
    // programa ya trae el grado (ESPECIALIDAD DE ...), no se repite.
    function cursoRealizado(grado, programa) {
        const g = textoGrado(grado);
        let p = String(programa || '').toLocaleUpperCase('es').trim();
        const sinTilde = claveGrado(p);
        const gClave = claveGrado(g);
        if (g && sinTilde.indexOf(gClave + ' ') === 0) {
            p = p.slice(gClave.length).trim().replace(/^(DE|EN)\s+/, '');
        }
        return g ? g + ' EN ' + p : p;
    }

    function escapar(texto) {
        const div = document.createElement('div');
        div.textContent = texto == null ? '' : String(texto);
        return div.innerHTML;
    }

    function formatoBs(monto) {
        return 'Bs. ' + (parseFloat(monto) || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function aviso(tipo, titulo, texto) {
        if (window.Swal && window.Swal.fire) window.Swal.fire({ type: tipo, title: titulo, text: texto });
        else alert(titulo + (texto ? '\n' + texto : ''));
    }

    function iniciales(insc) {
        return ((insc.Apaterno || '').charAt(0) + (insc.Nombre || '').charAt(0)).toUpperCase();
    }

    function ciCompleto(insc) {
        return insc.Ci + (insc.Complemento ? '-' + insc.Complemento : '') + (insc.Exp ? ' ' + insc.Exp : '');
    }

    function anioPrograma(insc) {
        const partes = String(insc.Codigo || '').split('-');
        return partes[partes.length - 1].trim();
    }

    // ------------------------------------------------------------------
    // Buscador
    // ------------------------------------------------------------------
    function buscador(op) {
        const $ = window.jQuery;
        const $cont = $(op.contenedor);
        const $select = $(op.select || '#select-inscripcion');
        const precios = op.precios || {};
        let temporizador = null;
        let peticion = null;
        let resultados = [];

        $cont.addClass('cert-buscador').html(
            '<div class="cert-buscar">' +
                '<i class="fas fa-search"></i>' +
                '<input type="text" class="form-control" id="cert-termino" autocomplete="off" ' +
                    'placeholder="C.I. o nombre del estudiante" aria-label="Buscar estudiante por C.I. o nombre">' +
                '<button type="button" class="btn" id="cert-btn-buscar">Buscar</button>' +
            '</div>' +
            '<div class="cert-estado" id="cert-estado"></div>' +
            '<div class="cert-resultados" id="cert-resultados"></div>' +
            '<div class="cert-elegido" id="cert-elegido" style="display:none;"></div>'
        );

        function estado(html) {
            $('#cert-estado').html(html || '').toggle(!!html);
        }

        function tarjeta(insc, i) {
            const nombre = [insc.Apaterno, insc.Amaterno, insc.Nombre].filter(Boolean).join(' ');
            return '<button type="button" class="cert-resultado" data-i="' + i + '">' +
                '<span class="cert-avatar">' + escapar(iniciales(insc)) + '</span>' +
                '<span class="cert-resultado-datos">' +
                    '<strong>' + escapar(nombre) + '</strong>' +
                    '<small>C.I. ' + escapar(ciCompleto(insc)) + '</small>' +
                    '<span class="cert-programa">' + escapar(insc.NombrePrograma) + '</span>' +
                '</span>' +
                '<span class="cert-resultado-lado">' +
                    '<span class="cert-grado">' + escapar(insc.GradoLegible || textoGrado(insc.GradoAcademico)) + '</span>' +
                    '<small>' + escapar([insc.Sede, anioPrograma(insc)].filter(Boolean).join(' · ')) + '</small>' +
                '</span>' +
            '</button>';
        }

        function mostrarResultados(lista) {
            resultados = lista;
            $('#cert-elegido').hide();
            if (!lista.length) {
                $('#cert-resultados').empty();
                estado('<i class="fas fa-info-circle"></i> No se encontró ningún estudiante activo.');
                return;
            }
            estado(lista.length === 1 ? '' : '<span>' + lista.length + ' resultados · elija uno</span>');
            $('#cert-resultados').html(lista.map(tarjeta).join('')).show();
            if (lista.length === 1) elegir(0);
        }

        function buscar() {
            const termino = $('#cert-termino').val().trim();
            clearTimeout(temporizador);
            if (peticion) peticion.abort();
            if (!termino) {
                limpiar();
                return;
            }
            if (!/^\d+/.test(termino) && termino.length < 3) {
                estado('<span>Escriba al menos 3 letras del nombre.</span>');
                return;
            }
            estado('<i class="fas fa-spinner fa-spin"></i> Buscando...');
            peticion = $.ajax({
                url: 'ajax/certificado.ajax.php',
                method: 'POST',
                dataType: 'json',
                data: { accion: 'buscarEstudiantes', termino: termino },
                success: function (resp) {
                    if (!resp || !resp.success) {
                        estado('<span>' + escapar((resp && resp.mensaje) || 'No se pudo buscar') + '</span>');
                        return;
                    }
                    mostrarResultados(resp.resultados || []);
                },
                error: function (xhr, est) {
                    if (est !== 'abort') estado('<span class="text-danger">No se pudo realizar la búsqueda.</span>');
                }
            });
        }

        function limpiar() {
            resultados = [];
            estado('');
            $('#cert-resultados').empty();
            $('#cert-elegido').hide().empty();
            $select.empty().append('<option value="">-- Seleccione --</option>').val('').trigger('change');
            if (op.alLimpiar) op.alLimpiar();
        }

        function elegir(i) {
            const insc = resultados[i];
            if (!insc) return;
            const nombre = [insc.Apaterno, insc.Amaterno, insc.Nombre].filter(Boolean).join(' ');
            $('#cert-resultados').hide();
            estado('');
            $('#cert-elegido').html(
                '<span class="cert-avatar">' + escapar(iniciales(insc)) + '</span>' +
                '<span class="cert-resultado-datos">' +
                    '<strong>' + escapar(nombre) + '</strong>' +
                    '<small>C.I. ' + escapar(ciCompleto(insc)) + ' · ' + escapar(insc.GradoLegible || textoGrado(insc.GradoAcademico)) + '</small>' +
                    '<span class="cert-programa">' + escapar(insc.NombrePrograma) + '</span>' +
                '</span>' +
                '<button type="button" class="btn btn-sm cert-cambiar" title="Elegir otro estudiante"><i class="fas fa-exchange-alt"></i> Cambiar</button>'
            ).show();

            const $opcion = op.opcion(insc);
            $opcion.attr('data-grado-clave', claveGrado(insc.GradoAcademico));
            $select.empty().append($opcion).val($opcion.val()).trigger('change');
            ponerPrecio(insc);
        }

        // Monto: el precio del certificado para el grado del programa (se puede cambiar)
        function ponerPrecio(insc) {
            const $monto = $('#input-monto');
            if (!$monto.length || !op.tipo) return;
            const grado = claveGrado(insc.GradoAcademico);
            const precio = precios[op.tipo] ? precios[op.tipo][grado] : null;
            let $ayuda = $('#ayuda-monto');
            if (!$ayuda.length) {
                $ayuda = $('<small class="cert-ayuda" id="ayuda-monto"></small>');
                ($monto.closest('.input-group').length ? $monto.closest('.input-group') : $monto).after($ayuda);
            }
            const nombreGrado = NOMBRES_GRADO[grado] || insc.GradoAcademico;
            if (precio !== null && precio !== undefined) {
                $monto.val(Number(precio).toFixed(2));
                $ayuda.text('Precio para ' + nombreGrado + ': ' + formatoBs(precio) + '. Puede cambiarlo.');
            } else {
                $monto.val('');
                $ayuda.text('Sin precio definido para ' + nombreGrado + '.');
            }
        }

        $cont.on('input', '#cert-termino', function () {
            clearTimeout(temporizador);
            temporizador = setTimeout(buscar, 450);
        });
        $cont.on('keydown', '#cert-termino', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscar();
            }
        });
        $cont.on('click', '#cert-btn-buscar', buscar);
        $cont.on('click', '.cert-resultado', function () { elegir(parseInt(this.getAttribute('data-i'), 10)); });
        $cont.on('click', '.cert-cambiar', function () {
            if (resultados.length > 1) {
                $('#cert-elegido').hide();
                $('#cert-resultados').show();
                estado('<span>' + resultados.length + ' resultados · elija uno</span>');
            } else {
                $('#cert-termino').val('').trigger('focus');
                limpiar();
            }
        });

        return { limpiar: limpiar };
    }

    // ------------------------------------------------------------------
    // Revisión ortográfica (LanguageTool, el mismo servicio del título del trabajo)
    // ------------------------------------------------------------------
    function revisarOrtografia(textos, $destino) {
        const $ = window.jQuery;
        const lista = (textos || []).filter(function (t) { return t && String(t.texto || '').trim(); });
        if (!lista.length) {
            $destino.hide();
            return;
        }
        $destino.show().attr('class', 'cert-ortografia is-info').html('<i class="fas fa-spinner fa-spin"></i> Revisando ortografía...');
        const unido = lista.map(function (t) { return t.texto; }).join('\n');
        $.ajax({
            url: 'https://api.languagetool.org/v2/check',
            method: 'POST',
            dataType: 'json',
            timeout: 15000,
            data: { text: unido, language: 'es' },
            success: function (resp) {
                const dudas = [];
                (resp.matches || []).forEach(function (m) {
                    const palabra = unido.substr(m.offset, m.length);
                    if (TERMINOS_CORRECTOS.indexOf(palabra.toLocaleUpperCase('es')) >= 0) return;
                    const sugerencias = (m.replacements || []).map(function (r) { return r.value.toLocaleUpperCase('es'); })
                        .filter(function (s, i, arr) { return s !== palabra.toLocaleUpperCase('es') && arr.indexOf(s) === i; }).slice(0, 3);
                    // En MAYÚSCULAS, una "corrección" que solo cambia mayúsculas no es error
                    if (m.rule && m.rule.issueType === 'misspelling' && !sugerencias.length) return;
                    if (m.rule && /UPPERCASE|MAYUSCULA|CASING/i.test(m.rule.id || '')) return;
                    dudas.push({ palabra: palabra, sugerencias: sugerencias });
                });
                if (!dudas.length) {
                    $destino.attr('class', 'cert-ortografia is-ok').html('<i class="fas fa-check-circle"></i> Sin errores de ortografía.');
                    return;
                }
                $destino.attr('class', 'cert-ortografia is-duda').html(
                    '<i class="fas fa-exclamation-triangle"></i> Revise: ' + dudas.map(function (d) {
                        return '<strong>' + escapar(d.palabra) + '</strong>' +
                            (d.sugerencias.length ? ' (¿' + escapar(d.sugerencias.join(', ')) + '?)' : '');
                    }).join(' · ')
                );
            },
            error: function () {
                $destino.attr('class', 'cert-ortografia is-info').html('<i class="fas fa-wifi"></i> No se pudo revisar la ortografía (sin internet). Revise los datos a mano.');
            }
        });
    }

    // ------------------------------------------------------------------
    // Confirmación con vista previa
    // ------------------------------------------------------------------
    function crearModal() {
        const $ = window.jQuery;
        if ($('#modalConfirmarCertificado').length) return;
        $('body').append(
            '<div class="modal fade cert-modal" id="modalConfirmarCertificado" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">' +
              '<div class="modal-dialog modal-xl modal-dialog-centered" role="document">' +
                '<div class="modal-content">' +
                  '<div class="modal-header">' +
                    '<div><small>Revise antes de generar</small><h5 class="modal-title" id="certModalTitulo"></h5></div>' +
                    '<button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>' +
                  '</div>' +
                  '<div class="modal-body">' +
                    '<div class="row">' +
                      '<div class="col-lg-5">' +
                        '<div class="cert-seccion">Datos que se registran</div>' +
                        '<table class="cert-resumen" id="certModalDatos"></table>' +
                        '<div id="certModalExtra"></div>' +
                        '<div class="cert-ortografia" id="certModalOrtografia" style="display:none;"></div>' +
                      '</div>' +
                      '<div class="col-lg-7">' +
                        '<div class="cert-seccion">Vista previa de la impresión</div>' +
                        '<div class="cert-previa">' +
                          '<div class="cert-previa-cargando" id="certPreviaCargando"><i class="fas fa-spinner fa-spin"></i> Preparando vista previa...</div>' +
                          '<iframe name="certVistaPrevia" id="certVistaPrevia" title="Vista previa del documento"></iframe>' +
                        '</div>' +
                      '</div>' +
                    '</div>' +
                  '</div>' +
                  '<div class="modal-footer">' +
                    '<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Corregir datos</button>' +
                    '<button type="button" class="btn btn-generar-certificado" id="certModalGenerar"><i class="fas fa-print"></i> Generar e imprimir</button>' +
                  '</div>' +
                '</div>' +
              '</div>' +
            '</div>'
        );
        $('#certVistaPrevia').on('load', function () { $('#certPreviaCargando').hide(); });
    }

    /**
     * @param {Object} op
     *   titulo: 'Certificado de Calificaciones'
     *   form: '#form-generar-certificado'
     *   datos: [['Estudiante', 'JUAN PÉREZ'], ['Monto', 'Bs. 100,00'], ...]
     *   extra: html opcional bajo los datos (p. ej. la lista de módulos)
     *   ortografia: [{ texto: '...' }, ...] textos a revisar
     */
    function confirmar(op) {
        const $ = window.jQuery;
        crearModal();
        const $form = $(op.form);
        $('#certModalTitulo').text(op.titulo || 'Documento');
        $('#certModalDatos').html((op.datos || []).map(function (fila) {
            const vacio = fila[1] === null || fila[1] === undefined || String(fila[1]).trim() === '';
            return '<tr><th>' + escapar(fila[0]) + '</th><td' + (vacio ? ' class="is-vacio"' : '') + '>' +
                (vacio ? 'Sin dato' : escapar(fila[1])) + '</td></tr>';
        }).join(''));
        $('#certModalExtra').html(op.extra || '');
        revisarOrtografia(op.ortografia, $('#certModalOrtografia'));

        // Vista previa: el mismo formulario con vistaPrevia=1 dentro del iframe. Si el
        // navegador no avisa que cargó (algunos no lo hacen con un PDF), el aviso se quita igual.
        $('#certPreviaCargando').show();
        clearTimeout(confirmar.temporizador);
        confirmar.temporizador = setTimeout(function () { $('#certPreviaCargando').hide(); }, 5000);
        const destinoOriginal = $form.attr('target') || '_blank';
        $form.find('input[name="vistaPrevia"]').remove();
        $form.append('<input type="hidden" name="vistaPrevia" value="1">').attr('target', 'certVistaPrevia');
        $form[0].submit();
        $form.find('input[name="vistaPrevia"]').remove();
        $form.attr('target', destinoOriginal);

        $('#certModalGenerar').off('click').on('click', function () {
            $('#modalConfirmarCertificado').modal('hide');
            $form.attr('target', destinoOriginal);
            $form[0].submit();
        });
        $('#modalConfirmarCertificado').modal('show');
    }

    window.Certificado = {
        buscador: buscador,
        confirmar: confirmar,
        textoGrado: textoGrado,
        cursoRealizado: cursoRealizado,
        formatoBs: formatoBs,
        aviso: aviso,
        anioPrograma: anioPrograma
    };
})();
