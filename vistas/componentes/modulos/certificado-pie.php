<?php
/**
 * Cierre común de las pantallas de certificados (ver certificado-encabezado.php):
 * pie, jQuery de la vista, componente certificados.js y los precios por grado.
 */
?>
                        </div>

                        <?php
                        $Footer = new FuncionesControladores();
                        $Footer->FooterResumidoControlador();
                        ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="vistas/recursos/assets/vendors/general/jquery/dist/jquery.js"></script>
    <script src="vistas/recursos/assets/js/scripts/certificados.js?v=<?php echo filemtime(__DIR__ . '/../../recursos/assets/js/scripts/certificados.js'); ?>"></script>
    <script>
    // Precios de los certificados por grado académico (pantalla Costos de certificados)
    window.PRECIOS_CERTIFICADOS = <?php echo json_encode(CertificadoModelo::PreciosCertificados()); ?>;
    </script>
