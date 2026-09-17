<?php
require_once 'conexion.modelo.php';

/**
 * Modelo de Firmantes
 * Catálogo de nombres que pueden aparecer como "Responsable de la Firma" en las
 * órdenes de pago impresas (coordinador/responsable que firma físicamente la
 * boleta). Es independiente del usuario que generó la orden en el sistema.
 */
class FirmanteModelos
{
    /**
     * Listar los firmantes activos, para el select del formulario de orden de pago.
     * @return array
     */
    public static function ListarFirmantesModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT IdFirmante, Nombre FROM firmante WHERE Estado = 1 ORDER BY Nombre ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarFirmantesModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Registrar un nuevo nombre en el catálogo de firmantes (o reactivarlo si ya
     * existía pero estaba dado de baja).
     * @param string $nombre
     * @return array
     */
    public static function RegistrarFirmanteModelo($nombre)
    {
        try {
            $nombre = trim($nombre);
            if ($nombre === '') {
                return ['status' => 'error', 'mensaje' => 'El nombre es requerido'];
            }

            $pdo = Conexion::Conectar();

            $stmtExiste = $pdo->prepare("SELECT IdFirmante, Estado FROM firmante WHERE Nombre = :nombre");
            $stmtExiste->bindParam(":nombre", $nombre, PDO::PARAM_STR);
            $stmtExiste->execute();
            $existente = $stmtExiste->fetch(PDO::FETCH_ASSOC);

            if ($existente) {
                if ((int)$existente['Estado'] === 0) {
                    $stmtReactivar = $pdo->prepare("UPDATE firmante SET Estado = 1 WHERE IdFirmante = :id");
                    $stmtReactivar->bindParam(":id", $existente['IdFirmante'], PDO::PARAM_INT);
                    $stmtReactivar->execute();
                }
                return [
                    'status' => 'exitoso',
                    'idFirmante' => $existente['IdFirmante'],
                    'nombre' => $nombre
                ];
            }

            $stmt = $pdo->prepare("INSERT INTO firmante (Nombre) VALUES (:nombre)");
            $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
            $stmt->execute();

            return [
                'status' => 'exitoso',
                'idFirmante' => $pdo->lastInsertId(),
                'nombre' => $nombre
            ];
        } catch (PDOException $e) {
            error_log("Error en RegistrarFirmanteModelo: " . $e->getMessage());
            return ['status' => 'error', 'mensaje' => 'Error en el servidor: ' . $e->getMessage()];
        }
    }
}
