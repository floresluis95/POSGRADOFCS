<?php
/**
 * Modelo de Módulos
 * Gestiona los módulos de cada programa
 */

require_once 'conexion.modelo.php';

class ModuloModelo
{
    /**
     * Obtener todos los módulos de una inscripción
     * @param int $idinscripcion
     * @return array
     */
    public static function ObtenerModulosPorInscripcionModelo($idinscripcion)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    Idmodulo,
                    idinscripcion,
                    nombremodulo,
                    codigomodulo,
                    estadomodulo
                FROM modulos
                WHERE idinscripcion = :idinscripcion
                ORDER BY codigomodulo ASC"
            );
            $stmt->bindParam(":idinscripcion", $idinscripcion, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerModulosPorInscripcionModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Registrar múltiples módulos de un programa (por inscripción)
     * @param array $datos
     * @return string
     */
    public static function RegistrarModulosModelo($datos)
    {
        try {
            $pdo = Conexion::Conectar();
            $pdo->beginTransaction();

            $idinscripcion = $datos['idinscripcion'];
            $modulos = $datos['modulos'];

            // Verificar si ya existen módulos para esta inscripción
            $stmtCheck = $pdo->prepare(
                "SELECT COUNT(*) as total FROM modulos WHERE idinscripcion = :idinscripcion"
            );
            $stmtCheck->bindParam(":idinscripcion", $idinscripcion, PDO::PARAM_INT);
            $stmtCheck->execute();
            $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($result['total'] > 0) {
                $pdo->rollBack();
                return "duplicado";
            }

            // Insertar cada módulo
            $stmt = $pdo->prepare(
                "INSERT INTO modulos
                (idinscripcion, nombremodulo, codigomodulo, estadomodulo)
                VALUES (:idinscripcion, :nombremodulo, :codigomodulo, 'ACTIVO')"
            );

            $insertados = 0;
            foreach ($modulos as $modulo) {
                if (empty($modulo['nombremodulo']) || empty($modulo['codigomodulo'])) {
                    continue;
                }

                $stmt->bindParam(":idinscripcion", $idinscripcion, PDO::PARAM_INT);
                $stmt->bindParam(":nombremodulo", $modulo['nombremodulo'], PDO::PARAM_STR);
                $stmt->bindParam(":codigomodulo", $modulo['codigomodulo'], PDO::PARAM_INT);

                if ($stmt->execute()) {
                    $insertados++;
                }
            }

            if ($insertados > 0) {
                $pdo->commit();
                return "exitoso";
            } else {
                $pdo->rollBack();
                return "error";
            }

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en RegistrarModulosModelo: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Actualizar estado de un módulo
     * @param int $idmodulo
     * @param string $estado
     * @return bool
     */
    public static function ActualizarEstadoModuloModelo($idmodulo, $estado)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "UPDATE modulos SET estadomodulo = :estado WHERE Idmodulo = :idmodulo"
            );
            $stmt->bindParam(":idmodulo", $idmodulo, PDO::PARAM_INT);
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ActualizarEstadoModuloModelo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar módulo
     * @param int $idmodulo
     * @return bool
     */
    public static function EliminarModuloModelo($idmodulo)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "DELETE FROM modulos WHERE Idmodulo = :idmodulo"
            );
            $stmt->bindParam(":idmodulo", $idmodulo, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en EliminarModuloModelo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener número de módulos de un programa
     * @param int $programaID
     * @return int
     */
    public static function ObtenerNumeroModulosProgramaModelo($programaID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT Modulos FROM programa WHERE ProgramaID = :programaID"
            );
            $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['Modulos'] : 0;
        } catch (PDOException $e) {
            error_log("Error en ObtenerNumeroModulosProgramaModelo: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Listar todos los módulos con información del estudiante y programa
     * @return array
     */
    public static function ListarTodosModulosModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    m.Idmodulo,
                    m.nombremodulo,
                    m.codigomodulo,
                    m.estadomodulo,
                    m.idinscripcion,
                    e.Nombre,
                    e.Apaterno,
                    e.Amaterno,
                    e.Ci,
                    p.NombrePrograma,
                    p.Codigo as CodigoPrograma
                FROM modulos m
                INNER JOIN estudianteprograma ep ON m.idinscripcion = ep.idInscripcion
                INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
                INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
                ORDER BY m.idinscripcion DESC, m.codigomodulo ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarTodosModulosModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * ========================================
     * FUNCIONES PARA MÓDULOS POR PROGRAMA
     * ========================================
     */

    /**
     * Registrar módulos directamente por ProgramaId
     * @param array $datos
     * @return string
     */
    public static function RegistrarModulosPorProgramaModelo($datos)
    {
        try {
            $pdo = Conexion::Conectar();
            $pdo->beginTransaction();

            $programaID = $datos['programaID'];
            $modulos = $datos['modulos'];

            // Verificar si ya existen módulos para este programa
            $stmtCheck = $pdo->prepare(
                "SELECT COUNT(*) as total FROM modulos WHERE ProgramaId = :programaID"
            );
            $stmtCheck->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmtCheck->execute();
            $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($result['total'] > 0) {
                $pdo->rollBack();
                return "duplicado";
            }

            // Insertar cada módulo
            $stmt = $pdo->prepare(
                "INSERT INTO modulos
                (ProgramaId, nombremodulo, codigomodulo, costomodulo, estadomodulo, DocenteID)
                VALUES (:programaID, :nombremodulo, :codigomodulo, :costomodulo, 'ACTIVO', :docenteID)"
            );

            $insertados = 0;
            foreach ($modulos as $modulo) {
                if (empty($modulo['codigomodulo'])) {
                    continue;
                }

                // Si no hay nombre, usar el código como nombre
                $nombremodulo = !empty($modulo['nombremodulo']) ? $modulo['nombremodulo'] : $modulo['codigomodulo'];

                // DocenteID puede ser NULL si no se asigna docente
                $docenteID = isset($modulo['docenteID']) && !empty($modulo['docenteID']) ? (int)$modulo['docenteID'] : null;

                // Costo del módulo (puede ser 0 si no se especifica)
                $costomodulo = isset($modulo['costomodulo']) && !empty($modulo['costomodulo']) ? (float)$modulo['costomodulo'] : 0.0;

                $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
                $stmt->bindParam(":nombremodulo", $nombremodulo, PDO::PARAM_STR);
                $stmt->bindParam(":codigomodulo", $modulo['codigomodulo'], PDO::PARAM_STR);
                $stmt->bindParam(":costomodulo", $costomodulo, PDO::PARAM_STR);

                // Usar bindValue con PDO::PARAM_NULL para valores NULL
                if ($docenteID === null) {
                    $stmt->bindValue(":docenteID", null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue(":docenteID", $docenteID, PDO::PARAM_INT);
                }

                if ($stmt->execute()) {
                    $insertados++;
                }
            }

            if ($insertados > 0) {
                $pdo->commit();
                return "exitoso";
            } else {
                $pdo->rollBack();
                return "error";
            }

        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en RegistrarModulosPorProgramaModelo: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Obtener módulos de un programa por ProgramaId
     * @param int $programaID
     * @return array
     */
    public static function ObtenerModulosPorProgramaIdModelo($programaID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    Idmodulo,
                    ProgramaId,
                    nombremodulo,
                    codigomodulo,
                    costomodulo,
                    estadomodulo,
                    DocenteID
                FROM modulos
                WHERE ProgramaId = :programaID
                ORDER BY codigomodulo ASC"
            );
            $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerModulosPorProgramaIdModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Listar todos los módulos por ProgramaId con información del programa y docente
     * @param int|null $programaID (opcional) - Si se proporciona, filtra por ese programa
     * @return array
     */
    public static function ListarModulosPorProgramaModelo($programaID = null)
    {
        try {
            $sql = "SELECT
                    m.Idmodulo,
                    m.ProgramaId,
                    m.nombremodulo,
                    m.codigomodulo,
                    m.costomodulo,
                    m.estadomodulo,
                    m.DocenteID,
                    p.NombrePrograma,
                    p.Codigo as CodigoPrograma,
                    p.GradoAcademico,
                    CONCAT(d.Nombre, ' ', d.Apaterno, ' ', d.Amaterno) as NombreDocente,
                    d.Especialidad as EspecialidadDocente
                FROM modulos m
                INNER JOIN programa p ON m.ProgramaId = p.ProgramaID
                LEFT JOIN docente d ON m.DocenteID = d.DocenteID";

            // Si se proporciona programaID, agregar filtro
            if ($programaID !== null) {
                $sql .= " WHERE m.ProgramaId = :programaID";
            }

            $sql .= " ORDER BY m.ProgramaId DESC, m.codigomodulo ASC";

            $stmt = Conexion::Conectar()->prepare($sql);

            if ($programaID !== null) {
                $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarModulosPorProgramaModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualizar un módulo por ProgramaId
     * @param array $datos
     * @return bool
     */
    public static function ActualizarModuloPorProgramaModelo($datos)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "UPDATE modulos
                SET nombremodulo = :nombremodulo,
                    codigomodulo = :codigomodulo,
                    costomodulo = :costomodulo,
                    DocenteID = :docenteID
                WHERE Idmodulo = :idmodulo AND ProgramaId = :programaID"
            );
            $stmt->bindParam(":idmodulo", $datos['idmodulo'], PDO::PARAM_INT);
            $stmt->bindParam(":programaID", $datos['programaID'], PDO::PARAM_INT);
            $stmt->bindParam(":nombremodulo", $datos['nombremodulo'], PDO::PARAM_STR);
            $stmt->bindParam(":codigomodulo", $datos['codigomodulo'], PDO::PARAM_STR);

            $costomodulo = isset($datos['costomodulo']) && !empty($datos['costomodulo']) ? (float)$datos['costomodulo'] : 0.0;
            $stmt->bindParam(":costomodulo", $costomodulo, PDO::PARAM_STR);

            if (isset($datos['docenteID']) && !empty($datos['docenteID'])) {
                $stmt->bindParam(":docenteID", $datos['docenteID'], PDO::PARAM_INT);
            } else {
                $stmt->bindValue(":docenteID", null, PDO::PARAM_NULL);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ActualizarModuloPorProgramaModelo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar módulos de un programa
     * @param int $programaID
     * @return bool
     */
    public static function EliminarModulosPorProgramaModelo($programaID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "DELETE FROM modulos WHERE ProgramaId = :programaID"
            );
            $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en EliminarModulosPorProgramaModelo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener lista de docentes activos
     * @return array
     */
    public static function ListarDocentesActivosModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    DocenteID,
                    CONCAT(Nombre, ' ', Apaterno, ' ', Amaterno) as NombreCompleto,
                    Ci,
                    Especialidad,
                    Correo
                FROM docente
                WHERE Estado = 1
                ORDER BY Nombre ASC, Apaterno ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarDocentesActivosModelo: " . $e->getMessage());
            return [];
        }
    }
}
?>
