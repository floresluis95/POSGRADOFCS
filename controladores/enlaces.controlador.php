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

            $respuesta = EnlacesModelos::EnlacesModelo($enlace);
            include $respuesta;

        } 
    }