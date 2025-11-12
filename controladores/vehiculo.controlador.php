<?php
    class VehiculoControladores
    {
        

        public function BuscarVehiculoControlador()
        {
            $BuscarVehiculo = VehiculoModelos::BuscarVehiculoModelo($busplaca);
                // PREFORMATEAR CODIGO - VARDUMP
                /*echo '<pre>';
                    var_dump($ListVehiculo);
                echo '</pre>';*/
            foreach ($BuscarVehiculo as $key => $Vehiculo) 
            {
                $i++;
                echo '<tr>
                    <td>'.$i.'</td>
                    <td>'.$Vehiculo["CedulaIdentidad"].'</td>
                    <td>'.$Vehiculo['ApellidoPaterno'].' '.$Vehiculo["ApellidoMaterno"].' '.$Vehiculo["Nombres"].'</td>
                    <td>'.$Vehiculo['Direccion'].'</td>
                    <td>'.$Vehiculo['Celular'].' - '.$Vehiculo["Telefono"].'</td>       
                    <td>'.$Vehiculo['FechaIngreso'].'</td>
                    <td>'.$Vehiculo['Estado'].'</td>
                    <td>'.$Vehiculo['tipo'].'</td>
                    <td><button data-toggle="modal" data-target="#ModalEditarUsuario" type="button" class="btn btn-info btnEditarUsuario" idUsuario="'.$Vehiculo["idUsuario"].'">
                    <i class="fa fa-edit"></i></button>
                    <button data-toggle="modal" data-target="#Modaleliminar" type="button" class="btn btn-success btn-sm round  mr-1 mb-1">
                    <i class="fa fa-trash-alt"></i></button>                   
                    </td>               
                </tr>';
            }
        }
        public function RegistrarVehiculoControlador()
        {
            if (isset($_POST["nroplaca"]))
            {
                $DatosVehiculo = array(
                    "nroplaca" => $_POST['nroplaca'],
                    "marca" => $_POST['marca'],
                    "tipo" => $_POST['tipo'],
                    "clase" => $_POST['clase'],
                    "modelo" => $_POST['modelo'],
                    "tipomotor" => $_POST['tipomotor'],
                    "cilindrada" => $_POST['cilindrada'],
                    "tipotransporte" => $_POST['tipotransporte'],         
                );
               
                $TraerVehiculo = VehiculoModelos::InsertarVehiculoModelo($DatosVehiculo);
                $item = 'idvehiculo';
                $tabla = 'vehiculo';
                $Idvehiculo = HeredadoModelos::UltimoIdModelo($item, $tabla) ;
                $soli='solicitud';
                $DatosSolicitud= array(
                "idusuario" =>  $_SESSION["IdPersonal"],
                "idpropietario" => 2,
                "idvehiculo" => $Idvehiculo   
            );
            $Solicitud = SolicitudModelos::InsertarSolicitudModelo($soli, $DatosSolicitud);
                if ($TraerVehiculo=="exitoso"){
                    echo'
                    <script src="vistas/recursos/sweetalert.min.js"></script>
                    <script>
                    swal("EXITOSO!", "Registro correcto de Vehiculo", "success")
                    .then(function () {
                        location.href="solicitud";
                      })
                      ;
                     </script>';
                }
                else
                {
                    echo ("error vehiculo correcto");
                 }
                
            }
        }
        public function RegistrarMarcaVControlador()
        {
            if (isset($_POST["descmarca"]))
                {
                $nmmarca = array(
                    "descmarca" => strtoupper($_POST['descmarca'])      
                );
                $nmkit = VehiculoModelos::InsertarMarcaModelo($nmmarca);
                if ($nmkit=="exitoso")
                {   
                       header( "Location:solicitud" ); 
                }
                else
                {
                    echo'
                        <script src="vistas/recursos/sweetalert.min.js"></script>
                        <script>
                        swal("ERROR!", "Registro incorrecto", "error")
                        .then(function () {
                            location.href="solicitud";
                          })
                          ;
                         </script>';
                }
            }

        }

    }