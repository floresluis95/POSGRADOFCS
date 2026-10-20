<?php
require_once __DIR__ . '/../core/conexion.modelo.php';

/**
 * Modelo de Plan de Pago del Programa (posgrado)
 * Guarda el TIPO de plan elegido (Regular / Contado con descuento / Grupal) y su
 * porcentaje de descuento. El pago en si (de cada modulo real del programa) se
 * registra siempre desde "Registrar Pago de Modulo" (pagomodulo); el descuento
 * definido aqui se aplica automaticamente al costo de cada modulo pendiente
 * (ver ModuloPagosCore::ObtenerModulosConEstadoPagoModelo).
 */
class PagoProgramaModelos
{
    /**
     * Monto de cada cuota del programa por defecto: el total entre el N° de cuotas;
     * si no sale exacto, redondeado hacia arriba a decenas (30000 / 11 = 2727,27 ->
     * 2730) y la última cuota ajusta la diferencia (30000 - 10 x 2730 = 2700).
     * Si redondear a decenas dejara la última en 0 o menos (muchas cuotas de un
     * total chico), se redondea a bolivianos y, si aún no alcanza, a centavos.
     * La misma regla está en Pre-Registro (preregistro.php, cuotaRedondeada).
     * @param float $total
     * @param int $numeroCuotas
     * @return float
     */
    public static function CuotaRedondeada($total, $numeroCuotas)
    {
        $total = round((float)$total, 2);
        $n = max(1, (int)$numeroCuotas);
        if ($n === 1 || $total <= 0) return $total;

        $base = $total / $n;
        if (abs($base - round($base)) < 0.005) return round($base, 2);

        foreach ([10, 1] as $multiplo) {
            $cuota = ceil(round($base, 6) / $multiplo) * $multiplo;
            if (round($total - $cuota * ($n - 1), 2) > 0) return (float)$cuota;
        }
        return round($base, 2);
    }

    /**
     * Revisa un monto de cuota escrito a mano: positivo y que la última cuota
     * (el total menos las demás) quede mayor a 0. Devuelve el mensaje de error o null.
     * @param float $montoCuota
     * @param float $total
     * @param int $numeroCuotas
     * @return string|null
     */
    public static function ValidarMontoCuota($montoCuota, $total, $numeroCuotas)
    {
        $n = max(1, (int)$numeroCuotas);
        if ($montoCuota <= 0) return 'El monto de la cuota debe ser mayor a 0';
        if ($n === 1) {
            return abs($montoCuota - $total) < 0.005 ? null : 'Con una sola cuota, la cuota debe ser el total del programa';
        }
        if (round($total - $montoCuota * ($n - 1), 2) <= 0) {
            return 'Con ' . $n . ' cuotas de Bs. ' . number_format($montoCuota, 2, ',', '.') . ' se pasa del total del programa (Bs. '
                . number_format($total, 2, ',', '.') . '): la última cuota quedaría en 0 o menos';
        }
        return null;
    }

    /**
     * Inserta el plan de pago (pagoprograma) usando una conexión/transacción
     * ya abierta por el llamador (no hace beginTransaction/commit/rollBack).
     * @param PDO $pdo
     * @param array $datos idInscripcion, EstudianteID, ProgramaID, CostoTotalPrograma, TipoPlan,
     *                      PorcentajeDescuento, CodigoGrupo, CantidadInscritosGrupo,
     *                      ResponsableGeneracion, Observaciones
     * @return array
     */
    public static function InsertarPlanEnTransaccion($pdo, $datos)
    {
        // Un plan activo por inscripción
        $stmtCheck = $pdo->prepare(
            "SELECT IdPagoPrograma FROM pagoprograma WHERE idInscripcion = :idInscripcion AND Estado != 'ANULADO'"
        );
        $stmtCheck->bindParam(":idInscripcion", $datos['idInscripcion'], PDO::PARAM_INT);
        $stmtCheck->execute();
        if ($stmtCheck->fetch()) {
            return ['status' => 'duplicado', 'mensaje' => 'Esta inscripción ya tiene un plan de pago del programa registrado'];
        }

        $costoTotal = round(floatval($datos['CostoTotalPrograma']), 2);
        $porcentaje = isset($datos['PorcentajeDescuento']) ? floatval($datos['PorcentajeDescuento']) : 0;
        // % negativo = aumento del programa (Pre-Registro, "Aumento"), hasta 100%
        if ($porcentaje < -100) $porcentaje = -100;
        if ($porcentaje > 100) $porcentaje = 100;
        $montoTotalPagar = round($costoTotal - round($costoTotal * $porcentaje / 100, 2), 2);
        // Total del programa escrito a mano en Pre-Registro (en Bs.): manda ese monto;
        // el % guardado es solo su equivalente redondeado
        if (isset($datos['MontoTotalPagar']) && $datos['MontoTotalPagar'] !== null && floatval($datos['MontoTotalPagar']) > 0) {
            $montoTotalPagar = round(floatval($datos['MontoTotalPagar']), 2);
        }
        // MontoDescuento solo guarda descuentos (con aumento queda en 0)
        $montoDescuento = max(0, round($costoTotal - $montoTotalPagar, 2));

        $tipoPlan = in_array($datos['TipoPlan'], ['REGULAR', 'DESCUENTO', 'GRUPAL']) ? $datos['TipoPlan'] : 'REGULAR';
        $codigoGrupo = !empty($datos['CodigoGrupo']) ? $datos['CodigoGrupo'] : null;
        $cantidadGrupo = isset($datos['CantidadInscritosGrupo']) ? (int)$datos['CantidadInscritosGrupo'] : 1;
        $responsable = $datos['ResponsableGeneracion'] ?? null;
        $observaciones = $datos['Observaciones'] ?? null;
        // Número de cuotas en que se paga el programa (los tres planes; Al Contado, máximo 3).
        // Lo usan el compromiso y la orden de cada cuota ("Cuota X de N").
        $numeroCuotas = !empty($datos['NumeroCuotas'])
            ? min($tipoPlan === 'DESCUENTO' ? 3 : 60, max(1, (int)$datos['NumeroCuotas']))
            : null;
        // Monto de cada cuota pactado en Pre-Registro (la última ajusta la diferencia).
        // Si no viene o ya no cuadra con el total (p. ej. cambió el costo del
        // programa), se usa el redondeado por defecto.
        $montoCuota = null;
        if ($numeroCuotas !== null) {
            $montoCuota = isset($datos['MontoCuota']) && $datos['MontoCuota'] !== null ? round(floatval($datos['MontoCuota']), 2) : null;
            if ($montoCuota === null || self::ValidarMontoCuota($montoCuota, $montoTotalPagar, $numeroCuotas) !== null) {
                $montoCuota = self::CuotaRedondeada($montoTotalPagar, $numeroCuotas);
            }
        }

        $stmt = $pdo->prepare(
            "INSERT INTO pagoprograma
            (idInscripcion, EstudianteID, ProgramaID, CostoTotalPrograma, TipoPlan, NumeroCuotas, MontoCuota,
             PorcentajeDescuento, MontoDescuento, MontoTotalPagar,
             CodigoGrupo, CantidadInscritosGrupo, ResponsableGeneracion, Observaciones, Estado)
            VALUES
            (:idInscripcion, :estudianteID, :programaID, :costoTotal, :tipoPlan, :numeroCuotas, :montoCuota,
             :porcentaje, :montoDescuento, :montoTotalPagar,
             :codigoGrupo, :cantidadGrupo, :responsable, :observaciones, 'ACTIVO')"
        );
        $stmt->bindParam(":idInscripcion", $datos['idInscripcion'], PDO::PARAM_INT);
        $stmt->bindParam(":estudianteID", $datos['EstudianteID'], PDO::PARAM_INT);
        $stmt->bindParam(":programaID", $datos['ProgramaID'], PDO::PARAM_INT);
        $stmt->bindParam(":costoTotal", $costoTotal);
        $stmt->bindParam(":tipoPlan", $tipoPlan, PDO::PARAM_STR);
        if ($numeroCuotas !== null) {
            $stmt->bindParam(":numeroCuotas", $numeroCuotas, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(":numeroCuotas", null, PDO::PARAM_NULL);
        }
        $stmt->bindValue(":montoCuota", $montoCuota, $montoCuota === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindParam(":porcentaje", $porcentaje);
        $stmt->bindParam(":montoDescuento", $montoDescuento);
        $stmt->bindParam(":montoTotalPagar", $montoTotalPagar);
        $stmt->bindParam(":codigoGrupo", $codigoGrupo, PDO::PARAM_STR);
        $stmt->bindParam(":cantidadGrupo", $cantidadGrupo, PDO::PARAM_INT);
        $stmt->bindParam(":responsable", $responsable, PDO::PARAM_STR);
        $stmt->bindParam(":observaciones", $observaciones, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            return ['status' => 'error', 'mensaje' => 'No se pudo registrar el plan de pago del programa'];
        }

        return ['status' => 'exitoso', 'idPagoPrograma' => $pdo->lastInsertId(), 'mensaje' => 'Plan de pago del programa registrado correctamente'];
    }

    /**
     * Registrar un plan de pago abriendo su propia conexión/transacción.
     * Se usa cuando el plan se define DESPUÉS de la matrícula (desde Matriculados),
     * en vez de en el mismo momento de validar el voucher.
     * @param array $datos
     * @return array
     */
    public static function RegistrarPlanModelo($datos)
    {
        try {
            $pdo = Conexion::Conectar();
            $pdo->beginTransaction();

            $resultado = self::InsertarPlanEnTransaccion($pdo, $datos);

            if ($resultado['status'] !== 'exitoso') {
                $pdo->rollBack();
                return $resultado;
            }

            $pdo->commit();
            return $resultado;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en RegistrarPlanModelo: " . $e->getMessage());
            return ['status' => 'error', 'mensaje' => 'Error en el servidor: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener el plan de pago de una inscripción, si existe.
     * @param int $idInscripcion
     * @return array|null
     */
    public static function ObtenerPlanPorInscripcionModelo($idInscripcion)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT * FROM pagoprograma
                 WHERE idInscripcion = :idInscripcion AND Estado != 'ANULADO'
                 ORDER BY IdPagoPrograma DESC LIMIT 1"
            );
            $stmt->bindParam(":idInscripcion", $idInscripcion, PDO::PARAM_INT);
            $stmt->execute();
            $plan = $stmt->fetch(PDO::FETCH_ASSOC);

            return $plan ?: null;
        } catch (PDOException $e) {
            error_log("Error en ObtenerPlanPorInscripcionModelo: " . $e->getMessage());
            return null;
        }
    }
}
