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
            $contador=0;
            foreach ($ListaUsuarios as $key => $Usuario)
            {
                $contador=$contador+1;

                // Definir badges de color según el tipo
                $tipoBadge = '';
                switch($Usuario['Tipo']) {
                    case 'ADM':
                        $tipoBadge = '<span class="badge badge-danger">ADMINISTRADOR</span>';
                        break;
                    case 'SEC':
                        $tipoBadge = '<span class="badge badge-info">SECRETARIA</span>';
                        break;
                    case 'DOC':
                        $tipoBadge = '<span class="badge badge-primary">DOCENTE</span>';
                        break;
                    case 'EST':
                        $tipoBadge = '<span class="badge badge-success">ESTUDIANTE</span>';
                        break;
                    default:
                        $tipoBadge = '<span class="badge badge-secondary">'.$Usuario['Tipo'].'</span>';
                }

                // Badge para el estado
                $estadoBadge = $Usuario['Estado'] == '1'
                    ? '<span class="badge badge-success"><i class="fa fa-check"></i> Activo</span>'
                    : '<span class="badge badge-danger"><i class="fa fa-times"></i> Inactivo</span>';

                echo '<tr>
                    <td class="text-center">'.$contador.'</td>
                    <td class="text-center"><strong>'.$Usuario["CedulaIdentidad"].'</strong></td>
                    <td>'.$Usuario['ApellidoPaterno'].' '.$Usuario["ApellidoMaterno"].' '.$Usuario["Nombres"].'</td>
                    <td><strong>'.$Usuario['Usuario'].'</strong></td>
                    <td>'.$Usuario['Celular'].'</td>
                    <td class="text-center">'.date('d/m/Y', strtotime($Usuario['FechaIngreso'])).'</td>
                    <td class="text-center">'.$tipoBadge.'</td>
                    <td class="text-center">'.$estadoBadge.'</td>
                    <td class="text-center">
                        <button
                            data-toggle="modal"
                            data-target="#ModalEditarUsuario"
                            type="button"
                            class="btn btn-warning btn-sm btnEditarUsuario"
                            CedulaIdentidad="'.$Usuario["CedulaIdentidad"].'"
                            ApellidoPaterno="'.$Usuario["ApellidoPaterno"].'"
                            ApellidoMaterno="'.$Usuario["ApellidoMaterno"].'"
                            Nombres="'.$Usuario["Nombres"].'"
                            Direccion="'.$Usuario["Direccion"].'"
                            Celular="'.$Usuario["Celular"].'"
                            Telefono="'.$Usuario["Telefono"].'"
                            title="Editar usuario">
                            <i class="fa fa-edit"></i>
                        </button>
                        '.($Usuario['Estado'] == '1'
                            ? '<button data-toggle="modal" data-target="#modalu" type="button" idusr="'.$Usuario['CedulaIdentidad'].'" class="btn btn-danger btn-sm btnusr" title="Dar de baja"><i class="fa fa-ban"></i></button>'
                            : '<button data-toggle="modal" data-target="#modals" type="button" idusrs="'.$Usuario['CedulaIdentidad'].'" class="btn btn-success btn-sm btnsubir" title="Activar usuario"><i class="fa fa-check"></i></button>').'
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
                            data-estudiante-id="'.$estudiante['EstudianteID'].'"
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

            if(isset($_POST['estudiante_id']) && isset($_POST['estudiante_ci']))
            {
                $estudianteID = $_POST['estudiante_id'];
                $ci = $_POST['estudiante_ci'];

                error_log("Procesando estudiante ID: " . $estudianteID . ", CI: " . $ci);

                // Verificar si el estudiante ya tiene usuario
                $verificar = UsuarioModelos::VerificarUsuarioEstudianteModelo($estudianteID);
                error_log("Verificación de usuario existente: " . ($verificar ? 'YA EXISTE' : 'NO EXISTE'));

                if($verificar)
                {
                    echo'
                    <script src="vistas/recursos/sweetalert.min.js"></script>
                    <script>
                    swal("ADVERTENCIA!", "Este estudiante ya tiene un usuario asignado", "warning")
                    .then(function () {
                        location.href="estudiantes";
                      })
                      ;
                     </script>';
                    return;
                }

                // Usuario será el número de carnet (CI)
                $usuario = $ci;

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
                        location.href="estudiantes";
                      });
                     </script>';
                    return;
                }

                $primeraLetra = strtoupper(substr($nombre, 0, 1));
                $passwordTexto = $primeraLetra . $ci;
                $password = password_hash($passwordTexto, PASSWORD_BCRYPT, ['cost' => 12]);

                error_log("Credenciales generadas - Usuario: " . $usuario . ", Password texto: " . $passwordTexto);

                $DatosModelo = array(
                    "EstudianteID" => $estudianteID,
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
                        location.href="estudiantes";
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
                        location.href="estudiantes";
                      })
                      ;
                     </script>';
                }
            } else {
                error_log("No se recibió estudiante_id o estudiante_ci en POST");
            }
        }

        /**
         * Genera una contraseña compleja y segura
         * Formato: Primeras 2 letras + CI + 4 caracteres aleatorios + símbolo
         * Ejemplo: Ju1234567xK9p@
         */
        private function GenerarPasswordCompleja($nombre, $ci)
        {
            // Obtener primeras 2 letras del nombre (primera mayúscula, segunda minúscula)
            $primerLetra = strtoupper(substr($nombre, 0, 1));
            $segundaLetra = strtolower(substr($nombre, 1, 1));
            $prefijo = $primerLetra . $segundaLetra;

            // Generar 4 caracteres aleatorios (mezcla de mayúsculas, minúsculas y números)
            $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $aleatorios = '';
            for ($i = 0; $i < 4; $i++) {
                $aleatorios .= $caracteres[random_int(0, strlen($caracteres) - 1)];
            }

            // Seleccionar un símbolo especial aleatorio
            $simbolos = '@#$%&*';
            $simbolo = $simbolos[random_int(0, strlen($simbolos) - 1)];

            // Construir la contraseña: Primeras2Letras + CI + 4Aleatorios + Símbolo
            $password = $prefijo . $ci . $aleatorios . $simbolo;

            return $password;
        }

        public function CrearUsuarioDocenteControlador()
        {
            // Debug: Registrar todos los datos POST recibidos
            error_log("=== DEBUG CrearUsuarioDocenteControlador ===");
            error_log("POST recibido: " . print_r($_POST, true));

            if(isset($_POST['docente_id']) && isset($_POST['docente_ci']))
            {
                $docenteID = $_POST['docente_id'];
                $ci = $_POST['docente_ci'];

                error_log("Procesando docente ID: " . $docenteID . ", CI: " . $ci);

                // Verificar si el docente ya tiene usuario
                $verificar = UsuarioModelos::VerificarUsuarioDocenteModelo($docenteID);
                error_log("Verificación de usuario existente: " . ($verificar ? 'YA EXISTE' : 'NO EXISTE'));

                if($verificar)
                {
                    echo'
                    <script src="vistas/recursos/sweetalert.min.js"></script>
                    <script>
                    swal("ADVERTENCIA!", "Este docente ya tiene un usuario asignado", "warning")
                    .then(function () {
                        location.href="docentes";
                      })
                      ;
                     </script>';
                    return;
                }

                // Usuario será el número de carnet (CI)
                $usuario = $ci;

                // Obtener nombre del docente
                $nombre = isset($_POST['docente_nombre']) ? $_POST['docente_nombre'] : '';
                error_log("Nombre recibido: " . $nombre);

                if(empty($nombre)) {
                    error_log("ERROR: Nombre del docente vacío");
                    echo'
                    <script src="vistas/recursos/sweetalert.min.js"></script>
                    <script>
                    swal("ERROR!", "Falta el nombre del docente", "error")
                    .then(function () {
                        location.href="docentes";
                      });
                     </script>';
                    return;
                }

                // Generar contraseña compleja
                $passwordTexto = $this->GenerarPasswordCompleja($nombre, $ci);
                $password = password_hash($passwordTexto, PASSWORD_BCRYPT, ['cost' => 12]);

                error_log("Credenciales generadas - Usuario: " . $usuario . ", Password texto: " . $passwordTexto);

                $DatosModelo = array(
                    "DocenteID" => $docenteID,
                    "Usuario" => $usuario,
                    "Password" => $password,
                    "PasswordTexto" => $passwordTexto,
                    "Tipo" => "DOC"
                );

                error_log("Datos a insertar: " . print_r($DatosModelo, true));

                $resultado = UsuarioModelos::CrearUsuarioDocenteModelo($DatosModelo);
                error_log("Resultado del modelo: " . $resultado);

                if ($resultado == 'exitoso')
                {
                    error_log("Usuario creado exitosamente");
                    echo'
                    <script src="vistas/recursos/sweetalert.min.js"></script>
                    <script>
                    swal({
                        title: "EXITOSO!",
                        text: "Usuario: '.$usuario.'\\nContraseña: '.$passwordTexto.'\\n\\n⚠️ IMPORTANTE: Guarde esta contraseña, es la única vez que se mostrará en texto plano.",
                        icon: "success",
                        button: "Aceptar"
                    })
                    .then(function () {
                        location.href="docentes";
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
                        location.href="docentes";
                      })
                      ;
                     </script>';
                }
            } else {
                error_log("No se recibió docente_id o docente_ci en POST");
            }
        }
        }
