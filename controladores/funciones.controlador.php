<?php
    class FuncionesControladores
    {
        public function ValidarSessionControlador()
        {
            session_start();
            if (!$_SESSION["Validar"])
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