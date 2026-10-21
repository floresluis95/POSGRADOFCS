<?php
$certTitulo = 'CERTIFICACIÓN DE MEDIO MAGNÉTICO';
$certSubtitulo = 'CARTA CON MEMBRETE PROPIO';
include __DIR__ . '/modulos/certificado-encabezado.php';

require_once 'modelos/autoridad.modelo.php';

// Firmas: los nombres vigentes de la pantalla "Autoridades" (se pueden ajustar aquí antes de imprimir)
$autoridadesCarta = AutoridadModelo::ListarModelo();
$coordinadorCarta = AutoridadModelo::NombreParaDocumento($autoridadesCarta, 'COORDINADOR');
$directorCarta = AutoridadModelo::NombreParaDocumento($autoridadesCarta, 'DIRECTOR');
?>

                            <div class="card-certificado">
                                <h3><span class="paso-numero">1</span> Estudiante</h3>
                                <div class="certificado-cuerpo">
                                    <div id="buscador-certificado"></div>
                                    <select id="select-inscripcion" class="d-none" aria-hidden="true"><option value="">-- Seleccione --</option></select>
                                </div>
                            </div>

                            <div class="card-certificado" id="cert-vacio">
                                <div class="cert-vacio">
                                    <i class="fas fa-user-graduate"></i>
                                    <p>Busque al estudiante por su C.I. o nombre.</p>
                                </div>
                            </div>

                            <!-- #card-carta agrupa los pasos 2 y 3 (se muestra al elegir al estudiante) -->
                            <div id="card-carta" style="display:none;">
                                <!-- Paso 2: lo que dice la carta -->
                                <div class="card-certificado">
                                    <h3><span class="paso-numero">2</span> Datos de la carta</h3>
                                    <div class="certificado-cuerpo">
                                        <div class="cert-fijos">
                                            <div><span>Curso realizado</span><div class="dato-solo-lectura" id="dato-curso"></div></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3 form-group">
                                                <label for="input-tratamiento">Tratamiento</label>
                                                <select class="form-control" id="input-tratamiento">
                                                    <option value="El Sr.">El Sr.</option>
                                                    <option value="La Sra.">La Sra.</option>
                                                    <option value="La Srta.">La Srta.</option>
                                                </select>
                                            </div>
                                            <div class="col-md-9 form-group">
                                                <label for="input-tipotrabajo">Trabajo entregado</label>
                                                <select class="form-control" id="input-tipotrabajo">
                                                    <option value="MEDIO FÍSICO Y DIGITAL DEL TRABAJO DE TESIS">Medio físico y digital del Trabajo de Tesis</option>
                                                    <option value="MEDIO FÍSICO Y DIGITAL DEL TRABAJO MONOGRÁFICO">Medio físico y digital del Trabajo Monográfico</option>
                                                    <option value="MEDIO FÍSICO Y DIGITAL DEL TRABAJO DE GRADO">Medio físico y digital del Trabajo de Grado</option>
                                                </select>
                                            </div>
                                            <div class="col-md-12 form-group">
                                                <label for="input-titulo">Título del trabajo<span class="cert-requerido">*</span></label>
                                                <textarea class="form-control" id="input-titulo" rows="3" spellcheck="true" lang="es"></textarea>
                                                <small class="cert-ayuda" id="ayuda-titulo"></small>
                                            </div>
                                            <div class="col-md-6 form-group mb-md-0">
                                                <label for="input-coordinador">Coordinador de Posgrado (firma)</label>
                                                <input type="text" class="form-control" id="input-coordinador" value="<?php echo htmlspecialchars($coordinadorCarta, ENT_QUOTES); ?>">
                                            </div>
                                            <div class="col-md-6 form-group mb-0">
                                                <label for="input-director">Director de Posgrado (firma)</label>
                                                <input type="text" class="form-control" id="input-director" value="<?php echo htmlspecialchars($directorCarta, ENT_QUOTES); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Paso 3: pago y fecha; aquí se genera -->
                                <div class="card-certificado">
                                    <h3><span class="paso-numero">3</span> Pago y fecha</h3>
                                    <div class="certificado-cuerpo">
                                        <div class="row">
                                            <div class="col-md-5 form-group mb-md-0">
                                                <label for="input-vaucher">N° de recibo o voucher<span class="cert-requerido">*</span></label>
                                                <input type="text" class="form-control" id="input-vaucher" data-voucher-partes="alfanumerico">
                                            </div>
                                            <div class="col-md-3 form-group mb-md-0">
                                                <label for="input-monto">Monto pagado (Bs.)<span class="cert-requerido">*</span></label>
                                                <input type="number" class="form-control" id="input-monto" min="0" step="0.01" placeholder="0.00">
                                            </div>
                                            <div class="col-md-4 form-group mb-0">
                                                <label for="input-fecha">Fecha de la carta<span class="cert-requerido">*</span></label>
                                                <input type="date" class="form-control" id="input-fecha">
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

    <form id="form-generar-certificado" method="POST" action="tcpdf/pdf/certificado-medio-magnetico-pdf.php" target="_blank">
        <input type="hidden" name="estudianteID" id="hidden-estudianteID">
        <input type="hidden" name="programaID" id="hidden-programaID">
        <input type="hidden" name="tratamiento" id="hidden-tratamiento">
        <input type="hidden" name="nombre" id="hidden-nombre">
        <input type="hidden" name="curso" id="hidden-curso">
        <input type="hidden" name="tipotrabajo" id="hidden-tipotrabajo">
        <input type="hidden" name="titulo" id="hidden-titulo">
        <input type="hidden" name="vaucher" id="hidden-vaucher">
        <input type="hidden" name="monto" id="hidden-monto">
        <input type="hidden" name="fecha" id="hidden-fecha">
        <input type="hidden" name="coordinador" id="hidden-coordinador">
        <input type="hidden" name="director" id="hidden-director">
    </form>

    <script>
    $(document).ready(function() {
        $('#input-fecha').val(new Date().toLocaleDateString('en-CA'));

        // El título va en MAYÚSCULAS (igual que en el trabajo de grado)
        $('#input-titulo').on('input', function() {
            var inicio = this.selectionStart, fin = this.selectionEnd;
            var mayus = this.value.toLocaleUpperCase('es');
            if (mayus !== this.value) { this.value = mayus; this.setSelectionRange(inicio, fin); }
        });

        Certificado.buscador({
            contenedor: '#buscador-certificado',
            tipo: 'MEDIO_MAGNETICO',
            precios: window.PRECIOS_CERTIFICADOS,
            opcion: function(insc) {
                return $('<option></option>')
                    .val(insc.EstudianteID)
                    .attr('data-programa-id', insc.ProgramaID)
                    .attr('data-nombre', [insc.Apaterno, insc.Amaterno, insc.Nombre].filter(Boolean).join(' ').toLocaleUpperCase('es'))
                    .attr('data-curso', Certificado.cursoRealizado(insc.GradoAcademico, insc.NombrePrograma));
            },
            alLimpiar: function() { $('#card-carta').hide(); }
        });

        $('#select-inscripcion').on('change', function() {
            var $opt = $(this).find('option:selected');
            var estudianteID = $(this).val();
            var programaID = $opt.data('programa-id');

            if (!estudianteID) {
                $('#card-carta').hide();
                return;
            }
            $('#dato-curso').text($opt.data('curso'));
            $('#card-carta').show();

            // El título se toma del trabajo de grado registrado (Aprobación Programa de Posgrado)
            $('#input-titulo').val('');
            $('#ayuda-titulo').text('');
            if (!programaID) return;
            $.post('ajax/certificado.ajax.php', { accion: 'obtenerTrabajoGrado', estudianteID: estudianteID, programaID: programaID }, function(data) {
                if (data && data.success && data.trabajo) {
                    $('#input-titulo').val(String(data.trabajo.TituloTrabajo || '').toLocaleUpperCase('es'));
                    var tipo = String(data.trabajo.TipoTrabajo || '').toLocaleUpperCase('es')
                        .replace('FISICO', 'FÍSICO').replace('MONOGRAFICO', 'MONOGRÁFICO');
                    if (tipo && $('#input-tipotrabajo option[value="' + tipo + '"]').length) {
                        $('#input-tipotrabajo').val(tipo);
                    }
                    $('#ayuda-titulo').text('Tomado del trabajo de grado registrado. Puede corregirlo aquí.');
                } else {
                    $('#ayuda-titulo').text('Sin trabajo de grado registrado: escriba el título completo.');
                }
            }, 'json');
        });

        $('#btn-generar-certificado').on('click', function() {
            var $opt = $('#select-inscripcion').find('option:selected');
            var nombre = $opt.data('nombre');
            var titulo = $('#input-titulo').val().trim().replace(/\s+/g, ' ');
            var vaucher = $('#input-vaucher').val().trim();
            var fecha = $('#input-fecha').val();
            var monto = ($('#input-monto').val() || '').trim();

            if (!nombre) {
                Certificado.aviso('warning', 'Falta el estudiante', 'Busque y elija un estudiante');
                return;
            }
            if (!titulo) {
                Certificado.aviso('warning', 'Falta el título', 'Ingrese el título del trabajo');
                return;
            }
            if (!vaucher) {
                Certificado.aviso('warning', 'Falta el N° de recibo', 'Ingrese el número de recibo o voucher');
                return;
            }
            if (monto === '' || isNaN(monto) || parseFloat(monto) < 0) {
                Certificado.aviso('warning', 'Falta el monto', 'Ingrese el monto pagado por el certificado (Bs.)');
                return;
            }
            if (!fecha) {
                Certificado.aviso('warning', 'Falta la fecha', 'Seleccione la fecha de la carta');
                return;
            }

            $('#hidden-estudianteID').val($('#select-inscripcion').val());
            $('#hidden-programaID').val($opt.data('programa-id'));
            $('#hidden-tratamiento').val($('#input-tratamiento').val());
            $('#hidden-nombre').val(nombre);
            $('#hidden-curso').val($opt.data('curso'));
            $('#hidden-tipotrabajo').val($('#input-tipotrabajo').val());
            $('#hidden-titulo').val(titulo);
            $('#hidden-vaucher').val(vaucher);
            $('#hidden-monto').val(monto);
            $('#hidden-fecha').val(fecha);
            $('#hidden-coordinador').val($('#input-coordinador').val().trim());
            $('#hidden-director').val($('#input-director').val().trim());

            Certificado.confirmar({
                titulo: 'Certificación de Medio Magnético',
                form: '#form-generar-certificado',
                datos: [
                    ['Estudiante', $('#input-tratamiento').val() + ' ' + nombre],
                    ['Curso realizado', $opt.data('curso')],
                    ['Trabajo entregado', $('#input-tipotrabajo').val()],
                    ['Título', titulo],
                    ['N° de recibo o voucher', vaucher],
                    ['Monto pagado', Certificado.formatoBs(monto)],
                    ['Fecha', fecha.split('-').reverse().join('/')],
                    ['Coordinador', $('#input-coordinador').val().trim()],
                    ['Director', $('#input-director').val().trim()]
                ],
                ortografia: [{ texto: $opt.data('curso') }, { texto: titulo }]
            });
        });
    });
    </script>
</body>
