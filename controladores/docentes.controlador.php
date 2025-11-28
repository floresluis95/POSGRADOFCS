<?php
class DocentesControlador
{
 public function ListaDocenteControlador()
{
    $Listad = DocentesModelo::ListaDocenteActivoModelo();
    $contador = 0;

    foreach ($Listad as $key => $Docente) {
        $contador++;

        $nombreCompleto = $Docente['Apaterno'] . ' ' . $Docente['Amaterno'] . ' ' . $Docente['Nombre'];
        $ciCompleto = $Docente['Ci'] . ($Docente['Complemento'] ? '-' . $Docente['Complemento'] : '') . ' ' . $Docente['Exp'];

        // Verificar si tiene usuario
        $tieneUsuario = !empty($Docente['Usuario']);
        $estadoUsuarioBadge = '';
        $botonUsuario = '';

        if ($tieneUsuario) {
            $estadoActivo = $Docente['EstadoUsuario'] == '1';
            $estadoUsuarioBadge = $estadoActivo
                ? '<span class="badge badge-success"><i class="fa fa-check"></i> ' . htmlspecialchars($Docente['Usuario']) . '</span>'
                : '<span class="badge badge-danger"><i class="fa fa-times"></i> ' . htmlspecialchars($Docente['Usuario']) . ' (Inactivo)</span>';
            $botonUsuario = '';
        } else {
            $estadoUsuarioBadge = '<span class="badge badge-warning"><i class="fa fa-exclamation-triangle"></i> Sin usuario</span>';
            $botonUsuario = '<button
                                data-toggle="modal"
                                data-target="#ModalAsignarUsuarioDocente"
                                type="button"
                                class="btn btn-success btn-sm btnAsignarUsuarioDocente"
                                data-docente-id="' . $Docente['DocenteID'] . '"
                                data-ci="' . $Docente['Ci'] . '"
                                data-ci-completo="' . htmlspecialchars($ciCompleto) . '"
                                data-nombre-completo="' . htmlspecialchars($nombreCompleto) . '"
                                data-nombre-pila="' . htmlspecialchars($Docente['Nombre']) . '"
                                data-correo="' . htmlspecialchars($Docente['Correo'] ? $Docente['Correo'] : '') . '"
                                title="Asignar usuario">
                                <i class="fa fa-user-plus"></i> Asignar
                            </button>';
        }

        echo '<tr>
                <td class="text-center">' . $contador . '</td>
                <td class="text-center"><strong>' . htmlspecialchars($ciCompleto) . '</strong></td>
                <td>' . htmlspecialchars($nombreCompleto) . '</td>
                <td>' . htmlspecialchars($Docente['CedulaProfesional']) . '</td>
                <td>' . htmlspecialchars($Docente['Correo'] ? $Docente['Correo'] : 'No registrado') . '</td>
                <td>' . htmlspecialchars($Docente['Especialidad']) . '</td>
                <td class="text-center">' . $estadoUsuarioBadge . '</td>
                <td class="text-center">
                    ' . $botonUsuario . '
                    <button type="button" class="btn btn-info btn-sm" title="Ver detalle">
                        <i class="bi bi-card-checklist"></i>
                    </button>
                    <button type="button" class="btn btn-warning btn-sm" title="Editar">
                        <i class="bi bi-pencil-fill"></i>
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
