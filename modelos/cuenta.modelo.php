<?php
/**
 * Modelo de Cuentas
 * Gestiona el catálogo de números de cuenta disponibles para los programas
 */

require_once __DIR__ . '/../core/conexion.modelo.php';

class CuentaModelo
{
    /**
     * Registrar una nueva cuenta
     * @param array $datos
     * @return string
     */
    public static function RegistrarCuentaModelo($datos)
    {
        try {
            $pdo = Conexion::Conectar();

            $stmtCheck = $pdo->prepare("SELECT COUNT(*) as total FROM cuenta WHERE NumeroCuenta = :numerocuenta");
            $stmtCheck->bindParam(":numerocuenta", $datos['NumeroCuenta'], PDO::PARAM_STR);
            $stmtCheck->execute();
            $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($result['total'] > 0) {
                return "duplicado";
            }

            $stmt = $pdo->prepare(
                "INSERT INTO cuenta (NumeroCuenta, Banco, Estado) VALUES (:numerocuenta, :banco, '1')"
            );
            $stmt->bindParam(":numerocuenta", $datos['NumeroCuenta'], PDO::PARAM_STR);
            $stmt->bindParam(":banco", $datos['Banco'], PDO::PARAM_STR);

            return $stmt->execute() ? "exitoso" : "error";
        } catch (PDOException $e) {
            error_log("Error en RegistrarCuentaModelo: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Listar todas las cuentas (para administración)
     * @return array
     */
    public static function ListarCuentasModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT CuentaID, NumeroCuenta, Banco, Estado FROM cuenta ORDER BY NumeroCuenta ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarCuentasModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Listar solo las cuentas activas (para el select del formulario de programa)
     * @return array
     */
    public static function ListarCuentasActivasModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT CuentaID, NumeroCuenta, Banco FROM cuenta WHERE Estado = '1' ORDER BY NumeroCuenta ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarCuentasActivasModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cambiar el estado de una cuenta (activar/dar de baja)
     * @param int $id
     * @param string $estado '1' o '0'
     * @return string
     */
    public static function CambiarEstadoCuentaModelo($id, $estado)
    {
        try {
            $stmt = Conexion::Conectar()->prepare("UPDATE cuenta SET Estado = :estado WHERE CuentaID = :id");
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

            return $stmt->execute() ? "exitoso" : "error";
        } catch (PDOException $e) {
            error_log("Error en CambiarEstadoCuentaModelo: " . $e->getMessage());
            return "error";
        }
    }
}
