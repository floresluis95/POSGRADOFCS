<?php
    require_once __DIR__ . '/../core/conexion.modelo.php';

    class UsuarioModelos
    {
        public static function ListaUsuariosModelo()
        {
            // Solo muestra usuarios de personal (IdPersonal NOT NULL)
            $stmt = Conexion::Conectar()->prepare("SELECT p.*, u.*
                                                    FROM usuario u
                                                    INNER JOIN personal p ON p.IdPersonal = u.IdPersonal
                                                    WHERE u.IdPersonal IS NOT NULL");
            $stmt -> execute();
            return $stmt -> fetchAll();
        }

        /**
         * ¿Ya existe ese nombre de usuario? Con él se inicia sesión, así que es único
         * en TODO el sistema (personal, docentes y estudiantes). Sin distinguir mayúsculas.
         */
        public static function ExisteNombreUsuarioModelo($usuario)
        {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT 1 FROM usuario WHERE LOWER(TRIM(Usuario)) = LOWER(TRIM(:usuario)) LIMIT 1"
            );
            $stmt->execute([':usuario' => $usuario]);
            return $stmt->fetchColumn() !== false;
        }

        /**
         * ¿Ya hay alguien del PERSONAL con ese C.I.? Solo se compara contra el personal:
         * la misma persona puede ser además estudiante o docente (otra tabla, otra cuenta).
         */
        public static function ExisteCiPersonalModelo($ci)
        {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT 1 FROM personal WHERE TRIM(CedulaIdentidad) = TRIM(:ci) LIMIT 1"
            );
            $stmt->execute([':ci' => $ci]);
            return $stmt->fetchColumn() !== false;
        }

        /**
         * Nombre de usuario libre a partir de una base (p. ej. el C.I. del estudiante).
         * Si alguien ya lo usa (p. ej. la misma persona como personal), se agrega un
         * sufijo: 7311599 → 7311599E → 7311599E2 ... (máx. 20 caracteres, como la columna).
         */
        public static function UsuarioDisponibleModelo($base, $sufijo)
        {
            $base = substr(trim($base), 0, 20 - strlen($sufijo) - 2);
            if (!self::ExisteNombreUsuarioModelo($base)) {
                return $base;
            }
            for ($n = 1; $n < 100; $n++) {
                $candidato = $base . $sufijo . ($n > 1 ? $n : '');
                if (!self::ExisteNombreUsuarioModelo($candidato)) {
                    return $candidato;
                }
            }
            return null;
        }

        /**
         * Registra al personal y su usuario en UNA transacción: o se crean los dos, o
         * ninguno (antes, si fallaba el usuario, el personal quedaba creado sin cuenta).
         * @param array $DatosPersonal CedulaIdentidad, ApellidoPaterno, ApellidoMaterno, Nombres, Direccion, Celular, Telefono, Correo
         * @param array $DatosUsuario Usuario, Password (hash), Tipo
         * @return string 'exitoso' | 'usuario_duplicado' | 'error'
         */
        public static function RegistrarPersonalConUsuarioModelo($DatosPersonal, $DatosUsuario)
        {
            $pdo = Conexion::Conectar();
            try {
                $pdo->beginTransaction();

                // IdPersonal no es autoincremental: el siguiente número, bloqueando la tabla
                // para que dos registros simultáneos no tomen el mismo.
                $idPersonal = (int)$pdo->query("SELECT COALESCE(MAX(IdPersonal), 0) + 1 FROM personal FOR UPDATE")->fetchColumn();

                $stmt = $pdo->prepare("INSERT INTO `personal` (`IdPersonal`, `CedulaIdentidad`, `ApellidoPaterno`, `ApellidoMaterno`, `Nombres`, `Direccion`, `Celular`, `Telefono`, `Correo`)
                    VALUES (:IdPersonal, :CedulaIdentidad, :ApellidoPaterno, :ApellidoMaterno, :Nombres, :Direccion, :Celular, :Telefono, :Correo)");
                $stmt->bindValue(":IdPersonal", $idPersonal, PDO::PARAM_INT);
                $stmt->bindValue(":CedulaIdentidad", $DatosPersonal['CedulaIdentidad'], PDO::PARAM_STR);
                $stmt->bindValue(":ApellidoPaterno", $DatosPersonal['ApellidoPaterno'], PDO::PARAM_STR);
                $stmt->bindValue(":ApellidoMaterno", $DatosPersonal['ApellidoMaterno'], PDO::PARAM_STR);
                $stmt->bindValue(":Nombres", $DatosPersonal['Nombres'], PDO::PARAM_STR);
                $stmt->bindValue(":Direccion", $DatosPersonal['Direccion'], PDO::PARAM_STR);
                $stmt->bindValue(":Celular", $DatosPersonal['Celular'], PDO::PARAM_STR);
                $stmt->bindValue(":Telefono", $DatosPersonal['Telefono'], PDO::PARAM_STR);
                $stmt->bindValue(":Correo", $DatosPersonal['Correo'], $DatosPersonal['Correo'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                $stmt->execute();

                $stmt = $pdo->prepare("INSERT INTO `usuario` (`IdPersonal`, `Usuario`, `Password`, `Tipo`)
                    VALUES (:IdPersonal, :Usuario, :Password, :Tipo)");
                $stmt->bindValue(":IdPersonal", $idPersonal, PDO::PARAM_INT);
                $stmt->bindValue(":Usuario", $DatosUsuario['Usuario'], PDO::PARAM_STR);
                $stmt->bindValue(":Password", $DatosUsuario['Password'], PDO::PARAM_STR);
                $stmt->bindValue(":Tipo", $DatosUsuario['Tipo'], PDO::PARAM_STR);
                $stmt->execute();

                $pdo->commit();
                return 'exitoso';
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                // 1062 = clave única repetida (índice uq_usuario_usuario): otro lo registró a la vez
                if (($e->errorInfo[1] ?? null) == 1062) {
                    return 'usuario_duplicado';
                }
                error_log("Error en RegistrarPersonalConUsuarioModelo: " . $e->getMessage());
                return 'error';
            }
        }

        /**
         * Edita los datos del personal. Se identifica por IdPersonal (no por C.I.):
         * hay registros del personal que comparten C.I. y se editaban juntos.
         */
        public static function EditarUsuaario($DatosModelo)
        {
            $stmt = Conexion::Conectar()->prepare("UPDATE `personal` SET `ApellidoPaterno`=:ApellidoPaterno,
            `ApellidoMaterno`=:ApellidoMaterno, `Nombres`=:Nombres,
            `Direccion`=:Direccion, `Celular`=:Celular, `Telefono`=:Telefono, `Correo`=:Correo
            WHERE `IdPersonal`=:IdPersonal");

            $stmt -> bindParam(":IdPersonal", $DatosModelo['IdPersonal'], PDO::PARAM_INT);
            $stmt -> bindParam(":ApellidoPaterno", $DatosModelo['ApellidoPaterno'], PDO::PARAM_STR);
            $stmt -> bindParam(":ApellidoMaterno", $DatosModelo['ApellidoMaterno'], PDO::PARAM_STR);
            $stmt -> bindParam(":Nombres", $DatosModelo['Nombres'], PDO::PARAM_STR);
            $stmt -> bindParam(":Direccion", $DatosModelo['Direccion'], PDO::PARAM_STR);
            $stmt -> bindParam(":Celular", $DatosModelo['Celular'], PDO::PARAM_STR);
            $stmt -> bindParam(":Telefono", $DatosModelo['Telefono'], PDO::PARAM_STR);
            $stmt -> bindValue(":Correo", $DatosModelo['Correo'], $DatosModelo['Correo'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

            if ($stmt -> execute())
            {
                return 'exitoso';
            }
            else
            {
                return 'error';
            }
        }

        // Rol actual de una cuenta del personal (ADM, SEC o AUX). Por ID de usuario: una
        // misma persona puede tener más de una cuenta
        public static function RolCuentaPersonalModelo($idUsuario)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT Tipo FROM usuario WHERE ID = :id AND IdPersonal IS NOT NULL");
            $stmt->execute([':id' => (int)$idUsuario]);
            return $stmt->fetchColumn() ?: null;
        }

        // Administradores con cuenta activa (nunca debe quedar ninguno)
        public static function ContarAdministradoresActivosModelo()
        {
            return (int)Conexion::Conectar()->query("SELECT COUNT(*) FROM usuario WHERE Tipo = 'ADM' AND Estado = 1 AND IdPersonal IS NOT NULL")->fetchColumn();
        }

        /**
         * Cambia el rol de una cuenta del personal (solo esa cuenta, por su ID). Quien tenga la
         * sesión abierta con el rol anterior la pierde en su siguiente clic (core/sesion-vigente.php).
         */
        public static function CambiarRolCuentaPersonalModelo($idUsuario, $tipo)
        {
            $stmt = Conexion::Conectar()->prepare("UPDATE usuario SET Tipo = :tipo WHERE ID = :id AND IdPersonal IS NOT NULL");
            return $stmt->execute([':tipo' => $tipo, ':id' => (int)$idUsuario]) ? 'exitoso' : 'error';
        }

        public static function CambiarEstadoUsuario($idusr)
        {
            $stmt = Conexion::Conectar()->prepare("UPDATE usuario u, personal p SET `Estado`='0' WHERE u.IdPersonal=p.IdPersonal AND p.CedulaIdentidad=:idusr");
            $stmt -> bindParam(":idusr", $idusr, PDO::PARAM_STR);

            return ($stmt -> execute()) ? 'exitoso' : 'error';
        }

        public static function CambiarEstadosUsuario($idusrs)
        {
            $stmt = Conexion::Conectar()->prepare("UPDATE usuario u, personal p SET `Estado`='1' WHERE u.IdPersonal=p.IdPersonal AND p.CedulaIdentidad=:idusrs");
            $stmt -> bindParam(":idusrs", $idusrs, PDO::PARAM_STR);

            return ($stmt -> execute()) ? 'exitoso' : 'error';
        }

        /**
         * Bloquear o habilitar el acceso al sistema del usuario asociado a un
         * Docente (no borra ni afecta sus datos academicos, solo el login).
         * @param int $docenteID
         * @param int $nuevoEstado 1 = habilitado, 0 = bloqueado
         * @return string 'exitoso'|'error'
         */
        public static function CambiarEstadoUsuarioDocenteModelo($docenteID, $nuevoEstado)
        {
            $stmt = Conexion::Conectar()->prepare("UPDATE usuario SET `Estado` = :estado WHERE DocenteID = :docenteID");
            $stmt->bindParam(":estado", $nuevoEstado, PDO::PARAM_INT);
            $stmt->bindParam(":docenteID", $docenteID, PDO::PARAM_INT);

            return ($stmt->execute() && $stmt->rowCount() > 0) ? 'exitoso' : 'error';
        }

        /**
         * Bloquear o habilitar el acceso al sistema del usuario asociado a un
         * Estudiante (no borra ni afecta sus datos academicos, solo el login).
         * @param int $estudianteID
         * @param int $nuevoEstado 1 = habilitado, 0 = bloqueado
         * @return string 'exitoso'|'error'
         */
        public static function CambiarEstadoUsuarioEstudianteModelo($estudianteID, $nuevoEstado)
        {
            $stmt = Conexion::Conectar()->prepare("UPDATE usuario SET `Estado` = :estado WHERE EstudianteID = :estudianteID");
            $stmt->bindParam(":estado", $nuevoEstado, PDO::PARAM_INT);
            $stmt->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);

            return ($stmt->execute() && $stmt->rowCount() > 0) ? 'exitoso' : 'error';
        }

        // Métodos para gestión de estudiantes con usuarios
        public static function ListaEstudiantesSinUsuarioModelo()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT e.EstudianteID, e.Ci, e.Complemento, e.Exp,
                                                    e.Nombre, e.Apaterno, e.Amaterno, e.Correo
                                                    FROM estudiante e
                                                    LEFT JOIN usuario u ON e.EstudianteID = u.EstudianteID
                                                    WHERE u.EstudianteID IS NULL AND e.Estado = 1
                                                    ORDER BY e.Apaterno, e.Amaterno, e.Nombre");
            $stmt->execute();
            return $stmt->fetchAll();
        }

        public static function CrearUsuarioEstudianteModelo($DatosModelo)
        {
            try {
                $conexion = Conexion::Conectar();
                $conexion->beginTransaction();

                // Insertar en tabla usuario (EstudianteID para estudiantes, IdPersonal NULL).
                // Solo se guarda el Password hasheado; la contraseña en claro se muestra
                // una única vez al crearla y nunca se persiste.
                $stmt = $conexion->prepare("INSERT INTO `usuario` (`IdPersonal`, `EstudianteID`, `Usuario`, `Password`, `Tipo`, `Estado`)
                                           VALUES (NULL, :EstudianteID, :Usuario, :Password, :Tipo, '1')");

                $stmt->bindParam(":EstudianteID", $DatosModelo['EstudianteID'], PDO::PARAM_INT);
                $stmt->bindParam(":Usuario", $DatosModelo['Usuario'], PDO::PARAM_STR);
                $stmt->bindParam(":Password", $DatosModelo['Password'], PDO::PARAM_STR);
                $stmt->bindParam(":Tipo", $DatosModelo['Tipo'], PDO::PARAM_STR);

                if ($stmt->execute()) {
                    $conexion->commit();
                    return 'exitoso';
                } else {
                    $conexion->rollBack();
                    $errorInfo = $stmt->errorInfo();
                    error_log("Error SQL en CrearUsuarioEstudianteModelo: " . print_r($errorInfo, true));
                    return 'error: ' . $errorInfo[2];
                }
            } catch (Exception $e) {
                if (isset($conexion)) {
                    $conexion->rollBack();
                }
                error_log("Exception en CrearUsuarioEstudianteModelo: " . $e->getMessage());
                return 'error: ' . $e->getMessage();
            }
        }

        /**
         * Restablecer la contraseña de un estudiante que ya tiene usuario asignado
         * (para "olvidó su contraseña"). Solo guarda el hash; la contraseña en claro
         * se devuelve al llamador para mostrarla una única vez, no se persiste.
         * No cambia el nombre de usuario.
         */
        public static function RestablecerPasswordEstudianteModelo($estudianteID, $passwordHash)
        {
            $stmt = Conexion::Conectar()->prepare(
                "UPDATE usuario SET Password = :password WHERE EstudianteID = :estudianteID"
            );
            $stmt->bindParam(":password", $passwordHash, PDO::PARAM_STR);
            $stmt->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
            return $stmt->execute() && $stmt->rowCount() > 0 ? 'exitoso' : 'error';
        }

        /**
         * Restablecer la contraseña de un docente que ya tiene usuario asignado.
         * No cambia el nombre de usuario.
         */
        public static function RestablecerPasswordDocenteModelo($docenteID, $passwordHash)
        {
            $stmt = Conexion::Conectar()->prepare(
                "UPDATE usuario SET Password = :password WHERE DocenteID = :docenteID"
            );
            $stmt->bindParam(":password", $passwordHash, PDO::PARAM_STR);
            $stmt->bindParam(":docenteID", $docenteID, PDO::PARAM_INT);
            return $stmt->execute() && $stmt->rowCount() > 0 ? 'exitoso' : 'error';
        }

        /**
         * Restablecer la contraseña de un usuario del personal administrativo
         * (ADM, SEC, etc.). No cambia el nombre de usuario.
         */
        public static function RestablecerPasswordPersonalModelo($idPersonal, $passwordHash)
        {
            $stmt = Conexion::Conectar()->prepare(
                "UPDATE usuario SET Password = :password WHERE IdPersonal = :idPersonal"
            );
            $stmt->bindParam(":password", $passwordHash, PDO::PARAM_STR);
            $stmt->bindParam(":idPersonal", $idPersonal, PDO::PARAM_INT);
            return $stmt->execute() && $stmt->rowCount() > 0 ? 'exitoso' : 'error';
        }

        public static function VerificarUsuarioPersonalModelo($idPersonal)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT u.IdPersonal, u.Tipo FROM usuario u WHERE u.IdPersonal = :idPersonal");
            $stmt->bindParam(":idPersonal", $idPersonal, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        }

        public static function VerificarUsuarioEstudianteModelo($estudianteID)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT u.EstudianteID FROM usuario u WHERE u.EstudianteID = :estudianteID");
            $stmt->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        }

        // Métodos para gestión de docentes con usuarios
        public static function ListaDocentesSinUsuarioModelo()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT d.DocenteID, d.Ci, d.Complemento, d.Exp,
                                                    d.Nombre, d.Apaterno, d.Amaterno, d.Correo
                                                    FROM docente d
                                                    LEFT JOIN usuario u ON d.DocenteID = u.DocenteID
                                                    WHERE u.DocenteID IS NULL
                                                    ORDER BY d.Apaterno, d.Amaterno, d.Nombre");
            $stmt->execute();
            return $stmt->fetchAll();
        }

        public static function CrearUsuarioDocenteModelo($DatosModelo)
        {
            try {
                $conexion = Conexion::Conectar();
                $conexion->beginTransaction();

                // Insertar en tabla usuario (DocenteID para docentes, IdPersonal y EstudianteID NULL)
                $stmt = $conexion->prepare("INSERT INTO `usuario` (`IdPersonal`, `EstudianteID`, `DocenteID`, `Usuario`, `Password`, `Tipo`, `Estado`)
                                           VALUES (NULL, NULL, :DocenteID, :Usuario, :Password, :Tipo, '1')");

                $stmt->bindParam(":DocenteID", $DatosModelo['DocenteID'], PDO::PARAM_INT);
                $stmt->bindParam(":Usuario", $DatosModelo['Usuario'], PDO::PARAM_STR);
                $stmt->bindParam(":Password", $DatosModelo['Password'], PDO::PARAM_STR);
                $stmt->bindParam(":Tipo", $DatosModelo['Tipo'], PDO::PARAM_STR);

                if ($stmt->execute()) {
                    $conexion->commit();
                    return 'exitoso';
                } else {
                    $conexion->rollBack();
                    $errorInfo = $stmt->errorInfo();
                    error_log("Error SQL en CrearUsuarioDocenteModelo: " . print_r($errorInfo, true));
                    return 'error: ' . $errorInfo[2];
                }
            } catch (Exception $e) {
                if (isset($conexion)) {
                    $conexion->rollBack();
                }
                error_log("Exception en CrearUsuarioDocenteModelo: " . $e->getMessage());
                return 'error: ' . $e->getMessage();
            }
        }

        public static function VerificarUsuarioDocenteModelo($docenteID)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT u.DocenteID FROM usuario u WHERE u.DocenteID = :docenteID");
            $stmt->bindParam(":docenteID", $docenteID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        }
    }