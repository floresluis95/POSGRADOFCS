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
                        // Aplicar Variables de Session con los datos del usuario y personal
                        $_SESSION["IdPersonal"] = $TraerUsuario["IdPersonal"];
                        $_SESSION["CedulaIdentidad"] = $TraerUsuario["CedulaIdentidad"];
                        $_SESSION["ApellidoPaterno"] = $TraerUsuario["ApellidoPaterno"];
                        $_SESSION["ApellidoMaterno"] = $TraerUsuario["ApellidoMaterno"];
                        $_SESSION["Nombres"] = $TraerUsuario["Nombres"];
                        $_SESSION["Direccion"] = $TraerUsuario["Direccion"];
                        $_SESSION["Celular"] = $TraerUsuario["Celular"];
                        $_SESSION["Telefono"] = $TraerUsuario["Telefono"];
                        $_SESSION["Usuario"] = $TraerUsuario["Usuario"];
                        // NO guardar password en sesión por seguridad
                        $_SESSION["FechaIngreso"] = $TraerUsuario["FechaIngreso"];
                        $_SESSION["Estado"] = $TraerUsuario["Estado"];
                        $_SESSION["Tipo"] = $TraerUsuario["Tipo"];

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