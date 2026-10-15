<?php
/**
 * Modelo de Vouchers
 * Gestiona la consulta de vouchers de pago
 */

require_once 'conexion.modelo.php';

class VoucherModelo
{
    /**
     * Subconsulta con la fecha en que se generó la orden de pago de la MATRÍCULA de una
     * inscripción, para mostrarla junto al voucher en historiales y recibos. Es la orden
     * confirmada de la inscripción que no es de cuota ni de clínica (la que se validó en
     * Inscripción). Las cuotas y clínicas no la necesitan: enlazan su orden por IdOrdenPago.
     * @param string $columnaInscripcion Columna con el idInscripcion (ej. 'ep.idInscripcion')
     * @return string
     */
    public static function SqlFechaOrdenMatricula($columnaInscripcion)
    {
        return "(SELECT opm.FechaGeneracion FROM ordenpago opm
                 WHERE opm.idinscripcion = $columnaInscripcion AND " . self::CONDICION_ORDEN_MATRICULA . "
                 ORDER BY opm.IdOrdenPago ASC LIMIT 1)";
    }

    /** Qué orden (alias opm) es la de la matrícula: la primera confirmada que no es cuota ni clínica. */
    const CONDICION_ORDEN_MATRICULA = "opm.Estado = 'CONFIRMADO' AND opm.ListaModulos IS NULL
                 AND COALESCE(opm.Observaciones, '') NOT IN ('CUOTA PROGRAMA', 'CLINICA')";

    /**
     * IdOrdenPago de la orden de matrícula de una inscripción (la misma que muestra
     * SqlFechaOrdenMatricula), para poder corregir su fecha desde el Historial de Pagos.
     * @return int|null
     */
    public static function IdOrdenMatriculaModelo($idInscripcion, $pdo = null)
    {
        $stmt = ($pdo ?: Conexion::Conectar())->prepare(
            "SELECT opm.IdOrdenPago FROM ordenpago opm
             WHERE opm.idinscripcion = :id AND " . self::CONDICION_ORDEN_MATRICULA . "
             ORDER BY opm.IdOrdenPago ASC LIMIT 1"
        );
        $stmt->execute([':id' => (int)$idInscripcion]);
        $id = $stmt->fetchColumn();
        return $id !== false ? (int)$id : null;
    }

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

    /**
     * El N° de voucher solo lleva dígitos, con un guion opcional entre la parte larga y
     * la corta: "1469337" o "1469337-002" (voucher-partes.js lo arma así en pantalla).
     * @param string $numeroVoucher
     * @return bool
     */
    public static function FormatoVoucherValido($numeroVoucher)
    {
        return (bool)preg_match('/^\d+(-\d+)?$/', trim((string)$numeroVoucher));
    }

    /** Mensaje para el usuario cuando el N° de voucher no tiene el formato. */
    const MENSAJE_FORMATO_VOUCHER = 'El N° de voucher solo puede tener números (y un guion entre la parte larga y la corta, ej.: 1469337-002)';

    /** Mensaje para el usuario cuando el voucher ya fue usado. */
    public static function MensajeVoucherRepetido($numeroVoucher, $donde)
    {
        return 'El N° de voucher ' . $numeroVoucher . ' ya fue registrado en ' . $donde
            . '. Cada voucher es único: verifique el número.';
    }

    /**
     * Obtener vouchers de cuotas del programa y de clínicas pagadas por el estudiante.
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

            // Cuotas y clínicas pagadas (Tipo distingue de qué tabla sale la foto:
            // obtener-voucher-imagen.php o obtener-voucher-clinica.php)
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    'CUOTA' as Tipo,
                    pm.Idpagomodulo as IdPago,
                    pm.NumeroCuota,
                    pm.nvaucher as NumeroVoucher,
                    pm.fechapago as FechaPago,
                    pm.montocuota as MontoTotal,
                    op.FechaGeneracion as FechaOrden,
                    CASE WHEN pm.fmodulo IS NOT NULL THEN 'SI' ELSE 'NO' END as TieneImagen,
                    0 as EsPdf
                FROM pagomodulo pm
                LEFT JOIN ordenpago op ON op.IdOrdenPago = pm.IdOrdenPago
                WHERE pm.idinscripcion = :idinscripcion
                AND pm.Estado = 'PAGADO'
                UNION ALL
                SELECT
                    'CLINICA',
                    pcl.IdPagoClinica,
                    NULL,
                    pcl.NumeroVoucher,
                    pcl.FechaPago,
                    pcl.Monto,
                    op.FechaGeneracion,
                    CASE WHEN pcl.FotoVoucher IS NOT NULL THEN 'SI' ELSE 'NO' END,
                    (LEFT(pcl.FotoVoucher, 4) = '%PDF')
                FROM pagoclinica pcl
                LEFT JOIN ordenpago op ON op.IdOrdenPago = pcl.IdOrdenPago
                WHERE pcl.idinscripcion = :idinscripcionClinica
                AND pcl.Estado = 'PAGADO'
                ORDER BY FechaPago DESC"
            );
            $stmt->bindParam(":idinscripcion", $idinscripcion, PDO::PARAM_INT);
            $stmt->bindParam(":idinscripcionClinica", $idinscripcion, PDO::PARAM_INT);
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
