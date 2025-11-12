<?php
    require_once 'conexion.modelo.php';
    class MarcaModelos
    {
        public function ListaMarcaModelo()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM `marca`");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
        public function BuscarTipoMarca($idmarca)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * from marca m , tipo t WHERE m.idmarca=t.idmarca and t.idmarca=$idmarca");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
        public function GuartadarTipoMarca($idmarca)
        {
            $stmt = Conexion::Conectar()->prepare("INSERT INTO `tipo`(`desctipo`, `idmarca`) 
            VALUES (:desctipo,:idmarca)");
            $stmt -> bindParam(":desctipo", $idmarca['desctipo'], PDO::PARAM_STR);
            $stmt -> bindParam(":idmarca", $idmarca['idmarca'], PDO::PARAM_INT);
            if ($stmt -> execute())
            {
                return 'exitoso';
                
            }
            else 
            {
                return 'error';
            }
        
        }
       




    }