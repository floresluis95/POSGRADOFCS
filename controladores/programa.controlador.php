<?php

// El servidor tiene otra zona horaria configurada en php.ini; sin esto, el
// estado del programa (ACTIVO/EN CURSO/CONCLUIDO) se calcula con la fecha
// equivocada al compararla contra hoy.
date_default_timezone_set('America/La_Paz');

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


    /**
     * Estado de un programa según su campo Estado y sus fechas:
     * Inactivo (dado de baja), Por iniciar, En curso o Concluido.
     * @return array{clave:string, etiqueta:string, icono:string}
     */
    public static function EstadoProgramaControlador($programa)
    {
        if ($programa['Estado'] == 'INACTIVO' || $programa['Estado'] == 0) {
            return ['clave' => 'inactivo', 'etiqueta' => 'Inactivo', 'icono' => 'fa-times-circle'];
        }
        $hoy = date('Y-m-d');
        $inicio = $programa['FechaInicio'];
        $fin = date('Y-m-d', strtotime($inicio . ' + ' . (int)$programa['DuracionMeses'] . ' months'));
        if ($hoy < $inicio) {
            return ['clave' => 'por-iniciar', 'etiqueta' => 'Por iniciar', 'icono' => 'fa-hourglass-start'];
        }
        if ($hoy <= $fin) {
            return ['clave' => 'en-curso', 'etiqueta' => 'En curso', 'icono' => 'fa-play-circle'];
        }
        return ['clave' => 'concluido', 'etiqueta' => 'Concluido', 'icono' => 'fa-flag-checkered'];
    }

    /**
     * Cantidad de programas vigentes por estado, para la franja de indicadores.
     * @return array{total:int, en-curso:int, por-iniciar:int, concluido:int}
     */
    public static function ResumenEstadosProgramasControlador()
    {
        $resumen = ['total' => 0, 'en-curso' => 0, 'por-iniciar' => 0, 'concluido' => 0, 'inactivo' => 0];
        foreach (ProgramasModelos::ListaProgramaModelo() as $programa) {
            $resumen['total']++;
            $resumen[self::EstadoProgramaControlador($programa)['clave']]++;
        }
        return $resumen;
    }

    public function ListaProgramaControlador($grado = null, $fechaInicio = null, $fechaFin = null, $sede = null)
    {
        // Con filtros: búsqueda filtrada; sin filtros: todos los programas vigentes
        if ($grado !== null || $fechaInicio !== null || $fechaFin !== null || $sede !== null) {
            $listaprogramas = ProgramasModelos::BuscarProgramasConFiltros($grado, $fechaInicio, $fechaFin, $sede);
        } else {
            $listaprogramas = ProgramasModelos::ListaProgramaModelo();
        }

        if (empty($listaprogramas)) {
            echo '<tr class="pg-sin-resultados"><td colspan="7" class="pg-vacio">
                    <i class="fa fa-search"></i>
                    <strong>No se encontraron programas</strong>
                    <span>Prueba cambiando los filtros de búsqueda</span>
                  </td></tr>';
            return;
        }

        $e = function ($valor) {
            return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
        };

        foreach ($listaprogramas as $programa) {
            $estado = self::EstadoProgramaControlador($programa);
            $id = (int)$programa['ProgramaID'];
            $activo = !($programa['Estado'] == 'INACTIVO' || $programa['Estado'] == 0);
            $duracion = (int)$programa['DuracionMeses'];

            echo '<tr>
                <td>
                    <div class="pg-nombre">' . $e($programa['NombrePrograma']) . '</div>
                    <div class="pg-meta">
                        <span class="pg-codigo">' . $e($programa['Codigo']) . '</span>'
                        . (!empty($programa['Sigla']) ? '<span class="pg-sigla">' . $e($programa['Sigla']) . '</span>' : '')
                        . (!empty($programa['Version']) && strtoupper($programa['Version']) !== 'V-1' ? '<span class="pg-sigla">' . $e($programa['Version']) . '</span>' : '') . '
                    </div>
                </td>
                <td><span class="' . FuncionesControladores::ClaseGrado($programa['GradoAcademico']) . '">' . $e($programa['GradoAcademico']) . '</span></td>
                <td class="pg-fecha">
                    <strong>' . date('d/m/Y', strtotime($programa['FechaInicio'])) . '</strong>
                    <small>' . ($duracion > 0 ? $duracion . ' meses' : 'Duración sin definir') . ' · ' . (int)$programa['Modulos'] . ' módulos</small>
                </td>
                <td>' . $e($programa['Sede']) . '</td>
                <td><span class="pg-estado is-' . $estado['clave'] . '"><i class="fa ' . $estado['icono'] . '"></i> ' . $estado['etiqueta'] . '</span></td>
                <td class="pg-costo">
                    <strong>Bs. ' . number_format((float)$programa['Costo'], 2, ',', '.') . '</strong>
                    <small>Matrícula Bs. ' . number_format((float)($programa['CostoMatricula'] ?? 0), 2, ',', '.') . '</small>
                </td>
                <td class="pg-acciones">
                    <button type="button" id="btnDetallePrograma" idcod="' . $id . '"
                        class="pg-accion" title="Ver detalle" aria-label="Ver detalle"
                        data-toggle="modal" data-target="#DetallePrograma">
                        <i class="fa fa-eye"></i>
                    </button>
                    <button type="button" idcod="' . $id . '"
                        data-idprograma="' . $id . '"
                        data-nombreprograma="' . $e($programa['NombrePrograma']) . '"
                        class="pg-accion btnAbrirCuentasPrograma" title="Cuentas bancarias" aria-label="Cuentas bancarias"
                        data-toggle="modal" data-target="#gestionarCuentas">
                        <i class="fa fa-university"></i>
                    </button>
                    <button type="button" class="pg-accion btnEditarPrograma" title="Editar" aria-label="Editar"
                        data-idprograma="' . $id . '"
                        data-nombre="' . $e($programa['NombrePrograma']) . '"
                        data-grado="' . $e($programa['GradoAcademico']) . '"
                        data-sigla="' . $e($programa['Sigla'] ?? '') . '"
                        data-duracion="' . $e($programa['DuracionMeses']) . '"
                        data-modulos="' . $e($programa['Modulos']) . '"
                        data-fecha="' . $e($programa['FechaInicio']) . '"
                        data-sede="' . $e($programa['Sede']) . '"
                        data-costo="' . $e($programa['Costo']) . '"
                        data-costomatricula="' . $e($programa['CostoMatricula'] ?? '') . '"
                        data-detalle="' . $e($programa['Detalle']) . '"
                        data-version="' . $e($programa['Version'] ?? 'V-1') . '"
                        data-toggle="modal" data-target="#modalEditarPrograma">
                        <i class="fa fa-pen"></i>
                    </button>';
            if ($activo) {
                echo '<button data-toggle="modal" data-target="#modalBorrar" type="button"
                        data-idprograma="' . $id . '"
                        data-nombre="' . $e($programa['NombrePrograma']) . '"
                        class="pg-accion is-peligro btnDarBaja" title="Dar de baja" aria-label="Dar de baja">
                        <i class="fa fa-arrow-down"></i>
                      </button>';
            } else {
                echo '<button data-toggle="modal" data-target="#modalsubir" type="button"
                        data-idprograma="' . $id . '"
                        data-nombre="' . $e($programa['NombrePrograma']) . '"
                        class="pg-accion is-exito btnSubir" title="Activar programa" aria-label="Activar programa">
                        <i class="fa fa-arrow-up"></i>
                      </button>';
            }
            echo '</td>
            </tr>';
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
                "Sigla" => strtoupper(htmlspecialchars(trim($_POST['Sigla'] ?? ''))),
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
                "Sigla" => strtoupper(htmlspecialchars(trim($_POST['SiglaEditar'] ?? ''))),
                "DuracionMeses" => (int)$_POST['DuracionMesesEditar'],
                "Modulos" => (int)$_POST['ModulosEditar'],
                "FechaInicio" => htmlspecialchars(trim($_POST['FechaInicioEditar'])),
                "Sede" => strtoupper(htmlspecialchars(trim($_POST['SedeEditar']))),
                "Costo" => (float)$_POST['CostoEditar'],
                "CostoMatricula" => (float)$_POST['CostoMatriculaEditar'],
                "Detalle" => strtoupper(htmlspecialchars(trim($_POST['DetalleEditar']))),
                "Version" => strtoupper(htmlspecialchars(trim($_POST['VersionEditar'] ?? 'V-1')))
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


