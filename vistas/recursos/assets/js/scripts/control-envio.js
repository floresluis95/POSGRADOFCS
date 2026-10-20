/**
 * Control de envíos cuando se corta internet.
 *
 * Se carga en el <head> de la plantilla, antes que cualquier vista.
 *
 * 1) Cada POST a ajax/ (jQuery/XMLHttpRequest o fetch) lleva la cabecera X-Solicitud-Id. Si la respuesta
 *    no llega (corte, tiempo agotado), el identificador se guarda: al volver a enviar los
 *    MISMOS datos se repite el mismo, y el servidor (ajax/control-solicitud.php) devuelve
 *    la respuesta del primer envío en vez de registrar todo otra vez. Si el usuario cambia
 *    algún dato, es un envío nuevo. Con respuesta del servidor (bien o mal) se olvida.
 * 2) Sin respuesta por corte o tiempo agotado: aviso claro de qué hacer (no se guardan
 *    datos a medias: el servidor guarda todo o nada).
 * 3) Franja roja arriba mientras el equipo no tiene internet.
 * 4) Formularios normales (POST sin ajax): el botón se desactiva al enviar para evitar
 *    el doble clic.
 */
(function () {
    'use strict';

    const CLAVE_ALMACEN = 'controlEnvioPendientes';
    const TIEMPO_MAXIMO = 90000; // ms: un envío sin respuesta en este tiempo se da por cortado

    // ---------- Identificadores pendientes (sobreviven a recargar la página) ----------
    let pendientes = {};
    try { pendientes = JSON.parse(sessionStorage.getItem(CLAVE_ALMACEN) || '{}') || {}; } catch (e) { pendientes = {}; }

    function guardarPendientes() {
        try { sessionStorage.setItem(CLAVE_ALMACEN, JSON.stringify(pendientes)); } catch (e) { /* sin almacenamiento: solo en memoria */ }
    }

    // UUID v4 con getRandomValues (crypto.randomUUID solo existe con HTTPS)
    function nuevoId() {
        const b = new Uint8Array(16);
        (window.crypto || window.msCrypto).getRandomValues(b);
        b[6] = (b[6] & 0x0f) | 0x40;
        b[8] = (b[8] & 0x3f) | 0x80;
        const h = Array.from(b, function (x) { return ('0' + x.toString(16)).slice(-2); }).join('');
        return h.slice(0, 8) + '-' + h.slice(8, 12) + '-' + h.slice(12, 16) + '-' + h.slice(16, 20) + '-' + h.slice(20);
    }

    // Huella de los datos enviados (los archivos por nombre, tamaño y fecha)
    function huella(url, datos) {
        let texto = String(url).split('#')[0] + '|';
        if (datos instanceof FormData) {
            const partes = [];
            datos.forEach(function (valor, nombre) {
                partes.push(nombre + '=' + (valor instanceof File ? 'archivo:' + valor.name + ':' + valor.size + ':' + valor.lastModified : valor));
            });
            texto += partes.join('&');
        } else if (datos instanceof URLSearchParams) {
            texto += datos.toString();
        } else if (typeof datos === 'string') {
            texto += datos;
        } else if (datos != null) {
            try { texto += JSON.stringify(datos); } catch (e) { texto += String(datos); }
        }
        // FNV-1a de 32 bits: basta para distinguir envíos de la misma pantalla
        let h = 0x811c9dc5;
        for (let i = 0; i < texto.length; i++) {
            h ^= texto.charCodeAt(i);
            h = Math.imul(h, 0x01000193) >>> 0;
        }
        return h.toString(16) + ':' + texto.length;
    }

    function esEnvioControlado(url, metodo) {
        return String(metodo || 'GET').toUpperCase() === 'POST' && /(^|\/)ajax\/[^?#]+\.php/.test(String(url));
    }

    function idPara(clave) {
        if (!pendientes[clave]) {
            pendientes[clave] = nuevoId();
            guardarPendientes();
        }
        return pendientes[clave];
    }

    function olvidar(clave) {
        if (pendientes[clave]) {
            delete pendientes[clave];
            guardarPendientes();
        }
    }

    // ---------- Aviso de corte ----------
    let avisoVisible = false;
    function avisarCorte() {
        if (avisoVisible) return;
        avisoVisible = true;
        const titulo = navigator.onLine ? 'No llegó la respuesta del servidor' : 'Se perdió la conexión a internet';
        const texto = 'No se sabe si los datos alcanzaron a guardarse, pero nunca quedan a medias: se guarda todo o nada.\n\n'
            + 'Cuando vuelva la conexión, presione de nuevo el mismo botón SIN cambiar los datos: '
            + 'si ya se había guardado, el sistema lo reconoce y no se duplica.';
        const cerrar = function () { avisoVisible = false; };
        try {
            let r;
            if (window.Swal && typeof window.Swal.fire === 'function') {
                // SweetAlert2 (v8 usa "type"; las versiones nuevas, "icon")
                const opciones = { title: titulo, text: texto, confirmButtonText: 'Entendido' };
                opciones[parseInt(window.Swal.version, 10) < 9 ? 'type' : 'icon'] = 'warning';
                r = window.Swal.fire(opciones);
            } else if (typeof window.swal === 'function') {
                r = window.swal({ title: titulo, text: texto, icon: 'warning', button: 'Entendido' });
            } else {
                window.alert(titulo + '\n\n' + texto);
            }
            if (r && typeof r.then === 'function') r.then(cerrar, cerrar); else cerrar();
        } catch (e) {
            window.alert(titulo + '\n\n' + texto);
            cerrar();
        }
    }

    // ---------- XMLHttpRequest ($.ajax, $.post, DataTables...) ----------
    // Se hace aquí y no con $.ajaxPrefilter porque varias vistas cargan su propia copia de jQuery.
    const abrirOriginal = XMLHttpRequest.prototype.open;
    const enviarOriginal = XMLHttpRequest.prototype.send;

    XMLHttpRequest.prototype.open = function (metodo, url, asincrono) {
        this._controlEnvio = esEnvioControlado(url, metodo) && asincrono !== false ? { url: url } : null;
        return abrirOriginal.apply(this, arguments);
    };

    XMLHttpRequest.prototype.send = function (cuerpo) {
        const control = this._controlEnvio;
        if (control) {
            const xhr = this;
            const clave = huella(control.url, cuerpo);
            let cancelado = false;
            xhr.setRequestHeader('X-Solicitud-Id', idPara(clave));
            if (!xhr.timeout) xhr.timeout = TIEMPO_MAXIMO;
            xhr.addEventListener('abort', function () { cancelado = true; });
            xhr.addEventListener('loadend', function () {
                // status 0 = sin respuesta (corte o tiempo agotado): se conserva para el reintento
                if (xhr.status !== 0) {
                    olvidar(clave);
                } else if (!cancelado) {
                    avisarCorte();
                }
            });
        }
        return enviarOriginal.apply(this, arguments);
    };

    // ---------- fetch ----------
    if (window.fetch) {
        const fetchOriginal = window.fetch.bind(window);
        window.fetch = function (recurso, opciones) {
            opciones = opciones || {};
            const url = typeof recurso === 'string' ? recurso : (recurso && recurso.url) || '';
            const metodo = opciones.method || (recurso && recurso.method) || 'GET';
            if (!esEnvioControlado(url, metodo)) return fetchOriginal(recurso, opciones);

            const clave = huella(url, opciones.body);
            const cabeceras = new Headers(opciones.headers || (recurso && recurso.headers) || {});
            cabeceras.set('X-Solicitud-Id', idPara(clave));
            return fetchOriginal(recurso, Object.assign({}, opciones, { headers: cabeceras })).then(function (respuesta) {
                olvidar(clave);
                return respuesta;
            }, function (error) {
                if (!error || error.name !== 'AbortError') avisarCorte();
                throw error;
            });
        };
    }

    // ---------- Franja "sin internet" ----------
    function actualizarFranja() {
        let franja = document.getElementById('franjaSinConexion');
        if (navigator.onLine) {
            if (franja) franja.style.display = 'none';
            return;
        }
        if (!franja) {
            franja = document.createElement('div');
            franja.id = 'franjaSinConexion';
            franja.setAttribute('role', 'alert');
            franja.style.cssText = 'position:fixed;top:0;left:0;right:0;z-index:100000;background:#c62828;color:#fff;'
                + 'text-align:center;padding:8px 16px;font-weight:600;font-size:14px;box-shadow:0 2px 6px rgba(0,0,0,.3)';
            franja.textContent = 'Sin conexión a internet. No envíe datos hasta que vuelva la conexión.';
            document.body.appendChild(franja);
        }
        franja.style.display = 'block';
    }
    window.addEventListener('online', actualizarFranja);
    window.addEventListener('offline', actualizarFranja);
    document.addEventListener('DOMContentLoaded', actualizarFranja);

    // ---------- Formularios normales: un solo envío ----------
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!(form instanceof HTMLFormElement) || String(form.method).toLowerCase() !== 'post') return;
        // Los que abren en otra pestaña (PDF, vista previa) se pueden volver a enviar
        if (form.target && form.target !== '_self') return;
        // Se revisa después de los demás manejadores: si alguno canceló el envío (validación), no se toca
        setTimeout(function () {
            if (e.defaultPrevented) return;
            const botones = form.querySelectorAll('button[type="submit"], input[type="submit"], button:not([type])');
            botones.forEach(function (b) { b.disabled = true; });
            // Si la página no cambia (descarga de PDF, corte), se vuelven a habilitar
            setTimeout(function () { botones.forEach(function (b) { b.disabled = false; }); }, 15000);
        }, 0);
    });
})();
