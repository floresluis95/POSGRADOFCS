<?php
/**
 * Modelo de Pago de Clínicas
 * Una orden de pago de clínica es una fila de `ordenpago` marcada con
 * Observaciones = 'CLINICA' (igual que las cuotas usan 'CUOTA PROGRAMA'). Su monto
 * es libre y se deposita en la cuenta CLINICA del programa. Se confirma al registrar
 * su voucher en `pagoclinica` (tabla aparte de pagomodulo, para que no cuente como
 * cuota ni afecte el saldo del programa).
 *
 * Solo los programas con al menos una cuenta CLINICA activa manejan clínicas.
 */

require_once file_exists(__DIR__ . '/conexion.modelo.php') ? __DIR__ . '/conexion.modelo.php' : __DIR__ . '/../core/conexion.modelo.php';
require_once __DIR__ . '/ordenpagoprincipal.modelo.php'; // OrdenPagoModelos::NumeroOrdenLibre

class PagoClinicaModelo
{
    const MARCA_ORDEN = 'CLINICA';
    const TIPO_CUENTA = 'CLINICA';

    /**
     * ¿El programa maneja clínicas? (tiene al menos una cuenta CLINICA activa)
     * @param int $programaID
     * @return bool
     */
    public static function ProgramaTieneClinicaModelo($programaID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT 1 FROM programacuenta
                 WHERE ProgramaID = :programaID AND TipoCuenta = :tipo AND Estado = 1
                 LIMIT 1"
            );
            $stmt->execute([':programaID' => $programaID, ':tipo' => self::TIPO_CUENTA]);
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en ProgramaTieneClinicaModelo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Orden de clínica PENDIENTE de una inscripción (solo puede haber una a la vez),
     * con el N° de cuenta a depositar (la elegida o, si no, la principal CLINICA).
     * @param int $idinscripcion
     * @return array|null
     */
    public static function ObtenerOrdenPendienteModelo($idinscripcion)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT op.IdOrdenPago, op.NumeroOrden, op.MontoTotal, op.FechaGeneracion, op.FechaVencimiento,
                        op.NombreFactura, op.NitCiFactura, op.Firma,
                        COALESCE(
                            (SELECT pc.NumeroCuenta FROM programacuenta pc WHERE pc.ProgramaCuentaID = op.ProgramaCuentaID),
                            (SELECT pc.NumeroCuenta FROM programacuenta pc
                             WHERE pc.ProgramaID = op.ProgramaID AND pc.TipoCuenta = :tipo AND pc.Principal = 1 AND pc.Estado = 1
                             LIMIT 1)
                        ) AS NumeroCuentaDeposito
                 FROM ordenpago op
                 WHERE op.idinscripcion = :idinscripcion
                 AND op.Estado = 'PENDIENTE'
                 AND op.Observaciones = :marca
                 ORDER BY op.FechaGeneracion DESC
                 LIMIT 1"
            );
            $stmt->execute([
                ':idinscripcion' => $idinscripcion,
                ':tipo' => self::TIPO_CUENTA,
                ':marca' => self::MARCA_ORDEN,
            ]);
            $orden = $stmt->fetch(PDO::FETCH_ASSOC);
            return $orden ?: null;
        } catch (PDOException $e) {
            error_log("Error en ObtenerOrdenPendienteModelo (clínica): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Registrar una orden de pago de clínica (Estado PENDIENTE).
     * @param array $datos EstudianteID, idinscripcion, ProgramaID, ProgramaCuentaID|null,
     *        MontoTotal, FechaVencimiento|null, ResponsableGeneracion|null, Firma,
     *        NombreFactura, NitCiFactura
     * @return array ['estado' => 'exitoso'|'validacion'|'error', 'mensaje' => ..., 'numeroOrden', 'idOrdenPago']
     */
    public static function RegistrarOrdenModelo($datos)
    {
        try {
            $pdo = Conexion::Conectar();

            // La cuenta elegida debe ser una cuenta CLINICA activa de este mismo programa
            if (!empty($datos['ProgramaCuentaID'])) {
                $stmtCuenta = $pdo->prepare(
                    "SELECT 1 FROM programacuenta
                     WHERE ProgramaCuentaID = :id AND ProgramaID = :programaID AND TipoCuenta = :tipo AND Estado = 1"
                );
                $stmtCuenta->execute([
                    ':id' => $datos['ProgramaCuentaID'],
                    ':programaID' => $datos['ProgramaID'],
                    ':tipo' => self::TIPO_CUENTA,
                ]);
                if (!$stmtCuenta->fetchColumn()) {
                    return ['estado' => 'validacion', 'mensaje' => 'La cuenta seleccionada no es válida para este programa'];
                }
            }

            if (self::ObtenerOrdenPendienteModelo($datos['idinscripcion'])) {
                return ['estado' => 'validacion', 'mensaje' => 'Ya existe una orden de clínica pendiente para esta inscripción. Confirme su pago o anúlela antes de generar otra'];
            }

            // Con voucher extraviado puede haber dos órdenes del mismo estudiante en el mismo segundo
            $numeroOrden = OrdenPagoModelos::NumeroOrdenLibre($pdo,
                'CLI-' . str_pad($datos['EstudianteID'], 4, '0', STR_PAD_LEFT) . '-' .
                str_pad($datos['ProgramaID'], 3, '0', STR_PAD_LEFT) . '-' .
                date('YmdHis'));

            $stmt = $pdo->prepare(
                "INSERT INTO ordenpago
                 (EstudianteID, idinscripcion, ProgramaID, ProgramaCuentaID, MontoTotal, FechaVencimiento,
                  ResponsableGeneracion, Firma, NombreFactura, NitCiFactura, NumeroOrden, FechaGeneracion, Observaciones, Estado)
                 VALUES
                 (:estudianteID, :idinscripcion, :programaID, :programaCuentaID, :montoTotal, :fechaVencimiento,
                  :responsable, :firma, :nombreFactura, :nitCiFactura, :numeroOrden, :fechaGeneracion, :marca, 'PENDIENTE')"
            );
            $stmt->execute([
                ':estudianteID' => $datos['EstudianteID'],
                ':idinscripcion' => $datos['idinscripcion'],
                ':programaID' => $datos['ProgramaID'],
                ':programaCuentaID' => $datos['ProgramaCuentaID'] ?: null,
                ':montoTotal' => $datos['MontoTotal'],
                ':fechaVencimiento' => $datos['FechaVencimiento'] ?: null,
                ':responsable' => $datos['ResponsableGeneracion'],
                ':firma' => $datos['Firma'],
                ':nombreFactura' => $datos['NombreFactura'],
                ':nitCiFactura' => $datos['NitCiFactura'],
                ':numeroOrden' => $numeroOrden,
                ':fechaGeneracion' => $datos['FechaGeneracion'] ?? date('Y-m-d H:i:s'),
                ':marca' => self::MARCA_ORDEN,
            ]);

            return [
                'estado' => 'exitoso',
                'mensaje' => 'Orden de pago de clínica registrada',
                'numeroOrden' => $numeroOrden,
                'idOrdenPago' => (int)$pdo->lastInsertId(),
            ];
        } catch (PDOException $e) {
            error_log("Error en RegistrarOrdenModelo (clínica): " . $e->getMessage());
            return ['estado' => 'error', 'mensaje' => 'Error al registrar la orden de pago de clínica'];
        }
    }

    /**
     * Registrar el voucher de la orden de clínica pendiente y confirmarla, en una
     * sola transacción. El monto pagado es el de la orden.
     * @param array $datos idinscripcion, FechaPago, NumeroVoucher, FotoVoucher, ResponsableRegistro
     * @return array ['estado' => 'exitoso'|'sin_orden'|'error', 'mensaje' => ...]
     */
    public static function RegistrarPagoModelo($datos)
    {
        $pdo = Conexion::Conectar();
        try {
            $pdo->beginTransaction();

            // FOR UPDATE: evita que dos registros simultáneos confirmen la misma orden.
            // Con IdOrdenPago se confirma esa orden (voucher extraviado ya recuperado);
            // sin él, la pendiente.
            if (!empty($datos['IdOrdenPago'])) {
                $stmtOrden = $pdo->prepare(
                    "SELECT IdOrdenPago, MontoTotal FROM ordenpago
                     WHERE IdOrdenPago = :id AND idinscripcion = :idinscripcion
                     AND Estado IN ('PENDIENTE', 'EXTRAVIADO') AND Observaciones = :marca
                     FOR UPDATE"
                );
                $stmtOrden->execute([':id' => (int)$datos['IdOrdenPago'], ':idinscripcion' => $datos['idinscripcion'], ':marca' => self::MARCA_ORDEN]);
            } else {
                $stmtOrden = $pdo->prepare(
                    "SELECT IdOrdenPago, MontoTotal FROM ordenpago
                     WHERE idinscripcion = :idinscripcion AND Estado = 'PENDIENTE' AND Observaciones = :marca
                     ORDER BY FechaGeneracion ASC LIMIT 1
                     FOR UPDATE"
                );
                $stmtOrden->execute([':idinscripcion' => $datos['idinscripcion'], ':marca' => self::MARCA_ORDEN]);
            }
            $orden = $stmtOrden->fetch(PDO::FETCH_ASSOC);

            if (!$orden) {
                $pdo->rollBack();
                return ['estado' => 'sin_orden', 'mensaje' => 'No hay una orden de clínica pendiente. Genere primero la orden de pago'];
            }

            $stmt = $pdo->prepare(
                "INSERT INTO pagoclinica
                 (IdOrdenPago, idinscripcion, Monto, FechaPago, NumeroVoucher, FotoVoucher, ResponsableRegistro, Estado)
                 VALUES (:idOrdenPago, :idinscripcion, :monto, :fechaPago, :voucher, :foto, :responsable, 'PAGADO')"
            );
            $stmt->bindValue(':idOrdenPago', $orden['IdOrdenPago'], PDO::PARAM_INT);
            $stmt->bindValue(':idinscripcion', $datos['idinscripcion'], PDO::PARAM_INT);
            $stmt->bindValue(':monto', $orden['MontoTotal']);
            $stmt->bindValue(':fechaPago', $datos['FechaPago'], PDO::PARAM_STR);
            $stmt->bindValue(':voucher', $datos['NumeroVoucher'], PDO::PARAM_STR);
            $stmt->bindValue(':foto', $datos['FotoVoucher'], PDO::PARAM_LOB);
            $stmt->bindValue(':responsable', $datos['ResponsableRegistro'], $datos['ResponsableRegistro'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->execute();

            $upd = $pdo->prepare(
                "UPDATE ordenpago SET Estado = 'CONFIRMADO', FechaConfirmacion = NOW() WHERE IdOrdenPago = :id"
            );
            $upd->execute([':id' => $orden['IdOrdenPago']]);

            $pdo->commit();
            return ['estado' => 'exitoso', 'mensaje' => 'Pago de clínica registrado y orden confirmada'];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en RegistrarPagoModelo (clínica): " . $e->getMessage());
            return ['estado' => 'error', 'mensaje' => 'Error al registrar el pago de clínica'];
        }
    }

    /**
     * Historial de órdenes de clínica de una inscripción (pendientes y confirmadas;
     * las anuladas no se muestran), con los datos de su voucher si ya se pagó.
     * @param int $idinscripcion
     * @return array
     */
    public static function ListarPorInscripcionModelo($idinscripcion)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT op.IdOrdenPago, op.NumeroOrden, op.MontoTotal, op.FechaGeneracion, op.Estado,
                        op.MotivoExtraviado, op.FechaExtraviado, op.ResponsableExtraviado,
                        pcl.IdPagoClinica, pcl.FechaPago, pcl.NumeroVoucher,
                        (pcl.FotoVoucher IS NOT NULL) AS TieneFoto,
                        (LEFT(pcl.FotoVoucher, 4) = '%PDF') AS EsPdf
                 FROM ordenpago op
                 LEFT JOIN pagoclinica pcl ON pcl.IdOrdenPago = op.IdOrdenPago AND pcl.Estado = 'PAGADO'
                 WHERE op.idinscripcion = :idinscripcion
                 AND op.Observaciones = :marca
                 AND op.Estado <> 'ANULADO'
                 ORDER BY op.FechaGeneracion DESC"
            );
            $stmt->execute([':idinscripcion' => $idinscripcion, ':marca' => self::MARCA_ORDEN]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarPorInscripcionModelo (clínica): " . $e->getMessage());
            return [];
        }
    }

    /**
     * ¿El pago de clínica es de una inscripción de ese estudiante? Para que un
     * estudiante solo vea SUS vouchers aunque cambie el id en la URL.
     * @return bool
     */
    public static function PagoEsDelEstudianteModelo($idPagoClinica, $estudianteID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT 1 FROM pagoclinica pcl
                 INNER JOIN estudianteprograma ep ON ep.idInscripcion = pcl.idinscripcion
                 WHERE pcl.IdPagoClinica = :id AND ep.EstudianteID = :estudianteID"
            );
            $stmt->execute([':id' => (int)$idPagoClinica, ':estudianteID' => (int)$estudianteID]);
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en PagoEsDelEstudianteModelo (clínica): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Foto del voucher de un pago de clínica (binario) o null.
     * @param int $idPagoClinica
     * @return string|null
     */
    public static function ObtenerFotoVoucherModelo($idPagoClinica)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT FotoVoucher FROM pagoclinica WHERE IdPagoClinica = :id"
            );
            $stmt->execute([':id' => $idPagoClinica]);
            $foto = $stmt->fetchColumn();
            return $foto !== false && $foto !== null ? $foto : null;
        } catch (PDOException $e) {
            error_log("Error en ObtenerFotoVoucherModelo (clínica): " . $e->getMessage());
            return null;
        }
    }
}
