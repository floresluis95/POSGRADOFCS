<?php
$certTitulo = 'TRÁMITE A/B';
$certSubtitulo = 'FORMULARIO DE SOLICITUD PARA TRÁMITE UNIVERSITARIO (PRE-IMPRESO)';
include __DIR__ . '/modulos/certificado-encabezado.php';

require_once 'modelos/autoridad.modelo.php';

// "Dirigido a": las autoridades vigentes (pantalla Autoridades), con su cargo
$autoridadesTramite = AutoridadModelo::ListarModelo();
$destinatarios = [
    ['nombre' => AutoridadModelo::NombreParaDocumento($autoridadesTramite, 'DIRECTOR'), 'cargo' => 'DIRECTOR DE POSGRADO'],
    ['nombre' => AutoridadModelo::NombreParaDocumento($autoridadesTramite, 'COORDINADOR'), 'cargo' => mb_strtoupper(InstitucionModelo::CargoFirma(), 'UTF-8')],
];
$destinatarios = array_values(array_filter($destinatarios, function ($d) { return trim($d['nombre']) !== ''; }));

// Ajuste de impresión guardado (mm) de cada formato: lo que la impresora corre los datos
$mm = function ($clave) {
    return rtrim(rtrim(number_format((float)ConfiguracionModelo::Obtener($clave, '0'), 1, '.', ''), '0'), '.');
};
$ajustes = [
    '1' => ['x' => $mm('TRAMITE_AJUSTE_X'), 'y' => $mm('TRAMITE_AJUSTE_Y')],
    '2' => ['x' => $mm('TRAMITE2_AJUSTE_X'), 'y' => $mm('TRAMITE2_AJUSTE_Y')],
];
?>
                            <!-- La UTO tiene dos impresiones del formulario, con medidas distintas -->
                            <div class="card-certificado">
                                <h3><i class="fas fa-file-alt"></i> Formato del formulario pre-impreso</h3>
                                <div class="certificado-cuerpo">
                                    <div class="d-flex flex-wrap" style="gap:24px;">
                                        <label class="mb-0" style="cursor:pointer;"><input type="radio" name="formato-tramite" value="1" checked> <b>Formato 1</b> · impreso en celeste, líneas de puntos</label>
                                        <label class="mb-0" style="cursor:pointer;"><input type="radio" name="formato-tramite" value="2"> <b>Formato 2</b> · impreso en negro, líneas continuas</label>
                                    </div>
                                    <small class="cert-ayuda">Elija el que tiene en la impresora: cada uno tiene sus propias medidas y su propio ajuste de impresión.</small>
                                </div>
                            </div>

                            <details class="card-certificado" id="ajuste-impresion" style="padding:0;">
                                <summary style="cursor:pointer;padding:14px 20px;font-weight:600;">
                                    <i class="fas fa-ruler-combined"></i> Ajuste de impresión
                                    <small class="cert-ayuda" style="font-weight:400;">(si los datos no caen sobre las líneas del formulario)</small>
                                </summary>
                                <div class="certificado-cuerpo" style="padding:0 20px 18px;">
                                    <p class="cert-ayuda" style="margin-bottom:10px;">
                                        1) Imprima la <b>hoja de prueba</b> sobre un formulario (o en hoja blanca y encímela a uno a contraluz), con la escala en <b>Tamaño real / 100&nbsp;%</b>.
                                        2) Mida con una regla cuántos milímetros hay que mover los datos para que queden sobre las líneas.
                                        3) Escríbalos aquí y vuelva a imprimir la prueba hasta que calce; luego <b>guarde</b>. Se aplica a todos los trámites de ese formato
                                        (ahora: <b id="ajuste-formato-nombre">Formato 1</b>).
                                    </p>
                                    <div class="row">
                                        <div class="col-md-3 form-group">
                                            <label for="input-ajuste-x">Mover a la derecha (mm)</label>
                                            <input type="number" class="form-control" id="input-ajuste-x" step="0.5" min="-25" max="25" value="<?php echo htmlspecialchars($ajustes['1']['x']); ?>">
                                            <small class="cert-ayuda">Negativo = a la izquierda</small>
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label for="input-ajuste-y">Mover hacia abajo (mm)</label>
                                            <input type="number" class="form-control" id="input-ajuste-y" step="0.5" min="-25" max="25" value="<?php echo htmlspecialchars($ajustes['1']['y']); ?>">
                                            <small class="cert-ayuda">Negativo = hacia arriba</small>
                                        </div>
                                        <div class="col-md-6 form-group d-flex align-items-start" style="gap:8px;padding-top:28px;">
                                            <button type="button" class="btn btn-outline-secondary" id="btn-hoja-prueba"><i class="fas fa-print"></i> Imprimir hoja de prueba</button>
                                            <button type="button" class="btn btn-primary" id="btn-guardar-ajuste"><i class="fas fa-save"></i> Guardar ajuste</button>
                                        </div>
                                    </div>
                                </div>
                            </details>


                            <div class="card-certificado">
                                <h3><span class="paso-numero">1</span> Solicitante</h3>
                                <div class="certificado-cuerpo">
                                    <div id="buscador-certificado"></div>
                                    <select id="select-inscripcion" class="d-none" aria-hidden="true"><option value="">-- Seleccione --</option></select>
                                </div>
                            </div>

                            <div class="card-certificado" id="cert-vacio">
                                <div class="cert-vacio">
                                    <i class="fas fa-user-graduate"></i>
                                    <p>Busque al solicitante por su C.I. o nombre.</p>
                                </div>
                            </div>

                            <!-- #card-tramite agrupa los pasos 2 y 3 (se muestra al elegir al solicitante) -->
                            <div id="card-tramite" style="display:none;">
                                <!-- Paso 2: lo que se escribe en el formulario -->
                                <div class="card-certificado">
                                    <h3><span class="paso-numero">2</span> Datos del formulario</h3>
                                    <div class="certificado-cuerpo">
                                        <div class="row">
                                            <div class="col-md-4 form-group">
                                                <label for="input-fecha">Fecha<span class="cert-requerido">*</span></label>
                                                <input type="date" class="form-control" id="input-fecha">
                                            </div>
                                            <div class="col-md-12 form-group">
                                                <label for="input-dirigido">Dirigido a<span class="cert-requerido">*</span></label>
                                                <input type="text" class="form-control" id="input-dirigido" list="lista-destinatarios" maxlength="90" autocomplete="off">
                                                <datalist id="lista-destinatarios">
                                                    <?php foreach ($destinatarios as $d): ?>
                                                    <option value="<?php echo htmlspecialchars($d['nombre'], ENT_QUOTES); ?>" data-cargo="<?php echo htmlspecialchars($d['cargo'], ENT_QUOTES); ?>"></option>
                                                    <?php endforeach; ?>
                                                </datalist>
                                            </div>
                                            <div class="col-md-12 form-group">
                                                <label for="input-cargo">Cargo<span class="cert-requerido">*</span></label>
                                                <input type="text" class="form-control" id="input-cargo" maxlength="90">
                                            </div>
                                            <div class="col-md-12 form-group">
                                                <label for="input-nombre">Nombre y apellidos<span class="cert-requerido">*</span></label>
                                                <input type="text" class="form-control" id="input-nombre" maxlength="120">
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <label for="input-ci">Cédula de identidad</label>
                                                <input type="text" class="form-control" id="input-ci" maxlength="20">
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <label for="input-telefono">N° de teléfono</label>
                                                <input type="text" class="form-control" id="input-telefono" maxlength="20" inputmode="tel">
                                            </div>
                                            <div class="col-md-12 form-group mb-0">
                                                <label for="input-descripcion">Descripción de la solicitud<span class="cert-requerido">*</span></label>
                                                <textarea class="form-control" id="input-descripcion" rows="4" maxlength="520" spellcheck="true" lang="es"></textarea>
                                                <small class="cert-ayuda" id="ayuda-descripcion">El formulario tiene 4 líneas: si el texto es largo la letra se achica; la vista previa muestra cómo queda.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Paso 3: pago (valida la impresión; no se escribe en el formulario) -->
                                <div class="card-certificado">
                                    <h3><span class="paso-numero">3</span> Pago del trámite</h3>
                                    <div class="certificado-cuerpo">
                                        <div class="row">
                                            <div class="col-md-6 form-group mb-md-0">
                                                <label for="input-vaucher">N° de recibo o voucher<span class="cert-requerido">*</span></label>
                                                <input type="text" class="form-control" id="input-vaucher" data-voucher-partes="alfanumerico">
                                                <small class="cert-ayuda">Valida el pago; no se imprime en el formulario.</small>
                                            </div>
                                            <div class="col-md-6 form-group mb-0">
                                                <label for="input-monto">Monto pagado (Bs.)<span class="cert-requerido">*</span></label>
                                                <input type="number" class="form-control" id="input-monto" min="0" step="0.01" placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="certificado-acciones cert-acciones">
                                            <button type="button" class="btn-generar-certificado" id="btn-generar-certificado">
                                                <i class="fas fa-eye"></i> Revisar y generar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

<?php include __DIR__ . '/modulos/certificado-pie.php'; ?>

    <form id="form-generar-certificado" method="POST" action="tcpdf/pdf/certificado-tramite-pdf.php" target="_blank">
        <input type="hidden" name="estudianteID" id="hidden-estudianteID">
        <input type="hidden" name="programaID" id="hidden-programaID">
        <input type="hidden" name="fecha" id="hidden-fecha">
        <input type="hidden" name="dirigido" id="hidden-dirigido">
        <input type="hidden" name="cargo" id="hidden-cargo">
        <input type="hidden" name="nombre" id="hidden-nombre">
        <input type="hidden" name="ci" id="hidden-ci">
        <input type="hidden" name="telefono" id="hidden-telefono">
        <input type="hidden" name="descripcion" id="hidden-descripcion">
        <input type="hidden" name="vaucher" id="hidden-vaucher">
        <input type="hidden" name="monto" id="hidden-monto">
        <input type="hidden" name="formato" id="hidden-formato" value="1">
    </form>

    <form id="form-hoja-prueba" method="POST" action="tcpdf/pdf/certificado-tramite-pdf.php" target="_blank">
        <input type="hidden" name="prueba" value="1">
        <input type="hidden" name="formato" id="hidden-formato-prueba" value="1">
        <input type="hidden" name="ajusteX" id="hidden-ajuste-x">
        <input type="hidden" name="ajusteY" id="hidden-ajuste-y">
    </form>

    <script>
    $(document).ready(function() {
        // ===== Formato del formulario (1 celeste / 2 negro): se recuerda en este navegador =====
        var AJUSTES = <?php echo json_encode($ajustes); ?>;
        function formatoElegido() { return $('input[name="formato-tramite"]:checked').val() || '1'; }
        function mostrarFormato() {
            var f = formatoElegido();
            $('#input-ajuste-x').val(AJUSTES[f].x);
            $('#input-ajuste-y').val(AJUSTES[f].y);
            $('#ajuste-formato-nombre').text('Formato ' + f);
            $('#hidden-formato, #hidden-formato-prueba').val(f);
        }
        try {
            var guardado = localStorage.getItem('tramiteFormato');
            if (guardado === '1' || guardado === '2') $('input[name="formato-tramite"][value="' + guardado + '"]').prop('checked', true);
        } catch (e) {}
        mostrarFormato();
        $('input[name="formato-tramite"]').on('change', function() {
            try { localStorage.setItem('tramiteFormato', formatoElegido()); } catch (e) {}
            mostrarFormato();
        });

        // ===== Ajuste de impresión =====
        function ajustesValidos() {
            var x = String($('#input-ajuste-x').val() || '0').replace(',', '.');
            var y = String($('#input-ajuste-y').val() || '0').replace(',', '.');
            if (isNaN(x) || isNaN(y) || Math.abs(x) > 25 || Math.abs(y) > 25) {
                Certificado.aviso('warning', 'Ajuste no válido', 'Escriba milímetros de -25 a 25');
                return null;
            }
            return { x: x, y: y };
        }
        $('#btn-hoja-prueba').on('click', function() {
            var a = ajustesValidos();
            if (!a) return;
            $('#hidden-ajuste-x').val(a.x);
            $('#hidden-ajuste-y').val(a.y);
            $('#form-hoja-prueba')[0].submit();
        });
        $('#btn-guardar-ajuste').on('click', function() {
            var a = ajustesValidos();
            if (!a) return;
            var f = formatoElegido();
            $.post('ajax/certificado.ajax.php', { accion: 'guardarAjusteTramite', formato: f, ajusteX: a.x, ajusteY: a.y }, function(r) {
                if (r && r.success) AJUSTES[f] = { x: a.x, y: a.y };
                Certificado.aviso(r && r.success ? 'success' : 'error', r && r.success ? 'Guardado' : 'No se guardó', (r && r.mensaje) || '');
            }, 'json').fail(function() { Certificado.aviso('error', 'No se guardó', 'No se pudo conectar con el servidor'); });
        });

        var DESTINATARIOS = <?php echo json_encode($destinatarios, JSON_UNESCAPED_UNICODE); ?>;
        $('#input-fecha').val(new Date().toLocaleDateString('en-CA'));
        if (DESTINATARIOS.length) {
            $('#input-dirigido').val(DESTINATARIOS[0].nombre);
            $('#input-cargo').val(DESTINATARIOS[0].cargo);
        }

        // Al elegir una autoridad de la lista se pone su cargo
        $('#input-dirigido').on('input change', function() {
            var valor = $(this).val().trim().toLocaleUpperCase('es');
            DESTINATARIOS.forEach(function(d) {
                if (d.nombre.toLocaleUpperCase('es') === valor) $('#input-cargo').val(d.cargo);
            });
        });

        // Cargo y nombre en MAYÚSCULAS, como se imprimen
        $('#input-cargo, #input-nombre, #input-ci').on('input', function() {
            var inicio = this.selectionStart, fin = this.selectionEnd;
            var mayus = this.value.toLocaleUpperCase('es');
            if (mayus !== this.value) { this.value = mayus; this.setSelectionRange(inicio, fin); }
        });

        $('#input-descripcion').on('input', function() {
            var n = $(this).val().length;
            $('#ayuda-descripcion').text(n + ' de 520 caracteres. El formulario tiene 4 líneas: si el texto es largo la letra se achica; la vista previa muestra cómo queda.');
        });

        Certificado.buscador({
            contenedor: '#buscador-certificado',
            tipo: 'TRAMITE',
            precios: window.PRECIOS_CERTIFICADOS,
            opcion: function(insc) {
                var ci = String(insc.Ci || '') + (insc.Complemento ? '-' + insc.Complemento : '') + (insc.Exp ? ' ' + insc.Exp : '');
                var celular = parseInt(insc.Celular, 10) > 0 ? String(insc.Celular) : '';
                return $('<option></option>')
                    .val(insc.EstudianteID)
                    .attr('data-programa-id', insc.ProgramaID)
                    // "NOMBRE Y APELLIDOS": primero el nombre
                    .attr('data-nombre', [insc.Nombre, insc.Apaterno, insc.Amaterno].filter(Boolean).join(' ').toLocaleUpperCase('es'))
                    .attr('data-ci', ci.toLocaleUpperCase('es'))
                    .attr('data-telefono', celular);
            },
            alLimpiar: function() { $('#card-tramite').hide(); }
        });

        $('#select-inscripcion').on('change', function() {
            var $opt = $(this).find('option:selected');
            if (!$(this).val()) {
                $('#card-tramite').hide();
                return;
            }
            $('#input-nombre').val($opt.data('nombre'));
            $('#input-ci').val($opt.data('ci'));
            $('#input-telefono').val($opt.data('telefono') || '');
            $('#card-tramite').show();
        });

        $('#btn-generar-certificado').on('click', function() {
            var $opt = $('#select-inscripcion').find('option:selected');
            var limpio = function(sel) { return String($(sel).val() || '').trim().replace(/\s+/g, ' '); };
            var fecha = $('#input-fecha').val();
            var dirigido = limpio('#input-dirigido');
            var cargo = limpio('#input-cargo');
            var nombre = limpio('#input-nombre');
            var descripcion = limpio('#input-descripcion');
            var vaucher = limpio('#input-vaucher');
            var monto = limpio('#input-monto');

            if (!$('#select-inscripcion').val()) { Certificado.aviso('warning', 'Falta el solicitante', 'Busque y elija al solicitante'); return; }
            if (!fecha) { Certificado.aviso('warning', 'Falta la fecha', 'Seleccione la fecha del formulario'); return; }
            if (!dirigido || !cargo) { Certificado.aviso('warning', 'Falta el destinatario', 'Escriba a quién va dirigido y su cargo'); return; }
            if (!nombre) { Certificado.aviso('warning', 'Falta el nombre', 'Escriba el nombre y apellidos del solicitante'); return; }
            if (!descripcion) { Certificado.aviso('warning', 'Falta la descripción', 'Escriba la descripción de la solicitud'); return; }
            if (!vaucher) { Certificado.aviso('warning', 'Falta el N° de recibo', 'El trámite tiene costo: ingrese el número de recibo o voucher'); return; }
            if (monto === '' || isNaN(monto) || parseFloat(monto) < 0) { Certificado.aviso('warning', 'Falta el monto', 'Ingrese el monto pagado por el trámite (Bs.)'); return; }

            $('#hidden-estudianteID').val($('#select-inscripcion').val());
            $('#hidden-programaID').val($opt.data('programa-id'));
            $('#hidden-fecha').val(fecha);
            $('#hidden-dirigido').val(dirigido);
            $('#hidden-cargo').val(cargo);
            $('#hidden-nombre').val(nombre);
            $('#hidden-ci').val(limpio('#input-ci'));
            $('#hidden-telefono').val(limpio('#input-telefono'));
            $('#hidden-descripcion').val(descripcion);
            $('#hidden-vaucher').val(vaucher);
            $('#hidden-monto').val(monto);

            Certificado.confirmar({
                titulo: 'Trámite A/B',
                form: '#form-generar-certificado',
                datos: [
                    ['Formulario', 'Formato ' + formatoElegido() + (formatoElegido() === '2' ? ' (negro, líneas continuas)' : ' (celeste, líneas de puntos)')],
                    ['Fecha', fecha.split('-').reverse().join('/')],
                    ['Dirigido a', dirigido],
                    ['Cargo', cargo],
                    ['Nombre y apellidos', nombre],
                    ['Cédula de identidad', limpio('#input-ci')],
                    ['N° de teléfono', limpio('#input-telefono')],
                    ['Descripción', descripcion],
                    ['N° de recibo o voucher (no se imprime)', vaucher],
                    ['Monto pagado', Certificado.formatoBs(monto)]
                ],
                extra: '<p class="cert-ayuda" style="margin:8px 0 0"><i class="fas fa-info-circle"></i> La vista previa muestra el formulario de fondo para comprobar que nada se salga de las líneas; al imprimir salen solo los datos (ponga el formulario pre-impreso en la impresora, hoja carta).</p>',
                ortografia: [{ texto: descripcion }]
            });
        });
    });
    </script>
</body>
