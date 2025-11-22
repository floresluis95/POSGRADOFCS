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
     * Registrar módulos de un programa
     */
    public function RegistrarModulosControlador()
    {
        error_log("=== RegistrarModulosControlador ejecutado ===");
        error_log("POST data: " . print_r($_POST, true));

        if (isset($_POST["registrarModulos"])) {
            error_log("POST registrarModulos detectado");

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

            error_log("Módulos a registrar: " . count($modulos));

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
     * Listar módulos en tabla HTML
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
                    <td>' . $modulo['codigomodulo'] . '</td>
                    <td><strong>' . $modulo['nombremodulo'] . '</strong></td>
                    <td>' . $nombreCompleto . '</td>
                    <td><span class="badge badge-secondary">' . $modulo['Ci'] . '</span></td>
                    <td>' . $modulo['NombrePrograma'] . ' (' . $modulo['CodigoPrograma'] . ')</td>
                    <td class="text-center">
                        <span class="badge ' . $estadoBadge . '">' . $modulo['estadomodulo'] . '</span>
                    </td>
                  </tr>';
        }
    }
}
?>
