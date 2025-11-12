<?php
    require_once 'conexion.modelo.php';
    class ClienteModelos
    {
        public static function ListaClienteWhereModelo($IdPropietario)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM propietario WHERE ci = :IdPropietario");
            $stmt -> bindParam(":IdPropietario", $IdPropietario, PDO::PARAM_STR);
            $stmt -> execute();
            return $stmt -> fetch();
        }

        public static function BuscarClienteModelo($busquedaci)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM propietario WHERE ci = :busquedaci");
            $stmt -> bindParam(":busquedaci", $busquedaci, PDO::PARAM_STR);
            $stmt -> execute();
            return $stmt -> fetch();
        }

        public static function InsertarClienteModelo($DatosModelo)
        {
            $stmt = Conexion::Conectar()->prepare("INSERT INTO `propietario`(`ci`, `nombre`, `paterno`, `materno`, `telefono`, `estado`)
             VALUES (:ci, :nombre, :paterno, :materno, :telefono, :estado)");
            $stmt -> bindParam(":ci", $DatosModelo['ci'], PDO::PARAM_STR);
            $stmt -> bindParam(":nombre", $DatosModelo['nombre'], PDO::PARAM_STR);
            $stmt -> bindParam(":paterno", $DatosModelo['paterno'], PDO::PARAM_STR);
            $stmt -> bindParam(":materno", $DatosModelo['materno'], PDO::PARAM_STR);
            $stmt -> bindParam(":telefono", $DatosModelo['telefono'], PDO::PARAM_STR);
            $stmt -> bindParam(":estado", $DatosModelo['estado'], PDO::PARAM_STR);
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
    