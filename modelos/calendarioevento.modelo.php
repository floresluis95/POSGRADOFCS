<?php
require_once 'conexion.modelo.php';

/**
 * Modelo del Calendario Académico (clases, actividades y clínicas de los
 * docentes), diferenciado por programa y sede.
 */
class CalendarioEventoModelo
{
    /**
     * Listar eventos para el calendario, con filtros opcionales.
     * @param array $filtros programaID, sede, docenteID, desde, hasta (rango de fechas visible)
     * @return array
     */
    public static function ListarEventosModelo($filtros = [])
    {
        try {
            $sql = "SELECT
                        ce.IdEvento,
                        ce.ProgramaID,
                        ce.DocenteID,
                        ce.TipoEvento,
                        ce.Titulo,
                        ce.Descripcion,
                        ce.FechaInicio,
                        ce.FechaFin,
                        ce.Estado,
                        p.NombrePrograma,
                        p.Codigo AS CodigoPrograma,
                        p.GradoAcademico,
                        p.Sede,
                        CONCAT(d.Nombre, ' ', d.Apaterno, ' ', d.Amaterno) AS NombreDocente
                    FROM calendario_evento ce
                    INNER JOIN programa p ON p.ProgramaID = ce.ProgramaID
                    LEFT JOIN docente d ON d.DocenteID = ce.DocenteID
                    WHERE ce.Estado = 'ACTIVO'";

            $params = [];

            if (!empty($filtros['programaID'])) {
                $sql .= " AND ce.ProgramaID = :programaID";
                $params[':programaID'] = $filtros['programaID'];
            }

            if (!empty($filtros['sede'])) {
                $sql .= " AND p.Sede = :sede";
                $params[':sede'] = $filtros['sede'];
            }

            if (!empty($filtros['docenteID'])) {
                $sql .= " AND ce.DocenteID = :docenteID";
                $params[':docenteID'] = $filtros['docenteID'];
            }

            if (!empty($filtros['programaIDs']) && is_array($filtros['programaIDs'])) {
                $marcadores = [];
                foreach ($filtros['programaIDs'] as $i => $pid) {
                    $clave = ":pid{$i}";
                    $marcadores[] = $clave;
                    $params[$clave] = $pid;
                }
                $sql .= " AND ce.ProgramaID IN (" . implode(',', $marcadores) . ")";
            }

            // Rango visible del calendario (FullCalendar manda start/end de la vista actual)
            if (!empty($filtros['desde'])) {
                $sql .= " AND ce.FechaFin >= :desde";
                $params[':desde'] = $filtros['desde'];
            }
            if (!empty($filtros['hasta'])) {
                $sql .= " AND ce.FechaInicio <= :hasta";
                $params[':hasta'] = $filtros['hasta'];
            }

            $sql .= " ORDER BY ce.FechaInicio ASC";

            $stmt = Conexion::Conectar()->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarEventosModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * @param int $idEvento
     * @return array|null
     */
    public static function ObtenerEventoPorIdModelo($idEvento)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT ce.*, p.NombrePrograma, p.Sede
                 FROM calendario_evento ce
                 INNER JOIN programa p ON p.ProgramaID = ce.ProgramaID
                 WHERE ce.IdEvento = :id"
            );
            $stmt->bindParam(":id", $idEvento, PDO::PARAM_INT);
            $stmt->execute();
            $evento = $stmt->fetch(PDO::FETCH_ASSOC);
            return $evento ?: null;
        } catch (PDOException $e) {
            error_log("Error en ObtenerEventoPorIdModelo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * @param array $datos ProgramaID, DocenteID, TipoEvento, Titulo, Descripcion,
     *                      FechaInicio, FechaFin, CreadoPorUsuarioID
     * @return int|false IdEvento insertado, o false si falló
     */
    public static function RegistrarEventoModelo($datos)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "INSERT INTO calendario_evento
                (ProgramaID, DocenteID, TipoEvento, Titulo, Descripcion, FechaInicio, FechaFin, CreadoPorUsuarioID)
                VALUES (:programaID, :docenteID, :tipoEvento, :titulo, :descripcion, :fechaInicio, :fechaFin, :creadoPor)"
            );
            $stmt->bindParam(":programaID", $datos['ProgramaID'], PDO::PARAM_INT);
            if (!empty($datos['DocenteID'])) {
                $stmt->bindParam(":docenteID", $datos['DocenteID'], PDO::PARAM_INT);
            } else {
                $stmt->bindValue(":docenteID", null, PDO::PARAM_NULL);
            }
            $stmt->bindParam(":tipoEvento", $datos['TipoEvento'], PDO::PARAM_STR);
            $stmt->bindParam(":titulo", $datos['Titulo'], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos['Descripcion'], PDO::PARAM_STR);
            $stmt->bindParam(":fechaInicio", $datos['FechaInicio'], PDO::PARAM_STR);
            $stmt->bindParam(":fechaFin", $datos['FechaFin'], PDO::PARAM_STR);
            if (!empty($datos['CreadoPorUsuarioID'])) {
                $stmt->bindParam(":creadoPor", $datos['CreadoPorUsuarioID'], PDO::PARAM_INT);
            } else {
                $stmt->bindValue(":creadoPor", null, PDO::PARAM_NULL);
            }

            if (!$stmt->execute()) {
                return false;
            }
            return (int)Conexion::Conectar()->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en RegistrarEventoModelo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @param array $datos IdEvento, ProgramaID, DocenteID, TipoEvento, Titulo, Descripcion, FechaInicio, FechaFin
     * @return bool
     */
    public static function ActualizarEventoModelo($datos)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "UPDATE calendario_evento SET
                    ProgramaID = :programaID,
                    DocenteID = :docenteID,
                    TipoEvento = :tipoEvento,
                    Titulo = :titulo,
                    Descripcion = :descripcion,
                    FechaInicio = :fechaInicio,
                    FechaFin = :fechaFin
                WHERE IdEvento = :id"
            );
            $stmt->bindParam(":programaID", $datos['ProgramaID'], PDO::PARAM_INT);
            if (!empty($datos['DocenteID'])) {
                $stmt->bindParam(":docenteID", $datos['DocenteID'], PDO::PARAM_INT);
            } else {
                $stmt->bindValue(":docenteID", null, PDO::PARAM_NULL);
            }
            $stmt->bindParam(":tipoEvento", $datos['TipoEvento'], PDO::PARAM_STR);
            $stmt->bindParam(":titulo", $datos['Titulo'], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos['Descripcion'], PDO::PARAM_STR);
            $stmt->bindParam(":fechaInicio", $datos['FechaInicio'], PDO::PARAM_STR);
            $stmt->bindParam(":fechaFin", $datos['FechaFin'], PDO::PARAM_STR);
            $stmt->bindParam(":id", $datos['IdEvento'], PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ActualizarEventoModelo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Anular (baja lógica) un evento del calendario.
     * @param int $idEvento
     * @return bool
     */
    public static function AnularEventoModelo($idEvento)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "UPDATE calendario_evento SET Estado = 'ANULADO' WHERE IdEvento = :id"
            );
            $stmt->bindParam(":id", $idEvento, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en AnularEventoModelo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * IDs de los programas en los que un estudiante esta activo (para el
     * filtro por defecto del calendario en la vista de Estudiante).
     * @param int $estudianteID
     * @return array<int>
     */
    public static function ObtenerProgramaIDsDeEstudianteModelo($estudianteID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT DISTINCT ProgramaID FROM estudianteprograma
                 WHERE EstudianteID = :estudianteID AND Estado = 'ACTIVO'"
            );
            $stmt->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
            $stmt->execute();
            return array_map('intval', array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'ProgramaID'));
        } catch (PDOException $e) {
            error_log("Error en ObtenerProgramaIDsDeEstudianteModelo: " . $e->getMessage());
            return [];
        }
    }
}
