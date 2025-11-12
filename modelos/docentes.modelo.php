<?php
  require_once 'conexion.modelo.php';
  //docentes
  class DocentesModelo{
    public function ListaDocenteModelo()
    {
         $stmt = Conexion::Conectar()->prepare
        ("SELECT * FROM docente ");
        $stmt -> execute();
        return $stmt -> fetchAll();
        $stmt ->close();
        $stmt =null;

    }
    public function RegistrarDocenteModelo($DatosDocentes)
    {
          $stmt = Conexion::Conectar()->prepare("
            INSERT INTO `docente` (
                `Ci`, 
                `Complemento`, 
                `Exp`, 
                `Nombre`, 
                `Apaterno`, 
                `Amaterno`, 
                `FechaNacimiento`, 
                `CedulaProfesional`, 
                `Especialidad`,  
                `Direccion`, 
                `Correo`, 
                `Tel`, 
                `Cel`
            )  
            VALUES (
                :Ci, 
                :Complemento, 
                :Exp, 
                :Nombre,      
                :Apaterno, 
                :Amaterno, 
                :FechaNacimiento, 
                :CedulaProfesional, 
                :Especialidad, 
                :Direccion, 
                :Correo,  
                :Tel, 
                :Cel
                )");
                $stmt->bindParam(":Ci",                $DatosDocentes['Ci'],                PDO::PARAM_INT); 
                $stmt->bindParam(":Complemento",       $DatosDocentes['Complemento'],       PDO::PARAM_STR);
                $stmt->bindParam(":Exp",               $DatosDocentes['Exp'],               PDO::PARAM_STR); 
                $stmt->bindParam(":Nombre",            $DatosDocentes['Nombre'],            PDO::PARAM_STR); 
                $stmt->bindParam(":Apaterno",          $DatosDocentes['Apaterno'],          PDO::PARAM_STR);
                $stmt->bindParam(":Amaterno",          $DatosDocentes['Amaterno'],          PDO::PARAM_STR); 
                $stmt->bindParam(":FechaNacimiento",   $DatosDocentes['FechaNacimiento'],   PDO::PARAM_STR);
                $stmt->bindParam(":CedulaProfesional",   $DatosDocentes['CedulaProfesional'],   PDO::PARAM_STR);
                $stmt->bindParam(":Especialidad",   $DatosDocentes['Especialidad'],   PDO::PARAM_STR);
                $stmt->bindParam(":Direccion",            $DatosDocentes['Direccion'],            PDO::PARAM_STR); 
                $stmt->bindParam(":Correo",         $DatosDocentes['Correo'],         PDO::PARAM_STR); 
                $stmt->bindParam(":Tel",          $DatosDocentes['Tel'],          PDO::PARAM_STR); 
                $stmt->bindParam(":Cel",           $DatosDocentes['Cel'],           PDO::PARAM_INT); 

        if ($stmt->execute()) {
            return 'exitoso';
        } else {
            
        print_r($stmt->errorInfo()); 
            return 'error';
        } 
    }
    public function BuscarDocenteModelo($Ci)
    {
        $stmt =conexion::Conectar()->prepare("SELECT * FROM `docente` where Ci= $Ci");
        $stmt -> execute();
        return $stmt -> fetchAll();
        $stmt ->close();
        $stmt =null;

    }
  }