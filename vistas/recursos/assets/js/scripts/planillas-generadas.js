/**
 * Botón "Generadas": lista las planillas de calificaciones ya generadas de un
 * módulo (copias guardadas en pdfs_generados/, la más reciente primero) y
 * cada una se abre tal como salió, en otra pestaña, para volver a imprimirla.
 *
 * Lo usan "Reporte de Notas" (ADM/SEC/AUX, ajax/reportenotas.ajax.php) y
 * "Reporte de calificaciones" del docente (ajax/calificacion.ajax.php).
 * Funciona con SweetAlert2 v8 (type) y v11 (icon).
 *
 *   mostrarPlanillasGeneradas({ url, datos, moduloNombre }) -> Promise
 *     url:   endpoint ajax
 *     datos: lo que se envía por POST (acción, moduloID, programaID, moduloCodigo)
 *   La promesa se resuelve cuando se cierra el cuadro.
 */
(function (window, $) {
    'use strict';

    function esc(texto) {
        return String(texto === null || texto === undefined ? '' : texto)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }
    function fechaHora(texto) {
        // "2026-11-25 12:04:56" -> "25/11/2026 12:04"
        const m = /^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/.exec(texto || '');
        return m ? (m[3] + '/' + m[2] + '/' + m[1] + ' ' + m[4] + ':' + m[5]) : esc(texto);
    }
    function fecha(texto) {
        const m = /^(\d{4})-(\d{2})-(\d{2})/.exec(texto || '');
        return m ? (m[3] + '/' + m[2] + '/' + m[1]) : '';
    }

    function armarTabla(lista) {
        let html = '<div style="max-height:360px; overflow:auto; text-align:left;">' +
                   '<table class="table table-sm table-bordered mb-0" style="font-size:13px;">' +
                   '<thead class="thead-light"><tr><th>Generada</th><th>Detalle</th><th class="text-center">PDF</th></tr></thead><tbody>';
        lista.forEach(function (p) {
            let detalle;
            if (p.anterior) {
                detalle = '<span class="badge badge-secondary">Anterior</span> ' +
                          '<small class="text-muted">sin datos guardados; revise que sea de este programa</small>';
            } else {
                detalle = (p.usuario ? 'Por <strong>' + esc(p.usuario) + '</strong><br>' : '') +
                          '<small class="text-muted">' +
                          (p.fechaInicio || p.fechaFin ? 'Módulo: ' + fecha(p.fechaInicio) + ' al ' + fecha(p.fechaFin) : '') +
                          (p.fechaFirma ? ' · Firma: ' + fecha(p.fechaFirma) : '') +
                          (p.estudiantes !== null && p.estudiantes !== undefined ? ' · ' + esc(p.estudiantes) + ' estudiante(s)' : '') +
                          '</small>';
            }
            html += '<tr><td style="white-space:nowrap;">' + fechaHora(p.generado) + '</td><td>' + detalle + '</td>' +
                    '<td class="text-center"><a class="btn btn-sm btn-danger" target="_blank" rel="noopener" ' +
                    'href="vistas/componentes/ver-planilla-guardada.php?archivo=' + encodeURIComponent(p.archivo) + '">' +
                    '<i class="fas fa-file-pdf"></i> Abrir</a></td></tr>';
        });
        return html + '</tbody></table></div>';
    }

    window.mostrarPlanillasGeneradas = function (opciones) {
        // Promise nativo (no .then de jQuery): se resuelve recién cuando se
        // cierra el SweetAlert, sin importar la versión de jQuery de la página.
        return Promise.resolve($.ajax({
            url: opciones.url,
            type: 'POST',
            dataType: 'json',
            data: opciones.datos
        })).then(function (resp) {
            const lista = (resp && resp.status === 'success') ? (resp.data || []) : null;
            let html;
            if (lista === null) {
                html = '<div class="alert alert-danger">' + esc((resp && resp.message) || 'No se pudo cargar la lista') + '</div>';
            } else if (!lista.length) {
                html = '<p class="text-muted my-3"><i class="fas fa-info-circle"></i> Todavía no se generó ninguna planilla de este módulo.<br>' +
                       'Use el botón <strong>Imprimir</strong> para generarla.</p>';
            } else {
                html = armarTabla(lista);
            }
            return Swal.fire({
                title: 'Planillas generadas',
                html: '<p class="mb-2"><strong>' + esc(opciones.moduloNombre) + '</strong></p>' + html,
                width: 720,
                confirmButtonText: 'Cerrar'
            });
        }, function () {
            return Swal.fire({ type: 'error', icon: 'error', title: 'Error', text: 'No se pudo cargar la lista de planillas generadas' });
        });
    };
})(window, jQuery);
