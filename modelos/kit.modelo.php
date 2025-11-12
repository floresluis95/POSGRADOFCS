<?php
    require_once 'conexion.modelo.php';
  
    class MarcaKitModelos
    {
        public function ListaMarcaKitModelo()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM `marcakit`");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
        public function ListaDetalleKitModelo($CodRecepcion)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT k.seriekit, mk.descripcion, k.tipo, k.estado FROM kit k INNER JOIN marcakit mk on k.codmarca= mk.codmarca WHERE codrecpecion = '$CodRecepcion'");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
        
        public function InsertarMarcaKitModelo($nmkit)
        {
            $stmt = Conexion::Conectar()->prepare("INSERT INTO `marcakit`(`descripcion`, `estado`) 
            VALUES (:descripcion,:estado)");
            $stmt -> bindParam(":descripcion", $nmkit['descripcion'], PDO::PARAM_STR);
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
        public function InsertaKitModelo($nmkit)
        {
            $stmt = Conexion::Conectar()->prepare("INSERT INTO `kit`(`seriekit`, `tipo`, `estado`, `codmarca`, `codrecpecion`) 
            VALUES (:seriekit, :tipo, :estado, :codmarca, :codrecpecion)");
            $stmt -> bindParam(":seriekit", $nmkit['seriekit'], PDO::PARAM_STR);
            $stmt -> bindParam(":tipo", $nmkit['tipo'], PDO::PARAM_STR);
            $stmt -> bindParam(":estado", $nmkit['estado'], PDO::PARAM_STR);
            $stmt -> bindParam(":codmarca", $nmkit['codmarca'], PDO::PARAM_INT);
            $stmt -> bindParam(":codrecpecion", $nmkit['codrecpecion'], PDO::PARAM_INT);
            if ($stmt -> execute())
            {
                return 'exitoso';
                
            }
            else 
            {
                return 'error';
            }
        }
      

        public function RegistrarnotakitModelo($nmkit)
        {
            $stmt = Conexion::Conectar()->prepare("INSERT INTO `recepcionkitkit`(`notadeentrega`, `idusuario`)
             VALUES (:notadeentrega, :idusuario )");
            $stmt -> bindParam(":notadeentrega", $nmkit['notadeentrega'], PDO::PARAM_STR);
            $stmt -> bindParam(":idusuario", $nmkit['idusuario'], PDO::PARAM_INT);
           

            if ($stmt -> execute())
            {
                return 'exitoso';
                
            }
            else 
            {
                return 'error';
            }
        }

        public function RegistrarNotaEntregaModelo($NotaEntrega, $IdPersonal)
        {
            $stmt = Conexion::Conectar()->prepare("INSERT INTO `recepcionkitkit` (`notadeentrega`, `idusuario`)
             VALUES ('$NotaEntrega', '$IdPersonal' )");

            return ($stmt -> execute()) ? 'exitoso' : 'error';
        }
        public function RegistrarNotaEntregacilModelo($NotaEntrega, $IdPersonal)
        {
            $stmt = Conexion::Conectar()->prepare("INSERT INTO `recepcioncilindro` (`notadeentrega`, `idusuario`)
             VALUES ('$NotaEntrega', '$IdPersonal' )");
           
            return ($stmt -> execute()) ? 'exitoso' : 'error';
        }
       
    }
 /*Notas d entrega*/
    class ListaNotaModelos
    {
        public function ListaNotaModelo()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT rk.fecharecepcion,rk.notadeentrega,p.ApellidoPaterno,p.ApellidoMaterno,p.Nombres, u.tipo FROM personal p INNER JOIN usuario u ON p.IdPersonal=u.IdPersonal 
            INNER JOIN recepcionkitkit rk ON u.IdPersonal=rk.idusuario");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
        public function ListaNotaModelocil()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT rc.fecharecepcioncil,rc.notadeentrega, p.ApellidoPaterno, p.ApellidoMaterno,p.Nombres, u.Tipo from personal p INNER JOIN usuario u on p.IdPersonal=u.IdPersonal
             INNER JOIN recepcioncilindro rc ON u.IdPersonal=rc.idusuario;");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
        public function ListaDetallecilModelo($CodRecepcioncil)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT c.seriecilindro,mc.descripcioncil,c.capacidad,c.aofab  FROM cilindro c INNER JOIN marcacilindro mc on c.codmarcacil=mc.codmarcacil WHERE c.codrecpecioncil='$CodRecepcioncil'");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
     
    }
    class pdfkitModelos
    {
        public function pdfkitModelo($idkit)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM kit k,marcakit mk, recepcionkitkit rk
             WHERE k.codrecpecion=rk.codrecepcion and k.codmarca=mk.codmarca AND rk.codrecepcion='$idkit'");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
    }
    class ListakitModelos
    {
        public function ListakitAsignarModelo()
        {
            
                $stmt = Conexion::Conectar()->prepare("SELECT * FROM `kit` WHERE estado=1");
                $stmt -> execute();
                return $stmt -> fetchAll();
                $stmt -> close();
                $stmt = null;
        
        }
    }
    class KitexistenteModelos
    {
        public function KitExistenteModelo($seriekit)
        {
            $stmt = Conexion::Conectar()->prepare("SELECT `seriekit`, `tipo`, `estado`, `codmarca`, `codrecpecion` FROM `kit` WHERE seriekit='$seriekit'");
            $stmt -> execute();
            return $stmt -> fetch();
            $stmt -> close();
            $stmt = null;
        }
    }
    

