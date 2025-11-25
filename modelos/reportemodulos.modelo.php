<?php
/**
 * Modelo de Reportes de Módulos
 * Gestiona las consultas para reportes de inscritos
 */

require_once 'conexion.modelo.php';

class ReporteModulosModelo
{
    /**
     * Obtener grados académicos únicos
     * @return array
     */
    public static function ObtenerGradosAcademicosModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT DISTINCT GradoAcademico
                FROM programa
                WHERE Estado = 1
                ORDER BY GradoAcademico ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerGradosAcademicosModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener programas por grado académico
     * @param string $grado
     * @return array
     */
    public static function ObtenerProgramasPorGradoModelo($grado)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    ProgramaID,
                    NombrePrograma,
                    Codigo,
                    GradoAcademico
                FROM programa
                WHERE GradoAcademico = :grado
                AND Estado = 1
                ORDER BY NombrePrograma ASC"
            );
            $stmt->bindParam(":grado", $grado, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerProgramasPorGradoModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener módulos por programa
     * @param int $programaID
     * @return array
     */
    public static function ObtenerModulosPorProgramaModelo($programaID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    Idmodulo,
                    codigomodulo,
                    nombremodulo,
                    costomodulo
                FROM modulos
                WHERE ProgramaId = :programaID
                AND estadomodulo = 'ACTIVO'
                ORDER BY codigomodulo ASC"
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
     * Obtener inscritos por programa (con pagos de módulos)
     * @param int $programaID
     * @param int|null $moduloID
     * @return array
     */
    public static function ObtenerInscritosPorProgramaModelo($programaID, $moduloID = null)
    {
        try {
            $sql = "SELECT
                        e.EstudianteID,
                        CONCAT(e.Nombre, ' ', e.Apaterno, ' ', IFNULL(e.Amaterno, '')) as NombreCompleto,
                        e.Ci,
                        e.Celular,
                        e.Correo,
                        m.Idmodulo,
                        m.codigomodulo,
                        m.nombremodulo,
                        pm.costomodulo,
                        pm.fechapago,
                        pm.Estado as EstadoPago
                    FROM estudianteprograma ep
                    INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
                    INNER JOIN pagomodulo pm ON ep.idInscripcion = pm.idinscripcion
                    INNER JOIN modulos m ON pm.IdModulo = m.Idmodulo
                    WHERE ep.ProgramaID = :programaID
                    AND pm.Estado != 'ANULADO'";

            if ($moduloID !== null) {
                $sql .= " AND m.Idmodulo = :moduloID";
            }

            $sql .= " ORDER BY e.Apaterno ASC, e.Nombre ASC, m.codigomodulo ASC";

            $stmt = Conexion::Conectar()->prepare($sql);
            $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);

            if ($moduloID !== null) {
                $stmt->bindParam(":moduloID", $moduloID, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerInscritosPorProgramaModelo: " . $e->getMessage());
            return [];
        }
    }
}
?>
