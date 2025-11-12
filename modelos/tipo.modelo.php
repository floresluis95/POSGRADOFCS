<?php
    require_once 'conexion.modelo.php';
    class MarcaModelos
    {
        public function ListaMarcaWhereModelo($IdMarca)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM tipo WHERE idmarca = '$IdMarca'");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
        }
    }