<?php
/**
 * Controlador de Especialidades
 * Gestiona el catálogo de especialidades usado en el formulario de docentes.
 */

require_once __DIR__ . '/../modelos/especialidad.modelo.php';

class EspecialidadControlador
{
    /**
     * Renderizar las <option> de especialidades activas para el select
     * del formulario de docentes. El valor de cada <option> es el propio
     * nombre (no el ID), para no tener que tocar la columna
     * docente.Especialidad ni los lugares que ya la leen como texto.
     * @param string $seleccionada Nombre actualmente seleccionado (para preseleccionar)
     */
    public function RenderOpcionesActivasControlador($seleccionada = '')
    {
        $especialidades = EspecialidadModelo::ListarActivasModelo();
        foreach ($especialidades as $especialidad) {
            $nombre = $especialidad['Nombre'];
            $selected = ($nombre === $seleccionada) ? ' selected' : '';
            echo '<option value="' . htmlspecialchars($nombre) . '"' . $selected . '>' . htmlspecialchars($nombre) . '</option>';
        }
    }

    /**
     * Listar especialidades en filas de tabla HTML (para el modal de administración)
     */
    public function ListarEspecialidadesControlador()
    {
        $especialidades = EspecialidadModelo::ListarTodasModelo();

        if (empty($especialidades)) {
            echo '<tr><td colspan="3" class="text-center">No hay especialidades registradas</td></tr>';
            return;
        }

        foreach ($especialidades as $especialidad) {
            $activa = $especialidad['Estado'] == '1';
            echo '<tr>
                <td>' . htmlspecialchars($especialidad['Nombre']) . '</td>
                <td class="text-center">' . ($activa
                    ? '<span class="badge badge-success">ACTIVA</span>'
                    : '<span class="badge badge-secondary">INACTIVA</span>') . '</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btnCambiarEstadoEspecialidad ' . ($activa ? 'btn-danger' : 'btn-success') . '"
                        data-idespecialidad="' . $especialidad['EspecialidadID'] . '"
                        data-nuevoestado="' . ($activa ? '0' : '1') . '">
                        ' . ($activa ? 'Dar de baja' : 'Activar') . '
                    </button>
                </td>
            </tr>';
        }
    }
}
