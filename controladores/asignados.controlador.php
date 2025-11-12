<?php
class ListaDeAsignacionControladores
{ 
    public function ListaDeAsignacionControladore()
    {
        $ListaAsignaciones = AsignadosModelos::ListadeAsignacionesModelo($CedulaIdentidad);
            // PREFORMATEAR CODIGO - VARDUMP
            /*echo '<pre>';
                var_dump($Listaasignacions);
            echo '</pre>';*/
        foreach ($ListaAsignaciones as $key => $asignacion) 
        {
            $i++;
    
            echo '<tr>
                <td>'.$asignacion['codsolicitud'].'</td>
                <td>'.$asignacion['fechasolicitud'].'</td>
                <td>'.$asignacion['nroplaca'].'</td>
                <td>'.$asignacion["seriekit"].'</td>
                <td>'.$asignacion["seriecilindro"].'</td>
                <td>'.$asignacion["fechatrabajo"].'</td>
                <td>'.$asignacion['Nombres'].' '.$asignacion["ApellidoPaterno"].'</td>  
                <td>'.$asignacion['estado'].'</td>  
                <td>          
                <button data-toggle="modal" data-target="#edittec" type="button" idusrs="'.$asignacion['codsolicitud'].'" class="btn btn-success btn-sm round  mr-1 mb-1 btntec"><i class="fa fa-arrow-up"></i></button>           
                </td>
            </tr>';
        }
    }
    public function Cambiartecnico()
    {

        if (isset($_POST["idtecnico"]))
            {
            $idtecnico= $_POST['idtecnico'];
            $idsolicitud= $_POST['idusrs'];
            $Traercambio = AsignadosModelos::Cambiartecnico($idtecnico,$idsolicitud);
            if ($Traercambio == 'exitoso')
            {echo'
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("EXITOSO!", "SE CAMBIO AL TECNICO", "success")
                .then(function () {
                    location.href="asignados";
                  })
                  ;
                 </script>';
               
            }
            else
            {
                echo'
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "NO SE CAMBIO AL TECNICO", "error")
                .then(function () {
                    location.href="asignados";
                  })
                  ;
                 </script>';
            }
    }
}
    
    public function ListaDeAsignacionTEcControladore()
    { $id= $_SESSION["IdPersonal"];
        $ListaAsignaciones = AsignadosModelos::ListadeAsignacionesTecModelo($id);

            // PREFORMATEAR CODIGO - VARDUMP
            /*echo '<pre>';
                var_dump($Listaasignacions);
            echo '</pre>';*/
        foreach ($ListaAsignaciones as $key => $asignacion) 
        {
            $i++;
    
            echo '<tr>
            <td>'.$asignacion['codsolicitud'].'</td>
                <td>'.$asignacion['fechasolicitud'].'</td>
                <td>'.$asignacion['nroplaca'].'</td>
                <td>'.$asignacion["fechatrabajo"].'</td>  
                <td>
                <button data-toggle="modal" data-target="#modalt" type="button" idtrabajo="'.$asignacion['codsolicitud'].'" class="btn btn-success btn-sm round  mr-1 mb-1 btncambiar"> echo             
                </td>
            </tr>';
        }
    }

    public function trabajosControlador()
    {
        //UPDATE `solicitud` SET `estado`= 'PROGRAMADO'
        if (isset($_POST["idt"]))
        {   date_default_timezone_set("America/La_Paz");
            $fecha=date('Y-m-d H:i:s');
            $concluido=$_POST["idt"];
            
            $c1 = ($_POST['c1'] == NULL) ? 'NO' : $_POST['c1'];
            $c2 = ($_POST['c2'] == NULL) ? 'NO' : $_POST['c2'];
            $c3 = ($_POST['c3'] == NULL) ? 'NO' : $_POST['c3'];
            $c4 = ($_POST['c4'] == NULL) ? 'NO' : $_POST['c4'];
            $c5 = ($_POST['c5'] == NULL) ? 'NO' : $_POST['c5'];

            $Datosdet= array(
                "dsolicitudt" => $_POST['idt'],
                "inyecctores" => $c1,
                "arranque" => $c2,
                "aceleracion" => $c3,
                "velocidad" => $c4,
                "elctrica" => $c5,
                "descripciond" => $_POST['descripciond'],
            ); 
          
            
            $Propietario = SolicitudModelos::CambiarEstadoSolicitudTModelo($concluido);
            $fechatrabajo = SolicitudModelos::CambiarFechaTModelo($concluido , $fecha);
            $Traerdetalle = GuardarDetalleSolicitudModelos::GuardardetalleModelo($Datosdet);
          
            if ($Traerdetalle=='exitoso'){
                $Propietario = SolicitudModelos::CambiarEstadoSolicitudTModelo($concluido);
                $fechatrabajo = SolicitudModelos::CambiarFechaTModelo($concluido , $fecha);
                    echo'
                    <script src="vistas/recursos/sweetalert.min.js"></script>
                    <script>
                    swal("EXITOSO!", "TRABAJO CONCLUIDO", "success")
                    .then(function () {
                        location.href="asignadostec";
                      })
                      ;
                     </script>';               
            }
            else
            {
                echo'
                <script src="vistas/recursos/sweetalert.min.js"></script>

                <script>
                swal("DEBE REALIZAR LAS PRUEBAS TECNICAS", "", "error")
                .then(function () {
                    location.href="asignadostec";
                  })
                  ;
                 </script>';  
            }
            
        }
    }
    
}
class GuardarDetalleSolicitudControladores
{
    public function guardardetalletrabajoControlador()
    {
            if (isset($_POST["idusrs"]))
            {
                $Datosdet= array(
                    "dsolicitudt" => $_POST['idusrs'],
                    "inyecctores" => $_POST['c1'],
                    "arranque" => $_POST['c2'],
                    "aceleracion" => $_POST['c3'],
                    "velocidad" => $_POST['c4'],
                    "elctrica" => $_POST['c5'],
                    "descripcion" => $_POST['descripcion']
            ); 
           
            $Traerdetalle = GuardarDetalleSolicitudModelos::GuardardetalleModelo($Datosdet);
          
            if ($Traerdetalle == 'exitoso')
                {
                    echo'
                    <script src="vistas/recursos/sweetalert.min.js"></script>
                    <script>
                    swal("TRABAJO CONCLUIDO!", "", "success")
                    .then(function () {
                        location.href="asignadostec";
                      })
                      ;
                     </script>';     
                          
                }
                else
                {
                    echo'
                    <script src="vistas/recursos/sweetalert.min.js"></script>
                    <script>
                    swal("DEBE REALIZAR LAS PRUEBAS TECNICAS", "", "error")
                    .then(function () {
                        location.href="asignadostec";
                      })
                      ;
                     </script>';  
                  
                }
            }


    }
}
