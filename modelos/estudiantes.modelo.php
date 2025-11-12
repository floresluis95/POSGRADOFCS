<?php
  require_once 'conexion.modelo.php';
  //ESTUDIANTES
class EstudiantesModelos
{
     public function ListaEstudianteModelo()
    {
        $stmt = Conexion::Conectar()->prepare
        ("SELECT * FROM estudiante ");
        $stmt -> execute();
        return $stmt -> fetchAll();
        $stmt ->close();
        $stmt =null;
    }
     public function ListaEstudianteActivoModelo()
    {
        $stmt = Conexion::Conectar()->prepare
        ("SELECT * FROM `estudiante` WHERE Estado=1");
        $stmt -> execute();
        return $stmt -> fetchAll();
        $stmt ->close();
        $stmt =null;
    }
   
    public function ListarEstudianteProgramaModelo()
    {
        $stmt = Conexion::Conectar()->prepare
        ("SELECT *  FROM estudianteprograma ep 
         INNER JOIN estudiante e on ep.EstudianteID=e.EstudianteID 
         INNER JOIN programa p on ep.ProgramaID =p.ProgramaID;");
        $stmt -> execute();
        return $stmt -> fetchAll();
        $stmt ->close();
        $stmt =null;
    }
   
    public function BuscarEstudianteModelo($ciestudiante)
    {
        $stmt =conexion::Conectar()->prepare("SELECT * FROM `estudiante` where Ci= `$ciestudiante`");
        $stmt -> execute();
        return $stmt -> fetchAll();
        $stmt ->close();
        $stmt =null;

    }
   public function RegistrarEstudianteModelo($DatosEstudiante)
    {
    // El $i++ no tiene sentido aquí, se elimina.

    $stmt = Conexion::Conectar()->prepare("
    INSERT INTO `estudiante` (
        `Ci`, 
        `Complemento`, 
        `Exp`, 
        `Nombre`, 
        `Apaterno`, 
        `Amaterno`, 
        `FechaNacimiento`, 
        `Edad`, 
        `Lugarn`, 
        `Correo`,
        `Profesion`,
        `Trabajo`,   
        `Direccion`, 
        `Telefono`, 
        `Celular`
    )  
    VALUES (
        :Ci, 
        :Complemento, 
        :Exp, 
        :Nombre,      /* Marcador simple, más claro */
        :Apaterno, 
        :Amaterno, 
        :FechaNacimiento, 
        :Edad,
        :Lugarn,
        :Correo,
        :Profesion,
        :Trabajo,
        :Direccion, 
        :Telefono, 
        :Celular
    )");

    // Vinculación de parámetros (bindParam)
    // Se utiliza PDO::PARAM_STR para todos los campos de texto, fechas e identificadores (CI, Celular) 
    // para evitar problemas con ceros iniciales o formatos de fecha.

    $stmt->bindParam(":Ci",                $DatosEstudiante['Ci'],                PDO::PARAM_INT); // Cambiado a STR
    $stmt->bindParam(":Complemento",       $DatosEstudiante['Complemento'],       PDO::PARAM_STR);
    $stmt->bindParam(":Exp",               $DatosEstudiante['Exp'],               PDO::PARAM_STR); // Corregido: Ahora usa la clave 'Exp' del controlador
    $stmt->bindParam(":Nombre",            $DatosEstudiante['Nombre'],            PDO::PARAM_STR); // Corregido: Usa clave 'Nombre'
    $stmt->bindParam(":Apaterno",          $DatosEstudiante['Apaterno'],          PDO::PARAM_STR); // Corregido: Usa clave 'Apaterno'
    $stmt->bindParam(":Amaterno",          $DatosEstudiante['Amaterno'],          PDO::PARAM_STR); // Corregido: Usa clave 'Amaterno'
    $stmt->bindParam(":FechaNacimiento",   $DatosEstudiante['FechaNacimiento'],   PDO::PARAM_STR);
    $stmt->bindParam(":Edad",              $DatosEstudiante['Edad'],              PDO::PARAM_INT);
    $stmt->bindParam(":Lugarn",            $DatosEstudiante['Lugarn'],            PDO::PARAM_STR);
    $stmt->bindParam(":Correo",            $DatosEstudiante['Correo'],            PDO::PARAM_STR); // Corregido: Usa clave 'Correo'
    $stmt->bindParam(":Profesion",         $DatosEstudiante['Profesion'],         PDO::PARAM_STR); // Corregido: Usa clave 'Direccion'
    $stmt->bindParam(":Trabajo",           $DatosEstudiante['Trabajo'],           PDO::PARAM_STR); // Corregido: Usa clave 'Direccion'
    $stmt->bindParam(":Direccion",         $DatosEstudiante['Direccion'],         PDO::PARAM_STR); // Corregido: Usa clave 'Direccion'
    $stmt->bindParam(":Telefono",          $DatosEstudiante['Telefono'],          PDO::PARAM_STR); // Corregido: Usa clave 'telefono'
    $stmt->bindParam(":Celular",           $DatosEstudiante['Celular'],           PDO::PARAM_INT); // Corregido: Cambiado a STR

    if ($stmt->execute()) {
        return 'exitoso';
    } else {
        // En un entorno de desarrollo, podrías imprimir un error de PDO para depurar:
     print_r($stmt->errorInfo()); 
        return 'error';
    }
    
    }
  public function RegistrarEstudianteModelo2($DatosEstudiante)
    {
    // El $i++ no tiene sentido aquí, se elimina.

    $stmt = Conexion::Conectar()->prepare("
    INSERT INTO `estudiante` (
        `Ci`, 
        `Complemento`, 
        `Exp`, 
        `Nombre`, 
        `Apaterno`, 
        `Amaterno`, 
        `FechaNacimiento`, 
        `Correo`, 
        `Direccion`, 
        `Telefono`, 
        `Celular`
    )  
    VALUES (
        :Ci, 
        :Complemento, 
        :Exp, 
        :Nombre,      /* Marcador simple, más claro */
        :Apaterno, 
        :Amaterno, 
        :FechaNacimiento, 
        :Correo, 
        :Direccion, 
        :Telefono, 
        :Celular
    )");

    // Vinculación de parámetros (bindParam)
    // Se utiliza PDO::PARAM_STR para todos los campos de texto, fechas e identificadores (CI, Celular) 
    // para evitar problemas con ceros iniciales o formatos de fecha.

    $stmt->bindParam(":Ci",                $DatosEstudiante['Ci'],                PDO::PARAM_INT); // Cambiado a STR
    $stmt->bindParam(":Complemento",       $DatosEstudiante['Complemento'],       PDO::PARAM_STR);
    $stmt->bindParam(":Exp",               $DatosEstudiante['Exp'],               PDO::PARAM_STR); // Corregido: Ahora usa la clave 'Exp' del controlador
    $stmt->bindParam(":Nombre",            $DatosEstudiante['Nombre'],            PDO::PARAM_STR); // Corregido: Usa clave 'Nombre'
    $stmt->bindParam(":Apaterno",          $DatosEstudiante['Apaterno'],          PDO::PARAM_STR); // Corregido: Usa clave 'Apaterno'
    $stmt->bindParam(":Amaterno",          $DatosEstudiante['Amaterno'],          PDO::PARAM_STR); // Corregido: Usa clave 'Amaterno'
    $stmt->bindParam(":FechaNacimiento",   $DatosEstudiante['FechaNacimiento'],   PDO::PARAM_STR);
    $stmt->bindParam(":Correo",            $DatosEstudiante['Correo'],            PDO::PARAM_STR); // Corregido: Usa clave 'Correo'
    $stmt->bindParam(":Direccion",         $DatosEstudiante['Direccion'],         PDO::PARAM_STR); // Corregido: Usa clave 'Direccion'
    $stmt->bindParam(":Telefono",          $DatosEstudiante['Telefono'],          PDO::PARAM_STR); // Corregido: Usa clave 'telefono'
    $stmt->bindParam(":Celular",           $DatosEstudiante['Celular'],           PDO::PARAM_INT); // Corregido: Cambiado a STR

    if ($stmt->execute()) {
        return 'exitoso';
    } else {
        // En un entorno de desarrollo, podrías imprimir un error de PDO para depurar:
     print_r($stmt->errorInfo()); 
        return 'error';
    } 
    }
    
    public function CambiarEstadoEstudianteModelo($Ci)
       {
           $stmt = Conexion::Conectar()->prepare("UPDATE estudiante SET `Estado`='0' WHERE  Ci='$Ci'");
           
           return ($stmt -> execute()) ? 'exitoso' : 'error'; 
       }
    public function CambiarEstadosEstudianteModelo($Ci)
       {
           $stmt = Conexion::Conectar()->prepare("UPDATE estudiante SET `Estado`='1' WHERE  Ci='$Ci'");
           
           return ($stmt -> execute()) ? 'exitoso' : 'error'; 
       }

    
}

class ProfesionModelos {
    public static function ListaprofesionModelos() {
        $stmt = Conexion::Conectar()->prepare("SELECT * FROM profesion ORDER BY NombreProfesion ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}