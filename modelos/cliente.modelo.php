<?php
    require_once 'conexion.modelo.php';
    class ClienteModelos
    {
        public function ListaClienteWhereModelo($IdPropietario)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM propietario WHERE ci = '$IdPropietario'");
            $stmt -> execute();
            return $stmt -> fetch();
            $stmt -> close();
            $stmt = null;
        }
        public function BuscarClienteModelo($busquedaci)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM propietario WHERE ci = '$busquedaci'");
            $stmt -> execute();
            return $stmt -> fetch();
            if ($stmt -> execute())
            {
                return 'exitoso';
            }
            else 
            {
                return 'error';
            }
            $stmt -> close();
            $stmt = null;
        }
        public function InsertarClienteModelo($DatosModelo)
        {
            $stmt = Conexion::Conectar()->prepare("INSERT INTO `propietario`(`ci`, `nombre`, `paterno`, `materno`, `telefono`, `estado`)
             VALUES (:ci,:nombre, :paterno, :materno,:telefono, :estado)");
            $stmt -> bindParam(":ci", $DatosModelo['ci'], PDO::PARAM_STR);
            $stmt -> bindParam(":nombre", $DatosModelo['nombre'], PDO::PARAM_STR);
            $stmt -> bindParam(":paterno", $DatosModelo['paterno'], PDO::PARAM_INT);
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
    