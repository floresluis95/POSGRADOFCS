<?php
    class FuncionesControladores
    {
        public function ValidarSessionControlador()
        {
            // Iniciar sesión si aún no se ha iniciado
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (!isset($_SESSION["Validar"]) || !$_SESSION["Validar"])
            {
                header('Location: ingreso');
                exit();
            }
        }
        
        public function SidebarControlador()
        {
            include 'vistas/componentes/modulos/sidebar.php';
        }
        
        public function NavBarControlador()
        {
            include 'vistas/componentes/modulos/nav-bar.php';
        }
        
        public function FooterControlador()
        {
            include 'vistas/componentes/modulos/footer.php';
        }

        public function FooterResumidoControlador()
        {
            include 'vistas/componentes/modulos/footer-resumido.php';
        }

        // Clase CSS de color institucional segun el Grado Academico
        // (Diplomado, Especialidad, Maestria, Doctorado - Facultad de Odontologia)
        public static function ClaseGrado($grado)
        {
            switch (strtoupper(trim((string)$grado))) {
                case 'DIPLOMADO':
                    return 'badge-grado-diplomado';
                case 'ESPECIALIDAD':
                    return 'badge-grado-especialidad';
                case 'MAESTRIA':
                case 'MAESTRÍA':
                    return 'badge-grado-maestria';
                case 'DOCTORADO':
                    return 'badge-grado-doctorado';
                default:
                    return 'badge-grado-generico';
            }
        }
    }