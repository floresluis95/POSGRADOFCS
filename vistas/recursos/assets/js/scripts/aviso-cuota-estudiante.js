/**
 * Aviso de cuota pendiente para el Estudiante: burbuja flotante fija en
 * pantalla (no un modal de una sola vez). Se muestra desde que
 * Administración/Secretaría registra el plazo de cuota del programa (venza o
 * no) mientras el estudiante tenga una orden de pago pendiente; si el plazo
 * ya venció, además no podrá ver sus calificaciones hasta regularizar su
 * pago. Se vuelve a mostrar en cada página mientras la condición siga
 * vigente; el estudiante puede minimizarla, pero reaparece al navegar.
 */
(function () {
    var BURBUJA_ID = 'burbujaCuotaPendiente';
    var MINI_ID = 'burbujaCuotaPendienteMini';
    var URL_ORDEN_PAGO = 'misordenespago';

    function formatoFecha(iso) {
        var d = new Date(iso + 'T00:00:00');
        if (isNaN(d.getTime())) return iso;
        return d.toLocaleDateString('es-BO', { day: '2-digit', month: 'long', year: 'numeric' });
    }

    function construirContenido(avisos) {
        var hayVencidos = false;

        var itemsHtml = avisos.map(function (a) {
            var dias = parseInt(a.DiasVencido, 10) || 0;
            var diasAbs = Math.abs(dias);
            var etiqueta;
            if (dias > 0) {
                hayVencidos = true;
                etiqueta = 'Venció el ' + formatoFecha(a.FechaPlazo) +
                    ' (' + diasAbs + (diasAbs === 1 ? ' día' : ' días') + ' de atraso)';
            } else if (dias === 0) {
                etiqueta = 'Vence hoy, ' + formatoFecha(a.FechaPlazo);
            } else {
                etiqueta = 'Vence el ' + formatoFecha(a.FechaPlazo) +
                    ' (faltan ' + diasAbs + (diasAbs === 1 ? ' día' : ' días') + ')';
            }
            return '<li style="margin-bottom:5px;"><strong>' + a.NombrePrograma + '</strong><br>' + etiqueta + '</li>';
        }).join('');

        return { itemsHtml: itemsHtml, hayVencidos: hayVencidos };
    }

    function inyectarEstilos() {
        if (document.getElementById('estiloBurbujaCuotaPendiente')) return;

        var style = document.createElement('style');
        style.id = 'estiloBurbujaCuotaPendiente';
        style.textContent =
            '#' + BURBUJA_ID + '{position:fixed;bottom:20px;right:20px;z-index:99999;width:300px;max-width:calc(100vw - 24px);' +
            'background:#fff;border-radius:10px;box-shadow:0 6px 24px rgba(0,0,0,.28);font-family:inherit;' +
            'overflow:hidden;animation:burbujaCuotaAparecer .25s ease-out;}' +
            '#' + BURBUJA_ID + ' .bcp-header{display:flex;align-items:center;justify-content:space-between;' +
            'padding:10px 14px;color:#fff;font-weight:600;font-size:14px;}' +
            '#' + BURBUJA_ID + ' .bcp-cerrar{cursor:pointer;font-size:20px;line-height:1;opacity:.85;padding:0 4px;}' +
            '#' + BURBUJA_ID + ' .bcp-cerrar:hover{opacity:1;}' +
            '#' + BURBUJA_ID + ' .bcp-body{padding:12px 14px;font-size:13px;color:#333;max-height:220px;overflow-y:auto;}' +
            '#' + BURBUJA_ID + ' .bcp-body ul{margin:0;padding-left:18px;}' +
            '#' + BURBUJA_ID + ' .bcp-body p{margin:8px 0 0;}' +
            '#' + BURBUJA_ID + ' .bcp-footer{padding:0 14px 14px;}' +
            '#' + BURBUJA_ID + ' .bcp-btn{display:block;width:100%;border:none;border-radius:6px;padding:9px 12px;' +
            'font-size:13px;font-weight:600;color:#fff;cursor:pointer;}' +
            '#' + BURBUJA_ID + ' .bcp-btn:hover{filter:brightness(0.92);}' +
            '#' + MINI_ID + '{position:fixed;bottom:20px;right:20px;z-index:99999;width:52px;height:52px;' +
            'border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;' +
            'box-shadow:0 4px 14px rgba(0,0,0,.32);color:#fff;font-size:22px;animation:burbujaCuotaAparecer .2s ease-out;}' +
            '@keyframes burbujaCuotaAparecer{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}';
        document.head.appendChild(style);
    }

    function quitarExistentes() {
        var actual = document.getElementById(BURBUJA_ID);
        if (actual) actual.remove();
        var mini = document.getElementById(MINI_ID);
        if (mini) mini.remove();
    }

    function irAOrdenPago() {
        window.location.href = URL_ORDEN_PAGO;
    }

    function mostrarMini(color, avisos) {
        quitarExistentes();

        var mini = document.createElement('div');
        mini.id = MINI_ID;
        mini.style.background = color;
        mini.title = 'Tiene una cuota pendiente: clic para ver detalle';
        mini.textContent = '⚠';
        mini.addEventListener('click', function () {
            mostrarBurbuja(avisos);
        });
        document.body.appendChild(mini);
    }

    function mostrarBurbuja(avisos) {
        if (!avisos || !avisos.length) return;

        inyectarEstilos();
        quitarExistentes();

        var info = construirContenido(avisos);
        var color = info.hayVencidos ? '#c0392b' : '#d4a017';

        var contenedor = document.createElement('div');
        contenedor.id = BURBUJA_ID;

        var header = document.createElement('div');
        header.className = 'bcp-header';
        header.style.background = color;

        var titulo = document.createElement('span');
        titulo.textContent = '⚠ ' + (info.hayVencidos ? 'Cuota vencida' : 'Cuota por vencer');
        header.appendChild(titulo);

        var cerrar = document.createElement('span');
        cerrar.className = 'bcp-cerrar';
        cerrar.innerHTML = '&times;';
        cerrar.title = 'Minimizar';
        cerrar.addEventListener('click', function () {
            mostrarMini(color, avisos);
        });
        header.appendChild(cerrar);

        var body = document.createElement('div');
        body.className = 'bcp-body';
        body.innerHTML = '<ul>' + info.itemsHtml + '</ul>' +
            (info.hayVencidos
                ? '<p><strong>No podrá ver sus calificaciones hasta regularizar su pago.</strong></p>'
                : '<p>Regularice su pago antes de la fecha límite para evitar quedar bloqueado.</p>');

        var footer = document.createElement('div');
        footer.className = 'bcp-footer';

        var boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'bcp-btn';
        boton.style.background = color;
        boton.textContent = 'Ver orden de pago';
        boton.addEventListener('click', irAOrdenPago);
        footer.appendChild(boton);

        contenedor.appendChild(header);
        contenedor.appendChild(body);
        contenedor.appendChild(footer);
        document.body.appendChild(contenedor);
    }

    function verificarAviso() {
        fetch('ajax/plazocuotaprograma.ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'accion=avisoEstudiante'
        })
            .then(function (resp) { return resp.json(); })
            .then(function (data) {
                if (data && data.success && data.hayAviso) {
                    mostrarBurbuja(data.avisos);
                }
            })
            .catch(function () {
                // Silencioso: si falla la verificación, no se interrumpe la navegación normal.
            });
    }

    if (document.readyState === 'complete') {
        verificarAviso();
    } else {
        window.addEventListener('load', verificarAviso);
    }
})();
