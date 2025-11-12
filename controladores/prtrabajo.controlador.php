<?php
    class SolicitudesControladores
    {
        public function ListaSolicitudesControlador()
        
        {
                $TraerListaSolicitudes = SolicitudesModelos:: ListaSolicitudesModelo();
                foreach ($TraerListaSolicitudes as $key => $value) 
                {
                    $i++;
                    echo ' <option value="'.$value["codsolicitud"].'">solicitud  '.$value["codsolicitud"].'</option>';
                }      
        }
      

    }
    class PRTrabajoControladores
    {
        public function PRTrabajoControlador()
        {
            if (isset($_POST["idsolicitud"]))
            {
                $regtrabajo = array(
                    "codsolicitud" => $_POST['idsolicitud'],
                    "seriekit" => $_POST['seriekit'],
                    "seriecilindro" => $_POST['seriecilindro'],
                    "idtecnico" =>$_POST['idtecnico'],  
                    "fechatrabajo" => $_POST['fechatrabajo']
            ); 
         
           $Traerregistro = PRTrabajoModelos::PRTrartrabajoModelo($regtrabajo);
           
           $idk=$_POST['seriekit'];
           $Cambiarkit=CambiarestadoKitModelos::CambiarEstadoKit($idk);
          
           $idc=$_POST['seriecilindro'];
           $Cambiarcil=CambiarestadoCilindroModelos::CambiarEstadoCilindro($idc);

            $id=$_POST['idsolicitud'];
            $Cambiar=SolicitudModelos::CambiarEstadoSolicitudModelo($id);
           
            if ($Traerregistro == 'exitoso')
                {
                    if (isset($_POST["idsolicitud"]))
                    {
                        $tecnicosolicitud = array(
                            "codsolicitud" => $_POST['codsolicitud'],
                            "idusuario" => $_POST['idusuario'],  
                            ); 
                            $seriecilindro= $_POST['seriecilindro'];
                            $Traerpr = PRTrabajoModelos::PersonalSolicitudModelo($tecnicosolicitud);
                         
                      
                         if ($Traerpr == 'exitoso')
                            { 
                                echo'
                                <script src="vistas/recursos/sweetalert.min.js"></script>
                                <script>
                                swal("EXITOSO!", "Solicitud programada", "success")
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
                                    swal("ERROR!", "No se programo la solicitud", "error")
                                    .then(function () {
                                        location.href="prtrabajo";
                                    })
                                    ;
                                    </script>';
                            }
                     }
                    else
                    {
                        echo'
                        <script src="vistas/recursos/sweetalert.min.js"></script>
                        <script>
                        swal("ERROR!", "No se programo la solicitud", "error")
                        .then(function () {
                            location.href="prtrabajo";
                          })
                          ;
                         </script>';
                    }
                }
            }
        }
    }
