<?php
require_once 'conexion.modelo.php';

// ===========================
//   MODELO CERTIFICADOS
// ===========================

class CertificadoModelo
{
    /* Inscripciones (estudiante+programa) cuyo CI empieza con lo digitado */
    public static function BuscarInscripcionesPorCIModelo($ci)
    {
        $stmt = Conexion::Conectar()->prepare("
            SELECT
                ep.idInscripcion,
                e.EstudianteID,
                e.Ci,
                e.Complemento,
                e.Nombre,
                e.Apaterno,
                e.Amaterno,
                p.ProgramaID,
                p.NombrePrograma,
                p.GradoAcademico,
                p.Codigo
            FROM estudianteprograma ep
            INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
            INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
            WHERE ep.Estado = 'ACTIVO'
              AND e.Ci LIKE :ci
            ORDER BY e.Apaterno, e.Amaterno, e.Nombre, p.NombrePrograma
        ");
        $stmt->bindValue(':ci', $ci . '%', PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Módulos calificados de un estudiante en un programa, en orden de cursado */
    public static function ObtenerModulosCalificadosModelo($estudianteID, $programaID)
    {
        $stmt = Conexion::Conectar()->prepare("
            SELECT
                m.Idmodulo,
                m.codigomodulo,
                m.nombremodulo,
                c.Nota
            FROM calificacion c
            INNER JOIN modulos m ON c.Idmodulo = m.Idmodulo AND m.ProgramaId = c.ProgramaId
            WHERE c.EstudianteID = :estudianteID
              AND c.ProgramaId = :programaID
              AND c.Nota IS NOT NULL
            ORDER BY m.Idmodulo ASC
        ");
        $stmt->bindParam(':estudianteID', $estudianteID, PDO::PARAM_INT);
        $stmt->bindParam(':programaID', $programaID, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
