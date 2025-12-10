<?php
/**
 * Modelo de Reporte de Notas
 * Gestiona los reportes de calificaciones por programa y módulo
 */

require_once 'conexion.modelo.php';

class ReporteNotasModelo
{
    /**
     * Obtener todos los programas activos con información de módulos
     * @return array
     */
    public static function ObtenerTodosProgramasConModulosModelo()
    {
        try {
            $conexion = Conexion::Conectar();

            $stmt = $conexion->prepare("
                SELECT
                    p.ProgramaID,
                    p.NombrePrograma,
                    p.GradoAcademico,
                    p.Codigo as CodigoPrograma,
                    p.FechaInicio,
                    p.DuracionMeses,
                    p.Sede,
                    COUNT(DISTINCT m.Idmodulo) as TotalModulos,
                    COUNT(DISTINCT ep.EstudianteID) as TotalEstudiantes
                FROM programa p
                LEFT JOIN modulos m ON p.ProgramaID = m.ProgramaId AND m.estadomodulo = 'ACTIVO'
                LEFT JOIN estudianteprograma ep ON p.ProgramaID = ep.ProgramaID AND ep.Estado = 'ACTIVO'
                WHERE p.Estado = 1
                GROUP BY p.ProgramaID
                ORDER BY p.GradoAcademico, p.NombrePrograma
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerTodosProgramasConModulosModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener módulos de un programa específico
     * @param int $programaID
     * @return array
     */
    public static function ObtenerModulosPorProgramaModelo($programaID)
    {
        try {
            $conexion = Conexion::Conectar();

            $stmt = $conexion->prepare("
                SELECT
                    m.Idmodulo,
                    m.nombremodulo,
                    m.codigomodulo,
                    m.DocenteID,
                    m.ProgramaId,
                    CONCAT(d.Nombre, ' ', d.Apaterno, ' ', d.Amaterno) as NombreDocente,
                    d.Especialidad as EspecialidadDocente,
                    p.NombrePrograma,
                    p.GradoAcademico,
                    COUNT(DISTINCT c.EstudianteID) as TotalCalificados,
                    COUNT(DISTINCT ep.EstudianteID) as TotalInscritos
                FROM modulos m
                LEFT JOIN programa p ON m.ProgramaId = p.ProgramaID
                LEFT JOIN docente d ON m.DocenteID = d.DocenteID
                LEFT JOIN estudianteprograma ep ON ep.ProgramaID = m.ProgramaId
                LEFT JOIN calificacion c ON c.Idmodulo = m.Idmodulo AND c.ProgramaId = m.ProgramaId
                WHERE m.ProgramaId = :programaID
                AND m.estadomodulo = 'ACTIVO'
                GROUP BY m.Idmodulo
                ORDER BY m.codigomodulo ASC
            ");
            $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerModulosPorProgramaModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estudiantes con calificaciones de un módulo específico
     * @param int $moduloID
     * @param int $programaID
     * @return array
     */
    public static function ObtenerEstudiantesConNotasModelo($moduloID, $programaID)
    {
        try {
            $conexion = Conexion::Conectar();

            $stmt = $conexion->prepare("
                SELECT
                    e.EstudianteID,
                    e.Ci,
                    e.Complemento,
                    e.Exp,
                    e.Nombre,
                    e.Apaterno,
                    e.Amaterno,
                    c.Nota,
                    c.FechaRegistro,
                    c.FechaModificacion,
                    c.UsuarioRegistroID,
                    c.UsuarioModificacionID,
                    CASE
                        WHEN c.Nota >= 51 THEN 'APROBADO'
                        WHEN c.Nota IS NULL THEN 'PENDIENTE'
                        ELSE 'REPROBADO'
                    END as Estado,
                    -- Información del usuario que registró
                    CASE
                        WHEN ureg.DocenteID IS NOT NULL THEN CONCAT(dreg.Nombre, ' ', dreg.Apaterno)
                        WHEN ureg.EstudianteID IS NOT NULL THEN CONCAT(ereg.Nombre, ' ', ereg.Apaterno)
                        ELSE ureg.Usuario
                    END as UsuarioRegistro,
                    ureg.Tipo as TipoUsuarioRegistro,
                    -- Información del usuario que modificó
                    CASE
                        WHEN umod.DocenteID IS NOT NULL THEN CONCAT(dmod.Nombre, ' ', dmod.Apaterno)
                        WHEN umod.EstudianteID IS NOT NULL THEN CONCAT(emod.Nombre, ' ', emod.Apaterno)
                        ELSE umod.Usuario
                    END as UsuarioModificacion,
                    umod.Tipo as TipoUsuarioModificacion
                FROM estudianteprograma ep
                INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
                LEFT JOIN calificacion c ON e.EstudianteID = c.EstudianteID
                    AND c.Idmodulo = :moduloID
                    AND c.ProgramaId = :programaID
                -- Joins para usuario de registro
                LEFT JOIN usuario ureg ON c.UsuarioRegistroID = ureg.ID
                LEFT JOIN docente dreg ON ureg.DocenteID = dreg.DocenteID
                LEFT JOIN estudiante ereg ON ureg.EstudianteID = ereg.EstudianteID
                -- Joins para usuario de modificación
                LEFT JOIN usuario umod ON c.UsuarioModificacionID = umod.ID
                LEFT JOIN docente dmod ON umod.DocenteID = dmod.DocenteID
                LEFT JOIN estudiante emod ON umod.EstudianteID = emod.EstudianteID
                WHERE ep.ProgramaID = :programaID
                AND ep.Estado = 'ACTIVO'
                ORDER BY e.Apaterno, e.Amaterno, e.Nombre ASC
            ");
            $stmt->bindParam(":moduloID", $moduloID, PDO::PARAM_INT);
            $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerEstudiantesConNotasModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener datos completos para el reporte PDF
     * @param int $moduloID
     * @param int $programaID
     * @return array
     */
    public static function ObtenerDatosParaReportePDFModelo($moduloID, $programaID)
    {
        try {
            $conexion = Conexion::Conectar();

            // Obtener información del programa
            $stmtPrograma = $conexion->prepare("
                SELECT
                    ProgramaID,
                    NombrePrograma,
                    GradoAcademico,
                    Codigo,
                    Sede
                FROM programa
                WHERE ProgramaID = :programaID
            ");
            $stmtPrograma->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmtPrograma->execute();
            $programa = $stmtPrograma->fetch(PDO::FETCH_ASSOC);

            // Obtener información del módulo
            $stmtModulo = $conexion->prepare("
                SELECT
                    m.Idmodulo,
                    m.nombremodulo,
                    m.codigomodulo,
                    m.DocenteID,
                    CONCAT(d.Nombre, ' ', d.Apaterno, ' ', d.Amaterno) as NombreDocente,
                    d.Especialidad as EspecialidadDocente,
                    CONCAT(d.Ci, IF(d.Complemento IS NOT NULL AND d.Complemento != '', CONCAT('-', d.Complemento), ''), ' ', d.Exp) as CIDocente
                FROM modulos m
                LEFT JOIN docente d ON m.DocenteID = d.DocenteID
                WHERE m.Idmodulo = :moduloID
                AND m.ProgramaId = :programaID
            ");
            $stmtModulo->bindParam(":moduloID", $moduloID, PDO::PARAM_INT);
            $stmtModulo->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmtModulo->execute();
            $modulo = $stmtModulo->fetch(PDO::FETCH_ASSOC);

            // Obtener estudiantes con notas
            $estudiantes = self::ObtenerEstudiantesConNotasModelo($moduloID, $programaID);

            return [
                'programa' => $programa,
                'modulo' => $modulo,
                'estudiantes' => $estudiantes,
                'fecha_generacion' => date('d/m/Y')
            ];

        } catch (PDOException $e) {
            error_log("Error en ObtenerDatosParaReportePDFModelo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Convertir nota numérica a literal
     * @param float $nota
     * @return string
     */
    public static function ConvertirNotaALiteral($nota)
    {
        if ($nota === null || $nota === '') {
            return 'PENDIENTE';
        }

        $nota = (int)$nota;

        $unidades = [
            '', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE',
            'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'
        ];

        $decenas = [
            '', '', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'
        ];

        if ($nota < 20) {
            return $unidades[$nota];
        } elseif ($nota < 30) {
            if ($nota == 20) return 'VEINTE';
            return 'VEINTI' . $unidades[$nota - 20];
        } elseif ($nota < 100) {
            $decena = floor($nota / 10);
            $unidad = $nota % 10;
            if ($unidad == 0) {
                return $decenas[$decena];
            } else {
                return $decenas[$decena] . ' Y ' . $unidades[$unidad];
            }
        } elseif ($nota == 100) {
            return 'CIEN';
        }

        return (string)$nota;
    }

    /**
     * Obtener auditoría completa de un módulo
     * @param int $moduloID
     * @param int $programaID
     * @return array
     */
    public static function ObtenerAuditoriaModuloModelo($moduloID, $programaID)
    {
        try {
            $conexion = Conexion::Conectar();

            $stmt = $conexion->prepare("
                SELECT
                    e.EstudianteID,
                    CONCAT(e.Nombre, ' ', e.Apaterno, ' ', e.Amaterno) as NombreEstudiante,
                    c.Nota,
                    c.FechaRegistro,
                    c.FechaModificacion,
                    -- Usuario que registró
                    CASE
                        WHEN ureg.DocenteID IS NOT NULL THEN CONCAT(dreg.Nombre, ' ', dreg.Apaterno, ' ', dreg.Amaterno)
                        WHEN ureg.EstudianteID IS NOT NULL THEN CONCAT(ereg.Nombre, ' ', ereg.Apaterno, ' ', ereg.Amaterno)
                        ELSE ureg.Usuario
                    END as UsuarioRegistro,
                    ureg.Tipo as TipoUsuarioRegistro,
                    -- Usuario que modificó
                    CASE
                        WHEN umod.DocenteID IS NOT NULL THEN CONCAT(dmod.Nombre, ' ', dmod.Apaterno, ' ', dmod.Amaterno)
                        WHEN umod.EstudianteID IS NOT NULL THEN CONCAT(emod.Nombre, ' ', emod.Apaterno, ' ', emod.Amaterno)
                        ELSE umod.Usuario
                    END as UsuarioModificacion,
                    umod.Tipo as TipoUsuarioModificacion,
                    -- Determinar si fue modificado
                    CASE
                        WHEN c.FechaModificacion IS NOT NULL THEN 'SÍ'
                        ELSE 'NO'
                    END as FueModificado
                FROM calificacion c
                INNER JOIN estudiante e ON c.EstudianteID = e.EstudianteID
                LEFT JOIN usuario ureg ON c.UsuarioRegistroID = ureg.ID
                LEFT JOIN docente dreg ON ureg.DocenteID = dreg.DocenteID
                LEFT JOIN estudiante ereg ON ureg.EstudianteID = ereg.EstudianteID
                LEFT JOIN usuario umod ON c.UsuarioModificacionID = umod.ID
                LEFT JOIN docente dmod ON umod.DocenteID = dmod.DocenteID
                LEFT JOIN estudiante emod ON umod.EstudianteID = emod.EstudianteID
                WHERE c.Idmodulo = :moduloID
                AND c.ProgramaId = :programaID
                ORDER BY c.FechaModificacion DESC, c.FechaRegistro DESC
            ");
            $stmt->bindParam(":moduloID", $moduloID, PDO::PARAM_INT);
            $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerAuditoriaModuloModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener resumen de auditoría de un módulo
     * @param int $moduloID
     * @param int $programaID
     * @return array
     */
    public static function ObtenerResumenAuditoriaModelo($moduloID, $programaID)
    {
        try {
            $conexion = Conexion::Conectar();

            $stmt = $conexion->prepare("
                SELECT
                    COUNT(*) as TotalCalificaciones,
                    SUM(CASE WHEN FechaModificacion IS NOT NULL THEN 1 ELSE 0 END) as TotalModificadas,
                    COUNT(DISTINCT UsuarioRegistroID) as TotalUsuariosRegistraron,
                    COUNT(DISTINCT UsuarioModificacionID) as TotalUsuariosModificaron,
                    MIN(FechaRegistro) as PrimeraFechaRegistro,
                    MAX(FechaRegistro) as UltimaFechaRegistro,
                    MAX(FechaModificacion) as UltimaModificacion
                FROM calificacion
                WHERE Idmodulo = :moduloID
                AND ProgramaId = :programaID
            ");
            $stmt->bindParam(":moduloID", $moduloID, PDO::PARAM_INT);
            $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerResumenAuditoriaModelo: " . $e->getMessage());
            return null;
        }
    }
}
?>
