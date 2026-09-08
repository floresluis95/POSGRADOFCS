<?php
/**
 * Controlador de Cuentas
 * Gestiona el registro y consulta del catálogo de números de cuenta
 */

require_once __DIR__ . '/../modelos/cuenta.modelo.php';

class CuentaControlador
{
    /**
     * Renderizar las <option> de cuentas activas (usado tanto en el
     * render inicial de la página como en la respuesta AJAX de refresco)
     */
    public function RenderOpcionesCuentasActivasControlador()
    {
        $cuentasDisponibles = CuentaModelo::ListarCuentasActivasModelo();
        foreach ($cuentasDisponibles as $cuentaOpcion) {
            $etiqueta = $cuentaOpcion['NumeroCuenta'] . (!empty($cuentaOpcion['Banco']) ? ' - ' . $cuentaOpcion['Banco'] : '');
            echo '<option value="' . htmlspecialchars($cuentaOpcion['NumeroCuenta']) . '">' . htmlspecialchars($etiqueta) . '</option>';
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
                    <button type="button" class="btn btn-sm btnCambiarEstadoCuenta ' . ($activa ? 'btn-danger' : 'btn-success') . '"
                        data-idcuenta="' . $cuenta['CuentaID'] . '"
                        data-nuevoestado="' . ($activa ? '0' : '1') . '">
                        ' . ($activa ? 'Dar de baja' : 'Activar') . '
                    </button>
                </td>
            </tr>';
        }
    }
}
