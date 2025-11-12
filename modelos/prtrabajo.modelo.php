<?php
    require_once 'conexion.modelo.php';
    class SolicitudesModelos
    {
        public function ListaSolicitudesModelo()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT codsolicitud,estado FROM solicitud WHERE estado='SOLICITADO'");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
    }
    class BuscarSolicitudModelos
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
            return $stmt -> fetch();
            $stmt -> close();
            $stmt = null;
        }
    }
    class PRTrabajoModelos
    {
        public function PRTrartrabajoModelo($regtrabajo)
        {
            $stmt = Conexion::Conectar()->prepare("INSERT INTO dsolicitud(`codsolicitud`, `seriekit`, `seriecilindro`, `idtecnico`,`fechatrabajo`) 
            VALUES (:codsolicitud, :seriekit, :seriecilindro, :idtecnico, :fechatrabajo)");
            $stmt -> bindParam(":codsolicitud", $regtrabajo['codsolicitud'], PDO::PARAM_INT);
            $stmt -> bindParam(":seriekit", $regtrabajo['seriekit'], PDO::PARAM_STR);
            $stmt -> bindParam(":seriecilindro", $regtrabajo['seriecilindro'], PDO::PARAM_STR);
            $stmt -> bindParam(":idtecnico", $regtrabajo['idtecnico'], PDO::PARAM_INT);
            $stmt -> bindParam(":fechatrabajo", $regtrabajo['fechatrabajo'], PDO::PARAM_STR);
            if ($stmt -> execute())
            {
                return 'exitoso';
                
            }
            else 
            {
                return 'error';
            }
        }
        public function PersonalSolicitudModelo()
        {
            $stmt = Conexion::Conectar()->prepare("INSERT INTO soltecnico(`codsolicitud`, `idusuario`) 
            VALUES (:codsolicitud,:idusuario)");
            $stmt -> bindParam(":codsolicitud", $regtrabajo['codsolicitud'], PDO::PARAM_INT);
            $stmt -> bindParam(":idusuario", $regtrabajo['idusuario'], PDO::PARAM_INT);
 
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
    class CambiarestadoCilindroModelos
    {
        public function CambiarEstadoCilindro($seriecilindro)
        {
            $stmt = Conexion::Conectar()->prepare("UPDATE `cilindro` SET `estado`='0' WHERE seriecilindro='$seriecilindro'");
            
            return ($stmt -> execute()) ? 'exitoso' : 'error'; 
        }
    }
    class CambiarestadoKitModelos
    {
        public function CambiarEstadoKit($seriekit)
        {
            $stmt = Conexion::Conectar()->prepare("UPDATE `kit` SET `estado`='0' WHERE seriekit='$seriekit'");
            
            return ($stmt -> execute()) ? 'exitoso' : 'error'; 
        }
    }
