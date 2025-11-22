<?php
/**
 * Modelo de Pago de Módulos
 * Gestiona el registro de pagos de módulos por estudiante
 */

require_once 'conexion.modelo.php';

class PagoModuloModelo
{
    /**
     * Obtener módulos de un programa con estado de pago por inscripción
     * @param int $programaID
     * @param int $idinscripcion
     * @return array
     */
    public static function ObtenerModulosConEstadoPagoModelo($programaID, $idinscripcion)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    m.ModuloID,
                    m.NombreModulo,
                    m.Codigo,
                    m.Descripcion,
                    m.Creditos,
                    m.HorasTeoricas,
                    m.HorasPracticas,
                    m.Costo,
                    m.Estado,
                    pm.Idmodulo,
                    pm.costomodulo as CostoPagado,
                    pm.fechapago as FechaPago,
                    pm.nvaucher as NumeroVaucher,
                    pm.Estado as EstadoPago,
                    CASE
                        WHEN pm.Idmodulo IS NOT NULL THEN 1
                        ELSE 0
                    END as Pagado
                FROM modulo m
                LEFT JOIN pagomodulo pm ON m.NombreModulo = pm.nmodulo
                    AND pm.idinscripcion = :idinscripcion
                    AND pm.Estado != 'ANULADO'
                WHERE m.ProgramaID = :programaID
                AND m.Estado = 1
                ORDER BY m.Codigo ASC"
            );
            $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmt->bindParam(":idinscripcion", $idinscripcion, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerModulosConEstadoPagoModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener módulos disponibles de un programa (sin estado de pago)
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
                    Descripcion,
                    Creditos,
                    HorasTeoricas,
                    HorasPracticas,
                    Costo,
                    Estado
                FROM modulo
                WHERE ProgramaID = :programaID
                AND Estado = 1
                ORDER BY Codigo ASC"
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
     * Registrar pago de módulo
     * @param array $datos
     * @return string
     */
    public static function RegistrarPagoModuloModelo($datos)
    {
        try {
            $pdo = Conexion::Conectar();
            $pdo->beginTransaction();

            // Verificar si ya existe un pago para este módulo en esta inscripción
            $stmtCheck = $pdo->prepare(
                "SELECT Idmodulo FROM pagomodulo
                 WHERE idinscripcion = :idinscripcion
                 AND nmodulo = :nmodulo
                 AND Estado != 'ANULADO'"
            );
            $stmtCheck->bindParam(":idinscripcion", $datos['idinscripcion'], PDO::PARAM_INT);
            $stmtCheck->bindParam(":nmodulo", $datos['nmodulo'], PDO::PARAM_STR);
            $stmtCheck->execute();

            if ($stmtCheck->fetch()) {
                $pdo->rollBack();
                return "duplicado";
            }

            // Preparar el archivo (si existe)
            $fmodulo = null;
            if (!empty($datos['fmodulo'])) {
                $fmodulo = $datos['fmodulo'];
            }

            // Insertar el pago del módulo
            $stmt = $pdo->prepare(
                "INSERT INTO pagomodulo
                (idinscripcion, nmodulo, costomodulo, fechapago, nvaucher, fmodulo, Estado)
                VALUES (:idinscripcion, :nmodulo, :costomodulo, :fechapago, :nvaucher, :fmodulo, 'PAGADO')"
            );

            $stmt->bindParam(":idinscripcion", $datos['idinscripcion'], PDO::PARAM_INT);
            $stmt->bindParam(":nmodulo", $datos['nmodulo'], PDO::PARAM_STR);
            $stmt->bindParam(":costomodulo", $datos['costomodulo'], PDO::PARAM_STR);
            $stmt->bindParam(":fechapago", $datos['fechapago'], PDO::PARAM_STR);
            $stmt->bindParam(":nvaucher", $datos['nvaucher'], PDO::PARAM_STR);
            $stmt->bindParam(":fmodulo", $fmodulo, PDO::PARAM_LOB);

            if ($stmt->execute()) {
                $pdo->commit();
                return "exitoso";
            } else {
                $pdo->rollBack();
                error_log("Error al ejecutar INSERT en pagomodulo: " . print_r($stmt->errorInfo(), true));
                return "error";
            }

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en RegistrarPagoModuloModelo: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Obtener pagos de módulos por inscripción
     * @param int $idinscripcion
     * @return array
     */
    public static function ObtenerPagosModulosPorInscripcionModelo($idinscripcion)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    Idmodulo,
                    idinscripcion,
                    nmodulo,
                    costomodulo,
                    fechapago,
                    nvaucher,
                    Estado,
                    FechaRegistro
                FROM pagomodulo
                WHERE idinscripcion = :idinscripcion
                AND Estado != 'ANULADO'
                ORDER BY fechapago DESC"
            );
            $stmt->bindParam(":idinscripcion", $idinscripcion, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerPagosModulosPorInscripcionModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener pagos de módulos por estudiante
     * @param int $estudianteID
     * @return array
     */
    public static function ObtenerPagosModulosPorEstudianteModelo($estudianteID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    pm.Idmodulo,
                    pm.nmodulo,
                    pm.costomodulo,
                    pm.fechapago,
                    pm.nvaucher,
                    pm.Estado,
                    pm.FechaRegistro,
                    ep.ProgramaID,
                    p.NombrePrograma,
                    p.GradoAcademico
                FROM pagomodulo pm
                INNER JOIN estudianteprograma ep ON pm.idinscripcion = ep.idInscripcion
                INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
                WHERE ep.EstudianteID = :estudianteID
                AND pm.Estado != 'ANULADO'
                ORDER BY pm.fechapago DESC"
            );
            $stmt->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerPagosModulosPorEstudianteModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener archivo/foto de voucher
     * @param int $idmodulo
     * @return mixed
     */
    public static function ObtenerArchivoVoucherModelo($idmodulo)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT fmodulo FROM pagomodulo WHERE Idmodulo = :idmodulo"
            );
            $stmt->bindParam(":idmodulo", $idmodulo, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['fmodulo'] : null;
        } catch (PDOException $e) {
            error_log("Error en ObtenerArchivoVoucherModelo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Anular pago de módulo
     * @param int $idmodulo
     * @return bool
     */
    public static function AnularPagoModuloModelo($idmodulo)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "UPDATE pagomodulo SET Estado = 'ANULADO' WHERE Idmodulo = :idmodulo"
            );
            $stmt->bindParam(":idmodulo", $idmodulo, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en AnularPagoModuloModelo: " . $e->getMessage());
            return false;
        }
    }
}
?>
