<?php
require_once 'conexion.modelo.php';

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
        if ($porcentaje < 0) $porcentaje = 0;
        if ($porcentaje > 100) $porcentaje = 100;
        $montoDescuento = round($costoTotal * $porcentaje / 100, 2);
        $montoTotalPagar = round($costoTotal - $montoDescuento, 2);

        $tipoPlan = in_array($datos['TipoPlan'], ['REGULAR', 'DESCUENTO', 'GRUPAL']) ? $datos['TipoPlan'] : 'REGULAR';
        $codigoGrupo = !empty($datos['CodigoGrupo']) ? $datos['CodigoGrupo'] : null;
        $cantidadGrupo = isset($datos['CantidadInscritosGrupo']) ? (int)$datos['CantidadInscritosGrupo'] : 1;
        $responsable = $datos['ResponsableGeneracion'] ?? null;
        $observaciones = $datos['Observaciones'] ?? null;
        // Número de cuotas planificado (solo tiene sentido para Plan Regular; las cuotas
        // siguen siendo de monto libre, esto es solo para poder mostrar "Cuota X de N").
        $numeroCuotas = ($tipoPlan === 'REGULAR' && !empty($datos['NumeroCuotas'])) ? (int)$datos['NumeroCuotas'] : null;

        $stmt = $pdo->prepare(
            "INSERT INTO pagoprograma
            (idInscripcion, EstudianteID, ProgramaID, CostoTotalPrograma, TipoPlan, NumeroCuotas,
             PorcentajeDescuento, MontoDescuento, MontoTotalPagar,
             CodigoGrupo, CantidadInscritosGrupo, ResponsableGeneracion, Observaciones, Estado)
            VALUES
            (:idInscripcion, :estudianteID, :programaID, :costoTotal, :tipoPlan, :numeroCuotas,
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
