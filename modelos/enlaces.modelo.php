<?php
    class EnlacesModelos
    {
        public static function EnlacesModelo($enlace)
        {
            if($enlace == 'ingreso' ||
                $enlace == 'panel' ||
                $enlace == 'salir' ||
                $enlace == 'usuario' ||
                $enlace == 'salir' ||
                $enlace == 'usuario'||
                $enlace == '404'||
                $enlace == 'estudiantes'||
                $enlace == 'programas'||
                $enlace == 'inscripcion'||
                $enlace == 'matriculas'||
                $enlace == 'modulos' ||
                $enlace == 'docentes'
                )
            {
                $ruta = "vistas/componentes/".$enlace.".php";

            }
            else if ($enlace == "index"){
                $ruta = "vistas/componentes/ingreso.php"; 
            }
            else
            {
                $ruta = "vistas/componentes/404.php";
            }

            return $ruta;
        }
    }