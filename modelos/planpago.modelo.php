<?php
require_once 'conexion.modelo.php';

/**
 * Modelo de Plan de Pago del Programa (posgrado)
 * Independiente del pago de matrícula (ordenpago / estudianteprograma).
 * Controla el plan elegido (Regular / Descuento / Grupal) y sus cuotas.
 */
class PagoProgramaModelos
{
    /**
     * Inserta el plan de pago (pagoprograma + cuotaprograma) usando una conexión/transacción
     * ya abierta por el llamador (no hace beginTransaction/commit/rollBack).
     * @param PDO $pdo
     * @param array $datos idInscripcion, EstudianteID, ProgramaID, CostoTotalPrograma, TipoPlan,
     *                      PorcentajeDescuento, CodigoGrupo, CantidadInscritosGrupo,
     *                      ResponsableGeneracion, Observaciones, Cuotas: [{monto, fecha}, ...]
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

        if (empty($datos['Cuotas'])) {
            return ['status' => 'error', 'mensaje' => 'El plan de pago debe tener al menos una cuota'];
        }

        $costoTotal = round(floatval($datos['CostoTotalPrograma']), 2);
        $porcentaje = isset($datos['PorcentajeDescuento']) ? floatval($datos['PorcentajeDescuento']) : 0;
        if ($porcentaje < 0) $porcentaje = 0;
        if ($porcentaje > 100) $porcentaje = 100;
        $montoDescuento = round($costoTotal * $porcentaje / 100, 2);
        $montoTotalPagar = round($costoTotal - $montoDescuento, 2);

        // La suma de las cuotas debe coincidir con el monto a pagar (tolerancia por redondeo)
        $sumaCuotas = 0;
        foreach ($datos['Cuotas'] as $cuota) {
            $sumaCuotas += round(floatval($cuota['monto']), 2);
        }
        if (abs($sumaCuotas - $montoTotalPagar) > 0.5) {
            return [
                'status' => 'error',
                'mensaje' => sprintf(
                    'La suma de las cuotas (Bs. %.2f) no coincide con el monto a pagar del plan (Bs. %.2f)',
                    $sumaCuotas,
                    $montoTotalPagar
                )
            ];
        }

        $tipoPlan = in_array($datos['TipoPlan'], ['REGULAR', 'DESCUENTO', 'GRUPAL']) ? $datos['TipoPlan'] : 'REGULAR';
        $numeroCuotas = count($datos['Cuotas']);
        $codigoGrupo = !empty($datos['CodigoGrupo']) ? $datos['CodigoGrupo'] : null;
        $cantidadGrupo = isset($datos['CantidadInscritosGrupo']) ? (int)$datos['CantidadInscritosGrupo'] : 1;
        $responsable = $datos['ResponsableGeneracion'] ?? null;
        $observaciones = $datos['Observaciones'] ?? null;

        $stmt = $pdo->prepare(
            "INSERT INTO pagoprograma
            (idInscripcion, EstudianteID, ProgramaID, CostoTotalPrograma, TipoPlan,
             PorcentajeDescuento, MontoDescuento, MontoTotalPagar, NumeroCuotas,
             CodigoGrupo, CantidadInscritosGrupo, ResponsableGeneracion, Observaciones, Estado)
            VALUES
            (:idInscripcion, :estudianteID, :programaID, :costoTotal, :tipoPlan,
             :porcentaje, :montoDescuento, :montoTotalPagar, :numeroCuotas,
             :codigoGrupo, :cantidadGrupo, :responsable, :observaciones, 'ACTIVO')"
        );
        $stmt->bindParam(":idInscripcion", $datos['idInscripcion'], PDO::PARAM_INT);
        $stmt->bindParam(":estudianteID", $datos['EstudianteID'], PDO::PARAM_INT);
        $stmt->bindParam(":programaID", $datos['ProgramaID'], PDO::PARAM_INT);
        $stmt->bindParam(":costoTotal", $costoTotal);
        $stmt->bindParam(":tipoPlan", $tipoPlan, PDO::PARAM_STR);
        $stmt->bindParam(":porcentaje", $porcentaje);
        $stmt->bindParam(":montoDescuento", $montoDescuento);
        $stmt->bindParam(":montoTotalPagar", $montoTotalPagar);
        $stmt->bindParam(":numeroCuotas", $numeroCuotas, PDO::PARAM_INT);
        $stmt->bindParam(":codigoGrupo", $codigoGrupo, PDO::PARAM_STR);
        $stmt->bindParam(":cantidadGrupo", $cantidadGrupo, PDO::PARAM_INT);
        $stmt->bindParam(":responsable", $responsable, PDO::PARAM_STR);
        $stmt->bindParam(":observaciones", $observaciones, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            return ['status' => 'error', 'mensaje' => 'No se pudo registrar el plan de pago del programa'];
        }

        $idPagoPrograma = $pdo->lastInsertId();

        $stmtCuota = $pdo->prepare(
            "INSERT INTO cuotaprograma (IdPagoPrograma, NumeroCuota, MontoCuota, FechaVencimiento, Estado)
             VALUES (:idPagoPrograma, :numero, :monto, :fecha, 'PENDIENTE')"
        );

        foreach ($datos['Cuotas'] as $i => $cuota) {
            $numero = $i + 1;
            $monto = round(floatval($cuota['monto']), 2);
            // Sin fecha fija: se acepta cancelar la cuota antes de que inicie su módulo correspondiente
            $fecha = !empty($cuota['fecha']) ? $cuota['fecha'] : null;

            $stmtCuota->bindValue(":idPagoPrograma", $idPagoPrograma, PDO::PARAM_INT);
            $stmtCuota->bindValue(":numero", $numero, PDO::PARAM_INT);
            $stmtCuota->bindValue(":monto", $monto);
            $stmtCuota->bindValue(":fecha", $fecha, $fecha === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            if (!$stmtCuota->execute()) {
                return ['status' => 'error', 'mensaje' => 'No se pudo registrar una de las cuotas del plan'];
            }
        }

        return ['status' => 'exitoso', 'idPagoPrograma' => $idPagoPrograma, 'mensaje' => 'Plan de pago del programa registrado correctamente'];
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
     * Obtener el plan de pago (con sus cuotas) de una inscripción, si existe.
     * @param int $idInscripcion
     * @return array|null
     */
    public static function ObtenerPlanPorInscripcionModelo($idInscripcion)
    {
        try {
            $pdo = Conexion::Conectar();

            $stmt = $pdo->prepare(
                "SELECT * FROM pagoprograma
                 WHERE idInscripcion = :idInscripcion AND Estado != 'ANULADO'
                 ORDER BY IdPagoPrograma DESC LIMIT 1"
            );
            $stmt->bindParam(":idInscripcion", $idInscripcion, PDO::PARAM_INT);
            $stmt->execute();
            $plan = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$plan) {
                return null;
            }

            $stmtCuotas = $pdo->prepare(
                "SELECT * FROM cuotaprograma
                 WHERE IdPagoPrograma = :id AND Estado != 'ANULADO'
                 ORDER BY NumeroCuota ASC"
            );
            $stmtCuotas->bindParam(":id", $plan['IdPagoPrograma'], PDO::PARAM_INT);
            $stmtCuotas->execute();
            $plan['Cuotas'] = $stmtCuotas->fetchAll(PDO::FETCH_ASSOC);

            return $plan;
        } catch (PDOException $e) {
            error_log("Error en ObtenerPlanPorInscripcionModelo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener una cuota puntual con los datos del estudiante/programa
     * (para generar su Orden de Pago en PDF).
     * @param int $idCuota
     * @return array|null
     */
    public static function ObtenerCuotaModelo($idCuota)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    cp.IdCuota, cp.IdPagoPrograma, cp.NumeroCuota, cp.MontoCuota,
                    cp.FechaVencimiento, cp.FechaPago, cp.NumeroVoucher, cp.Estado,
                    pp.TipoPlan, pp.NumeroCuotas, pp.CodigoGrupo, pp.CantidadInscritosGrupo,
                    pp.PorcentajeDescuento, pp.MontoTotalPagar,
                    e.Nombre, e.Apaterno, e.Amaterno, e.Ci, e.Complemento, e.Exp, e.Correo, e.Celular,
                    p.NombrePrograma, p.GradoAcademico, p.Codigo AS CodigoPrograma, p.Version, p.NumeroTramite
                 FROM cuotaprograma cp
                 INNER JOIN pagoprograma pp ON cp.IdPagoPrograma = pp.IdPagoPrograma
                 INNER JOIN estudiante e ON pp.EstudianteID = e.EstudianteID
                 INNER JOIN programa p ON pp.ProgramaID = p.ProgramaID
                 WHERE cp.IdCuota = :idCuota"
            );
            $stmt->bindParam(":idCuota", $idCuota, PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return $fila ?: null;
        } catch (PDOException $e) {
            error_log("Error en ObtenerCuotaModelo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Listar estudiantes matriculados que todavía no tienen un plan de pago del programa
     * (para elegir a los integrantes de un Plan Grupal).
     * @param int|null $programaID
     * @return array
     */
    public static function ListarMatriculadosSinPlanModelo($programaID = null)
    {
        try {
            $sql = "SELECT idInscripcion, EstudianteID, NombreCompleto, CiCompleto, ProgramaID,
                           NombrePrograma, CodigoPrograma, GradoAcademico, CostoPrograma
                    FROM vista_estudiantes_matriculados
                    WHERE TipoPlan IS NULL";
            if ($programaID) {
                $sql .= " AND ProgramaID = :programaID";
            }
            $sql .= " ORDER BY NombreCompleto ASC";

            $stmt = Conexion::Conectar()->prepare($sql);
            if ($programaID) {
                $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarMatriculadosSinPlanModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Registrar un Plan Grupal para varios estudiantes matriculados a la vez: un plan
     * por estudiante, todos enlazados con el mismo CodigoGrupo, en una sola transacción
     * (si uno falla, no se crea ninguno).
     * @param array $idsInscripcion
     * @param float $porcentajeDescuento
     * @param string $fechaVencimiento Fecha de la cuota única (cancelación total del programa)
     * @param string|null $responsable
     * @return array
     */
    public static function RegistrarPlanGrupalModelo($idsInscripcion, $porcentajeDescuento, $fechaVencimiento, $responsable = null)
    {
        $idsInscripcion = array_values(array_unique(array_map('intval', $idsInscripcion)));

        if (count($idsInscripcion) < 2) {
            return ['status' => 'error', 'mensaje' => 'Seleccione al menos 2 estudiantes para el plan grupal'];
        }

        try {
            $pdo = Conexion::Conectar();
            $pdo->beginTransaction();

            $codigoGrupo = 'GRUPO-' . date('YmdHis');
            $cantidad = count($idsInscripcion);

            $stmtInfo = $pdo->prepare(
                "SELECT ep.idInscripcion, ep.EstudianteID, ep.ProgramaID, p.Costo AS CostoPrograma
                 FROM estudianteprograma ep
                 INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
                 WHERE ep.idInscripcion = :id AND ep.Estado = 'ACTIVO'"
            );

            foreach ($idsInscripcion as $idInscripcion) {
                $stmtInfo->bindValue(":id", $idInscripcion, PDO::PARAM_INT);
                $stmtInfo->execute();
                $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

                if (!$info) {
                    $pdo->rollBack();
                    return ['status' => 'error', 'mensaje' => "La inscripción #$idInscripcion no existe o no está activa"];
                }

                $costoPrograma = floatval($info['CostoPrograma']);
                $montoDescuento = round($costoPrograma * $porcentajeDescuento / 100, 2);
                $montoTotalPagar = round($costoPrograma - $montoDescuento, 2);

                $datos = [
                    'idInscripcion' => $info['idInscripcion'],
                    'EstudianteID' => $info['EstudianteID'],
                    'ProgramaID' => $info['ProgramaID'],
                    'CostoTotalPrograma' => $costoPrograma,
                    'TipoPlan' => 'GRUPAL',
                    'PorcentajeDescuento' => $porcentajeDescuento,
                    'CodigoGrupo' => $codigoGrupo,
                    'CantidadInscritosGrupo' => $cantidad,
                    'ResponsableGeneracion' => $responsable,
                    'Cuotas' => [
                        ['monto' => $montoTotalPagar, 'fecha' => $fechaVencimiento]
                    ]
                ];

                $resultado = self::InsertarPlanEnTransaccion($pdo, $datos);
                if ($resultado['status'] !== 'exitoso') {
                    $pdo->rollBack();
                    return ['status' => 'error', 'mensaje' => "Estudiante #{$info['EstudianteID']}: " . $resultado['mensaje']];
                }
            }

            $pdo->commit();
            return ['status' => 'exitoso', 'codigoGrupo' => $codigoGrupo, 'mensaje' => "Plan grupal creado para $cantidad estudiantes"];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en RegistrarPlanGrupalModelo: " . $e->getMessage());
            return ['status' => 'error', 'mensaje' => 'Error en el servidor: ' . $e->getMessage()];
        }
    }

    /**
     * Registrar el pago (voucher) de una cuota pendiente del plan de pago del programa.
     * @param int $idCuota
     * @param string $numeroVoucher
     * @param string|null $fechaPago
     * @param string|null $fotoVoucher Contenido binario (opcional)
     * @return array
     */
    public static function RegistrarPagoCuotaModelo($idCuota, $numeroVoucher, $fechaPago = null, $fotoVoucher = null)
    {
        try {
            $fechaPagoFinal = !empty($fechaPago) ? $fechaPago : date('Y-m-d');

            $stmt = Conexion::Conectar()->prepare(
                "UPDATE cuotaprograma
                 SET Estado = 'PAGADO', NumeroVoucher = :numeroVoucher, FechaPago = :fechaPago, FotoVoucher = :foto
                 WHERE IdCuota = :idCuota AND Estado = 'PENDIENTE'"
            );
            $stmt->bindParam(":numeroVoucher", $numeroVoucher, PDO::PARAM_STR);
            $stmt->bindParam(":fechaPago", $fechaPagoFinal, PDO::PARAM_STR);
            $stmt->bindParam(":foto", $fotoVoucher, PDO::PARAM_LOB);
            $stmt->bindParam(":idCuota", $idCuota, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return ['status' => 'error', 'mensaje' => 'La cuota ya no está pendiente o no existe'];
            }

            return ['status' => 'exitoso', 'mensaje' => 'Pago de cuota registrado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en RegistrarPagoCuotaModelo: " . $e->getMessage());
            return ['status' => 'error', 'mensaje' => 'Error en el servidor: ' . $e->getMessage()];
        }
    }
}
