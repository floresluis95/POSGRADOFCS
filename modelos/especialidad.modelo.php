<?php
/**
 * Modelo de Especialidades
 * Catálogo reutilizable de especialidades para el select del
 * formulario de docentes.
 */

require_once 'conexion.modelo.php';

class EspecialidadModelo
{
    /**
     * Listar todas las especialidades (para administración)
     * @return array
     */
    public static function ListarTodasModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT EspecialidadID, Nombre, Estado FROM especialidad ORDER BY Nombre ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarTodasModelo (Especialidad): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Listar solo las especialidades activas (para el select del formulario)
     * @return array
     */
    public static function ListarActivasModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT EspecialidadID, Nombre FROM especialidad WHERE Estado = 1 ORDER BY Nombre ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarActivasModelo (Especialidad): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Registrar una nueva especialidad
     * @param string $nombre
     * @return string
     */
    public static function RegistrarModelo($nombre)
    {
        try {
            $pdo = Conexion::Conectar();

            $stmtCheck = $pdo->prepare("SELECT COUNT(*) as total FROM especialidad WHERE Nombre = :nombre");
            $stmtCheck->bindParam(":nombre", $nombre, PDO::PARAM_STR);
            $stmtCheck->execute();
            if ($stmtCheck->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
                return "duplicado";
            }

            $stmt = $pdo->prepare("INSERT INTO especialidad (Nombre, Estado) VALUES (:nombre, 1)");
            $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);

            return $stmt->execute() ? "exitoso" : "error";
        } catch (PDOException $e) {
            error_log("Error en RegistrarModelo (Especialidad): " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Cambiar el estado (activa/inactiva) de una especialidad
     * @param int $id
     * @param string $estado '1' o '0'
     * @return string
     */
    public static function CambiarEstadoModelo($id, $estado)
    {
        try {
            $stmt = Conexion::Conectar()->prepare("UPDATE especialidad SET Estado = :estado WHERE EspecialidadID = :id");
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

            return $stmt->execute() ? "exitoso" : "error";
        } catch (PDOException $e) {
            error_log("Error en CambiarEstadoModelo (Especialidad): " . $e->getMessage());
            return "error";
        }
    }
}
