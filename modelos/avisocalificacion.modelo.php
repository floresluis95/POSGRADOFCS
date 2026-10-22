<?php
require_once __DIR__ . '/../core/conexion.modelo.php';

/**
 * Avisos a los docentes para que registren las calificaciones (tabla avisocalificacion,
 * migración 29). Un módulo "falta calificar" cuando ya terminó (FechaFinal pasada), no
 * está cerrado ni validado, tiene docente y hay estudiantes activos del programa sin nota.
 * Mientras la tabla no exista, el listado funciona igual pero sin el historial de avisos.
 */
class AvisoCalificacionModelo
{
    // Condición de "módulo que falta calificar" sobre modulos m y programa p
    const SQL_PENDIENTE = "m.estadomodulo NOT IN ('VALIDADO', 'CERRADO', 'INACTIVO') AND p.Estado = 1
        AND m.DocenteID IS NOT NULL AND m.FechaFinal IS NOT NULL AND m.FechaFinal < CURDATE()";

    // Estudiantes activos del programa y cuántos ya tienen nota en el módulo
    const SQL_CONTEOS = "(SELECT COUNT(DISTINCT ep.EstudianteID) FROM estudianteprograma ep
                          WHERE ep.ProgramaID = p.ProgramaID AND ep.Estado = 'ACTIVO') AS TotalEstudiantes,
                         (SELECT COUNT(*) FROM calificacion c
                          WHERE c.Idmodulo = m.Idmodulo AND c.ProgramaId = p.ProgramaID AND c.Nota IS NOT NULL) AS TotalCalificados";

    private static function TablaExiste()
    {
        static $existe = null;
        if ($existe === null) {
            $existe = (bool)Conexion::Conectar()->query("SHOW TABLES LIKE 'avisocalificacion'")->fetchColumn();
        }
        return $existe;
    }

    /**
     * Módulos que faltan calificar, con su docente, avance y el último aviso enviado.
     * @return array
     */
    public static function ModulosPendientesModelo()
    {
        try {
            $ultimoAviso = self::TablaExiste()
                ? "(SELECT MAX(a.FechaEnvio) FROM avisocalificacion a WHERE a.Idmodulo = m.Idmodulo AND a.DocenteID = m.DocenteID) AS UltimoAviso,
                   (SELECT COUNT(*) FROM avisocalificacion a WHERE a.Idmodulo = m.Idmodulo AND a.DocenteID = m.DocenteID) AS NumAvisos,
                   (SELECT a.FechaLeido FROM avisocalificacion a WHERE a.Idmodulo = m.Idmodulo AND a.DocenteID = m.DocenteID
                    ORDER BY a.FechaEnvio DESC LIMIT 1) AS UltimoLeido"
                : "NULL AS UltimoAviso, 0 AS NumAvisos, NULL AS UltimoLeido";
            $stmt = Conexion::Conectar()->prepare(
                "SELECT * FROM (
                    SELECT m.Idmodulo, m.nombremodulo AS NombreModulo, m.codigomodulo AS CodigoModulo, m.FechaFinal,
                           m.DocenteID, TRIM(CONCAT_WS(' ', d.Nombre, d.Apaterno, d.Amaterno)) AS Docente,
                           p.ProgramaID, p.NombrePrograma, p.Sede, p.GradoAcademico,
                           " . self::SQL_CONTEOS . ",
                           $ultimoAviso
                    FROM modulos m
                    INNER JOIN programa p ON p.ProgramaID = m.ProgramaId
                    INNER JOIN docente d ON d.DocenteID = m.DocenteID
                    WHERE " . self::SQL_PENDIENTE . "
                 ) t
                 WHERE t.TotalEstudiantes > 0 AND t.TotalCalificados < t.TotalEstudiantes
                 ORDER BY t.FechaFinal, t.NombrePrograma, t.Idmodulo"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ModulosPendientesModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Registra un aviso por cada módulo (al docente asignado en ese momento). Solo se
     * avisa de módulos que de verdad faltan calificar.
     * @return int|string cantidad de avisos registrados, o 'sin_tabla' / 'error'
     */
    public static function NotificarModelo(array $idsModulo, $mensaje, $responsable)
    {
        if (!self::TablaExiste()) {
            return 'sin_tabla';
        }
        $pendientes = [];
        foreach (self::ModulosPendientesModelo() as $m) {
            $pendientes[(int)$m['Idmodulo']] = (int)$m['DocenteID'];
        }
        try {
            $pdo = Conexion::Conectar();
            $stmt = $pdo->prepare(
                "INSERT INTO avisocalificacion (Idmodulo, DocenteID, Mensaje, FechaEnvio, EnviadoPor)
                 VALUES (:modulo, :docente, :mensaje, NOW(), :responsable)"
            );
            $enviados = 0;
            $pdo->beginTransaction();
            foreach (array_unique(array_map('intval', $idsModulo)) as $id) {
                if (!isset($pendientes[$id])) continue;
                $stmt->execute([':modulo' => $id, ':docente' => $pendientes[$id], ':mensaje' => $mensaje, ':responsable' => $responsable]);
                $enviados++;
            }
            $pdo->commit();
            return $enviados;
        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            error_log("Error en NotificarModelo: " . $e->getMessage());
            return 'error';
        }
    }

    /**
     * Avisos sin leer del docente, solo de módulos que todavía faltan calificar (si ya los
     * calificó o los cerraron, el aviso deja de mostrarse).
     * @return array
     */
    public static function AvisosDocenteModelo($docenteID)
    {
        if (!self::TablaExiste() || !(int)$docenteID) {
            return [];
        }
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT * FROM (
                    SELECT a.IdAviso, a.Mensaje, a.FechaEnvio, a.EnviadoPor,
                           m.Idmodulo, m.nombremodulo AS NombreModulo, m.codigomodulo AS CodigoModulo, m.FechaFinal,
                           p.NombrePrograma, " . self::SQL_CONTEOS . "
                    FROM avisocalificacion a
                    INNER JOIN modulos m ON m.Idmodulo = a.Idmodulo AND m.DocenteID = a.DocenteID
                    INNER JOIN programa p ON p.ProgramaID = m.ProgramaId
                    WHERE a.DocenteID = :docente AND a.FechaLeido IS NULL AND " . self::SQL_PENDIENTE . "
                 ) t
                 WHERE t.TotalCalificados < t.TotalEstudiantes
                 ORDER BY t.FechaEnvio DESC"
            );
            $stmt->execute([':docente' => (int)$docenteID]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en AvisosDocenteModelo: " . $e->getMessage());
            return [];
        }
    }

    // "Entendido": marca como leídos los avisos pendientes del docente
    public static function MarcarLeidosModelo($docenteID)
    {
        if (!self::TablaExiste() || !(int)$docenteID) {
            return false;
        }
        try {
            $stmt = Conexion::Conectar()->prepare(
                "UPDATE avisocalificacion SET FechaLeido = NOW() WHERE DocenteID = :docente AND FechaLeido IS NULL"
            );
            return $stmt->execute([':docente' => (int)$docenteID]);
        } catch (PDOException $e) {
            error_log("Error en MarcarLeidosModelo: " . $e->getMessage());
            return false;
        }
    }
}
