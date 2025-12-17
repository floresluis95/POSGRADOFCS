<?php
  require_once 'conexion.modelo.php';
  //docentes
  class DocentesModelo{
    /* LISTAR TODOS LOS DOCENTES */
    public static function ListaDocenteModelo()
    {
         $stmt = Conexion::Conectar()->prepare
        ("SELECT * FROM docente ORDER BY Apaterno, Amaterno, Nombre");
        $stmt -> execute();
        return $stmt -> fetchAll();
        $stmt =null;
    }

    /* LISTAR DOCENTES CON INFORMACIÓN DE USUARIO */
    public static function ListaDocenteActivoModelo()
    {
        $stmt = Conexion::Conectar()->prepare("SELECT d.*,
                                                u.Usuario, u.Estado as EstadoUsuario, u.PasswordTexto
                                                FROM docente d
                                                LEFT JOIN usuario u ON d.DocenteID = u.DocenteID
                                                ORDER BY d.Apaterno, d.Amaterno, d.Nombre");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function RegistrarDocenteModelo($DatosDocentes)
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
                `Cel`,
                `EsExtranjero`,
                `Pais`,
                `Region`
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
                :Cel,
                :EsExtranjero,
                :Pais,
                :Region
                )");
                $stmt->bindParam(":Ci",                $DatosDocentes['Ci'],                PDO::PARAM_STR);
                $stmt->bindParam(":Complemento",       $DatosDocentes['Complemento'],       PDO::PARAM_STR);
                $stmt->bindParam(":Exp",               $DatosDocentes['Exp'],               PDO::PARAM_STR);
                $stmt->bindParam(":Nombre",            $DatosDocentes['Nombre'],            PDO::PARAM_STR);
                $stmt->bindParam(":Apaterno",          $DatosDocentes['Apaterno'],          PDO::PARAM_STR);
                $stmt->bindParam(":Amaterno",          $DatosDocentes['Amaterno'],          PDO::PARAM_STR);
                $stmt->bindParam(":FechaNacimiento",   $DatosDocentes['FechaNacimiento'],   PDO::PARAM_STR);
                $stmt->bindParam(":CedulaProfesional", $DatosDocentes['CedulaProfesional'], PDO::PARAM_STR);
                $stmt->bindParam(":Especialidad",      $DatosDocentes['Especialidad'],      PDO::PARAM_STR);
                $stmt->bindParam(":Direccion",         $DatosDocentes['Direccion'],         PDO::PARAM_STR);
                $stmt->bindParam(":Correo",            $DatosDocentes['Correo'],            PDO::PARAM_STR);
                $stmt->bindParam(":Tel",               $DatosDocentes['Tel'],               PDO::PARAM_STR);
                $stmt->bindParam(":Cel",               $DatosDocentes['Cel'],               PDO::PARAM_STR);
                $stmt->bindParam(":EsExtranjero",      $DatosDocentes['EsExtranjero'],      PDO::PARAM_INT);
                $stmt->bindParam(":Pais",              $DatosDocentes['Pais'],              PDO::PARAM_STR);
                $stmt->bindParam(":Region",            $DatosDocentes['Region'],            PDO::PARAM_STR);

        if ($stmt->execute()) {
            return 'exitoso';
        } else {

        print_r($stmt->errorInfo());
            return 'error';
        }
    }
    public static function BuscarDocenteModelo($Ci)
    {
        $stmt = Conexion::Conectar()->prepare("SELECT * FROM `docente` WHERE Ci = :Ci");
        $stmt->bindParam(":Ci", $Ci, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* EDITAR DOCENTE */
    public static function EditarDocenteModelo($DatosDocente)
    {
        $stmt = Conexion::Conectar()->prepare("UPDATE `docente` SET
            `Complemento` = :Complemento,
            `Exp` = :Exp,
            `Nombre` = :Nombre,
            `Apaterno` = :Apaterno,
            `Amaterno` = :Amaterno,
            `FechaNacimiento` = :FechaNacimiento,
            `CedulaProfesional` = :CedulaProfesional,
            `Especialidad` = :Especialidad,
            `Direccion` = :Direccion,
            `Correo` = :Correo,
            `Tel` = :Tel,
            `Cel` = :Cel,
            `EsExtranjero` = :EsExtranjero,
            `Pais` = :Pais,
            `Region` = :Region
            WHERE `Ci` = :Ci");

        $stmt->bindParam(":Ci", $DatosDocente['Ci'], PDO::PARAM_STR);
        $stmt->bindParam(":Complemento", $DatosDocente['Complemento'], PDO::PARAM_STR);
        $stmt->bindParam(":Exp", $DatosDocente['Exp'], PDO::PARAM_STR);
        $stmt->bindParam(":Nombre", $DatosDocente['Nombre'], PDO::PARAM_STR);
        $stmt->bindParam(":Apaterno", $DatosDocente['Apaterno'], PDO::PARAM_STR);
        $stmt->bindParam(":Amaterno", $DatosDocente['Amaterno'], PDO::PARAM_STR);
        $stmt->bindParam(":FechaNacimiento", $DatosDocente['FechaNacimiento'], PDO::PARAM_STR);
        $stmt->bindParam(":CedulaProfesional", $DatosDocente['CedulaProfesional'], PDO::PARAM_STR);
        $stmt->bindParam(":Especialidad", $DatosDocente['Especialidad'], PDO::PARAM_STR);
        $stmt->bindParam(":Direccion", $DatosDocente['Direccion'], PDO::PARAM_STR);
        $stmt->bindParam(":Correo", $DatosDocente['Correo'], PDO::PARAM_STR);
        $stmt->bindParam(":Tel", $DatosDocente['Tel'], PDO::PARAM_STR);
        $stmt->bindParam(":Cel", $DatosDocente['Cel'], PDO::PARAM_STR);
        $stmt->bindParam(":EsExtranjero", $DatosDocente['EsExtranjero'], PDO::PARAM_INT);
        $stmt->bindParam(":Pais", $DatosDocente['Pais'], PDO::PARAM_STR);
        $stmt->bindParam(":Region", $DatosDocente['Region'], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return 'exitoso';
        } else {
            print_r($stmt->errorInfo());
            return 'error';
        }
    }

    /* CAMBIAR ESTADO DEL DOCENTE (ELIMINAR LÓGICO) */
    public static function CambiarEstadoDocenteModelo($Ci)
    {
        $stmt = Conexion::Conectar()->prepare("UPDATE docente SET Estado = 0 WHERE Ci = :Ci");
        $stmt->bindParam(":Ci", $Ci, PDO::PARAM_STR);
        return $stmt->execute() ? "exitoso" : "error";
    }

    /* REACTIVAR DOCENTE */
    public static function CambiarEstadosDocenteModelo($Ci)
    {
        $stmt = Conexion::Conectar()->prepare("UPDATE docente SET Estado = 1 WHERE Ci = :Ci");
        $stmt->bindParam(":Ci", $Ci, PDO::PARAM_STR);
        return $stmt->execute() ? "exitoso" : "error";
    }

    /* VERIFICAR SI EL DOCENTE TIENE MÓDULOS ASIGNADOS */
    public static function VerificarModulosAsignadosModelo($DocenteID)
    {
        $stmt = Conexion::Conectar()->prepare("SELECT COUNT(*) as total FROM modulos WHERE DocenteID = :DocenteID AND estadomodulo = 'ACTIVO'");
        $stmt->bindParam(":DocenteID", $DocenteID, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
    }

    /* OBTENER DOCENTE POR ID */
    public static function ObtenerDocentePorIDModelo($DocenteID)
    {
        $stmt = Conexion::Conectar()->prepare("SELECT * FROM docente WHERE DocenteID = :DocenteID");
        $stmt->bindParam(":DocenteID", $DocenteID, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* DAR DE BAJA DOCENTE (CAMBIAR ESTADO) */
    public static function DarDeBajaDocenteModelo($DocenteID)
    {
        $stmt = Conexion::Conectar()->prepare("UPDATE docente SET Estado = 0 WHERE DocenteID = :DocenteID");
        $stmt->bindParam(":DocenteID", $DocenteID, PDO::PARAM_INT);
        return $stmt->execute() ? "exitoso" : "error";
    }
  }