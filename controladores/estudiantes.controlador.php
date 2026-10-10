<?php
class EstudiantesControladores
{ 
    public function ListaEstudianteControladores()
    {
        $Listae = EstudiantesModelos::ListaEstudianteActivoModelo();
        $contador = 0;

        foreach ($Listae as $Estudiante) {
            $contador++;

            $nombreCompleto = $Estudiante['Apaterno'] . ' ' . $Estudiante['Amaterno'] . ' ' . $Estudiante['Nombre'];
            $ciCompleto = $Estudiante['Ci'] . ($Estudiante['Complemento'] ? '-' . $Estudiante['Complemento'] : '') . ' ' . $Estudiante['Exp'];

            // Verificar si tiene usuario
            $tieneUsuario = !empty($Estudiante['Usuario']);
            $estadoUsuarioBadge = '';
            $botonUsuario = '';
            $botonBloqueo = '';

            if ($tieneUsuario) {
                $estadoActivo = $Estudiante['EstadoUsuario'] == '1';
                $estadoUsuarioBadge = $estadoActivo
                    ? '<span class="badge badge-success"><i class="fa fa-check"></i> ' . $Estudiante['Usuario'] . '</span>'
                    : '<span class="badge badge-danger"><i class="fa fa-times"></i> ' . $Estudiante['Usuario'] . ' (Inactivo)</span>';
                $botonUsuario = '';

                // Bloquear/Habilitar el acceso del estudiante al sistema (no borra ni
                // afecta sus datos academicos, solo su capacidad de iniciar sesion).
                $botonBloqueo = $estadoActivo
                    ? '<button type="button" class="btn btn-secondary btn-sm btnBloquearAccesoEstudiante" title="Bloquear acceso al sistema"
                            data-estudiante-id="' . $Estudiante['EstudianteID'] . '"
                            data-nombre-completo="' . htmlspecialchars($nombreCompleto) . '"
                            data-nuevo-estado="0">
                            <i class="fa fa-lock"></i>
                        </button>'
                    : '<button type="button" class="btn btn-success btn-sm btnBloquearAccesoEstudiante" title="Habilitar acceso al sistema"
                            data-estudiante-id="' . $Estudiante['EstudianteID'] . '"
                            data-nombre-completo="' . htmlspecialchars($nombreCompleto) . '"
                            data-nuevo-estado="1">
                            <i class="fa fa-unlock"></i>
                        </button>';
            } else {
                $estadoUsuarioBadge = '<span class="badge badge-warning"><i class="fa fa-exclamation-triangle"></i> Sin usuario</span>';
                $botonUsuario = '<button
                                    data-toggle="modal"
                                    data-target="#ModalAsignarUsuario"
                                    type="button"
                                    class="btn btn-success btn-sm btnAsignarUsuario"
                                    data-estudiante-id="' . $Estudiante['EstudianteID'] . '"
                                    data-ci="' . $Estudiante['Ci'] . '"
                                    data-ci-completo="' . $ciCompleto . '"
                                    data-nombre-completo="' . $nombreCompleto . '"
                                    data-nombre-pila="' . $Estudiante['Nombre'] . '"
                                    data-correo="' . ($Estudiante['Correo'] ? $Estudiante['Correo'] : '') . '"
                                    title="Asignar usuario">
                                    <i class="fa fa-user-plus"></i> Asignar
                                </button>';
            }

            $botonEditar = '<button
                                type="button"
                                class="btn btn-primary btn-sm btnEditarEstudiante"
                                data-estudiante-id="' . $Estudiante['EstudianteID'] . '"
                                title="Editar datos del estudiante">
                                <i class="fa fa-pencil-alt"></i>
                            </button>';

            // Botón "Ver Detalle": agrupa correo, profesión, celular, usuario y
            // contraseña en un modal, igual que en Docentes.
            $estadoUsuarioTexto = !$tieneUsuario ? 'Sin usuario asignado' : ($Estudiante['EstadoUsuario'] == '1' ? 'Activo' : 'Bloqueado');
            $botonDetalle = '<button type="button" class="btn btn-info btn-sm btnVerDetalleEstudiante" title="Ver detalle"
                                data-toggle="modal" data-target="#ModalDetalleEstudiante"
                                data-estudiante-id="' . $Estudiante['EstudianteID'] . '"
                                data-tiene-usuario="' . ($tieneUsuario ? '1' : '0') . '"
                                data-nombre-completo="' . htmlspecialchars($nombreCompleto) . '"
                                data-ci-completo="' . htmlspecialchars($ciCompleto) . '"
                                data-correo="' . htmlspecialchars($Estudiante['Correo'] ? $Estudiante['Correo'] : 'No registrado') . '"
                                data-profesion="' . htmlspecialchars($Estudiante['NombreProfesion'] ?: 'No registrada') . '"
                                data-celular="' . htmlspecialchars($Estudiante['Celular'] ?: 'No registrado') . '"
                                data-usuario="' . htmlspecialchars($tieneUsuario ? $Estudiante['Usuario'] : 'No asignado') . '"
                                data-estado-usuario="' . htmlspecialchars($estadoUsuarioTexto) . '">
                                <i class="fa fa-eye"></i>
                            </button>';

            echo '<tr>
                    <td class="text-center">' . $contador . '</td>
                    <td class="text-center"><strong>' . $ciCompleto . '</strong></td>
                    <td>' . $nombreCompleto . '</td>
                    <td>' . $Estudiante['NombreProfesion'] . '</td>
                    <td>' . ($Estudiante['Correo'] ? $Estudiante['Correo'] : '<span class="text-muted">No registrado</span>') . '</td>
                    <td class="text-center">' . $Estudiante['Celular'] . '</td>
                    <td class="text-center">' . $estadoUsuarioBadge . '</td>
                    <td class="text-center">' . $botonDetalle . ' ' . $botonEditar . ' ' . $botonUsuario . ' ' . $botonBloqueo . '</td>
                  </tr>';
        }
    }

    public function ListarEstudiantesProgramasControladores()
    {
        $ListaEstudiantes = EstudiantesModelos::ListarEstudianteProgramaModelo();

        foreach ($ListaEstudiantes as $Estudiante) {
            echo '<tr>
                    <td WIDTH="30" HEIGHT="30">' . $Estudiante['EstudianteID'] . '</td>
                    <td>' . $Estudiante['Ci'] . ' ' . $Estudiante['Complemento'] . ' ' . $Estudiante['Exp'] . '</td>
                    <td>' . $Estudiante['Nombre'] . ' ' . $Estudiante['Apaterno'] . ' ' . $Estudiante['Amaterno'] . '</td>
                  
                    <td>' . $Estudiante['Celular'] . ' / ' . $Estudiante['Telefono'] . '</td>
                    <td>' . $Estudiante['NombrePrograma'] . '</td>
                    <td>' . $Estudiante['FechaInscripcion'] . '</td>
                  </tr>';
        }
    }

  public function RegistarEstudianteControlador()
{
  
    if (isset($_POST["Ci"])) {

        // Validaciones mínimas
        if (empty($_POST["IdProfesion"]) || !is_numeric($_POST["IdProfesion"])) {
            echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                  <script>
                  swal("ERROR!", "Seleccione una profesión válida", "error")
                  .then(function () { location.href="estudiantes"; });
                  </script>';
            return;
        }

        $errorEgreso = EstudiantesModelos::ValidarDatosEgresoModelo($_POST['IdUniversidad'] ?? '', $_POST['FechaEgreso'] ?? '', $_POST['FechaNacimiento'] ?? '');
        if ($errorEgreso !== null) {
            echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                  <script>
                  swal("ERROR!", "' . addslashes($errorEgreso) . '", "error")
                  .then(function () { location.href="estudiantes"; });
                  </script>';
            return;
        }

        $DatosEstudiante = array(
            "Ci"              => htmlspecialchars(trim($_POST['Ci'])),
            "Complemento"     => mb_strtoupper(htmlspecialchars(trim($_POST['Complemento'])), 'UTF-8'),
            "Exp"             => mb_strtoupper(htmlspecialchars(trim($_POST['Exp'])), 'UTF-8'),
            "Nombre"          => mb_strtoupper(htmlspecialchars(trim($_POST['Nombre'])), 'UTF-8'),
            "Apaterno"        => mb_strtoupper(htmlspecialchars(trim($_POST['Apaterno'])), 'UTF-8'),
            "Amaterno"        => mb_strtoupper(htmlspecialchars(trim($_POST['Amaterno'])), 'UTF-8'),
            "FechaNacimiento" => htmlspecialchars(trim($_POST['FechaNacimiento'])),
            "Edad"            => (int)htmlspecialchars(trim($_POST['Edad'])),
            "Lugarn"          => mb_strtoupper(htmlspecialchars(trim($_POST['Lugarn'])), 'UTF-8'),
            "Correo"          => strtolower(htmlspecialchars(trim($_POST['Correo']))), // Correo en minúsculas
            "IdProfesion"     => (int)$_POST["IdProfesion"],
            "Trabajo"         => mb_strtoupper(htmlspecialchars(trim($_POST['Trabajo'])), 'UTF-8'),
            "Direccion"       => mb_strtoupper(htmlspecialchars(trim($_POST['Direccion'])), 'UTF-8'),
            "Telefono"        => htmlspecialchars(trim($_POST['Telefono'])),
            "Celular"         => htmlspecialchars(trim($_POST['Celular'])),
            "IdUniversidad"   => (!empty($_POST['IdUniversidad']) && is_numeric($_POST['IdUniversidad'])) ? (int)$_POST['IdUniversidad'] : null,
            "FechaEgreso"     => !empty($_POST['FechaEgreso']) ? htmlspecialchars(trim($_POST['FechaEgreso'])) : null,
        );

            $ciestudiante = $DatosEstudiante['Ci']; 
        $existe = EstudiantesModelos::BuscarEstudianteModelo($ciestudiante);

        if ($existe) {
            echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                  <script>
                  swal("ERROR!", "' . addslashes(EstudiantesModelos::MotivoDatoRepetidoModelo($ciestudiante, null) ?? 'El estudiante ya está registrado en el sistema') . '", "error")
                  .then(function () { location.href="estudiantes"; });
                  </script>';
            return;
        }

        // Verificar que la profesión existe en la tabla profesion (previene FK fail)
        $existeProf = ProfesionModelos::ObtenerPorId($DatosEstudiante['IdProfesion']);
        if (!$existeProf) {
            echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                  <script>
                  swal("ERROR!", "Profesión no válida (no existe)", "error")
                  .then(function () { location.href="estudiantes"; });
                  </script>';
            return;
        }

        $resultado = EstudiantesModelos::RegistrarEstudianteModelo($DatosEstudiante);

        if ($resultado === 'exitoso') {
            echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                  <script>
                  swal("EXITOSO!", "Se registro al estudiante", "success")
                  .then(function () { location.href="estudiantes"; });
                  </script>';
        } else {
            // Si $resultado trae mensaje de error, mostrarlo (útil para debugging)
            $msg = addslashes($resultado);
            echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                  <script>
                  swal("ERROR!", "No se registro al estudiante: ' . $msg . '", "error")
                  .then(function () { location.href="estudiantes"; });
                  </script>';
        }
    }
}
  public function RegistarEstudianteControlador2()
{
  
    if (isset($_POST["Ci"])) {

        // Página a la que se debe regresar tras procesar el formulario (por defecto 'inscripcion')
        $paginaRedirect = isset($_POST['paginaRedirect'])
            ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['paginaRedirect'])
            : 'inscripcion';
        if ($paginaRedirect === '') {
            $paginaRedirect = 'inscripcion';
        }

        // Validaciones mínimas
        if (empty($_POST["IdProfesion"]) || !is_numeric($_POST["IdProfesion"])) {
            echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                  <script>
                  swal("ERROR!", "Seleccione una profesión válida", "error")
                  .then(function () { location.href="' . $paginaRedirect . '"; });
                  </script>';
            return;
        }

        $errorEgreso = EstudiantesModelos::ValidarDatosEgresoModelo($_POST['IdUniversidad'] ?? '', $_POST['FechaEgreso'] ?? '', $_POST['FechaNacimiento'] ?? '');
        if ($errorEgreso !== null) {
            echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                  <script>
                  swal("ERROR!", "' . addslashes($errorEgreso) . '", "error")
                  .then(function () { location.href="' . $paginaRedirect . '"; });
                  </script>';
            return;
        }

        $DatosEstudiante = array(
            "Ci"              => htmlspecialchars(trim($_POST['Ci'])),
            "Complemento"     => mb_strtoupper(htmlspecialchars(trim($_POST['Complemento'])), 'UTF-8'),
            "Exp"             => mb_strtoupper(htmlspecialchars(trim($_POST['Exp'])), 'UTF-8'),
            "Nombre"          => mb_strtoupper(htmlspecialchars(trim($_POST['Nombre'])), 'UTF-8'),
            "Apaterno"        => mb_strtoupper(htmlspecialchars(trim($_POST['Apaterno'])), 'UTF-8'),
            "Amaterno"        => mb_strtoupper(htmlspecialchars(trim($_POST['Amaterno'])), 'UTF-8'),
            "FechaNacimiento" => htmlspecialchars(trim($_POST['FechaNacimiento'])),
            "Edad"            => (int)htmlspecialchars(trim($_POST['Edad'])),
            "Lugarn"          => mb_strtoupper(htmlspecialchars(trim($_POST['Lugarn'])), 'UTF-8'),
            "Correo"          => strtolower(htmlspecialchars(trim($_POST['Correo']))), // Correo en minúsculas
            "IdProfesion"     => (int)$_POST["IdProfesion"],
            "Trabajo"         => mb_strtoupper(htmlspecialchars(trim($_POST['Trabajo'])), 'UTF-8'),
            "Direccion"       => mb_strtoupper(htmlspecialchars(trim($_POST['Direccion'])), 'UTF-8'),
            "Telefono"        => htmlspecialchars(trim($_POST['Telefono'])),
            "Celular"         => htmlspecialchars(trim($_POST['Celular'])),
            "IdUniversidad"   => (!empty($_POST['IdUniversidad']) && is_numeric($_POST['IdUniversidad'])) ? (int)$_POST['IdUniversidad'] : null,
            "FechaEgreso"     => !empty($_POST['FechaEgreso']) ? htmlspecialchars(trim($_POST['FechaEgreso'])) : null,
        );

            $ciestudiante = $DatosEstudiante['Ci']; 
        $existe = EstudiantesModelos::BuscarEstudianteModelo($ciestudiante);

        if ($existe) {
            echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                  <script>
                  swal("ERROR!", "' . addslashes(EstudiantesModelos::MotivoDatoRepetidoModelo($ciestudiante, null) ?? 'El estudiante ya está registrado en el sistema') . '", "error")
                  .then(function () { location.href="' . $paginaRedirect . '"; });
                  </script>';
            return;
        }

        // Verificar que la profesión existe en la tabla profesion (previene FK fail)
        $existeProf = ProfesionModelos::ObtenerPorId($DatosEstudiante['IdProfesion']);
        if (!$existeProf) {
            echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                  <script>
                  swal("ERROR!", "Profesión no válida (no existe)", "error")
                  .then(function () { location.href="' . $paginaRedirect . '"; });
                  </script>';
            return;
        }

        $resultado = EstudiantesModelos::RegistrarEstudianteModelo($DatosEstudiante);

        if ($resultado === 'exitoso') {
            // Se incluye el CI en la URL de retorno para que la página de origen
            // pueda preseleccionar automáticamente al estudiante recién creado.
            $redirectUrl = $paginaRedirect . '?nuevoEstudianteCi=' . urlencode($DatosEstudiante['Ci']);
            echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                  <script>
                  swal("EXITOSO!", "Se registro al estudiante", "success")
                  .then(function () { location.href="' . $redirectUrl . '"; });
                  </script>';
        } else {
            // Si $resultado trae mensaje de error, mostrarlo (útil para debugging)
            $msg = addslashes($resultado);
            echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                  <script>
                  swal("ERROR!", "No se registro al estudiante: ' . $msg . '", "error")
                  .then(function () { location.href="' . $paginaRedirect . '"; });
                  </script>';
        }
    }
}


    public function EstudianteActivoControlador()
    {
        $TraerListaEstudianteActivo = EstudiantesModelos::ListaEstudianteActivoModelo();

        foreach ($TraerListaEstudianteActivo as $value) {
            echo '<option value="' . $value["EstudianteID"] . '">' .
                 $value["Ci"] . ' -> ' . $value["Nombre"] . ' ' .
                 $value["Apaterno"] . ' ' . $value["Amaterno"] .
                 '</option>';
        }
    }

  

    public function EstadoEstudianteControlador()
    {
        if (isset($_POST["Ci"])) {

            $idusr = $_POST["Ci"];

            // ❗ CORREGIDO: antes usabas $Ci (NO existe)
            $ed = UsuarioModelos::CambiarEstadoUsuario($idusr);

            if ($ed == 'exitoso') {
                echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                      <script>
                      swal("EXITOSO!", "El usuario ya no tiene acceso al sistema", "success")
                      .then(function () { location.href="usuario"; });
                      </script>';
                     
            } else {
                echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                      <script>
                      swal("ERROR!", "No se realizo el cambio", "error")
                      .then(function () { location.href="usuario"; });
                      </script>';
            }
        }
    }

    /**
     * Bloquear o habilitar el acceso al sistema (login) de un Estudiante, sin
     * afectar sus datos academicos ni sus inscripciones/pagos.
     */
    public function CambiarEstadoUsuarioEstudianteControlador()
    {
        if (isset($_POST["cambiarEstadoUsuarioEstudiante"])) {
            $estudianteID = intval($_POST['estudianteID']);
            $nuevoEstado = intval($_POST['nuevoEstado']) === 1 ? 1 : 0;

            $resultado = UsuarioModelos::CambiarEstadoUsuarioEstudianteModelo($estudianteID, $nuevoEstado);
            $accionTexto = $nuevoEstado === 1 ? 'habilitado' : 'bloqueado';

            if ($resultado == 'exitoso') {
                echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                      <script>
                      swal("EXITOSO!", "Acceso al sistema ' . $accionTexto . ' correctamente", "success")
                      .then(function () {
                          location.href="estudiantes";
                      });
                      </script>';
            } else {
                echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                      <script>
                      swal("ERROR!", "No se pudo cambiar el acceso del estudiante", "error")
                      .then(function () {
                          location.href="estudiantes";
                      });
                      </script>';
            }
        }
    }
}


class ProfesionControlador {
    public function ListaProfesionControlador() {
        $ListaProfesion = ProfesionModelos::ListaprofesionModelos();

        if ($ListaProfesion && count($ListaProfesion) > 0) {
            foreach ($ListaProfesion as $value) {
                // value debe ser el ID
                echo '<option value="'.$value["IdProfesion"].'">'.$value["NombreProfesion"].'</option>';
            }
        } else {
            echo '<option value="">⚠️ No se encontraron profesiones</option>';
        }
    }
}

class UniversidadControlador {
    // data-tipo en cada <option> permite mostrar el tipo (pública/privada/...)
    // en el formulario sin otra consulta, apenas se selecciona la universidad.
    public function ListaUniversidadControlador() {
        $ListaUniversidad = UniversidadModelos::ListarUniversidadesModelo();

        if ($ListaUniversidad && count($ListaUniversidad) > 0) {
            foreach ($ListaUniversidad as $value) {
                echo '<option value="' . $value["IdUniversidad"] . '" data-tipo="' . $value["TipoUniversidad"] . '">'
                    . htmlspecialchars($value["NombreUniversidad"]) . '</option>';
            }
        } else {
            echo '<option value="">⚠️ No se encontraron universidades</option>';
        }
    }
}
