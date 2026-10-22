/**
 * Registrar Notas (solo ADM): "Calificaciones pendientes".
 * Lista los módulos que ya terminaron y tienen estudiantes sin nota, marcados, con el
 * último aviso enviado y si el docente ya lo vio. Desde aquí se avisa a los docentes
 * de los módulos marcados (ajax/avisocalificacion.ajax.php); el docente ve el aviso en
 * su panel y en Reporte de calificaciones.
 */
(function ($) {
    'use strict';

    let pendientes = [];

    function esc(t) {
        return $('<div>').text(t == null ? '' : String(t)).html();
    }
    function fecha(f) {
        if (!f) return '';
        const p = String(f).slice(0, 10).split('-');
        return p[2] + '/' + p[1] + '/' + p[0];
    }
    function diasDesde(f) {
        return Math.max(0, Math.round((new Date(new Date().toLocaleDateString('en-CA')) - new Date(String(f).slice(0, 10))) / 86400000));
    }

    function cargar() {
        $.post('ajax/avisocalificacion.ajax.php', { accion: 'pendientes' }, function (resp) {
            pendientes = (resp && resp.modulos) || [];
            pintar();
        }, 'json').fail(function () {
            $('#rnPendLista').html('<div class="rn-pend-vacio">No se pudo cargar la lista</div>');
        });
    }

    function pintar() {
        $('#rnPendN').text(pendientes.length).toggleClass('is-cero', pendientes.length === 0);
        if (!pendientes.length) {
            $('#rnPendLista').html('<div class="rn-pend-vacio"><i class="fa fa-check-circle" style="color:#15803d"></i> Todos los módulos terminados tienen sus calificaciones registradas.</div>');
            $('#rnPendAcciones, #rnPendCaja').hide();
            return;
        }
        let html = '<div class="table-responsive rn-pend-lista-scroll"><table class="rn-pend-tabla"><thead><tr>' +
            '<th style="width:32px"><input type="checkbox" id="rnPendTodos" checked aria-label="Marcar todos"></th>' +
            '<th>Módulo</th><th>Docente</th><th>Terminó</th><th>Calificados</th><th>Aviso</th></tr></thead><tbody>';
        pendientes.forEach(function (m) {
            const total = parseInt(m.TotalEstudiantes, 10) || 0;
            const hechos = parseInt(m.TotalCalificados, 10) || 0;
            const pct = total ? Math.round(hechos / total * 100) : 0;
            const dias = diasDesde(m.FechaFinal);
            let aviso = '<span class="rn-pend-aviso-no">Sin avisar</span>';
            if (m.UltimoAviso) {
                aviso = 'Avisado el ' + fecha(m.UltimoAviso) + (parseInt(m.NumAvisos, 10) > 1 ? ' (' + m.NumAvisos + ' veces)' : '') +
                    (m.UltimoLeido
                        ? '<small class="rn-pend-visto"><i class="fa fa-check"></i> Visto el ' + fecha(m.UltimoLeido) + '</small>'
                        : '<small class="rn-pend-novisto"><i class="fa fa-clock"></i> Aún no lo vio</small>');
            }
            html += '<tr class="is-marcada">' +
                '<td><input type="checkbox" class="rn-pend-check" value="' + esc(m.Idmodulo) + '" checked aria-label="Marcar ' + esc(m.NombreModulo) + '"></td>' +
                '<td><strong>' + esc(m.CodigoModulo) + ' · ' + esc(m.NombreModulo) + '</strong><small>' + esc(m.NombrePrograma) + '</small></td>' +
                '<td>' + esc(m.Docente) + '</td>' +
                '<td style="white-space:nowrap">' + fecha(m.FechaFinal) + '<small class="rn-pend-atraso">hace ' + dias + (dias === 1 ? ' día' : ' días') + '</small></td>' +
                '<td style="white-space:nowrap"><span class="rn-pend-barra"><span style="width:' + pct + '%"></span></span>' + hechos + ' de ' + total + '</td>' +
                '<td>' + aviso + '</td>' +
            '</tr>';
        });
        html += '</tbody></table></div>';
        $('#rnPendLista').html(html);
        $('#rnPendAcciones').show();
        contarMarcados();
    }

    function marcados() {
        return $('.rn-pend-check:checked').map(function () { return this.value; }).get();
    }
    function contarMarcados() {
        const n = marcados().length;
        $('#rnPendNotificar').prop('disabled', n === 0)
            .html('<i class="fa fa-bell"></i> Notificar a los docentes (' + n + ' módulo' + (n === 1 ? '' : 's') + ')');
        $('#rnPendTodos').prop('checked', n === $('.rn-pend-check').length);
    }

    $(function () {
        if (!$('#rnPendientes').length) return;
        cargar();

        $('#rnPendToggle').on('click', function () {
            const $c = $('#rnPendCuerpo').slideToggle(150);
            setTimeout(function () {
                $('#rnPendToggle').html($c.is(':visible') ? '<i class="fa fa-chevron-up"></i> Ocultar' : '<i class="fa fa-chevron-down"></i> Ver');
            }, 160);
        });
        $(document).on('change', '#rnPendTodos', function () {
            $('.rn-pend-check').prop('checked', this.checked).closest('tr').toggleClass('is-marcada', this.checked);
            contarMarcados();
        });
        $(document).on('change', '.rn-pend-check', function () {
            $(this).closest('tr').toggleClass('is-marcada', this.checked);
            contarMarcados();
        });

        $('#rnPendNotificar').on('click', function () {
            $('#rnPendAcciones').hide();
            $('#rnPendCaja').slideDown(150);
            $('#rnPendMensaje').trigger('focus');
        });
        $('#rnPendCancelar').on('click', function () {
            $('#rnPendCaja').slideUp(150);
            $('#rnPendAcciones').show();
        });
        $('#rnPendEnviar').on('click', function () {
            const modulos = marcados();
            const mensaje = $('#rnPendMensaje').val().trim();
            if (!modulos.length || mensaje.length < 10) {
                Swal.fire({ icon: 'warning', title: 'Falta información', text: !modulos.length ? 'Marque al menos un módulo' : 'Escriba el mensaje para los docentes' });
                return;
            }
            const $btn = $(this).prop('disabled', true);
            $.post('ajax/avisocalificacion.ajax.php', { accion: 'notificar', mensaje: mensaje, 'modulos[]': modulos }, function (resp) {
                $btn.prop('disabled', false);
                if (resp && resp.success) {
                    $('#rnPendCaja').hide();
                    Swal.fire({ icon: 'success', title: 'Aviso enviado', text: 'Se avisó de ' + resp.enviados + ' módulo(s). Los docentes lo verán al ingresar al sistema.', timer: 2500 });
                    cargar();
                } else {
                    Swal.fire({ icon: 'error', title: 'No se envió', text: (resp && resp.mensaje) || 'No se pudo enviar el aviso' });
                }
            }, 'json').fail(function () {
                $btn.prop('disabled', false);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error de comunicación con el servidor' });
            });
        });
    });
})(jQuery);
