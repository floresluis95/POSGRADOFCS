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
                "SELECT ProgramaCuentaID, ProgramaID, TipoCuenta, NumeroCuenta, Banco, Principal, Estado
                 FROM programacuenta
                 WHERE ProgramaID = :programaID
                 ORDER BY TipoCuenta ASC, Principal DESC, NumeroCuenta ASC"
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
}
