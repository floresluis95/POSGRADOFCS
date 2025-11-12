<?php
    require_once 'conexion.modelo.php';
    class MarcaCilindroModelos
    {
        public function ListaMarcaCilindroModelo()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM `marcacilindro`");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
        public function InsertarMarcaCilindroModelo($nmkit)
        {
            $stmt = Conexion::Conectar()->prepare("INSERT INTO `marcacilindro`(`descripcioncil`, `estado`)
             VALUES (:descripcioncil,:estado)");
            
            $stmt -> bindParam(":descripcioncil", $nmkit['descripcioncil'], PDO::PARAM_STR);
            $stmt -> bindParam(":estado", $nmkit['estado'], PDO::PARAM_INT);
            if ($stmt -> execute())
            {
                return 'exitoso';
                
            }
            else 
            {
                return 'error';
            }
        }
        public function RegistrarNotaEntregaModelo($NotaEntregac, $IdPersonal)
        {
            $stmt = Conexion::Conectar()->prepare("INSERT INTO `recepcioncilindro` (`notadeentrega`, `idusuario`)
             VALUES ('$NotaEntregac', '$IdPersonal' )");
       

            return ($stmt -> execute()) ? 'exitoso' : 'error';
        }

       
    }
    class ListaNotacModelos
    {
        public function ListaNotacModelo()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM `recepcioncilindro`");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
        public function ListaNotaModelo()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT rk.codrecepcion,rk.fecharecepcion,rk.notadeentrega, p.Nombres,p.ApellidoPaterno,p.ApellidoMaterno,u.tipo FROM personal p
            INNER JOIN usuario u ON p.IdPersonal=u.IdPersonal INNER JOIN recepcionkitkit rk ON
            u.IdPersonal=rk.idusuario ");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
    }
    class RegistrarCilModelos
    {
        public function RegistrarCilModelo($nmCilI)
        {
            $stmt = Conexion::Conectar()->prepare("INSERT INTO `cilindro`(`seriecilindro`, `codmarcacil`, `capacidad`, `aofab`, `codrecpecioncil`) 
            VALUES (:seriecilindro, :codmarcacil, :capacidad, :aofab, :codrecpecioncil)");
            $stmt -> bindParam(":seriecilindro", $nmCilI['seriecilindro'], PDO::PARAM_STR);
            $stmt -> bindParam(":codmarcacil", $nmCilI['codmarcacil'], PDO::PARAM_INT);
            $stmt -> bindParam(":capacidad", $nmCilI['capacidad'], PDO::PARAM_INT);  
            $stmt -> bindParam(":codrecpecioncil", $nmCilI['codrecpecioncil'], PDO::PARAM_INT);
            $stmt -> bindParam(":aofab", $nmCilI['aofab'], PDO::PARAM_STR);
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
    class pdfcilModelos
    {
        public function pdfcilModelo($idcil)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM cilindro c, marcacilindro mc ,recepcioncilindro rc 
            WHERE c.codrecpecioncil=rc.codrecepcioncil and c.codmarcacil=mc.codmarcacil and rc.codrecepcioncil='$idcil'");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
      

    }
    class ListacilModelos
    {
        public function ListacilAsignarModelo()
        {
            
                $stmt = Conexion::Conectar()->prepare("SELECT * FROM `cilindro` WHERE cilindro.estado=1");
                $stmt -> execute();
                return $stmt -> fetchAll();
                $stmt -> close();
                $stmt = null;
        
        }
    }
    class buscarcilindroModelos
    {
        public function buscarcilindromodelo($seriecilindro)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT `seriecilindro`, `codmarcacil`, `capacidad`, `aofab`, `estado`, `codrecpecioncil`
             FROM `cilindro` WHERE seriecilindro='8823047'");
            $stmt -> execute();
            return $stmt -> fetch();
            $stmt -> close();
            $stmt = null;
        }
    }
    

//contar cantidad segun id
//SELECT   K.codrecpecion ,COUNT(k.codrecpecion) AS CANTIDAD FROM recepcionkitkit rk INNER JOIN kit k on rk.codrecepcion=k.codrecpecion WHERE rk.codrecepcion='1' GROUP by (k.codrecpecion) 