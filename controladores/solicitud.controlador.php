<?php
    class Solicitudcontroladores
    {
        public function RegistrarVehiculo()
        {//registarvehiculo
            if (isset($_POST["nroplaca"]))
            {
                $DatosVehiculo = array(
                    "nroplaca" => strtoupper($_POST['nroplaca']),
                    "marca" => $_POST['lista1'],
                    "tipo" => $_POST['lista2'],
                    "clase" => $_POST['clase'],
                    "modelo" => $_POST['modelo'],
                    "tipomotor" => $_POST['tipomotor'],
                    "cilindrada" => $_POST['cilindrada'],
                    "tipotransporte" => $_POST['tipotransporte'],    
                  
            ); 
            $nroplaca =$_POST['nroplaca'];
            $existe = VehiculoModelos::busv($nroplaca);
            if($existe==true)
            {
                echo'
                        <script src="vistas/recursos/sweetalert.min.js"></script>
                        <script>
                        swal("el vehiculo ya se especifico en otra solicitud", "", "error")
                        .then(function () {
                            location.href="solicitud";
                          })
                          ;
                         </script>';
            }
            else
            {
                $TraerVehiculo = VehiculoModelos::InsertarVehiculoModelo($DatosVehiculo);
                if ($TraerVehiculo == 'exitoso')
                {
                     if (isset($_POST["nroplaca"]))
                        {
                            $Idvehiculo = HeredadoModelos::UltimoIdModelo('idvehiculo', 'vehiculo');

                            $DatosVehiculo = array(
                            "Idpropietario" => $_POST['idcliente'],
                            "idvehiculo" => $Idvehiculo,
                            "estado" => 1         
                            );
                            $propvehiculo = PropvehiculoModelos::InsertarPropVehiculoModelo($DatosVehiculo);
                            if ($propvehiculo == 'exitoso')
                            {
                                $IdPropVehiculo = HeredadoModelos::UltimoIdModelo('idpropvehiculo', 'propvehiculo');

                                    $solicitud = SolicitudModelos::InsertarSolicitudModelo($IdPropVehiculo,$_SESSION["IdPersonal"]);
                                    if ($solicitud == 'exitoso')
                                    {
                                        $DatosVehiculo = array(
                                            "nroplaca" => $_POST['nroplaca'],
                                            "marca" => $_POST['lista1'],
                                            "tipo" => $_POST['lista2'],
                                            "clase" => $_POST['clase'],
                                            "modelo" => $_POST['modelo'],
                                            "tipomotor" => $_POST['tipomotor'],
                                            "cilindrada" => $_POST['cilindrada'],
                                            "tipotransporte" => $_POST['tipotransporte'],    
                                            
                                    ); 
                                            $nroplaca= $_POST['nroplaca'];
                                            $marca= $_POST['lista1'];
                                            $tipo= $_POST['lista2'];
                                            $modelo= $_POST['modelo'];
                                            $tipomotor= $_POST['tipomotor'];
                                            $cilindrada= $_POST['cilindrada'];
                                            $tipotransporte= $_POST['tipotransporte'];
                                        $IdSol = HeredadoModelos::UltimoIdModelo('codsolicitud', 'solicitud');
                                        echo"
                                            <script src='vistas/recursos/sweetalert.min.js'></script>
                                            <script>
                                            swal('EXITOSO!', 'SOLICITUD REGISTRADA', 'success')
                                            .then(function () {
                                                window.open('extensiones/tcpdf/pdf/pdfsolicitud.php?IdSolicitud=".$IdSol."&nroplaca=".$nroplaca."&marca=".$marca."&tipo=".$tipo."&modelo=".$modelo."&tipomotor=".$tipomotor."&cilindrada=".$cilindrada."$tipotransporte=".$tipotransporte."');
                                            })
                                            ;
                                            </script>";
                                    }else 
                                    {
                                        echo'
                                        <script src="vistas/recursos/sweetalert.min.js"></script>
                                        <script>
                                        swal("ERROR!", "NO SE REALIZO LA SOLICITUD", "error")
                                        .then(function () {
                                            location.href="solicitud";
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
                                    swal("ERROR!", "NO SE REALIZO LA SOLICITUD", "error")
                                    .then(function () {
                                        location.href="solicitud";
                                    })
                                    ;
                                    </script>';
                                } 
                        }
                }   

            }
            
            }
        }
    }
