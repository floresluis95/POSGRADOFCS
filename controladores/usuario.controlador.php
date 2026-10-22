<?php
    class UsuarioControladores
    {
        // Quién asigna usuario y restablece contraseñas de estudiantes y docentes (acordado
        // con Luis 2026-10-22: la Secretaría no; el Auxiliar no tiene la página Docentes)
        const GESTIONAN_CUENTAS = ['estudiante' => ['ADM', 'AUX'], 'docente' => ['ADM']];

        /**
         * ¿La sesión actual puede asignar usuario o restablecer la contraseña de ese tipo de cuenta?
         */
        public static function PuedeGestionarCuentas($tipoCuenta)
        {
            return in_array($_SESSION['Tipo'] ?? '', self::GESTIONAN_CUENTAS[$tipoCuenta] ?? [], true);
        }

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
                    case 'AUX':
                        $tipoBadge = '<span class="badge badge-warning">AUXILIAR</span>';
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

                // Por seguridad la contraseña ya no se almacena en claro ni se muestra
                // en la lista: solo se ve una vez al crearla o restablecerla.
                $passwordMostrar = '••••••';

                // Restablecer contraseña: solo el administrador (si lo hiciera una
                // secretaria, podría restablecer la del administrador y entrar como él).
                $botonRestablecer = '';
                if (($_SESSION['Tipo'] ?? '') === 'ADM') {
                    $botonRestablecer = '<button type="button" class="btn btn-info btn-sm btnRestablecerPasswordPersonal" title="Restablecer contraseña"
                            data-id-personal="'.$Usuario['IdPersonal'].'"
                            data-nombre-completo="'.htmlspecialchars($Usuario['ApellidoPaterno'].' '.$Usuario['ApellidoMaterno'].' '.$Usuario['Nombres']).'">
                            <i class="fa fa-key"></i>
                        </button>';
                }

                echo '<tr>
                    <td class="text-center">'.$contador.'</td>
                    <td class="text-center"><strong>'.$Usuario["CedulaIdentidad"].'</strong></td>
                    <td>'.$Usuario['ApellidoPaterno'].' '.$Usuario["ApellidoMaterno"].' '.$Usuario["Nombres"].'</td>
                    <td><strong>'.$Usuario['Usuario'].'</strong></td>
                    <td class="text-center"><span class="badge badge-dark passwordPersonal">'.$passwordMostrar.'</span></td>
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
                            IdPersonal="'.(int)$Usuario["IdPersonal"].'"
                            CedulaIdentidad="'.$Usuario["CedulaIdentidad"].'"
                            ApellidoPaterno="'.$Usuario["ApellidoPaterno"].'"
                            ApellidoMaterno="'.$Usuario["ApellidoMaterno"].'"
                            Nombres="'.$Usuario["Nombres"].'"
                            Direccion="'.$Usuario["Direccion"].'"
                            Celular="'.$Usuario["Celular"].'"
                            Telefono="'.$Usuario["Telefono"].'"
                            Correo="'.htmlspecialchars($Usuario["Correo"] ?? '').'"
                            IdUsuario="'.(int)$Usuario["ID"].'"
                            Tipo="'.htmlspecialchars($Usuario["Tipo"]).'"
                            EsPropio="'.((int)$Usuario["ID"] === (int)($_SESSION['IdUsuario'] ?? 0) ? '1' : '0').'"
                            title="Editar usuario">
                            <i class="fa fa-edit"></i>
                        </button>
                        '.$botonRestablecer.'
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
                    // Solo los cargos del personal (docentes y estudiantes tienen su propio registro)
                    if (!in_array($_POST['UICargo'] ?? '', ['ADM', 'SEC', 'AUX'], true)) {
                        echo'
                        <script src="vistas/recursos/sweetalert.min.js"></script>
                        <script>
                        swal("ERROR!", "Seleccione un cargo válido", "error")
                        .then(function () {
                            location.href="usuario";
                          })
                          ;
                         </script>';
                        return;
                    }

                    // El correo es el medio para recuperar la contraseña
                    $correo = self::CorreoValido($_POST['correo'] ?? '');
                    $errorPassword = $correo === null
                        ? 'Ingrese un correo electrónico válido'
                        : self::ValidarPasswordPersonal($_POST['password'] ?? '', $_POST['password_confirmar'] ?? '');
                    if ($errorPassword !== null) {
                        echo'
                        <script src="vistas/recursos/sweetalert.min.js"></script>
                        <script>
                        swal("ERROR!", '.json_encode($errorPassword).', "error")
                        .then(function () {
                            location.href="usuario";
                          })
                          ;
                         </script>';
                        return;
                    }

                    // Nombre de usuario: único en todo el sistema (con él se inicia sesión)
                    // y C.I. único dentro del personal (puede repetirse con un estudiante
                    // o docente: la misma persona puede tener también esa otra cuenta).
                    $ci = trim($_POST['ci']);
                    $usuario = trim($_POST['usuario'] ?? '');
                    $errorDatos = null;
                    if ($ci === '') {
                        $errorDatos = 'Ingrese el C.I.';
                    } elseif (!preg_match('/^[A-Za-z0-9._-]{3,20}$/', $usuario)) {
                        $errorDatos = 'El usuario debe tener de 3 a 20 caracteres: letras, números, punto, guion o guion bajo (sin espacios)';
                    } elseif (UsuarioModelos::ExisteNombreUsuarioModelo($usuario)) {
                        $errorDatos = 'El usuario "' . $usuario . '" ya existe. Elija otro nombre de usuario';
                    } elseif (UsuarioModelos::ExisteCiPersonalModelo($ci)) {
                        $errorDatos = 'Ya hay un personal registrado con el C.I. ' . $ci;
                    }
                    if ($errorDatos !== null) {
                        echo'
                        <script src="vistas/recursos/sweetalert.min.js"></script>
                        <script>
                        swal("ERROR!", '.json_encode($errorDatos).', "error")
                        .then(function () {
                            location.href="usuario";
                          })
                          ;
                         </script>';
                        return;
                    }

                    $DatosPersonal = array(
                        "CedulaIdentidad" => $ci,
                        "ApellidoPaterno" => strtoupper($_POST['apaterno']),
                        "ApellidoMaterno" => strtoupper($_POST['amaterno']),
                        "Nombres" => strtoupper($_POST['nombres']),
                        "Direccion" => strtoupper($_POST['direccion']),
                        "Celular" => $_POST['celular'],
                        "Telefono" => $_POST['telefono'],
                        "Correo" => $correo
                    );

                    // Contraseña escrita por el administrador en el formulario (no se genera)
                    $DatosUsuario = array(
                        "Usuario" => $usuario,
                        "Password" => password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => 12]),
                        "Tipo" => $_POST['UICargo']
                    );

                    // Personal y usuario juntos: o se crean los dos, o ninguno
                    $resultado = UsuarioModelos::RegistrarPersonalConUsuarioModelo($DatosPersonal, $DatosUsuario);

                    if ($resultado == 'exitoso')
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
                        swal("ERROR!", '.json_encode($resultado == 'usuario_duplicado'
                            ? 'El usuario "' . $usuario . '" ya existe. Elija otro nombre de usuario'
                            : 'Registro incorrecto de usuario').', "error")
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
                // Opcional al editar (el personal anterior no tenía correo), pero si se escribe debe ser válido
                $correoEditado = trim($_POST['editarcorreo'] ?? '');
                if ($correoEditado !== '' && self::CorreoValido($correoEditado) === null) {
                    echo'
                    <script src="vistas/recursos/sweetalert.min.js"></script>
                    <script>
                    swal("ERROR!", "El correo electrónico no es válido", "error")
                    .then(function () {
                        location.href="usuario";
                      })
                      ;
                     </script>';
                    return;
                }

                // Cambio de rol (solo el ADM): nunca el propio ni dejar el sistema sin administrador
                // Por cuenta (ID de usuario): una misma persona puede tener varias cuentas
                $idUsuarioEditado = (int)($_POST['editaridusuario'] ?? 0);
                $rolNuevo = $_POST['editarcargo'] ?? '';
                $rolActual = UsuarioModelos::RolCuentaPersonalModelo($idUsuarioEditado);
                $cambiaRol = $rolNuevo !== '' && $rolActual !== null && $rolNuevo !== $rolActual;
                if ($cambiaRol) {
                    $errorRol = null;
                    if (($_SESSION['Tipo'] ?? '') !== 'ADM') {
                        $errorRol = 'Solo el administrador puede cambiar roles';
                    } elseif (!in_array($rolNuevo, ['ADM', 'SEC', 'AUX'], true)) {
                        $errorRol = 'Rol no válido';
                    } elseif ($idUsuarioEditado === (int)($_SESSION['IdUsuario'] ?? 0)) {
                        $errorRol = 'No puede cambiar su propio rol';
                    } elseif ($rolActual === 'ADM' && UsuarioModelos::ContarAdministradoresActivosModelo() <= 1) {
                        $errorRol = 'Es el único administrador activo: primero dé el rol de administrador a otra persona';
                    }
                    if ($errorRol !== null) {
                        echo'
                        <script src="vistas/recursos/sweetalert.min.js"></script>
                        <script>
                        swal("ERROR!", ' . json_encode($errorRol) . ', "error")
                        .then(function () {
                            location.href="usuario";
                          })
                          ;
                         </script>';
                        return;
                    }
                }

                $DatosPersonal = array(
                    // Se edita por IdPersonal: hay personal que comparte C.I. y se editaba junto
                    "IdPersonal" => (int)($_POST['editaridpersonal'] ?? 0),
                    "ApellidoPaterno" => strtoupper($_POST['editarapaterno']),
                    "ApellidoMaterno" => strtoupper($_POST['editaramaterno']),
                    "Nombres" => strtoupper($_POST['editarnombres']),
                    "Direccion" => strtoupper($_POST['editardireccion']),
                    "Celular" => $_POST['editarcelular'],
                    "Telefono" => $_POST['editartelefono'],
                    "Correo" => self::CorreoValido($correoEditado)
                );

                $EditarPersonal = UsuarioModelos::EditarUsuaario($DatosPersonal);
                if ($EditarPersonal == 'exitoso' && $cambiaRol) {
                    $EditarPersonal = UsuarioModelos::CambiarRolCuentaPersonalModelo($idUsuarioEditado, $rolNuevo);
                    if ($EditarPersonal == 'exitoso') {
                        error_log('Cambio de rol: usuario ' . $idUsuarioEditado . ' de ' . $rolActual . ' a ' . $rolNuevo . ' por ' . ($_SESSION['Usuario'] ?? '?'));
                    }
                }
                $nombresRol = ['ADM' => 'Administrador', 'SEC' => 'Secretaría', 'AUX' => 'Auxiliar'];
                $textoExito = $cambiaRol
                    ? 'Se editó el personal. Su rol ahora es ' . $nombresRol[$rolNuevo] . '; si tenía la sesión abierta, deberá volver a ingresar.'
                    : 'Se edito el personal correctamente';

                if ($EditarPersonal == 'exitoso')
                {
                    echo'
                    <script src="vistas/recursos/sweetalert.min.js"></script>
                    <script>
                    swal("EXITOSO!", ' . json_encode($textoExito) . ', "success")
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

            if(isset($_POST['estudiante_id']) && isset($_POST['estudiante_ci']) && self::PuedeGestionarCuentas('estudiante'))
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

                // Usuario será el número de carnet (CI). Si ese nombre ya lo usa otra cuenta
                // (p. ej. la misma persona como personal), se le agrega "E": 7311599E
                $usuario = UsuarioModelos::UsuarioDisponibleModelo($ci, 'E');

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

                $passwordTexto = $this->GenerarPasswordEstudiante();
                $password = password_hash($passwordTexto, PASSWORD_BCRYPT, ['cost' => 12]);

                $DatosModelo = array(
                    "EstudianteID" => $estudianteID,
                    "Usuario" => $usuario,
                    "Password" => $password,
                    "Tipo" => "EST"
                );

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
         * Correo en minúsculas si es válido; null si está vacío o no es un correo.
         * @return string|null
         */
        public static function CorreoValido($correo)
        {
            $correo = strtolower(trim($correo));
            return filter_var($correo, FILTER_VALIDATE_EMAIL) ? $correo : null;
        }

        /**
         * Valida una contraseña escrita a mano: la que el administrador pone a un
         * usuario del personal (ADM, SEC, AUX) al crearlo o restablecerla, y la que
         * cualquier usuario elige al recuperar su contraseña por correo.
         * @return string|null Mensaje de error, o null si es válida
         */
        public static function ValidarPasswordPersonal($password, $confirmacion)
        {
            if (strlen($password) < 8) {
                return 'La contraseña debe tener al menos 8 caracteres';
            }
            if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
                return 'La contraseña debe tener al menos una letra y un número';
            }
            if ($password !== $confirmacion) {
                return 'Las contraseñas no coinciden';
            }
            return null;
        }

        /**
         * Genera una contraseña compleja y segura
         * Formato: Primeras 2 letras + CI + 4 caracteres aleatorios + símbolo
         * Ejemplo: Ju1234567xK9p@
         */
        /**
         * Genera una contraseña aleatoria de 12 caracteres, con al menos una
         * mayuscula, una minuscula, un numero y un simbolo garantizados, sin
         * caracteres ambiguos (0/O, 1/l/I) para evitar errores al transcribirla.
         * No incluye el nombre ni el CI del usuario: son datos conocidos/publicos
         * (aparecen en documentos, carnets, listas), no deben formar parte de un
         * secreto. $nombre y $ci se mantienen como parametros por compatibilidad
         * con el llamador, pero ya no se usan para construir la contraseña.
         */
        public static function GenerarPasswordCompleja($nombre = '', $ci = '')
        {
            $minusculas = 'abcdefghjkmnpqrstuvwxyz';
            $mayusculas = 'ABCDEFGHJKMNPQRSTUVWXYZ';
            $numeros = '23456789';
            $simbolos = '@#$%&*!?';
            $todos = $minusculas . $mayusculas . $numeros . $simbolos;

            // Garantizar al menos un caracter de cada tipo
            $password = [
                $mayusculas[random_int(0, strlen($mayusculas) - 1)],
                $minusculas[random_int(0, strlen($minusculas) - 1)],
                $numeros[random_int(0, strlen($numeros) - 1)],
                $simbolos[random_int(0, strlen($simbolos) - 1)],
            ];

            // Completar hasta 12 caracteres con el conjunto completo
            for ($i = count($password); $i < 12; $i++) {
                $password[] = $todos[random_int(0, strlen($todos) - 1)];
            }

            // Barajar (aleatoriamente, no con shuffle() que no es criptografico
            // pero aqui solo reordena posiciones ya elegidas al azar)
            for ($i = count($password) - 1; $i > 0; $i--) {
                $j = random_int(0, $i);
                [$password[$i], $password[$j]] = [$password[$j], $password[$i]];
            }

            return implode('', $password);
        }

        /**
         * Genera una contraseña para estudiantes: mas fuerte que "inicial + CI"
         * (que es adivinable porque el CI es un dato publico) pero mas simple que
         * GenerarPasswordCompleja() (sin simbolos, 8 caracteres) para que sea facil
         * de transcribir/escribir en el celular. Garantiza al menos una mayuscula,
         * una minuscula y un numero; no usa el nombre ni el CI del estudiante.
         */
        public static function GenerarPasswordEstudiante()
        {
            $minusculas = 'abcdefghjkmnpqrstuvwxyz';
            $mayusculas = 'ABCDEFGHJKMNPQRSTUVWXYZ';
            $numeros = '23456789';
            $todos = $minusculas . $mayusculas . $numeros;

            $password = [
                $mayusculas[random_int(0, strlen($mayusculas) - 1)],
                $minusculas[random_int(0, strlen($minusculas) - 1)],
                $numeros[random_int(0, strlen($numeros) - 1)],
            ];

            for ($i = count($password); $i < 8; $i++) {
                $password[] = $todos[random_int(0, strlen($todos) - 1)];
            }

            for ($i = count($password) - 1; $i > 0; $i--) {
                $j = random_int(0, $i);
                [$password[$i], $password[$j]] = [$password[$j], $password[$i]];
            }

            return implode('', $password);
        }

        public function CrearUsuarioDocenteControlador()
        {

            if(isset($_POST['docente_id']) && isset($_POST['docente_ci']) && self::PuedeGestionarCuentas('docente'))
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

                // Usuario será el número de carnet (CI). Si ese nombre ya lo usa otra cuenta
                // (p. ej. la misma persona como personal o estudiante), se le agrega "D": 7311599D
                $usuario = UsuarioModelos::UsuarioDisponibleModelo($ci, 'D');

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

                $DatosModelo = array(
                    "DocenteID" => $docenteID,
                    "Usuario" => $usuario,
                    "Password" => $password,
                    "Tipo" => "DOC"
                );

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
