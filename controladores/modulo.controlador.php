<?php
/**
 * Controlador de Módulos
 * Gestiona el registro y consulta de módulos por programa
 */

require_once __DIR__ . '/../modelos/modulo.modelo.php';
require_once __DIR__ . '/../modelos/conexion.modelo.php';

class ModuloControlador
{
    /**
     * Registrar módulos de un programa (por inscripción)
     */
    public function RegistrarModulosControlador()
    {
        if (isset($_POST["registrarModulos"])) {
            // Validar datos requeridos
            if (empty($_POST['idinscripcion']) || empty($_POST['numModulos'])) {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "Faltan datos obligatorios", "error")
                .then(function () {
                    location.href="modulos";
                });
                </script>';
                return;
            }

            $idinscripcion = (int)$_POST['idinscripcion'];
            $numModulos = (int)$_POST['numModulos'];
            $modulos = [];

            // Recopilar módulos del formulario
            for ($i = 1; $i <= $numModulos; $i++) {
                $nombreKey = 'nombremodulo_' . $i;
                $codigoKey = 'codigomodulo_' . $i;

                if (isset($_POST[$nombreKey]) && isset($_POST[$codigoKey])) {
                    $nombremodulo = strtoupper(htmlspecialchars(trim($_POST[$nombreKey])));
                    $codigomodulo = (int)$_POST[$codigoKey];

                    if (!empty($nombremodulo) && $codigomodulo > 0) {
                        $modulos[] = [
                            'nombremodulo' => $nombremodulo,
                            'codigomodulo' => $codigomodulo
                        ];
                    }
                }
            }

            if (empty($modulos)) {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "Debe llenar al menos un módulo", "error")
                .then(function () {
                    location.href="modulos";
                });
                </script>';
                return;
            }

            // Preparar datos para el modelo
            $datosModulos = [
                'idinscripcion' => $idinscripcion,
                'modulos' => $modulos
            ];

            // Registrar en la base de datos
            $resultado = ModuloModelo::RegistrarModulosModelo($datosModulos);

            if ($resultado == 'exitoso') {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("EXITOSO!", "Los módulos se registraron correctamente (' . count($modulos) . ' módulos)", "success")
                .then(function () {
                    location.href="modulos";
                });
                </script>';
            } elseif ($resultado == 'duplicado') {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "Ya existen módulos registrados para esta inscripción", "error")
                .then(function () {
                    location.href="modulos";
                });
                </script>';
            } else {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "No se pudieron registrar los módulos", "error")
                .then(function () {
                    location.href="modulos";
                });
                </script>';
            }
        }
    }

    /**
     * Listar módulos en tabla HTML (por inscripción)
     */
    public function ListarModulosControlador()
    {
        $modulos = ModuloModelo::ListarTodosModulosModelo();

        if (empty($modulos)) {
            echo '<tr>
                    <td colspan="7" class="text-center">No hay módulos registrados</td>
                  </tr>';
            return;
        }

        foreach ($modulos as $key => $modulo) {
            $nombreCompleto = $modulo['Nombre'] . ' ' . $modulo['Apaterno'] . ' ' . $modulo['Amaterno'];
            $estadoBadge = $modulo['estadomodulo'] == 'ACTIVO' ? 'badge-success' : 'badge-secondary';

            echo '<tr>
                    <td class="text-center"><strong>' . ($key + 1) . '</strong></td>
                    <td>' . htmlspecialchars($modulo['codigomodulo']) . '</td>
                    <td><strong>' . htmlspecialchars($modulo['nombremodulo']) . '</strong></td>
                    <td>' . htmlspecialchars($nombreCompleto) . '</td>
                    <td><span class="badge badge-secondary">' . htmlspecialchars($modulo['Ci']) . '</span></td>
                    <td>' . htmlspecialchars($modulo['NombrePrograma']) . ' (' . htmlspecialchars($modulo['CodigoPrograma']) . ')</td>
                    <td class="text-center">
                        <span class="badge ' . $estadoBadge . '">' . htmlspecialchars($modulo['estadomodulo']) . '</span>
                    </td>
                  </tr>';
        }
    }

    /**
     * Registrar módulos directamente por ProgramaId
     */
    public function RegistrarModulosPorProgramaControlador()
    {
        if (isset($_POST["registrarModulosPorPrograma"])) {
            // Validar datos requeridos
            if (empty($_POST['programaID']) || empty($_POST['totalModulos'])) {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "Faltan datos obligatorios", "error")
                .then(function () {
                    location.href="modulos";
                });
                </script>';
                return;
            }

            $programaID = (int)$_POST['programaID'];
            $totalModulos = (int)$_POST['totalModulos'];
            $modulos = [];

            // Recopilar módulos del formulario
            for ($i = 1; $i <= $totalModulos; $i++) {
                $nombreKey = 'nombremodulo_' . $i;
                $codigoKey = 'codigomodulo_' . $i;
                $docenteKey = 'docentemodulo_' . $i;
                $costoKey = 'costomodulo_' . $i;

                if (isset($_POST[$codigoKey])) {
                    $codigomodulo = strtoupper(htmlspecialchars(trim($_POST[$codigoKey])));

                    // El nombre es opcional, si está vacío usar el código como nombre
                    $nombremodulo = isset($_POST[$nombreKey]) && !empty(trim($_POST[$nombreKey]))
                        ? strtoupper(htmlspecialchars(trim($_POST[$nombreKey])))
                        : $codigomodulo;

                    $docenteID = isset($_POST[$docenteKey]) && !empty($_POST[$docenteKey])
                        ? (int)$_POST[$docenteKey]
                        : null;

                    $costomodulo = isset($_POST[$costoKey]) && !empty($_POST[$costoKey])
                        ? (float)$_POST[$costoKey]
                        : 0;

                    if (!empty($codigomodulo)) {
                        $modulos[] = [
                            'nombremodulo' => $nombremodulo,
                            'codigomodulo' => $codigomodulo,
                            'docenteID' => $docenteID,
                            'costomodulo' => $costomodulo
                        ];
                    }
                }
            }

            if (empty($modulos)) {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "Debe llenar al menos un módulo", "error")
                .then(function () {
                    location.href="modulos";
                });
                </script>';
                return;
            }

            // Preparar datos para el modelo
            $datosModulos = [
                'programaID' => $programaID,
                'modulos' => $modulos
            ];

            // Registrar en la base de datos
            $resultado = ModuloModelo::RegistrarModulosPorProgramaModelo($datosModulos);

            if ($resultado == 'exitoso') {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                // Guardar el programaID en sessionStorage para mantener la selección
                sessionStorage.setItem("programaIDSeleccionado", "' . $programaID . '");
                swal("EXITOSO!", "Los módulos se registraron correctamente (' . count($modulos) . ' módulos)", "success")
                .then(function () {
                    location.href="modulos";
                });
                </script>';
            } elseif ($resultado == 'duplicado') {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "Ya existen módulos registrados para este programa", "error")
                .then(function () {
                    location.href="modulos";
                });
                </script>';
            } else {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "No se pudieron registrar los módulos", "error")
                .then(function () {
                    location.href="modulos";
                });
                </script>';
            }
        }
    }

    /**
     * Listar módulos por programa en tabla HTML
     */
    public function ListarModulosPorProgramaControlador()
    {
        $modulos = ModuloModelo::ListarModulosPorProgramaModelo();

        if (empty($modulos)) {
            echo '<tr>
                    <td colspan="9" class="text-center text-muted">
                        <i class="fa fa-info-circle"></i> No hay módulos registrados por programa
                    </td>
                  </tr>';
            return;
        }

        foreach ($modulos as $key => $modulo) {
            $estadoBadge = $modulo['estadomodulo'] == 'ACTIVO' ? 'badge-success' : 'badge-secondary';

            // Información del docente
            $docenteInfo = '<span class="text-muted"><em>Sin asignar</em></span>';
            if (!empty($modulo['NombreDocente'])) {
                $docenteInfo = '<strong>' . htmlspecialchars($modulo['NombreDocente']) . '</strong>';
                if (!empty($modulo['EspecialidadDocente'])) {
                    $docenteInfo .= '<br><small class="text-muted">' . htmlspecialchars($modulo['EspecialidadDocente']) . '</small>';
                }
            }

            // Datos para el botón de editar (en JSON para pasar al modal)
            $datosModulo = htmlspecialchars(json_encode([
                'idmodulo' => $modulo['Idmodulo'],
                'programaId' => $modulo['ProgramaId'],
                'nombremodulo' => $modulo['nombremodulo'],
                'codigomodulo' => $modulo['codigomodulo'],
                'costomodulo' => $modulo['costomodulo'],
                'docenteID' => $modulo['DocenteID'],
                'nombrePrograma' => $modulo['NombrePrograma'],
                'codigoPrograma' => $modulo['CodigoPrograma']
            ]), ENT_QUOTES, 'UTF-8');

            echo '<tr>
                    <td class="text-center"><strong>' . ($key + 1) . '</strong></td>
                    <td class="text-center"><span class="badge badge-primary">' . htmlspecialchars($modulo['codigomodulo']) . '</span></td>
                    <td><strong>' . htmlspecialchars($modulo['nombremodulo']) . '</strong></td>
                    <td class="text-center"><strong class="text-success">Bs. ' . number_format($modulo['costomodulo'], 2) . '</strong></td>
                    <td>' . htmlspecialchars($modulo['NombrePrograma']) . '</td>
                    <td class="text-center"><span class="badge badge-info">' . htmlspecialchars($modulo['CodigoPrograma']) . '</span></td>
                    <td>' . $docenteInfo . '</td>
                    <td class="text-center">
                        <span class="badge ' . $estadoBadge . '">' . htmlspecialchars($modulo['estadomodulo']) . '</span>
                    </td>
                    <td class="text-center">
                        <button type="button"
                                class="btn btn-sm btn-warning btn-editar-modulo"
                                data-modulo=\'' . $datosModulo . '\'
                                title="Editar módulo">
                            <i class="fa fa-edit"></i>
                        </button>
                    </td>
                  </tr>';
        }
    }

    /**
     * Actualizar módulo
     */
    public function ActualizarModuloControlador()
    {
        if (isset($_POST["actualizarModulo"])) {
            // Validar datos requeridos
            if (empty($_POST['idmodulo']) || empty($_POST['programaID']) || empty($_POST['nombremodulo']) || empty($_POST['codigomodulo'])) {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "Faltan datos obligatorios para actualizar", "error")
                .then(function () {
                    location.href="modulos";
                });
                </script>';
                return;
            }

            $idmodulo = (int)$_POST['idmodulo'];
            $programaID = (int)$_POST['programaID'];
            $nombremodulo = strtoupper(htmlspecialchars(trim($_POST['nombremodulo'])));
            $codigomodulo = strtoupper(htmlspecialchars(trim($_POST['codigomodulo'])));
            $docenteID = isset($_POST['docenteID']) && !empty($_POST['docenteID']) ? (int)$_POST['docenteID'] : null;
            $costomodulo = isset($_POST['costomodulo']) && !empty($_POST['costomodulo']) ? (float)$_POST['costomodulo'] : 0;

            // Preparar datos para el modelo
            $datosModulo = [
                'idmodulo' => $idmodulo,
                'programaID' => $programaID,
                'nombremodulo' => $nombremodulo,
                'codigomodulo' => $codigomodulo,
                'docenteID' => $docenteID,
                'costomodulo' => $costomodulo
            ];

            // Actualizar en la base de datos
            $resultado = ModuloModelo::ActualizarModuloPorProgramaModelo($datosModulo);

            if ($resultado) {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("EXITOSO!", "El módulo se actualizó correctamente", "success")
                .then(function () {
                    location.href="modulos";
                });
                </script>';
            } else {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "No se pudo actualizar el módulo", "error")
                .then(function () {
                    location.href="modulos";
                });
                </script>';
            }
        }
    }
}
?>
