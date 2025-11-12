<?php
    class EnlacesModelos
    {
        public function EnlacesModelo($enlace)
        {
            if($enlace == 'ingreso' ||
                $enlace == 'panel' ||
                $enlace == 'salir' ||
                $enlace == 'usuario' ||
                $enlace == 'kit' ||
                $enlace == 'cilindro'||
                $enlace == 'nentrega'||
                $enlace == 'nentregac'||
                $enlace == 'salir' ||
                $enlace == 'usuario'||
                $enlace == '404'||
                $enlace == 'tecnico'||
                $enlace == 'solicitud'||
                $enlace == 'notadetalle'||
                $enlace == 'notadetallec'||
                $enlace == 'personal'||
                $enlace == 'prtrabajo'||
                $enlace == 'sconversion'||
                $enlace == 'asignados'||
                $enlace == 'asignadostec'||
                $enlace == 'concluidos'||
                $enlace == 'consultas'||
                $enlace == 'ckit'||
                $enlace == 'ccilindro'||
                $enlace == 'prparticular'||
                $enlace == 'trabajos'||
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