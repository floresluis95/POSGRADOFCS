<?php
require_once file_exists(__DIR__ . '/conexion.modelo.php') ? __DIR__ . '/conexion.modelo.php' : __DIR__ . '/../core/conexion.modelo.php';
require_once __DIR__ . '/certificado.modelo.php'; // nombres de los tipos de certificado

/**
 * Pagos recibidos (página "pagos"): todo lo cobrado en un solo listado.
 *   MATRICULA    estudianteprograma.montoPagado (voucher de matrícula)
 *   CUOTAS       pagomodulo PAGADO
 *   CLINICA      pagoclinica PAGADO
 *   CERTIFICADO  documentoemitido con monto (también los "sin documento")
 * Matrícula, cuotas y clínicas siguen la misma regla que ReportesModelos::MovimientosPagados
 * (Reportes › Por cuenta), así los totales coinciden con ese reporte.
 */
class PagosModelo
{
    const CONCEPTOS = [
        'MATRICULA' => 'Matrícula',
        'CUOTAS' => 'Cuotas',
        'CLINICA' => 'Clínicas',
        'CERTIFICADO' => 'Certificados',
    ];

    // Más filas no caben con comodidad en pantalla: se avisa que acote el periodo
    const LIMITE = 3000;

    // Cuenta Principal activa de un tipo para el programa de la columna dada (misma regla que Reportes)
    private static function SqlCuentaPrincipal($tipo, $columnaPrograma)
    {
        return "(SELECT pc.ProgramaCuentaID FROM programacuenta pc
                 WHERE pc.ProgramaID = $columnaPrograma AND pc.TipoCuenta = '$tipo'
                 AND pc.Principal = 1 AND pc.Estado = 1 LIMIT 1)";
    }

    /**
     * @param array $f desde, hasta (AAAA-MM-DD), concepto, sede, programaID, estudiante (nombre o C.I.)
     * @return array ['pagos' => [...], 'recortado' => bool]
     */
    public static function Listar(array $f)
    {
        $condiciones = [];
        $params = [];

        if (!empty($f['desde'])) {
            $condiciones[] = 'mov.Fecha >= :desde';
            $params[':desde'] = $f['desde'];
        }
        if (!empty($f['hasta'])) {
            $condiciones[] = 'mov.Fecha <= :hasta';
            $params[':hasta'] = $f['hasta'];
        }
        if (!empty($f['concepto']) && isset(self::CONCEPTOS[$f['concepto']])) {
            $condiciones[] = 'mov.Concepto = :concepto';
            $params[':concepto'] = $f['concepto'];
        }
        if (!empty($f['sede'])) {
            $condiciones[] = 'p.Sede = :sede';
            $params[':sede'] = $f['sede'];
        }
        if (!empty($f['programaID'])) {
            $condiciones[] = 'p.ProgramaID = :programaID';
            $params[':programaID'] = (int)$f['programaID'];
        }
        // Estudiante: solo números = C.I. (empieza por); si no, cada palabra en el nombre, en cualquier orden
        $estudiante = trim(preg_replace('/\s+/u', ' ', (string)($f['estudiante'] ?? '')));
        if ($estudiante !== '') {
            if (ctype_digit($estudiante)) {
                $condiciones[] = 'e.Ci LIKE :ci';
                $params[':ci'] = $estudiante . '%';
            } else {
                foreach (array_slice(explode(' ', $estudiante), 0, 6) as $i => $palabra) {
                    $condiciones[] = "CONCAT_WS(' ', e.Nombre, e.Apaterno, e.Amaterno) LIKE :palabra$i";
                    $params[":palabra$i"] = '%' . addcslashes($palabra, '%_\\') . '%';
                }
            }
        }

        $ordenMatricula = "(
            SELECT o.IdOrdenPago FROM ordenpago o
            WHERE o.idinscripcion = ep.idInscripcion AND o.Estado = 'CONFIRMADO'
            AND o.ListaModulos IS NULL AND o.ListaPagosModulo IS NULL
            AND COALESCE(o.Observaciones, '') NOT IN ('CUOTA PROGRAMA', 'CLINICA')
            ORDER BY o.IdOrdenPago LIMIT 1
        )";

        $sql = "
            SELECT mov.Fecha, mov.Concepto, mov.Detalle, mov.Monto, mov.Voucher,
                   p.ProgramaID, p.NombrePrograma, p.Codigo AS CodigoPrograma, p.Sede,
                   pc.Banco, pc.NumeroCuenta,
                   e.EstudianteID, e.Ci, e.Complemento, e.Exp,
                   TRIM(CONCAT_WS(' ', e.Apaterno, e.Amaterno, e.Nombre)) AS Estudiante
            FROM (
                SELECT COALESCE(op.ProgramaCuentaID, " . self::SqlCuentaPrincipal('MATRICULA', 'ep.ProgramaID') . ") AS CuentaID,
                       ep.ProgramaID, ep.EstudianteID, 'MATRICULA' AS Concepto, NULL AS Detalle,
                       ep.montoPagado AS Monto, ep.FechaInscripcion AS Fecha, ep.nvauchermatricula AS Voucher
                FROM estudianteprograma ep
                LEFT JOIN ordenpago op ON op.IdOrdenPago = $ordenMatricula
                WHERE ep.montoPagado > 0

                UNION ALL

                SELECT COALESCE(op.ProgramaCuentaID, " . self::SqlCuentaPrincipal('CUOTAS', 'ep.ProgramaID') . "),
                       ep.ProgramaID, ep.EstudianteID, 'CUOTAS',
                       CASE WHEN pm.NumeroCuota IS NOT NULL THEN CONCAT('Cuota N° ', pm.NumeroCuota) END,
                       pm.montocuota, pm.fechapago, pm.nvaucher
                FROM pagomodulo pm
                INNER JOIN estudianteprograma ep ON ep.idInscripcion = pm.idinscripcion
                LEFT JOIN ordenpago op ON op.IdOrdenPago = pm.IdOrdenPago
                WHERE pm.Estado = 'PAGADO'

                UNION ALL

                SELECT COALESCE(op.ProgramaCuentaID, " . self::SqlCuentaPrincipal('CLINICA', 'op.ProgramaID') . "),
                       op.ProgramaID, ep.EstudianteID, 'CLINICA', NULL,
                       pcl.Monto, pcl.FechaPago, pcl.NumeroVoucher
                FROM pagoclinica pcl
                INNER JOIN ordenpago op ON op.IdOrdenPago = pcl.IdOrdenPago
                INNER JOIN estudianteprograma ep ON ep.idInscripcion = pcl.idinscripcion
                WHERE pcl.Estado = 'PAGADO'

                UNION ALL

                -- Certificados: fecha en que se pagó el voucher; si no se anotó, la de emisión.
                -- documentoemitido es utf8mb4_general_ci y las tablas de pagos utf8mb4_unicode_ci:
                -- sin el COLLATE, el UNION falla con 'Illegal mix of collations'
                SELECT NULL, d.ProgramaID, d.EstudianteID, 'CERTIFICADO',
                       CAST(COALESCE(d.NombreCertificado, d.TipoDocumento) AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci,
                       d.Monto, COALESCE(d.FechaPago, DATE(d.FechaEmision)),
                       CAST(d.NumeroVaucher AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci
                FROM documentoemitido d
                WHERE d.Monto > 0
            ) mov
            INNER JOIN programa p ON p.ProgramaID = mov.ProgramaID
            INNER JOIN estudiante e ON e.EstudianteID = mov.EstudianteID
            LEFT JOIN programacuenta pc ON pc.ProgramaCuentaID = mov.CuentaID
            " . ($condiciones ? 'WHERE ' . implode(' AND ', $condiciones) : '') . "
            ORDER BY mov.Fecha DESC, Estudiante
            LIMIT " . (self::LIMITE + 1);

        $stmt = Conexion::Conectar()->prepare($sql);
        $stmt->execute($params);
        $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $recortado = count($pagos) > self::LIMITE;
        if ($recortado) {
            array_pop($pagos);
        }

        // Nombre legible del certificado ("CALIFICACIONES" -> "Certificado de calificaciones")
        $tipos = CertificadoModelo::TiposParaListado();
        foreach ($pagos as &$pago) {
            if ($pago['Concepto'] === 'CERTIFICADO') {
                $pago['Detalle'] = $tipos[$pago['Detalle']] ?? $pago['Detalle'];
            }
        }
        unset($pago);

        return ['pagos' => $pagos, 'recortado' => $recortado];
    }

    // Programas para el filtro (también los ya cerrados: sus pagos siguen contando)
    public static function ProgramasFiltro()
    {
        $stmt = Conexion::Conectar()->prepare(
            "SELECT ProgramaID, NombrePrograma, Codigo, Sede FROM programa ORDER BY FechaInicio DESC, NombrePrograma"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
