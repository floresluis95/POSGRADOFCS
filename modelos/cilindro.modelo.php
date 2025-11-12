<?php
    require_once 'conexion.modelo.php';
    class MarcaCilindroModelos
    {
        public static function ListaMarcaCilindroModelo()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM `marcacilindro`");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt = null;
        }
        public static function InsertarMarcaCilindroModelo($nmkit)
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
        public static function RegistrarNotaEntregaModelo($NotaEntregac, $IdPersonal)
        {
            $stmt = Conexion::Conectar()->prepare("INSERT INTO `recepcioncilindro` (`notadeentrega`, `idusuario`)
             VALUES (:NotaEntregac, :IdPersonal )");
            $stmt->bindParam(":NotaEntregac", $NotaEntregac, PDO::PARAM_STR);
            $stmt->bindParam(":IdPersonal", $IdPersonal, PDO::PARAM_INT);

            return ($stmt -> execute()) ? 'exitoso' : 'error';
        }


    }
    class ListaNotacModelos
    {
        public static function ListaNotacModelo()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM `recepcioncilindro`");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt = null;
        }
        public static function ListaNotaModelo()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT rk.codrecepcion,rk.fecharecepcion,rk.notadeentrega, p.Nombres,p.ApellidoPaterno,p.ApellidoMaterno,u.tipo FROM personal p
            INNER JOIN usuario u ON p.IdPersonal=u.IdPersonal INNER JOIN recepcionkitkit rk ON
            u.IdPersonal=rk.idusuario ");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt = null;
        }
    }
    class RegistrarCilModelos
    {
        public static function RegistrarCilModelo($nmCilI)
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
        public static function pdfcilModelo($idcil)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM cilindro c, marcacilindro mc ,recepcioncilindro rc
            WHERE c.codrecpecioncil=rc.codrecepcioncil and c.codmarcacil=mc.codmarcacil and rc.codrecepcioncil=:idcil");
            $stmt->bindParam(":idcil", $idcil, PDO::PARAM_INT);
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt = null;
        }


    }
    class ListacilModelos
    {
        public static function ListacilAsignarModelo()
        {

                $stmt = Conexion::Conectar()->prepare("SELECT * FROM `cilindro` WHERE cilindro.estado=1");
                $stmt -> execute();
                return $stmt -> fetchAll();
                $stmt = null;

        }
    }
    class buscarcilindroModelos
    {
        public static function buscarcilindromodelo($seriecilindro)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT `seriecilindro`, `codmarcacil`, `capacidad`, `aofab`, `estado`, `codrecpecioncil`
             FROM `cilindro` WHERE seriecilindro=:seriecilindro");
            $stmt->bindParam(":seriecilindro", $seriecilindro, PDO::PARAM_STR);
            $stmt -> execute();
            return $stmt -> fetch();
            $stmt = null;
        }
    }
    

//contar cantidad segun id
//SELECT   K.codrecpecion ,COUNT(k.codrecpecion) AS CANTIDAD FROM recepcionkitkit rk INNER JOIN kit k on rk.codrecepcion=k.codrecpecion WHERE rk.codrecepcion='1' GROUP by (k.codrecpecion) 