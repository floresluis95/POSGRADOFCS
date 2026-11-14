/**
 * Registrar la cuenta bancaria de un programa sin salir de la pantalla.
 *
 * En Pre-Registro (matrícula), orden de cuotas y orden de clínica, a veces el programa
 * aún no tiene registrada su cuenta. Antes había que ir a Programas › Cuentas y volver
 * a llenar al estudiante y su inscripción. Ahora, debajo del combo "Cuenta de depósito",
 * aparece un enlace que abre un mini formulario en la misma pantalla (no es un modal:
 * así funciona también cuando el combo ya está dentro de un modal). Al guardar, la
 * cuenta se registra en el programa (misma acción que Programas › Cuentas), se recarga
 * el combo y queda elegida.
 *
 * Uso, cada vez que se cargan las opciones del combo:
 *     CuentaRapida.preparar('#cuentaPago', programaID, 'MATRICULA');
 *
 * Registrar cuentas es solo de Administración y Secretaría (igual que en Programas);
 * el Auxiliar ve a quién pedírsela.
 */
window.CuentaRapida = (function() {
    const NOMBRE_TIPO = { MATRICULA: 'matrícula', CUOTAS: 'cuotas', CLINICA: 'clínica' };

    // Estilos propios (un solo archivo para las tres pantallas)
    $(function() {
        if (document.getElementById('crEstilos')) return;
        $('<style id="crEstilos">'
            + '.cr-caja{margin-top:4px;font-size:12px}'
            + '.cr-aviso{color:#92400e;background:#fffbeb;border:1px solid #fde68a;border-radius:4px;padding:5px 8px}'
            + '.cr-enlace{background:none;border:0;padding:0;color:#2563eb;font-size:12px;font-weight:600;cursor:pointer;text-decoration:underline}'
            + '.cr-enlace:hover{color:#1d4ed8}'
            + '.cr-form{margin-top:6px;border:1px dashed #93c5fd;background:#f8fbff;border-radius:6px;padding:8px}'
            + '.cr-titulo{font-weight:600;color:#1e3a8a;margin-bottom:6px}'
            + '.cr-fila{display:flex;gap:6px;flex-wrap:wrap}.cr-fila .form-control{flex:1 1 140px;min-width:0}'
            + '.cr-botones{margin-top:6px;display:flex;gap:4px;align-items:center}'
            + '.cr-nota{color:#64748b;margin-top:2px}'
            + '.cr-mensaje{margin-top:6px;padding:4px 8px;border-radius:4px}'
            + '.cr-error{color:#991b1b;background:#fee2e2}.cr-exito{color:#166534;background:#dcfce7;margin-bottom:4px}'
            + '</style>').appendTo('head');
    });

    function esc(t) {
        return String(t == null ? '' : t).replace(/[&<>"']/g, function(c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function puedeRegistrar() {
        return window.PUEDE_REGISTRAR_CUENTA === true;
    }

    // ¿El combo tiene alguna cuenta elegible? (la opción vacía no cuenta)
    function tieneCuentas($select) {
        return $select.find('option').filter(function() { return this.value !== ''; }).length > 0;
    }

    function recargar($select, programaID, tipo, numeroElegir) {
        return $.ajax({
            url: 'ajax/programacuenta.ajax.php',
            type: 'POST',
            data: { accion: 'listarOptionsHtml', ProgramaID: programaID, TipoCuenta: tipo }
        }).then(function(html) {
            $select.html(html);
            if (numeroElegir) {
                const $op = $select.find('option').filter(function() {
                    return String($(this).data('numero')) === numeroElegir;
                }).first();
                if ($op.length) $select.val($op.val());
            }
            $select.trigger('change');
        });
    }

    function preparar(selector, programaID, tipo) {
        const $select = $(selector);
        if (!$select.length) return;
        programaID = parseInt(programaID, 10) || 0;
        tipo = String(tipo || 'MATRICULA').toUpperCase();
        const nombreTipo = NOMBRE_TIPO[tipo] || tipo.toLowerCase();

        // Una sola caja por combo; se rehace con el programa y el tipo actuales
        let $caja = $select.next('.cr-caja');
        if (!$caja.length) {
            $caja = $('<div class="cr-caja"></div>').insertAfter($select);
        }
        $caja.empty();
        if (!programaID) return;

        const sinCuentas = !tieneCuentas($select);

        if (!puedeRegistrar()) {
            if (sinCuentas) {
                $caja.html('<div class="cr-aviso"><i class="fa fa-exclamation-triangle"></i> Este programa aún no tiene cuenta de '
                    + esc(nombreTipo) + '. Pida a Secretaría o Administración que la registre (Programas › Cuentas); '
                    + 'después vuelva a elegir el programa, sin perder lo llenado.</div>');
            }
            return;
        }

        $caja.html(
            (sinCuentas
                ? '<div class="cr-aviso"><i class="fa fa-exclamation-triangle"></i> Este programa aún no tiene cuenta de ' + esc(nombreTipo) + '. '
                  + '<button type="button" class="cr-enlace cr-abrir"><i class="fa fa-plus-circle"></i> Registrarla aquí</button></div>'
                : '<button type="button" class="cr-enlace cr-abrir"><i class="fa fa-plus-circle"></i> ¿No está la cuenta? Registrar otra</button>')
            + '<div class="cr-form" hidden>'
            +   '<div class="cr-titulo">Nueva cuenta de ' + esc(nombreTipo) + ' para este programa</div>'
            +   '<div class="cr-fila">'
            +     '<input type="text" class="form-control form-control-sm cr-numero" maxlength="40" placeholder="Número de cuenta *" aria-label="Número de cuenta">'
            +     '<input type="text" class="form-control form-control-sm cr-banco" maxlength="60" placeholder="Banco (opcional)" aria-label="Banco">'
            +   '</div>'
            +   '<div class="cr-mensaje" hidden></div>'
            +   '<div class="cr-botones">'
            +     '<button type="button" class="btn btn-sm btn-success cr-guardar"><i class="fa fa-save"></i> Guardar y elegir</button>'
            +     '<button type="button" class="btn btn-sm btn-link cr-cancelar">Cancelar</button>'
            +   '</div>'
            +   '<div class="cr-nota">Queda registrada en Programas › Cuentas. Lo que ya llenó no se pierde.</div>'
            + '</div>'
        );

        const $form = $caja.find('.cr-form');
        const $numero = $caja.find('.cr-numero');
        const $banco = $caja.find('.cr-banco');
        const $mensaje = $caja.find('.cr-mensaje');
        const $guardar = $caja.find('.cr-guardar');

        function mensaje(texto, tipoMsj) {
            $mensaje.prop('hidden', !texto).attr('class', 'cr-mensaje cr-' + (tipoMsj || 'error')).text(texto || '');
        }

        $caja.find('.cr-abrir').on('click', function() {
            $form.prop('hidden', false);
            mensaje('');
            $numero.trigger('focus');
        });
        $caja.find('.cr-cancelar').on('click', function() {
            $form.prop('hidden', true);
            $numero.val('');
            $banco.val('');
        });

        // Enter dentro del mini formulario guarda la cuenta; nunca envía el formulario de la página
        $form.on('keydown', 'input', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $guardar.trigger('click');
            }
        });

        $guardar.on('click', function() {
            const numero = $.trim($numero.val()).toUpperCase();
            const banco = $.trim($banco.val());
            if (!numero) {
                mensaje('Escriba el número de cuenta.');
                $numero.trigger('focus');
                return;
            }
            $guardar.prop('disabled', true);
            mensaje('');
            $.ajax({
                url: 'ajax/programacuenta.ajax.php',
                type: 'POST',
                dataType: 'json',
                data: { accion: 'registrar', ProgramaID: programaID, TipoCuenta: tipo, NumeroCuenta: numero, Banco: banco }
            }).done(function(r) {
                if (!r || r.estado !== 'exitoso') {
                    $guardar.prop('disabled', false);
                    mensaje(r && r.estado === 'duplicado'
                        ? 'Esa cuenta ya está registrada en este programa. Si no aparece en la lista es porque está dada de baja: actívela en Programas › Cuentas.'
                        : ((r && r.mensaje) || 'No se pudo registrar la cuenta.'));
                    return;
                }
                recargar($select, programaID, tipo, numero).always(function() {
                    // preparar() se vuelve a llamar desde fuera o aquí: la caja muestra el éxito
                    preparar($select, programaID, tipo);
                    $select.next('.cr-caja').prepend(
                        '<div class="cr-mensaje cr-exito"><i class="fa fa-check-circle"></i> Cuenta ' + esc(numero) + ' registrada y elegida.</div>'
                    );
                });
            }).fail(function() {
                $guardar.prop('disabled', false);
                mensaje('No se pudo registrar la cuenta. Revise la conexión e intente de nuevo.');
            });
        });
    }

    return { preparar: preparar };
})();
