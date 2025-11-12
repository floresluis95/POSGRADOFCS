<?php
    require_once 'conexion.modelo.php';
    class ConcluidosModelos
    {
        public static function ListaTrabajosConcluidos()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT s.codsolicitud, s.fechasolicitud, v.nroplaca, ds.seriekit, ds.seriecilindro, s.estado, ds.fechatrabajo, p.Nombres,p.ApellidoPaterno, u.Tipo, s.fechaconcluido FROM usuario u INNER JOIN dsolicitud ds on u.IdPersonal=ds.idtecnico INNER JOIN solicitud s on s.codsolicitud=ds.codsolicitud INNER JOIN personal p on p.IdPersonal=u.IdPersonal INNER JOIN propvehiculo pr on pr.idpropvehiculo=s.idpropvehiculo INNER JOIN vehiculo v on v.idvehiculo=pr.idvehiculo WHERE s.estado='TERMINADO' GROUP by (s.codsolicitud)");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt = null;
        }
    }
    class pdfconcluidoModelos
    {
        public static function concluidoModelo($codigo)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM (solicitud s INNER JOIN dsolicitud ds ON s.codsolicitud=ds.codsolicitud)
            inner JOIN detalle de on de.dsolicitudt=s.codsolicitud
            INNER JOIN propvehiculo pr on pr.idpropvehiculo =s.idpropvehiculo
            inner JOIN propietario pro on pro.idpropietaro=pr.Idpropietario
            INNER JOIN vehiculo v on v.idvehiculo=pr.idvehiculo
            INNER JOIN marca ma on v.marca=ma.idmarca
            INNER JOIN tipo t on t.idmarca=ma.idmarca
            INNER JOIN usuario u on s.idusuario
            INNER JOIN kit k on ds.seriekit=k.seriekit
            INNER JOIN marcakit mk on mk.codmarca=k.codmarca
            INNER JOIN cilindro cil on ds.seriecilindro=cil.seriecilindro
            INNER JOIN marcacilindro mc on cil.codmarcacil= mc.codmarcacil
            INNER JOIN recepcionkitkit rek on rek.codrecepcion=k.codrecpecion
            INNER JOIN recepcioncilindro rc on rc.codrecepcioncil=cil.codrecpecioncil
            INNER JOIN personal p on p.IdPersonal= ds.idtecnico
            WHERE s.codsolicitud=:codigo GROUP by s.codsolicitud");
            $stmt->bindParam(":codigo", $codigo, PDO::PARAM_INT);
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt = null;
        }
    }
