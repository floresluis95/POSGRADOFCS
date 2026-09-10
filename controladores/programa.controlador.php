<?php

class ProgramasControladores
{
    /**
     * Valida los datos de un programa (registro o edición).
     * $sufijo permite reusar la validación en el formulario de edición,
     * cuyos campos tienen el mismo nombre + "Editar" (ej. NombreProgramaEditar).
     * @return string|null Mensaje de error, o null si los datos son válidos.
     */
    private function ValidarDatosProgramaControlador($sufijo = '')
    {
        $camposObligatorios = [
            'NombrePrograma' => 'Nombre del programa',
            'GradoAcademico' => 'Grado académico',
            'DuracionMeses' => 'Duración en meses',
            'Modulos' => 'Número de módulos',
            'FechaInicio' => 'Fecha de inicio',
            'Sede' => 'Sede',
            'Costo' => 'Costo',
            'CostoMatricula' => 'Costo de matrícula',
        ];

        foreach ($camposObligatorios as $campo => $etiqueta) {
            $key = $campo . $sufijo;
            if (!isset($_POST[$key]) || trim($_POST[$key]) === '') {
                return "El campo \"$etiqueta\" es obligatorio";
            }
        }

        if (!is_numeric($_POST['DuracionMeses' . $sufijo]) || (int)$_POST['DuracionMeses' . $sufijo] <= 0) {
            return 'La duración en meses debe ser un número mayor a 0';
        }

        if (!is_numeric($_POST['Modulos' . $sufijo]) || (int)$_POST['Modulos' . $sufijo] <= 0) {
            return 'El número de módulos debe ser un número mayor a 0';
        }

        if (!is_numeric($_POST['Costo' . $sufijo]) || (float)$_POST['Costo' . $sufijo] <= 0) {
            return 'El costo debe ser un número mayor a 0';
        }

        if (!is_numeric($_POST['CostoMatricula' . $sufijo]) || (float)$_POST['CostoMatricula' . $sufijo] < 0) {
            return 'El costo de matrícula debe ser un número válido';
        }

        $fecha = trim($_POST['FechaInicio' . $sufijo]);
        $fechaValida = DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$fechaValida || $fechaValida->format('Y-m-d') !== $fecha) {
            return 'La fecha de inicio no es válida (formato esperado: AAAA-MM-DD)';
        }

        return null;
    }

    private function MostrarErrorValidacionPrograma($mensaje)
    {
        echo '
        <script src="vistas/recursos/sweetalert.min.js"></script>
        <script>
        swal("ERROR!", "' . addslashes($mensaje) . '", "error")
        .then(function () {
            location.href="programas";
        })
        ;
        </script>';
    }


    public function ListaProgramaControlador($grado = null, $fechaInicio = null, $fechaFin = null)
    {
        // Debug activado - ver en el código fuente HTML (Ctrl+U)
        echo "<!-- DEBUG CONTROLADOR: Grado='$grado', FechaInicio='$fechaInicio', FechaFin='$fechaFin' -->";

        // Si hay filtros, usar búsqueda con filtros, sino listar todos
        if ($grado !== null || $fechaInicio !== null || $fechaFin !== null) {
            echo "<!-- Usando BuscarProgramasConFiltros -->";
            $listaprogramas = ProgramasModelos::BuscarProgramasConFiltros($grado, $fechaInicio, $fechaFin);
        } else {
            echo "<!-- Usando ListaProgramaModelo (sin filtros) -->";
            $listaprogramas = ProgramasModelos::ListaProgramaModelo();
        }

        // Debug: mostrar cantidad de resultados
        echo "<!-- Total programas encontrados: ".count($listaprogramas)." -->";

        // Si no hay resultados, mostrar mensaje
        if (empty($listaprogramas)) {
            echo '<tr>
                    <td colspan="12" class="text-center" style="padding: 40px;">
                        <i class="fa fa-search" style="font-size: 48px; color: #ccc;"></i>
                        <h4 style="color: #999; margin-top: 20px;">No se encontraron programas con los filtros seleccionados</h4>
                        <p style="color: #999;">Intenta cambiar los criterios de búsqueda</p>
                    </td>
                  </tr>';
            return;
        }

      foreach ($listaprogramas as $key => $programa) 
{
 
    // Calcular el estado dinámico del programa
    $fechaActual = date('Y-m-d');
    $fechaInicioProg = $programa["FechaInicio"];
    $duracionMeses = $programa["DuracionMeses"];

    // Calcular fecha de fin
    $fechaFin = date('Y-m-d', strtotime($fechaInicioProg . " + $duracionMeses months"));

    $estadoPrograma = '';
    $colorEstado = '';
    $iconoEstado = '';

    // Determinar estado basado en el campo Estado de la BD y las fechas
    if ($programa["Estado"] == 'INACTIVO' || $programa["Estado"] == 0) {
        $estadoPrograma = 'INACTIVO';
        $colorEstado = '#dc3545'; // rojo
        $iconoEstado = '<i class="fa fa-times-circle"></i>';
    } elseif ($fechaActual < $fechaInicioProg) {
        $estadoPrograma = 'ACTIVO';
        $colorEstado = '#28a745'; // verde
        $iconoEstado = '<i class="fa fa-check-circle"></i>';
    } elseif ($fechaActual >= $fechaInicioProg && $fechaActual <= $fechaFin) {
        $estadoPrograma = 'EN CURSO';
        $colorEstado = '#007bff'; // azul
        $iconoEstado = '<i class="fa fa-play-circle"></i>';
    } else {
        $estadoPrograma = 'CONCLUIDO';
        $colorEstado = '#6c757d'; // gris
        $iconoEstado = '<i class="fa fa-flag-checkered"></i>';
    }

    echo '<tr style="border-bottom: 2px solid #e9ecef;">
        <td class="text-center align-middle"><strong>'.$programa['ProgramaID'].'</strong></td>
        <td class="align-middle"><strong>'.$programa["NombrePrograma"].'</strong></td>
        <td class="text-center align-middle"><span class="'.FuncionesControladores::ClaseGrado($programa["GradoAcademico"]).'">'.$programa["GradoAcademico"].'</span></td>
        <td class="text-center align-middle"><span class="badge badge-secondary">'.$programa["Codigo"].'</span></td>
        <td class="text-center align-middle">'.date("d/m/Y", strtotime($programa["FechaInicio"])).'</td>
        <td class="text-center align-middle">'.$programa["Sede"].'</td>';

    // Campo ESTADO con diseño mejorado
    echo '<td class="text-center align-middle">
            <span class="badge" style="background-color: '.$colorEstado.'; color: white; padding: 8px 12px; font-size: 11px;">
                '.$iconoEstado.' '.$estadoPrograma.'
            </span>
          </td>';

    // Botón DETALLE
    echo '<td class="text-center align-middle">
        <button type="button" id="btnDetallePrograma" idcod="'.$programa['ProgramaID'].'"
            class="btn btn-info btn-sm" title="Ver Detalle"
            data-toggle="modal" data-target="#DetallePrograma">
            <i class="fa fa-eye"></i>
        </button>
    </td>';

    // Botón CUENTAS (administrar cuentas bancarias del programa por tipo)
    echo '<td class="text-center align-middle">
        <button type="button" id="btnCuentasPrograma" idcod="'.$programa['ProgramaID'].'"
            data-idprograma="'.$programa['ProgramaID'].'"
            data-nombreprograma="'.htmlspecialchars($programa['NombrePrograma']).'"
            class="btn btn-secondary btn-sm btnAbrirCuentasPrograma" title="Administrar cuentas"
            data-toggle="modal" data-target="#gestionarCuentas">
            <i class="fa fa-university"></i>
        </button>
    </td>';

    // Botón EDITAR
    echo '<td class="text-center align-middle">
        <button type="button" class="btn btn-warning btn-sm btnEditarPrograma" title="Editar Programa"
            data-idprograma="'.$programa['ProgramaID'].'"
            data-nombre="'.$programa['NombrePrograma'].'"
            data-grado="'.$programa['GradoAcademico'].'"
            data-duracion="'.$programa['DuracionMeses'].'"
            data-modulos="'.$programa['Modulos'].'"
            data-fecha="'.$programa['FechaInicio'].'"
            data-sede="'.$programa['Sede'].'"
            data-costo="'.$programa['Costo'].'"
            data-costomatricula="'.(isset($programa['CostoMatricula']) ? $programa['CostoMatricula'] : '').'"
            data-detalle="'.htmlspecialchars($programa['Detalle']).'"
            data-version="'.(isset($programa['Version']) ? $programa['Version'] : 'V-1').'"
            data-numerotramite="'.(isset($programa['NumeroTramite']) ? $programa['NumeroTramite'] : '').'"
            data-toggle="modal" data-target="#modalEditarPrograma">
            <i class="fa fa-edit"></i>
        </button>
    </td>';

    // Botón SUBIR (activar programa) - solo si está inactivo
    echo '<td class="text-center align-middle">';
    if ($programa["Estado"] == 'ACTIVO' || $programa["Estado"] == 1) {
        echo '<button type="button" class="btn btn-success btn-sm" disabled title="Programa Activo">
                <i class="fa fa-check"></i>
              </button>';
    } else {
        echo '<button data-toggle="modal" data-target="#modalsubir" type="button"
                data-idprograma="'.$programa['ProgramaID'].'"
                class="btn btn-success btn-sm btnSubir" title="Activar Programa">
                <i class="fa fa-arrow-up"></i>
              </button>';
    }
    echo '</td>';

    // Botón DAR DE BAJA (desactivar programa) - solo si está activo
    echo '<td class="text-center align-middle">';
    if ($programa["Estado"] == 'INACTIVO' || $programa["Estado"] == 0) {
        echo '<button type="button" class="btn btn-danger btn-sm" disabled title="Programa Inactivo">
                <i class="fa fa-times"></i>
              </button>';
    } else {
        echo '<button data-toggle="modal" data-target="#modalBorrar" type="button"
                data-idprograma="'.$programa['ProgramaID'].'"
                class="btn btn-danger btn-sm btnDarBaja" title="Dar de Baja">
                <i class="fa fa-arrow-down"></i>
              </button>';
    }
    echo '</td>';
     

        echo '</tr>';
   
        
     }
        
          
        }
        

    public function RegistrarProgramaControlador()
    {
        if (isset($_POST["NombrePrograma"]))
        {
                $errorValidacion = $this->ValidarDatosProgramaControlador();
                if ($errorValidacion !== null) {
                    $this->MostrarErrorValidacionPrograma($errorValidacion);
                    return;
                }

                $datosPrograma =array(
                "NombrePrograma" => strtoupper(htmlspecialchars(trim($_POST['NombrePrograma']))), // Sanitizar y convertir a mayúsculas
                "GradoAcademico" => strtoupper(htmlspecialchars(trim($_POST['GradoAcademico']))),
                "DuracionMeses" => (int)$_POST['DuracionMeses'],
                "Modulos" => (int)$_POST['Modulos'], // Forzar tipo entero
                "FechaInicio" => htmlspecialchars(trim($_POST['FechaInicio'])),
                "Sede" => strtoupper(htmlspecialchars(trim($_POST['Sede']))),
                "Costo" => (float)$_POST['Costo'], // Forzar tipo flotante/decimal
                "CostoMatricula" => (float)$_POST['CostoMatricula'], // Costo de matrícula
                "Detalle" => strtoupper(htmlspecialchars(trim($_POST['Detalle']))),
                "Version" => strtoupper(htmlspecialchars(trim($_POST['Version'] ?? 'V-1'))), // Versión del programa
                "NumeroTramite" => strtoupper(htmlspecialchars(trim($_POST['NumeroTramite'] ?? ''))) // Cuenta del programa
                );
                $nombreprograma = $datosPrograma['NombrePrograma']; // Ya normalizado (mayúsculas/trim) para comparar correctamente
                $existe = ProgramasModelos::Buscarprogramamodelo($nombreprograma);
                if ($existe==true)
                    {
                            echo'
                            <script src="vistas/recursos/sweetalert.min.js"></script>
                            <script>
                            swal("ERROR!", "El programa ya se creo", "error")
                            .then(function () {
                                location.href="programas";
                            })
                            ;
                            </script>';
                    }
                else
                    {   
                        $resultado = ProgramasModelos::RegistrarProgramaModelo($datosPrograma);
                        if ($resultado == 'exitoso')
                            {
                                echo'
                                <script src="vistas/recursos/sweetalert.min.js"></script>
                                <script>
                                swal("EXITOSO!", "Se registro el programa", "success")
                                .then(function () {
                                    location.href="programas";
                                })
                                ;
                                </script>';  
                            }
                        else
                            {
                               echo'
                            <script src="vistas/recursos/sweetalert.min.js"></script>
                            <script>
                            swal("ERROR!", "No se registro el programa", "error")
                            .then(function () {
                                location.href="programas";
                            })
                            ;
                            </script>';
                            }
                    }
        }
    }
    public function ListaProgramaActivoControlador()
    {
        $TraerListaProgramaActivo = ProgramasModelos::ListaProgramaActivoModelo();
        foreach ($TraerListaProgramaActivo as $key =>$value)
        {
            $i++;
            echo '<option value="'.$value["ProgramaID"].'">'.$value["NombrePrograma"]. '</option>';
        
        }

    }

    // Mostrar detalle en panel
    public static function MostrarDetalleProgramaControlador($id) {
    $detalle = ProgramasModelos::MostrarDetallePrograma($id);
    return $detalle;
    }

    // Actualizar programa existente
    public function ActualizarProgramaControlador()
    {
        if (isset($_POST["ProgramaIDEditar"]))
        {
            $errorValidacion = $this->ValidarDatosProgramaControlador('Editar');
            if ($errorValidacion !== null) {
                $this->MostrarErrorValidacionPrograma($errorValidacion);
                return;
            }

            $datosPrograma = array(
                "ProgramaID" => (int)$_POST['ProgramaIDEditar'],
                "NombrePrograma" => strtoupper(htmlspecialchars(trim($_POST['NombreProgramaEditar']))),
                "GradoAcademico" => strtoupper(htmlspecialchars(trim($_POST['GradoAcademicoEditar']))),
                "DuracionMeses" => (int)$_POST['DuracionMesesEditar'],
                "Modulos" => (int)$_POST['ModulosEditar'],
                "FechaInicio" => htmlspecialchars(trim($_POST['FechaInicioEditar'])),
                "Sede" => strtoupper(htmlspecialchars(trim($_POST['SedeEditar']))),
                "Costo" => (float)$_POST['CostoEditar'],
                "CostoMatricula" => (float)$_POST['CostoMatriculaEditar'],
                "Detalle" => strtoupper(htmlspecialchars(trim($_POST['DetalleEditar']))),
                "Version" => strtoupper(htmlspecialchars(trim($_POST['VersionEditar'] ?? 'V-1'))),
                "NumeroTramite" => strtoupper(htmlspecialchars(trim($_POST['NumeroTramiteEditar'] ?? '')))
            );

            $resultado = ProgramasModelos::ActualizarProgramaModelo($datosPrograma);

            if ($resultado == 'exitoso')
            {
                echo'
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("EXITOSO!", "Se actualizó el programa correctamente", "success")
                .then(function () {
                    location.href="programas";
                })
                ;
                </script>';
            }
            else
            {
                echo'
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "No se pudo actualizar el programa", "error")
                .then(function () {
                    location.href="programas";
                })
                ;
                </script>';
            }
        }
    }
}

class ProgramaEstadoControlador
{
   public function SubirProgramaControlador()
{
    if (isset($_POST["idProgramaSubir"])) {
        $id = $_POST["idProgramaSubir"];

        $respuesta = ProgramaEstadoModelo::SubirProgramaModelo($id);

        if ($respuesta == "exitoso") {
            echo '
            <script src="vistas/recursos/sweetalert.min.js"></script>
            <script>
            swal("EXITOSO!", "El programa fue activado correctamente", "success")
            .then(function () {
                location.href="programas";
            });
            </script>';
        } else {
            echo '
            <script src="vistas/recursos/sweetalert.min.js"></script>
            <script>
            swal("ERROR!", "No se pudo activar el programa", "error")
            .then(function () {
                location.href="programas";
            });
            </script>';
        }
    }
}
    public function BajarProgramaControlador() {
        if (isset($_POST["idProgramaBaja"])) {
            
            $id = $_POST["idProgramaBaja"];
            
            $respuesta = ProgramaEstadoModelo::BajarProgramaModelo($id);

            if ($respuesta == "exitoso") {
                echo '
                                <script src="vistas/recursos/sweetalert.min.js"></script>
                                <script>
                                swal("EXITOSO!", "El programa ya no esta disponible", "success")
                                .then(function () {
                                    location.href="programas";
                                })
                                ;
                                </script>';
            } else {
                 echo'
                            <script src="vistas/recursos/sweetalert.min.js"></script>
                            <script>
                            swal("ERROR!", "No se modifico el programa", "error")
                            .then(function () {
                                location.href="programas";
                            })
                            ;
                            </script>';
            }
        }
    }
}


