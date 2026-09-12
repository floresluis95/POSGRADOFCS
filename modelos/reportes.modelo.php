<?php
require_once __DIR__ . '/conexion.modelo.php';

class ReportesModelos
{
    // Lista de estudiantes activos con toda su información
    public static function ListaEstudiantesCompleta()
    {
        $stmt = Conexion::Conectar()->prepare("
            SELECT e.*, p.NombreProfesion
            FROM estudiante e
            INNER JOIN profesion p ON e.IdProfesion = p.IdProfesion
            WHERE e.Estado = 1
            ORDER BY e.Apaterno, e.Amaterno, e.Nombre
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Estudiantes por programa y/o sede específicos
    public static function EstudiantesPorPrograma($programaID = null, $sede = null)
    {
        $condiciones = ["ep.Estado = 'ACTIVO'"];
        $params = [];

        if ($programaID) {
            $condiciones[] = "pr.ProgramaID = :programaID";
            $params[':programaID'] = $programaID;
        }
        if ($sede) {
            $condiciones[] = "pr.Sede = :sede";
            $params[':sede'] = $sede;
        }

        $sql = "
            SELECT e.*, p.NombreProfesion, pr.NombrePrograma, pr.Sede, pr.GradoAcademico, ep.FechaInscripcion
            FROM estudianteprograma ep
            INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
            INNER JOIN programa pr ON ep.ProgramaID = pr.ProgramaID
            INNER JOIN profesion p ON e.IdProfesion = p.IdProfesion
            WHERE " . implode(' AND ', $condiciones) . "
            ORDER BY pr.Sede, pr.NombrePrograma, e.Apaterno, e.Amaterno
        ";
        $stmt = Conexion::Conectar()->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Módulos con matriculados, calificados y estado de aprobación.
     * (Corregido: la version anterior apuntaba a una tabla `modulo`/`estudiantemodulo`
     * que no existe en la base real; usa `modulos` + `calificacion`.)
     */
    public static function ModulosConMatriculados($programaID = null, $sede = null)
    {
        $condiciones = ["m.estadomodulo != 'INACTIVO'"];
        $params = [];

        if ($programaID) {
            $condiciones[] = "m.ProgramaId = :programaID";
            $params[':programaID'] = $programaID;
        }
        if ($sede) {
            $condiciones[] = "p.Sede = :sede";
            $params[':sede'] = $sede;
        }

        $sql = "
            SELECT
                m.Idmodulo,
                m.codigomodulo as CodigoModulo,
                m.nombremodulo as NombreModulo,
                m.estadomodulo as EstadoModulo,
                p.NombrePrograma,
                p.Sede,
                CONCAT(d.Nombre, ' ', d.Apaterno) as Docente,
                (SELECT COUNT(*) FROM calificacion c WHERE c.Idmodulo = m.Idmodulo AND c.Nota IS NOT NULL) as TotalCalificados,
                (SELECT COUNT(*) FROM calificacion c WHERE c.Idmodulo = m.Idmodulo AND c.Nota >= 76) as TotalAprobados,
                (SELECT COUNT(*) FROM calificacion c WHERE c.Idmodulo = m.Idmodulo AND c.Nota IS NOT NULL AND c.Nota < 76) as TotalReprobados
            FROM modulos m
            INNER JOIN programa p ON m.ProgramaId = p.ProgramaID
            LEFT JOIN docente d ON m.DocenteID = d.DocenteID
            WHERE " . implode(' AND ', $condiciones) . "
            ORDER BY p.Sede, p.NombrePrograma, m.Idmodulo
        ";
        $stmt = Conexion::Conectar()->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Detalle de estudiantes inscritos en un módulo específico (vía calificación registrada o no)
    public static function EstudiantesEnModulo($idmodulo)
    {
        $stmt = Conexion::Conectar()->prepare("
            SELECT
                e.Ci, e.Complemento, e.Exp,
                e.Nombre, e.Apaterno, e.Amaterno,
                e.Celular, e.Correo,
                c.Nota,
                c.estado as EstadoCalificacion,
                c.FechaRegistro
            FROM calificacion c
            INNER JOIN estudiante e ON c.EstudianteID = e.EstudianteID
            WHERE c.Idmodulo = :idmodulo AND e.Estado = 1
            ORDER BY e.Apaterno, e.Amaterno, e.Nombre
        ");
        $stmt->bindParam(":idmodulo", $idmodulo, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lista de programas (opcionalmente filtrada por sede)
    public static function ListaProgramas($sede = null)
    {
        if ($sede) {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM programa WHERE Estado = 1 AND Sede = :sede ORDER BY NombrePrograma");
            $stmt->bindParam(':sede', $sede, PDO::PARAM_STR);
        } else {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM programa WHERE Estado = 1 ORDER BY NombrePrograma");
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lista de sedes distintas entre los programas activos
    public static function ListaSedes()
    {
        $stmt = Conexion::Conectar()->prepare("SELECT DISTINCT Sede FROM programa WHERE Estado = 1 AND Sede IS NOT NULL AND Sede != '' ORDER BY Sede");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Reporte ejecutivo: KPIs por programa (matrícula, cobranza, aprobación,
     * módulos, docentes) para apoyar decisiones administrativas. Se puede
     * acotar por Sede y/o Programa.
     */
    public static function ReporteEjecutivoPorPrograma($sede = null, $programaID = null)
    {
        $condiciones = ["p.Estado = 1"];
        $params = [];

        if ($sede) {
            $condiciones[] = "p.Sede = :sede";
            $params[':sede'] = $sede;
        }
        if ($programaID) {
            $condiciones[] = "p.ProgramaID = :programaID";
            $params[':programaID'] = $programaID;
        }

        $sql = "
            SELECT
                p.ProgramaID,
                p.NombrePrograma,
                p.GradoAcademico,
                p.Sede,
                p.Codigo,
                p.CostoMatricula,

                -- Matrícula
                (SELECT COUNT(*) FROM estudianteprograma ep WHERE ep.ProgramaID = p.ProgramaID AND ep.Estado = 'ACTIVO') as TotalMatriculados,

                -- Dinero comprometido por matrícula (costo pactado - descuento) de inscripciones activas
                (SELECT COALESCE(SUM(ep.costomatricula - ep.montoDescuento), 0)
                    FROM estudianteprograma ep
                    WHERE ep.ProgramaID = p.ProgramaID AND ep.Estado = 'ACTIVO') as MontoComprometido,

                -- Dinero efectivamente recaudado (cuotas pagadas, vía pagomodulo)
                (SELECT COALESCE(SUM(pm.montocuota), 0)
                    FROM pagomodulo pm
                    INNER JOIN estudianteprograma ep ON pm.idinscripcion = ep.idInscripcion
                    WHERE ep.ProgramaID = p.ProgramaID AND pm.Estado = 'PAGADO') as MontoRecaudado,

                -- Módulos
                (SELECT COUNT(*) FROM modulos m WHERE m.ProgramaId = p.ProgramaID) as TotalModulos,
                (SELECT COUNT(*) FROM modulos m WHERE m.ProgramaId = p.ProgramaID AND m.estadomodulo = 'ACTIVO') as ModulosActivos,
                (SELECT COUNT(*) FROM modulos m WHERE m.ProgramaId = p.ProgramaID AND m.estadomodulo IN ('VALIDADO', 'CERRADO')) as ModulosValidados,
                (SELECT COUNT(DISTINCT m.DocenteID) FROM modulos m WHERE m.ProgramaId = p.ProgramaID AND m.DocenteID IS NOT NULL) as TotalDocentes,

                -- Calificaciones
                (SELECT COUNT(*) FROM calificacion c WHERE c.ProgramaId = p.ProgramaID AND c.Nota IS NOT NULL) as TotalCalificados,
                (SELECT COUNT(*) FROM calificacion c WHERE c.ProgramaId = p.ProgramaID AND c.Nota >= 76) as TotalAprobados,
                (SELECT COUNT(*) FROM calificacion c WHERE c.ProgramaId = p.ProgramaID AND c.Nota IS NOT NULL AND c.Nota < 76) as TotalReprobados

            FROM programa p
            WHERE " . implode(' AND ', $condiciones) . "
            ORDER BY p.Sede, p.NombrePrograma
        ";

        $stmt = Conexion::Conectar()->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
