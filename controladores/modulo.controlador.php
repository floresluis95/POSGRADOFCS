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
                    $nombremodulo = htmlspecialchars(trim($_POST[$nombreKey]));
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

                if (isset($_POST[$codigoKey])) {
                    $codigomodulo = htmlspecialchars(trim($_POST[$codigoKey]));

                    // El nombre es opcional, si está vacío usar el código como nombre
                    $nombremodulo = isset($_POST[$nombreKey]) && !empty(trim($_POST[$nombreKey]))
                        ? htmlspecialchars(trim($_POST[$nombreKey]))
                        : $codigomodulo;

                    $docenteID = isset($_POST[$docenteKey]) && !empty($_POST[$docenteKey])
                        ? (int)$_POST[$docenteKey]
                        : null;

                    if (!empty($codigomodulo)) {
                        $modulos[] = [
                            'nombremodulo' => $nombremodulo,
                            'codigomodulo' => $codigomodulo,
                            'docenteID' => $docenteID
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
                    <td colspan="7" class="text-center">No hay módulos registrados por programa</td>
                  </tr>';
            return;
        }

        foreach ($modulos as $key => $modulo) {
            $estadoBadge = $modulo['estadomodulo'] == 'ACTIVO' ? 'badge-success' : 'badge-secondary';

            // Información del docente
            $docenteInfo = '<span class="text-muted">Sin asignar</span>';
            if (!empty($modulo['NombreDocente'])) {
                $docenteInfo = '<strong>' . htmlspecialchars($modulo['NombreDocente']) . '</strong>';
                if (!empty($modulo['EspecialidadDocente'])) {
                    $docenteInfo .= '<br><small class="text-muted">' . htmlspecialchars($modulo['EspecialidadDocente']) . '</small>';
                }
            }

            echo '<tr>
                    <td class="text-center"><strong>' . ($key + 1) . '</strong></td>
                    <td class="text-center">' . htmlspecialchars($modulo['codigomodulo']) . '</td>
                    <td><strong>' . htmlspecialchars($modulo['nombremodulo']) . '</strong></td>
                    <td>' . htmlspecialchars($modulo['NombrePrograma']) . '</td>
                    <td class="text-center"><span class="badge badge-info">' . htmlspecialchars($modulo['CodigoPrograma']) . '</span></td>
                    <td>' . $docenteInfo . '</td>
                    <td class="text-center">
                        <span class="badge ' . $estadoBadge . '">' . htmlspecialchars($modulo['estadomodulo']) . '</span>
                    </td>
                  </tr>';
        }
    }
}
?>
