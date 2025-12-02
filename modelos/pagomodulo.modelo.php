<?php
/**
 * Modelo de Pago de Módulos
 * Gestiona el registro de pagos de módulos por estudiante
 */

require_once 'conexion.modelo.php';

class PagoModuloModelo
{
    /**
     * Distribuir el costo total del programa entre sus módulos activos
     * Si el programa tiene 2 o más módulos, divide el costo equitativamente
     * @param int $programaID
     * @return bool
     */
    public static function DistribuirCostoProgramaEnModulosModelo($programaID)
    {
        try {
            $pdo = Conexion::Conectar();
            $pdo->beginTransaction();

            // 1. Obtener el costo total del programa
            $stmtPrograma = $pdo->prepare(
                "SELECT CostoMatricula FROM programa WHERE ProgramaID = :programaID"
            );
            $stmtPrograma->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmtPrograma->execute();
            $programa = $stmtPrograma->fetch(PDO::FETCH_ASSOC);

            if (!$programa || !isset($programa['CostoMatricula'])) {
                $pdo->rollBack();
                error_log("Programa no encontrado o sin costo: " . $programaID);
                return false;
            }

            $costoTotalPrograma = floatval($programa['CostoMatricula']);

            // 2. Contar módulos activos del programa
            $stmtCount = $pdo->prepare(
                "SELECT COUNT(*) as total FROM modulos
                 WHERE ProgramaId = :programaID AND estadomodulo = 'ACTIVO'"
            );
            $stmtCount->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmtCount->execute();
            $count = $stmtCount->fetch(PDO::FETCH_ASSOC);
            $totalModulos = intval($count['total']);

            if ($totalModulos === 0) {
                $pdo->rollBack();
                error_log("No hay módulos activos para el programa: " . $programaID);
                return false;
            }

            // 3. Calcular costo por módulo
            $costoPorModulo = $costoTotalPrograma / $totalModulos;

            // 4. Actualizar todos los módulos con el costo distribuido
            $stmtUpdate = $pdo->prepare(
                "UPDATE modulos
                 SET costomodulo = :costo
                 WHERE ProgramaId = :programaID AND estadomodulo = 'ACTIVO'"
            );
            $stmtUpdate->bindParam(":costo", $costoPorModulo, PDO::PARAM_STR);
            $stmtUpdate->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmtUpdate->execute();

            $pdo->commit();

            error_log("Costos distribuidos exitosamente: Programa ID $programaID, $totalModulos módulos, Bs. $costoPorModulo c/u");
            return true;

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en DistribuirCostoProgramaEnModulosModelo: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Obtener módulos de un programa con estado de pago por inscripción
     * Usa la tabla modulos (con 's') que tiene la estructura: ProgramaId, nombremodulo, codigomodulo
     * @param int $programaID
     * @param int $idinscripcion
     * @return array
     */
    public static function ObtenerModulosConEstadoPagoModelo($programaID, $idinscripcion)
    {
        // NOTA: Ya no es necesario distribuir costos porque los módulos
        // ya tienen su costo asignado cuando se registran
        // self::DistribuirCostoProgramaEnModulosModelo($programaID);

        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    m.Idmodulo as ModuloID,
                    m.nombremodulo as NombreModulo,
                    m.codigomodulo as Codigo,
                    m.costomodulo as Costo,
                    m.estadomodulo,
                    CONCAT(d.Nombre, ' ', d.Apaterno, ' ', d.Amaterno) as NombreDocente,
                    d.Especialidad as EspecialidadDocente,
                    pm.Idpagomodulo,
                    pm.costomodulo as CostoPagado,
                    pm.fechapago as FechaPago,
                    pm.nvaucher as NumeroVaucher,
                    pm.Estado as EstadoPago,
                    CASE
                        WHEN pm.Idpagomodulo IS NOT NULL THEN 1
                        ELSE 0
                    END as Pagado
                FROM modulos m
                LEFT JOIN docente d ON m.DocenteID = d.DocenteID
                LEFT JOIN pagomodulo pm ON m.Idmodulo = pm.IdModulo
                    AND pm.idinscripcion = :idinscripcion
                    AND pm.Estado != 'ANULADO'
                WHERE m.ProgramaId = :programaID
                AND m.estadomodulo = 'ACTIVO'
                ORDER BY m.codigomodulo ASC"
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
                    m.Idmodulo as ModuloID,
                    m.nombremodulo as NombreModulo,
                    m.codigomodulo as Codigo,
                    m.costomodulo as Costo,
                    m.estadomodulo as Estado,
                    CONCAT(d.Nombre, ' ', d.Apaterno, ' ', d.Amaterno) as NombreDocente
                FROM modulos m
                LEFT JOIN docente d ON m.DocenteID = d.DocenteID
                WHERE m.ProgramaId = :programaID
                AND m.estadomodulo = 'ACTIVO'
                ORDER BY m.codigomodulo ASC"
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
     * Ahora usa IdModulo como FK a la tabla modulos
     * @param array $datos - debe incluir: idinscripcion, IdModulo, costomodulo, fechapago, nvaucher, fmodulo
     * @return string
     */
    public static function RegistrarPagoModuloModelo($datos)
    {
        try {
            $pdo = Conexion::Conectar();
            $pdo->beginTransaction();

            // Verificar si ya existe un pago para este módulo en esta inscripción
            $stmtCheck = $pdo->prepare(
                "SELECT Idpagomodulo FROM pagomodulo
                 WHERE idinscripcion = :idinscripcion
                 AND IdModulo = :idModulo
                 AND Estado != 'ANULADO'"
            );
            $stmtCheck->bindParam(":idinscripcion", $datos['idinscripcion'], PDO::PARAM_INT);
            $stmtCheck->bindParam(":idModulo", $datos['IdModulo'], PDO::PARAM_INT);
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
                (idinscripcion, IdModulo, costomodulo, fechapago, nvaucher, fmodulo, Estado)
                VALUES (:idinscripcion, :idModulo, :costomodulo, :fechapago, :nvaucher, :fmodulo, 'PAGADO')"
            );

            $stmt->bindParam(":idinscripcion", $datos['idinscripcion'], PDO::PARAM_INT);
            $stmt->bindParam(":idModulo", $datos['IdModulo'], PDO::PARAM_INT);
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
                    pm.Idpagomodulo,
                    pm.idinscripcion,
                    pm.IdModulo,
                    m.nombremodulo as nmodulo,
                    m.codigomodulo,
                    pm.costomodulo,
                    pm.fechapago,
                    pm.nvaucher,
                    pm.Estado,
                    pm.FechaRegistro
                FROM pagomodulo pm
                LEFT JOIN modulos m ON pm.IdModulo = m.Idmodulo
                WHERE pm.idinscripcion = :idinscripcion
                AND pm.Estado != 'ANULADO'
                ORDER BY pm.fechapago DESC"
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
                    pm.Idpagomodulo,
                    pm.IdModulo,
                    m.nombremodulo as nmodulo,
                    m.codigomodulo,
                    pm.costomodulo,
                    pm.fechapago,
                    pm.nvaucher,
                    pm.Estado,
                    pm.FechaRegistro,
                    ep.ProgramaID,
                    p.NombrePrograma,
                    p.GradoAcademico
                FROM pagomodulo pm
                LEFT JOIN modulos m ON pm.IdModulo = m.Idmodulo
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
