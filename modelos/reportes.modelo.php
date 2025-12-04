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

    // Estudiantes por programa específico
    public static function EstudiantesPorPrograma($programaID = null)
    {
        if ($programaID) {
            $stmt = Conexion::Conectar()->prepare("
                SELECT e.*, p.NombreProfesion, pr.NombrePrograma, ep.FechaInscripcion
                FROM estudianteprograma ep
                INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
                INNER JOIN programa pr ON ep.ProgramaID = pr.ProgramaID
                INNER JOIN profesion p ON e.IdProfesion = p.IdProfesion
                WHERE pr.ProgramaID = :programaID
                AND ep.Estado = 'ACTIVO'
                ORDER BY e.Apaterno, e.Amaterno
            ");
            $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
        } else {
            $stmt = Conexion::Conectar()->prepare("
                SELECT e.*, p.NombreProfesion, pr.NombrePrograma, ep.FechaInscripcion
                FROM estudianteprograma ep
                INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
                INNER JOIN programa pr ON ep.ProgramaID = pr.ProgramaID
                INNER JOIN profesion p ON e.IdProfesion = p.IdProfesion
                WHERE ep.Estado = 'ACTIVO'
                ORDER BY pr.NombrePrograma, e.Apaterno, e.Amaterno
            ");
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Módulos con matriculados
    public static function ModulosConMatriculados()
    {
        $stmt = Conexion::Conectar()->prepare("
            SELECT
                m.CodigoModulo,
                m.NombreModulo,
                m.Costo,
                m.HorasAcademicas,
                COUNT(DISTINCT em.EstudianteID) as TotalMatriculados,
                SUM(CASE WHEN pm.Estado = 'PAGADO' THEN 1 ELSE 0 END) as TotalPagados,
                SUM(CASE WHEN pm.Estado = 'PENDIENTE' THEN 1 ELSE 0 END) as TotalPendientes,
                SUM(CASE WHEN pm.Estado = 'PAGADO' THEN pm.MontoPagado ELSE 0 END) as TotalRecaudado
            FROM modulo m
            LEFT JOIN estudiantemodulo em ON m.CodigoModulo = em.CodigoModulo
            LEFT JOIN pagomodulo pm ON em.idInscripcionModulo = pm.idInscripcionModulo
            WHERE m.Estado = 1
            GROUP BY m.CodigoModulo, m.NombreModulo, m.Costo, m.HorasAcademicas
            ORDER BY m.NombreModulo
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Detalle de estudiantes en un módulo
    public static function EstudiantesEnModulo($codigoModulo)
    {
        $stmt = Conexion::Conectar()->prepare("
            SELECT
                e.Ci, e.Complemento, e.Exp,
                e.Nombre, e.Apaterno, e.Amaterno,
                e.Celular, e.Correo,
                em.FechaInscripcion,
                COALESCE(pm.Estado, 'SIN REGISTRO') as EstadoPago,
                COALESCE(pm.MontoPagado, 0) as MontoPagado,
                pm.FechaPago
            FROM estudiantemodulo em
            INNER JOIN estudiante e ON em.EstudianteID = e.EstudianteID
            LEFT JOIN pagomodulo pm ON em.idInscripcionModulo = pm.idInscripcionModulo
            WHERE em.CodigoModulo = :codigoModulo AND e.Estado = 1
            ORDER BY e.Apaterno, e.Amaterno, e.Nombre
        ");
        $stmt->bindParam(":codigoModulo", $codigoModulo, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lista de programas
    public static function ListaProgramas()
    {
        $stmt = Conexion::Conectar()->prepare("SELECT * FROM programa WHERE Estado = 1 ORDER BY NombrePrograma");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
