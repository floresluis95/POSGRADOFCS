<?php
/**
 * Modelo de Calificaciones
 * Gestiona las calificaciones finales de los estudiantes
 */

require_once 'conexion.modelo.php';

class CalificacionModelo
{
    /**
     * Obtener grados académicos disponibles
     * @return array
     */
    public static function ObtenerGradosAcademicosModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT DISTINCT GradoAcademico
                FROM programa
                WHERE Estado = 1
                ORDER BY GradoAcademico ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerGradosAcademicosModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener programas por grado académico
     * @param string $gradoAcademico
     * @return array
     */
    public static function ObtenerProgramasPorGradoModelo($gradoAcademico)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    ProgramaID,
                    NombrePrograma,
                    Codigo,
                    GradoAcademico
                FROM programa
                WHERE GradoAcademico = :gradoAcademico
                AND Estado = 1
                ORDER BY NombrePrograma ASC"
            );
            $stmt->bindParam(":gradoAcademico", $gradoAcademico, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerProgramasPorGradoModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener módulos asignados a un docente por programa
     * @param int $docenteID
     * @param int $programaID
     * @return array
     */
    public static function ObtenerModulosDocentePorProgramaModelo($docenteID, $programaID)
    {
        try {
            // Primero verificar qué tabla de módulos usar
            // Verificamos si existe la tabla 'modulo' con el campo ProgramaID
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    ModuloID as Idmodulo,
                    NombreModulo as nombremodulo,
                    Codigo as codigomodulo,
                    DocenteID
                FROM modulo
                WHERE ProgramaID = :programaID
                AND DocenteID = :docenteID
                AND Estado = 1
                ORDER BY Codigo ASC"
            );
            $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmt->bindParam(":docenteID", $docenteID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Si falla, intentar con la tabla 'modulos' (con 's')
            try {
                $stmt = Conexion::Conectar()->prepare(
                    "SELECT DISTINCT
                        m.Idmodulo,
                        m.nombremodulo,
                        m.codigomodulo,
                        m.DocenteID
                    FROM modulos m
                    INNER JOIN estudianteprograma ep ON m.idinscripcion = ep.idInscripcion
                    WHERE ep.ProgramaID = :programaID
                    AND m.DocenteID = :docenteID
                    AND m.estadomodulo = 'ACTIVO'
                    ORDER BY m.codigomodulo ASC"
                );
                $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
                $stmt->bindParam(":docenteID", $docenteID, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e2) {
                error_log("Error en ObtenerModulosDocentePorProgramaModelo: " . $e2->getMessage());
                return [];
            }
        }
    }

    /**
     * Obtener estudiantes inscritos en un módulo específico
     * @param int $moduloID
     * @param int $programaID
     * @return array
     */
    public static function ObtenerEstudiantesPorModuloModelo($moduloID, $programaID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    e.EstudianteID,
                    e.Nombre,
                    e.Apaterno,
                    e.Amaterno,
                    e.Ci,
                    c.CalificacionID,
                    c.Nota,
                    c.FechaRegistro
                FROM estudiante e
                INNER JOIN estudianteprograma ep ON e.EstudianteID = ep.EstudianteID
                LEFT JOIN calificacion c ON e.EstudianteID = c.EstudianteID
                    AND c.Idmodulo = :moduloID
                    AND c.ProgramaId = :programaID
                WHERE ep.ProgramaID = :programaID
                AND ep.Estado = 'ACTIVO'
                ORDER BY e.Apaterno, e.Amaterno, e.Nombre ASC"
            );
            $stmt->bindParam(":moduloID", $moduloID, PDO::PARAM_INT);
            $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerEstudiantesPorModuloModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Guardar o actualizar calificaciones
     * @param array $datos
     * @return string
     */
    public static function GuardarCalificacionesModelo($datos)
    {
        try {
            $pdo = Conexion::Conectar();
            $pdo->beginTransaction();

            $programaID = $datos['programaID'];
            $moduloID = $datos['moduloID'];
            $calificaciones = $datos['calificaciones'];
            $fechaRegistro = date('Y-m-d');

            $stmt = $pdo->prepare(
                "INSERT INTO calificacion
                (EstudianteID, ProgramaId, Idmodulo, Nota, FechaRegistro)
                VALUES (:estudianteID, :programaID, :moduloID, :nota, :fechaRegistro)
                ON DUPLICATE KEY UPDATE
                Nota = :nota,
                FechaRegistro = :fechaRegistro"
            );

            $guardados = 0;
            foreach ($calificaciones as $calificacion) {
                $estudianteID = $calificacion['estudianteID'];
                $nota = $calificacion['nota'];

                // Validar nota
                if ($nota < 0 || $nota > 100) {
                    continue;
                }

                $stmt->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
                $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
                $stmt->bindParam(":moduloID", $moduloID, PDO::PARAM_INT);
                $stmt->bindParam(":nota", $nota, PDO::PARAM_STR);
                $stmt->bindParam(":fechaRegistro", $fechaRegistro, PDO::PARAM_STR);

                if ($stmt->execute()) {
                    $guardados++;
                }
            }

            if ($guardados > 0) {
                $pdo->commit();
                return "exitoso";
            } else {
                $pdo->rollBack();
                return "error";
            }

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en GuardarCalificacionesModelo: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Listar todos los docentes
     * @return array
     */
    public static function ListarTodosLosDocentesModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    DocenteID,
                    Ci,
                    Complemento,
                    Exp,
                    Nombre,
                    Apaterno,
                    Amaterno,
                    Especialidad,
                    CedulaProfesional,
                    Direccion
                FROM docente
                ORDER BY Apaterno, Amaterno, Nombre ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarTodosLosDocentesModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todas las asignaciones de un docente (módulos asignados con detalle)
     * @param int $docenteID
     * @return array
     */
    public static function ObtenerAsignacionesDocenteModelo($docenteID)
    {
        try {
            // Consulta para tabla modulos (con 's') usando ProgramaId directamente
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    m.Idmodulo,
                    m.nombremodulo,
                    m.codigomodulo,
                    m.DocenteID,
                    p.ProgramaID,
                    p.NombrePrograma,
                    p.GradoAcademico,
                    p.Codigo as CodigoPrograma,
                    (SELECT COUNT(DISTINCT EstudianteID)
                     FROM estudianteprograma
                     WHERE ProgramaID = p.ProgramaID
                     AND Estado = 'ACTIVO') as TotalEstudiantes
                FROM modulos m
                INNER JOIN programa p ON m.ProgramaId = p.ProgramaID
                WHERE m.DocenteID = :docenteID
                AND m.estadomodulo = 'ACTIVO'
                ORDER BY p.GradoAcademico, p.NombrePrograma, m.codigomodulo"
            );
            $stmt->bindParam(":docenteID", $docenteID, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($result) > 0) {
                return $result;
            }

            // Si no hay resultados, intentar con tabla modulo (sin 's')
            $stmt2 = Conexion::Conectar()->prepare(
                "SELECT
                    m.ModuloID as Idmodulo,
                    m.NombreModulo as nombremodulo,
                    m.Codigo as codigomodulo,
                    m.DocenteID,
                    p.ProgramaID,
                    p.NombrePrograma,
                    p.GradoAcademico,
                    p.Codigo as CodigoPrograma,
                    (SELECT COUNT(DISTINCT EstudianteID)
                     FROM estudianteprograma
                     WHERE ProgramaID = p.ProgramaID
                     AND Estado = 'ACTIVO') as TotalEstudiantes
                FROM modulo m
                INNER JOIN programa p ON m.ProgramaID = p.ProgramaID
                WHERE m.DocenteID = :docenteID
                AND m.Estado = 1
                ORDER BY p.GradoAcademico, p.NombrePrograma, m.Codigo"
            );
            $stmt2->bindParam(":docenteID", $docenteID, PDO::PARAM_INT);
            $stmt2->execute();
            return $stmt2->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en ObtenerAsignacionesDocenteModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener información del docente por CI (usuario)
     * @param string $ci - CI del docente (que coincide con usuario.Usuario)
     * @return array|null
     */
    public static function ObtenerDocentePorCIModelo($ci)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    DocenteID,
                    Ci,
                    Complemento,
                    Exp,
                    Nombre,
                    Apaterno,
                    Amaterno,
                    Especialidad
                FROM docente
                WHERE Ci = :ci
                LIMIT 1"
            );
            $stmt->bindParam(":ci", $ci, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerDocentePorCIModelo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener información del docente por ID de usuario
     * @param int $usuarioID
     * @return array|null
     */
    public static function ObtenerDocentePorUsuarioModelo($usuarioID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    d.DocenteID,
                    d.Nombre,
                    d.Apaterno,
                    d.Amaterno,
                    d.Ci
                FROM docente d
                INNER JOIN usuario u ON d.Ci = u.Usuario
                WHERE u.IdUsuario = :usuarioID
                LIMIT 1"
            );
            $stmt->bindParam(":usuarioID", $usuarioID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerDocentePorUsuarioModelo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verificar si existe índice único en la tabla calificacion
     * Si no existe, lo crea para evitar duplicados
     */
    public static function VerificarIndiceUnicoCalificacion()
    {
        try {
            $pdo = Conexion::Conectar();

            // Verificar si existe el índice
            $stmt = $pdo->prepare(
                "SHOW INDEX FROM calificacion WHERE Key_name = 'idx_estudiante_programa_modulo'"
            );
            $stmt->execute();
            $result = $stmt->fetch();

            if (!$result) {
                // Crear índice único
                $pdo->exec(
                    "ALTER TABLE calificacion
                    ADD UNIQUE INDEX idx_estudiante_programa_modulo (EstudianteID, ProgramaId, Idmodulo)"
                );
                error_log("Índice único creado en tabla calificacion");
            }
        } catch (PDOException $e) {
            error_log("Error al verificar índice único: " . $e->getMessage());
        }
    }
}

// Verificar índice único al cargar el modelo
CalificacionModelo::VerificarIndiceUnicoCalificacion();
?>
