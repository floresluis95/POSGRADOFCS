<?php
    class IngresoControladores
    {
        public function ValidarIngresoControlador()

        {
            if (isset($_POST["IUsuario"]))
            {
                $User = $_POST["IUsuario"];
                $Password = $_POST["IPassword"];

                $TraerUsuario = IngresoModelos::ValidarIngresoModelo($User);

                // Verificar si el usuario existe y está activo
                if ($TraerUsuario && $TraerUsuario["Usuario"] == $User && $TraerUsuario["Estado"] == 1)
                {
                    // Verificar password con password_verify (soporta tanto bcrypt nuevo como crypt antiguo)
                    $passwordValido = false;

                    // Intentar con password_verify primero (nuevo sistema)
                    if (password_verify($Password, $TraerUsuario["Password"])) {
                        $passwordValido = true;
                    }
                    // Si falla, intentar con crypt (sistema antiguo - compatibilidad temporal)
                    else if ($TraerUsuario["Password"] == crypt($Password, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$')) {
                        $passwordValido = true;
                        // TODO: Actualizar password al nuevo formato en próximo login
                    }

                    if ($passwordValido) {
                        session_start();
                        $_SESSION["Validar"] = true;
                        $_SESSION["Usuario"] = $TraerUsuario["Usuario"];
                        $_SESSION["FechaIngreso"] = $TraerUsuario["FechaIngreso"];
                        $_SESSION["Estado"] = $TraerUsuario["Estado"];
                        $_SESSION["Tipo"] = $TraerUsuario["Tipo"];

                        // Determinar tipo de usuario y cargar datos correspondientes
                        if (!empty($TraerUsuario["IdPersonal"])) {
                            // Usuario es PERSONAL ADMINISTRATIVO
                            $_SESSION["IdPersonal"] = $TraerUsuario["IdPersonal"];
                            $_SESSION["CedulaIdentidad"] = $TraerUsuario["CedulaIdentidad"];
                            $_SESSION["ApellidoPaterno"] = $TraerUsuario["ApellidoPaterno"];
                            $_SESSION["ApellidoMaterno"] = $TraerUsuario["ApellidoMaterno"];
                            $_SESSION["Nombres"] = $TraerUsuario["Nombres"];
                            $_SESSION["Direccion"] = $TraerUsuario["Direccion"];
                            $_SESSION["Celular"] = $TraerUsuario["Celular"];
                            $_SESSION["Telefono"] = $TraerUsuario["Telefono"];
                        }
                        elseif (!empty($TraerUsuario["EstudianteID"])) {
                            // Usuario es ESTUDIANTE
                            $_SESSION["EstudianteID"] = $TraerUsuario["EstudianteID"];
                            $_SESSION["CedulaIdentidad"] = $TraerUsuario["EstudianteCi"];
                            $_SESSION["Complemento"] = $TraerUsuario["EstudianteComplemento"];
                            $_SESSION["Expedido"] = $TraerUsuario["EstudianteExp"];
                            $_SESSION["ApellidoPaterno"] = $TraerUsuario["EstudianteApaterno"];
                            $_SESSION["ApellidoMaterno"] = $TraerUsuario["EstudianteAmaterno"];
                            $_SESSION["Nombres"] = $TraerUsuario["EstudianteNombre"];
                            $_SESSION["Correo"] = $TraerUsuario["EstudianteCorreo"];
                            $_SESSION["Celular"] = $TraerUsuario["EstudianteCelular"];
                            $_SESSION["Direccion"] = $TraerUsuario["EstudianteDireccion"];
                        }
                        elseif (!empty($TraerUsuario["DocenteID"])) {
                            // Usuario es DOCENTE
                            $_SESSION["DocenteID"] = $TraerUsuario["DocenteID"];
                            $_SESSION["CedulaIdentidad"] = $TraerUsuario["DocenteCi"];
                            $_SESSION["Complemento"] = $TraerUsuario["DocenteComplemento"];
                            $_SESSION["Expedido"] = $TraerUsuario["DocenteExp"];
                            $_SESSION["ApellidoPaterno"] = $TraerUsuario["DocenteApaterno"];
                            $_SESSION["ApellidoMaterno"] = $TraerUsuario["DocenteAmaterno"];
                            $_SESSION["Nombres"] = $TraerUsuario["DocenteNombre"];
                            $_SESSION["Correo"] = $TraerUsuario["DocenteCorreo"];
                            $_SESSION["Celular"] = $TraerUsuario["DocenteCel"];
                            $_SESSION["Direccion"] = $TraerUsuario["DocenteDireccion"];
                            $_SESSION["Especialidad"] = $TraerUsuario["DocenteEspecialidad"];
                        }

                       header('Location: panel');
                       exit();
                    }
                }
                else 
                {
                    echo '
                    <div class="alert alert-primary fade show" role="alert">
                            <div class="alert-icon"><i class="flaticon-warning"></i></div>
                            <div class="alert-text">Usuario o Contraseña incorrecto </div>
                            <div class="alert-close">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true"><i class="la la-close"></i></span>
                                </button>
                            </div>
                        </div>
                    ';
                }
            }
        }
    }