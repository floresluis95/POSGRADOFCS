/**
 * Aviso de cuota vencida para el Estudiante al ingresar al sistema.
 * Si el plazo de cuota de su(s) programa(s) ya venció y tiene una orden de
 * pago pendiente, se le notifica que no podrá ver sus calificaciones hasta
 * regularizar su pago. Se muestra una sola vez por sesión de navegador.
 */
(function () {
    var YA_MOSTRADO_KEY = 'avisoCuotaVencidaMostrado';

    function mostrarAviso(avisos) {
        if (typeof Swal === 'undefined' || !avisos || !avisos.length) return;

        var listaHtml = avisos.map(function (a) {
            var dias = parseInt(a.DiasVencido, 10) || 0;
            return '<li style="text-align:left; margin-bottom:6px;">' +
                '<strong>' + a.NombrePrograma + '</strong><br>' +
                'Plazo vencido el ' + formatoFecha(a.FechaPlazo) +
                ' (' + dias + (dias === 1 ? ' día' : ' días') + ' de atraso)' +
                '</li>';
        }).join('');

        Swal.fire({
            icon: 'warning',
            title: 'Tiene una cuota pendiente',
            html:
                '<p>El plazo de pago de su cuota ya venció en el/los siguiente(s) programa(s):</p>' +
                '<ul style="padding-left: 18px;">' + listaHtml + '</ul>' +
                '<p class="mt-2"><strong>Si no cancela, no podrá ver sus calificaciones.</strong> ' +
                'Por favor regularice su pago en Secretaría lo antes posible.</p>',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#c0392b',
            allowOutsideClick: true
        });

        try {
            sessionStorage.setItem(YA_MOSTRADO_KEY, '1');
        } catch (e) {
            // Almacenamiento no disponible (modo privado, etc.): no es crítico, simplemente
            // podría repetirse el aviso en la siguiente navegación de la misma sesión.
        }
    }

    function formatoFecha(iso) {
        var d = new Date(iso + 'T00:00:00');
        if (isNaN(d.getTime())) return iso;
        return d.toLocaleDateString('es-BO', { day: '2-digit', month: 'long', year: 'numeric' });
    }

    function verificarAviso() {
        var yaMostrado = false;
        try {
            yaMostrado = sessionStorage.getItem(YA_MOSTRADO_KEY) === '1';
        } catch (e) {
            yaMostrado = false;
        }
        if (yaMostrado) return;

        fetch('ajax/plazocuotaprograma.ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'accion=avisoEstudiante'
        })
            .then(function (resp) { return resp.json(); })
            .then(function (data) {
                if (data && data.success && data.hayAviso) {
                    mostrarAviso(data.avisos);
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
