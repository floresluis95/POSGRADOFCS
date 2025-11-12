<?php
    require_once 'conexion.modelo.php';

    class UsuarioModelos
    {
        public function ListaUsuariosModelo()
        {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM personal p INNER JOIN usuario u ON p.IdPersonal = u.IdPersonal");
            $stmt -> execute();
            return $stmt -> fetchAll();
            $stmt -> close();
            $stmt = null;
        }
        public function InsertarPersonalModelo($TablaPersonal, $DatosModelo)
        {
            $stmt = Conexion::Conectar()->prepare("INSERT INTO $TablaPersonal (`IdPersonal`, `CedulaIdentidad`, `ApellidoPaterno`, `ApellidoMaterno`, `Nombres`, `Direccion`, `Celular`, `Telefono`) 
            VALUES (:IdPersonal, :CedulaIdentidad, :ApellidoPaterno, :ApellidoMaterno, :Prueba, :Nombres, :Direccion, :Celular, :Telefono)");

            $stmt -> bindParam(":IdPersonal", $DatosModelo['IdPersonal'], PDO::PARAM_INT);
            $stmt -> bindParam(":CedulaIdentidad", $DatosModelo['CedulaIdentidad'], PDO::PARAM_STR);
            $stmt -> bindParam(":ApellidoPaterno", $DatosModelo['ApellidoPaterno'], PDO::PARAM_STR);
            $stmt -> bindParam(":ApellidoMaterno", $DatosModelo['ApellidoMaterno'], PDO::PARAM_STR);
            $stmt -> bindParam(":Nombres", $DatosModelo['Nombres'], PDO::PARAM_STR);
            $stmt -> bindParam(":Direccion", $DatosModelo['Direccion'], PDO::PARAM_STR);
            $stmt -> bindParam(":Prueba", $DatosModelo['Prueba'], PDO::PARAM_STR);
            $stmt -> bindParam(":Celular", $DatosModelo['Celular'], PDO::PARAM_INT);
            $stmt -> bindParam(":Telefono", $DatosModelo['Telefono'], PDO::PARAM_INT);         

            if ($stmt -> execute())
            {
                return 'exitoso';
            }
            else 
            {
                return 'error';
            }
        }

        public function InsertarUsuarioModelo($TablaUsuario, $DatosModelo)
        {
            $stmt = Conexion::Conectar()->prepare("INSERT INTO $TablaUsuario(`IdPersonal`, `Usuario`, `Password`, `Tipo`)
             VALUES (:IdPersonal, :Usuario, :Password2, :Tipo)");
            
            
            $stmt -> bindParam(":IdPersonal", $DatosModelo['IdPersonal'], PDO::PARAM_INT);
            $stmt -> bindParam(":Usuario", $DatosModelo['Usuario'], PDO::PARAM_STR);
            $stmt -> bindParam(":Password2", $DatosModelo['Password'], PDO::PARAM_STR);
            $stmt -> bindParam(":Tipo", $DatosModelo['Tipo'], PDO::PARAM_STR);
            if ($stmt -> execute())
            {
                return 'exitoso';
            }
            else 
            {
                return 'error';
            }
            
            
        }
      public function EditarUsuaario()
       {
        $stmt = Conexion::Conectar()->prepare("UPDATE `personal` SET `ApellidoPaterno`=:ApellidoPaterno,
        `ApellidoMaterno`=:ApellidoMaterno,`Nombres`=:Nombres,
        `Direccion`=:Direccion,`Celular`=:Celular,`Telefono`=:Telefono");
       
       
       $stmt -> bindParam(":ApellidoPaterno", $DatosModelo['ApellidoPaterno'], PDO::PARAM_INT);
       $stmt -> bindParam(":ApellidoMaterno", $DatosModelo['ApellidoMaterno'], PDO::PARAM_STR);
       $stmt -> bindParam(":Nombres", $DatosModelo['Nombres'], PDO::PARAM_STR);
       $stmt -> bindParam(":Direccion", $DatosModelo['Direccion'], PDO::PARAM_STR);
       $stmt -> bindParam(":Celular", $DatosModelo['Celular'], PDO::PARAM_INT);
       $stmt -> bindParam(":Telefono", $DatosModelo['Telefono'], PDO::PARAM_INT);
  
       if ($stmt -> execute())
       {
           return 'exitoso';
       }
       else 
       {
           return 'error';
       }
       
       }
       public function CambiarEstadoUsuario($idusr)
       {
           $stmt = Conexion::Conectar()->prepare("UPDATE usuario u, personal p SET `Estado`='0' WHERE u.IdPersonal=p.IdPersonal AND p.CedulaIdentidad='$idusr'");
           
           return ($stmt -> execute()) ? 'exitoso' : 'error'; 
       }
       public function CambiarEstadosUsuario($idusrs)
       {
           $stmt = Conexion::Conectar()->prepare("UPDATE usuario u, personal p SET `Estado`='1' WHERE u.IdPersonal=p.IdPersonal AND p.CedulaIdentidad='$idusrs'");
           
           return ($stmt -> execute()) ? 'exitoso' : 'error'; 
       }
    }