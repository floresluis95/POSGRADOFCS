<?php
require_once 'conexion.modelo.php';

/**
 * Modelo del Cronograma de Defensa (Tesis/Monografía) mostrado en el Panel.
 * Administración/Secretaría puede crear, editar y eliminar (baja lógica);
 * todos los usuarios autenticados lo ven en el Panel.
 */
class CronogramaDefensaModelo
{
    /**
     * Defensas activas, para mostrarlas en el Panel (más próximas primero).
     * @return array
     */
    public static function ListarActivasModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT cd.*, p.NombrePrograma, p.GradoAcademico
                 FROM cronogramadefensa cd
                 LEFT JOIN programa p ON p.ProgramaID = cd.ProgramaID
                 WHERE cd.Estado = 'ACTIVO'
                 ORDER BY cd.FechaDefensa ASC, cd.HoraDefensa ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarActivasModelo (cronogramadefensa): " . $e->getMessage());
            return [];
        }
    }

    /**
     * @param int $idDefensa
     * @return array|null
     */
    public static function ObtenerPorIdModelo($idDefensa)
    {
        try {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM cronogramadefensa WHERE IdDefensa = :id");
            $stmt->bindParam(":id", $idDefensa, PDO::PARAM_INT);
            $stmt->execute();
            $defensa = $stmt->fetch(PDO::FETCH_ASSOC);
            return $defensa ?: null;
        } catch (PDOException $e) {
            error_log("Error en ObtenerPorIdModelo (cronogramadefensa): " . $e->getMessage());
            return null;
        }
    }

    /**
     * @param array $datos NombreEstudiante, ProgramaID, FechaDefensa, HoraDefensa, Lugar, Detalle, CreadoPorUsuarioID
     * @return int|false
     */
    public static function RegistrarModelo($datos)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "INSERT INTO cronogramadefensa (NombreEstudiante, ProgramaID, FechaDefensa, HoraDefensa, Lugar, Detalle, CreadoPorUsuarioID)
                 VALUES (:nombre, :programaID, :fecha, :hora, :lugar, :detalle, :creadoPor)"
            );
            $stmt->bindParam(":nombre", $datos['NombreEstudiante'], PDO::PARAM_STR);
            if (!empty($datos['ProgramaID'])) {
                $stmt->bindParam(":programaID", $datos['ProgramaID'], PDO::PARAM_INT);
            } else {
                $stmt->bindValue(":programaID", null, PDO::PARAM_NULL);
            }
            $stmt->bindParam(":fecha", $datos['FechaDefensa'], PDO::PARAM_STR);
            if (!empty($datos['HoraDefensa'])) {
                $stmt->bindParam(":hora", $datos['HoraDefensa'], PDO::PARAM_STR);
            } else {
                $stmt->bindValue(":hora", null, PDO::PARAM_NULL);
            }
            $stmt->bindParam(":lugar", $datos['Lugar'], PDO::PARAM_STR);
            if (!empty($datos['Detalle'])) {
                $stmt->bindParam(":detalle", $datos['Detalle'], PDO::PARAM_STR);
            } else {
                $stmt->bindValue(":detalle", null, PDO::PARAM_NULL);
            }
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
            error_log("Error en RegistrarModelo (cronogramadefensa): " . $e->getMessage());
            return false;
        }
    }

    /**
     * @param array $datos IdDefensa, NombreEstudiante, ProgramaID, FechaDefensa, HoraDefensa, Lugar, Detalle
     * @return bool
     */
    public static function ActualizarModelo($datos)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "UPDATE cronogramadefensa SET
                    NombreEstudiante = :nombre,
                    ProgramaID = :programaID,
                    FechaDefensa = :fecha,
                    HoraDefensa = :hora,
                    Lugar = :lugar,
                    Detalle = :detalle
                 WHERE IdDefensa = :id"
            );
            $stmt->bindParam(":nombre", $datos['NombreEstudiante'], PDO::PARAM_STR);
            if (!empty($datos['ProgramaID'])) {
                $stmt->bindParam(":programaID", $datos['ProgramaID'], PDO::PARAM_INT);
            } else {
                $stmt->bindValue(":programaID", null, PDO::PARAM_NULL);
            }
            $stmt->bindParam(":fecha", $datos['FechaDefensa'], PDO::PARAM_STR);
            if (!empty($datos['HoraDefensa'])) {
                $stmt->bindParam(":hora", $datos['HoraDefensa'], PDO::PARAM_STR);
            } else {
                $stmt->bindValue(":hora", null, PDO::PARAM_NULL);
            }
            $stmt->bindParam(":lugar", $datos['Lugar'], PDO::PARAM_STR);
            if (!empty($datos['Detalle'])) {
                $stmt->bindParam(":detalle", $datos['Detalle'], PDO::PARAM_STR);
            } else {
                $stmt->bindValue(":detalle", null, PDO::PARAM_NULL);
            }
            $stmt->bindParam(":id", $datos['IdDefensa'], PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ActualizarModelo (cronogramadefensa): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Anular (baja lógica) una defensa del cronograma.
     * @param int $idDefensa
     * @return bool
     */
    public static function AnularModelo($idDefensa)
    {
        try {
            $stmt = Conexion::Conectar()->prepare("UPDATE cronogramadefensa SET Estado = 'ANULADO' WHERE IdDefensa = :id");
            $stmt->bindParam(":id", $idDefensa, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en AnularModelo (cronogramadefensa): " . $e->getMessage());
            return false;
        }
    }
}
