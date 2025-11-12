<?php
    require_once 'conexion.modelo.php';
    class SolicitudesModelos
    {
        public static function ListaSolicitudesModelo()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT codsolicitud,estado FROM solicitud WHERE estado='SOLICITADO'");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt = null;
        }
    }
    class BuscarSolicitudModelos
    {
        public static function SolicitudModelo($IdSol)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM (solicitud s INNER JOIN propvehiculo pv ON s.idpropvehiculo=pv.idpropvehiculo)
            INNER JOIN propietario p on pv.Idpropietario=p.idpropietaro
            INNER JOIN vehiculo v on pv.idvehiculo=v.idvehiculo
            INNER JOIN marca m on v.marca=m.idmarca
            INNER JOIN tipo t ON v.tipo = t.idtipo
            WHERE s.codsolicitud = :IdSol");
            $stmt->bindParam(":IdSol", $IdSol, PDO::PARAM_INT);
            $stmt -> execute();
            return $stmt -> fetch();
            $stmt = null;
        }
    }
    class PRTrabajoModelos
    {
        public static function PRTrartrabajoModelo($regtrabajo)
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
        public static function PersonalSolicitudModelo()
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
        public static function CambiarEstadoCilindro($seriecilindro)
        {
            $stmt = Conexion::Conectar()->prepare("UPDATE `cilindro` SET `estado`='0' WHERE seriecilindro=:seriecilindro");
            $stmt->bindParam(":seriecilindro", $seriecilindro, PDO::PARAM_STR);
            return ($stmt -> execute()) ? 'exitoso' : 'error';
        }
    }
    class CambiarestadoKitModelos
    {
        public static function CambiarEstadoKit($seriekit)
        {
            $stmt = Conexion::Conectar()->prepare("UPDATE `kit` SET `estado`='0' WHERE seriekit=:seriekit");
            $stmt->bindParam(":seriekit", $seriekit, PDO::PARAM_STR);
            return ($stmt -> execute()) ? 'exitoso' : 'error';
        }
    }
