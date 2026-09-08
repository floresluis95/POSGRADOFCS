<?php
/**
 * Modelo de Vouchers
 * Gestiona la consulta de vouchers de pago
 */

require_once 'conexion.modelo.php';

class VoucherModelo
{
    /**
     * Obtener vouchers de cuotas del programa pagadas por el estudiante.
     * Las cuotas ya no están ligadas a módulos: cada voucher corresponde a una cuota.
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

            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    Idpagomodulo as IdPago,
                    NumeroCuota,
                    nvaucher as NumeroVoucher,
                    fechapago as FechaPago,
                    montocuota as MontoTotal,
                    CASE WHEN fmodulo IS NOT NULL THEN 'SI' ELSE 'NO' END as TieneImagen
                FROM pagomodulo
                WHERE idinscripcion = :idinscripcion
                AND Estado = 'PAGADO'
                ORDER BY fechapago DESC"
            );
            $stmt->bindParam(":idinscripcion", $idinscripcion, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

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
