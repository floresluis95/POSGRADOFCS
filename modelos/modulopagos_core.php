<?php
/**
 * Modelo de Pago del Programa (cuotas)
 * Gestiona el registro de cuotas del programa por inscripción.
 * Las cuotas NO están ligadas a los módulos: su número y monto son libres
 * (Plan Regular), o una única cuota por el total (Plan Contado/Grupal).
 */

require_once 'conexion.modelo.php';
require_once __DIR__ . '/voucher.modelo.php';
require_once __DIR__ . '/pagoclinica.modelo.php';

class PagoModuloModelo
{
    /**
     * Porcentaje de descuento del Plan de Pago del Programa activo de una inscripción
     * (0 si no tiene plan o el plan no tiene descuento).
     * @param int $idinscripcion
     * @return float
     */
    private static function ObtenerPorcentajeDescuentoPlanModelo($idinscripcion)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT PorcentajeDescuento FROM pagoprograma
                 WHERE idInscripcion = :idinscripcion AND Estado != 'ANULADO' LIMIT 1"
            );
            $stmt->bindParam(":idinscripcion", $idinscripcion, PDO::PARAM_INT);
            $stmt->execute();
            $plan = $stmt->fetch(PDO::FETCH_ASSOC);
            return $plan ? floatval($plan['PorcentajeDescuento']) : 0.0;
        } catch (PDOException $e) {
            error_log("Error en ObtenerPorcentajeDescuentoPlanModelo: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Plan de pago del programa (tipo y descuento) de una inscripción, o null si no tiene.
     * @param int $idinscripcion
     * @return array|null
     */
    private static function ObtenerPlanProgramaModelo($idinscripcion)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT TipoPlan, NumeroCuotas, MontoCuota, PorcentajeDescuento, MontoTotalPagar, CostoTotalPrograma
                 FROM pagoprograma
                 WHERE idInscripcion = :idinscripcion AND Estado != 'ANULADO'
                 ORDER BY IdPagoPrograma DESC LIMIT 1"
            );
            $stmt->bindParam(":idinscripcion", $idinscripcion, PDO::PARAM_INT);
            $stmt->execute();
            $plan = $stmt->fetch(PDO::FETCH_ASSOC);
            return $plan ?: null;
        } catch (PDOException $e) {
            error_log("Error en ObtenerPlanProgramaModelo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Resumen de pago del programa para una inscripción: costo total (con el
     * descuento del plan ya aplicado), lo pagado (cuotas VALIDADAS/PAGADO), saldo
     * pendiente, y si el plan admite cuotas libres (Regular) o es de una sola
     * cuota por el total (Contado/Grupal).
     * @param int $idinscripcion
     * @param int $programaID
     * @return array
     */
    public static function ObtenerResumenPagoProgramaModelo($idinscripcion, $programaID)
    {
        try {
            $stmtPrograma = Conexion::Conectar()->prepare("SELECT Costo FROM programa WHERE ProgramaID = :programaID");
            $stmtPrograma->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmtPrograma->execute();
            $programa = $stmtPrograma->fetch(PDO::FETCH_ASSOC);
            $costoBase = $programa ? floatval($programa['Costo']) : 0.0;

            $plan = self::ObtenerPlanProgramaModelo($idinscripcion);
            $tipoPlan = $plan['TipoPlan'] ?? null;

            // Con plan, manda el monto PACTADO al crearlo (no cambia si después sube el
            // costo del programa), igual que en los reportes. Sin plan, el costo actual.
            $costoTotal = $plan
                ? round(floatval($plan['MontoTotalPagar']), 2)
                : round($costoBase, 2);

            $stmtPagado = Conexion::Conectar()->prepare(
                "SELECT COALESCE(SUM(montocuota), 0) as total, COUNT(*) as cantidad FROM pagomodulo
                 WHERE idinscripcion = :idinscripcion AND Estado = 'PAGADO'"
            );
            $stmtPagado->bindParam(":idinscripcion", $idinscripcion, PDO::PARAM_INT);
            $stmtPagado->execute();
            $filaPagado = $stmtPagado->fetch(PDO::FETCH_ASSOC);
            $totalPagado = round(floatval($filaPagado['total']), 2);
            $cuotasPagadasCount = (int)$filaPagado['cantidad'];

            $saldoPendiente = round($costoTotal - $totalPagado, 2);
            if ($saldoPendiente < 0) {
                $saldoPendiente = 0.0;
            }

            // Órdenes de cuota con voucher extraviado: el estudiante ya pagó, pero sin
            // voucher no cuentan como cobrado; sí se descuentan de lo que se puede pedir
            // en la orden siguiente (no se cobra dos veces lo mismo)
            $stmtEspera = Conexion::Conectar()->prepare(
                "SELECT COALESCE(SUM(MontoTotal), 0) AS monto, COUNT(*) AS cantidad FROM ordenpago
                 WHERE idinscripcion = :idinscripcion AND Estado = 'EXTRAVIADO' AND Observaciones = 'CUOTA PROGRAMA'"
            );
            $stmtEspera->execute([':idinscripcion' => $idinscripcion]);
            $filaEspera = $stmtEspera->fetch(PDO::FETCH_ASSOC);
            $montoEnEspera = round(floatval($filaEspera['monto']), 2);
            $disponibleOrden = max(0.0, round($saldoPendiente - $montoEnEspera, 2));

            // Todos los planes (Regular, Contado y Grupal) admiten cuotas de monto libre
            // hasta cubrir el saldo; el plan solo define el % de descuento del programa.
            return [
                'tienePlan' => $plan !== null,
                'tipoPlan' => $tipoPlan,
                'costoTotal' => $costoTotal,
                'totalPagado' => $totalPagado,
                'saldoPendiente' => $saldoPendiente,
                'completado' => $plan !== null && $saldoPendiente <= 0,
                // Número de cuotas planificado (solo Plan Regular; null si no se definió o no aplica)
                'numeroCuotasPlan' => isset($plan['NumeroCuotas']) ? (int)$plan['NumeroCuotas'] : null,
                // Monto de cada cuota pactado en Pre-Registro (la última ajusta); null en planes anteriores
                'montoCuotaPlan' => isset($plan['MontoCuota']) ? (float)$plan['MontoCuota'] : null,
                'cuotasPagadasCount' => $cuotasPagadasCount,
                // Voucher extraviado: monto y cuántas órdenes; disponibleOrden = saldo menos
                // ese monto (tope de la orden siguiente)
                'montoEnEspera' => $montoEnEspera,
                'cuotasEnEspera' => (int)$filaEspera['cantidad'],
                'disponibleOrden' => $disponibleOrden
            ];
        } catch (PDOException $e) {
            error_log("Error en ObtenerResumenPagoProgramaModelo: " . $e->getMessage());
            return [
                'tienePlan' => false, 'tipoPlan' => null, 'costoTotal' => 0,
                'totalPagado' => 0, 'saldoPendiente' => 0, 'completado' => false,
                'numeroCuotasPlan' => null, 'montoCuotaPlan' => null, 'cuotasPagadasCount' => 0,
                'montoEnEspera' => 0, 'cuotasEnEspera' => 0, 'disponibleOrden' => 0
            ];
        }
    }

    /**
     * Historial de cuotas (pagadas, pendientes, rechazadas) de una inscripción.
     * @param int $idinscripcion
     * @return array
     */
    public static function ObtenerCuotasPorInscripcionModelo($idinscripcion)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT pm.Idpagomodulo, pm.idinscripcion, pm.NumeroCuota, pm.montocuota, pm.fechapago,
                        pm.nvaucher, pm.Estado, pm.MotivoRechazo, pm.FechaRegistro,
                        op.FechaGeneracion AS FechaSolicitud,
                        CASE WHEN pm.fmodulo IS NOT NULL THEN 'SI' ELSE 'NO' END as TieneImagen
                 FROM pagomodulo pm
                 LEFT JOIN ordenpago op ON op.IdOrdenPago = pm.IdOrdenPago
                 WHERE pm.idinscripcion = :idinscripcion AND pm.Estado != 'ANULADO'
                 ORDER BY pm.FechaRegistro DESC"
            );
            $stmt->bindParam(":idinscripcion", $idinscripcion, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerCuotasPorInscripcionModelo: " . $e->getMessage());
            return [];
        }
    }


    /**
     * Busca la orden de pago PENDIENTE de una cuota del programa para esta inscripción
     * (emitida antes de pagar, desde la pestaña "Orden de Pago"). A diferencia del
     * esquema anterior, ya no se filtra por módulo: cualquier orden pendiente sin
     * lista de módulos asociada a esta inscripción cuenta como la orden de la cuota.
     * @return array|null fila de ordenpago
     */
    private static function BuscarOrdenPagoPendienteCuotaModelo($pdo, $idinscripcion)
    {
        $stmt = $pdo->prepare(
            "SELECT IdOrdenPago, MontoTotal FROM ordenpago
             WHERE idinscripcion = :idinscripcion AND Estado = 'PENDIENTE' AND ListaModulos IS NULL AND COALESCE(Observaciones, '') <> 'CLINICA'
             ORDER BY IdOrdenPago ASC LIMIT 1"
            // Por IdOrdenPago: la cuota de N° menor primero (las que genera el estudiante
            // juntas tienen la misma FechaGeneracion, y esa fecha se puede editar)
        );
        $stmt->bindParam(":idinscripcion", $idinscripcion, PDO::PARAM_INT);
        $stmt->execute();
        $orden = $stmt->fetch(PDO::FETCH_ASSOC);
        return $orden ?: null;
    }

    /**
     * N° de cuota de una orden: su posición entre las órdenes de cuota de la inscripción
     * (sin las anuladas). Así una orden con voucher extraviado conserva su número aunque
     * la siguiente se pague antes (con "pagadas + 1" las dos dirían la misma cuota).
     * Para las cuotas ya registradas da el mismo número que tenían.
     * @param PDO $pdo
     * @param int $idOrdenPago
     * @return int
     */
    public static function NumeroCuotaDeOrdenModelo($pdo, $idOrdenPago)
    {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM ordenpago o2
             INNER JOIN ordenpago o1 ON o1.IdOrdenPago = :id
             WHERE o2.idinscripcion = o1.idinscripcion AND o2.Observaciones = 'CUOTA PROGRAMA'
             AND o2.Estado <> 'ANULADO' AND o2.IdOrdenPago <= o1.IdOrdenPago"
        );
        $stmt->execute([':id' => (int)$idOrdenPago]);
        return max(1, (int)$stmt->fetchColumn());
    }

    /**
     * Órdenes de cuota de la inscripción con voucher extraviado (se les registra el
     * voucher cuando se recupera el código en Tesoro).
     * @param int $idinscripcion
     * @return array
     */
    public static function ListarCuotasExtraviadasModelo($idinscripcion)
    {
        return self::ListarCuotasSinPagarModelo($idinscripcion, ['EXTRAVIADO']);
    }

    /**
     * Órdenes de cuota que aún no tienen voucher validado: pendientes de pago y con
     * voucher extraviado. Los historiales las muestran como "Pendiente" junto a las
     * cuotas pagadas (antes solo se veían los pagos ya registrados).
     * @param int $idinscripcion
     * @param array $estados Estados de ordenpago a incluir
     * @return array Con NumeroCuota (posición de la orden)
     */
    public static function ListarCuotasSinPagarModelo($idinscripcion, array $estados = ['PENDIENTE', 'EXTRAVIADO'])
    {
        try {
            $pdo = Conexion::Conectar();
            $estados = array_values(array_intersect($estados, ['PENDIENTE', 'EXTRAVIADO']));
            if (!$estados) return [];
            $marcadores = implode(',', array_fill(0, count($estados), '?'));
            $stmt = $pdo->prepare(
                "SELECT IdOrdenPago, NumeroOrden, MontoTotal, FechaGeneracion, FechaVencimiento, Estado,
                        MotivoExtraviado, FechaExtraviado, ResponsableExtraviado
                 FROM ordenpago
                 WHERE idinscripcion = ? AND Estado IN ($marcadores) AND Observaciones = 'CUOTA PROGRAMA'
                 ORDER BY IdOrdenPago ASC"
            );
            $stmt->execute(array_merge([$idinscripcion], $estados));
            $ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($ordenes as &$orden) {
                $orden['NumeroCuota'] = self::NumeroCuotaDeOrdenModelo($pdo, $orden['IdOrdenPago']);
            }
            unset($orden);
            return $ordenes;
        } catch (PDOException $e) {
            error_log("Error en ListarCuotasSinPagarModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Registrar el pago (con voucher) de una cuota del programa.
     * Confirma la orden pendiente de la inscripción o, si se indica IdOrdenPago, esa
     * orden (la de un voucher extraviado que ya se recuperó).
     * @param array $datos idinscripcion, montocuota, fechapago, nvaucher, fmodulo, IdOrdenPago (opcional)
     * @return string 'exitoso'|'sin_orden'|'monto_distinto'|'error'
     */
    public static function RegistrarCuotaProgramaModelo($datos)
    {
        try {
            $pdo = Conexion::Conectar();
            $pdo->beginTransaction();

            if (!empty($datos['IdOrdenPago'])) {
                $stmtOrden = $pdo->prepare(
                    "SELECT IdOrdenPago, MontoTotal FROM ordenpago
                     WHERE IdOrdenPago = :id AND idinscripcion = :idinscripcion
                     AND Estado IN ('PENDIENTE', 'EXTRAVIADO') AND Observaciones = 'CUOTA PROGRAMA'
                     FOR UPDATE"
                );
                $stmtOrden->execute([':id' => (int)$datos['IdOrdenPago'], ':idinscripcion' => $datos['idinscripcion']]);
                $orden = $stmtOrden->fetch(PDO::FETCH_ASSOC) ?: null;
            } else {
                $orden = self::BuscarOrdenPagoPendienteCuotaModelo($pdo, $datos['idinscripcion']);
            }
            if ($orden === null) {
                $pdo->rollBack();
                return "sin_orden";
            }

            // El voucher debe ser por el monto exacto de la orden que confirma
            if (abs(floatval($datos['montocuota']) - floatval($orden['MontoTotal'])) > 0.01) {
                $pdo->rollBack();
                return "monto_distinto";
            }

            $numeroCuota = self::NumeroCuotaDeOrdenModelo($pdo, $orden['IdOrdenPago']);

            $fmodulo = !empty($datos['fmodulo']) ? $datos['fmodulo'] : null;

            $stmt = $pdo->prepare(
                "INSERT INTO pagomodulo
                (idinscripcion, IdOrdenPago, NumeroCuota, montocuota, fechapago, nvaucher, fmodulo, Estado)
                VALUES (:idinscripcion, :idOrdenPago, :numeroCuota, :montocuota, :fechapago, :nvaucher, :fmodulo, 'PAGADO')"
            );
            $stmt->bindParam(":idinscripcion", $datos['idinscripcion'], PDO::PARAM_INT);
            $stmt->bindParam(":idOrdenPago", $orden['IdOrdenPago'], PDO::PARAM_INT);
            $stmt->bindParam(":numeroCuota", $numeroCuota, PDO::PARAM_INT);
            $stmt->bindParam(":montocuota", $datos['montocuota'], PDO::PARAM_STR);
            $stmt->bindParam(":fechapago", $datos['fechapago'], PDO::PARAM_STR);
            $stmt->bindParam(":nvaucher", $datos['nvaucher'], PDO::PARAM_STR);
            $stmt->bindParam(":fmodulo", $fmodulo, PDO::PARAM_LOB);

            if (!$stmt->execute()) {
                $pdo->rollBack();
                error_log("Error al ejecutar INSERT en pagomodulo: " . print_r($stmt->errorInfo(), true));
                return "error";
            }

            $upd = $pdo->prepare(
                "UPDATE ordenpago SET Estado = 'CONFIRMADO', FechaConfirmacion = NOW() WHERE IdOrdenPago = :id"
            );
            $upd->bindParam(":id", $orden['IdOrdenPago'], PDO::PARAM_INT);
            $upd->execute();

            $pdo->commit();
            return "exitoso";
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en RegistrarCuotaProgramaModelo: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Rechazar el voucher de una cuota: queda disponible para volver a intentarse
     * (no se anula el compromiso de pago, solo el comprobante presentado).
     * @param int $idpago
     * @param string|null $motivo
     * @return bool
     */
    public static function RechazarCuotaModelo($idpago, $motivo = null)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "UPDATE pagomodulo SET Estado = 'RECHAZADO', MotivoRechazo = :motivo WHERE Idpagomodulo = :id"
            );
            $stmt->bindParam(":motivo", $motivo, PDO::PARAM_STR);
            $stmt->bindParam(":id", $idpago, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en RechazarCuotaModelo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Anular una cuota registrada.
     * @param int $idpago
     * @return bool
     */
    public static function AnularPagoModuloModelo($idpago)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "UPDATE pagomodulo SET Estado = 'ANULADO' WHERE Idpagomodulo = :id"
            );
            $stmt->bindParam(":id", $idpago, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en AnularPagoModuloModelo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener cuotas pagadas por estudiante (todas sus inscripciones).
     * @param int $estudianteID
     * @return array
     */
    public static function ObtenerPagosModulosPorEstudianteModelo($estudianteID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    pm.Idpagomodulo,
                    pm.NumeroCuota,
                    pm.montocuota,
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
                ORDER BY pm.FechaRegistro DESC"
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
     * Obtener programas inscritos del estudiante con ID de inscripción
     * @param int $estudianteID
     * @return array
     */
    public static function ObtenerProgramasEstudianteConInscripcionModelo($estudianteID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    ep.idInscripcion,
                    ep.ProgramaID,
                    p.NombrePrograma,
                    p.GradoAcademico,
                    p.CostoMatricula,
                    ep.FechaInscripcion,
                    ep.Estado as EstadoInscripcion
                FROM estudianteprograma ep
                INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
                WHERE ep.EstudianteID = :estudianteID
                AND ep.Estado = 'ACTIVO'
                ORDER BY ep.FechaInscripcion DESC"
            );
            $stmt->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerProgramasEstudianteConInscripcionModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Detalle de pago del programa (resumen + cuotas) para el estudiante, por programa.
     * @param int $estudianteID
     * @param int $programaID
     * @return array
     */
    public static function ObtenerDetalleModulosEstudianteModelo($estudianteID, $programaID)
    {
        try {
            $stmtInsc = Conexion::Conectar()->prepare(
                "SELECT ep.idInscripcion, ep.costomatricula, ep.montoPagado, ep.nvauchermatricula, ep.FechaInscripcion,
                        ep.foto IS NOT NULL AS TieneFotoMatricula,
                        " . VoucherModelo::SqlFechaOrdenMatricula('ep.idInscripcion') . " AS FechaOrdenMatricula
                 FROM estudianteprograma ep
                 WHERE ep.EstudianteID = :estudianteID
                 AND ep.ProgramaID = :programaID
                 AND ep.Estado = 'ACTIVO'
                 LIMIT 1"
            );
            $stmtInsc->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
            $stmtInsc->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmtInsc->execute();
            $inscripcion = $stmtInsc->fetch(PDO::FETCH_ASSOC);

            if (!$inscripcion) {
                return ['resumen' => null, 'cuotas' => [], 'clinicas' => [], 'matricula' => null];
            }

            $idinscripcion = $inscripcion['idInscripcion'];

            return [
                // Para el recibo de pagos y el comprobante de la matrícula
                'idInscripcion' => (int)$idinscripcion,
                'resumen' => self::ObtenerResumenPagoProgramaModelo($idinscripcion, $programaID),
                'cuotas' => self::ObtenerCuotasPorInscripcionModelo($idinscripcion),
                // Cuotas con orden pero sin voucher validado (se ven como Pendiente)
                'cuotasPorPagar' => self::ListarCuotasSinPagarModelo($idinscripcion),
                // Clínicas: aparte de las cuotas (no cuentan para el saldo del programa)
                'clinicas' => PagoClinicaModelo::ListarPorInscripcionModelo($idinscripcion),
                'matricula' => [
                    'costo' => round(floatval($inscripcion['costomatricula']), 2),
                    'montoPagado' => round(floatval($inscripcion['montoPagado']), 2),
                    'voucher' => $inscripcion['nvauchermatricula'],
                    'fecha' => $inscripcion['FechaInscripcion'],
                    'fechaOrden' => $inscripcion['FechaOrdenMatricula'],
                    'tieneFoto' => (bool)$inscripcion['TieneFotoMatricula'],
                    // Exonerada o al Contado sin matrícula: no hubo pago de matrícula
                    'sinMatricula' => mb_strtoupper((string)$inscripcion['nvauchermatricula'], 'UTF-8') === 'SIN MATRÍCULA',
                    'pagada' => floatval($inscripcion['montoPagado']) >= floatval($inscripcion['costomatricula']) && floatval($inscripcion['costomatricula']) > 0
                ]
            ];
        } catch (PDOException $e) {
            error_log("Error en ObtenerDetalleModulosEstudianteModelo: " . $e->getMessage());
            return ['resumen' => null, 'cuotas' => [], 'clinicas' => [], 'matricula' => null];
        }
    }
}
