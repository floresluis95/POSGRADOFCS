<?php
    require_once 'conexion.modelo.php';
    //REPORTES DE TRABAJOS
    class trabajosConcluidosModelos
    {
        public function pdfTrabajosConcluidos($inicio,$final)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM solicitud s INNER JOIN propvehiculo pv on s.idpropvehiculo =pv.idpropvehiculo
            INNER JOIN propietario p on pv.Idpropietario=p.idpropietaro
            INNER JOIN vehiculo v on pv.idpropvehiculo=v.idvehiculo
            INNER JOIN dsolicitud ds on ds.codsolicitud=s.codsolicitud
            WHERE s.estado='TERMINADO' AND s.fechaconcluido BETWEEN '$inicio' and '$final'");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
        public function tipotransporte($inicio,$final)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT COUNT(v.tipotransporte) as cantidad, v.tipotransporte FROM solicitud s INNER JOIN dsolicitud ds on s.codsolicitud=ds.codsolicitud
            inner JOIN propvehiculo pr on pr.idpropvehiculo=s.idpropvehiculo
            INNER JOIN vehiculo v on v.idvehiculo=pr.idvehiculo where s.estado='TERMINADO' AND s.fechaconcluido BETWEEN '$inicio' and '$final'
            GROUP BY v.tipotransporte");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
        public function motor($inicio,$final)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT COUNT(v.tipomotor) as cantidad,v.tipomotor FROM solicitud s INNER JOIN dsolicitud ds on s.codsolicitud=ds.codsolicitud
            inner JOIN propvehiculo pr on pr.idpropvehiculo=s.idpropvehiculo
            INNER JOIN vehiculo v on v.idvehiculo=pr.idvehiculo where s.estado='TERMINADO' AND s.fechaconcluido BETWEEN '$inicio' and '$final'
            GROUP BY v.tipomotor");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
        public function TrabajosConcluidosModelo($inicio,$final)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT s.codsolicitud, s.fechasolicitud, s.estado, p.nombre,p.paterno,v.nroplaca, s.fechaconcluido, ds.fechatrabajo FROM solicitud s INNER JOIN propvehiculo pv on s.idpropvehiculo =pv.idpropvehiculo
            INNER JOIN propietario p on pv.Idpropietario=p.idpropietaro
            INNER JOIN vehiculo v on pv.idpropvehiculo=v.idvehiculo
            INNER JOIN dsolicitud ds on ds.codsolicitud=s.codsolicitud
            WHERE s.estado='TERMINADO' AND s.fechaconcluido BETWEEN '$inicio' and '$final'");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
        public function Trabajossolicitados($inicio,$final)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT s.codsolicitud, s.fechasolicitud, s.estado, p.nombre,p.paterno,v.nroplaca FROM solicitud s INNER JOIN propvehiculo pv on s.idpropvehiculo =pv.idpropvehiculo
            INNER JOIN propietario p on pv.Idpropietario=p.idpropietaro
            INNER JOIN vehiculo v on pv.idpropvehiculo=v.idvehiculo
            WHERE s.estado='SOLICITADO' AND s.fechasolicitud BETWEEN '$inicio' and '$final'");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
        public function trabajosprogramados($inicio,$final)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT s.codsolicitud, s.fechasolicitud, s.estado, p.nombre,p.paterno,v.nroplaca, s.fechaconcluido, ds.fechatrabajo FROM solicitud s INNER JOIN propvehiculo pv on s.idpropvehiculo =pv.idpropvehiculo
            INNER JOIN propietario p on pv.Idpropietario=p.idpropietaro
            INNER JOIN vehiculo v on pv.idpropvehiculo=v.idvehiculo
            INNER JOIN dsolicitud ds on ds.codsolicitud=s.codsolicitud
            WHERE s.estado='PROGRAMADO' and ds.fechatrabajo BETWEEN '$inicio' and '$final'");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
      
    }
    //KITS
    class ConsultakitModelos
    {
        public function pdfKitdisponibles()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT k.seriekit, mk.descripcion, k.tipo, k.codrecpecion,k.estado,rk.notadeentrega FROM kit K INNER JOIN marcakit mk on k.codmarca=mk.codmarca
            INNER JOIN recepcionkitkit rk on k.codrecpecion = rk.codrecepcion WHERE k.estado='1' ORDER by (rk.fecharecepcion)DESC ");
           
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
        public function pdfKitdisponiblesinyeccioncarburador()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT COUNT(k.seriekit)as cantidad, k.tipo ,mk.descripcion FROM kit K INNER JOIN marcakit mk on k.codmarca=mk.codmarca
            INNER JOIN recepcionkitkit rk on k.codrecpecion = rk.codrecepcion WHERE k.estado='1' GROUP by mk.descripcion,k.tipo");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
        public function totalkit()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT COUNT(seriekit) as cantidad FROM kit WHERE estado='1'");
           
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
       

        public function ListaKitdisponibles($seriekit)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT `seriekit`, `tipo`, `estado`, `codmarca`, `codrecpecion` FROM `kit` WHERE estado='1' AND  
            seriekit='$seriekit'");
            $stmt -> execute();
            return $stmt -> fetch();
            $stmt -> close();
            $stmt = null;
        }
        public function ListaKitAsignado($seriekit)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT K.seriekit  FROM kit k INNER JOIN dsolicitud ds on ds.seriekit=k.seriekit 
            INNER JOIN solicitud s on s.codsolicitud=ds.codsolicitud and s.estado='PROGRAMADO' and k.seriekit='$seriekit'");
            $stmt -> execute();
            return $stmt -> fetch();
            $stmt -> close();
            $stmt = null;
        }
        public function ConsultakitModelo($seriekit)
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
            WHERE k.seriekit='$seriekit' GROUP by s.codsolicitud");
            $stmt -> execute();
            return $stmt -> fetch();
            $stmt -> close();
            $stmt = null;
        }
    }
    //CILINDROS
    class ConsultacilModelos
    {
        public function pdfcildisponiblesdesc()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM cilindro c INNER JOIN marcacilindro mc on c.codmarcacil=mc.codmarcacil
            INNER JOIN recepcioncilindro rc on rc.codrecepcioncil=c.codrecpecioncil
            WHERE c.estado=1 ORDER by (rc.fecharecepcioncil) desc");
           
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
        public function pdfcildisponibles()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT c.seriecilindro, mc.descripcioncil FROM cilindro c INNER JOIN marcacilindro mc on c.codmarcacil=mc.codmarcacil WHERE c.estado='1'");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
        public function pdfcildisponiblespormarca()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT COUNT(c.seriecilindro)as cantidad, c.capacidad,mc.descripcioncil FROM cilindro c INNER JOIN marcacilindro mc on c.codmarcacil=mc.codmarcacil
            INNER JOIN recepcioncilindro rc on rc.codrecepcioncil=c.codrecpecioncil WHERE c.estado='1' GROUP BY (mc.descripcioncil)");
           
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
        public function concluidoModelo($seriecilindro)
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
            WHERE cil.seriecilindro='$seriecilindro' GROUP by s.codsolicitud");
            $stmt -> execute();
            return $stmt -> fetch();
            $stmt -> close();
            $stmt = null;
        }
        public function ListaCilAsignado($seriecilindro)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * from cilindro cil INNER JOIN dsolicitud ds on ds.seriecilindro=cil.seriecilindro
            inner JOIN solicitud s on s.codsolicitud=ds.codsolicitud and s.estado='PROGRAMADO' AND cil.seriecilindro='$seriecilindro'");
            $stmt -> execute();
            return $stmt -> fetch();
            $stmt -> close();
            $stmt = null;
        }
        public function Listacildisponibles($seriecilindro)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * from cilindro WHERE estado='1' AND seriecilindro='$seriecilindro'");
            $stmt -> execute();
            return $stmt -> fetch();
            $stmt -> close();
            $stmt = null;
        }
        public function pdfCildisponiblesmarca()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT COUNT(cil.seriecilindro) as cantidad ,cil.capacidad , mc.descripcioncil FROM cilindro cil INNER JOIN marcacilindro mc on cil.codmarcacil=mc.codmarcacil
            INNER JOIN recepcioncilindro rc on cil.codrecpecioncil =rc.codrecepcioncil WHERE cil.estado='1' GROUP BY mc.codmarcacil,cil.capacidad");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
        
    }