<?php
    require_once 'conexion.modelo.php';

    class ContratodModelos
    {
        public static function InsertarSolicitudModelo($IdPropVehiculo, $IdPersonal,$Datoscontrato)
        {
            $stmt = Conexion::Conectar()->prepare("INSERT INTO `contrato`(`fechac`, `finicio`, `ffinal`, `estado`, `idpropvehiculo`, `kitp`, `cilindrop`, `idusuario`, `tecnico`, `marcak`, `marcac`)
            VALUES (:fechac,:finicio,:ffinal,:estado,:IdPropVehiculo,:kitp,:cilindrop,:IdPersonal,:tecnico,:marcak,:marcac)");
            $stmt -> bindParam(":fechac", $Datoscontrato['fechac'], PDO::PARAM_STR);
            $stmt -> bindParam(":finicio", $Datoscontrato['finicio'], PDO::PARAM_STR);
            $stmt -> bindParam(":ffinal", $Datoscontrato['ffinal'], PDO::PARAM_STR);
            $stmt -> bindParam(":estado", $Datoscontrato['estado'], PDO::PARAM_INT);
            $stmt -> bindParam(":IdPropVehiculo", $IdPropVehiculo, PDO::PARAM_INT);
            $stmt -> bindParam(":kitp", $Datoscontrato['kitp'], PDO::PARAM_STR);
            $stmt -> bindParam(":cilindrop", $Datoscontrato['cilindrop'], PDO::PARAM_STR);
            $stmt -> bindParam(":IdPersonal", $IdPersonal, PDO::PARAM_INT);
            $stmt -> bindParam(":tecnico", $Datoscontrato['tecnico'], PDO::PARAM_INT);
            $stmt -> bindParam(":marcak", $Datoscontrato['marcak'], PDO::PARAM_STR);
            $stmt -> bindParam(":marcac", $Datoscontrato['marcac'], PDO::PARAM_STR);

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
