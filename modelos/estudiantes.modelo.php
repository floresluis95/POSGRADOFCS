<?php
require_once 'conexion.modelo.php';

// ===========================
//      MODELO ESTUDIANTES
// ===========================

class EstudiantesModelos
{
    /* LISTAR TODOS */
    public static function ListaEstudianteModelo()
    {
        $stmt = Conexion::Conectar()->prepare("SELECT * FROM estudiante");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* LISTAR SOLO ACTIVOS CON INFORMACIÓN DE USUARIO */
    public static function ListaEstudianteActivoModelo()
    {
        $stmt = Conexion::Conectar()->prepare("SELECT e.*, p.NombreProfesion,
                                                u.Usuario, u.Estado as EstadoUsuario, u.PasswordTexto
                                                FROM estudiante e
                                                INNER JOIN profesion p ON e.IdProfesion = p.IdProfesion
                                                LEFT JOIN usuario u ON e.EstudianteID = u.EstudianteID
                                                WHERE e.Estado = 1
                                                ORDER BY e.Apaterno, e.Amaterno, e.Nombre");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* LISTAR ESTUDIANTE + PROGRAMA */
    public static function ListarEstudianteProgramaModelo()
    {
        $stmt = Conexion::Conectar()->prepare("
            SELECT *  
            FROM estudianteprograma ep
            INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
            INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* BUSCAR POR CI */
    public static function BuscarEstudianteModelo($ciestudiante)
    {
        $stmt = Conexion::Conectar()->prepare("
            SELECT * FROM estudiante WHERE Ci = :ci
        ");
        $stmt->bindParam(":ci", $ciestudiante, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* REGISTRAR CON TODOS LOS CAMPOS */
   public static function RegistrarEstudianteModelo($DatosEstudiante)
{
    $stmt = Conexion::Conectar()->prepare("
        INSERT INTO estudiante (
            Ci, Complemento, Exp, Nombre, Apaterno, Amaterno,
            FechaNacimiento, Edad, Lugarn, Correo, IdProfesion,
            Trabajo, Direccion, Telefono, Celular, IdUniversidad, FechaEgreso
        )
        VALUES (
            :Ci, :Complemento, :Exp, :Nombre, :Apaterno, :Amaterno,
            :FechaNacimiento, :Edad, :Lugarn, :Correo, :IdProfesion,
            :Trabajo, :Direccion, :Telefono, :Celular, :IdUniversidad, :FechaEgreso
        )
    ");

    $stmt->bindParam(":Ci", $DatosEstudiante['Ci'], PDO::PARAM_STR);
    $stmt->bindParam(":Complemento", $DatosEstudiante['Complemento'], PDO::PARAM_STR);
    $stmt->bindParam(":Exp", $DatosEstudiante['Exp'], PDO::PARAM_STR);
    $stmt->bindParam(":Nombre", $DatosEstudiante['Nombre'], PDO::PARAM_STR);
    $stmt->bindParam(":Apaterno", $DatosEstudiante['Apaterno'], PDO::PARAM_STR);
    $stmt->bindParam(":Amaterno", $DatosEstudiante['Amaterno'], PDO::PARAM_STR);
    $stmt->bindParam(":FechaNacimiento", $DatosEstudiante['FechaNacimiento'], PDO::PARAM_STR);
    $stmt->bindParam(":Edad", $DatosEstudiante['Edad'], PDO::PARAM_INT);
    $stmt->bindParam(":Lugarn", $DatosEstudiante['Lugarn'], PDO::PARAM_STR);
    $stmt->bindParam(":Correo", $DatosEstudiante['Correo'], PDO::PARAM_STR);

    // 👉 Este es el ID de la tabla PROFESION
    $stmt->bindParam(":IdProfesion", $DatosEstudiante['IdProfesion'], PDO::PARAM_INT);

    $stmt->bindParam(":Trabajo", $DatosEstudiante['Trabajo'], PDO::PARAM_STR);
    $stmt->bindParam(":Direccion", $DatosEstudiante['Direccion'], PDO::PARAM_STR);
    $stmt->bindParam(":Telefono", $DatosEstudiante['Telefono'], PDO::PARAM_STR);
    $stmt->bindParam(":Celular", $DatosEstudiante['Celular'], PDO::PARAM_STR);

    // Universidad de egreso: opcional (algunos flujos de alta no la piden todavía).
    $idUniversidad = $DatosEstudiante['IdUniversidad'] ?? null;
    $fechaEgreso = $DatosEstudiante['FechaEgreso'] ?? null;
    $stmt->bindValue(":IdUniversidad", $idUniversidad, $idUniversidad === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmt->bindValue(":FechaEgreso", $fechaEgreso, $fechaEgreso === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

    if ($stmt->execute()) {
        return 'exitoso';
    } else {
        print_r($stmt->errorInfo());
        return 'error';
    }
}
    
 

    /* ACTUALIZAR DATOS DE UN ESTUDIANTE EXISTENTE (no toca Ci ni Estado) */
    public static function ActualizarEstudianteModelo($EstudianteID, $DatosEstudiante)
    {
        $stmt = Conexion::Conectar()->prepare("
            UPDATE estudiante SET
                Complemento = :Complemento,
                Exp = :Exp,
                Nombre = :Nombre,
                Apaterno = :Apaterno,
                Amaterno = :Amaterno,
                FechaNacimiento = :FechaNacimiento,
                Edad = :Edad,
                Lugarn = :Lugarn,
                Correo = :Correo,
                IdProfesion = :IdProfesion,
                Trabajo = :Trabajo,
                Direccion = :Direccion,
                Telefono = :Telefono,
                Celular = :Celular,
                IdUniversidad = :IdUniversidad,
                FechaEgreso = :FechaEgreso
            WHERE EstudianteID = :EstudianteID
        ");

        $stmt->bindParam(":Complemento", $DatosEstudiante['Complemento'], PDO::PARAM_STR);
        $stmt->bindParam(":Exp", $DatosEstudiante['Exp'], PDO::PARAM_STR);
        $stmt->bindParam(":Nombre", $DatosEstudiante['Nombre'], PDO::PARAM_STR);
        $stmt->bindParam(":Apaterno", $DatosEstudiante['Apaterno'], PDO::PARAM_STR);
        $stmt->bindParam(":Amaterno", $DatosEstudiante['Amaterno'], PDO::PARAM_STR);
        $stmt->bindParam(":FechaNacimiento", $DatosEstudiante['FechaNacimiento'], PDO::PARAM_STR);
        $stmt->bindParam(":Edad", $DatosEstudiante['Edad'], PDO::PARAM_INT);
        $stmt->bindParam(":Lugarn", $DatosEstudiante['Lugarn'], PDO::PARAM_STR);
        $stmt->bindParam(":Correo", $DatosEstudiante['Correo'], PDO::PARAM_STR);
        $stmt->bindParam(":IdProfesion", $DatosEstudiante['IdProfesion'], PDO::PARAM_INT);
        $stmt->bindParam(":Trabajo", $DatosEstudiante['Trabajo'], PDO::PARAM_STR);
        $stmt->bindParam(":Direccion", $DatosEstudiante['Direccion'], PDO::PARAM_STR);
        $stmt->bindParam(":Telefono", $DatosEstudiante['Telefono'], PDO::PARAM_STR);
        $stmt->bindParam(":Celular", $DatosEstudiante['Celular'], PDO::PARAM_STR);
        $stmt->bindParam(":EstudianteID", $EstudianteID, PDO::PARAM_INT);

        $idUniversidad = $DatosEstudiante['IdUniversidad'] ?? null;
        $fechaEgreso = $DatosEstudiante['FechaEgreso'] ?? null;
        $stmt->bindValue(":IdUniversidad", $idUniversidad, $idUniversidad === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(":FechaEgreso", $fechaEgreso, $fechaEgreso === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

        return $stmt->execute() ? 'exitoso' : 'error';
    }

    /* CAMBIAR A INACTIVO */
    public static function CambiarEstadoEstudianteModelo($Ci)
    {
        $stmt = Conexion::Conectar()->prepare("
            UPDATE estudiante SET Estado = 0 WHERE Ci = :Ci
        ");
        $stmt->bindParam(":Ci", $Ci, PDO::PARAM_STR);
        return $stmt->execute() ? "exitoso" : "error";
    }

    /* CAMBIAR A ACTIVO */
    public static function CambiarEstadosEstudianteModelo($Ci)
    {
        $stmt = Conexion::Conectar()->prepare("
            UPDATE estudiante SET Estado = 1 WHERE Ci = :Ci
        ");
        $stmt->bindParam(":Ci", $Ci, PDO::PARAM_STR);
        return $stmt->execute() ? "exitoso" : "error";
    }
}



// ===========================
//       MODELO PROFESIÓN
// ===========================
class ProfesionModelos {
    public static function ListaprofesionModelos()
    {
        $stmt = Conexion::Conectar()->prepare("SELECT * FROM profesion ORDER BY NombreProfesion ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function ObtenerPorId($id)
    {
        $stmt = Conexion::Conectar()->prepare("SELECT * FROM profesion WHERE IdProfesion = :id LIMIT 1");
        $stmt->bindValue(":id", (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC); // false si no existe
    }

    public static function BuscarPorNombreModelo($nombre)
    {
        $stmt = Conexion::Conectar()->prepare("SELECT * FROM profesion WHERE NombreProfesion = :nombre LIMIT 1");
        $stmt->bindValue(":nombre", $nombre, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC); // false si no existe
    }

    /**
     * Registrar una profesión nueva (usado desde el botón "+" del select de Profesión
     * en Pre-Registro, cuando la profesión buscada no está en la lista).
     * @param string $nombre
     * @return array ['status' => 'exitoso'/'duplicado'/'error', 'profesion' => array|null]
     */
    public static function RegistrarProfesionModelo($nombre)
    {
        $nombre = trim($nombre);

        if ($nombre === '') {
            return ['status' => 'error', 'mensaje' => 'El nombre de la profesión es requerido'];
        }

        $existente = self::BuscarPorNombreModelo($nombre);
        if ($existente) {
            return ['status' => 'duplicado', 'mensaje' => 'Esa profesión ya existe', 'profesion' => $existente];
        }

        try {
            $pdo = Conexion::Conectar();
            $stmt = $pdo->prepare(
                "INSERT INTO profesion (NombreProfesion, Detalle) VALUES (:nombre, '')"
            );
            $stmt->bindValue(":nombre", $nombre, PDO::PARAM_STR);

            if (!$stmt->execute()) {
                return ['status' => 'error', 'mensaje' => 'No se pudo registrar la profesión'];
            }

            $id = $pdo->lastInsertId();
            return [
                'status' => 'exitoso',
                'mensaje' => 'Profesión registrada correctamente',
                'profesion' => ['IdProfesion' => $id, 'NombreProfesion' => $nombre]
            ];
        } catch (PDOException $e) {
            error_log("Error en RegistrarProfesionModelo: " . $e->getMessage());
            return ['status' => 'error', 'mensaje' => 'Error en el servidor: ' . $e->getMessage()];
        }
    }
}
?>
