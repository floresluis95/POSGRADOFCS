<?php
/**
 * Modelo de Cuentas por Programa
 * Un programa puede tener varias cuentas bancarias clasificadas por tipo
 * (ej. MATRICULA, CUOTAS, CLINICAS). Cada tipo puede tener más de una
 * cuenta activa, pero solo una puede marcarse como "Principal" por tipo:
 * esa es la que se usa automáticamente al imprimir la orden de pago.
 */

require_once __DIR__ . '/../core/conexion.modelo.php';

class ProgramaCuentaModelo
{
    /**
     * Listar todas las cuentas (activas e inactivas) de un programa
     * @param int $programaID
     * @return array
     */
    public static function ListarPorProgramaModelo($programaID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT pc.ProgramaCuentaID, pc.ProgramaID, pc.TipoCuenta, pc.NumeroCuenta, pc.Banco, pc.Principal, pc.Estado,
                        (SELECT COUNT(*) FROM ordenpago op WHERE op.ProgramaCuentaID = pc.ProgramaCuentaID) AS Ordenes
                 FROM programacuenta pc
                 WHERE pc.ProgramaID = :programaID
                 ORDER BY pc.TipoCuenta ASC, pc.Principal DESC, pc.NumeroCuenta ASC"
            );
            $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarPorProgramaModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Listar las cuentas ACTIVAS de un programa filtradas por tipo, para
     * ofrecerlas como opciones de un <select> (ej. elegir a qué cuenta
     * debe depositar el estudiante en la orden de pago de matrícula).
     * @param int $programaID
     * @param string $tipoCuenta
     * @return array
     */
    public static function ListarActivasPorProgramaYTipoModelo($programaID, $tipoCuenta)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT ProgramaCuentaID, TipoCuenta, NumeroCuenta, Banco, Principal
                 FROM programacuenta
                 WHERE ProgramaID = :programaID AND TipoCuenta = :tipoCuenta AND Estado = 1
                 ORDER BY Principal DESC, NumeroCuenta ASC"
            );
            $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmt->bindParam(":tipoCuenta", $tipoCuenta, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarActivasPorProgramaYTipoModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Listar los tipos de cuenta a sugerir (datalist) al registrar una cuenta
     * nueva. Siempre incluye los tipos "canónicos" (los que el resto del
     * sistema busca por nombre exacto al generar órdenes de pago: MATRICULA,
     * CUOTAS) primero, para que no se vuelvan a escribir mal (ej. "CUOTA" en
     * vez de "CUOTAS", que hacía que la cuenta quedara invisible aunque
     * existiera). Los demás tipos ya usados se agregan después, tal cual.
     * @return array
     */
    public static function ListarTiposUsadosModelo()
    {
        $canonicos = ['MATRICULA', 'CUOTAS'];
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT DISTINCT TipoCuenta FROM programacuenta ORDER BY TipoCuenta ASC"
            );
            $stmt->execute();
            $usados = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return array_values(array_unique(array_merge($canonicos, $usados)));
        } catch (PDOException $e) {
            error_log("Error en ListarTiposUsadosModelo: " . $e->getMessage());
            return $canonicos;
        }
    }

    /**
     * Registrar una nueva cuenta para un programa.
     * Si es la primera cuenta de ese tipo para el programa, se marca
     * automáticamente como Principal.
     * @param array $datos
     * @return string
     */
    public static function RegistrarModelo($datos)
    {
        try {
            $pdo = Conexion::Conectar();

            $stmtCheck = $pdo->prepare(
                "SELECT COUNT(*) as total FROM programacuenta
                 WHERE ProgramaID = :programaID AND TipoCuenta = :tipo AND NumeroCuenta = :numerocuenta"
            );
            $stmtCheck->execute([
                ':programaID' => $datos['ProgramaID'],
                ':tipo' => $datos['TipoCuenta'],
                ':numerocuenta' => $datos['NumeroCuenta'],
            ]);
            if ($stmtCheck->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
                return "duplicado";
            }

            $stmtExisteTipo = $pdo->prepare(
                "SELECT COUNT(*) as total FROM programacuenta WHERE ProgramaID = :programaID AND TipoCuenta = :tipo"
            );
            $stmtExisteTipo->execute([
                ':programaID' => $datos['ProgramaID'],
                ':tipo' => $datos['TipoCuenta'],
            ]);
            $esPrimeraDelTipo = $stmtExisteTipo->fetch(PDO::FETCH_ASSOC)['total'] == 0;

            $stmt = $pdo->prepare(
                "INSERT INTO programacuenta (ProgramaID, TipoCuenta, NumeroCuenta, Banco, Principal, Estado)
                 VALUES (:programaID, :tipo, :numerocuenta, :banco, :principal, 1)"
            );
            $stmt->execute([
                ':programaID' => $datos['ProgramaID'],
                ':tipo' => $datos['TipoCuenta'],
                ':numerocuenta' => $datos['NumeroCuenta'],
                ':banco' => $datos['Banco'] !== '' ? $datos['Banco'] : null,
                ':principal' => $esPrimeraDelTipo ? 1 : 0,
            ]);

            return "exitoso";
        } catch (PDOException $e) {
            error_log("Error en RegistrarModelo (ProgramaCuenta): " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Cambiar el estado (activa/inactiva) de una cuenta de programa
     * @param int $id
     * @param string $estado '1' o '0'
     * @return string
     */
    public static function CambiarEstadoModelo($id, $estado)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "UPDATE programacuenta SET Estado = :estado WHERE ProgramaCuentaID = :id"
            );
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            return $stmt->execute() ? "exitoso" : "error";
        } catch (PDOException $e) {
            error_log("Error en CambiarEstadoModelo (ProgramaCuenta): " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Marcar una cuenta como Principal para su tipo dentro del programa.
     * Quita el estado Principal de las demás cuentas del mismo
     * ProgramaID + TipoCuenta, para que solo haya una principal a la vez.
     * @param int $id
     * @return string
     */
    public static function MarcarPrincipalModelo($id)
    {
        try {
            $pdo = Conexion::Conectar();

            $stmt = $pdo->prepare(
                "SELECT ProgramaID, TipoCuenta FROM programacuenta WHERE ProgramaCuentaID = :id"
            );
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            $cuenta = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$cuenta) {
                return "error";
            }

            $pdo->beginTransaction();

            $stmtQuitar = $pdo->prepare(
                "UPDATE programacuenta SET Principal = 0 WHERE ProgramaID = :programaID AND TipoCuenta = :tipo"
            );
            $stmtQuitar->execute([':programaID' => $cuenta['ProgramaID'], ':tipo' => $cuenta['TipoCuenta']]);

            $stmtMarcar = $pdo->prepare(
                "UPDATE programacuenta SET Principal = 1 WHERE ProgramaCuentaID = :id"
            );
            $stmtMarcar->execute([':id' => $id]);

            $pdo->commit();
            return "exitoso";
        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en MarcarPrincipalModelo (ProgramaCuenta): " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Cuántas órdenes de pago eligieron esta cuenta (ordenpago.ProgramaCuentaID).
     * Una cuenta usada no se borra y no cambia de tipo: solo se corrige número/banco.
     */
    public static function ContarOrdenesModelo($id)
    {
        $stmt = Conexion::Conectar()->prepare("SELECT COUNT(*) FROM ordenpago WHERE ProgramaCuentaID = :id");
        $stmt->execute([':id' => $id]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Si el tipo de cuenta de un programa se quedó sin Principal (se borró o cambió de
     * tipo la que lo era), pasa a Principal la cuenta activa más antigua de ese tipo.
     */
    private static function AsegurarPrincipalModelo($pdo, $programaID, $tipo)
    {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM programacuenta WHERE ProgramaID = :programaID AND TipoCuenta = :tipo AND Principal = 1"
        );
        $stmt->execute([':programaID' => $programaID, ':tipo' => $tipo]);
        if ((int)$stmt->fetchColumn() > 0) {
            return;
        }
        $stmt = $pdo->prepare(
            "UPDATE programacuenta SET Principal = 1
             WHERE ProgramaID = :programaID AND TipoCuenta = :tipo
             ORDER BY Estado DESC, ProgramaCuentaID ASC LIMIT 1"
        );
        $stmt->execute([':programaID' => $programaID, ':tipo' => $tipo]);
    }

    /**
     * Corregir una cuenta registrada por error.
     * @param array $datos ProgramaCuentaID, TipoCuenta, NumeroCuenta, Banco
     * @return string exitoso | duplicado | en_uso (cambio de tipo en cuenta ya usada) | error
     */
    public static function EditarModelo($datos)
    {
        try {
            $pdo = Conexion::Conectar();

            $stmt = $pdo->prepare("SELECT ProgramaID, TipoCuenta FROM programacuenta WHERE ProgramaCuentaID = :id");
            $stmt->execute([':id' => $datos['ProgramaCuentaID']]);
            $actual = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$actual) {
                return "error";
            }

            $cambiaTipo = $datos['TipoCuenta'] !== $actual['TipoCuenta'];
            // Una orden de matrícula no puede quedar apuntando a una cuenta de CUOTAS
            if ($cambiaTipo && self::ContarOrdenesModelo($datos['ProgramaCuentaID']) > 0) {
                return "en_uso";
            }

            $stmtCheck = $pdo->prepare(
                "SELECT COUNT(*) FROM programacuenta
                 WHERE ProgramaID = :programaID AND TipoCuenta = :tipo AND NumeroCuenta = :numerocuenta
                   AND ProgramaCuentaID <> :id"
            );
            $stmtCheck->execute([
                ':programaID' => $actual['ProgramaID'],
                ':tipo' => $datos['TipoCuenta'],
                ':numerocuenta' => $datos['NumeroCuenta'],
                ':id' => $datos['ProgramaCuentaID'],
            ]);
            if ((int)$stmtCheck->fetchColumn() > 0) {
                return "duplicado";
            }

            $pdo->beginTransaction();

            // Al cambiar de tipo deja de ser Principal (el tipo nuevo puede tener la suya)
            $stmt = $pdo->prepare(
                "UPDATE programacuenta
                 SET TipoCuenta = :tipo, NumeroCuenta = :numerocuenta, Banco = :banco,
                     Principal = IF(:cambia = 1, 0, Principal)
                 WHERE ProgramaCuentaID = :id"
            );
            $stmt->execute([
                ':tipo' => $datos['TipoCuenta'],
                ':numerocuenta' => $datos['NumeroCuenta'],
                ':banco' => $datos['Banco'] !== '' ? $datos['Banco'] : null,
                ':cambia' => $cambiaTipo ? 1 : 0,
                ':id' => $datos['ProgramaCuentaID'],
            ]);

            if ($cambiaTipo) {
                self::AsegurarPrincipalModelo($pdo, $actual['ProgramaID'], $actual['TipoCuenta']);
                self::AsegurarPrincipalModelo($pdo, $actual['ProgramaID'], $datos['TipoCuenta']);
            }

            $pdo->commit();
            return "exitoso";
        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en EditarModelo (ProgramaCuenta): " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Borrar una cuenta registrada por error. Solo si ninguna orden de pago la usó
     * (si no, se da de baja). Si era la Principal, otra de su tipo pasa a serlo.
     * @return string exitoso | en_uso | error
     */
    public static function EliminarModelo($id)
    {
        try {
            $pdo = Conexion::Conectar();

            $stmt = $pdo->prepare("SELECT ProgramaID, TipoCuenta FROM programacuenta WHERE ProgramaCuentaID = :id");
            $stmt->execute([':id' => $id]);
            $cuenta = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$cuenta) {
                return "error";
            }
            if (self::ContarOrdenesModelo($id) > 0) {
                return "en_uso";
            }

            $pdo->beginTransaction();
            $pdo->prepare("DELETE FROM programacuenta WHERE ProgramaCuentaID = :id")->execute([':id' => $id]);
            self::AsegurarPrincipalModelo($pdo, $cuenta['ProgramaID'], $cuenta['TipoCuenta']);
            $pdo->commit();
            return "exitoso";
        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en EliminarModelo (ProgramaCuenta): " . $e->getMessage());
            return "error";
        }
    }
}
