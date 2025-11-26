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

            if ($tieneUsuario) {
                $estadoActivo = $Estudiante['EstadoUsuario'] == '1';
                $estadoUsuarioBadge = $estadoActivo
                    ? '<span class="badge badge-success"><i class="fa fa-check"></i> ' . $Estudiante['Usuario'] . '</span>'
                    : '<span class="badge badge-danger"><i class="fa fa-times"></i> ' . $Estudiante['Usuario'] . ' (Inactivo)</span>';
                $botonUsuario = '';
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

            echo '<tr>
                    <td class="text-center">' . $contador . '</td>
                    <td class="text-center"><strong>' . $ciCompleto . '</strong></td>
                    <td>' . $nombreCompleto . '</td>
                    <td>' . $Estudiante['NombreProfesion'] . '</td>
                    <td>' . ($Estudiante['Correo'] ? $Estudiante['Correo'] : '<span class="text-muted">No registrado</span>') . '</td>
                    <td class="text-center">' . $Estudiante['Celular'] . '</td>
                    <td class="text-center">' . $estadoUsuarioBadge . '</td>
                    <td class="text-center">' . $botonUsuario . '</td>
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

        $DatosEstudiante = array(
            "Ci"              => htmlspecialchars(trim($_POST['Ci'])),
            "Complemento"     => htmlspecialchars(trim($_POST['Complemento'])),
            "Exp"             => htmlspecialchars(trim($_POST['Exp'])),
            "Nombre"          => htmlspecialchars(trim($_POST['Nombre'])),
            "Apaterno"        => htmlspecialchars(trim($_POST['Apaterno'])),
            "Amaterno"        => htmlspecialchars(trim($_POST['Amaterno'])),
            "FechaNacimiento" => htmlspecialchars(trim($_POST['FechaNacimiento'])),
            "Edad"            => (int)htmlspecialchars(trim($_POST['Edad'])),
            "Lugarn"          => htmlspecialchars(trim($_POST['Lugarn'])),
            "Correo"          => htmlspecialchars(trim($_POST['Correo'])),
            "IdProfesion"     => (int)$_POST["IdProfesion"],
            "Trabajo"         => htmlspecialchars(trim($_POST['Trabajo'])),
            "Direccion"       => htmlspecialchars(trim($_POST['Direccion'])),
            "Telefono"        => htmlspecialchars(trim($_POST['Telefono'])),
            "Celular"         => htmlspecialchars(trim($_POST['Celular'])),
        );

            $ciestudiante = $DatosEstudiante['Ci']; 
        $existe = EstudiantesModelos::BuscarEstudianteModelo($ciestudiante);

        if ($existe) {
            echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                  <script>
                  swal("ERROR!", "El estudiante ya esta registrado en el sistema", "error")
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

        // Validaciones mínimas
        if (empty($_POST["IdProfesion"]) || !is_numeric($_POST["IdProfesion"])) {
            echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                  <script>
                  swal("ERROR!", "Seleccione una profesión válida", "error")
                  .then(function () { location.href="inscripcion"; });
                  </script>';
            return;
        }

        $DatosEstudiante = array(
            "Ci"              => htmlspecialchars(trim($_POST['Ci'])),
            "Complemento"     => htmlspecialchars(trim($_POST['Complemento'])),
            "Exp"             => htmlspecialchars(trim($_POST['Exp'])),
            "Nombre"          => htmlspecialchars(trim($_POST['Nombre'])),
            "Apaterno"        => htmlspecialchars(trim($_POST['Apaterno'])),
            "Amaterno"        => htmlspecialchars(trim($_POST['Amaterno'])),
            "FechaNacimiento" => htmlspecialchars(trim($_POST['FechaNacimiento'])),
            "Edad"            => (int)htmlspecialchars(trim($_POST['Edad'])),
            "Lugarn"          => htmlspecialchars(trim($_POST['Lugarn'])),
            "Correo"          => htmlspecialchars(trim($_POST['Correo'])),
            "IdProfesion"     => (int)$_POST["IdProfesion"],
            "Trabajo"         => htmlspecialchars(trim($_POST['Trabajo'])),
            "Direccion"       => htmlspecialchars(trim($_POST['Direccion'])),
            "Telefono"        => htmlspecialchars(trim($_POST['Telefono'])),
            "Celular"         => htmlspecialchars(trim($_POST['Celular'])),
        );

            $ciestudiante = $DatosEstudiante['Ci']; 
        $existe = EstudiantesModelos::BuscarEstudianteModelo($ciestudiante);

        if ($existe) {
            echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                  <script>
                  swal("ERROR!", "El estudiante ya esta registrado en el sistema", "error")
                  .then(function () { location.href="inscripcion"; });
                  </script>';
            return;
        }

        // Verificar que la profesión existe en la tabla profesion (previene FK fail)
        $existeProf = ProfesionModelos::ObtenerPorId($DatosEstudiante['IdProfesion']);
        if (!$existeProf) {
            echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                  <script>
                  swal("ERROR!", "Profesión no válida (no existe)", "error")
                  .then(function () { location.href="inscripcion"; });
                  </script>';
            return;
        }

        $resultado = EstudiantesModelos::RegistrarEstudianteModelo($DatosEstudiante);

        if ($resultado === 'exitoso') {
            echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                  <script>
                  swal("EXITOSO!", "Se registro al estudiante", "success")
                  .then(function () { location.href="inscripcion"; });
                  </script>';
        } else {
            // Si $resultado trae mensaje de error, mostrarlo (útil para debugging)
            $msg = addslashes($resultado);
            echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                  <script>
                  swal("ERROR!", "No se registro al estudiante: ' . $msg . '", "error")
                  .then(function () { location.href="inscripcion"; });
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
