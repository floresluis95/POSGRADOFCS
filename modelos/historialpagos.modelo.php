<?php
require_once 'conexion.modelo.php';

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

            $estudianteID = $estudiante['EstudianteID'];

            $stmtProgramas = $pdo->prepare(
                "SELECT
                    ep.idInscripcion, ep.ProgramaID, ep.costomatricula, ep.montoPagado,
                    ep.pagoCompleto, ep.porcentajeDescuento AS PorcentajeDescuentoMatricula,
                    ep.nvauchermatricula, ep.FechaInscripcion, ep.Estado AS EstadoInscripcion,
                    CASE WHEN ep.foto IS NOT NULL THEN 'SI' ELSE 'NO' END AS TieneFotoMatricula,
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
                "SELECT Idpagomodulo, NumeroCuota, nvaucher AS NumeroVoucher, fechapago AS FechaPago,
                        montocuota AS Monto, Estado, MotivoRechazo,
                        CASE WHEN fmodulo IS NOT NULL THEN 'SI' ELSE 'NO' END AS TieneFoto
                 FROM pagomodulo
                 WHERE idinscripcion = :idInscripcion
                 ORDER BY fechapago ASC, Idpagomodulo ASC"
            );

            foreach ($programas as &$programa) {
                $stmtCuotas->bindParam(":idInscripcion", $programa['idInscripcion'], PDO::PARAM_INT);
                $stmtCuotas->execute();
                $programa['Cuotas'] = $stmtCuotas->fetchAll(PDO::FETCH_ASSOC);
            }
            unset($programa);

            return [
                'estudiante' => $estudiante,
                'programas' => $programas
            ];
        } catch (PDOException $e) {
            error_log("Error en BuscarHistorialPorCiModelo: " . $e->getMessage());
            return null;
        }
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
     * @param array $datos numeroVoucher, fechaPago, monto, foto (string binaria u null)
     * @return string 'exitoso'|'error'
     */
    public static function ActualizarMatriculaModelo($idInscripcion, $datos)
    {
        try {
            $pdo = Conexion::Conectar();

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

            return $stmt->execute() ? 'exitoso' : 'error';
        } catch (PDOException $e) {
            error_log("Error en ActualizarMatriculaModelo: " . $e->getMessage());
            return 'error';
        }
    }

    /**
     * Corregir los datos de una cuota ya registrada (fecha, N° de voucher, monto
     * y, opcionalmente, la foto). Igual que ActualizarMatriculaModelo: es una
     * corrección de captura, no cambia el Estado de la cuota.
     * @param int $idPagoModulo
     * @param array $datos numeroVoucher, fechaPago, monto, foto (string binaria u null)
     * @return string 'exitoso'|'error'
     */
    public static function ActualizarCuotaModelo($idPagoModulo, $datos)
    {
        try {
            $pdo = Conexion::Conectar();

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

            return $stmt->execute() ? 'exitoso' : 'error';
        } catch (PDOException $e) {
            error_log("Error en ActualizarCuotaModelo: " . $e->getMessage());
            return 'error';
        }
    }
}
