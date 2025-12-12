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

            // Obtener el ID del usuario actual de la sesión
            $usuarioRegistroID = null;
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // El sistema guarda $_SESSION['Usuario'] pero no $_SESSION['ID']
            // Necesitamos obtener el ID basándonos en el Usuario
            if (isset($_SESSION['Usuario'])) {
                $stmtUser = $pdo->prepare("SELECT ID FROM usuario WHERE Usuario = :usuario LIMIT 1");
                $stmtUser->bindParam(":usuario", $_SESSION['Usuario'], PDO::PARAM_STR);
                $stmtUser->execute();
                $userRow = $stmtUser->fetch(PDO::FETCH_ASSOC);
                if ($userRow) {
                    $usuarioRegistroID = $userRow['ID'];
                }
            }

            $guardados = 0;
            $estado = 'REGISTRADO'; // Estado por defecto para las calificaciones

            foreach ($calificaciones as $calificacion) {
                $estudianteID = $calificacion['estudianteID'];
                $nota = $calificacion['nota'];

                // Validar nota
                if ($nota < 0 || $nota > 100) {
                    error_log("Nota fuera de rango: $nota para estudiante: $estudianteID");
                    continue;
                }

                // Log para debug
                error_log("Guardando calificación: EstudianteID=$estudianteID, ProgramaID=$programaID, ModuloID=$moduloID, Nota=$nota");

                // Verificar si ya existe la calificación para determinar si es INSERT o UPDATE
                $stmtCheck = $pdo->prepare(
                    "SELECT CalificacionID FROM calificacion
                    WHERE EstudianteID = :estudianteID
                    AND ProgramaId = :programaID
                    AND Idmodulo = :moduloID"
                );
                $stmtCheck->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
                $stmtCheck->bindParam(":programaID", $programaID, PDO::PARAM_INT);
                $stmtCheck->bindParam(":moduloID", $moduloID, PDO::PARAM_INT);
                $stmtCheck->execute();
                $existeCalificacion = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                // Preparar statement individual para cada calificación
                $stmt = $pdo->prepare(
                    "INSERT INTO calificacion
                    (EstudianteID, ProgramaId, Idmodulo, Nota, estado, FechaRegistro, UsuarioRegistroID)
                    VALUES (:estudianteID, :programaID, :moduloID, :nota, :estado, :fechaRegistro, :usuarioRegistroID)
                    ON DUPLICATE KEY UPDATE
                    Nota = VALUES(Nota),
                    estado = VALUES(estado),
                    UsuarioModificacionID = :usuarioModificacionID,
                    FechaModificacion = NOW()"
                );

                $stmt->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
                $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
                $stmt->bindParam(":moduloID", $moduloID, PDO::PARAM_INT);
                $stmt->bindParam(":nota", $nota, PDO::PARAM_STR);
                $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
                $stmt->bindParam(":fechaRegistro", $fechaRegistro, PDO::PARAM_STR);

                // Manejar usuarioRegistroID como NULL si no se encontró
                if ($usuarioRegistroID === null) {
                    $stmt->bindValue(":usuarioRegistroID", null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindParam(":usuarioRegistroID", $usuarioRegistroID, PDO::PARAM_INT);
                }

                // Bind para UsuarioModificacionID (solo se usa en UPDATE)
                if ($usuarioRegistroID === null) {
                    $stmt->bindValue(":usuarioModificacionID", null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindParam(":usuarioModificacionID", $usuarioRegistroID, PDO::PARAM_INT);
                }

                try {
                    if ($stmt->execute()) {
                        $guardados++;
                        error_log("Calificación guardada exitosamente para estudiante: $estudianteID");
                    } else {
                        error_log("Error al ejecutar statement para estudiante: $estudianteID - " . print_r($stmt->errorInfo(), true));
                    }
                } catch (PDOException $e) {
                    error_log("Error PDO al guardar calificación: " . $e->getMessage());
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
                     AND Estado = 'ACTIVO') as TotalEstudiantes,
                    (SELECT COUNT(*)
                     FROM calificacion c
                     WHERE c.Idmodulo = m.Idmodulo
                     AND c.ProgramaId = p.ProgramaID
                     AND c.Nota IS NOT NULL) as TotalCalificados,
                    (SELECT MAX(c.FechaRegistro)
                     FROM calificacion c
                     WHERE c.Idmodulo = m.Idmodulo
                     AND c.ProgramaId = p.ProgramaID) as UltimaCalificacion,
                    (SELECT c.estado
                     FROM calificacion c
                     WHERE c.Idmodulo = m.Idmodulo
                     AND c.ProgramaId = p.ProgramaID
                     LIMIT 1) as EstadoCalificacion,
                    (SELECT
                        CASE
                            WHEN u.DocenteID IS NOT NULL THEN CONCAT(d.Nombre, ' ', d.Apaterno)
                            WHEN u.EstudianteID IS NOT NULL THEN CONCAT(e.Nombre, ' ', e.Apaterno)
                            ELSE u.Usuario
                        END
                     FROM calificacion c
                     LEFT JOIN usuario u ON c.UsuarioRegistroID = u.ID
                     LEFT JOIN docente d ON u.DocenteID = d.DocenteID
                     LEFT JOIN estudiante e ON u.EstudianteID = e.EstudianteID
                     WHERE c.Idmodulo = m.Idmodulo
                     AND c.ProgramaId = p.ProgramaID
                     AND c.UsuarioRegistroID IS NOT NULL
                     LIMIT 1) as RegistradoPor,
                    (SELECT u.Tipo
                     FROM calificacion c
                     LEFT JOIN usuario u ON c.UsuarioRegistroID = u.ID
                     WHERE c.Idmodulo = m.Idmodulo
                     AND c.ProgramaId = p.ProgramaID
                     AND c.UsuarioRegistroID IS NOT NULL
                     LIMIT 1) as TipoUsuarioRegistro
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
                     AND Estado = 'ACTIVO') as TotalEstudiantes,
                    (SELECT COUNT(*)
                     FROM calificacion c
                     WHERE c.Idmodulo = m.ModuloID
                     AND c.ProgramaId = p.ProgramaID
                     AND c.Nota IS NOT NULL) as TotalCalificados,
                    (SELECT MAX(c.FechaRegistro)
                     FROM calificacion c
                     WHERE c.Idmodulo = m.ModuloID
                     AND c.ProgramaId = p.ProgramaID) as UltimaCalificacion,
                    (SELECT c.estado
                     FROM calificacion c
                     WHERE c.Idmodulo = m.ModuloID
                     AND c.ProgramaId = p.ProgramaID
                     LIMIT 1) as EstadoCalificacion,
                    (SELECT
                        CASE
                            WHEN u.DocenteID IS NOT NULL THEN CONCAT(d.Nombre, ' ', d.Apaterno)
                            WHEN u.EstudianteID IS NOT NULL THEN CONCAT(e.Nombre, ' ', e.Apaterno)
                            ELSE u.Usuario
                        END
                     FROM calificacion c
                     LEFT JOIN usuario u ON c.UsuarioRegistroID = u.ID
                     LEFT JOIN docente d ON u.DocenteID = d.DocenteID
                     LEFT JOIN estudiante e ON u.EstudianteID = e.EstudianteID
                     WHERE c.Idmodulo = m.ModuloID
                     AND c.ProgramaId = p.ProgramaID
                     AND c.UsuarioRegistroID IS NOT NULL
                     LIMIT 1) as RegistradoPor,
                    (SELECT u.Tipo
                     FROM calificacion c
                     LEFT JOIN usuario u ON c.UsuarioRegistroID = u.ID
                     WHERE c.Idmodulo = m.ModuloID
                     AND c.ProgramaId = p.ProgramaID
                     AND c.UsuarioRegistroID IS NOT NULL
                     LIMIT 1) as TipoUsuarioRegistro
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
     * Buscar calificaciones por diferentes criterios
     * @param string $tipoBusqueda - 'ci', 'nombre', 'apellido'
     * @param string $valor - Valor a buscar
     * @param int|null $programaID - ID del programa (opcional)
     * @return array
     */
    public static function BuscarCalificacionesEstudianteModelo($tipoBusqueda, $valor, $programaID = null)
    {
        try {
            $sql = "SELECT
                        e.EstudianteID,
                        e.Ci,
                        e.Complemento,
                        e.Exp,
                        e.Nombre,
                        e.Apaterno,
                        e.Amaterno,
                        m.nombremodulo as NombreModulo,
                        m.codigomodulo as CodigoModulo,
                        c.Nota,
                        c.estado as Estado,
                        c.FechaRegistro,
                        p.NombrePrograma,
                        p.GradoAcademico
                    FROM calificacion c
                    INNER JOIN estudiante e ON c.EstudianteID = e.EstudianteID
                    INNER JOIN modulos m ON c.Idmodulo = m.Idmodulo
                    INNER JOIN programa p ON c.ProgramaId = p.ProgramaID
                    WHERE 1=1";

            $params = [];

            // Agregar condición según tipo de búsqueda
            switch ($tipoBusqueda) {
                case 'ci':
                    $sql .= " AND e.Ci LIKE :valor";
                    $params[':valor'] = "%$valor%";
                    break;
                case 'nombre':
                    $sql .= " AND e.Nombre LIKE :valor";
                    $params[':valor'] = "%$valor%";
                    break;
                case 'apellido':
                    $sql .= " AND (e.Apaterno LIKE :valor OR e.Amaterno LIKE :valor)";
                    $params[':valor'] = "%$valor%";
                    break;
            }

            // Agregar filtro de programa si se especifica
            if ($programaID !== null && $programaID !== '') {
                $sql .= " AND c.ProgramaId = :programaID";
                $params[':programaID'] = $programaID;
            }

            $sql .= " ORDER BY e.Apaterno, e.Amaterno, e.Nombre, p.NombrePrograma, m.codigomodulo";

            $stmt = Conexion::Conectar()->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en BuscarCalificacionesEstudianteModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todas las calificaciones de un estudiante específico
     * @param int $estudianteID
     * @return array
     */
    public static function ObtenerCalificacionesPorEstudianteModelo($estudianteID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    c.CalificacionID,
                    c.Nota,
                    c.estado,
                    c.FechaRegistro,
                    m.nombremodulo as NombreModulo,
                    m.codigomodulo as CodigoModulo,
                    p.NombrePrograma,
                    p.GradoAcademico,
                    p.ProgramaID
                FROM calificacion c
                INNER JOIN modulos m ON c.Idmodulo = m.Idmodulo
                INNER JOIN programa p ON c.ProgramaId = p.ProgramaID
                WHERE c.EstudianteID = :estudianteID
                ORDER BY p.NombrePrograma, m.codigomodulo"
            );
            $stmt->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en ObtenerCalificacionesPorEstudianteModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener lista de programas con calificaciones registradas
     * @return array
     */
    public static function ObtenerProgramasConCalificacionesModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT DISTINCT
                    p.ProgramaID,
                    p.NombrePrograma,
                    p.Codigo,
                    p.GradoAcademico
                FROM programa p
                INNER JOIN calificacion c ON p.ProgramaID = c.ProgramaId
                WHERE p.Estado = 1
                ORDER BY p.GradoAcademico, p.NombrePrograma"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en ObtenerProgramasConCalificacionesModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estudiante por ID
     * @param int $estudianteID - ID del estudiante
     * @return array|null
     */
    public static function ObtenerEstudiantePorIDModelo($estudianteID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    EstudianteID,
                    Ci,
                    Complemento,
                    Exp,
                    Nombre,
                    Apaterno,
                    Amaterno,
                    Correo,
                    Telefono,
                    Celular,
                    Direccion,
                    Estado
                FROM estudiante
                WHERE EstudianteID = :estudianteID
                AND Estado = 1
                LIMIT 1"
            );
            $stmt->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerEstudiantePorIDModelo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener estudiante por CI (usuario)
     * @param string $ci - CI del estudiante
     * @return array|null
     */
    public static function ObtenerEstudiantePorCIModelo($ci)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    EstudianteID,
                    Ci,
                    Complemento,
                    Exp,
                    Nombre,
                    Apaterno,
                    Amaterno,
                    Correo,
                    Telefono,
                    Celular,
                    Direccion,
                    Estado
                FROM estudiante
                WHERE Ci = :ci
                AND Estado = 1
                LIMIT 1"
            );
            $stmt->bindParam(":ci", $ci, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerEstudiantePorCIModelo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener programas inscritos de un estudiante
     * @param int $estudianteID
     * @return array
     */
    public static function ObtenerProgramasEstudianteModelo($estudianteID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT DISTINCT
                    p.ProgramaID,
                    p.NombrePrograma,
                    p.Codigo,
                    p.GradoAcademico,
                    ep.FechaInscripcion,
                    ep.Estado,
                    (SELECT COUNT(*)
                     FROM calificacion c
                     WHERE c.EstudianteID = :estudianteID
                     AND c.ProgramaId = p.ProgramaID) as TotalModulosConNota
                FROM programa p
                INNER JOIN estudianteprograma ep ON p.ProgramaID = ep.ProgramaID
                WHERE ep.EstudianteID = :estudianteID
                AND ep.Estado = 'ACTIVO'
                ORDER BY ep.FechaInscripcion DESC, p.NombrePrograma"
            );
            $stmt->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerProgramasEstudianteModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener calificaciones de un estudiante por programa
     * @param int $estudianteID
     * @param int $programaID
     * @return array
     */
    public static function ObtenerCalificacionesEstudianteProgramaModelo($estudianteID, $programaID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    c.CalificacionID,
                    c.Nota,
                    c.estado,
                    c.FechaRegistro,
                    m.nombremodulo as NombreModulo,
                    m.codigomodulo as CodigoModulo,
                    m.Idmodulo,
                    d.Nombre as DocenteNombre,
                    d.Apaterno as DocenteApaterno,
                    d.Amaterno as DocenteAmaterno,
                    p.NombrePrograma,
                    p.GradoAcademico
                FROM calificacion c
                INNER JOIN modulos m ON c.Idmodulo = m.Idmodulo
                INNER JOIN programa p ON c.ProgramaId = p.ProgramaID
                LEFT JOIN docente d ON m.DocenteID = d.DocenteID
                WHERE c.EstudianteID = :estudianteID
                AND c.ProgramaId = :programaID
                ORDER BY m.codigomodulo ASC"
            );
            $stmt->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
            $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerCalificacionesEstudianteProgramaModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener resumen de calificaciones de un estudiante
     * @param int $estudianteID
     * @param int $programaID
     * @return array
     */
    public static function ObtenerResumenCalificacionesModelo($estudianteID, $programaID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    COUNT(*) as TotalModulos,
                    SUM(CASE WHEN c.Nota >= 76 THEN 1 ELSE 0 END) as ModulosAprobados,
                    SUM(CASE WHEN c.Nota < 76 THEN 1 ELSE 0 END) as ModulosReprobados,
                    ROUND(AVG(c.Nota), 0) as PromedioGeneral,
                    MAX(c.Nota) as NotaMaxima,
                    MIN(c.Nota) as NotaMinima
                FROM calificacion c
                WHERE c.EstudianteID = :estudianteID
                AND c.ProgramaId = :programaID"
            );
            $stmt->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
            $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerResumenCalificacionesModelo: " . $e->getMessage());
            return [];
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
