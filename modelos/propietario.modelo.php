<?php
    require_once 'conexion.modelo.php';
    class PropietarioModelos
    {
        public function BuscarPropietarioModelo($busplaca)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM `vehiculo` WHERE nroplaca = $busplaca");
            $stmt -> execute();
            return $stmt -> fetch();
            $stmt -> close();
            $stmt = null;
        }
        public function RegistarPropietarioModelo($DatosPropietario)
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
        public function ListaPropietarioModelo()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT `idpropietaro`, `ci`, `nombre`, `paterno`, `materno`, `telefono`, `estado` FROM `propietario`");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
        public function buscarcliente($ci)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT `idpropietaro`, `ci`, `nombre`, `paterno`, `materno`, `telefono`, `estado` FROM `propietario` WHERE ci='$ci'");
            $stmt -> execute();
            return $stmt -> fetch();
            $stmt -> close();
            $stmt = null;
        }

    }