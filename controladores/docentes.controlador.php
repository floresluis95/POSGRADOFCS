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
        $passwordDisplay = '';
        $botonUsuario = '';

        if ($tieneUsuario) {
            $estadoActivo = $Docente['EstadoUsuario'] == '1';
            $estadoUsuarioBadge = $estadoActivo
                ? '<span class="badge badge-success"><i class="fa fa-check"></i> ' . htmlspecialchars($Docente['Usuario']) . '</span>'
                : '<span class="badge badge-danger"><i class="fa fa-times"></i> ' . htmlspecialchars($Docente['Usuario']) . ' (Inactivo)</span>';

            // Mostrar contraseña si existe
            if (!empty($Docente['PasswordTexto'])) {
                $passwordDisplay = '<span class="badge badge-warning" style="font-size: 12px; font-family: monospace; cursor: pointer;"
                                          title="Contraseña asignada"
                                          onclick="copyToClipboard(\'' . htmlspecialchars($Docente['PasswordTexto']) . '\', this)">
                                        <i class="fa fa-key"></i> ' . htmlspecialchars($Docente['PasswordTexto']) . '
                                    </span>';
            } else {
                $passwordDisplay = '<span class="badge badge-secondary" title="Contraseña no disponible">
                                        <i class="fa fa-lock"></i> Oculta
                                    </span>';
            }
            $botonUsuario = '';
        } else {
            $estadoUsuarioBadge = '<span class="badge badge-warning"><i class="fa fa-exclamation-triangle"></i> Sin usuario</span>';
            $passwordDisplay = '<span class="badge badge-secondary"><i class="fa fa-minus"></i> N/A</span>';
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
                <td class="text-center">' . $passwordDisplay . '</td>
                <td class="text-center">
                    ' . $botonUsuario . '
                    <button type="button"
                            class="btn btn-warning btn-sm btnEditarDocente"
                            title="Editar"
                            data-toggle="modal"
                            data-target="#ModalEditarDocente"
                            data-docente-id="' . $Docente['DocenteID'] . '"
                            data-ci="' . $Docente['Ci'] . '"
                            data-complemento="' . htmlspecialchars($Docente['Complemento']) . '"
                            data-exp="' . htmlspecialchars($Docente['Exp']) . '"
                            data-nombre="' . htmlspecialchars($Docente['Nombre']) . '"
                            data-apaterno="' . htmlspecialchars($Docente['Apaterno']) . '"
                            data-amaterno="' . htmlspecialchars($Docente['Amaterno']) . '"
                            data-fecha-nacimiento="' . htmlspecialchars($Docente['FechaNacimiento']) . '"
                            data-cedula="' . htmlspecialchars($Docente['CedulaProfesional']) . '"
                            data-especialidad="' . htmlspecialchars($Docente['Especialidad']) . '"
                            data-direccion="' . htmlspecialchars($Docente['Direccion']) . '"
                            data-correo="' . htmlspecialchars($Docente['Correo']) . '"
                            data-tel="' . htmlspecialchars($Docente['Tel']) . '"
                            data-cel="' . htmlspecialchars($Docente['Cel']) . '">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="button"
                            class="btn btn-danger btn-sm btnDarDeBaja"
                            title="Dar de baja"
                            data-docente-id="' . $Docente['DocenteID'] . '"
                            data-nombre-completo="' . htmlspecialchars($nombreCompleto) . '">
                        <i class="fa fa-ban"></i>
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
            "Complemento"       => strtoupper(htmlspecialchars(trim($_POST['Complemento']))),
            "Exp"               => strtoupper(htmlspecialchars(trim($_POST['Exp']))),
            "Nombre"            => strtoupper(htmlspecialchars(trim($_POST['Nombre']))),
            "Apaterno"          => strtoupper(htmlspecialchars(trim($_POST['Apaterno']))),
            "Amaterno"          => strtoupper(htmlspecialchars(trim($_POST['Amaterno']))),
            "FechaNacimiento"   => htmlspecialchars(trim($_POST['FechaNacimiento'])),
            "CedulaProfesional" => strtoupper(htmlspecialchars(trim($_POST['CedulaProfesional']))),
            "Especialidad"      => strtoupper(htmlspecialchars(trim($_POST['Especialidad']))),
            "Direccion"         => strtoupper(htmlspecialchars(trim($_POST['Direccion']))),
            "Correo"            => htmlspecialchars(trim($_POST['Correo'])), // Correo en minúsculas
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
  

    /**
     * Editar Docente
     */
    public function EditarDocenteControlador()
    {
        if (isset($_POST["editarDocente"])) {
            $DatosDocente = array(
                "Ci"                => htmlspecialchars(trim($_POST['editCi'])),
                "Complemento"       => strtoupper(htmlspecialchars(trim($_POST['editComplemento']))),
                "Exp"               => strtoupper(htmlspecialchars(trim($_POST['editExp']))),
                "Nombre"            => strtoupper(htmlspecialchars(trim($_POST['editNombre']))),
                "Apaterno"          => strtoupper(htmlspecialchars(trim($_POST['editApaterno']))),
                "Amaterno"          => strtoupper(htmlspecialchars(trim($_POST['editAmaterno']))),
                "FechaNacimiento"   => htmlspecialchars(trim($_POST['editFechaNacimiento'])),
                "CedulaProfesional" => strtoupper(htmlspecialchars(trim($_POST['editCedulaProfesional']))),
                "Especialidad"      => strtoupper(htmlspecialchars(trim($_POST['editEspecialidad']))),
                "Direccion"         => strtoupper(htmlspecialchars(trim($_POST['editDireccion']))),
                "Correo"            => htmlspecialchars(trim($_POST['editCorreo'])), // Correo en minúsculas
                "Tel"               => htmlspecialchars(trim($_POST['editTel'])),
                "Cel"               => htmlspecialchars(trim($_POST['editCel']))
            );

            $resultado = DocentesModelo::EditarDocenteModelo($DatosDocente);

            if ($resultado == 'exitoso') {
                echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                      <script>
                      swal("EXITOSO!", "Datos del docente actualizados correctamente", "success")
                      .then(function () {
                          location.href="docentes";
                      });
                      </script>';
            } else {
                echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                      <script>
                      swal("ERROR!", "No se pudo actualizar los datos del docente", "error")
                      .then(function () {
                          location.href="docentes";
                      });
                      </script>';
            }
        }
    }

    /**
     * Dar de baja a Docente (solo si no tiene módulos asignados)
     */
    public function DarDeBajaDocenteControlador()
    {
        if (isset($_POST["darDeBajaDocente"])) {
            $docenteID = intval($_POST['docenteID']);

            // Verificar si tiene módulos asignados
            $tieneModulos = DocentesModelo::VerificarModulosAsignadosModelo($docenteID);

            if ($tieneModulos) {
                echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                      <script>
                      swal("NO SE PUEDE DAR DE BAJA!", "Este docente tiene módulos asignados actualmente. Debe reasignar o desactivar los módulos primero.", "warning")
                      .then(function () {
                          location.href="docentes";
                      });
                      </script>';
            } else {
                $resultado = DocentesModelo::DarDeBajaDocenteModelo($docenteID);

                if ($resultado == 'exitoso') {
                    echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                          <script>
                          swal("EXITOSO!", "Docente dado de baja correctamente", "success")
                          .then(function () {
                              location.href="docentes";
                          });
                          </script>';
                } else {
                    echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                          <script>
                          swal("ERROR!", "No se pudo dar de baja al docente", "error")
                          .then(function () {
                              location.href="docentes";
                          });
                          </script>';
                }
            }
        }
    }

}
