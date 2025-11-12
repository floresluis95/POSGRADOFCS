<?php
    require_once 'conexion.modelo.php';
    class IngresoModelos
    {
        public static function ValidarIngresoModelo($usuario)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM personal p INNER JOIN usuario u ON p.IdPersonal = u.IdPersonal WHERE u.Usuario = :usuario");
            $stmt -> bindParam(":usuario", $usuario, PDO::PARAM_STR);
            $stmt -> execute();
            return $stmt -> fetch();
        }
    }