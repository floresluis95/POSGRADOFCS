<?php
/**
 * Controlador de Cuentas por Programa
 * Gestiona la administración de las cuentas bancarias asociadas a un
 * programa (una o varias por tipo: MATRICULA, CUOTAS, CLINICAS, etc.)
 */

require_once __DIR__ . '/../modelos/programacuenta.modelo.php';

class ProgramaCuentaControlador
{
    /**
     * Listar las cuentas de un programa en filas de tabla HTML,
     * agrupadas visualmente por tipo de cuenta.
     */
    public function ListarCuentasProgramaControlador($programaID)
    {
        $cuentas = ProgramaCuentaModelo::ListarPorProgramaModelo($programaID);

        if (empty($cuentas)) {
            echo '<tr><td colspan="5" class="text-center text-muted">Este programa aún no tiene cuentas registradas</td></tr>';
            return;
        }

        foreach ($cuentas as $cuenta) {
            $activa = $cuenta['Estado'] == '1';
            $principal = $cuenta['Principal'] == '1';

            echo '<tr>
                <td><span class="badge badge-info">' . htmlspecialchars($cuenta['TipoCuenta']) . '</span></td>
                <td>' . htmlspecialchars($cuenta['NumeroCuenta']) . '</td>
                <td>' . htmlspecialchars($cuenta['Banco'] ?? '') . '</td>
                <td class="text-center">';

            if ($principal) {
                echo '<span class="badge badge-success"><i class="fa fa-star"></i> Principal</span>';
            } else {
                echo '<button type="button" class="btn btn-sm btn-outline-secondary btnMarcarPrincipalCuenta"
                        data-idcuenta="' . $cuenta['ProgramaCuentaID'] . '" title="Marcar como principal">
                        Marcar principal
                      </button>';
            }

            echo '</td>
                <td class="text-center">
                    <span class="badge ' . ($activa ? 'badge-success' : 'badge-secondary') . ' mr-1">' . ($activa ? 'ACTIVA' : 'INACTIVA') . '</span>
                    <button type="button" class="btn btn-sm btnCambiarEstadoCuentaPrograma ' . ($activa ? 'btn-danger' : 'btn-success') . '"
                        data-idcuenta="' . $cuenta['ProgramaCuentaID'] . '"
                        data-nuevoestado="' . ($activa ? '0' : '1') . '">
                        ' . ($activa ? 'Dar de baja' : 'Activar') . '
                    </button>
                </td>
            </tr>';
        }
    }

    /**
     * Renderizar las <option> del datalist de tipos de cuenta ya usados,
     * para que el usuario los pueda reutilizar en vez de re-escribirlos.
     */
    public function RenderTiposCuentaDatalistControlador()
    {
        $tipos = ProgramaCuentaModelo::ListarTiposUsadosModelo();
        foreach ($tipos as $tipo) {
            echo '<option value="' . htmlspecialchars($tipo) . '">';
        }
    }
}
