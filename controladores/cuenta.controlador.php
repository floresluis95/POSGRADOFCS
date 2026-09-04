<?php
/**
 * Controlador de Cuentas
 * Gestiona el registro y consulta del catálogo de números de cuenta
 */

require_once __DIR__ . '/../modelos/cuenta.modelo.php';

class CuentaControlador
{
    /**
     * Registrar una nueva cuenta
     */
    public function RegistrarCuentaControlador()
    {
        if (isset($_POST["NumeroCuenta"]) && isset($_POST["registrarCuenta"])) {
            $datos = array(
                "NumeroCuenta" => strtoupper(htmlspecialchars(trim($_POST['NumeroCuenta']))),
                "Banco" => strtoupper(htmlspecialchars(trim($_POST['Banco'] ?? '')))
            );

            if (empty($datos['NumeroCuenta'])) {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "Debe indicar el número de cuenta", "error")
                .then(function () {
                    location.href="programas?panel=gestionarCuentas";
                });
                </script>';
                return;
            }

            $resultado = CuentaModelo::RegistrarCuentaModelo($datos);

            if ($resultado == "exitoso") {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("EXITOSO!", "La cuenta se registró correctamente", "success")
                .then(function () {
                    location.href="programas?panel=gestionarCuentas";
                });
                </script>';
            } elseif ($resultado == "duplicado") {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "Esa cuenta ya está registrada", "error")
                .then(function () {
                    location.href="programas?panel=gestionarCuentas";
                });
                </script>';
            } else {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "No se pudo registrar la cuenta", "error")
                .then(function () {
                    location.href="programas?panel=gestionarCuentas";
                });
                </script>';
            }
        }
    }

    /**
     * Cambiar el estado de una cuenta (activar / dar de baja)
     */
    public function CambiarEstadoCuentaControlador()
    {
        if (isset($_POST["idCuentaEstado"]) && isset($_POST["nuevoEstadoCuenta"])) {
            $id = $_POST["idCuentaEstado"];
            $estado = $_POST["nuevoEstadoCuenta"] == '1' ? '1' : '0';

            $resultado = CuentaModelo::CambiarEstadoCuentaModelo($id, $estado);
            $mensaje = $estado == '1' ? "La cuenta fue activada correctamente" : "La cuenta fue dada de baja correctamente";

            if ($resultado == "exitoso") {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("EXITOSO!", "' . $mensaje . '", "success")
                .then(function () {
                    location.href="programas?panel=gestionarCuentas";
                });
                </script>';
            } else {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "No se pudo modificar la cuenta", "error")
                .then(function () {
                    location.href="programas?panel=gestionarCuentas";
                });
                </script>';
            }
        }
    }

    /**
     * Listar cuentas en filas de tabla HTML (para el modal de administración)
     */
    public function ListarCuentasControlador()
    {
        $cuentas = CuentaModelo::ListarCuentasModelo();

        if (empty($cuentas)) {
            echo '<tr><td colspan="4" class="text-center">No hay cuentas registradas</td></tr>';
            return;
        }

        foreach ($cuentas as $cuenta) {
            $activa = $cuenta['Estado'] == '1';
            echo '<tr>
                <td>' . htmlspecialchars($cuenta['NumeroCuenta']) . '</td>
                <td>' . htmlspecialchars($cuenta['Banco']) . '</td>
                <td class="text-center">' . ($activa
                    ? '<span class="badge badge-success">ACTIVA</span>'
                    : '<span class="badge badge-secondary">INACTIVA</span>') . '</td>
                <td class="text-center">
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="idCuentaEstado" value="' . $cuenta['CuentaID'] . '">
                        <input type="hidden" name="nuevoEstadoCuenta" value="' . ($activa ? '0' : '1') . '">
                        <button type="submit" class="btn btn-sm ' . ($activa ? 'btn-danger' : 'btn-success') . '">
                            ' . ($activa ? 'Dar de baja' : 'Activar') . '
                        </button>
                    </form>
                </td>
            </tr>';
        }
    }
}
