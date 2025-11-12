<?php
    require_once 'conexion.modelo.php';
    class PropvehiculoModelos
    {
        public function InsertarPropVehiculoModelo($Datospropvehiculo)
        {
            $stmt = Conexion::Conectar()->prepare("INSERT INTO `propvehiculo`(`Idpropietario`, `idvehiculo`, `estado`) 
            VALUES (:Idpropietario,:idvehiculo,:estado)");
            $stmt -> bindParam(":Idpropietario", $Datospropvehiculo['Idpropietario'], PDO::PARAM_INT);
            $stmt -> bindParam(":idvehiculo", $Datospropvehiculo['idvehiculo'], PDO::PARAM_INT);
            $stmt -> bindParam(":estado", $Datospropvehiculo['estado'], PDO::PARAM_INT);
            
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
  
    class SolicitudModelos
    {
        public function InsertarSolicitudModelo($IdPropVehiculo, $IdPersonal)
        {
            $stmt = Conexion::Conectar()->prepare("INSERT INTO solicitud (idpropvehiculo, idusuario)
             VALUES ('$IdPropVehiculo', '$IdPersonal')");   
            return ($stmt -> execute()) ? 'exitoso' : 'error';
        }
        public function CambiarEstadoSolicitudModelo($Id)
        {
            $stmt = Conexion::Conectar()->prepare("UPDATE `solicitud` SET estado='PROGRAMADO' WHERE codsolicitud=$Id");
            
            return ($stmt -> execute()) ? 'exitoso' : 'error';
            
            
        }
        public function CambiarEstadoSolicitudTModelo($asignacion)
        {
            $stmt = Conexion::Conectar()->prepare("UPDATE `solicitud` SET estado='TERMINADO' WHERE codsolicitud=$asignacion");
            
            return ($stmt -> execute()) ? 'exitoso' : 'error'; 
        }
        public function CambiarFechaTModelo($asignacion , $fecha)
        {
            $stmt = Conexion::Conectar()->prepare("UPDATE `solicitud` SET `fechaconcluido`='$fecha' WHERE solicitud.codsolicitud=$asignacion");
            
            return ($stmt -> execute()) ? 'exitoso' : 'error'; 
        }
    }
    class pdfsolicitudModelos
    {
        public function SolicitudModelo($IdSol)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM (solicitud s INNER JOIN propvehiculo pv ON s.idpropvehiculo=pv.idpropvehiculo)
            INNER JOIN propietario p on pv.Idpropietario=p.idpropietaro
            INNER JOIN vehiculo v on pv.idvehiculo=v.idvehiculo
            INNER JOIN marca m on v.marca=m.idmarca
            INNER JOIN tipo t ON v.tipo = t.idtipo
            WHERE s.codsolicitud = '$IdSol'");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
    }
    class BuscarSolicitudwhereModelos
    {
        public function buscarSolicitudModelo($IdSol)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT s.codsolicitud, p.ci, p.nombre, p.paterno, p.materno, v.nroplaca, mv.descmarca, v.tipomotor,s.fechasolicitud FROM solicitud s 
            INNER JOIN propvehiculo pv on s.idpropvehiculo = pv.idpropvehiculo
            INNER JOIN propietario p on pv.Idpropietario=p.idpropietaro
            INNER JOIN vehiculo v on pv.idvehiculo=v.idvehiculo
            INNER JOIN marca mv on mv.idmarca=v.marca  and s.codsolicitud=$IdSol");
            $stmt -> execute();
            return $stmt -> fetch();
            $stmt -> close();
            $stmt = null;
        }
    }