<?php
    require_once 'conexion.modelo.php';

    class ContratodModelos
    {
        public function InsertarSolicitudModelo($IdPropVehiculo, $IdPersonal,$Datoscontrato)
        {
            $stmt = Conexion::Conectar()->prepare("INSERT INTO `contrato`(`fechac`, `finicio`, `ffinal`, `estado`, `idpropvehiculo`, `kitp`, `cilindrop`, `idusuario`, `tecnico`, `marcak`, `marcac`) 
            VALUES (:fechac,:finicio,:ffinal,:estado,'$IdPropVehiculo',:kitp,:cilindrop,'$IdPersonal',:tecnico,:marcak,:marcac)");
            $stmt -> bindParam(":fechac", $datoscontrato['fechac'], PDO::PARAM_STR);
            $stmt -> bindParam(":finicio", $datoscontrato['finicio'], PDO::PARAM_STR);
            $stmt -> bindParam(":ffinal", $datoscontrato['ffinal'], PDO::PARAM_STR);
            $stmt -> bindParam(":estado", $datoscontrato['estado'], PDO::PARAM_INT); 
            $stmt -> bindParam(":kitp", $datoscontrato['kitp'], PDO::PARAM_STR);
            $stmt -> bindParam(":cilindrop", $datoscontrato['cilindrop'], PDO::PARAM_STR);
            $stmt -> bindParam(":tecnico", $datoscontrato['tecnico'], PDO::PARAM_INT);
            $stmt -> bindParam(":marcak", $datoscontrato['marcak'], PDO::PARAM_STR);
            $stmt -> bindParam(":marcac", $datoscontrato['marcac'], PDO::PARAM_STR);
            
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
