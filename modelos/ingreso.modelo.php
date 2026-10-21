<?php
    require_once __DIR__ . '/../core/conexion.modelo.php';
    class IngresoModelos
    {
        public static function ValidarIngresoModelo($usuario)
        {
            // Consulta mejorada que soporta 3 tipos de usuarios: Personal, Estudiantes y Docentes
            $stmt = Conexion::Conectar()->prepare("
                SELECT
                    u.*,
                    -- Datos de Personal (si aplica)
                    p.IdPersonal,
                    p.CedulaIdentidad,
                    p.ApellidoPaterno,
                    p.ApellidoMaterno,
                    p.Nombres,
                    p.Direccion,
                    p.Celular,
                    p.Telefono,
                    -- Datos de Estudiante (si aplica)
                    e.EstudianteID,
                    e.Ci as EstudianteCi,
                    e.Complemento as EstudianteComplemento,
                    e.Exp as EstudianteExp,
                    e.Nombre as EstudianteNombre,
                    e.Apaterno as EstudianteApaterno,
                    e.Amaterno as EstudianteAmaterno,
                    e.Correo as EstudianteCorreo,
                    e.Celular as EstudianteCelular,
                    e.Direccion as EstudianteDireccion,
                    -- Datos de Docente (si aplica)
                    d.DocenteID,
                    d.Ci as DocenteCi,
                    d.Complemento as DocenteComplemento,
                    d.Exp as DocenteExp,
                    d.Nombre as DocenteNombre,
                    d.Apaterno as DocenteApaterno,
                    d.Amaterno as DocenteAmaterno,
                    d.Correo as DocenteCorreo,
                    d.Cel as DocenteCel,
                    d.Direccion as DocenteDireccion,
                    d.Especialidad as DocenteEspecialidad
                FROM usuario u
                LEFT JOIN personal p ON u.IdPersonal = p.IdPersonal
                LEFT JOIN estudiante e ON u.EstudianteID = e.EstudianteID
                LEFT JOIN docente d ON u.DocenteID = d.DocenteID
                WHERE u.Usuario = :usuario
            ");
            $stmt -> bindParam(":usuario", $usuario, PDO::PARAM_STR);
            $stmt -> execute();
            return $stmt -> fetch();
        }

        // Límite de intentos fallidos de ingreso (tabla intentoingreso, paso 25 del LEEME).
        // Se cuenta por nombre de usuario y no por IP: en la facultad muchos equipos salen
        // a internet con la misma IP y se bloquearía a todos.
        const MAX_INTENTOS_INGRESO = 5;
        const MINUTOS_BLOQUEO_INGRESO = 15;

        // Cantidad de intentos fallidos de este usuario en los últimos minutos de bloqueo.
        // Si la tabla aún no existe devuelve 0 (el login funciona igual, sin el límite).
        public static function ContarIntentosFallidosModelo($usuario)
        {
            try {
                $stmt = Conexion::Conectar()->prepare("
                    SELECT COUNT(*) FROM intentoingreso
                    WHERE Usuario = :usuario
                      AND Fecha > NOW() - INTERVAL " . (int)self::MINUTOS_BLOQUEO_INGRESO . " MINUTE
                ");
                $stmt->execute([':usuario' => $usuario]);
                return (int)$stmt->fetchColumn();
            } catch (PDOException $e) {
                error_log("ContarIntentosFallidosModelo: " . $e->getMessage());
                return 0;
            }
        }

        public static function RegistrarIntentoFallidoModelo($usuario)
        {
            try {
                $conexion = Conexion::Conectar();
                $stmt = $conexion->prepare("INSERT INTO intentoingreso (Usuario, IP) VALUES (:usuario, :ip)");
                $stmt->execute([
                    ':usuario' => mb_substr($usuario, 0, 100),
                    ':ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                ]);
                // Limpieza: los intentos de más de un día ya no sirven
                $conexion->exec("DELETE FROM intentoingreso WHERE Fecha < NOW() - INTERVAL 1 DAY");
            } catch (PDOException $e) {
                error_log("RegistrarIntentoFallidoModelo: " . $e->getMessage());
            }
        }

        public static function LimpiarIntentosModelo($usuario)
        {
            try {
                $stmt = Conexion::Conectar()->prepare("DELETE FROM intentoingreso WHERE Usuario = :usuario");
                $stmt->execute([':usuario' => $usuario]);
            } catch (PDOException $e) {
                error_log("LimpiarIntentosModelo: " . $e->getMessage());
            }
        }
    }