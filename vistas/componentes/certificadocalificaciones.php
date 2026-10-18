<?php
$certTitulo = 'CERTIFICADO DE CALIFICACIONES';
$certSubtitulo = 'FORMATO PRE-IMPRESO DE POSGRADO CENTRAL';
include __DIR__ . '/modulos/certificado-encabezado.php';
?>

                            <div class="card-certificado">
                                <h3><span class="paso-numero">1</span> Estudiante</h3>
                                <div class="certificado-cuerpo">
                                    <div id="buscador-certificado"></div>
                                    <select id="select-inscripcion" class="d-none" aria-hidden="true"><option value="">-- Seleccione --</option></select>
                                </div>
                            </div>

                            <div class="card-certificado" id="bloque-datos" style="display:none;">
                                <h3><span class="paso-numero">2</span> Datos del certificado</h3>
                                <div class="certificado-cuerpo">
                                    <div class="row">
                                        <div class="col-md-8 form-group">
                                            <label>Programa</label>
                                            <div class="dato-solo-lectura" id="dato-programa"></div>
                                        </div>
                                        <div class="col-md-2 col-6 form-group">
                                            <label>Nivel</label>
                                            <div class="dato-solo-lectura" id="dato-nivel"></div>
                                        </div>
                                        <div class="col-md-2 col-6 form-group">
                                            <label for="input-anio">Año</label>
                                            <input type="text" class="form-control" id="input-anio" maxlength="20" placeholder="2024 - 2025" title="Años en que se cursó el programa; puede corregirlo">
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label for="input-vaucher">N° de voucher</label>
                                            <input type="text" class="form-control" id="input-vaucher" data-voucher-partes="alfanumerico">
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label for="input-monto">Monto pagado (Bs.)</label>
                                            <input type="number" class="form-control" id="input-monto" min="0" step="0.01" placeholder="0.00">
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label for="input-fecha">Fecha del certificado</label>
                                            <input type="date" class="form-control" id="input-fecha">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-certificado" id="card-modulos" style="display:none;">
                                <h3><span class="paso-numero">3</span> Módulos y calificaciones</h3>
                                <div class="certificado-cuerpo">
                                    <div class="table-responsive">
                                        <table class="table tabla-certificado" id="tabla-modulos-certificado">
                                            <thead>
                                                <tr>
                                                    <th>Sigla</th>
                                                    <th>Módulo</th>
                                                    <th class="text-center">Nota</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                    <div id="alerta-sin-modulos" class="alert alert-warning" style="display:none;">
                                        <i class="fas fa-exclamation-triangle"></i> Sin calificaciones registradas en este programa.
                                    </div>
                                    <div class="certificado-acciones">
                                        <button type="button" class="btn-generar-certificado" id="btn-generar-certificado" disabled>
                                            <i class="fas fa-eye"></i> Revisar y generar
                                        </button>
                                    </div>
                                </div>
                            </div>

<?php include __DIR__ . '/modulos/certificado-pie.php'; ?>

    <form id="form-generar-certificado" method="POST" action="tcpdf/pdf/certificado-calificaciones-pdf.php" target="_blank">
        <input type="hidden" name="estudianteID" id="hidden-estudianteID">
        <input type="hidden" name="programaID" id="hidden-programaID">
        <input type="hidden" name="nombre" id="hidden-nombre">
        <input type="hidden" name="programa" id="hidden-programa">
        <input type="hidden" name="nivel" id="hidden-nivel">
        <input type="hidden" name="anio" id="hidden-anio">
        <input type="hidden" name="vaucher" id="hidden-vaucher">
        <input type="hidden" name="monto" id="hidden-monto">
        <input type="hidden" name="fecha" id="hidden-fecha">
    </form>

    <script>
    $(document).ready(function() {
        $('#input-fecha').val(new Date().toLocaleDateString('en-CA'));

        var modulosActuales = [];

        function rangoAnios(modulos) {
            var anios = [];
            modulos.forEach(function(m) {
                [m.FechaInicio, m.FechaFinal].forEach(function(f) {
                    var a = parseInt(String(f || '').substring(0, 4), 10);
                    if (a > 1900) anios.push(a);
                });
            });
            if (!anios.length) return '';
            var desde = Math.min.apply(null, anios), hasta = Math.max.apply(null, anios);
            return desde === hasta ? String(desde) : desde + ' - ' + hasta;
        }

        // En el formato pre-impreso el nombre va primero: "ANA MARÍA PÉREZ LÓPEZ"
        Certificado.buscador({
            contenedor: '#buscador-certificado',
            tipo: 'CALIFICACIONES',
            precios: window.PRECIOS_CERTIFICADOS,
            opcion: function(insc) {
                return $('<option></option>')
                    .val(insc.EstudianteID)
                    .attr('data-programa-id', insc.ProgramaID)
                    .attr('data-nombre', [insc.Nombre, insc.Apaterno, insc.Amaterno].filter(Boolean).join(' ').toLocaleUpperCase('es'))
                    .attr('data-programa', insc.NombrePrograma)
                    .attr('data-nivel', Certificado.textoGrado(insc.GradoAcademico))
                    .attr('data-anio', Certificado.anioPrograma(insc));
            }
        });

        $('#select-inscripcion').on('change', function() {
            var $opt = $(this).find('option:selected');
            var estudianteID = $(this).val();
            var programaID = $opt.data('programa-id');

            if (!estudianteID || !programaID) {
                $('#bloque-datos, #card-modulos').hide();
                return;
            }

            $('#dato-programa').text($opt.data('programa'));
            $('#dato-nivel').text($opt.data('nivel'));
            $('#input-anio').val($opt.data('anio'));
            $('#bloque-datos').show();

            $.ajax({
                url: 'ajax/certificado.ajax.php',
                method: 'POST',
                data: { accion: 'modulosCalificados', estudianteID: estudianteID, programaID: programaID },
                dataType: 'json',
                success: function(data) {
                    $('#card-modulos').show();
                    var $tbody = $('#tabla-modulos-certificado tbody').empty();
                    modulosActuales = (data.success && data.modulos) ? data.modulos : [];
                    $('#alerta-sin-modulos').toggle(!modulosActuales.length);
                    $('#btn-generar-certificado').prop('disabled', !modulosActuales.length);
                    // Año sugerido: del inicio del primer módulo al fin del último ("2024 - 2025")
                    var rango = rangoAnios(modulosActuales);
                    if (rango) $('#input-anio').val(rango);
                    modulosActuales.forEach(function(mod) {
                        $tbody.append(
                            '<tr>' +
                            '<td>' + $('<div>').text(mod.codigomodulo || '').html() + '</td>' +
                            '<td>' + $('<div>').text(mod.nombremodulo || '').html() + '</td>' +
                            '<td class="text-center">' + parseInt(mod.Nota, 10) + '</td>' +
                            '</tr>'
                        );
                    });
                },
                error: function() {
                    Certificado.aviso('error', 'Error', 'No se pudieron obtener las calificaciones');
                }
            });
        });

        $('#btn-generar-certificado').on('click', function() {
            var $opt = $('#select-inscripcion').find('option:selected');
            var vaucher = $('#input-vaucher').val().trim();
            var fecha = $('#input-fecha').val();
            var monto = ($('#input-monto').val() || '').trim();
            var anio = $('#input-anio').val().trim().replace(/\s*-\s*/g, ' - ');

            if (!anio) {
                Certificado.aviso('warning', 'Falta el año', 'Ingrese los años en que se cursó el programa (ej. 2024 - 2025)');
                return;
            }
            if (!vaucher) {
                Certificado.aviso('warning', 'Falta el N° de voucher', 'Ingrese el número de voucher del pago');
                return;
            }
            if (monto === '' || isNaN(monto) || parseFloat(monto) < 0) {
                Certificado.aviso('warning', 'Falta el monto', 'Ingrese el monto pagado por el certificado (Bs.)');
                return;
            }
            if (!fecha) {
                Certificado.aviso('warning', 'Falta la fecha', 'Seleccione la fecha del certificado');
                return;
            }
            if (!modulosActuales.length) {
                Certificado.aviso('warning', 'Sin calificaciones', 'El estudiante no tiene módulos calificados en este programa');
                return;
            }

            $('#hidden-estudianteID').val($('#select-inscripcion').val());
            $('#hidden-programaID').val($opt.data('programa-id'));
            $('#hidden-nombre').val($opt.data('nombre'));
            $('#hidden-programa').val($opt.data('programa'));
            $('#hidden-nivel').val($opt.data('nivel'));
            $('#hidden-anio').val(anio);
            $('#hidden-vaucher').val(vaucher);
            $('#hidden-monto').val(monto);
            $('#hidden-fecha').val(fecha);

            Certificado.confirmar({
                titulo: 'Certificado de Calificaciones',
                form: '#form-generar-certificado',
                datos: [
                    ['Estudiante', $opt.data('nombre')],
                    ['Programa', String($opt.data('programa')).toLocaleUpperCase('es')],
                    ['Nivel', $opt.data('nivel')],
                    ['Año', anio],
                    ['N° de voucher', vaucher],
                    ['Monto pagado', Certificado.formatoBs(monto)],
                    ['Fecha', fecha.split('-').reverse().join('/')],
                    ['Módulos', modulosActuales.length + (modulosActuales.length === 1 ? ' módulo' : ' módulos') +
                        (modulosActuales.length > 6 ? ' (' + Math.ceil(modulosActuales.length / 6) + ' hojas)' : '')]
                ],
                ortografia: [
                    { texto: String($opt.data('programa')).toLocaleUpperCase('es') }
                ].concat(modulosActuales.map(function(m) { return { texto: String(m.nombremodulo || '').toLocaleUpperCase('es') }; }))
            });
        });
    });
    </script>
</body>
