/**
 * Avisos de pago para el Estudiante: burbuja flotante fija en pantalla (no un
 * modal de una sola vez). Muestra dos tipos de aviso (ver
 * PlazoCuotaProgramaControlador::AvisoEstudianteAjaxControlador):
 *  - Personales: sus órdenes de cuota pendientes con fecha límite.
 *  - Generales: el plazo de cuota de su programa, aunque aún no tenga una orden.
 * Reaparece en cada página mientras haya avisos; el estudiante puede minimizarla.
 */
(function () {
    var BURBUJA_ID = 'burbujaCuotaPendiente';
    var MINI_ID = 'burbujaCuotaPendienteMini';
    var URL_ORDEN_PAGO = 'misordenespago';
    var COLOR_VENCIDO = '#b91c1c';
    var COLOR_POR_VENCER = '#b45309';

    function escapar(texto) {
        var div = document.createElement('div');
        div.textContent = texto == null ? '' : String(texto);
        return div.innerHTML;
    }

    function formatoFecha(iso) {
        var d = new Date(iso + 'T00:00:00');
        if (isNaN(d.getTime())) return iso;
        return d.toLocaleDateString('es-BO', { day: '2-digit', month: 'long', year: 'numeric' });
    }

    function formatoBs(monto) {
        return 'Bs. ' + (parseFloat(monto) || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // "Venció el 23 de septiembre (4 días de atraso)" / "Vence hoy" / "Vence el ... (faltan 3 días)"
    function textoPlazo(fecha, dias) {
        var n = Math.abs(dias);
        var unidad = n === 1 ? ' día' : ' días';
        if (dias > 0) return 'Venció el ' + formatoFecha(fecha) + ' (' + n + unidad + ' de atraso)';
        if (dias === 0) return 'Vence hoy, ' + formatoFecha(fecha);
        return 'Vence el ' + formatoFecha(fecha) + ' (faltan ' + n + unidad + ')';
    }

    function item(aviso, detalle) {
        var dias = parseInt(aviso.DiasVencido, 10) || 0;
        var claseEstado = dias > 0 ? 'bcp-vencido' : 'bcp-por-vencer';
        return '<li class="' + claseEstado + '">' +
            '<span class="bcp-punto"></span>' +
            '<div><strong>' + detalle + '</strong>' +
            '<small>' + escapar(aviso.NombrePrograma) + '</small>' +
            '<span class="bcp-fecha">' + textoPlazo(aviso.Fecha, dias) + '</span></div>' +
        '</li>';
    }

    function construirContenido(avisos) {
        var personales = avisos.filter(function (a) { return a.Tipo === 'PERSONAL'; });
        var generales = avisos.filter(function (a) { return a.Tipo === 'GENERAL'; });
        var hayVencidos = avisos.some(function (a) { return (parseInt(a.DiasVencido, 10) || 0) > 0; });

        var html = '';
        if (personales.length) {
            html += '<div class="bcp-grupo">Tus órdenes de pago</div><ul>' +
                personales.map(function (a) { return item(a, 'Cuota de ' + formatoBs(a.Monto)); }).join('') +
                '</ul>';
        }
        if (generales.length) {
            html += '<div class="bcp-grupo">Plazo de cuota de tu programa</div><ul>' +
                generales.map(function (a) {
                    return item(a, a.Descripcion ? escapar(a.Descripcion) : 'Pago de la cuota vigente');
                }).join('') +
                '</ul>';
        }

        return { html: html, hayVencidos: hayVencidos };
    }

    function inyectarEstilos() {
        if (document.getElementById('estiloBurbujaCuotaPendiente')) return;

        var b = '#' + BURBUJA_ID;
        var style = document.createElement('style');
        style.id = 'estiloBurbujaCuotaPendiente';
        style.textContent =
            b + '{position:fixed;bottom:20px;right:20px;z-index:99999;width:330px;max-width:calc(100vw - 24px);' +
            'background:#fff;border-radius:14px;box-shadow:0 12px 32px rgba(15,23,42,.28);font-family:inherit;' +
            'overflow:hidden;animation:burbujaCuotaAparecer .25s ease-out;color:#1f2937;}' +
            b + ' .bcp-header{display:flex;align-items:center;justify-content:space-between;gap:8px;' +
            'padding:12px 16px;color:#fff;font-weight:700;font-size:14px;}' +
            b + ' .bcp-cerrar{cursor:pointer;font-size:22px;line-height:1;opacity:.9;padding:0 4px;background:none;border:0;color:#fff;}' +
            b + ' .bcp-cerrar:hover{opacity:1;}' +
            b + ' .bcp-body{padding:6px 16px 12px;font-size:13px;max-height:280px;overflow-y:auto;}' +
            b + ' .bcp-grupo{font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#6b7280;margin:10px 0 6px;}' +
            b + ' ul{list-style:none;margin:0;padding:0;}' +
            b + ' li{display:flex;gap:10px;padding:8px 10px;border-radius:10px;background:#f8fafc;margin-bottom:6px;}' +
            b + ' li strong{display:block;font-size:13px;color:#1f2937;}' +
            b + ' li small{display:block;font-size:12px;color:#6b7280;}' +
            b + ' .bcp-fecha{display:block;font-size:12px;font-weight:600;margin-top:2px;}' +
            b + ' .bcp-punto{flex-shrink:0;width:8px;height:8px;border-radius:50%;margin-top:6px;}' +
            b + ' .bcp-vencido .bcp-punto{background:' + COLOR_VENCIDO + ';}' +
            b + ' .bcp-vencido .bcp-fecha{color:' + COLOR_VENCIDO + ';}' +
            b + ' .bcp-por-vencer .bcp-punto{background:' + COLOR_POR_VENCER + ';}' +
            b + ' .bcp-por-vencer .bcp-fecha{color:' + COLOR_POR_VENCER + ';}' +
            b + ' .bcp-nota{margin:8px 0 0;font-size:12.5px;color:#374151;}' +
            b + ' .bcp-nota.bcp-alerta{color:' + COLOR_VENCIDO + ';font-weight:600;}' +
            b + ' .bcp-footer{padding:0 16px 16px;}' +
            b + ' .bcp-btn{display:block;width:100%;border:none;border-radius:10px;padding:10px 12px;' +
            'font-size:13px;font-weight:700;color:#fff;cursor:pointer;}' +
            b + ' .bcp-btn:hover{filter:brightness(.92);}' +
            '#' + MINI_ID + '{position:fixed;bottom:20px;right:20px;z-index:99999;width:54px;height:54px;' +
            'border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;border:0;' +
            'box-shadow:0 6px 18px rgba(15,23,42,.32);color:#fff;font-size:22px;animation:burbujaCuotaAparecer .2s ease-out;}' +
            '#' + MINI_ID + ' .bcp-contador{position:absolute;top:-4px;right:-4px;min-width:20px;height:20px;border-radius:10px;' +
            'background:#fff;color:#1f2937;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;padding:0 5px;' +
            'box-shadow:0 1px 4px rgba(0,0,0,.25);}' +
            '@keyframes burbujaCuotaAparecer{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}';
        document.head.appendChild(style);
    }

    function quitarExistentes() {
        var actual = document.getElementById(BURBUJA_ID);
        if (actual) actual.remove();
        var mini = document.getElementById(MINI_ID);
        if (mini) mini.remove();
    }

    function mostrarMini(color, datos) {
        quitarExistentes();

        var mini = document.createElement('button');
        mini.type = 'button';
        mini.id = MINI_ID;
        mini.style.background = color;
        mini.title = 'Tienes avisos de pago: clic para ver el detalle';
        mini.setAttribute('aria-label', mini.title);
        mini.innerHTML = '⚠<span class="bcp-contador">' + datos.avisos.length + '</span>';
        mini.addEventListener('click', function () {
            mostrarBurbuja(datos);
        });
        document.body.appendChild(mini);
    }

    function mostrarBurbuja(datos) {
        var avisos = datos.avisos || [];
        if (!avisos.length) return;

        inyectarEstilos();
        quitarExistentes();

        var info = construirContenido(avisos);
        var color = info.hayVencidos ? COLOR_VENCIDO : COLOR_POR_VENCER;

        var contenedor = document.createElement('div');
        contenedor.id = BURBUJA_ID;
        contenedor.setAttribute('role', 'alert');

        var header = document.createElement('div');
        header.className = 'bcp-header';
        header.style.background = color;
        header.innerHTML = '<span>⚠ ' + (info.hayVencidos ? 'Tienes pagos vencidos' : 'Tienes pagos por vencer') + '</span>';

        var cerrar = document.createElement('button');
        cerrar.type = 'button';
        cerrar.className = 'bcp-cerrar';
        cerrar.innerHTML = '&times;';
        cerrar.title = 'Minimizar';
        cerrar.setAttribute('aria-label', 'Minimizar');
        cerrar.addEventListener('click', function () {
            mostrarMini(color, datos);
        });
        header.appendChild(cerrar);

        var body = document.createElement('div');
        body.className = 'bcp-body';
        body.innerHTML = info.html + (datos.bloqueado
            ? '<p class="bcp-nota bcp-alerta">No podrás ver tus calificaciones hasta regularizar tu pago.</p>'
            : '<p class="bcp-nota">Regulariza tu pago antes de la fecha límite para evitar quedar bloqueado.</p>');

        var footer = document.createElement('div');
        footer.className = 'bcp-footer';

        var boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'bcp-btn';
        boton.style.background = color;
        boton.textContent = 'Ver mis órdenes de pago';
        boton.addEventListener('click', function () {
            window.location.href = URL_ORDEN_PAGO;
        });
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
                    mostrarBurbuja(data);
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
