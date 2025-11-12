<?php
class EstudiantesControladores
{ 
    public function ListaEstudianteControladores()
    {     $Listae = EstudiantesModelos::ListaEstudianteActivoModelo();
            foreach ($Listae as $key => $Estudiante) 
          {
           echo '<tr>
                    <td WIDTH="30" HEIGHT="30">' . $Estudiante['EstudianteID'] . '</td>
                    <td >' . $Estudiante['Ci'] . ' ' . $Estudiante['Complemento'] . ' ' . $Estudiante['Exp'] . '</td>
                    <td>' . $Estudiante['Nombre'] . ' ' . $Estudiante['Apaterno'] . ' ' . $Estudiante['Amaterno'] . '</td>
                    <td >' . $Estudiante['Correo'] . '</td>
                    <td>' . $Estudiante['Celular'] . ' / ' . $Estudiante['Telefono'] . '</td>
                    <td>
                    <button type="button" class="btn btn-success"><i class="bi bi-card-checklist"></i></button>
                    </td>
                    <td>
                    <button type="button" class="btn btn-outline-info"><i class="bi bi-arrow-down-circle-fill"></i></button></td>
                    <td>
                    <button type="button" class="btn btn-outline-danger"><i class="bi bi-trash-fill"></i></button>
                    </td>         
                </tr>';
        }
    }
    public function ListarEstudiantesProgramasControladores()
    {
            $ListaEstudiantes = EstudiantesModelos::ListarEstudianteProgramaModelo();
            foreach ($ListaEstudiantes as $key => $Estudiante) 
        {
           echo '<tr>
                    <td WIDTH="30" HEIGHT="30">' . $Estudiante['EstudianteID'] . '</td>
                    <td >' . $Estudiante['Ci'] . ' ' . $Estudiante['Complemento'] . ' ' . $Estudiante['Exp'] . '</td>
                    <td>' . $Estudiante['Nombre'] . ' ' . $Estudiante['Apaterno'] . ' ' . $Estudiante['Amaterno'] . '</td>
                    <td WIDTH="30" HEIGHT="30">' . $Estudiante['Celular'] . ' / ' . $Estudiante['Telefono'] . '</td>
                    <td>' . $Estudiante['NombrePrograma'] . '</td>
                    <td>' . $Estudiante['FechaInscripcion'] . '</td>
                </tr>';
        }
    }
public function RegistarEstudianteControlador()
{
    // Verificar que la solicitud sea un POST y que al menos el campo CI esté presente.
    if (isset($_POST["Ci"])) {
        
        // **IMPORTANTE:** Se unifican las claves del array $DatosEstudiante con los 'name' del formulario.
        $DatosEstudiante = array(
            "Ci"                => htmlspecialchars(trim($_POST['Ci'])), // Mejor como string
            "Complemento"       => htmlspecialchars(trim($_POST['Complemento'])),
            "Exp"               => htmlspecialchars(trim($_POST['Exp'])),
            "Nombre"            => htmlspecialchars(trim($_POST['Nombre'])), // Corregido: Usar 'Nombre'
            "Apaterno"          => htmlspecialchars(trim($_POST['Apaterno'])),
            "Amaterno"          => htmlspecialchars(trim($_POST['Amaterno'])),
            "FechaNacimiento"   => htmlspecialchars(trim($_POST['FechaNacimiento'])),
            "Edad"            => htmlspecialchars(trim($_POST['Edad'])),
            "Lugarn"            => htmlspecialchars(trim($_POST['Lugarn'])),
            "Correo"            => htmlspecialchars(trim($_POST['Correo'])),
            "Profesion"            => htmlspecialchars(trim($_POST['Profesion'])),
            "Trabajo"            => htmlspecialchars(trim($_POST['Trabajo'])),
            "Direccion"         => htmlspecialchars(trim($_POST['Direccion'])), // Corregido: Usar 'direccion'
            "Telefono"          => htmlspecialchars(trim($_POST['Telefono'])), // Corregido: Usar 'telefono' (sin tilde, minúscula)
            "Celular"           => htmlspecialchars(trim($_POST['Celular'])), // Mejor como string
         
        );
        
        // Uso de la variable del formulario para la búsqueda, asegurando el mismo formato.
        $ciestudiante = $DatosEstudiante['Ci']; 

        // Asume que la función BuscarEstudianteModelo existe.
        $existe = EstudiantesModelos::BuscarEstudianteModelo($ciestudiante);

        if ($existe == true) {
            echo'
                            <script src="vistas/recursos/sweetalert.min.js"></script>
                            <script>
                            swal("ERROR!", "El estudiante ya esta registrado en el sistema", "error")
                            .then(function () {
                                location.href="estudiantes";
                            })
                            ;
                            </script>';
        } else {
            // Llama a la función del modelo con el array de datos
            $resultado = EstudiantesModelos::RegistrarEstudianteModelo($DatosEstudiante);
            
             if ($resultado == 'exitoso')
                            {
                                echo'
                                <script src="vistas/recursos/sweetalert.min.js"></script>
                                <script>
                                swal("EXITOSO!", "Se registro al estudiante", "success")
                                .then(function () {
                                    location.href="estudiantes";
                                })
                                ;
                                </script>';  
                            }
                        else
                            {
                               echo'
                            <script src="vistas/recursos/sweetalert.min.js"></script>
                            <script>
                            swal("ERROR!", "No se registro al estudiante", "error")
                            .then(function () {
                                location.href="estudiantes";
                            })
                            ;
                            </script>';
                            }
        }
    }
}
public function EstudianteActivoControlador()
{
    $TraerListaEstudianteActivo = EstudiantesModelos::ListaEstudianteActivoModelo();
        foreach ($TraerListaEstudianteActivo as $key =>$value)
        {
            $i++;
            echo '<option value="' . $value["Ci"] . '"> ' . $value["Ci"] . ' -> ' . $value["Nombre"] . ' ' . $value["Apaterno"] . ' ' . $value["Amaterno"] . ' </option>';

        }
}
public function RegistarEstudianteControlador2()
{
    // Verificar que la solicitud sea un POST y que al menos el campo CI esté presente.
    if (isset($_POST["Ci"])) {
        
        // **IMPORTANTE:** Se unifican las claves del array $DatosEstudiante con los 'name' del formulario.
        $DatosEstudiante = array(
            "Ci"                => htmlspecialchars(trim($_POST['Ci'])), // Mejor como string
            "Complemento"       => htmlspecialchars(trim($_POST['Complemento'])),
            "Exp"               => htmlspecialchars(trim($_POST['Exp'])),
            "Nombre"            => htmlspecialchars(trim($_POST['Nombre'])), // Corregido: Usar 'Nombre'
            "Apaterno"          => htmlspecialchars(trim($_POST['Apaterno'])),
            "Amaterno"          => htmlspecialchars(trim($_POST['Amaterno'])),
            "FechaNacimiento"   => htmlspecialchars(trim($_POST['FechaNacimiento'])),
            "Correo"            => htmlspecialchars(trim($_POST['Correo'])),
            "Direccion"         => htmlspecialchars(trim($_POST['Direccion'])), // Corregido: Usar 'direccion'
            "Telefono"          => htmlspecialchars(trim($_POST['Telefono'])), // Corregido: Usar 'telefono' (sin tilde, minúscula)
            "Celular"           => htmlspecialchars(trim($_POST['Celular'])), // Mejor como string
         
        );
        
        // Uso de la variable del formulario para la búsqueda, asegurando el mismo formato.
        $ciestudiante = $DatosEstudiante['Ci']; 

        // Asume que la función BuscarEstudianteModelo existe.
        $existe = EstudiantesModelos::BuscarEstudianteModelo($ciestudiante);

        if ($existe == true) {
            echo'
                            <script src="vistas/recursos/sweetalert.min.js"></script>
                            <script>
                            swal("ERROR!", "El estudiante ya esta registrado en el sistema", "error")
                            .then(function () {
                                location.href="inscripcion";
                            })
                            ;
                            </script>';
        } else {
            // Llama a la función del modelo con el array de datos
            $resultado = EstudiantesModelos::RegistrarEstudianteModelo2($DatosEstudiante);
            
             if ($resultado == 'exitoso')
                            {
                                echo'
                                <script src="vistas/recursos/sweetalert.min.js"></script>
                                <script>
                                swal("EXITOSO!", "Se registro al estudiante", "success")
                                .then(function () {
                                    location.href="inscripcion";
                                })
                                ;
                                </script>';  
                            }
                        else
                            {
                               echo'
                            <script src="vistas/recursos/sweetalert.min.js"></script>
                            <script>
                            swal("ERROR!", "No se registro al estudiante", "error")
                            .then(function () {
                                location.href="inscripcion";
                            })
                            ;
                            </script>';
                            }
        }
    }
}
public function EstadoEstudianteControlador()
{
     if (isset($_POST["Ci"]))
            {
                $idusr = $_POST["Ci"];
                $ed = UsuarioModelos::CambiarEstadoUsuario($Ci);
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
}

class ProfesionControlador {
    public function ListaProfesionControlador() {
        $ListaProfesion = ProfesionModelos::ListaprofesionModelos();

        if ($ListaProfesion && count($ListaProfesion) > 0) {
            foreach ($ListaProfesion as $value) {
                echo '<option value="'.$value["IdProfesion"].'">'.$value["NombreProfesion"].'</option>';
                
            }
        } else {
            echo '<option value="">⚠️ No se encontraron profesiones</option>';
        }
    }
}