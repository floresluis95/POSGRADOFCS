<?php
require_once 'conexion.modelo.php';
require_once 'planpago.modelo.php';

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
     *
     * Opcionalmente, en la misma transacción, registra el plan de pago del PROGRAMA
     * (Regular/Descuento/Grupal) definido antes de validar el voucher.
     *
     * @param int $idOrdenPago
     * @param string $numeroVoucher
     * @param string|null $fechaInscripcion
     * @param string|null $fotoVoucher Contenido binario de la imagen del voucher (opcional)
     * @param array|null $planPago Datos del plan de pago del programa (opcional, ver PagoProgramaModelos)
     * @param bool $forzarSinMatricula Si es true, la matrícula queda en Bs. 0 en la inscripción
     *             sin importar el monto de la orden (el estudiante pagará el programa completo después)
     * @return array
     */
    public static function ValidarVoucherMatriculaModelo($idOrdenPago, $numeroVoucher, $fechaInscripcion = null, $fotoVoucher = null, $planPago = null, $forzarSinMatricula = false)
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

            // Una orden de clínica nunca se valida como matrícula: su pago va aparte (pagoclinica)
            if ($orden['Observaciones'] === 'CLINICA') {
                $pdo->rollBack();
                return ['status' => 'error', 'mensaje' => 'Esta es una orden de clínica; su pago se registra en la pestaña Clínicas'];
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

            // "Matricular sin pago de matrícula": la inscripción queda con matrícula en Bs. 0
            // sin importar el monto de la orden original; el programa completo se cobra después.
            $costomatriculaFinal = $forzarSinMatricula ? 0 : $orden['CostoMatricula'];
            $montoPagadoFinal = $forzarSinMatricula ? 0 : $orden['MontoFinal'];

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
            $stmtInsert->bindParam(":costomatricula", $costomatriculaFinal);
            $stmtInsert->bindParam(":montoPagado", $montoPagadoFinal);
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

            // El plan de pago del programa (Regular/Contado) se define en Pre-Registro y
            // queda DEFINITIVO en este mismo momento, automáticamente, sin volver a
            // pedirlo aquí. Si en Pre-Registro se eligió "Grupal", no se crea ningún plan
            // todavía: el estudiante queda disponible para armarse en un grupo después,
            // desde Matriculados. Un llamador puede seguir pasando $planPago explícito
            // (por compatibilidad); si no lo pasa, se deriva de la propia orden.
            if ($planPago === null && !empty($orden['PlanPropuestoTipo']) && $orden['PlanPropuestoTipo'] !== 'GRUPAL') {
                $stmtCostoPrograma = $pdo->prepare("SELECT Costo FROM programa WHERE ProgramaID = :programaID");
                $stmtCostoPrograma->bindParam(":programaID", $orden['ProgramaID'], PDO::PARAM_INT);
                $stmtCostoPrograma->execute();
                $filaPrograma = $stmtCostoPrograma->fetch(PDO::FETCH_ASSOC);

                // El enum de pagoprograma.TipoPlan usa 'DESCUENTO' para lo que en el resto
                // del sistema se llama "Plan al Contado" (PlanPropuestoTipo usa 'CONTADO').
                $tipoPlanFinal = $orden['PlanPropuestoTipo'] === 'CONTADO' ? 'DESCUENTO' : $orden['PlanPropuestoTipo'];

                $planPago = [
                    'idInscripcion' => $idInscripcion,
                    'EstudianteID' => $orden['EstudianteID'],
                    'ProgramaID' => $orden['ProgramaID'],
                    'CostoTotalPrograma' => $filaPrograma ? floatval($filaPrograma['Costo']) : 0,
                    'TipoPlan' => $tipoPlanFinal,
                    'NumeroCuotas' => $orden['PlanPropuestoNumeroCuotas'] ?? null,
                    'PorcentajeDescuento' => $orden['PlanPropuestoPorcentaje'] ?? 0
                ];
            }

            if (!empty($planPago) && !empty($planPago['TipoPlan'])) {
                $planPago['idInscripcion'] = $idInscripcion;
                $planPago['EstudianteID'] = $orden['EstudianteID'];
                $planPago['ProgramaID'] = $orden['ProgramaID'];

                $resultadoPlan = PagoProgramaModelos::InsertarPlanEnTransaccion($pdo, $planPago);
                if ($resultadoPlan['status'] !== 'exitoso') {
                    $pdo->rollBack();
                    return $resultadoPlan;
                }
            }

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
     * Actualizar los datos de facturación (nombre, NIT/CI, firma) de una orden de pago
     * ya generada, antes de imprimir la boleta. El "responsable" (usuario que generó la
     * orden en el sistema) no se toca aquí: se captura una sola vez, de la sesión, al
     * crear la orden, y queda fijo para fines de auditoría.
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
                     Firma = :firma
                 WHERE IdOrdenPago = :idOrdenPago"
            );
            $stmt->bindParam(":nombreFactura", $datos['nombreFactura'], PDO::PARAM_STR);
            $stmt->bindParam(":nitCiFactura", $datos['nitCiFactura'], PDO::PARAM_STR);
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
                 AND Estado = 'PENDIENTE'
                 AND COALESCE(Observaciones, '') <> 'CLINICA'"
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

            // Validar que la cuenta elegida (a la que el estudiante debe depositar) exista,
            // esté activa y pertenezca efectivamente a este programa (tipo MATRICULA).
            $programaCuentaID = !empty($datos['ProgramaCuentaID']) ? (int)$datos['ProgramaCuentaID'] : null;
            if ($programaCuentaID) {
                $stmtCuenta = $pdo->prepare(
                    "SELECT ProgramaCuentaID FROM programacuenta
                     WHERE ProgramaCuentaID = :id AND ProgramaID = :programaID
                     AND TipoCuenta = 'MATRICULA' AND Estado = 1"
                );
                $stmtCuenta->bindParam(":id", $programaCuentaID, PDO::PARAM_INT);
                $stmtCuenta->bindParam(":programaID", $datos['ProgramaID'], PDO::PARAM_INT);
                $stmtCuenta->execute();

                if (!$stmtCuenta->fetch()) {
                    $pdo->rollBack();
                    return [
                        'status' => 'error',
                        'mensaje' => 'La cuenta seleccionada no es válida para este programa'
                    ];
                }
            }

            // Porcentaje de descuento según el plan de pago elegido (Contado / Grupal / Regular=0)
            $porcentajeDescuento = isset($datos['porcentajeDescuento']) ? floatval($datos['porcentajeDescuento']) : 0;
            if ($porcentajeDescuento < 0) $porcentajeDescuento = 0;
            if ($porcentajeDescuento > 100) $porcentajeDescuento = 100;

            // Plan Regular: la matrícula puede quedar exonerada (ya pagada fuera del sistema),
            // en cuyo caso se espera un monto de Bs. 0 en vez de matrícula con descuento.
            $matriculaExonerada = !empty($datos['MatriculaExonerada']);

            // VALIDACIÓN: Si NO es pago completo, validar que el monto corresponda a la matrícula con descuento.
            // Excepción: si el estudiante egresó de una universidad PRIVADA, EXTRANJERA o DE CONVENIO,
            // el monto de matrícula es editable en el formulario (la coordinación puede cobrar más),
            // así que solo se exige que no sea MENOR al esperado, no que coincida exacto.
            if ($datos['pagoCompleto'] != 1) {
                $montoPagado = floatval($datos['montoPagado']);
                $montoEsperado = $matriculaExonerada ? 0 : round($costoMatriculaPrograma * (1 - $porcentajeDescuento / 100), 2);

                $stmtTipoUniv = $pdo->prepare(
                    "SELECT u.TipoUniversidad FROM estudiante e
                     LEFT JOIN universidad u ON e.IdUniversidad = u.IdUniversidad
                     WHERE e.EstudianteID = :estudianteID"
                );
                $stmtTipoUniv->bindParam(":estudianteID", $datos['EstudianteID'], PDO::PARAM_INT);
                $stmtTipoUniv->execute();
                $tipoUniversidad = $stmtTipoUniv->fetchColumn();

                $matriculaEditable = in_array($tipoUniversidad, ['PRIVADA', 'EXTRANJERA', 'CONVENIO'], true);

                $cumpleMonto = $matriculaEditable
                    ? ($montoPagado - $montoEsperado) >= -0.01
                    : abs($montoPagado - $montoEsperado) <= 0.01;

                if (!$cumpleMonto) {
                    $pdo->rollBack();
                    return [
                        'status' => 'error',
                        'mensaje' => $matriculaEditable
                            ? sprintf(
                                'El monto de matrícula no puede ser menor a Bs. %.2f (matrícula Bs. %.2f con %.2f%% de descuento). Monto ingresado: Bs. %.2f',
                                $montoEsperado,
                                $costoMatriculaPrograma,
                                $porcentajeDescuento,
                                $montoPagado
                            )
                            : sprintf(
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

            // Plan de pago del PROGRAMA que el estudiante propone en este momento
            // (Regular/Contado/Grupal). Es solo una propuesta: recien se vuelve
            // definitiva cuando se valida el voucher (ver ValidarVoucherMatriculaModelo),
            // que crea el registro correspondiente en `pagoprograma`.
            $planTipo = in_array($datos['PlanPropuestoTipo'] ?? null, ['REGULAR', 'CONTADO', 'GRUPAL'], true)
                ? $datos['PlanPropuestoTipo']
                : 'REGULAR';
            $planPorcentaje = isset($datos['PlanPropuestoPorcentaje']) ? floatval($datos['PlanPropuestoPorcentaje']) : 0;
            // Número de cuotas planificado (solo Plan Regular; NULL para Contado/Grupal)
            $planNumeroCuotas = ($planTipo === 'REGULAR' && !empty($datos['PlanPropuestoNumeroCuotas']))
                ? (int)$datos['PlanPropuestoNumeroCuotas']
                : null;

            // INSERTAR SOLO EN ORDENPAGO - NO tocar estudianteprograma
            $stmtOrden = $pdo->prepare(
                "INSERT INTO ordenpago
                (NumeroOrden, idInscripcion, EstudianteID, ProgramaID, ProgramaCuentaID,
                 MontoTotal, MontoDescuento, PorcentajeDescuento, MontoFinal,
                 PagoCompleto, CostoMatricula, FechaGeneracion, Observaciones,
                 PlanPropuestoTipo, PlanPropuestoNumeroCuotas, PlanPropuestoPorcentaje,
                 NombreFactura, NitCiFactura, ResponsableGeneracion, Firma, Estado)
                VALUES
                (:numeroOrden, NULL, :estudianteID, :programaID, :programaCuentaID,
                 :montoTotal, :montoDescuento, :porcentajeDescuento, :montoFinal,
                 :pagoCompleto, :costoMatricula, NOW(), :observaciones,
                 :planTipo, :planNumeroCuotas, :planPorcentaje,
                 :nombreFactura, :nitCiFactura, :responsable, :firma, 'PENDIENTE')"
            );

            $stmtOrden->bindParam(":numeroOrden", $numeroOrden, PDO::PARAM_STR);
            $stmtOrden->bindParam(":estudianteID", $datos['EstudianteID'], PDO::PARAM_INT);
            $stmtOrden->bindParam(":programaID", $datos['ProgramaID'], PDO::PARAM_INT);
            if ($programaCuentaID) {
                $stmtOrden->bindParam(":programaCuentaID", $programaCuentaID, PDO::PARAM_INT);
            } else {
                $stmtOrden->bindValue(":programaCuentaID", null, PDO::PARAM_NULL);
            }
            $stmtOrden->bindParam(":montoTotal", $montoTotal);
            $stmtOrden->bindParam(":montoDescuento", $montoDescuento);
            $stmtOrden->bindParam(":porcentajeDescuento", $porcentajeDescuento);
            $stmtOrden->bindParam(":montoFinal", $montoFinal);
            $stmtOrden->bindParam(":pagoCompleto", $datos['pagoCompleto'], PDO::PARAM_INT);
            $stmtOrden->bindParam(":costoMatricula", $costoMatricula);
            $stmtOrden->bindParam(":observaciones", $observaciones, PDO::PARAM_STR);
            $stmtOrden->bindParam(":planTipo", $planTipo, PDO::PARAM_STR);
            if ($planNumeroCuotas !== null) {
                $stmtOrden->bindParam(":planNumeroCuotas", $planNumeroCuotas, PDO::PARAM_INT);
            } else {
                $stmtOrden->bindValue(":planNumeroCuotas", null, PDO::PARAM_NULL);
            }
            $stmtOrden->bindParam(":planPorcentaje", $planPorcentaje);
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
                    op.PlanPropuestoTipo,
                    op.PlanPropuestoPorcentaje,
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
                    p.Codigo as CodigoPrograma,
                    p.Sede
                FROM ordenpago op
                INNER JOIN estudiante e ON op.EstudianteID = e.EstudianteID
                INNER JOIN programa p ON op.ProgramaID = p.ProgramaID
                WHERE op.Estado = 'PENDIENTE'
                AND op.idInscripcion IS NULL
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
                AND op.idInscripcion IS NULL
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
                    op.PlanPropuestoTipo,
                    op.PlanPropuestoPorcentaje,
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
                    COALESCE(
                        (SELECT pc.NumeroCuenta FROM programacuenta pc
                         WHERE pc.ProgramaCuentaID = op.ProgramaCuentaID),
                        (SELECT pc.NumeroCuenta FROM programacuenta pc
                         WHERE pc.ProgramaID = p.ProgramaID AND pc.TipoCuenta = 'MATRICULA' AND pc.Principal = 1 AND pc.Estado = 1
                         LIMIT 1),
                        p.NumeroTramite
                    ) AS NumeroTramite,
                    p.Costo AS CostoPrograma,
                    -- Detalle de la cuenta a depositar (para el modal de detalle):
                    -- la elegida en el pre-registro o, si no se guardó, la principal
                    -- de matrícula del programa (misma regla que NumeroTramite).
                    op.ProgramaCuentaID IS NOT NULL AS CuentaElegida,
                    cta.TipoCuenta AS CuentaTipo,
                    cta.NumeroCuenta AS CuentaNumero,
                    cta.Banco AS CuentaBanco,
                    cta.Principal AS CuentaPrincipal
                FROM ordenpago op
                INNER JOIN estudiante e ON op.EstudianteID = e.EstudianteID
                INNER JOIN programa p ON op.ProgramaID = p.ProgramaID
                LEFT JOIN programacuenta cta ON cta.ProgramaCuentaID = COALESCE(
                    op.ProgramaCuentaID,
                    (SELECT pc.ProgramaCuentaID FROM programacuenta pc
                     WHERE pc.ProgramaID = p.ProgramaID AND pc.TipoCuenta = 'MATRICULA' AND pc.Principal = 1 AND pc.Estado = 1
                     LIMIT 1)
                )
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

    /**
     * Ordenes de pago de cuota vencidas (fecha límite ya pasada y sin pagar
     * todavía), para el aviso de mora al ingresar al sistema y para
     * marcarlas en el Calendario Académico.
     * @return array
     */
    public static function ObtenerOrdenesVencidasModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    op.IdOrdenPago, op.NumeroOrden, op.MontoTotal, op.FechaVencimiento, op.ProgramaID,
                    e.EstudianteID, e.Nombre, e.Apaterno, e.Amaterno, e.Celular,
                    p.NombrePrograma, p.Sede
                 FROM ordenpago op
                 INNER JOIN estudiante e ON e.EstudianteID = op.EstudianteID
                 INNER JOIN programa p ON p.ProgramaID = op.ProgramaID
                 WHERE op.Estado = 'PENDIENTE'
                 AND op.ListaModulos IS NULL AND COALESCE(op.Observaciones, '') <> 'CLINICA'
                 AND op.FechaVencimiento IS NOT NULL
                 AND op.FechaVencimiento < CURDATE()
                 ORDER BY op.FechaVencimiento ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerOrdenesVencidasModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Ordenes de pago de cuota con fecha límite definida, dentro de un rango
     * de fechas (para mostrarlas como marcadores en el Calendario Académico,
     * ya sea vencidas o próximas a vencer).
     * @param string|null $desde
     * @param string|null $hasta
     * @param int|null $programaID
     * @param string|null $sede
     * @param int|null $estudianteID Si se indica, solo trae los vencimientos de ese estudiante
     *                                (para que el propio Estudiante vea su fecha límite, no la de otros)
     * @return array
     */
    /**
     * Todas las órdenes de pago (matrícula/cuota del programa y pago de módulos)
     * de un estudiante, en cualquier estado, para que él mismo las consulte e
     * imprima las que le correspondan.
     * @param int $estudianteID
     * @return array
     */
    public static function ListarOrdenesPorEstudianteModelo($estudianteID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    op.IdOrdenPago, op.NumeroOrden, op.ProgramaID, op.ListaModulos, op.MontoTotal,
                    op.MontoDescuento, op.MontoFinal, op.PagoCompleto, op.FechaGeneracion,
                    op.FechaVencimiento, op.FechaConfirmacion, op.Estado, op.Observaciones,
                    p.NombrePrograma, p.Codigo AS CodigoPrograma, p.GradoAcademico, p.Sede
                 FROM ordenpago op
                 INNER JOIN programa p ON p.ProgramaID = op.ProgramaID
                 WHERE op.EstudianteID = :estudianteID
                 ORDER BY op.FechaGeneracion DESC"
            );
            $stmt->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarOrdenesPorEstudianteModelo: " . $e->getMessage());
            return [];
        }
    }

    public static function ObtenerVencimientosPorRangoModelo($desde = null, $hasta = null, $programaID = null, $sede = null, $estudianteID = null)
    {
        try {
            $sql = "SELECT
                        op.IdOrdenPago, op.NumeroOrden, op.MontoTotal, op.FechaVencimiento, op.ProgramaID,
                        e.EstudianteID, e.Nombre, e.Apaterno, e.Amaterno, e.Celular,
                        p.NombrePrograma, p.Codigo AS CodigoPrograma, p.GradoAcademico, p.Sede
                    FROM ordenpago op
                    INNER JOIN estudiante e ON e.EstudianteID = op.EstudianteID
                    INNER JOIN programa p ON p.ProgramaID = op.ProgramaID
                    WHERE op.Estado = 'PENDIENTE'
                    AND op.ListaModulos IS NULL AND COALESCE(op.Observaciones, '') <> 'CLINICA'
                    AND op.FechaVencimiento IS NOT NULL";

            $params = [];
            if (!empty($desde)) {
                $sql .= " AND op.FechaVencimiento >= :desde";
                $params[':desde'] = substr($desde, 0, 10);
            }
            if (!empty($hasta)) {
                $sql .= " AND op.FechaVencimiento <= :hasta";
                $params[':hasta'] = substr($hasta, 0, 10);
            }
            if (!empty($programaID)) {
                $sql .= " AND op.ProgramaID = :programaID";
                $params[':programaID'] = $programaID;
            }
            if (!empty($sede)) {
                $sql .= " AND p.Sede = :sede";
                $params[':sede'] = $sede;
            }
            if (!empty($estudianteID)) {
                $sql .= " AND op.EstudianteID = :estudianteID";
                $params[':estudianteID'] = $estudianteID;
            }

            $sql .= " ORDER BY op.FechaVencimiento ASC";

            $stmt = Conexion::Conectar()->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerVencimientosPorRangoModelo: " . $e->getMessage());
            return [];
        }
    }
}
