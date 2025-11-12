<?php
  require_once 'conexion.modelo.php';
class NotaEntregaModelos
{ 
    public function RegistrarNotaKitModelo($nmkit)
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
    public function ListaNotaKitModelo()
    {
        $stmt = Conexion::Conectar()->prepare("SELECT rk.codrecepcion,rk.fecharecepcion,rk.notadeentrega,p.ApellidoPaterno,p.ApellidoMaterno,p.Nombres, u.tipo FROM personal p INNER JOIN usuario u ON p.IdPersonal=u.IdPersonal 
        INNER JOIN recepcionkitkit rk ON u.IdPersonal=rk.idusuario ORDER BY rk.fecharecepcion DESC");
        $stmt -> execute();
        return $stmt -> fetchAll();
        $stmt -> close();
        $stmt = null;
    }

    public function ListadetalleKitModelo($idnotadetalle)
    {
        $stmt = Conexion::Conectar()->prepare("SELECT k.seriekit,mk.descripcion, k.tipo ,rc.notadeentrega FROM recepcionkitkit rc ,kit k ,marcakit mk 
        WHERE rc.codrecepcion=k.codrecpecion and k.codmarca=mk.codmarca and rc.codrecepcion='$idnotadetalle'");
        $stmt -> execute();
        return $stmt -> fetchAll();
        $stmt -> close();
        $stmt = null;
    }

    //notacilindros
    public function RegistrarNotaCilModelo($nmkit)
    {
        $stmt = Conexion::Conectar()->prepare("INSERT INTO `recepcioncilindro`(`notadeentrega`, `idusuario`)
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


    public function RegistrarNotaEntregacilModelo($NotaEntrega, $IdPersonal)
    {
        $stmt = Conexion::Conectar()->prepare("INSERT INTO `recepcioncilindro` (`notadeentrega`, `idusuario`)
         VALUES ('$NotaEntrega', '$IdPersonal' )");
       
        return ($stmt -> execute()) ? 'exitoso' : 'error';
    }


    public function ListaNotacilModelo()
    {
        $stmt = Conexion::Conectar()->prepare("SELECT rc.codrecepcioncil, rc.fecharecepcioncil,rc.notadeentrega,p.ApellidoPaterno,p.ApellidoMaterno,p.Nombres, u.Tipo FROM personal P INNER JOIN usuario U ON P.IdPersonal=U.IdPersonal 
        INNER JOIN recepcioncilindro rc on u.IdPersonal=rc.idusuario  ORDER BY rc.fecharecepcioncil DESC");
        $stmt -> execute();
        return $stmt -> fetchAll();
        $stmt -> close();
        $stmt = null;
    }
    public function ListadetalleCilModelo($idnotadetallecil)
    {
        $stmt = Conexion::Conectar()->prepare("SELECT rc.codrecepcioncil, c.seriecilindro ,mc.descripcioncil, c.capacidad,c.aofab , rc.notadeentrega FROM cilindro c, marcacilindro mc ,recepcioncilindro rc 
        WHERE c.codrecpecioncil=rc.codrecepcioncil and c.codmarcacil=mc.codmarcacil and rc.codrecepcioncil='$idnotadetallecil'");
        $stmt -> execute();
        return $stmt -> fetchAll();
        $stmt -> close();
        $stmt = null;
    }
    


}
class NotaExistenteModelos
{
    public function NotaKitExistenteModelo($NotaEntrega)
    {
        $stmt = Conexion::Conectar()->prepare("SELECT `codrecepcion`, `fecharecepcion`, `notadeentrega`, `idusuario` FROM `recepcionkitkit` WHERE notadeentrega='$NotaEntrega'");
        $stmt -> execute();
        return $stmt -> fetch();
        $stmt -> close();
        $stmt = null;
    }
    public function NotaCitExistenteModelo($NotaEntregacil)
    {
        $stmt = Conexion::Conectar()->prepare("SELECT `codrecepcioncil`, `fecharecepcioncil`, `notadeentrega`, `idusuario` FROM `recepcioncilindro` WHERE notadeentrega='$NotaEntregacil'");
        $stmt -> execute();
        return $stmt -> fetch();
        $stmt -> close();
        $stmt = null;
    }
}