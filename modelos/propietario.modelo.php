<?php
    require_once 'conexion.modelo.php';
    class PropietarioModelos
    {
        public static function BuscarPropietarioModelo($busplaca)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM `vehiculo` WHERE nroplaca = :busplaca");
            $stmt -> bindParam(":busplaca", $busplaca, PDO::PARAM_STR);
            $stmt -> execute();
            return $stmt -> fetch();
        }
        public static function RegistarPropietarioModelo($DatosPropietario)
        {
            $stmt = Conexion::Conectar()->prepare("INSERT INTO `propietario`(`ci`, `nombre`, `paterno`, `materno`, `telefono`, `estado`) 
            VALUES (:ci, :nombre, :paterno, :materno, :telefono, :estado)");
            $stmt -> bindParam(":ci", $DatosPropietario['ci'], PDO::PARAM_STR);
            $stmt -> bindParam(":nombre", $DatosPropietario['nombre'], PDO::PARAM_STR);
            $stmt -> bindParam(":paterno", $DatosPropietario['paterno'], PDO::PARAM_STR);
            $stmt -> bindParam(":materno", $DatosPropietario['materno'], PDO::PARAM_STR);
            $stmt -> bindParam(":telefono", $DatosPropietario['telefono'], PDO::PARAM_STR);
            $stmt -> bindParam(":estado", $DatosPropietario['estado'], PDO::PARAM_STR);
            if ($stmt -> execute())
            {
                return 'exitoso';
            }
            else 
            {
                return 'error';
            }
        }
        public static function ListaPropietarioModelo()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT `idpropietaro`, `ci`, `nombre`, `paterno`, `materno`, `telefono`, `estado` FROM `propietario`");
            $stmt -> execute();
            return $stmt -> fetchAll();
        }

        public static function buscarcliente($ci)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT `idpropietaro`, `ci`, `nombre`, `paterno`, `materno`, `telefono`, `estado` FROM `propietario` WHERE ci=:ci");
            $stmt -> bindParam(":ci", $ci, PDO::PARAM_STR);
            $stmt -> execute();
            return $stmt -> fetch();
        }

    }