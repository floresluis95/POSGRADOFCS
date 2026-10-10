<?php
require_once 'conexion.modelo.php';
require_once __DIR__ . '/universidad.modelo.php';

// ===========================
//      MODELO ESTUDIANTES
// ===========================

class EstudiantesModelos
{
    /**
     * Universidad y fecha de egreso del estudiante: ambas obligatorias, la universidad
     * debe existir y la fecha no puede ser posterior a hoy ni anterior a los 15 años de
     * edad del estudiante. La usan el Pre-Registro y el módulo Estudiantes (alta y edición).
     * @return string|null Mensaje de error, o null si son válidas
     */
    public static function ValidarDatosEgresoModelo($idUniversidad, $fechaEgreso, $fechaNacimiento)
    {
        $edadMinimaEgreso = 15;

        if (!is_numeric($idUniversidad) || !UniversidadModelos::ObtenerPorIdModelo((int)$idUniversidad)) {
            return 'Seleccione la universidad de egreso';
        }

        $fecha = DateTime::createFromFormat('!Y-m-d', trim((string)$fechaEgreso));
        if (!$fecha || $fecha->format('Y-m-d') !== trim((string)$fechaEgreso)) {
            return 'Ingrese una fecha de egreso válida';
        }
        if ($fecha > new DateTime('today')) {
            return 'La fecha de egreso no puede ser posterior a la fecha actual';
        }

        $nacimiento = DateTime::createFromFormat('!Y-m-d', trim((string)$fechaNacimiento));
        if (!$nacimiento) {
            return 'Ingrese una fecha de nacimiento válida';
        }
        $minima = (clone $nacimiento)->modify('+' . $edadMinimaEgreso . ' years');
        if ($fecha < $minima) {
            return sprintf(
                'La fecha de egreso debe ser al menos %d años posterior a la fecha de nacimiento (desde el %s)',
                $edadMinimaEgreso, $minima->format('d/m/Y')
            );
        }
        return null;
    }

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
                                                u.Usuario, u.Estado as EstadoUsuario
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

    /**
     * Motivo por el que no se puede guardar un estudiante: su C.I. o su correo ya los
     * tiene otro estudiante (la base no permite repetirlos). Se usa al registrar y al
     * editar, para mostrar el porqué en vez de un error genérico.
     * @param string|null $ci        null = no revisar el C.I. (al editar no se cambia)
     * @param int|null    $excluirId al editar, el propio estudiante
     * @return string|null Mensaje, o null si no hay datos repetidos
     */
    public static function MotivoDatoRepetidoModelo($ci, $correo, $excluirId = null)
    {
        $pdo = Conexion::Conectar();
        $nombreDe = function ($e) {
            return trim($e['Nombre'] . ' ' . $e['Apaterno'] . ' ' . $e['Amaterno']);
        };

        if ($ci !== null && trim((string)$ci) !== '') {
            $stmt = $pdo->prepare("SELECT EstudianteID, Ci, Nombre, Apaterno, Amaterno FROM estudiante WHERE Ci = :ci AND EstudianteID <> :excluir LIMIT 1");
            $stmt->execute([':ci' => trim((string)$ci), ':excluir' => (int)$excluirId]);
            if ($e = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return sprintf('Ya existe un estudiante con el C.I. %s: %s.', $e['Ci'], $nombreDe($e));
            }
        }

        if (trim((string)$correo) !== '') {
            $stmt = $pdo->prepare("SELECT EstudianteID, Ci, Nombre, Apaterno, Amaterno FROM estudiante WHERE Correo = :correo AND EstudianteID <> :excluir LIMIT 1");
            $stmt->execute([':correo' => trim((string)$correo), ':excluir' => (int)$excluirId]);
            if ($e = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return sprintf('El correo %s ya está registrado para el estudiante %s (C.I. %s). Use otro correo.', trim((string)$correo), $nombreDe($e), $e['Ci']);
            }
        }

        return null;
    }

    /* REGISTRAR CON TODOS LOS CAMPOS */
   /**
    * @return string 'exitoso', o el motivo por el que no se guardó (C.I./correo repetido)
    */
   public static function RegistrarEstudianteModelo($DatosEstudiante)
{
    $motivo = self::MotivoDatoRepetidoModelo($DatosEstudiante['Ci'], $DatosEstudiante['Correo']);
    if ($motivo !== null) {
        return $motivo;
    }

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

    // Universidad y fecha de egreso: obligatorias, se validan antes con ValidarDatosEgresoModelo().
    $idUniversidad = $DatosEstudiante['IdUniversidad'] ?? null;
    $fechaEgreso = $DatosEstudiante['FechaEgreso'] ?? null;
    $stmt->bindValue(":IdUniversidad", $idUniversidad, $idUniversidad === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmt->bindValue(":FechaEgreso", $fechaEgreso, $fechaEgreso === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

    try {
        return $stmt->execute() ? 'exitoso' : 'No se pudo registrar al estudiante';
    } catch (PDOException $e) {
        // Otro usuario lo registró al mismo tiempo: se informa qué dato se repitió
        if ($e->getCode() === '23000') {
            return self::MotivoDatoRepetidoModelo($DatosEstudiante['Ci'], $DatosEstudiante['Correo'])
                ?? 'El C.I. o el correo ya están registrados para otro estudiante';
        }
        throw $e;
    }
}
    
 

    /* ACTUALIZAR DATOS DE UN ESTUDIANTE EXISTENTE (no toca Ci ni Estado) */
    /** @return string 'exitoso', o el motivo por el que no se guardó (correo de otro estudiante) */
    public static function ActualizarEstudianteModelo($EstudianteID, $DatosEstudiante)
    {
        $motivo = self::MotivoDatoRepetidoModelo(null, $DatosEstudiante['Correo'], $EstudianteID);
        if ($motivo !== null) {
            return $motivo;
        }

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

        try {
            return $stmt->execute() ? 'exitoso' : 'No se pudo actualizar al estudiante';
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                return self::MotivoDatoRepetidoModelo(null, $DatosEstudiante['Correo'], $EstudianteID)
                    ?? 'El correo ya está registrado para otro estudiante';
            }
            throw $e;
        }
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
            // Índice único: otro usuario la registró al mismo tiempo => se devuelve esa
            if ($e->getCode() === '23000' && ($existente = self::BuscarPorNombreModelo($nombre))) {
                return ['status' => 'duplicado', 'mensaje' => 'Esa profesión ya existe', 'profesion' => $existente];
            }
            error_log("Error en RegistrarProfesionModelo: " . $e->getMessage());
            return ['status' => 'error', 'mensaje' => 'Error en el servidor: ' . $e->getMessage()];
        }
    }
}
?>
