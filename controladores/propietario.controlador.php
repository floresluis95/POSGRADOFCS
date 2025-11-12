<?php
    class PropietarioControladores
    {

        public function RegistrarPropietarioControlador()
        {
            if (isset($_POST["ci"]))
            {
                $DatosPropietario = array(
                    "ci" => $_POST['ci'].' '.$_POST['exp'],
                    "nombre" => strtoupper($_POST['nombre']),
                    "paterno" =>strtoupper( $_POST['paterno']),
                    "materno" => strtoupper($_POST['materno']),
                    "telefono" => $_POST['telefono'],
                    "estado" => 1
                );
                
                $ci=$_POST['ci'];
                $existe=PropietarioModelos::buscarcliente($ci);
                if($existe==true)
                {
                    echo'
                            <script src="vistas/recursos/sweetalert.min.js"></script>
                            <script>
                            swal("Ya se registro al propietario", "", "error")  
                             </script>';
                }
                else
                {
                    $Propietario = PropietarioModelos::RegistarPropietarioModelo($DatosPropietario);
                    if ($Propietario == 'exitoso'){
                    
                            echo'
                            <script src="vistas/recursos/sweetalert.min.js"></script>
                            <script>
                            swal("EXITOSO!", "Registro correcto de propietario", "success")
                            .then(function () {
                                location.href="solicitud";
                              })
                              ;
                             </script>';      
                    }
                    else
                    {
                         echo'error de registro';
                    }
                }
               
               
                
            }
        }

        public function ListaPropietarioControlador()
        {
                $TraerListaPropietario = PropietarioModelos:: ListaPropietarioModelo();
                foreach ($TraerListaPropietario as $key => $value) 
                {
                    $i++;
                    echo ' <option value="'.$value["idpropietaro"].'">'.$value["ci"].' - '.$value["nombre"].' '.$value["paterno"].' '.$value["materno"].'</option>';
                }      
        }
        
    }