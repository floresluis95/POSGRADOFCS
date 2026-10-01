<?php
/**
 * Modelo de Requisitos de Matriculación
 * `requisito` es el catálogo (Trámite B, fotocopia de carnet, ...) y
 * `requisitoinscripcion` guarda lo que entregó cada inscripción
 * (estudiante + programa). No bloquea la inscripción: solo registra.
 */

require_once file_exists(__DIR__ . '/conexion.modelo.php') ? __DIR__ . '/conexion.modelo.php' : __DIR__ . '/../core/conexion.modelo.php';

class RequisitoModelo
{
    /**
     * Requisitos activos del catálogo, en su orden.
     * @return array IdRequisito, Nombre, Obligatorio
     */
    public static function ListarActivosModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT IdRequisito, Nombre, Obligatorio FROM requisito WHERE Estado = 1 ORDER BY Orden, IdRequisito"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarActivosModelo (requisitos): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Requisitos activos con lo entregado por una inscripción (los que aún no
     * tienen registro vienen con Entregado = 0).
     * @param int $idInscripcion
     * @return array
     */
    public static function ListarPorInscripcionModelo($idInscripcion)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT r.IdRequisito, r.Nombre, r.Obligatorio,
                        COALESCE(ri.Entregado, 0) AS Entregado, ri.FechaEntrega, ri.Observacion,
                        ri.ResponsableRegistro, ri.FechaActualizacion
                 FROM requisito r
                 LEFT JOIN requisitoinscripcion ri ON ri.IdRequisito = r.IdRequisito AND ri.idInscripcion = :idInscripcion
                 WHERE r.Estado = 1
                 ORDER BY r.Orden, r.IdRequisito"
            );
            $stmt->execute([':idInscripcion' => $idInscripcion]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarPorInscripcionModelo (requisitos): " . $e->getMessage());
            return [];
        }
    }

    public static function ExisteInscripcionModelo($idInscripcion)
    {
        try {
            $stmt = Conexion::Conectar()->prepare("SELECT 1 FROM estudianteprograma WHERE idInscripcion = :id");
            $stmt->execute([':id' => $idInscripcion]);
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en ExisteInscripcionModelo (requisitos): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Guardar el estado de los requisitos de una inscripción, en una sola
     * transacción. Solo se tocan los requisitos recibidos; la fecha de entrega
     * se conserva si ya estaba registrada y vuelve a marcarse.
     * @param int $idInscripcion
     * @param array $requisitos [IdRequisito => ['entregado' => bool, 'fecha' => 'Y-m-d'|null, 'observacion' => string]]
     * @param string|null $responsable
     * @return bool
     */
    public static function GuardarModelo($idInscripcion, array $requisitos, $responsable)
    {
        $pdo = Conexion::Conectar();
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "INSERT INTO requisitoinscripcion
                    (idInscripcion, IdRequisito, Entregado, FechaEntrega, Observacion, ResponsableRegistro)
                 SELECT :idInscripcion, r.IdRequisito, :entregado, :fecha, :observacion, :responsable
                 FROM requisito r WHERE r.IdRequisito = :idRequisito AND r.Estado = 1
                 ON DUPLICATE KEY UPDATE
                    Entregado = VALUES(Entregado),
                    FechaEntrega = VALUES(FechaEntrega),
                    Observacion = VALUES(Observacion),
                    ResponsableRegistro = VALUES(ResponsableRegistro)"
            );

            foreach ($requisitos as $idRequisito => $datos) {
                $entregado = !empty($datos['entregado']);
                $stmt->execute([
                    ':idInscripcion' => $idInscripcion,
                    ':idRequisito' => (int)$idRequisito,
                    ':entregado' => $entregado ? 1 : 0,
                    ':fecha' => $entregado ? ($datos['fecha'] ?: date('Y-m-d')) : null,
                    ':observacion' => $datos['observacion'] !== '' ? $datos['observacion'] : null,
                    ':responsable' => $responsable,
                ]);
            }

            $pdo->commit();
            return true;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en GuardarModelo (requisitos): " . $e->getMessage());
            return false;
        }
    }
}
