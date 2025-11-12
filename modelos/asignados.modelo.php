<?php
    require_once 'conexion.modelo.php';
    class AsignadosModelos
    {
        public static function ListadeAsignacionesModelo()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT s.codsolicitud, s.fechasolicitud, v.nroplaca, ds.seriekit, ds.seriecilindro, s.estado, ds.fechatrabajo, p.Nombres,p.ApellidoPaterno, u.Tipo
            FROM usuario u INNER JOIN dsolicitud ds on u.IdPersonal=ds.idtecnico
            INNER JOIN solicitud s on s.codsolicitud=ds.codsolicitud
            INNER JOIN personal p on p.IdPersonal=u.IdPersonal
            INNER JOIN propvehiculo pr on pr.idpropvehiculo=s.idpropvehiculo
            INNER JOIN vehiculo v on v.idvehiculo=pr.idvehiculo WHERE s.estado='PROGRAMADO' GROUP by (s.codsolicitud)");
            $stmt -> execute();
            return $stmt -> fetchAll();
        }

        public static function ListadeAsignacionesTecModelo($id)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT s.codsolicitud, s.fechasolicitud, v.nroplaca, ds.seriekit, ds.seriecilindro, s.estado, ds.fechatrabajo, p.Nombres,p.ApellidoPaterno, u.Tipo
            FROM usuario u INNER JOIN dsolicitud ds on u.IdPersonal=ds.idtecnico
            INNER JOIN solicitud s on s.codsolicitud=ds.codsolicitud
            INNER JOIN personal p on p.IdPersonal=u.IdPersonal
            INNER JOIN propvehiculo pr on pr.idpropvehiculo=s.idpropvehiculo
            INNER JOIN vehiculo v on v.idvehiculo=pr.idvehiculo WHERE s.estado='PROGRAMADO' AND u.IdPersonal=:id GROUP by (s.codsolicitud)");
            $stmt -> bindParam(":id", $id, PDO::PARAM_INT);
            $stmt -> execute();
            return $stmt -> fetchAll();
        }

        public static function Cambiartecnico($idtecnico, $idsolicitud)
        {
            $stmt = Conexion::Conectar()->prepare("UPDATE `dsolicitud` SET `idtecnico`=:idtecnico WHERE dsolicitud.codsolicitud=:idsolicitud");
            $stmt -> bindParam(":idtecnico", $idtecnico, PDO::PARAM_INT);
            $stmt -> bindParam(":idsolicitud", $idsolicitud, PDO::PARAM_INT);

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
    class GuardarDetalleSolicitudModelos
    {
        public static function GuardardetalleModelo($Datosdet)
        {
            $stmt = Conexion::Conectar()->prepare("INSERT INTO `detalle`(`dsolicitudt`, `inyecctores`, `arranque`, `aceleracion`, `velocidad`, `elctrica`, `descripciond`)
             VALUES (:dsolicitudt, :inyecctores, :arranque, :aceleracion, :velocidad, :elctrica, :descripciond)");
            $stmt -> bindParam(":dsolicitudt", $Datosdet['dsolicitudt'], PDO::PARAM_INT);
            $stmt -> bindParam(":inyecctores", $Datosdet['inyecctores'], PDO::PARAM_STR);
            $stmt -> bindParam(":arranque", $Datosdet['arranque'], PDO::PARAM_STR);
            $stmt -> bindParam(":aceleracion", $Datosdet['aceleracion'], PDO::PARAM_STR);
            $stmt -> bindParam(":velocidad", $Datosdet['velocidad'], PDO::PARAM_STR);
            $stmt -> bindParam(":elctrica", $Datosdet['elctrica'], PDO::PARAM_STR);
            $stmt -> bindParam(":descripciond", $Datosdet['descripciond'], PDO::PARAM_STR);
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
   
    