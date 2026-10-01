<?php
/**
 * Modelo de Recuperación de Contraseña por correo.
 * Cada solicitud genera un código de 6 dígitos que se guarda como hash en
 * `recuperacionpassword`: vence a los 10 minutos, sirve una sola vez y se
 * invalida después de MAX_INTENTOS intentos fallidos.
 */

require_once file_exists(__DIR__ . '/conexion.modelo.php') ? __DIR__ . '/conexion.modelo.php' : __DIR__ . '/../core/conexion.modelo.php';

class RecuperacionPasswordModelo
{
    const MINUTOS_VIGENCIA = 10;
    const MAX_INTENTOS = 5;

    /**
     * Usuario activo por nombre de usuario o C.I., con el correo y el nombre de
     * la persona (personal, estudiante o docente). Si el C.I. coincide con más
     * de un usuario no se elige ninguno.
     * @param string $identificador
     * @return array|null ID, Usuario, Tipo, Correo, Nombre
     */
    public static function BuscarUsuarioModelo($identificador)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT u.ID, u.Usuario, u.Tipo,
                        COALESCE(p.Correo, e.Correo, d.Correo) AS Correo,
                        COALESCE(p.Nombres, e.Nombre, d.Nombre) AS Nombre
                 FROM usuario u
                 LEFT JOIN personal p ON p.IdPersonal = u.IdPersonal
                 LEFT JOIN estudiante e ON e.EstudianteID = u.EstudianteID
                 LEFT JOIN docente d ON d.DocenteID = u.DocenteID
                 WHERE u.Estado = '1'
                 AND (u.Usuario = :usuario OR p.CedulaIdentidad = :ciPersonal OR e.Ci = :ciEstudiante OR d.Ci = :ciDocente)
                 LIMIT 2"
            );
            $stmt->execute([
                ':usuario' => $identificador,
                ':ciPersonal' => $identificador,
                ':ciEstudiante' => $identificador,
                ':ciDocente' => $identificador,
            ]);
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return count($filas) === 1 ? $filas[0] : null;
        } catch (PDOException $e) {
            error_log("Error en BuscarUsuarioModelo (recuperación): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Códigos pedidos en la última hora por un usuario y desde una IP (para
     * limitar envíos y no gastar el cupo diario de la cuenta de correo).
     * @return array ['usuario' => int, 'ip' => int]
     */
    public static function ContarSolicitudesRecientesModelo($idUsuario, $ip)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    (SELECT COUNT(*) FROM recuperacionpassword WHERE IdUsuario = :idUsuario AND FechaCreacion > NOW() - INTERVAL 1 HOUR) AS usuario,
                    (SELECT COUNT(*) FROM recuperacionpassword WHERE IpSolicitud = :ip AND FechaCreacion > NOW() - INTERVAL 1 HOUR) AS ip"
            );
            $stmt->execute([':idUsuario' => $idUsuario, ':ip' => $ip]);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return ['usuario' => (int)$fila['usuario'], 'ip' => (int)$fila['ip']];
        } catch (PDOException $e) {
            error_log("Error en ContarSolicitudesRecientesModelo: " . $e->getMessage());
            // Ante la duda, se considera el límite alcanzado
            return ['usuario' => PHP_INT_MAX, 'ip' => PHP_INT_MAX];
        }
    }

    /**
     * Registrar un código nuevo; los anteriores sin usar del mismo usuario dejan de valer.
     * @return bool
     */
    public static function CrearCodigoModelo($idUsuario, $codigoHash, $ip)
    {
        $pdo = Conexion::Conectar();
        try {
            $pdo->beginTransaction();

            $pdo->prepare("UPDATE recuperacionpassword SET Usado = 1 WHERE IdUsuario = :idUsuario AND Usado = 0")
                ->execute([':idUsuario' => $idUsuario]);

            $pdo->prepare(
                "INSERT INTO recuperacionpassword (IdUsuario, CodigoHash, FechaExpira, IpSolicitud)
                 VALUES (:idUsuario, :codigoHash, NOW() + INTERVAL " . self::MINUTOS_VIGENCIA . " MINUTE, :ip)"
            )->execute([':idUsuario' => $idUsuario, ':codigoHash' => $codigoHash, ':ip' => $ip]);

            $pdo->commit();
            return true;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en CrearCodigoModelo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Último código vigente del usuario: sin usar, sin vencer y con intentos disponibles.
     * @return array|null IdRecuperacion, CodigoHash, Intentos
     */
    public static function ObtenerCodigoVigenteModelo($idUsuario)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT IdRecuperacion, CodigoHash, Intentos FROM recuperacionpassword
                 WHERE IdUsuario = :idUsuario AND Usado = 0 AND FechaExpira > NOW() AND Intentos < :maxIntentos
                 ORDER BY IdRecuperacion DESC LIMIT 1"
            );
            $stmt->bindValue(':idUsuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindValue(':maxIntentos', self::MAX_INTENTOS, PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return $fila ?: null;
        } catch (PDOException $e) {
            error_log("Error en ObtenerCodigoVigenteModelo: " . $e->getMessage());
            return null;
        }
    }

    public static function SumarIntentoModelo($idRecuperacion)
    {
        try {
            Conexion::Conectar()
                ->prepare("UPDATE recuperacionpassword SET Intentos = Intentos + 1 WHERE IdRecuperacion = :id")
                ->execute([':id' => $idRecuperacion]);
        } catch (PDOException $e) {
            error_log("Error en SumarIntentoModelo: " . $e->getMessage());
        }
    }

    /**
     * Cambiar la contraseña y marcar el código como usado, en una sola transacción.
     * El "Usado = 0" del UPDATE evita que el mismo código se use dos veces a la vez.
     * @return bool
     */
    public static function CambiarPasswordModelo($idRecuperacion, $idUsuario, $passwordHash)
    {
        $pdo = Conexion::Conectar();
        try {
            $pdo->beginTransaction();

            $marcar = $pdo->prepare("UPDATE recuperacionpassword SET Usado = 1 WHERE IdRecuperacion = :id AND Usado = 0");
            $marcar->execute([':id' => $idRecuperacion]);
            if ($marcar->rowCount() !== 1) {
                $pdo->rollBack();
                return false;
            }

            $pdo->prepare("UPDATE usuario SET Password = :password WHERE ID = :idUsuario")
                ->execute([':password' => $passwordHash, ':idUsuario' => $idUsuario]);

            $pdo->commit();
            return true;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en CambiarPasswordModelo (recuperación): " . $e->getMessage());
            return false;
        }
    }
}
