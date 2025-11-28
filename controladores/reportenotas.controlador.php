<?php
/**
 * Controlador de Reporte de Notas
 * Gestiona las acciones relacionadas con los reportes de calificaciones
 */

class ReporteNotasControlador
{
    /**
     * Listar programas con información de módulos (para la tabla principal)
     */
    public function ListarProgramasConModulosControlador()
    {
        $programas = ReporteNotasModelo::ObtenerTodosProgramasConModulosModelo();
        $contador = 0;

        foreach ($programas as $programa) {
            $contador++;

            // Determinar badge del grado académico
            $badgeGrado = '';
            switch (strtoupper($programa['GradoAcademico'])) {
                case 'MAESTRIA':
                case 'MAESTRÍA':
                    $badgeGrado = '<span class="badge badge-danger">MAESTRÍA</span>';
                    break;
                case 'DIPLOMADO':
                    $badgeGrado = '<span class="badge badge-primary">DIPLOMADO</span>';
                    break;
                case 'ESPECIALIDAD':
                    $badgeGrado = '<span class="badge badge-success">ESPECIALIDAD</span>';
                    break;
                default:
                    $badgeGrado = '<span class="badge badge-secondary">' . htmlspecialchars($programa['GradoAcademico']) . '</span>';
            }

            echo '<tr>';
            echo '<td class="text-center">' . $contador . '</td>';
            echo '<td><strong>' . htmlspecialchars($programa['NombrePrograma']) . '</strong></td>';
            echo '<td class="text-center">' . $badgeGrado . '</td>';
            echo '<td class="text-center">' . htmlspecialchars($programa['CodigoPrograma']) . '</td>';
            echo '<td class="text-center">' . ($programa['TotalModulos'] ?: '0') . '</td>';
            echo '<td class="text-center">' . ($programa['TotalEstudiantes'] ?: '0') . '</td>';
            echo '<td class="text-center">
                    <button type="button" class="btn btn-info btn-sm btnVerModulos"
                            data-programa-id="' . $programa['ProgramaID'] . '"
                            data-programa-nombre="' . htmlspecialchars($programa['NombrePrograma']) . '"
                            data-toggle="modal"
                            data-target="#ModalModulosPrograma"
                            title="Ver módulos">
                        <i class="fa fa-list"></i> Ver Módulos
                    </button>
                  </td>';
            echo '</tr>';
        }

        if ($contador == 0) {
            echo '<tr><td colspan="7" class="text-center text-muted">No hay programas registrados</td></tr>';
        }
    }

    /**
     * Obtener módulos de un programa (AJAX)
     */
    public function ObtenerModulosProgramaControlador()
    {
        if (isset($_POST['programaID'])) {
            $programaID = intval($_POST['programaID']);
            $modulos = ReporteNotasModelo::ObtenerModulosPorProgramaModelo($programaID);

            echo json_encode([
                'status' => 'success',
                'data' => $modulos
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'ID de programa no especificado'
            ]);
        }
    }

    /**
     * Obtener estudiantes con notas (AJAX)
     */
    public function ObtenerEstudiantesConNotasControlador()
    {
        if (isset($_POST['moduloID']) && isset($_POST['programaID'])) {
            $moduloID = intval($_POST['moduloID']);
            $programaID = intval($_POST['programaID']);

            $estudiantes = ReporteNotasModelo::ObtenerEstudiantesConNotasModelo($moduloID, $programaID);

            echo json_encode([
                'status' => 'success',
                'data' => $estudiantes
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Parámetros incompletos'
            ]);
        }
    }
}
?>
