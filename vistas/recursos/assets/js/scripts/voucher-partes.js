/**
 * N° de voucher en dos partes: largo y corto (ej. 1469337 - 002).
 *
 * Se activa en cualquier <input data-voucher-partes>, también en los que se crean
 * después por JavaScript (p. ej. la pestaña Clínicas). El input original se vuelve
 * oculto y sigue siendo el que se envía y el que leen las pantallas ($('#id').val()):
 * aquí se le pone el valor ya unido, "1469337-002", o solo "1469337" si la parte corta
 * está vacía (es opcional). El guion lo pone el sistema: si el usuario lo escribe o
 * pega el número completo, se reparte solo entre los dos campos.
 *
 * Cuando una pantalla le pone valor al original con jQuery (.val('...') al editar o
 * .val('') al limpiar), los dos campos se actualizan solos (valHooks).
 */
(function () {
    'use strict';

    const SELECTOR = 'input[data-voucher-partes]';

    // "1469337-002" → ['1469337', '002']; sin guion todo es la parte larga
    function partesDe(valor) {
        const texto = String(valor == null ? '' : valor).trim();
        const i = texto.indexOf('-');
        return i < 0 ? [texto, ''] : [texto.slice(0, i).trim(), texto.slice(i + 1).trim()];
    }

    // Sin parte larga no hay voucher (cada pantalla avisa que falta el N°)
    function unir(largo, corto) {
        largo = largo.trim();
        corto = corto.replace(/^-+/, '').trim();
        if (!largo) return '';
        return corto ? largo + '-' + corto : largo;
    }

    // El N° de voucher solo lleva números (el guion lo pone el sistema): se quita lo
    // demás y se avisa un momento debajo del campo
    function soloDigitos(input, valor) {
        const limpio = String(valor).replace(/\D/g, '');
        if (limpio !== String(valor)) avisarSoloNumeros(input);
        return limpio;
    }
    function avisarSoloNumeros(input) {
        const grupo = input.closest('.voucher-partes');
        if (!grupo) return;
        let aviso = grupo.querySelector('.vp-aviso');
        if (!aviso) {
            aviso = document.createElement('small');
            aviso.className = 'vp-aviso';
            aviso.textContent = 'Solo se aceptan números';
            grupo.appendChild(aviso);
        }
        aviso.style.display = 'block';
        clearTimeout(aviso._temporizador);
        aviso._temporizador = setTimeout(function () { aviso.style.display = 'none'; }, 2500);
    }

    function campos(original) {
        return {
            largo: document.getElementById(original.id + '_largo'),
            corto: document.getElementById(original.id + '_corto'),
            vista: document.getElementById(original.id + '_vista')
        };
    }

    function pintarVista(original) {
        const c = campos(original);
        if (!c.vista) return;
        c.vista.textContent = original.value;
        c.vista.parentNode.style.display = original.value ? '' : 'none';
    }

    // Del original (valor unido) a los dos campos
    function repartir(original) {
        const c = campos(original);
        if (!c.largo) return;
        const p = partesDe(original.value);
        c.largo.value = p[0];
        c.corto.value = p[1];
        pintarVista(original);
    }

    // De los dos campos al original; avisa a la pantalla como si se hubiera escrito en él
    function sincronizar(original) {
        const c = campos(original);
        const nuevo = unir(c.largo.value, c.corto.value);
        if (original.value === nuevo) {
            pintarVista(original);
            return;
        }
        original.value = nuevo;
        pintarVista(original);
        original.dispatchEvent(new Event('input', { bubbles: true }));
        original.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function preparar(original) {
        if (original.dataset.vpListo || !original.id) return;
        original.dataset.vpListo = '1';

        // Mismo estilo que el campo original (tamaño, clases propias de cada pantalla)
        const clases = original.className.replace(/\bd-none\b/g, '').trim();
        const requerido = original.required;

        const grupo = document.createElement('div');
        grupo.className = 'voucher-partes';
        grupo.innerHTML =
            '<div class="input-group">' +
                '<input type="text" class="' + clases + ' vp-largo" id="' + original.id + '_largo" ' +
                    'inputmode="numeric" pattern="[0-9]*" autocomplete="off" placeholder="Ej: 1469337" aria-label="N° de voucher">' +
                '<div class="input-group-append input-group-prepend"><span class="input-group-text vp-guion">-</span></div>' +
                '<input type="text" class="' + clases + ' vp-corto" id="' + original.id + '_corto" ' +
                    'inputmode="numeric" pattern="[0-9]*" autocomplete="off" placeholder="002" aria-label="N° corto del voucher (opcional)" ' +
                    'title="Parte corta del voucher (opcional)">' +
            '</div>' +
            '<small class="vp-se-guarda" style="display:none;">Se guarda como: <strong id="' + original.id + '_vista"></strong></small>';

        original.type = 'hidden';
        original.required = false;
        original.parentNode.insertBefore(grupo, original.nextSibling);

        const c = campos(original);
        c.largo.required = requerido;
        if (original.maxLength > 0) {
            c.largo.maxLength = original.maxLength;
            c.corto.maxLength = original.maxLength;
        }
        // La etiqueta del campo apunta ahora a la parte larga
        document.querySelectorAll('label[for="' + original.id + '"]').forEach(function (label) {
            label.htmlFor = c.largo.id;
        });

        c.largo.addEventListener('input', function () {
            // Si escribe o pega el número completo, el guion separa las dos partes
            if (this.value.indexOf('-') >= 0) {
                const p = partesDe(this.value);
                this.value = p[0];
                if (p[1]) c.corto.value = soloDigitos(c.corto, p[1]);
                c.corto.focus();
            }
            this.value = soloDigitos(this, this.value);
            sincronizar(original);
        });
        c.largo.addEventListener('keydown', function (e) {
            if (e.key === '-' || e.key === 'Subtract') {
                e.preventDefault();
                c.corto.focus();
                c.corto.select();
            }
        });
        c.corto.addEventListener('input', function () {
            this.value = soloDigitos(this, this.value);
            sincronizar(original);
        });
        c.corto.addEventListener('keydown', function (e) {
            // Borrar con el campo corto vacío vuelve a la parte larga
            if (e.key === 'Backspace' && !this.value) {
                e.preventDefault();
                c.largo.focus();
            }
        });

        repartir(original);
    }

    function prepararTodos(raiz) {
        instalarHook(window.jQuery);
        if (raiz.matches && raiz.matches(SELECTOR)) preparar(raiz);
        if (raiz.querySelectorAll) raiz.querySelectorAll(SELECTOR).forEach(preparar);
    }

    // .val('...') de jQuery sobre el original (ya oculto) actualiza los dos campos. La
    // página puede cargar más de una copia de jQuery: se instala en cada una.
    const conHook = [];
    function instalarHook(jq) {
        if (!jq || !jq.valHooks || conHook.indexOf(jq) >= 0) return;
        conHook.push(jq);
        const previo = jq.valHooks.hidden || {};
        const hook = {
            set: function (el, valor) {
                if (el.dataset && el.dataset.vpListo) {
                    el.value = valor == null ? '' : String(valor);
                    repartir(el);
                    return true;
                }
                return previo.set ? previo.set(el, valor) : undefined;
            }
        };
        if (previo.get) hook.get = previo.get;
        jq.valHooks.hidden = hook;
    }

    // "Limpiar"/reset de un formulario: el oculto no se limpia solo
    document.addEventListener('reset', function (e) {
        const form = e.target;
        setTimeout(function () {
            form.querySelectorAll(SELECTOR).forEach(function (original) {
                original.value = '';
                repartir(original);
            });
        }, 0);
    }, true);

    // Valor que se guardará (para los resúmenes que se muestran antes de guardar)
    window.VoucherPartes = { unir: unir, partes: partesDe };

    instalarHook(window.jQuery);
    function iniciar() {
        prepararTodos(document);
        new MutationObserver(function (cambios) {
            cambios.forEach(function (cambio) {
                cambio.addedNodes.forEach(function (nodo) {
                    if (nodo.nodeType === 1) prepararTodos(nodo);
                });
            });
        }).observe(document.body, { childList: true, subtree: true });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }
    // Copias de jQuery cargadas al final de algunas vistas
    window.addEventListener('load', function () { instalarHook(window.jQuery); });
})();
