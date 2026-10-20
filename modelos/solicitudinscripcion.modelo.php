<?php
require_once __DIR__ . '/../core/conexion.modelo.php';

/**
 * Modelo de Solicitudes de Inscripción (formulario suelto de "Inscribirme"
 * del panel). Es un buzón de contacto independiente del flujo formal de
 * Pre-Registro/Inscripción: no crea estudiantes, órdenes de pago ni
 * matrículas, solo guarda el interés del estudiante para que ADM/SEC lo
 * contacte y, si corresponde, recién ahí inicie el trámite formal.
 */
class SolicitudInscripcionModelos
{
    public static function CrearSolicitudModelo($datos)
    {
        $pdo = Conexion::Conectar();

        $stmt = $pdo->prepare(
            "INSERT INTO solicitudinscripcion
                (Nombre, Apaterno, Amaterno, Ci, Complemento, Correo, Celular, ProgramaID, ProgramaNombre, Mensaje, UsuarioID)
             VALUES
                (:nombre, :apaterno, :amaterno, :ci, :complemento, :correo, :celular, :programaID, :programaNombre, :mensaje, :usuarioID)"
        );

        $stmt->bindValue(':nombre', $datos['nombre'], PDO::PARAM_STR);
        $stmt->bindValue(':apaterno', $datos['apaterno'], PDO::PARAM_STR);
        $stmt->bindValue(':amaterno', $datos['amaterno'] !== '' ? $datos['amaterno'] : null, PDO::PARAM_STR);
        $stmt->bindValue(':ci', $datos['ci'], PDO::PARAM_STR);
        $stmt->bindValue(':complemento', $datos['complemento'] !== '' ? $datos['complemento'] : null, PDO::PARAM_STR);
        $stmt->bindValue(':correo', $datos['correo'] !== '' ? $datos['correo'] : null, PDO::PARAM_STR);
        $stmt->bindValue(':celular', $datos['celular'], PDO::PARAM_STR);
        $stmt->bindValue(':programaID', $datos['programaID'] ?: null, $datos['programaID'] ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':programaNombre', $datos['programaNombre'] !== '' ? $datos['programaNombre'] : null, PDO::PARAM_STR);
        $stmt->bindValue(':mensaje', $datos['mensaje'] !== '' ? $datos['mensaje'] : null, PDO::PARAM_STR);
        $stmt->bindValue(':usuarioID', $datos['usuarioID'] ?: null, $datos['usuarioID'] ? PDO::PARAM_INT : PDO::PARAM_NULL);

        $stmt->execute();

        return $pdo->lastInsertId();
    }

    public static function ListarSolicitudesModelo($estado = null)
    {
        $pdo = Conexion::Conectar();

        $sql = "SELECT s.*, p.NombrePrograma AS ProgramaNombreActual
                FROM solicitudinscripcion s
                LEFT JOIN programa p ON p.ProgramaID = s.ProgramaID";
        if ($estado) {
            $sql .= " WHERE s.Estado = :estado";
        }
        $sql .= " ORDER BY s.FechaSolicitud DESC";

        $stmt = $pdo->prepare($sql);
        if ($estado) {
            $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function ActualizarEstadoSolicitudModelo($idSolicitud, $estado)
    {
        $pdo = Conexion::Conectar();

        $stmt = $pdo->prepare("UPDATE solicitudinscripcion SET Estado = :estado WHERE IdSolicitud = :id");
        $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
        $stmt->bindValue(':id', $idSolicitud, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public static function ContarPendientesModelo()
    {
        $pdo = Conexion::Conectar();
        $stmt = $pdo->query("SELECT COUNT(*) FROM solicitudinscripcion WHERE Estado = 'PENDIENTE'");
        return (int) $stmt->fetchColumn();
    }
}
