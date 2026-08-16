<?php
require_once 'conexion.modelo.php';

/**
 * Modelo de Orden de Pago (Preregistro)
 * Gestiona el preregistro de estudiantes sin voucher
 */
class OrdenPagoModelos
{
    /**
     * Registrar orden de pago (sin tocar estudianteprograma)
     * @param array $datos - Datos de la orden de pago
     * @return array - ['status' => 'exitoso'/'duplicado'/'error', 'idOrdenPago' => ID, 'numeroOrden' => NUM]
     */
    public static function RegistrarPreregistroModelo($datos)
    {
        try {
            $pdo = Conexion::Conectar();
            $pdo->beginTransaction();

            // Verificar si el estudiante ya está inscrito ACTIVAMENTE en el mismo programa
            // NOTA: Solo verificamos contra inscripciones ACTIVAS en estudianteprograma
            $stmtCheck = $pdo->prepare(
                "SELECT idInscripcion FROM estudianteprograma
                 WHERE EstudianteID = :estudianteID
                 AND ProgramaID = :programaID
                 AND Estado IN ('ACTIVO', 'CONFIRMADO')"
            );
            $stmtCheck->bindParam(":estudianteID", $datos['EstudianteID'], PDO::PARAM_INT);
            $stmtCheck->bindParam(":programaID", $datos['ProgramaID'], PDO::PARAM_INT);
            $stmtCheck->execute();

            if ($stmtCheck->fetch()) {
                $pdo->rollBack();
                return [
                    'status' => 'duplicado',
                    'mensaje' => 'El estudiante ya tiene una inscripción activa en este programa'
                ];
            }

            // Obtener el costo de matrícula del programa
            $stmtPrograma = $pdo->prepare(
                "SELECT CostoMatricula FROM programa WHERE ProgramaID = :programaID"
            );
            $stmtPrograma->bindParam(":programaID", $datos['ProgramaID'], PDO::PARAM_INT);
            $stmtPrograma->execute();
            $programa = $stmtPrograma->fetch(PDO::FETCH_ASSOC);

            if (!$programa) {
                $pdo->rollBack();
                return [
                    'status' => 'error',
                    'mensaje' => 'Programa no encontrado'
                ];
            }

            $costoMatriculaPrograma = floatval($programa['CostoMatricula']);

            // VALIDACIÓN: Si NO es pago completo, validar que el monto corresponda a la matrícula
            if ($datos['pagoCompleto'] != 1) {
                $montoPagado = floatval($datos['montoPagado']);

                // Validar que el monto pagado coincida con la matrícula (con pequeña tolerancia para redondeos)
                if (abs($montoPagado - $costoMatriculaPrograma) > 0.01) {
                    $pdo->rollBack();
                    return [
                        'status' => 'error',
                        'mensaje' => sprintf(
                            'El monto de matrícula debe ser Bs. %.2f (programa seleccionado). Monto ingresado: Bs. %.2f',
                            $costoMatriculaPrograma,
                            $montoPagado
                        )
                    ];
                }
            }

            // Generar número de orden único ANTES de insertar
            // Usar timestamp para garantizar unicidad
            $numeroOrden = 'ORD-' . date('YmdHis') . '-' . rand(1000, 9999);

            // Calcular montos para la tabla ordenpago
            $montoDescuento = floatval($datos['montoDescuento']);
            $porcentajeDescuento = floatval($datos['porcentajeDescuento']);
            $montoFinal = floatval($datos['montoPagado']);
            $costoMatricula = $datos['pagoCompleto'] == 1 ? 0 : floatval($datos['costomatricula']);

            // MontoTotal = Monto que debería pagar sin descuento
            $montoTotal = $montoFinal + $montoDescuento;

            // Obtener responsable (usuario de sesión si existe)
            // La sesión ya está iniciada desde plantilla.php
            $responsable = isset($_SESSION['Nombre']) && isset($_SESSION['Apellido'])
                ? $_SESSION['Nombre'] . ' ' . $_SESSION['Apellido']
                : null;

            // INSERTAR SOLO EN ORDENPAGO - NO tocar estudianteprograma
            $stmtOrden = $pdo->prepare(
                "INSERT INTO ordenpago
                (NumeroOrden, idInscripcion, EstudianteID, ProgramaID,
                 MontoTotal, MontoDescuento, PorcentajeDescuento, MontoFinal,
                 PagoCompleto, CostoMatricula, FechaGeneracion,
                 NombreFactura, NitCiFactura, ResponsableGeneracion, Firma, Estado)
                VALUES
                (:numeroOrden, NULL, :estudianteID, :programaID,
                 :montoTotal, :montoDescuento, :porcentajeDescuento, :montoFinal,
                 :pagoCompleto, :costoMatricula, NOW(),
                 :nombreFactura, :nitCiFactura, :responsable, :firma, 'PENDIENTE')"
            );

            $stmtOrden->bindParam(":numeroOrden", $numeroOrden, PDO::PARAM_STR);
            $stmtOrden->bindParam(":estudianteID", $datos['EstudianteID'], PDO::PARAM_INT);
            $stmtOrden->bindParam(":programaID", $datos['ProgramaID'], PDO::PARAM_INT);
            $stmtOrden->bindParam(":montoTotal", $montoTotal);
            $stmtOrden->bindParam(":montoDescuento", $montoDescuento);
            $stmtOrden->bindParam(":porcentajeDescuento", $porcentajeDescuento);
            $stmtOrden->bindParam(":montoFinal", $montoFinal);
            $stmtOrden->bindParam(":pagoCompleto", $datos['pagoCompleto'], PDO::PARAM_INT);
            $stmtOrden->bindParam(":costoMatricula", $costoMatricula);
            $stmtOrden->bindParam(":nombreFactura", $datos['NombreFactura'], PDO::PARAM_STR);
            $stmtOrden->bindParam(":nitCiFactura", $datos['NitCiFactura'], PDO::PARAM_STR);
            $stmtOrden->bindParam(":responsable", $responsable, PDO::PARAM_STR);
            $stmtOrden->bindParam(":firma", $datos['Firma'], PDO::PARAM_STR);

            if (!$stmtOrden->execute()) {
                $pdo->rollBack();
                error_log("Error al insertar en ordenpago: " . print_r($stmtOrden->errorInfo(), true));
                return ['status' => 'error', 'mensaje' => 'Error al registrar la orden de pago'];
            }

            $idOrdenPago = $pdo->lastInsertId();
            error_log("ORDEN DE PAGO REGISTRADA: ID={$idOrdenPago}, Número={$numeroOrden}");

            $pdo->commit();

            return [
                'status' => 'exitoso',
                'idOrdenPago' => $idOrdenPago,
                'numeroOrden' => $numeroOrden,
                'mensaje' => 'Orden de pago creada exitosamente'
            ];

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en RegistrarPreregistroModelo: " . $e->getMessage());
            return ['status' => 'error', 'mensaje' => 'Error en el servidor: ' . $e->getMessage()];
        }
    }

    /**
     * Listar preregistros (órdenes de pago pendientes)
     * @return array
     */
    public static function ListarPreregistrosModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    ep.idInscripcion,
                    ep.EstudianteID,
                    ep.ProgramaID,
                    ep.FechaInscripcion,
                    ep.costomatricula,
                    ep.montoPagado,
                    ep.pagoCompleto,
                    ep.porcentajeDescuento,
                    ep.montoDescuento,
                    ep.Estado,
                    e.Nombre,
                    e.Apaterno,
                    e.Amaterno,
                    e.Ci,
                    e.Correo,
                    e.Celular,
                    p.NombrePrograma,
                    p.GradoAcademico,
                    p.Codigo as CodigoPrograma,
                    op.NumeroOrden
                FROM estudianteprograma ep
                INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
                INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
                LEFT JOIN ordenpago op ON op.idInscripcion = ep.idInscripcion
                WHERE ep.Estado = 'PENDIENTE'
                ORDER BY ep.FechaInscripcion DESC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarPreregistrosModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener un preregistro puntual (para precargar el modal de edición)
     * @param int $idInscripcion
     * @return array|null
     */
    public static function ObtenerPreregistroModelo($idInscripcion)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    ep.idInscripcion,
                    ep.EstudianteID,
                    ep.ProgramaID,
                    ep.FechaInscripcion,
                    ep.costomatricula,
                    ep.montoPagado,
                    ep.pagoCompleto,
                    ep.porcentajeDescuento,
                    ep.montoDescuento,
                    ep.Estado
                FROM estudianteprograma ep
                WHERE ep.idInscripcion = :idInscripcion AND ep.Estado = 'PENDIENTE'"
            );
            $stmt->bindParam(":idInscripcion", $idInscripcion, PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return $fila ?: null;
        } catch (PDOException $e) {
            error_log("Error en ObtenerPreregistroModelo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualizar el monto/descuento/fecha de un preregistro (solo si sigue PENDIENTE)
     * @param array $datos
     * @return array
     */
    public static function ActualizarPreregistroModelo($datos)
    {
        try {
            $pdo = Conexion::Conectar();
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "UPDATE estudianteprograma
                 SET costomatricula = :costomatricula,
                     montoPagado = :montoPagado,
                     porcentajeDescuento = :porcentajeDescuento,
                     montoDescuento = :montoDescuento,
                     FechaInscripcion = :fechaInscripcion
                 WHERE idInscripcion = :idInscripcion AND Estado = 'PENDIENTE'"
            );
            $stmt->bindParam(":costomatricula", $datos['costomatricula']);
            $stmt->bindParam(":montoPagado", $datos['montoPagado']);
            $stmt->bindParam(":porcentajeDescuento", $datos['porcentajeDescuento']);
            $stmt->bindParam(":montoDescuento", $datos['montoDescuento']);
            $stmt->bindParam(":fechaInscripcion", $datos['FechaInscripcion'], PDO::PARAM_STR);
            $stmt->bindParam(":idInscripcion", $datos['idInscripcion'], PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                $pdo->rollBack();
                return ['status' => 'error', 'mensaje' => 'El preregistro ya no está pendiente o no existe'];
            }

            // Mantener sincronizada la tabla ordenpago (si existe el registro)
            $stmtOrden = $pdo->prepare(
                "UPDATE ordenpago
                 SET MontoDescuento = :montoDescuento,
                     PorcentajeDescuento = :porcentajeDescuento,
                     MontoFinal = :montoPagado
                 WHERE idInscripcion = :idInscripcion AND Estado = 'PENDIENTE'"
            );
            $stmtOrden->bindParam(":montoDescuento", $datos['montoDescuento']);
            $stmtOrden->bindParam(":porcentajeDescuento", $datos['porcentajeDescuento']);
            $stmtOrden->bindParam(":montoPagado", $datos['montoPagado']);
            $stmtOrden->bindParam(":idInscripcion", $datos['idInscripcion'], PDO::PARAM_INT);
            $stmtOrden->execute();

            $pdo->commit();
            return ['status' => 'exitoso', 'mensaje' => 'Preregistro actualizado correctamente'];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en ActualizarPreregistroModelo: " . $e->getMessage());
            return ['status' => 'error', 'mensaje' => 'Error en el servidor: ' . $e->getMessage()];
        }
    }

    /**
     * Anular (cancelar) un preregistro pendiente. No se elimina físicamente:
     * se marca como ANULADO para conservar historial/auditoría.
     * @param int $idInscripcion
     * @return array
     */
    public static function AnularPreregistroModelo($idInscripcion)
    {
        try {
            $pdo = Conexion::Conectar();
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "UPDATE estudianteprograma SET Estado = 'ANULADO'
                 WHERE idInscripcion = :idInscripcion AND Estado = 'PENDIENTE'"
            );
            $stmt->bindParam(":idInscripcion", $idInscripcion, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                $pdo->rollBack();
                return ['status' => 'error', 'mensaje' => 'El preregistro ya no está pendiente o no existe'];
            }

            $pdo->prepare(
                "UPDATE ordenpago SET Estado = 'ANULADO' WHERE idInscripcion = :idInscripcion"
            )->execute([':idInscripcion' => $idInscripcion]);

            $pdo->prepare(
                "UPDATE pagomodulo SET Estado = 'ANULADO' WHERE idinscripcion = :idInscripcion AND Estado = 'PENDIENTE'"
            )->execute([':idInscripcion' => $idInscripcion]);

            $pdo->commit();
            return ['status' => 'exitoso', 'mensaje' => 'Preregistro anulado correctamente'];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en AnularPreregistroModelo: " . $e->getMessage());
            return ['status' => 'error', 'mensaje' => 'Error en el servidor: ' . $e->getMessage()];
        }
    }
}
?>
