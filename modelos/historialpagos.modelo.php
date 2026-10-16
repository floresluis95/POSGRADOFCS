<?php
require_once 'conexion.modelo.php';
require_once __DIR__ . '/voucher.modelo.php';
require_once __DIR__ . '/pagoclinica.modelo.php';
require_once __DIR__ . '/modulopagos_core.php'; // cuotas sin voucher validado

/**
 * Modelo de Historial de Pagos
 * Junta, para un estudiante buscado por C.I., todas sus inscripciones a
 * programas con el detalle de pago de cada una: tipo de plan, matrícula
 * (voucher/fecha/foto) y cuotas del programa (voucher/fecha/foto de cada una).
 * Es de solo lectura: no modifica nada, solo arma la vista consolidada.
 */
class HistorialPagosModelos
{
    /**
     * Buscar un estudiante por C.I. exacto y devolver, junto a sus datos, el
     * historial de pagos de todos los programas en los que se inscribió.
     * @param string $ci
     * @return array|null null si no se encontró el estudiante
     */
    public static function BuscarHistorialPorCiModelo($ci)
    {
        try {
            $pdo = Conexion::Conectar();

            $stmtEst = $pdo->prepare(
                "SELECT EstudianteID, Ci, Complemento, Exp, Nombre, Apaterno, Amaterno, Correo, Celular
                 FROM estudiante WHERE Ci = :ci LIMIT 1"
            );
            $stmtEst->bindParam(":ci", $ci, PDO::PARAM_STR);
            $stmtEst->execute();
            $estudiante = $stmtEst->fetch(PDO::FETCH_ASSOC);

            if (!$estudiante) {
                return null;
            }

            return self::ArmarHistorialDelEstudiante($estudiante);
        } catch (PDOException $e) {
            error_log("Error en BuscarHistorialPorCiModelo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Buscar estudiantes cuyo nombre y/o apellidos coincidan (parcialmente, sin
     * distinguir mayúsculas) con el término ingresado. Se usa como alternativa
     * a la búsqueda por C.I. cuando no se conoce el carnet.
     * @param string $termino
     * @return array lista de estudiantes (puede ser vacía)
     */
    public static function BuscarEstudiantesPorNombreModelo($termino)
    {
        try {
            $pdo = Conexion::Conectar();

            $like = '%' . $termino . '%';

            $stmt = $pdo->prepare(
                "SELECT EstudianteID, Ci, Complemento, Exp, Nombre, Apaterno, Amaterno, Correo, Celular
                 FROM estudiante
                 WHERE CONCAT(Apaterno, ' ', Amaterno, ' ', Nombre) LIKE :termino1
                    OR CONCAT(Nombre, ' ', Apaterno, ' ', Amaterno) LIKE :termino2
                    OR Nombre LIKE :termino3
                    OR Apaterno LIKE :termino4
                    OR Amaterno LIKE :termino5
                 ORDER BY Apaterno, Amaterno, Nombre
                 LIMIT 50"
            );
            $stmt->bindParam(":termino1", $like, PDO::PARAM_STR);
            $stmt->bindParam(":termino2", $like, PDO::PARAM_STR);
            $stmt->bindParam(":termino3", $like, PDO::PARAM_STR);
            $stmt->bindParam(":termino4", $like, PDO::PARAM_STR);
            $stmt->bindParam(":termino5", $like, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en BuscarEstudiantesPorNombreModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Devolver el historial de pagos completo de un estudiante ya identificado
     * por su EstudianteID (usado tras elegir un resultado de la búsqueda por
     * nombre).
     * @param int $estudianteID
     * @return array|null null si el estudiante no existe
     */
    public static function BuscarHistorialPorEstudianteIdModelo($estudianteID)
    {
        try {
            $pdo = Conexion::Conectar();

            $stmtEst = $pdo->prepare(
                "SELECT EstudianteID, Ci, Complemento, Exp, Nombre, Apaterno, Amaterno, Correo, Celular
                 FROM estudiante WHERE EstudianteID = :estudianteID LIMIT 1"
            );
            $stmtEst->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
            $stmtEst->execute();
            $estudiante = $stmtEst->fetch(PDO::FETCH_ASSOC);

            if (!$estudiante) {
                return null;
            }

            return self::ArmarHistorialDelEstudiante($estudiante);
        } catch (PDOException $e) {
            error_log("Error en BuscarHistorialPorEstudianteIdModelo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Armar el arreglo estudiante + programas/cuotas para un estudiante ya
     * obtenido de la BD. Lógica compartida entre la búsqueda por C.I. y por
     * EstudianteID.
     * @param array $estudiante
     * @return array
     */
    private static function ArmarHistorialDelEstudiante($estudiante)
    {
        try {
            $pdo = Conexion::Conectar();
            $estudianteID = $estudiante['EstudianteID'];

            $stmtProgramas = $pdo->prepare(
                "SELECT
                    ep.idInscripcion, ep.ProgramaID, ep.costomatricula, ep.montoPagado,
                    ep.pagoCompleto, ep.porcentajeDescuento AS PorcentajeDescuentoMatricula,
                    ep.nvauchermatricula, ep.FechaInscripcion, ep.Estado AS EstadoInscripcion,
                    CASE WHEN ep.foto IS NOT NULL THEN 'SI' ELSE 'NO' END AS TieneFotoMatricula,
                    " . VoucherModelo::SqlFechaOrdenMatricula('ep.idInscripcion') . " AS FechaOrdenMatricula,
                    p.NombrePrograma, p.GradoAcademico, p.Codigo AS CodigoPrograma, p.Sede,
                    pp.TipoPlan, pp.NumeroCuotas AS NumeroCuotasPlan,
                    pp.PorcentajeDescuento AS PorcentajeDescuentoPlan,
                    pp.CostoTotalPrograma, pp.MontoTotalPagar
                 FROM estudianteprograma ep
                 INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
                 LEFT JOIN pagoprograma pp ON pp.idInscripcion = ep.idInscripcion AND pp.Estado != 'ANULADO'
                 WHERE ep.EstudianteID = :estudianteID
                 ORDER BY ep.FechaInscripcion DESC"
            );
            $stmtProgramas->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
            $stmtProgramas->execute();
            $programas = $stmtProgramas->fetchAll(PDO::FETCH_ASSOC);

            $stmtCuotas = $pdo->prepare(
                "SELECT pm.Idpagomodulo, pm.NumeroCuota, pm.nvaucher AS NumeroVoucher, pm.fechapago AS FechaPago,
                        pm.montocuota AS Monto, pm.Estado, pm.MotivoRechazo,
                        CASE WHEN pm.fmodulo IS NOT NULL THEN 'SI' ELSE 'NO' END AS TieneFoto,
                        op.FechaGeneracion AS FechaOrden
                 FROM pagomodulo pm
                 LEFT JOIN ordenpago op ON op.IdOrdenPago = pm.IdOrdenPago
                 WHERE pm.idinscripcion = :idInscripcion
                 ORDER BY pm.fechapago ASC, pm.Idpagomodulo ASC"
            );

            foreach ($programas as &$programa) {
                $stmtCuotas->bindParam(":idInscripcion", $programa['idInscripcion'], PDO::PARAM_INT);
                $stmtCuotas->execute();
                $programa['Cuotas'] = $stmtCuotas->fetchAll(PDO::FETCH_ASSOC);
                // Clínicas: aparte de las cuotas (no cuentan para el saldo del programa)
                $programa['Clinicas'] = PagoClinicaModelo::ListarPorInscripcionModelo($programa['idInscripcion']);
                // Cuotas con orden pero sin voucher validado: se muestran como Pendiente
                $programa['CuotasPorPagar'] = PagoModuloModelo::ListarCuotasSinPagarModelo($programa['idInscripcion']);
            }
            unset($programa);

            return [
                'estudiante' => $estudiante,
                'programas' => $programas
            ];
        } catch (PDOException $e) {
            error_log("Error en ArmarHistorialDelEstudiante: " . $e->getMessage());
            return null;
        }
    }

    /**
     * ¿La inscripción es de ese estudiante? Para que un estudiante solo vea su
     * comprobante de matrícula y su recibo aunque cambie el id en la URL.
     * @return bool
     */
    public static function InscripcionEsDelEstudianteModelo($idInscripcion, $estudianteID)
    {
        if (!(int)$estudianteID) return false;
        $stmt = Conexion::Conectar()->prepare(
            "SELECT 1 FROM estudianteprograma WHERE idInscripcion = :id AND EstudianteID = :estudianteID"
        );
        $stmt->execute([':id' => (int)$idInscripcion, ':estudianteID' => (int)$estudianteID]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Foto del voucher de MATRÍCULA de una inscripción (distinta de las fotos de
     * cuota, que ya se sirven desde VoucherModelo/pagomodulo).
     * @param int $idInscripcion
     * @return string|null
     */
    public static function ObtenerFotoMatriculaModelo($idInscripcion)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT foto FROM estudianteprograma WHERE idInscripcion = :idInscripcion"
            );
            $stmt->bindParam(":idInscripcion", $idInscripcion, PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return $fila ? $fila['foto'] : null;
        } catch (PDOException $e) {
            error_log("Error en ObtenerFotoMatriculaModelo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Corregir los datos del voucher de MATRÍCULA de una inscripción (fecha,
     * N° de voucher, monto y, opcionalmente, la foto). Se usa desde "Historial
     * de Pagos" para arreglar errores de captura, no para revalidar ni volver a
     * confirmar nada (la inscripción ya quedó confirmada al validar el voucher).
     * @param int $idInscripcion
     * @param array $datos numeroVoucher, fechaPago, monto, foto (string binaria u null),
     *        fechaOrden (Y-m-d o null: fecha en que se generó su orden de pago)
     * @return string 'exitoso'|'error'
     */
    public static function ActualizarMatriculaModelo($idInscripcion, $datos)
    {
        try {
            $pdo = Conexion::Conectar();
            $pdo->beginTransaction();

            $campos = "nvauchermatricula = :numeroVoucher, FechaInscripcion = :fechaPago,
                       costomatricula = :monto, montoPagado = :monto";
            if ($datos['foto'] !== null) {
                $campos .= ", foto = :foto";
            }

            $stmt = $pdo->prepare("UPDATE estudianteprograma SET $campos WHERE idInscripcion = :idInscripcion");
            $stmt->bindParam(":numeroVoucher", $datos['numeroVoucher'], PDO::PARAM_STR);
            $stmt->bindParam(":fechaPago", $datos['fechaPago'], PDO::PARAM_STR);
            $stmt->bindParam(":monto", $datos['monto']);
            if ($datos['foto'] !== null) {
                $stmt->bindParam(":foto", $datos['foto'], PDO::PARAM_LOB);
            }
            $stmt->bindParam(":idInscripcion", $idInscripcion, PDO::PARAM_INT);
            $stmt->execute();

            self::ActualizarFechaOrden($pdo, VoucherModelo::IdOrdenMatriculaModelo($idInscripcion, $pdo), $datos['fechaOrden'] ?? null);

            $pdo->commit();
            return 'exitoso';
        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            error_log("Error en ActualizarMatriculaModelo: " . $e->getMessage());
            return 'error';
        }
    }

    /**
     * Corregir los datos de una cuota ya registrada (fecha, N° de voucher, monto
     * y, opcionalmente, la foto). Igual que ActualizarMatriculaModelo: es una
     * corrección de captura, no cambia el Estado de la cuota.
     * @param int $idPagoModulo
     * @param array $datos numeroVoucher, fechaPago, monto, foto (string binaria u null),
     *        fechaOrden (Y-m-d o null: fecha en que se generó su orden de pago)
     * @return string 'exitoso'|'error'
     */
    public static function ActualizarCuotaModelo($idPagoModulo, $datos)
    {
        try {
            $pdo = Conexion::Conectar();
            $pdo->beginTransaction();

            $campos = "nvaucher = :numeroVoucher, fechapago = :fechaPago, montocuota = :monto";
            if ($datos['foto'] !== null) {
                $campos .= ", fmodulo = :foto";
            }

            $stmt = $pdo->prepare("UPDATE pagomodulo SET $campos WHERE Idpagomodulo = :idPagoModulo");
            $stmt->bindParam(":numeroVoucher", $datos['numeroVoucher'], PDO::PARAM_STR);
            $stmt->bindParam(":fechaPago", $datos['fechaPago'], PDO::PARAM_STR);
            $stmt->bindParam(":monto", $datos['monto']);
            if ($datos['foto'] !== null) {
                $stmt->bindParam(":foto", $datos['foto'], PDO::PARAM_LOB);
            }
            $stmt->bindParam(":idPagoModulo", $idPagoModulo, PDO::PARAM_INT);
            $stmt->execute();

            $stmtOrden = $pdo->prepare("SELECT IdOrdenPago FROM pagomodulo WHERE Idpagomodulo = :id");
            $stmtOrden->execute([':id' => $idPagoModulo]);
            self::ActualizarFechaOrden($pdo, $stmtOrden->fetchColumn() ?: null, $datos['fechaOrden'] ?? null);

            $pdo->commit();
            return 'exitoso';
        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            error_log("Error en ActualizarCuotaModelo: " . $e->getMessage());
            return 'error';
        }
    }

    /**
     * Corregir el pago (voucher) de una orden de clínica ya confirmada: fecha, N° de
     * voucher, monto y, opcionalmente, la foto o PDF. El monto se corrige también en
     * su orden (el historial y los reportes de clínicas muestran el de la orden).
     * Igual que las cuotas: no cambia el Estado. El voucher sigue siendo único en
     * todo el sistema: si se cambió, se revisa que no esté usado en otro pago.
     * @param int $idPagoClinica
     * @param array $datos numeroVoucher, fechaPago, monto, foto (binario u null), fechaOrden (Y-m-d o null)
     * @return string 'exitoso'|'error'|mensaje de voucher repetido
     */
    public static function ActualizarClinicaModelo($idPagoClinica, $datos)
    {
        try {
            $pdo = Conexion::Conectar();
            $pdo->beginTransaction();

            $stmtActual = $pdo->prepare(
                "SELECT IdOrdenPago, NumeroVoucher FROM pagoclinica
                 WHERE IdPagoClinica = :id AND Estado = 'PAGADO' FOR UPDATE"
            );
            $stmtActual->execute([':id' => $idPagoClinica]);
            $actual = $stmtActual->fetch(PDO::FETCH_ASSOC);
            if (!$actual) {
                $pdo->rollBack();
                return 'error';
            }

            $voucherCambio = mb_strtolower(trim($actual['NumeroVoucher']), 'UTF-8') !== mb_strtolower(trim($datos['numeroVoucher']), 'UTF-8');
            if ($voucherCambio) {
                $usadoEn = VoucherModelo::VoucherYaUsadoModelo($datos['numeroVoucher'], $pdo);
                if ($usadoEn !== null) {
                    $pdo->rollBack();
                    return VoucherModelo::MensajeVoucherRepetido($datos['numeroVoucher'], $usadoEn);
                }
            }

            $campos = "NumeroVoucher = :numeroVoucher, FechaPago = :fechaPago, Monto = :monto";
            if ($datos['foto'] !== null) {
                $campos .= ", FotoVoucher = :foto";
            }
            $stmt = $pdo->prepare("UPDATE pagoclinica SET $campos WHERE IdPagoClinica = :id");
            $stmt->bindParam(":numeroVoucher", $datos['numeroVoucher'], PDO::PARAM_STR);
            $stmt->bindParam(":fechaPago", $datos['fechaPago'], PDO::PARAM_STR);
            $stmt->bindParam(":monto", $datos['monto']);
            if ($datos['foto'] !== null) {
                $stmt->bindParam(":foto", $datos['foto'], PDO::PARAM_LOB);
            }
            $stmt->bindParam(":id", $idPagoClinica, PDO::PARAM_INT);
            $stmt->execute();

            $stmtOrden = $pdo->prepare("UPDATE ordenpago SET MontoTotal = :monto WHERE IdOrdenPago = :id");
            $stmtOrden->execute([':monto' => $datos['monto'], ':id' => (int)$actual['IdOrdenPago']]);
            self::ActualizarFechaOrden($pdo, $actual['IdOrdenPago'], $datos['fechaOrden'] ?? null);

            $pdo->commit();
            return 'exitoso';
        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            error_log("Error en ActualizarClinicaModelo: " . $e->getMessage());
            return 'error';
        }
    }

    /**
     * Corrige la fecha en que se generó una orden de pago (F. orden del historial).
     * Conserva la hora original, para que las órdenes del día sigan en su orden.
     * Sin orden o sin fecha nueva no hace nada.
     * @param PDO $pdo Conexión con la transacción abierta
     * @param int|null $idOrdenPago
     * @param string|null $fechaOrden Y-m-d
     */
    private static function ActualizarFechaOrden($pdo, $idOrdenPago, $fechaOrden)
    {
        if (empty($idOrdenPago) || empty($fechaOrden)) {
            return;
        }
        $stmt = $pdo->prepare(
            "UPDATE ordenpago SET FechaGeneracion = CONCAT(:fecha, ' ', TIME(FechaGeneracion))
             WHERE IdOrdenPago = :id"
        );
        $stmt->execute([':fecha' => $fechaOrden, ':id' => (int)$idOrdenPago]);
    }
}
