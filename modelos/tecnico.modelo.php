<?php
    require_once 'conexion.modelo.php';
    class TecnicoModelos
    {
        public function ListaTecnicoModelo()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM personal p INNER JOIN usuario u on p.IdPersonal= u.IdPersonal 
            WHERE u.Tipo='TEC' and u.Estado='1'");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }

    }