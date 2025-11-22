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
     * Registrar múltiples módulos de un programa
     * @param array $datos
     * @return string
     */
    public static function RegistrarModulosModelo($datos)
    {
        try {
            $pdo = Conexion::Conectar();
            $pdo->beginTransaction();

            $idinscripcion = $datos['idinscripcion'];
            $modulos = $datos['modulos']; // Array de módulos

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
                    continue; // Saltar módulos vacíos
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
}
?>
