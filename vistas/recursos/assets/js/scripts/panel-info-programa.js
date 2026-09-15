/**
 * Panel principal: modal "Más Información" de cada diapositiva del carrusel
 * (costo, módulos, fecha de inicio, etc. de un programa real). Disponible
 * para todos los usuarios, no solo Administración.
 *
 * Se difiere a window 'load' porque este script se imprime ANTES que
 * jQuery/SweetAlert2 en el HTML final de la página.
 */
(function () {
    function formatoMoneda(valor) {
        var n = parseFloat(valor);
        if (isNaN(n)) return '-';
        return 'Bs. ' + n.toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function iniciar() {
        if (typeof $ === 'undefined') return;

        $(document).on('click', '.btn-info-programa', function () {
            var $btn = $(this);

            $('#infoProgramaTitulo').text($btn.data('programa') || 'Programa');
            $('#infoProgramaGrado').text(($btn.data('grado') || '') + ' · ' + ($btn.data('codigo') || ''));
            $('#infoProgramaCosto').text(formatoMoneda($btn.data('costo')));
            $('#infoProgramaMatricula').text(formatoMoneda($btn.data('matricula')));
            $('#infoProgramaModulos').text($btn.data('modulos') || '-');
            var duracion = $btn.data('duracion');
            $('#infoProgramaDuracion').text(duracion ? (duracion + ' meses') : 'A definir');
            $('#infoProgramaFecha').text($btn.data('fecha') || 'A definir');
            $('#infoProgramaSede').text($btn.data('sede') || '-');

            var detalle = $btn.data('detalle');
            if (detalle) {
                $('#infoProgramaDetalle').text(detalle);
                $('#infoProgramaDetalleWrap').show();
            } else {
                $('#infoProgramaDetalleWrap').hide();
            }

            $('#modalInfoPrograma').modal('show');
        });
    }

    if (document.readyState === 'complete') {
        iniciar();
    } else {
        window.addEventListener('load', iniciar);
    }
})();
