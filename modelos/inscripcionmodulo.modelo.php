<?php
require_once 'conexion.modelo.php';

/**
 * Modelo de Inscripción a Módulos
 * Gestiona el registro de estudiantes en módulos específicos
 */
class InscripcionModuloModelos
{
    /**
     * Listar estudiantes matriculados con sus programas
     * @return array
     */
    public static function ListarEstudiantesMatriculadosModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    ep.idInscripcion,
                    ep.EstudianteID,
                    ep.ProgramaID,
                    ep.FechaInscripcion,
                    ep.costomatricula,
                    ep.nvauchermatricula,
                    ep.Estado,
                    e.Nombre,
                    e.Apaterno,
                    e.Amaterno,
                    e.Ci,
                    e.Correo,
                    e.Celular,
                    p.NombrePrograma,
                    p.GradoAcademico,
                    p.Codigo as CodigoPrograma
                FROM estudianteprograma ep
                INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
                INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
                WHERE ep.Estado = 'ACTIVO'
                ORDER BY ep.FechaInscripcion DESC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarEstudiantesMatriculadosModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener módulos de un programa específico
     * @param int $programaID
     * @return array
     */
    public static function ObtenerModulosPorProgramaModelo($programaID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    ModuloID,
                    NombreModulo,
                    Codigo,
                    Creditos,
                    HorasTeoricas,
                    HorasPracticas,
                    Costo
                FROM modulo
                WHERE ProgramaID = :programaID
                AND Estado = 1
                ORDER BY Codigo"
            );
            $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerModulosPorProgramaModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Registrar inscripción de estudiante a módulo
     * @param array $datos
     * @return string
     */
    public static function RegistrarInscripcionModuloModelo($datos)
    {
        try {
            $pdo = Conexion::Conectar();
            $pdo->beginTransaction();

            // Verificar si ya está inscrito en ese módulo
            $stmtCheck = $pdo->prepare(
                "SELECT idEstudianteModulo FROM estudiantemodulo
                 WHERE EstudianteID = :estudianteID
                 AND ModuloID = :moduloID
                 AND Estado = 'ACTIVO'"
            );
            $stmtCheck->bindParam(":estudianteID", $datos['EstudianteID'], PDO::PARAM_INT);
            $stmtCheck->bindParam(":moduloID", $datos['ModuloID'], PDO::PARAM_INT);
            $stmtCheck->execute();

            if ($stmtCheck->fetch()) {
                $pdo->rollBack();
                return "duplicado";
            }

            // Insertar inscripción
            $stmt = $pdo->prepare(
                "INSERT INTO estudiantemodulo
                (EstudianteID, ModuloID, costomodulo, nvauchermodulo, FechaInscripcion, Estado)
                VALUES (:estudianteID, :moduloID, :costo, :nvaucher, :fecha, 'ACTIVO')"
            );

            $stmt->bindParam(":estudianteID", $datos['EstudianteID'], PDO::PARAM_INT);
            $stmt->bindParam(":moduloID", $datos['ModuloID'], PDO::PARAM_INT);
            $stmt->bindParam(":costo", $datos['costo'], PDO::PARAM_STR);
            $stmt->bindParam(":nvaucher", $datos['nvaucher'], PDO::PARAM_STR);
            $stmt->bindParam(":fecha", $datos['fecha'], PDO::PARAM_STR);

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
            error_log("Error en RegistrarInscripcionModuloModelo: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Listar módulos inscritos de un estudiante
     * @param int $estudianteID
     * @return array
     */
    public static function ObtenerModulosInscritosEstudianteModelo($estudianteID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    em.idEstudianteModulo,
                    em.FechaInscripcion,
                    em.costomodulo,
                    em.nvauchermodulo,
                    em.Estado,
                    m.NombreModulo,
                    m.Codigo,
                    m.Creditos
                FROM estudiantemodulo em
                INNER JOIN modulo m ON em.ModuloID = m.ModuloID
                WHERE em.EstudianteID = :estudianteID
                ORDER BY em.FechaInscripcion DESC"
            );
            $stmt->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerModulosInscritosEstudianteModelo: " . $e->getMessage());
            return [];
        }
    }
}
?>
