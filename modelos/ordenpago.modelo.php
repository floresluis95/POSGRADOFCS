<?php
require_once 'conexion.modelo.php';

/**
 * Modelo de Orden de Pago (Preregistro)
 * Gestiona el preregistro de estudiantes sin voucher
 */
class OrdenPagoModelos
{
    /**
     * Validar el voucher de la matrícula ya cancelada e inscribir formalmente al estudiante.
     * Este es el único punto donde se escribe en `estudianteprograma`: recién aquí la
     * inscripción pasa a ser real. La orden de pago pasa a Estado = 'CONFIRMADO'.
     * @param int $idOrdenPago
     * @param string $numeroVoucher
     * @param string|null $fechaInscripcion
     * @param string|null $fotoVoucher Contenido binario de la imagen del voucher (opcional)
     * @return array
     */
    public static function ValidarVoucherMatriculaModelo($idOrdenPago, $numeroVoucher, $fechaInscripcion = null, $fotoVoucher = null)
    {
        try {
            $pdo = Conexion::Conectar();
            $pdo->beginTransaction();

            // Traer la orden de pago (debe seguir PENDIENTE)
            $stmt = $pdo->prepare(
                "SELECT * FROM ordenpago WHERE IdOrdenPago = :idOrdenPago AND Estado = 'PENDIENTE' FOR UPDATE"
            );
            $stmt->bindParam(":idOrdenPago", $idOrdenPago, PDO::PARAM_INT);
            $stmt->execute();
            $orden = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$orden) {
                $pdo->rollBack();
                return ['status' => 'error', 'mensaje' => 'La orden de pago no existe o ya no está pendiente'];
            }

            // Un estudiante no puede quedar inscrito dos veces (activo) en el mismo programa
            $stmtDup = $pdo->prepare(
                "SELECT idInscripcion FROM estudianteprograma
                 WHERE EstudianteID = :estudianteID AND ProgramaID = :programaID
                 AND Estado IN ('ACTIVO', 'CONFIRMADO')"
            );
            $stmtDup->bindParam(":estudianteID", $orden['EstudianteID'], PDO::PARAM_INT);
            $stmtDup->bindParam(":programaID", $orden['ProgramaID'], PDO::PARAM_INT);
            $stmtDup->execute();

            if ($stmtDup->fetch()) {
                $pdo->rollBack();
                return ['status' => 'error', 'mensaje' => 'El estudiante ya está inscrito en este programa'];
            }

            // Inscripción formal: única escritura en estudianteprograma
            $fechaInscripcionFinal = !empty($fechaInscripcion) ? $fechaInscripcion : date('Y-m-d');

            $stmtInsert = $pdo->prepare(
                "INSERT INTO estudianteprograma
                (EstudianteID, ProgramaID, costomatricula, montoPagado, pagoCompleto,
                 porcentajeDescuento, montoDescuento, nvauchermatricula, FechaInscripcion, foto, Estado)
                VALUES
                (:estudianteID, :programaID, :costomatricula, :montoPagado, :pagoCompleto,
                 :porcentajeDescuento, :montoDescuento, :nvauchermatricula, :fechaInscripcion, :foto, 'ACTIVO')"
            );
            $stmtInsert->bindParam(":estudianteID", $orden['EstudianteID'], PDO::PARAM_INT);
            $stmtInsert->bindParam(":programaID", $orden['ProgramaID'], PDO::PARAM_INT);
            $stmtInsert->bindParam(":costomatricula", $orden['CostoMatricula']);
            $stmtInsert->bindParam(":montoPagado", $orden['MontoFinal']);
            $stmtInsert->bindParam(":pagoCompleto", $orden['PagoCompleto'], PDO::PARAM_INT);
            $stmtInsert->bindParam(":porcentajeDescuento", $orden['PorcentajeDescuento']);
            $stmtInsert->bindParam(":montoDescuento", $orden['MontoDescuento']);
            $stmtInsert->bindParam(":nvauchermatricula", $numeroVoucher, PDO::PARAM_STR);
            $stmtInsert->bindParam(":fechaInscripcion", $fechaInscripcionFinal, PDO::PARAM_STR);
            $stmtInsert->bindParam(":foto", $fotoVoucher, PDO::PARAM_LOB);

            if (!$stmtInsert->execute()) {
                $pdo->rollBack();
                return ['status' => 'error', 'mensaje' => 'No se pudo inscribir al estudiante'];
            }

            $idInscripcion = $pdo->lastInsertId();

            // Confirmar la orden de pago y enlazarla con la inscripción recién creada
            $stmtUpdate = $pdo->prepare(
                "UPDATE ordenpago
                 SET Estado = 'CONFIRMADO', FechaConfirmacion = NOW(), idInscripcion = :idInscripcion
                 WHERE IdOrdenPago = :idOrdenPago"
            );
            $stmtUpdate->bindParam(":idInscripcion", $idInscripcion, PDO::PARAM_INT);
            $stmtUpdate->bindParam(":idOrdenPago", $idOrdenPago, PDO::PARAM_INT);
            $stmtUpdate->execute();

            $pdo->commit();

            return [
                'status' => 'exitoso',
                'mensaje' => 'Voucher validado. El estudiante quedó inscrito formalmente.',
                'idInscripcion' => $idInscripcion,
                'estudianteID' => $orden['EstudianteID'],
                'programaID' => $orden['ProgramaID']
            ];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en ValidarVoucherMatriculaModelo: " . $e->getMessage());
            return ['status' => 'error', 'mensaje' => 'Error en el servidor: ' . $e->getMessage()];
        }
    }

    /**
     * Actualizar los datos de facturación (nombre, NIT/CI, responsable, firma) de una
     * orden de pago ya generada, antes de imprimir la boleta.
     * @param array $datos
     * @return bool
     */
    public static function ActualizarFacturacionOrdenModelo($datos)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "UPDATE ordenpago
                 SET NombreFactura = :nombreFactura,
                     NitCiFactura = :nitCiFactura,
                     ResponsableGeneracion = :responsable,
                     Firma = :firma
                 WHERE IdOrdenPago = :idOrdenPago"
            );
            $stmt->bindParam(":nombreFactura", $datos['nombreFactura'], PDO::PARAM_STR);
            $stmt->bindParam(":nitCiFactura", $datos['nitCiFactura'], PDO::PARAM_STR);
            $stmt->bindParam(":responsable", $datos['responsable'], PDO::PARAM_STR);
            $stmt->bindParam(":firma", $datos['firma'], PDO::PARAM_STR);
            $stmt->bindParam(":idOrdenPago", $datos['idOrdenPago'], PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ActualizarFacturacionOrdenModelo: " . $e->getMessage());
            return false;
        }
    }

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

            // Verificar si el estudiante ya está inscrito ACTIVAMENTE en el mismo programa.
            // Una orden de "Solo Matrícula" no tiene sentido si ya está inscrito (esa matrícula
            // ya se pagó y validó). Una orden de "Programa Completo" sí es válida en ese caso:
            // es justamente cómo se cobra el resto del programa tras la inscripción formal.
            if ($datos['pagoCompleto'] != 1) {
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
            }

            // Un estudiante solo puede tener UN pre-registro pendiente del mismo tipo por programa (1:1).
            // Verificamos contra las órdenes de pago (ordenpago) que sigan PENDIENTES.
            $stmtCheckPendiente = $pdo->prepare(
                "SELECT IdOrdenPago FROM ordenpago
                 WHERE EstudianteID = :estudianteID
                 AND ProgramaID = :programaID
                 AND PagoCompleto = :pagoCompleto
                 AND Estado = 'PENDIENTE'"
            );
            $stmtCheckPendiente->bindParam(":estudianteID", $datos['EstudianteID'], PDO::PARAM_INT);
            $stmtCheckPendiente->bindParam(":programaID", $datos['ProgramaID'], PDO::PARAM_INT);
            $stmtCheckPendiente->bindParam(":pagoCompleto", $datos['pagoCompleto'], PDO::PARAM_INT);
            $stmtCheckPendiente->execute();

            if ($stmtCheckPendiente->fetch()) {
                $pdo->rollBack();
                return [
                    'status' => 'duplicado',
                    'mensaje' => 'El estudiante ya tiene un pre-registro pendiente de este tipo para este programa'
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

            // Porcentaje de descuento según el plan de pago elegido (Contado / Grupal / Regular=0)
            $porcentajeDescuento = isset($datos['porcentajeDescuento']) ? floatval($datos['porcentajeDescuento']) : 0;
            if ($porcentajeDescuento < 0) $porcentajeDescuento = 0;
            if ($porcentajeDescuento > 100) $porcentajeDescuento = 100;

            // VALIDACIÓN: Si NO es pago completo, validar que el monto corresponda a la matrícula con descuento
            if ($datos['pagoCompleto'] != 1) {
                $montoPagado = floatval($datos['montoPagado']);
                $montoEsperado = round($costoMatriculaPrograma * (1 - $porcentajeDescuento / 100), 2);

                // Validar que el monto pagado coincida con la matrícula (con pequeña tolerancia para redondeos)
                if (abs($montoPagado - $montoEsperado) > 0.01) {
                    $pdo->rollBack();
                    return [
                        'status' => 'error',
                        'mensaje' => sprintf(
                            'El monto de matrícula debe ser Bs. %.2f (matrícula Bs. %.2f con %.2f%% de descuento). Monto ingresado: Bs. %.2f',
                            $montoEsperado,
                            $costoMatriculaPrograma,
                            $porcentajeDescuento,
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
            $montoFinal = floatval($datos['montoPagado']);
            $costoMatricula = $datos['pagoCompleto'] == 1 ? 0 : floatval($datos['costomatricula']);

            // MontoTotal = Monto que debería pagar sin descuento
            $montoTotal = $montoFinal + $montoDescuento;

            // Obtener responsable (usuario de sesión si existe)
            // La sesión ya está iniciada desde plantilla.php
            $responsable = isset($_SESSION['Nombre']) && isset($_SESSION['Apellido'])
                ? $_SESSION['Nombre'] . ' ' . $_SESSION['Apellido']
                : null;

            $observaciones = isset($datos['Observaciones']) ? $datos['Observaciones'] : null;

            // INSERTAR SOLO EN ORDENPAGO - NO tocar estudianteprograma
            $stmtOrden = $pdo->prepare(
                "INSERT INTO ordenpago
                (NumeroOrden, idInscripcion, EstudianteID, ProgramaID,
                 MontoTotal, MontoDescuento, PorcentajeDescuento, MontoFinal,
                 PagoCompleto, CostoMatricula, FechaGeneracion, Observaciones,
                 NombreFactura, NitCiFactura, ResponsableGeneracion, Firma, Estado)
                VALUES
                (:numeroOrden, NULL, :estudianteID, :programaID,
                 :montoTotal, :montoDescuento, :porcentajeDescuento, :montoFinal,
                 :pagoCompleto, :costoMatricula, NOW(), :observaciones,
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
            $stmtOrden->bindParam(":observaciones", $observaciones, PDO::PARAM_STR);
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
                    op.IdOrdenPago,
                    op.NumeroOrden,
                    op.EstudianteID,
                    op.ProgramaID,
                    op.CostoMatricula as costomatricula,
                    op.MontoFinal as montoPagado,
                    op.PagoCompleto as pagoCompleto,
                    op.PorcentajeDescuento as porcentajeDescuento,
                    op.MontoDescuento as montoDescuento,
                    op.FechaGeneracion,
                    op.Estado,
                    e.Nombre,
                    e.Apaterno,
                    e.Amaterno,
                    e.Ci,
                    e.Correo,
                    e.Celular,
                    p.NombrePrograma,
                    p.GradoAcademico,
                    p.Codigo as CodigoPrograma
                FROM ordenpago op
                INNER JOIN estudiante e ON op.EstudianteID = e.EstudianteID
                INNER JOIN programa p ON op.ProgramaID = p.ProgramaID
                WHERE op.Estado = 'PENDIENTE'
                ORDER BY op.FechaGeneracion DESC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarPreregistrosModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar preregistros pendientes (para validar voucher en Inscripción) por CI,
     * nombre o apellidos del estudiante.
     * @param string $termino
     * @return array
     */
    public static function BuscarPreregistrosPendientesModelo($termino)
    {
        try {
            $like = '%' . $termino . '%';
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    op.IdOrdenPago,
                    op.NumeroOrden,
                    op.EstudianteID,
                    op.ProgramaID,
                    op.MontoFinal as montoPagado,
                    op.PagoCompleto as pagoCompleto,
                    op.Observaciones,
                    op.FechaGeneracion,
                    e.Nombre,
                    e.Apaterno,
                    e.Amaterno,
                    e.Ci,
                    p.NombrePrograma,
                    p.Codigo as CodigoPrograma
                FROM ordenpago op
                INNER JOIN estudiante e ON op.EstudianteID = e.EstudianteID
                INNER JOIN programa p ON op.ProgramaID = p.ProgramaID
                WHERE op.Estado = 'PENDIENTE'
                AND (
                    e.Ci LIKE :like1
                    OR e.Nombre LIKE :like2
                    OR e.Apaterno LIKE :like3
                    OR e.Amaterno LIKE :like4
                    OR CONCAT(e.Apaterno, ' ', e.Amaterno, ' ', e.Nombre) LIKE :like5
                )
                ORDER BY op.FechaGeneracion DESC
                LIMIT 30"
            );
            $stmt->bindParam(":like1", $like, PDO::PARAM_STR);
            $stmt->bindParam(":like2", $like, PDO::PARAM_STR);
            $stmt->bindParam(":like3", $like, PDO::PARAM_STR);
            $stmt->bindParam(":like4", $like, PDO::PARAM_STR);
            $stmt->bindParam(":like5", $like, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en BuscarPreregistrosPendientesModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener un preregistro puntual (para precargar el modal de edición)
     * @param int $idOrdenPago
     * @return array|null
     */
    public static function ObtenerPreregistroModelo($idOrdenPago)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    op.IdOrdenPago,
                    op.NumeroOrden,
                    op.EstudianteID,
                    op.ProgramaID,
                    op.FechaGeneracion,
                    op.CostoMatricula,
                    op.MontoFinal,
                    op.PagoCompleto,
                    op.PorcentajeDescuento,
                    op.MontoDescuento,
                    op.NombreFactura,
                    op.NitCiFactura,
                    op.ResponsableGeneracion,
                    op.Firma,
                    op.Observaciones,
                    op.Estado,
                    e.Nombre,
                    e.Apaterno,
                    e.Amaterno,
                    e.Ci,
                    e.Complemento,
                    e.Exp,
                    e.Correo,
                    e.Celular,
                    p.NombrePrograma,
                    p.Codigo AS CodigoPrograma,
                    p.GradoAcademico,
                    p.Sede,
                    p.Version,
                    p.NumeroTramite
                FROM ordenpago op
                INNER JOIN estudiante e ON op.EstudianteID = e.EstudianteID
                INNER JOIN programa p ON op.ProgramaID = p.ProgramaID
                WHERE op.IdOrdenPago = :idOrdenPago AND op.Estado = 'PENDIENTE'"
            );
            $stmt->bindParam(":idOrdenPago", $idOrdenPago, PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return $fila ?: null;
        } catch (PDOException $e) {
            error_log("Error en ObtenerPreregistroModelo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualizar el monto/descuento/fecha de un preregistro (solo si sigue PENDIENTE).
     * Solo toca la tabla ordenpago; estudianteprograma no se ve afectada.
     * @param array $datos
     * @return array
     */
    public static function ActualizarPreregistroModelo($datos)
    {
        try {
            $pdo = Conexion::Conectar();
            $pdo->beginTransaction();

            $montoFinal = floatval($datos['montoPagado']);
            $montoDescuento = floatval($datos['montoDescuento']);
            $porcentajeDescuento = floatval($datos['porcentajeDescuento']);
            $montoTotal = $montoFinal + $montoDescuento;
            $costomatricula = (isset($datos['pagoCompleto']) && (int)$datos['pagoCompleto'] === 1) ? 0 : $montoFinal;

            $stmt = $pdo->prepare(
                "UPDATE ordenpago
                 SET CostoMatricula = :costomatricula,
                     MontoFinal = :montoFinal,
                     MontoTotal = :montoTotal,
                     PorcentajeDescuento = :porcentajeDescuento,
                     MontoDescuento = :montoDescuento,
                     FechaGeneracion = :fechaInscripcion
                 WHERE IdOrdenPago = :idOrdenPago AND Estado = 'PENDIENTE'"
            );
            $stmt->bindParam(":costomatricula", $costomatricula);
            $stmt->bindParam(":montoFinal", $montoFinal);
            $stmt->bindParam(":montoTotal", $montoTotal);
            $stmt->bindParam(":porcentajeDescuento", $porcentajeDescuento);
            $stmt->bindParam(":montoDescuento", $montoDescuento);
            $stmt->bindParam(":fechaInscripcion", $datos['FechaInscripcion'], PDO::PARAM_STR);
            $stmt->bindParam(":idOrdenPago", $datos['idOrdenPago'], PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                $pdo->rollBack();
                return ['status' => 'error', 'mensaje' => 'El preregistro ya no está pendiente o no existe'];
            }

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
     * Solo toca la tabla ordenpago; estudianteprograma no se ve afectada
     * (nunca llegó a tener un registro para este preregistro).
     * @param int $idOrdenPago
     * @return array
     */
    public static function AnularPreregistroModelo($idOrdenPago)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "UPDATE ordenpago SET Estado = 'ANULADO'
                 WHERE IdOrdenPago = :idOrdenPago AND Estado = 'PENDIENTE'"
            );
            $stmt->bindParam(":idOrdenPago", $idOrdenPago, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return ['status' => 'error', 'mensaje' => 'El preregistro ya no está pendiente o no existe'];
            }

            return ['status' => 'exitoso', 'mensaje' => 'Preregistro anulado correctamente'];
        } catch (PDOException $e) {
            error_log("Error en AnularPreregistroModelo: " . $e->getMessage());
            return ['status' => 'error', 'mensaje' => 'Error en el servidor: ' . $e->getMessage()];
        }
    }
}
?>
