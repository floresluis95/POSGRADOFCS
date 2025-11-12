<?php
    class Contratocontroladores
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
                                $Datoscontrato = array(
                                    "fechac" => null,
                                    "finicio" => null,
                                    "ffinal" => null,
                                    "estado" => 1,
                                    "kitp" => $_POST['seriekit'],
                                    "cilindrop" => $_POST['seriecilindro'],
                                    "tecnico" => $_POST['cilindrada'],
                                    "marcak" => $_POST['idmarca'],
                                    "marcac" => $_POST['idmarcac'],);
                                     $contrato = ContratodModelos::InsertarSolicitudModelo($IdPropVehiculo,$_SESSION["IdPersonal"],$Datoscontrato);

                                    if ($contrato == 'exitoso')
                                    {  
                                        echo'
                                        <script src="vistas/recursos/sweetalert.min.js"></script>
                                        <script>
                                        swal("succes!", "SE REALIZO EL CONTRATO", "succes")
                                        .then(function () {
                                            location.href="solicitud";
                                        })
                                        ;
                                        </script>';
                                                        
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
