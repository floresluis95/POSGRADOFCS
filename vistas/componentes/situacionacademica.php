<?php
$certTitulo = 'SITUACIÓN ACADÉMICA';
$certSubtitulo = 'MÓDULOS DEL PROGRAMA CON SU PUNTUACIÓN · HOJA CARTA';
include __DIR__ . '/modulos/certificado-encabezado.php';
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

                            <!-- Paso 2: vista previa del documento (se muestra al elegir al estudiante) -->
                            <div class="card-certificado" id="card-situacion" style="display:none;">
                                <h3><span class="paso-numero">2</span> Documento</h3>
                                <div class="certificado-cuerpo">
                                    <p class="cert-ayuda mt-0 mb-2">Todos los módulos del programa, con su puntuación y situación. No se registra en Registro de documentos.</p>
                                    <iframe id="situacion-vista" title="Vista previa de la situación académica"
                                            style="width:100%; height:70vh; min-height:420px; border:1px solid #e2e8f0; border-radius:8px; background:#f8fafc;"></iframe>
                                    <div class="certificado-acciones cert-acciones">
                                        <a class="btn-generar-certificado" id="btn-abrir-situacion" target="_blank" rel="noopener" href="#">
                                            <i class="fas fa-print"></i> Abrir para imprimir
                                        </a>
                                    </div>
                                </div>
                            </div>

<?php include __DIR__ . '/modulos/certificado-pie.php'; ?>

    <script>
    $(document).ready(function() {
        Certificado.buscador({
            contenedor: '#buscador-certificado',
            incluirAbandono: true,
            opcion: function(insc) {
                return $('<option></option>').val(insc.EstudianteID).attr('data-programa-id', insc.ProgramaID);
            }
        });

        // Al elegir al estudiante se carga su situación académica en ese programa
        $('#select-inscripcion').on('change', function() {
            if (!$(this).val()) {
                $('#card-situacion').hide();
                $('#situacion-vista').attr('src', 'about:blank');
                return;
            }
            var url = 'vistas/componentes/situacion-academica-pdf.php?estudianteID=' + encodeURIComponent($(this).val()) +
                '&programaID=' + encodeURIComponent($(this).find('option:selected').data('programa-id'));
            $('#situacion-vista').attr('src', url);
            $('#btn-abrir-situacion').attr('href', url);
            $('#card-situacion').show();
        });
    });
    </script>
</body>
