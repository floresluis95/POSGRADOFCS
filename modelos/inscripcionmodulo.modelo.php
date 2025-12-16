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
     * @param int|null $programaID (opcional) - Si se proporciona, filtra por ese programa
     * @return array
     */
    public static function ListarEstudiantesMatriculadosModelo($programaID = null)
    {
        try {
            $sql = "SELECT
                    ep.idInscripcion,
                    ep.EstudianteID,
                    ep.ProgramaID,
                    ep.FechaInscripcion,
                    ep.costomatricula,
                    ep.montoPagado,
                    ep.pagoCompleto,
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
                    p.Codigo as CodigoPrograma,
                    p.Version,
                    p.NumeroTramite
                FROM estudianteprograma ep
                INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
                INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
                WHERE ep.Estado = 'ACTIVO'";

            // Si se proporciona programaID, agregar filtro
            if ($programaID !== null && $programaID != '') {
                $sql .= " AND ep.ProgramaID = :programaID";
            }

            $sql .= " ORDER BY ep.FechaInscripcion DESC";

            $stmt = Conexion::Conectar()->prepare($sql);

            if ($programaID !== null && $programaID != '') {
                $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            }

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
     * Listar módulos inscritos de un estudiante con información del docente y calificación
     * @param int $estudianteID - ID del estudiante
     * @param int|null $programaID - ID del programa (opcional, para filtrar por programa específico)
     * @param int|null $idInscripcion - ID de la inscripción (opcional, para filtrar por inscripción específica)
     * @return array
     */
    public static function ObtenerModulosInscritosEstudianteModelo($estudianteID, $programaID = null, $idInscripcion = null)
    {
        try {
            // Si no se proporciona programaID ni idInscripcion, obtener el programa del estudiante (comportamiento antiguo)
            if ($programaID === null && $idInscripcion === null) {
                $stmtPrograma = Conexion::Conectar()->prepare(
                    "SELECT ProgramaID FROM estudianteprograma
                     WHERE EstudianteID = :estudianteID
                     AND Estado = 'ACTIVO'
                     LIMIT 1"
                );
                $stmtPrograma->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
                $stmtPrograma->execute();
                $programa = $stmtPrograma->fetch(PDO::FETCH_ASSOC);

                if (!$programa) {
                    return [];
                }

                $programaID = $programa['ProgramaID'];
            }

            // Construir la consulta SQL con filtros opcionales
            $sql = "SELECT
                    pm.Idpagomodulo,
                    pm.fechapago as FechaInscripcion,
                    pm.costomodulo,
                    pm.nvaucher as nvauchermodulo,
                    pm.Estado,
                    m.nombremodulo as NombreModulo,
                    m.codigomodulo as Codigo,
                    0 as Creditos,
                    CONCAT(d.Nombre, ' ', d.Apaterno, ' ', d.Amaterno) as NombreDocente,
                    d.Especialidad as EspecialidadDocente,
                    c.Nota,
                    CASE
                        WHEN c.Nota IS NULL THEN 'SIN CALIFICACIÓN'
                        WHEN c.Nota >= 51 THEN 'APROBADO'
                        ELSE 'REPROBADO'
                    END as EstadoAprobacion
                FROM pagomodulo pm
                INNER JOIN modulos m ON pm.IdModulo = m.Idmodulo
                INNER JOIN estudianteprograma ep ON pm.idinscripcion = ep.idInscripcion
                LEFT JOIN docente d ON m.DocenteID = d.DocenteID
                LEFT JOIN calificacion c ON c.EstudianteID = :estudianteID
                    AND c.Idmodulo = m.Idmodulo
                    AND c.ProgramaId = :programaID
                WHERE ep.EstudianteID = :estudianteID
                AND pm.Estado != 'ANULADO'";

            // Agregar filtro por idInscripcion si se proporciona
            if ($idInscripcion !== null) {
                $sql .= " AND ep.idInscripcion = :idInscripcion";
            }

            $sql .= " ORDER BY pm.fechapago DESC";

            $stmt = Conexion::Conectar()->prepare($sql);
            $stmt->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
            $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);

            if ($idInscripcion !== null) {
                $stmt->bindParam(":idInscripcion", $idInscripcion, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerModulosInscritosEstudianteModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener datos completos del estudiante matriculado para PDF
     * @param int $idInscripcion - ID de la inscripción específica
     * @return array|null
     */
    public static function ObtenerDatosCompletosEstudianteModelo($idInscripcion)
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
                    e.Complemento,
                    e.Exp,
                    e.Correo,
                    e.Celular,
                    e.Telefono,
                    e.Direccion,
                    e.FechaNacimiento,
                    e.Edad,
                    e.Trabajo,
                    prof.NombreProfesion,
                    p.NombrePrograma,
                    p.GradoAcademico,
                    p.Codigo as CodigoPrograma,
                    p.DuracionMeses,
                    p.Modulos,
                    p.Costo,
                    p.Version,
                    p.NumeroTramite
                FROM estudianteprograma ep
                INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
                INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
                LEFT JOIN profesion prof ON e.IdProfesion = prof.IdProfesion
                WHERE ep.idInscripcion = :idInscripcion
                AND ep.Estado = 'ACTIVO'"
            );
            $stmt->bindParam(":idInscripcion", $idInscripcion, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerDatosCompletosEstudianteModelo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener módulos pagados del estudiante para PDF
     * @param int $estudianteID
     * @return array
     */
    public static function ObtenerModulosPagadosEstudianteModelo($estudianteID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    pm.Idpagomodulo,
                    pm.fechapago,
                    pm.costomodulo,
                    pm.nvaucher,
                    pm.Estado,
                    m.nombremodulo as NombreModulo,
                    m.codigomodulo as Codigo,
                    m.costomodulo as CostoOriginal
                FROM pagomodulo pm
                INNER JOIN modulos m ON pm.IdModulo = m.Idmodulo
                INNER JOIN estudianteprograma ep ON pm.idinscripcion = ep.idInscripcion
                WHERE ep.EstudianteID = :estudianteID
                AND pm.Estado = 'PAGADO'
                ORDER BY pm.fechapago DESC"
            );
            $stmt->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerModulosPagadosEstudianteModelo: " . $e->getMessage());
            return [];
        }
    }
}
?>
