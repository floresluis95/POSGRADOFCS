<?php
$certTitulo = 'CERTIFICADO DE REVISIÓN';
$certSubtitulo = 'FORMATO PRE-IMPRESO DE POSGRADO';
include __DIR__ . '/modulos/certificado-encabezado.php';
?>

                            <div class="card-certificado">
                                <h3><span class="paso-numero">1</span> Estudiante</h3>
                                <div class="certificado-cuerpo">
                                    <div id="buscador-certificado"></div>
                                    <select id="select-inscripcion" class="d-none" aria-hidden="true"><option value="">-- Seleccione --</option></select>
                                </div>
                            </div>

                            <div class="card-certificado" id="card-revision" style="display:none;">
                                <h3><span class="paso-numero">2</span> Datos del certificado</h3>
                                <div class="certificado-cuerpo">
                                    <div class="row">
                                        <div class="col-md-3 form-group">
                                            <label for="input-tratamiento">Tratamiento</label>
                                            <select class="form-control" id="input-tratamiento">
                                                <option value="la señora">la señora</option>
                                                <option value="el señor">el señor</option>
                                                <option value="la señorita">la señorita</option>
                                            </select>
                                        </div>
                                        <div class="col-md-9 form-group">
                                            <label>Programa</label>
                                            <div class="dato-solo-lectura" id="dato-curso"></div>
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label for="input-fecha">Fecha</label>
                                            <input type="date" class="form-control" id="input-fecha">
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label for="input-vaucher">N° de recibo o voucher <span class="text-muted font-weight-normal">(opcional)</span></label>
                                            <input type="text" class="form-control" id="input-vaucher" data-voucher-partes="alfanumerico">
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label for="input-monto">Monto pagado (Bs.) <span class="text-muted font-weight-normal">(opcional)</span></label>
                                            <input type="number" class="form-control" id="input-monto" min="0" step="0.01" placeholder="0.00">
                                        </div>
                                    </div>

                                    <div class="certificado-acciones">
                                        <button type="button" class="btn-generar-certificado" id="btn-generar-certificado">
                                            <i class="fas fa-eye"></i> Revisar y generar
                                        </button>
                                    </div>
                                </div>
                            </div>

<?php include __DIR__ . '/modulos/certificado-pie.php'; ?>

    <form id="form-generar-certificado" method="POST" action="tcpdf/pdf/certificado-revision-pdf.php" target="_blank">
        <input type="hidden" name="estudianteID" id="hidden-estudianteID">
        <input type="hidden" name="programaID" id="hidden-programaID">
        <input type="hidden" name="tratamiento" id="hidden-tratamiento">
        <input type="hidden" name="nombre" id="hidden-nombre">
        <input type="hidden" name="curso" id="hidden-curso">
        <input type="hidden" name="fecha" id="hidden-fecha">
        <input type="hidden" name="vaucher" id="hidden-vaucher">
        <input type="hidden" name="monto" id="hidden-monto">
    </form>

    <script>
    $(document).ready(function() {
        $('#input-fecha').val(new Date().toLocaleDateString('en-CA'));

        // En el certificado va primero el nombre: "ILSEN ARIANA ALA VALDIVIA"
        Certificado.buscador({
            contenedor: '#buscador-certificado',
            tipo: 'REVISION',
            precios: window.PRECIOS_CERTIFICADOS,
            opcion: function(insc) {
                return $('<option></option>')
                    .val(insc.EstudianteID)
                    .attr('data-programa-id', insc.ProgramaID)
                    .attr('data-nombre', [insc.Nombre, insc.Apaterno, insc.Amaterno].filter(Boolean).join(' ').toLocaleUpperCase('es'))
                    .attr('data-curso', Certificado.cursoRealizado(insc.GradoAcademico, insc.NombrePrograma));
            }
        });

        $('#select-inscripcion').on('change', function() {
            var $opt = $(this).find('option:selected');
            if (!$(this).val()) {
                $('#card-revision').hide();
                return;
            }
            $('#dato-curso').text($opt.data('curso'));
            $('#card-revision').show();
        });

        $('#btn-generar-certificado').on('click', function() {
            var $opt = $('#select-inscripcion').find('option:selected');
            var fecha = $('#input-fecha').val();
            var vaucher = $('#input-vaucher').val().trim();
            var monto = ($('#input-monto').val() || '').trim();

            if (!$opt.data('nombre')) {
                Certificado.aviso('warning', 'Falta el estudiante', 'Busque y elija un estudiante');
                return;
            }
            if (!fecha) {
                Certificado.aviso('warning', 'Falta la fecha', 'Seleccione la fecha del certificado');
                return;
            }
            if (monto !== '' && (isNaN(monto) || parseFloat(monto) < 0)) {
                Certificado.aviso('warning', 'Monto no válido', 'Revise el monto pagado (Bs.)');
                return;
            }

            $('#hidden-estudianteID').val($('#select-inscripcion').val());
            $('#hidden-programaID').val($opt.data('programa-id'));
            $('#hidden-tratamiento').val($('#input-tratamiento').val());
            $('#hidden-nombre').val($opt.data('nombre'));
            $('#hidden-curso').val($opt.data('curso'));
            $('#hidden-fecha').val(fecha);
            $('#hidden-vaucher').val(vaucher);
            $('#hidden-monto').val(monto);

            Certificado.confirmar({
                titulo: 'Certificado de Revisión',
                form: '#form-generar-certificado',
                datos: [
                    ['Tratamiento', $('#input-tratamiento').val()],
                    ['Estudiante', $opt.data('nombre')],
                    ['Programa', $opt.data('curso')],
                    ['Fecha', fecha.split('-').reverse().join('/')],
                    ['N° de recibo o voucher', vaucher],
                    ['Monto pagado', monto === '' ? '' : Certificado.formatoBs(monto)]
                ],
                ortografia: [{ texto: $opt.data('curso') }]
            });
        });
    });
    </script>
</body>
