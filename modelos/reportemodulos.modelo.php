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

    /**
     * Obtener estadísticas generales del sistema
     * @return array
     */
    public static function ObtenerEstadisticasGeneralesModelo()
    {
        try {
            $conexion = Conexion::Conectar();

            // Contar programas activos
            $stmtProgramas = $conexion->prepare("SELECT COUNT(*) as total FROM programa WHERE Estado = 1");
            $stmtProgramas->execute();
            $totalProgramas = $stmtProgramas->fetch(PDO::FETCH_ASSOC)['total'];

            // Contar módulos activos
            $stmtModulos = $conexion->prepare("SELECT COUNT(*) as total FROM modulos WHERE estadomodulo = 'ACTIVO'");
            $stmtModulos->execute();
            $totalModulos = $stmtModulos->fetch(PDO::FETCH_ASSOC)['total'];

            // Contar estudiantes inscritos
            $stmtEstudiantes = $conexion->prepare("SELECT COUNT(DISTINCT EstudianteID) as total FROM estudianteprograma");
            $stmtEstudiantes->execute();
            $totalEstudiantes = $stmtEstudiantes->fetch(PDO::FETCH_ASSOC)['total'];

            // Contar pagos de módulos
            $stmtPagos = $conexion->prepare("SELECT COUNT(*) as total FROM pagomodulo WHERE Estado != 'ANULADO'");
            $stmtPagos->execute();
            $totalPagos = $stmtPagos->fetch(PDO::FETCH_ASSOC)['total'];

            return [
                'totalProgramas' => $totalProgramas,
                'totalModulos' => $totalModulos,
                'totalEstudiantes' => $totalEstudiantes,
                'totalPagos' => $totalPagos
            ];
        } catch (PDOException $e) {
            error_log("Error en ObtenerEstadisticasGeneralesModelo: " . $e->getMessage());
            return [
                'totalProgramas' => 0,
                'totalModulos' => 0,
                'totalEstudiantes' => 0,
                'totalPagos' => 0
            ];
        }
    }

    /**
     * Obtener conteo de módulos por programa
     * @return array
     */
    public static function ObtenerConteoModulosPorProgramaModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    p.ProgramaID,
                    p.NombrePrograma,
                    p.Codigo,
                    p.GradoAcademico,
                    COUNT(m.Idmodulo) as TotalModulos
                FROM programa p
                LEFT JOIN modulos m ON p.ProgramaID = m.ProgramaId AND m.estadomodulo = 'ACTIVO'
                WHERE p.Estado = 1
                GROUP BY p.ProgramaID, p.NombrePrograma, p.Codigo, p.GradoAcademico
                ORDER BY p.GradoAcademico ASC, p.NombrePrograma ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerConteoModulosPorProgramaModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener conteo de inscritos por módulo
     * @param int $programaID
     * @return array
     */
    public static function ObtenerConteoInscritosPorModuloModelo($programaID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    m.Idmodulo,
                    m.codigomodulo,
                    m.nombremodulo,
                    m.costomodulo,
                    COUNT(DISTINCT pm.idinscripcion) as TotalInscritos
                FROM modulos m
                LEFT JOIN pagomodulo pm ON m.Idmodulo = pm.IdModulo AND pm.Estado != 'ANULADO'
                WHERE m.ProgramaId = :programaID
                AND m.estadomodulo = 'ACTIVO'
                GROUP BY m.Idmodulo, m.codigomodulo, m.nombremodulo, m.costomodulo
                ORDER BY m.codigomodulo ASC"
            );
            $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerConteoInscritosPorModuloModelo: " . $e->getMessage());
            return [];
        }
    }
}
?>
