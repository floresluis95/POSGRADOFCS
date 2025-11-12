<?php
    require_once 'conexion.modelo.php';
    class VehiculoModelos
    {
        public static function BuscarVehiculoModelo($busplaca)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM `vehiculo` WHERE nroplaca = :busplaca");
            $stmt -> bindParam(":busplaca", $busplaca, PDO::PARAM_STR);
            $stmt -> execute();
            return $stmt -> fetch();
        }

        public static function busv($nroplaca)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT `idvehiculo`, `nroplaca`, `marca`, `tipo`, `clase`, `modelo`,
            `tipomotor`, `cilindrada`, `tipotransporte`, `estado` FROM `vehiculo` WHERE nroplaca=:nroplaca");
            $stmt -> bindParam(":nroplaca", $nroplaca, PDO::PARAM_STR);
            $stmt -> execute();
            return $stmt -> fetch();
        }
        
        public static function InsertarVehiculoModelo($DatosVehiculo)
        {
            $stmt = Conexion::Conectar()->prepare("INSERT INTO `vehiculo`(`nroplaca`, `marca`, `tipo`, `clase`, `modelo`, `tipomotor`, `cilindrada`, `tipotransporte`) 
                                    VALUES (:nroplaca, :marca, :tipo, :clase, :modelo, :tipomotor, :cilindrada, :tipotransporte)");
            $stmt -> bindParam(":nroplaca", $DatosVehiculo['nroplaca'], PDO::PARAM_STR);
            $stmt -> bindParam(":marca", $DatosVehiculo['marca'], PDO::PARAM_STR);
            $stmt -> bindParam(":tipo", $DatosVehiculo['tipo'], PDO::PARAM_STR);
            $stmt -> bindParam(":clase", $DatosVehiculo['clase'], PDO::PARAM_STR);
            $stmt -> bindParam(":modelo", $DatosVehiculo['modelo'], PDO::PARAM_STR);
            $stmt -> bindParam(":tipomotor", $DatosVehiculo['tipomotor'], PDO::PARAM_STR);
            $stmt -> bindParam(":cilindrada", $DatosVehiculo['cilindrada'], PDO::PARAM_INT);
            $stmt -> bindParam(":tipotransporte", $DatosVehiculo['tipotransporte'], PDO::PARAM_STR);
            
            if ($stmt -> execute())
            {
                return 'exitoso';
            }
            else 
            {
                return 'error';
            }
        }

        public static function InsertarMarcaModelo($nmmarca)
        {
            $stmt = Conexion::Conectar()->prepare("INSERT INTO `marca`(`descmarca`)
             VALUES (:descmarca)");
            
            $stmt -> bindParam(":descmarca", $nmmarca['descmarca'], PDO::PARAM_STR);
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