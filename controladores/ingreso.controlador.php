<?php
    class IngresoControladores
    {
        public function ValidarIngresoControlador()
 
        {
            if (isset($_POST["IUsuario"]))
            {
                $User = $_POST["IUsuario"];
                $Password = $_POST["IPassword"];

                $encriptar = crypt($Password, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
                $TraerUsuario = IngresoModelos::ValidarIngresoModelo($User);
                if ($TraerUsuario["Usuario"] == $User && $TraerUsuario["Password"] == $encriptar && $TraerUsuario["Estado"] == 1)
                {
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
                    $_SESSION["Password"] = $TraerUsuario["Password"];
                    $_SESSION["FechaIngreso"] = $TraerUsuario["FechaIngreso"];
                    $_SESSION["Estado"] = $TraerUsuario["Estado"];
                    $_SESSION["Tipo"] = $TraerUsuario["Tipo"];
                   
                   header('Location: panel');
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