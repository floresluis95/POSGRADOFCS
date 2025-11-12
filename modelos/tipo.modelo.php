<?php
    require_once 'conexion.modelo.php';
    class MarcaModelos
    {
        public static function ListaMarcaWhereModelo($IdMarca)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM tipo WHERE idmarca = :IdMarca");
            $stmt -> bindParam(":IdMarca", $IdMarca, PDO::PARAM_INT);
            $stmt -> execute();
            return $stmt -> fetchAll();
        }
    }