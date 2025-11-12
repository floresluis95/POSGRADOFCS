<?php
class DocentesControlador
{
 public function ListaDocenteControlador() 
{
    $Listad = DocentesModelo::ListaDocenteModelo();

    foreach ($Listad as $key => $Docente) {
        echo '<tr>
                <td width="30" height="30">' . htmlspecialchars($Docente['DocenteID']) . '</td>
                <td>' . htmlspecialchars($Docente['Ci'] . ' ' . $Docente['Complemento'] . ' ' . $Docente['Exp']) . '</td>
                <td>' . htmlspecialchars($Docente['Nombre'] . ' ' . $Docente['Apaterno'] . ' ' . $Docente['Amaterno']) . '</td>
                <td>' . htmlspecialchars($Docente['CedulaProfesional']) . '</td>
                <td>' . htmlspecialchars($Docente['Correo']) . '</td>
                <td>' . htmlspecialchars($Docente['Especialidad']) . '</td>
                <td>
                    <button type="button" class="btn btn-success btn-sm" title="Ver detalle">
                        <i class="bi bi-card-checklist"></i>
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm" title="Descargar">
                        <i class="bi bi-arrow-down-circle-fill"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" title="Eliminar">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </td>
            </tr>';
    }
}
    public function RegistrarDocenteControlador()
    {
       if (isset($_POST["Ci"])) {
        
        // **IMPORTANTE:** Se unifican las claves del array $DatosEstudiante con los 'name' del formulario.
        $DatosDocente = array(
            "Ci"                => htmlspecialchars(trim($_POST['Ci'])), 
            "Complemento"       => htmlspecialchars(trim($_POST['Complemento'])),
            "Exp"               => htmlspecialchars(trim($_POST['Exp'])),
            "Nombre"            => htmlspecialchars(trim($_POST['Nombre'])), 
            "Apaterno"          => htmlspecialchars(trim($_POST['Apaterno'])),
            "Amaterno"          => htmlspecialchars(trim($_POST['Amaterno'])),
            "FechaNacimiento"   => htmlspecialchars(trim($_POST['FechaNacimiento'])),
            "CedulaProfesional" => htmlspecialchars(trim($_POST['CedulaProfesional'])),
            "Especialidad"      => htmlspecialchars(trim($_POST['Especialidad'])),
            "Direccion"         => htmlspecialchars(trim($_POST['Direccion'])),
            "Correo"            => htmlspecialchars(trim($_POST['Correo'])),
            "Tel"          => htmlspecialchars(trim($_POST['Tel'])), 
            "Cel"           => htmlspecialchars(trim($_POST['Cel'])), 
        );
        
        
        $Ci = $DatosDocente['Ci']; 
        // Asume que la función Buscardocente existe.
        $existe = DocentesModelo::BuscarDocenteModelo($Ci);

        if ($existe == true) {
            echo'
                            <script src="vistas/recursos/sweetalert.min.js"></script>
                            <script>
                            swal("ERROR!", "El Docente ya esta registrado en el sistema", "error")
                            .then(function () {
                                location.href="docentes";
                            })
                            ;
                            </script>';
        } else {
            // Llama a la función del modelo con el array de datos
            $resultado = DocentesModelo::RegistrarDocenteModelo($DatosDocente);
            
             if ($resultado == 'exitoso')
                            {
                                echo'
                                <script src="vistas/recursos/sweetalert.min.js"></script>
                                <script>
                                swal("EXITOSO!", "Se registro al Docente", "success")
                                .then(function () {
                                    location.href="docentes";
                                })
                                ;
                                </script>';  
                            }
                        else
                            {
                               echo'
                            <script src="vistas/recursos/sweetalert.min.js"></script>
                            <script>
                            swal("ERROR!", "No se registro al Docente", "error")
                            .then(function () {
                                location.href="docentes";
                            })
                            ;
                            </script>';
                            }
        }
    }
    }
  
    
}
