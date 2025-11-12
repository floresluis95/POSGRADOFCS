<?php
    require_once 'conexion.modelo.php';
    class HeredadoModelos
    {
        public function UltimoIdModelo($item, $tabla)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT MAX($item) AS UltimoId FROM $tabla");
            $stmt -> setFetchMode(PDO::FETCH_ASSOC);
            $stmt -> execute();
            while ($row = $stmt -> fetch())
            {
                return $UltimoId = $row['UltimoId'];
            }
        }

        public function TraerCampoModelo($item, $tabla, $id, $value)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT $item AS item FROM $tabla WHERE $id = '$value'");
            $stmt -> setFetchMode(PDO::FETCH_ASSOC);
            $stmt -> execute();
            while ($row = $stmt -> fetch())
            {
                return $row['item'];
            }
        }
    }