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
    }