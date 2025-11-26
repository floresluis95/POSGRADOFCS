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

        public static function ListaUsuariosControlador()
        {
            $ListaUsuarios = UsuarioModelos::ListaUsuariosModelo();
                // PREFORMATEAR CODIGO - VARDUMP
                /*echo '<pre>';
                    var_dump($ListaUsuarios);
                echo '</pre>';*/
            foreach ($ListaUsuarios as $key => $Usuario) 
            {
                 
        
                echo '<tr>
                    
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
                        "Direccion" => strtoupper($_POST['direccion']),
                        "Celular" => $_POST['celular'],
                        "Telefono" => $_POST['telefono']
                    );
                
                    $InsertarPersonal = UsuarioModelos::InsertarPersonalModelo($TablaPersonal, $DatosPersonal);

                    // La contraseña del usuario sera igual a su Cedula de Identidad
                    // Usar password_hash con bcrypt (más seguro que crypt)
                    $encriptar = password_hash($_POST['ci'], PASSWORD_BCRYPT, ['cost' => 12]);

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
        public function EditarUsuarioControlador()
        {
            if(isset($_POST['editarci']))
            {
                $DatosPersonal = array(
                    "CedulaIdentidad" => $_POST['editarci'],
                    "ApellidoPaterno" => strtoupper($_POST['editarapaterno']),
                    "ApellidoMaterno" => strtoupper($_POST['editaramaterno']),
                    "Nombres" => strtoupper($_POST['editarnombres']),
                    "Direccion" => strtoupper($_POST['editardireccion']),
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
                else
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
        }

        // Métodos para gestión de estudiantes con usuarios
        public static function ListaEstudiantesSinUsuarioControlador()
        {
            $ListaEstudiantes = UsuarioModelos::ListaEstudiantesSinUsuarioModelo();

            if(count($ListaEstudiantes) == 0)
            {
                echo '<tr>
                    <td colspan="4" class="text-center py-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 48px;"></i>
                        <h5 class="mt-3">Todos los estudiantes tienen usuario asignado</h5>
                    </td>
                </tr>';
                return;
            }

            foreach ($ListaEstudiantes as $key => $estudiante)
            {
                $nombreCompleto = $estudiante['Apaterno'].' '.$estudiante['Amaterno'].' '.$estudiante['Nombre'];
                $ciCompleto = $estudiante['Ci'].($estudiante['Complemento'] ? '-'.$estudiante['Complemento'] : '').' '.$estudiante['Exp'];

                echo '<tr>
                    <td class="text-center"><strong>'.$ciCompleto.'</strong></td>
                    <td>'.$nombreCompleto.'</td>
                    <td>'.($estudiante['Correo'] ? $estudiante['Correo'] : '<span class="text-muted">No registrado</span>').'</td>
                    <td class="text-center">
                        <button
                            data-toggle="modal"
                            data-target="#ModalAsignarUsuario"
                            type="button"
                            class="btn btn-success btn-sm btnAsignarUsuario btn-asignar"
                            data-ci="'.$estudiante['Ci'].'"
                            data-ci-completo="'.$ciCompleto.'"
                            data-nombre-completo="'.$nombreCompleto.'"
                            data-nombre-pila="'.$estudiante['Nombre'].'"
                            data-correo="'.($estudiante['Correo'] ? $estudiante['Correo'] : '').'">
                            <i class="fas fa-user-plus"></i> Asignar
                        </button>
                    </td>
                </tr>';
            }
        }

        public function CrearUsuarioEstudianteControlador()
        {
            // Debug: Registrar todos los datos POST recibidos
            error_log("=== DEBUG CrearUsuarioEstudianteControlador ===");
            error_log("POST recibido: " . print_r($_POST, true));

            if(isset($_POST['estudiante_ci']))
            {
                error_log("Procesando estudiante CI: " . $_POST['estudiante_ci']);

                // Verificar si el estudiante ya tiene usuario
                $verificar = UsuarioModelos::VerificarUsuarioEstudianteModelo($_POST['estudiante_ci']);
                error_log("Verificación de usuario existente: " . ($verificar ? 'YA EXISTE' : 'NO EXISTE'));

                if($verificar)
                {
                    echo'
                    <script src="vistas/recursos/sweetalert.min.js"></script>
                    <script>
                    swal("ADVERTENCIA!", "Este estudiante ya tiene un usuario asignado", "warning")
                    .then(function () {
                        location.href="estudianteusr";
                      })
                      ;
                     </script>';
                    return;
                }

                // Usuario será el número de carnet (CI)
                $usuario = $_POST['estudiante_ci'];

                // Contraseña será: primera letra del nombre + número de carnet
                $nombre = isset($_POST['estudiante_nombre']) ? $_POST['estudiante_nombre'] : '';
                error_log("Nombre recibido: " . $nombre);

                if(empty($nombre)) {
                    error_log("ERROR: Nombre del estudiante vacío");
                    echo'
                    <script src="vistas/recursos/sweetalert.min.js"></script>
                    <script>
                    swal("ERROR!", "Falta el nombre del estudiante", "error")
                    .then(function () {
                        location.href="estudianteusr";
                      });
                     </script>';
                    return;
                }

                $primeraLetra = strtoupper(substr($nombre, 0, 1));
                $passwordTexto = $primeraLetra . $_POST['estudiante_ci'];
                $password = password_hash($passwordTexto, PASSWORD_BCRYPT, ['cost' => 12]);

                error_log("Credenciales generadas - Usuario: " . $usuario . ", Password texto: " . $passwordTexto);

                $DatosModelo = array(
                    "IdPersonal" => $_POST['estudiante_ci'],
                    "Usuario" => $usuario,
                    "Password" => $password,
                    "Tipo" => "EST"
                );

                error_log("Datos a insertar: " . print_r($DatosModelo, true));

                $resultado = UsuarioModelos::CrearUsuarioEstudianteModelo($DatosModelo);
                error_log("Resultado del modelo: " . $resultado);

                if ($resultado == 'exitoso')
                {
                    error_log("Usuario creado exitosamente");
                    echo'
                    <script src="vistas/recursos/sweetalert.min.js"></script>
                    <script>
                    swal({
                        title: "EXITOSO!",
                        text: "Usuario: '.$usuario.'\\nContraseña: '.$passwordTexto.'",
                        icon: "success",
                        button: "Aceptar"
                    })
                    .then(function () {
                        location.href="estudianteusr";
                      });
                     </script>';
                }
                else
                {
                    error_log("ERROR al crear usuario: " . $resultado);
                    echo'
                    <script src="vistas/recursos/sweetalert.min.js"></script>
                    <script>
                    swal("ERROR!", "No se pudo crear el usuario: '.$resultado.'", "error")
                    .then(function () {
                        location.href="estudianteusr";
                      })
                      ;
                     </script>';
                }
            } else {
                error_log("No se recibió estudiante_ci en POST");
            }
        }
        }
