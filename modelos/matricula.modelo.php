<?php
require_once 'conexion.modelo.php';

/**
 * Modelo de Matrícula/Inscripción
 * Gestiona el registro de inscripciones en la tabla 'inscripcion'
 */
class MatriculaModelos
{
    /**
     * Registrar nueva inscripción/matriculación
     * @param array $datos - Datos de la inscripción
     * @return string - 'exitoso' o 'error'
     */
    public static function RegistrarMatriculaModelo($datos)
    {
        try {
            $pdo = Conexion::Conectar();
            $pdo->beginTransaction();

            // Verificar si el estudiante ya está inscrito en el mismo programa
            $stmtCheck = $pdo->prepare(
                "SELECT idInscripcion FROM estudianteprograma
                 WHERE EstudianteID = :estudianteID
                 AND ProgramaID = :programaID
                 AND Estado = 'ACTIVO'"
            );
            $stmtCheck->bindParam(":estudianteID", $datos['EstudianteID'], PDO::PARAM_INT);
            $stmtCheck->bindParam(":programaID", $datos['ProgramaID'], PDO::PARAM_INT);
            $stmtCheck->execute();

            if ($stmtCheck->fetch()) {
                $pdo->rollBack();
                return "duplicado";
            }

            // Insertar la inscripción
            $stmt = $pdo->prepare(
                "INSERT INTO estudianteprograma
                (EstudianteID, ProgramaID, costomatricula, nvauchermatricula, FechaInscripcion, foto, Estado)
                VALUES (:estudianteID, :programaID, :costomatricula, :nvaucher, :fechaInscripcion, :foto, 'ACTIVO')"
            );

            $stmt->bindParam(":estudianteID", $datos['EstudianteID'], PDO::PARAM_INT);
            $stmt->bindParam(":programaID", $datos['ProgramaID'], PDO::PARAM_INT);
            $stmt->bindParam(":costomatricula", $datos['costomatricula'], PDO::PARAM_INT);
            $stmt->bindParam(":nvaucher", $datos['nvauchermatricula'], PDO::PARAM_INT);
            $stmt->bindParam(":fechaInscripcion", $datos['FechaInscripcion'], PDO::PARAM_STR);
            $stmt->bindParam(":foto", $datos['foto'], PDO::PARAM_LOB);

            if ($stmt->execute()) {
                $pdo->commit();
                return "exitoso";
            } else {
                $pdo->rollBack();
                error_log("Error al ejecutar INSERT: " . print_r($stmt->errorInfo(), true));
                return "error";
            }

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en RegistrarMatriculaModelo: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Verificar si un estudiante ya está inscrito en un programa
     * @param int $estudianteID
     * @param int $programaID
     * @return bool
     */
    public static function VerificarInscripcionExistente($estudianteID, $programaID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT idInscripcion FROM estudianteprograma
                 WHERE EstudianteID = :estudianteID
                 AND ProgramaID = :programaID
                 AND Estado = 'ACTIVO'"
            );
            $stmt->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
            $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            error_log("Error en VerificarInscripcionExistente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Listar todas las inscripciones
     * @return array
     */
    public static function ListarInscripcionesModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT i.idInscripcion, i.FechaInscripcion, i.costomatricula, i.nvauchermatricula, i.Estado,
                        e.EstudianteID, e.Nombre, e.Apaterno, e.Amaterno, e.Ci,
                        p.ProgramaID, p.NombrePrograma, p.GradoAcademico, p.Codigo, p.Sede, p.Version, p.NumeroTramite
                 FROM estudianteprograma i
                 INNER JOIN estudiante e ON i.EstudianteID = e.EstudianteID
                 INNER JOIN programa p ON i.ProgramaID = p.ProgramaID
                 ORDER BY i.FechaInscripcion DESC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarInscripcionesModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener inscripción por ID
     * @param int $idInscripcion
     * @return array|false
     */
    public static function ObtenerInscripcionPorIdModelo($idInscripcion)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT i.*,
                        e.Nombre, e.Apaterno, e.Amaterno, e.Ci, e.Correo, e.Celular,
                        p.NombrePrograma, p.GradoAcademico, p.Codigo, p.Costo, p.Sede, p.Version, p.NumeroTramite
                 FROM estudianteprograma i
                 INNER JOIN estudiante e ON i.EstudianteID = e.EstudianteID
                 INNER JOIN programa p ON i.ProgramaID = p.ProgramaID
                 WHERE i.idInscripcion = :idInscripcion"
            );
            $stmt->bindParam(":idInscripcion", $idInscripcion, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerInscripcionPorIdModelo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar estado de inscripción
     * @param int $idInscripcion
     * @param string $estado
     * @return bool
     */
    public static function ActualizarEstadoInscripcionModelo($idInscripcion, $estado)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "UPDATE estudianteprograma SET Estado = :estado WHERE idInscripcion = :idInscripcion"
            );
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            $stmt->bindParam(":idInscripcion", $idInscripcion, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ActualizarEstadoInscripcionModelo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener inscripciones de un estudiante
     * @param int $estudianteID
     * @return array
     */
    public static function ObtenerInscripcionesPorEstudianteModelo($estudianteID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT i.idInscripcion, i.FechaInscripcion, i.costomatricula, i.Estado,
                        p.NombrePrograma, p.GradoAcademico, p.Codigo, p.Sede
                 FROM estudianteprograma i
                 INNER JOIN programa p ON i.ProgramaID = p.ProgramaID
                 WHERE i.EstudianteID = :estudianteID
                 ORDER BY i.FechaInscripcion DESC"
            );
            $stmt->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerInscripcionesPorEstudianteModelo: " . $e->getMessage());
            return [];
        }
    }
}
