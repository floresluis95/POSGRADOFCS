<?php
    class EnlacesControladores
    {
        public function EnlacesControlador()
        {
            if (isset($_GET["action"]))
            {
                $enlace = $_GET["action"];
            }
            else
            {
                $enlace = "index";
            }

            // El Auxiliar no ve Usuarios, Certificados ni Reportes PDF (no están en su
            // menú); si escribe la URL a mano se le muestra el panel.
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $paginasNoPermitidasAuxiliar = [
                'usuario', 'reportes', 'registrodocumentos',
                'certificadocalificaciones', 'certificadohistorialacademico',
                'certificadoaprobacionmonografia', 'certificadomediomagnetico',
            ];
            if (($_SESSION['Tipo'] ?? '') === 'AUX' && in_array($enlace, $paginasNoPermitidasAuxiliar, true)) {
                $enlace = 'panel';
            }

            $respuesta = EnlacesModelos::EnlacesModelo($enlace);
            include $respuesta;

        } 
    }