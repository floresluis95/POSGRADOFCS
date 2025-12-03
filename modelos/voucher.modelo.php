<?php
/**
 * Modelo de Vouchers
 * Gestiona la consulta de vouchers de pago
 */

require_once 'conexion.modelo.php';

class VoucherModelo
{
    /**
     * Obtener vouchers del programa del estudiante con módulos asociados
     * @param int $estudianteID
     * @param int $programaID
     * @return array
     */
    public static function ObtenerVouchersProgramaModelo($estudianteID, $programaID)
    {
        try {
            // Primero obtener el ID de inscripción del estudiante en el programa
            $stmtInsc = Conexion::Conectar()->prepare(
                "SELECT idInscripcion FROM estudianteprograma
                 WHERE EstudianteID = :estudianteID
                 AND ProgramaID = :programaID
                 AND Estado = 'ACTIVO'
                 LIMIT 1"
            );
            $stmtInsc->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
            $stmtInsc->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmtInsc->execute();
            $inscripcion = $stmtInsc->fetch(PDO::FETCH_ASSOC);

            if (!$inscripcion) {
                return [];
            }

            $idinscripcion = $inscripcion['idInscripcion'];

            // Obtener vouchers únicos (agrupados por número de voucher y fecha)
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    MIN(pm.Idpagomodulo) as IdPago,
                    pm.nvaucher as NumeroVoucher,
                    pm.fechapago as FechaPago,
                    SUM(pm.costomodulo) as MontoTotal,
                    MAX(CASE WHEN pm.fmodulo IS NOT NULL THEN 'SI' ELSE 'NO' END) as TieneImagen,
                    MAX(pm.fmodulo) as Imagen
                FROM pagomodulo pm
                WHERE pm.idinscripcion = :idinscripcion
                AND pm.Estado = 'PAGADO'
                GROUP BY pm.nvaucher, pm.fechapago
                ORDER BY pm.fechapago DESC"
            );
            $stmt->bindParam(":idinscripcion", $idinscripcion, PDO::PARAM_INT);
            $stmt->execute();
            $vouchers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Para cada voucher, obtener sus módulos asociados
            foreach ($vouchers as &$voucher) {
                $stmtModulos = Conexion::Conectar()->prepare(
                    "SELECT
                        m.nombremodulo as NombreModulo,
                        m.codigomodulo as CodigoModulo,
                        pm.costomodulo as CostoModulo
                    FROM pagomodulo pm
                    INNER JOIN modulos m ON pm.IdModulo = m.Idmodulo
                    WHERE pm.idinscripcion = :idinscripcion
                    AND pm.nvaucher = :nvaucher
                    AND pm.fechapago = :fechapago
                    AND pm.Estado = 'PAGADO'
                    ORDER BY m.codigomodulo"
                );
                $stmtModulos->bindParam(":idinscripcion", $idinscripcion, PDO::PARAM_INT);
                $stmtModulos->bindParam(":nvaucher", $voucher['NumeroVoucher'], PDO::PARAM_STR);
                $stmtModulos->bindParam(":fechapago", $voucher['FechaPago'], PDO::PARAM_STR);
                $stmtModulos->execute();
                $voucher['Modulos'] = $stmtModulos->fetchAll(PDO::FETCH_ASSOC);
            }

            return $vouchers;

        } catch (PDOException $e) {
            error_log("Error en ObtenerVouchersProgramaModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener imagen del voucher
     * @param int $idPago
     * @return mixed
     */
    public static function ObtenerImagenVoucherModelo($idPago)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT fmodulo FROM pagomodulo WHERE Idpagomodulo = :idPago"
            );
            $stmt->bindParam(":idPago", $idPago, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['fmodulo'] : null;
        } catch (PDOException $e) {
            error_log("Error en ObtenerImagenVoucherModelo: " . $e->getMessage());
            return null;
        }
    }
}
?>
