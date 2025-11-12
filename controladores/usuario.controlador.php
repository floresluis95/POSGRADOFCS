<?php
    class UsuarioControladores
    {
        public function CambiarEstadoModelos()
        {
            if (isset($_POST["idusr"]))
            {
                $idusr = $_POST["idusr"];
                $ed = UsuarioModelos::CambiarEstadoUsuario($idusr);
                if ($ed == 'exitoso') {
                    echo'
                    <script src="vistas/recursos/sweetalert.min.js"></script>
                    <script>
                    swal("EXITOSO!", "El usuario ya no tiene acceso al sistema", "success")
                    .then(function () {
                        location.href="usuario";
                      })
                      ;
                     </script>';  
                     
                }
                else {
                    echo'
                        <script src="vistas/recursos/sweetalert.min.js"></script>
                        <script>
                        swal("ERROR!", "No se realizo el cambio", "error")
                        .then(function () {
                            location.href="usuario";
                          })
                          ;
                        </script>';
                }
            
            }
        }
        public function CambiarEstadosControlador()
        {
            if (isset($_POST["idusrs"]))
            {
                $idusr = $_POST["idusrs"];
                $su = UsuarioModelos::CambiarEstadosUsuario($idusr);
                if ($su == 'exitoso') {
                    echo'
                    <script src="vistas/recursos/sweetalert.min.js"></script>
                    <script>
                    swal("EXITOSO!", "Permiso de usuario", "success")
                    .then(function () {
                        location.href="usuario";
                      })
                      ;
                     </script>';  
                }
                else {
                    echo'
                        <script src="vistas/recursos/sweetalert.min.js"></script>
                        <script>
                        swal("ERROR!", "No se realizo el cambio", "error")
                        .then(function () {
                            location.href="usuario";
                          })
                          ;
                         </script>';
                }
            
            }
            
        }

        public function ListaUsuariosControlador()
        {
            $ListaUsuarios = UsuarioModelos::ListaUsuariosModelo();
                // PREFORMATEAR CODIGO - VARDUMP
                /*echo '<pre>';
                    var_dump($ListaUsuarios);
                echo '</pre>';*/
            foreach ($ListaUsuarios as $key => $Usuario) 
            {
                $i++;
        
                echo '<tr>
                    <td>'.$i.'</td>
                    <td>'.$Usuario["CedulaIdentidad"].'</td>
                    <td>'.$Usuario['ApellidoPaterno'].' '.$Usuario["ApellidoMaterno"].' '.$Usuario["Nombres"].'</td>
                    <td>'.$Usuario['Direccion'].'</td>
                    <td>'.$Usuario['Celular'].' - '.$Usuario["Telefono"].'</td>       
                    <td>'.$Usuario['FechaIngreso'].'</td>
                    <td>'.$Usuario['Tipo'].'</td>             
                    <td>'.$Usuario['Estado'].'</td>            
                    <td>
                    <button 
                    data-toggle="modal"
                    data-target="#ModalEditarUsuario"
                    type="button"
                    class="btn btn-info btnEditarUsuario" 
                    CedulaIdentidad="'.$Usuario["CedulaIdentidad"].'"
                    ApellidoPaterno="'.$Usuario["ApellidoPaterno"].'"
                    ApellidoMaterno="'.$Usuario["ApellidoMaterno"].'"
                    Nombres="'.$Usuario["Nombres"].'"
                    Direccion="'.$Usuario["Direccion"].'"
                    Celular="'.$Usuario["Celular"].'"
                    Telefono="'.$Usuario["Telefono"].'">
                    <i class="fa fa-edit"></i></button>
                    </td>
                    <td>
                    <button data-toggle="modal" data-target="#modalu" type="button" idusr="'.$Usuario['CedulaIdentidad'].'" class="btn btn-danger btn-sm round  mr-1 mb-1 btnusr"><i class="fa fa-trash-alt"></i></button>           
                    </td>
                    <td>
                    <button data-toggle="modal" data-target="#modals" type="button" idusrs="'.$Usuario['CedulaIdentidad'].'" class="btn btn-success btn-sm round  mr-1 mb-1 btnsubir"><i class="fa fa-arrow-up"></i></button>           
                    </td>
                </tr>';
            }
        }

        public function InsertarUsuarioControlador()
        {
            if(isset($_POST['ci']))
            {    
           
                    // Capturar el IdPersonal Maximo de la tabla personal
                    $item = 'IdPersonal';
                    $tabla = 'personal';
                    $IdPersonal = HeredadoModelos::UltimoIdModelo($item, $tabla) + 1;
                    
                    // Enviar datos del Personal
                    $TablaPersonal = 'personal';

                    $DatosPersonal = array(
                        "IdPersonal" => $IdPersonal,
                        "CedulaIdentidad" => $_POST['ci'],
                        "ApellidoPaterno" => strtoupper($_POST['apaterno']),
                        "ApellidoMaterno" => strtoupper($_POST['amaterno']),
                        "Nombres" => strtoupper($_POST['nombres']),
                        "Prueba" => strtoupper($_POST['prueba']),
                        "Direccion" => strtoupper($_POST['direccion']),
                        "Celular" => $_POST['celular'],
                        "Telefono" => $_POST['telefono']
                    );
                
                    $InsertarPersonal = UsuarioModelos::InsertarPersonalModelo($TablaPersonal, $DatosPersonal);
                    
                    // La contraseña del usuario sera igual a su Cedula de Identidad
                    $encriptar = crypt($_POST['ci'], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');

                    // Enviar datos del Usuario
                    $TablaUsuario = 'usuario';

                    $DatosModelo = array(
                        
                        "IdPersonal" => $IdPersonal,
                        "Usuario" => $_POST['usuario'],
                        "Password" => $encriptar,
                        "Tipo" => $_POST['UICargo']
                    );
                 
                  
                    $InsertarUsuario = UsuarioModelos::InsertarUsuarioModelo($TablaUsuario, $DatosModelo);
                    

                    if ($InsertarPersonal == 'exitoso' && $InsertarUsuario == 'exitoso')
                     {
                        echo'
                        <script src="vistas/recursos/sweetalert.min.js"></script>
                        <script>
                        swal("EXITOSO!", "Registro correcto de usuario", "success")
                        .then(function () {
                            location.href="usuario";
                          })
                          ;
                         </script>';    
                    }
                    else
                    {
                        echo'
                        <script src="vistas/recursos/sweetalert.min.js"></script>
                        <script>
                        swal("ERROR!", "Registro incorrecto de usuario", "error")
                        .then(function () {
                            location.href="usuario";
                          })
                          ;
                         </script>';
                    }
                    
                }
            }
         /*public function EditarUsuarioControlador()
            {
                if(isset($_POST['editarapaterno']))
                {    
               
                        // Capturar el IdPersonal Maximo de la tabla personal
                    
                        
                        // Enviar datos del Personal
                       
    
                        $DatosPersonal = array(
                            
                            "ApellidoPaterno" => $_POST['editarapaterno'],
                            "ApellidoMaterno" => $_POST['editaramaterno'],
                            "Nombres" => $_POST['editarnombres'],
                            "Direccion" => $_POST['editardireccion'],
                            "Celular" => $_POST['editarcelular'],
                            "Telefono" => $_POST['editartelefono']
                        );
                    
                        $EditarPersonal = UsuarioModelos::EditarUsuaario($DatosPersonal);
                        
                        if ($EditarPersonal == 'exitoso')
                         {
                            echo'
                            <script src="vistas/recursos/sweetalert.min.js"></script>
                            <script>
                            swal("EXITOSO!", "Se edito el personal correctamente", "success")
                            .then(function () {
                                location.href="usuario";
                              })
                              ;
                             </script>';    
                        }
                        
                        {
                            echo'
                            <script src="vistas/recursos/sweetalert.min.js"></script>
                            <script>
                            swal("ERROR!", "No se edito al personal", "error")
                            .then(function () {
                                location.href="usuario";
                              })
                              ;
                             </script>';
                        }
                        
                    }
            }*/
        }
    