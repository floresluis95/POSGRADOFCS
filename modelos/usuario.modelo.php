<?php
    require_once 'conexion.modelo.php';

    class UsuarioModelos
    {
        // Método privado para validar nombres de tablas
        private static function ValidarNombreTabla($nombre)
        {
            // Lista blanca de tablas permitidas
            $tablasPermitidas = ['personal', 'usuario'];
            if (!in_array($nombre, $tablasPermitidas)) {
                throw new Exception("Tabla no permitida");
            }
            return $nombre;
        }

        public static function ListaUsuariosModelo()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM personal p INNER JOIN usuario u ON p.IdPersonal = u.IdPersonal");
            $stmt -> execute();
            return $stmt -> fetchAll();
        }

        public static function InsertarPersonalModelo($TablaPersonal, $DatosModelo)
        {
            // Validar nombre de tabla
            $TablaPersonal = self::ValidarNombreTabla($TablaPersonal);

            $stmt = Conexion::Conectar()->prepare("INSERT INTO `$TablaPersonal` (`IdPersonal`, `CedulaIdentidad`, `ApellidoPaterno`, `ApellidoMaterno`, `Nombres`, `Direccion`, `Celular`, `Telefono`)
            VALUES (:IdPersonal, :CedulaIdentidad, :ApellidoPaterno, :ApellidoMaterno, :Nombres, :Direccion, :Celular, :Telefono)");

            $stmt -> bindParam(":IdPersonal", $DatosModelo['IdPersonal'], PDO::PARAM_INT);
            $stmt -> bindParam(":CedulaIdentidad", $DatosModelo['CedulaIdentidad'], PDO::PARAM_STR);
            $stmt -> bindParam(":ApellidoPaterno", $DatosModelo['ApellidoPaterno'], PDO::PARAM_STR);
            $stmt -> bindParam(":ApellidoMaterno", $DatosModelo['ApellidoMaterno'], PDO::PARAM_STR);
            $stmt -> bindParam(":Nombres", $DatosModelo['Nombres'], PDO::PARAM_STR);
            $stmt -> bindParam(":Direccion", $DatosModelo['Direccion'], PDO::PARAM_STR);
            $stmt -> bindParam(":Celular", $DatosModelo['Celular'], PDO::PARAM_STR);
            $stmt -> bindParam(":Telefono", $DatosModelo['Telefono'], PDO::PARAM_STR);

            if ($stmt -> execute())
            {
                return 'exitoso';
            }
            else
            {
                return 'error';
            }
        }

        public static function InsertarUsuarioModelo($TablaUsuario, $DatosModelo)
        {
            // Validar nombre de tabla
            $TablaUsuario = self::ValidarNombreTabla($TablaUsuario);

            $stmt = Conexion::Conectar()->prepare("INSERT INTO `$TablaUsuario` (`IdPersonal`, `Usuario`, `Password`, `Tipo`)
             VALUES (:IdPersonal, :Usuario, :Password2, :Tipo)");

            $stmt -> bindParam(":IdPersonal", $DatosModelo['IdPersonal'], PDO::PARAM_INT);
            $stmt -> bindParam(":Usuario", $DatosModelo['Usuario'], PDO::PARAM_STR);
            $stmt -> bindParam(":Password2", $DatosModelo['Password'], PDO::PARAM_STR);
            $stmt -> bindParam(":Tipo", $DatosModelo['Tipo'], PDO::PARAM_STR);

            if ($stmt -> execute())
            {
                return 'exitoso';
            }
            else
            {
                return 'error';
            }
        }

        public static function EditarUsuaario($DatosModelo)
        {
            $stmt = Conexion::Conectar()->prepare("UPDATE `personal` SET `ApellidoPaterno`=:ApellidoPaterno,
            `ApellidoMaterno`=:ApellidoMaterno, `Nombres`=:Nombres,
            `Direccion`=:Direccion, `Celular`=:Celular, `Telefono`=:Telefono
            WHERE `CedulaIdentidad`=:CedulaIdentidad");

            $stmt -> bindParam(":CedulaIdentidad", $DatosModelo['CedulaIdentidad'], PDO::PARAM_STR);
            $stmt -> bindParam(":ApellidoPaterno", $DatosModelo['ApellidoPaterno'], PDO::PARAM_STR);
            $stmt -> bindParam(":ApellidoMaterno", $DatosModelo['ApellidoMaterno'], PDO::PARAM_STR);
            $stmt -> bindParam(":Nombres", $DatosModelo['Nombres'], PDO::PARAM_STR);
            $stmt -> bindParam(":Direccion", $DatosModelo['Direccion'], PDO::PARAM_STR);
            $stmt -> bindParam(":Celular", $DatosModelo['Celular'], PDO::PARAM_STR);
            $stmt -> bindParam(":Telefono", $DatosModelo['Telefono'], PDO::PARAM_STR);

            if ($stmt -> execute())
            {
                return 'exitoso';
            }
            else
            {
                return 'error';
            }
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

        // Métodos para gestión de estudiantes con usuarios
        public static function ListaEstudiantesSinUsuarioModelo()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT e.EstudianteID, e.Ci, e.Complemento, e.Exp,
                                                    e.Nombre, e.Apaterno, e.Amaterno, e.Correo
                                                    FROM estudiante e
                                                    LEFT JOIN usuario u ON e.Ci = u.IdPersonal
                                                    WHERE u.IdPersonal IS NULL AND e.Estado = 1
                                                    ORDER BY e.Apaterno, e.Amaterno, e.Nombre");
            $stmt->execute();
            return $stmt->fetchAll();
        }

        public static function CrearUsuarioEstudianteModelo($DatosModelo)
        {
            try {
                $conexion = Conexion::Conectar();
                $conexion->beginTransaction();

                // Insertar en tabla usuario
                $stmt = $conexion->prepare("INSERT INTO `usuario` (`IdPersonal`, `Usuario`, `Password`, `Tipo`, `Estado`)
                                           VALUES (:IdPersonal, :Usuario, :Password, :Tipo, '1')");

                $stmt->bindParam(":IdPersonal", $DatosModelo['IdPersonal'], PDO::PARAM_INT);
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

        public static function VerificarUsuarioEstudianteModelo($ci)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT u.IdPersonal FROM usuario u WHERE u.IdPersonal = :ci");
            $stmt->bindParam(":ci", $ci, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        }
    }