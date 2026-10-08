<?php
/**
 * Modelo de Vouchers
 * Gestiona la consulta de vouchers de pago
 */

require_once 'conexion.modelo.php';

class VoucherModelo
{
    /**
     * El N° de voucher es ÚNICO en todo el sistema: un mismo depósito no puede
     * registrarse dos veces, ni en dos matrículas, ni en dos cuotas, ni en una
     * matrícula y una cuota o clínica. Compara sin distinguir mayúsculas ni espacios.
     * Las cuotas/clínicas ANULADAS no cuentan (su voucher queda libre).
     * @param string $numeroVoucher
     * @param PDO|null $pdo Conexión de una transacción ya abierta (opcional)
     * @return string|null Dónde se usó (para el mensaje), o null si está libre
     */
    public static function VoucherYaUsadoModelo($numeroVoucher, $pdo = null)
    {
        $numero = trim((string)$numeroVoucher);
        if ($numero === '' || mb_strtoupper($numero, 'UTF-8') === 'SIN MATRÍCULA') {
            return null;
        }

        $pdo = $pdo ?: Conexion::Conectar();
        $consultas = [
            'la matrícula de' =>
                "SELECT CONCAT(CONCAT_WS(' ', e.Nombre, e.Apaterno, e.Amaterno), ' (C.I. ', e.Ci, ')')
                 FROM estudianteprograma ep INNER JOIN estudiante e ON e.EstudianteID = ep.EstudianteID
                 WHERE LOWER(TRIM(ep.nvauchermatricula)) = LOWER(:numero) LIMIT 1",
            'una cuota de' =>
                "SELECT CONCAT(CONCAT_WS(' ', e.Nombre, e.Apaterno, e.Amaterno), ' (C.I. ', e.Ci, ')')
                 FROM pagomodulo pm
                 INNER JOIN estudianteprograma ep ON ep.idInscripcion = pm.idinscripcion
                 INNER JOIN estudiante e ON e.EstudianteID = ep.EstudianteID
                 WHERE LOWER(TRIM(pm.nvaucher)) = LOWER(:numero) AND pm.Estado <> 'ANULADO' LIMIT 1",
            'un pago de clínica de' =>
                "SELECT CONCAT(CONCAT_WS(' ', e.Nombre, e.Apaterno, e.Amaterno), ' (C.I. ', e.Ci, ')')
                 FROM pagoclinica pc
                 INNER JOIN estudianteprograma ep ON ep.idInscripcion = pc.idinscripcion
                 INNER JOIN estudiante e ON e.EstudianteID = ep.EstudianteID
                 WHERE LOWER(TRIM(pc.NumeroVoucher)) = LOWER(:numero) AND pc.Estado <> 'ANULADO' LIMIT 1",
        ];

        foreach ($consultas as $donde => $sql) {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':numero' => $numero]);
            $estudiante = $stmt->fetchColumn();
            if ($estudiante !== false) {
                return $donde . ' ' . $estudiante;
            }
        }
        return null;
    }

    /** Mensaje para el usuario cuando el voucher ya fue usado. */
    public static function MensajeVoucherRepetido($numeroVoucher, $donde)
    {
        return 'El N° de voucher ' . $numeroVoucher . ' ya fue registrado en ' . $donde
            . '. Cada voucher es único: verifique el número.';
    }

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
    /**
     * ¿El pago (voucher) es de una inscripción de ese estudiante? Para que un
     * estudiante solo pueda ver SUS vouchers aunque cambie el id en la URL.
     * @return bool
     */
    public static function VoucherEsDelEstudianteModelo($idPago, $estudianteID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT 1 FROM pagomodulo pm
                 INNER JOIN estudianteprograma ep ON ep.idInscripcion = pm.idinscripcion
                 WHERE pm.Idpagomodulo = :idPago AND ep.EstudianteID = :estudianteID"
            );
            $stmt->execute([':idPago' => (int)$idPago, ':estudianteID' => (int)$estudianteID]);
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en VoucherEsDelEstudianteModelo: " . $e->getMessage());
            return false;
        }
    }

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
