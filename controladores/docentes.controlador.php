<?php
class DocentesControlador
{
    /**
     * Convierte el arreglo de menciones del formulario ("Menciones[]") en
     * un único string separado por comas, listo para guardar en la BD.
     * No se fuerza a mayúsculas: se respeta la capitalización tal como se
     * escribió (ej. "M. Sc.", "Lic.").
     * Ej: ["M. Sc.", "Lic."] => "M. Sc., Lic."
     */
    private static function ProcesarMencionesControlador($menciones)
    {
        if (!is_array($menciones)) {
            return '';
        }

        $menciones = array_map(function ($mencion) {
            return htmlspecialchars(trim($mencion));
        }, $menciones);

        $menciones = array_filter($menciones, function ($mencion) {
            return $mencion !== '';
        });

        return implode(' ', $menciones);
    }

 // Cache en memoria del listado ya ordenado (más reciente primero), para no
 // repetir la consulta cuando la vista pide la tabla principal y la de "más docentes".
 private static $listaDocentesCache = null;

 private static function ObtenerListaDocentesCache()
 {
     if (self::$listaDocentesCache === null) {
         self::$listaDocentesCache = DocentesModelo::ListaDocenteActivoModelo();
     }
     return self::$listaDocentesCache;
 }

 /**
  * Imprime las filas <tr> de la tabla de docentes.
  * @param int $offset Cuántos registros más recientes saltar (0 = desde el primero)
  * @param int|null $limite Cuántos registros imprimir (null = todos los restantes)
  */
 public function ListaDocenteControlador($offset = 0, $limite = null)
{
    $Listad = self::ObtenerListaDocentesCache();
    $Listad = ($limite !== null) ? array_slice($Listad, $offset, $limite) : array_slice($Listad, $offset);
    $numero = $offset;

    foreach ($Listad as $Docente) {
        $numero++;
        echo $this->ConstruirFilaDocente($Docente, $numero);
    }
}

/**
 * Renderiza las filas <tr> de un arreglo de docentes ya obtenido (por ejemplo,
 * el resultado de una búsqueda), con numeración consecutiva desde $offsetNumeracion + 1.
 * @return string HTML concatenado de las filas
 */
public function RenderizarFilasDesdeArreglo($listaDocentes, $offsetNumeracion = 0)
{
    $html = '';
    $numero = $offsetNumeracion;
    foreach ($listaDocentes as $Docente) {
        $numero++;
        $html .= $this->ConstruirFilaDocente($Docente, $numero);
    }
    return $html;
}

/**
 * Busca docentes en TODA la base de datos (no solo los ya cargados en la
 * vista) y devuelve las filas ya renderizadas, con numeración consecutiva.
 */
public function BuscarDocentesControlador($patron)
{
    $patron = trim($patron);
    $listaDocentes = ($patron === '')
        ? self::ObtenerListaDocentesCache()
        : DocentesModelo::BuscarDocentesActivoModelo($patron);

    return array(
        'total' => count($listaDocentes),
        'html' => $this->RenderizarFilasDesdeArreglo($listaDocentes, 0),
    );
}

private function ConstruirFilaDocente($Docente, $numero)
{
        $menciones = !empty($Docente['Menciones']) ? $Docente['Menciones'] . ' ' : '';
        $nombreCompleto = $menciones . $Docente['Nombre'] . ' ' . $Docente['Apaterno'] . ' ' . $Docente['Amaterno'];
        $ciCompleto = $Docente['Ci'] . ($Docente['Complemento'] ? '-' . $Docente['Complemento'] : '') . ' ' . $Docente['Exp'];

        // Verificar si tiene usuario
        $tieneUsuario = !empty($Docente['Usuario']);
        $estadoUsuarioBadge = '';
        $botonUsuario = '';
        $botonBloqueo = '';
        $estadoUsuarioTexto = 'Sin usuario asignado';

        if ($tieneUsuario) {
            $estadoActivo = $Docente['EstadoUsuario'] == '1';
            $estadoUsuarioTexto = $estadoActivo ? 'Activo' : 'Bloqueado';
            $estadoUsuarioBadge = $estadoActivo
                ? '<span class="badge badge-success"><i class="fa fa-unlock"></i> Activo</span>'
                : '<span class="badge badge-danger"><i class="fa fa-lock"></i> Bloqueado</span>';

            // Bloquear/Habilitar el acceso del docente al sistema (no borra ni afecta
            // sus datos academicos, solo su capacidad de iniciar sesion).
            $botonBloqueo = $estadoActivo
                ? '<button type="button" class="btn btn-secondary btn-sm btnBloquearAccesoDocente" title="Bloquear acceso al sistema"
                        data-docente-id="' . $Docente['DocenteID'] . '"
                        data-nombre-completo="' . htmlspecialchars($nombreCompleto) . '"
                        data-nuevo-estado="0">
                        <i class="fa fa-lock"></i>
                    </button>'
                : '<button type="button" class="btn btn-success btn-sm btnBloquearAccesoDocente" title="Habilitar acceso al sistema"
                        data-docente-id="' . $Docente['DocenteID'] . '"
                        data-nombre-completo="' . htmlspecialchars($nombreCompleto) . '"
                        data-nuevo-estado="1">
                        <i class="fa fa-unlock"></i>
                    </button>';
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
                                <i class="fa fa-user-plus"></i>
                            </button>';
        }

        // Botón "Ver Detalle": agrupa correo, especialidad, cédula, usuario y
        // contraseña en un modal, para no saturar la tabla principal.
        $botonDetalle = '<button type="button" class="btn btn-info btn-sm btnVerDetalleDocente" title="Ver detalle"
                            data-toggle="modal" data-target="#ModalDetalleDocente"
                            data-docente-id="' . $Docente['DocenteID'] . '"
                            data-tiene-usuario="' . ($tieneUsuario ? '1' : '0') . '"
                            data-nombre-completo="' . htmlspecialchars($nombreCompleto) . '"
                            data-ci-completo="' . htmlspecialchars($ciCompleto) . '"
                            data-correo="' . htmlspecialchars($Docente['Correo'] ? $Docente['Correo'] : 'No registrado') . '"
                            data-especialidad="' . htmlspecialchars($Docente['Especialidad'] ?: 'No registrada') . '"
                            data-cedula="' . htmlspecialchars($Docente['CedulaProfesional'] ?: 'No registrada') . '"
                            data-matricula-nacional="' . htmlspecialchars($Docente['MatriculaNacional'] ?: 'No registrada') . '"
                            data-usuario="' . htmlspecialchars($tieneUsuario ? $Docente['Usuario'] : 'No asignado') . '"
                            data-estado-usuario="' . htmlspecialchars($estadoUsuarioTexto) . '">
                            <i class="fa fa-eye"></i>
                        </button>';

        return '<tr>
                <td class="text-center">' . $numero . '</td>
                <td class="text-center"><strong>' . htmlspecialchars($ciCompleto) . '</strong></td>
                <td>' . htmlspecialchars($nombreCompleto) . '</td>
                <td class="text-center">' . htmlspecialchars($Docente['MatriculaNacional'] ?: '-') . '</td>
                <td class="text-center">' . $estadoUsuarioBadge . '</td>
                <td class="text-center">
                    ' . $botonDetalle . '
                    ' . $botonUsuario . '
                    ' . $botonBloqueo . '
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
                            data-matricula-nacional="' . htmlspecialchars($Docente['MatriculaNacional'] ?? '') . '"
                            data-especialidad="' . htmlspecialchars($Docente['Especialidad']) . '"
                            data-menciones="' . htmlspecialchars($Docente['Menciones'] ?? '') . '"
                            data-direccion="' . htmlspecialchars($Docente['Direccion']) . '"
                            data-correo="' . htmlspecialchars($Docente['Correo']) . '"
                            data-tel="' . htmlspecialchars($Docente['Tel']) . '"
                            data-cel="' . htmlspecialchars($Docente['Cel']) . '"
                            data-es-extranjero="' . (isset($Docente['EsExtranjero']) ? $Docente['EsExtranjero'] : '0') . '"
                            data-pais="' . htmlspecialchars(isset($Docente['Pais']) ? $Docente['Pais'] : '') . '"
                            data-region="' . htmlspecialchars(isset($Docente['Region']) ? $Docente['Region'] : '') . '">
                        <i class="fa fa-edit"></i>
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
    public function RegistrarDocenteControlador()
    {
       if (isset($_POST["Ci"])) {
        
        // **IMPORTANTE:** Se unifican las claves del array $DatosEstudiante con los 'name' del formulario.
        $DatosDocente = array(
            "Ci"                => htmlspecialchars(trim($_POST['Ci'])),
            "Complemento"       => strtoupper(htmlspecialchars(trim($_POST['Complemento']))),
            "Exp"               => strtoupper(htmlspecialchars(trim($_POST['Exp'] ?? ''))),
            "Nombre"            => strtoupper(htmlspecialchars(trim($_POST['Nombre']))),
            "Apaterno"          => strtoupper(htmlspecialchars(trim($_POST['Apaterno']))),
            "Amaterno"          => strtoupper(htmlspecialchars(trim($_POST['Amaterno']))),
            "FechaNacimiento"   => htmlspecialchars(trim($_POST['FechaNacimiento'])),
            // Ya no es obligatoria; se guarda como NULL si queda vacía (tiene indice
            // UNICO en la BD, y NULL si permite repetirse entre varios docentes,
            // a diferencia de un texto vacio que chocaria con el segundo registro).
            "CedulaProfesional" => !empty(trim($_POST['CedulaProfesional'] ?? '')) ? strtoupper(htmlspecialchars(trim($_POST['CedulaProfesional']))) : null,
            "MatriculaNacional" => !empty(trim($_POST['MatriculaNacional'] ?? '')) ? strtoupper(htmlspecialchars(trim($_POST['MatriculaNacional']))) : null,
            "Especialidad"      => strtoupper(htmlspecialchars(trim($_POST['Especialidad']))),
            "Menciones"         => self::ProcesarMencionesControlador($_POST['Menciones'] ?? []),
            "Direccion"         => strtoupper(htmlspecialchars(trim($_POST['Direccion']))),
            "Correo"            => htmlspecialchars(trim($_POST['Correo'])), // Correo en minúsculas
            "Tel"               => htmlspecialchars(trim($_POST['Tel'])),
            "Cel"               => htmlspecialchars(trim($_POST['Cel'])),
            "EsExtranjero"      => isset($_POST['EsExtranjero']) && $_POST['EsExtranjero'] == '1' ? 1 : 0,
            "Pais"              => isset($_POST['Pais']) ? strtoupper(htmlspecialchars(trim($_POST['Pais']))) : null,
            "Region"            => isset($_POST['Region']) ? strtoupper(htmlspecialchars(trim($_POST['Region']))) : null,
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
                "Exp"               => strtoupper(htmlspecialchars(trim($_POST['editExp'] ?? ''))),
                "Nombre"            => strtoupper(htmlspecialchars(trim($_POST['editNombre']))),
                "Apaterno"          => strtoupper(htmlspecialchars(trim($_POST['editApaterno']))),
                "Amaterno"          => strtoupper(htmlspecialchars(trim($_POST['editAmaterno']))),
                "FechaNacimiento"   => htmlspecialchars(trim($_POST['editFechaNacimiento'])),
                "CedulaProfesional" => !empty(trim($_POST['editCedulaProfesional'] ?? '')) ? strtoupper(htmlspecialchars(trim($_POST['editCedulaProfesional']))) : null,
                "MatriculaNacional" => !empty(trim($_POST['editMatriculaNacional'] ?? '')) ? strtoupper(htmlspecialchars(trim($_POST['editMatriculaNacional']))) : null,
                "Especialidad"      => strtoupper(htmlspecialchars(trim($_POST['editEspecialidad']))),
                "Menciones"         => self::ProcesarMencionesControlador($_POST['editMenciones'] ?? []),
                "Direccion"         => strtoupper(htmlspecialchars(trim($_POST['editDireccion']))),
                "Correo"            => htmlspecialchars(trim($_POST['editCorreo'])), // Correo en minúsculas
                "Tel"               => htmlspecialchars(trim($_POST['editTel'])),
                "Cel"               => htmlspecialchars(trim($_POST['editCel'])),
                "EsExtranjero"      => isset($_POST['editEsExtranjero']) && $_POST['editEsExtranjero'] == '1' ? 1 : 0,
                "Pais"              => isset($_POST['editPais']) ? strtoupper(htmlspecialchars(trim($_POST['editPais']))) : null,
                "Region"            => isset($_POST['editRegion']) ? strtoupper(htmlspecialchars(trim($_POST['editRegion']))) : null
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

    /**
     * Bloquear o habilitar el acceso al sistema (login) de un Docente, sin
     * afectar sus datos academicos ni la asignacion de modulos.
     */
    public function CambiarEstadoUsuarioDocenteControlador()
    {
        if (isset($_POST["cambiarEstadoUsuarioDocente"])) {
            $docenteID = intval($_POST['docenteID']);
            $nuevoEstado = intval($_POST['nuevoEstado']) === 1 ? 1 : 0;

            $resultado = UsuarioModelos::CambiarEstadoUsuarioDocenteModelo($docenteID, $nuevoEstado);
            $accionTexto = $nuevoEstado === 1 ? 'habilitado' : 'bloqueado';

            if ($resultado == 'exitoso') {
                echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                      <script>
                      swal("EXITOSO!", "Acceso al sistema ' . $accionTexto . ' correctamente", "success")
                      .then(function () {
                          location.href="docentes";
                      });
                      </script>';
            } else {
                echo '<script src="vistas/recursos/sweetalert.min.js"></script>
                      <script>
                      swal("ERROR!", "No se pudo cambiar el acceso del docente", "error")
                      .then(function () {
                          location.href="docentes";
                      });
                      </script>';
            }
        }
    }

}
