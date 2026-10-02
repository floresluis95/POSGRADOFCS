/**
 * Revisión de textos que se imprimen en documentos oficiales (nombres de
 * módulos, títulos...): los pasa a MAYÚSCULAS mientras se escriben y revisa su
 * ortografía con LanguageTool (servicio gratuito en línea), marcando las
 * palabras dudosas y ofreciendo correcciones con un clic.
 *
 * Uso (requiere jQuery):
 *   RevisionOrtografica.activar('.nombre-modulo-input');
 * Funciona también con campos que se agregan después (delegación de eventos).
 * Los errores pendientes de un campo quedan en $(campo).data('erroresOrtografia').
 *
 * Solo avisa, nunca bloquea: sin internet muestra un aviso y se sigue trabajando.
 */
(function (window, $) {
    'use strict';

    var URL_LANGUAGETOOL = 'https://api.languagetool.org/v2/check';
    var ESPERA_MS = 900;

    var estilos =
        '.rev-orto { margin-top: 6px; padding: 8px 10px; border: 1px solid #e3e7ef; border-radius: 8px; background: #fafbfd; font-size: 12.5px; }' +
        '.rev-orto-estado { font-weight: 600; }' +
        '.rev-orto-estado.ok { color: #2f855a; }' +
        '.rev-orto-estado.error { color: #b45309; }' +
        '.rev-orto-estado.info { color: #6b7280; font-weight: 500; }' +
        '.rev-orto-vista { margin-top: 4px; font-weight: 600; line-height: 1.6; color: #1f2937; }' +
        '.rev-orto-vista mark { background: #fde68a; color: #92400e; padding: 0 2px; border-radius: 3px; border-bottom: 2px solid #d97706; }' +
        '.rev-orto-lista { margin: 4px 0 0; padding-left: 18px; }' +
        '.rev-orto-lista li { margin: 2px 0; }' +
        '.rev-orto-sugerencia { display: inline-block; margin: 0 4px 2px 0; padding: 0 8px; border: 1px solid #1e3c72; border-radius: 12px; color: #1e3c72; background: #fff; cursor: pointer; font-size: 11.5px; font-weight: 600; }' +
        '.rev-orto-sugerencia:hover { background: #1e3c72; color: #fff; }';

    function escaparHtml(texto) {
        return $('<div>').text(texto == null ? '' : String(texto)).html();
    }

    // Panel de resultados debajo del campo (se crea la primera vez)
    function panelDe($campo) {
        var $panel = $campo.data('panelOrtografia');
        if (!$panel) {
            $panel = $('<div class="rev-orto" style="display:none;">' +
                '<div class="rev-orto-estado"></div><div class="rev-orto-vista"></div><ul class="rev-orto-lista"></ul></div>');
            // Después del texto de ayuda del campo, si lo tiene
            var $ayuda = $campo.nextAll('small').first();
            ($ayuda.length ? $ayuda : $campo).after($panel);
            $panel.data('campo', $campo);
            $campo.data('panelOrtografia', $panel);
        }
        return $panel;
    }

    function mostrar($campo, texto, errores) {
        var $panel = panelDe($campo);
        $campo.data('erroresOrtografia', errores);

        if (!errores.length) {
            $panel.find('.rev-orto-estado').attr('class', 'rev-orto-estado ok')
                .html('<i class="fa fa-check-circle"></i> Sin errores de ortografía.');
            $panel.find('.rev-orto-vista, .rev-orto-lista').empty();
            return;
        }

        $panel.find('.rev-orto-estado').attr('class', 'rev-orto-estado error').html(
            '<i class="fa fa-exclamation-triangle"></i> ' + errores.length +
            (errores.length === 1 ? ' posible error' : ' posibles errores') + ' de ortografía:');

        var html = '', pos = 0;
        errores.forEach(function (e) {
            html += escaparHtml(texto.substring(pos, e.inicio)) + '<mark>' + escaparHtml(e.palabra) + '</mark>';
            pos = e.inicio + e.largo;
        });
        $panel.find('.rev-orto-vista').html(html + escaparHtml(texto.substring(pos)));

        var lista = '';
        errores.forEach(function (e, i) {
            lista += '<li><strong>' + escaparHtml(e.palabra) + '</strong>' +
                (e.sugerencias.length
                    ? ' &rarr; ' + e.sugerencias.map(function (s) {
                        return '<button type="button" class="rev-orto-sugerencia" data-i="' + i + '" data-valor="' + escaparHtml(s) + '">' + escaparHtml(s) + '</button>';
                    }).join('')
                    : ' <small class="text-muted">' + escaparHtml(e.mensaje) + '</small>') +
                '</li>';
        });
        $panel.find('.rev-orto-lista').html(lista);
    }

    function revisar($campo) {
        var texto = $campo.val();
        var anterior = $campo.data('peticionOrtografia');
        if (anterior) anterior.abort();
        $campo.data('erroresOrtografia', []);

        var $panel = panelDe($campo);
        if (!texto.trim()) {
            $panel.hide();
            return;
        }
        $panel.show().find('.rev-orto-estado').attr('class', 'rev-orto-estado info')
            .html('<i class="fa fa-spinner fa-spin"></i> Revisando ortografía...');

        $campo.data('peticionOrtografia', $.ajax({
            url: URL_LANGUAGETOOL,
            method: 'POST',
            dataType: 'json',
            timeout: 15000,
            data: { text: texto, language: 'es' },
            success: function (resp) {
                if ($campo.val() !== texto) return; // se siguió escribiendo
                var errores = [];
                (resp.matches || []).forEach(function (m) {
                    var palabra = texto.substr(m.offset, m.length);
                    var sugerencias = (m.replacements || []).map(function (r) {
                        return r.value.toLocaleUpperCase('es');
                    }).filter(function (s, i, l) {
                        return s !== palabra && l.indexOf(s) === i;
                    }).slice(0, 4);
                    // "Corrección" que solo cambia mayúsculas (nombres propios): no es error en un texto en mayúsculas
                    if (m.rule && m.rule.issueType === 'misspelling' && !sugerencias.length) return;
                    errores.push({ palabra: palabra, inicio: m.offset, largo: m.length, mensaje: m.message, sugerencias: sugerencias });
                });
                mostrar($campo, texto, errores);
            },
            error: function (xhr, estado) {
                if (estado === 'abort') return;
                $panel.find('.rev-orto-estado').attr('class', 'rev-orto-estado info')
                    .html('<i class="fa fa-wifi"></i> No se pudo revisar la ortografía (sin conexión a internet). Revíselo a mano.');
                $panel.find('.rev-orto-vista, .rev-orto-lista').empty();
            }
        }));
    }

    var RevisionOrtografica = {
        activar: function (selector) {
            if (!$('#rev-orto-estilos').length) {
                $('<style id="rev-orto-estilos">' + estilos + '</style>').appendTo('head');
            }

            // MAYÚSCULAS mientras se escribe (sin mover el cursor) y revisión con pausa
            $(document).on('input', selector, function () {
                var inicio = this.selectionStart, fin = this.selectionEnd;
                var mayus = this.value.toLocaleUpperCase('es');
                if (mayus !== this.value) {
                    this.value = mayus;
                    if (inicio !== null) this.setSelectionRange(inicio, fin);
                }
                var $campo = $(this);
                clearTimeout($campo.data('temporizadorOrtografia'));
                $campo.data('temporizadorOrtografia', setTimeout(function () { revisar($campo); }, ESPERA_MS));
            });

            // Aplicar una sugerencia
            $(document).on('click', '.rev-orto-sugerencia', function () {
                var $campo = $(this).closest('.rev-orto').data('campo');
                if (!$campo) return;
                var e = ($campo.data('erroresOrtografia') || [])[$(this).data('i')];
                var texto = $campo.val();
                if (!e || texto.substr(e.inicio, e.largo) !== e.palabra) return;
                $campo.val(texto.substring(0, e.inicio) + $(this).data('valor') + texto.substring(e.inicio + e.largo)).trigger('input');
                clearTimeout($campo.data('temporizadorOrtografia'));
                revisar($campo);
            });
        },

        // Revisar ya (p. ej. al cargar un valor guardado en un formulario de edición)
        revisarAhora: function (campo) {
            revisar($(campo));
        },

        // Palabras marcadas en los campos indicados, para avisar antes de guardar
        erroresPendientes: function (campos) {
            var palabras = [];
            $(campos).each(function () {
                ($(this).data('erroresOrtografia') || []).forEach(function (e) { palabras.push(e.palabra); });
            });
            return palabras;
        }
    };

    window.RevisionOrtografica = RevisionOrtografica;
})(window, jQuery);
