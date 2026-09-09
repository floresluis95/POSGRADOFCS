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
                c.Nota,
                c.FechaRegistro
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

    /* Trabajo de grado (Tesis/Monografía) registrado para un estudiante+programa */
    public static function ObtenerTrabajoGradoModelo($estudianteID, $programaID)
    {
        $stmt = Conexion::Conectar()->prepare("
            SELECT *
            FROM trabajogrado
            WHERE EstudianteID = :estudianteID
              AND ProgramaID = :programaID
            LIMIT 1
        ");
        $stmt->bindParam(':estudianteID', $estudianteID, PDO::PARAM_INT);
        $stmt->bindParam(':programaID', $programaID, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }

    /* Guarda (o actualiza) el trabajo de grado de un estudiante+programa */
    public static function GuardarTrabajoGradoModelo($datos)
    {
        $stmt = Conexion::Conectar()->prepare("
            INSERT INTO trabajogrado
                (EstudianteID, ProgramaID, TituloTrabajo, TipoTrabajo, Tribunal,
                 CalificacionNumeral, FechaEntrega, FechaAprobacion, UsuarioRegistroID)
            VALUES
                (:estudianteID, :programaID, :titulo, :tipoTrabajo, :tribunal,
                 :calificacion, :fechaEntrega, :fechaAprobacion, :usuarioID)
            ON DUPLICATE KEY UPDATE
                TituloTrabajo = VALUES(TituloTrabajo),
                TipoTrabajo = VALUES(TipoTrabajo),
                Tribunal = VALUES(Tribunal),
                CalificacionNumeral = VALUES(CalificacionNumeral),
                FechaEntrega = VALUES(FechaEntrega),
                FechaAprobacion = VALUES(FechaAprobacion),
                UsuarioRegistroID = VALUES(UsuarioRegistroID)
        ");

        $fechaEntrega = !empty($datos['fechaEntrega']) ? $datos['fechaEntrega'] : null;
        $fechaAprobacion = !empty($datos['fechaAprobacion']) ? $datos['fechaAprobacion'] : date('Y-m-d');
        $calificacion = $datos['calificacion'] !== '' ? $datos['calificacion'] : null;

        $stmt->bindParam(':estudianteID', $datos['estudianteID'], PDO::PARAM_INT);
        $stmt->bindParam(':programaID', $datos['programaID'], PDO::PARAM_INT);
        $stmt->bindParam(':titulo', $datos['titulo'], PDO::PARAM_STR);
        $stmt->bindParam(':tipoTrabajo', $datos['tipoTrabajo'], PDO::PARAM_STR);
        $stmt->bindParam(':tribunal', $datos['tribunal'], PDO::PARAM_STR);
        $stmt->bindParam(':calificacion', $calificacion);
        $stmt->bindParam(':fechaEntrega', $fechaEntrega);
        $stmt->bindParam(':fechaAprobacion', $fechaAprobacion);
        $stmt->bindParam(':usuarioID', $datos['usuarioID'], PDO::PARAM_INT);

        return $stmt->execute();
    }

    /* Actualiza un trabajo de grado ya registrado (edición manual) */
    public static function ActualizarTrabajoGradoModelo($idTrabajo, $titulo, $calificacion, $tribunal)
    {
        $stmt = Conexion::Conectar()->prepare("
            UPDATE trabajogrado
            SET TituloTrabajo = :titulo,
                CalificacionNumeral = :calificacion,
                Tribunal = :tribunal
            WHERE IdTrabajo = :idTrabajo
        ");
        $calificacionValor = $calificacion !== '' ? $calificacion : null;
        $stmt->bindParam(':titulo', $titulo, PDO::PARAM_STR);
        $stmt->bindParam(':calificacion', $calificacionValor);
        $stmt->bindParam(':tribunal', $tribunal, PDO::PARAM_STR);
        $stmt->bindParam(':idTrabajo', $idTrabajo, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /* Tipos de documento reconocidos (clave interna => etiqueta legible) */
    public static function TiposDocumento()
    {
        return [
            'CALIFICACIONES' => 'Certificado de Calificaciones',
            'HISTORIAL_ACADEMICO' => 'Historial Académico',
            'APROBACION_MONOGRAFIA' => 'Aprobación de Monografía',
            'MEDIO_MAGNETICO' => 'Medio Magnético',
        ];
    }

    /* Registra en la bitácora que se emitió un documento */
    public static function RegistrarDocumentoEmitidoModelo($tipoDocumento, $estudianteID, $programaID, $nombreEstudiante, $vaucher, $usuarioID)
    {
        $stmt = Conexion::Conectar()->prepare("
            INSERT INTO documentoemitido
                (TipoDocumento, EstudianteID, ProgramaID, NombreEstudiante, NumeroVaucher, UsuarioEmisorID)
            VALUES
                (:tipo, :estudianteID, :programaID, :nombre, :vaucher, :usuarioID)
        ");
        $estudianteIDValor = $estudianteID ?: null;
        $programaIDValor = $programaID ?: null;
        $stmt->bindParam(':tipo', $tipoDocumento, PDO::PARAM_STR);
        $stmt->bindParam(':estudianteID', $estudianteIDValor, PDO::PARAM_INT);
        $stmt->bindParam(':programaID', $programaIDValor, PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $nombreEstudiante, PDO::PARAM_STR);
        $stmt->bindParam(':vaucher', $vaucher, PDO::PARAM_STR);
        $stmt->bindParam(':usuarioID', $usuarioID, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /* Para cada tipo de documento, la última emisión de un estudiante+programa (o null si nunca se emitió) */
    public static function EstadoDocumentosEstudianteModelo($estudianteID, $programaID)
    {
        $stmt = Conexion::Conectar()->prepare("
            SELECT d.*
            FROM documentoemitido d
            INNER JOIN (
                SELECT TipoDocumento, MAX(FechaEmision) as UltimaFecha
                FROM documentoemitido
                WHERE EstudianteID = :estudianteID1
                  AND ProgramaID = :programaID1
                GROUP BY TipoDocumento
            ) ultimos ON d.TipoDocumento = ultimos.TipoDocumento AND d.FechaEmision = ultimos.UltimaFecha
            WHERE d.EstudianteID = :estudianteID2
              AND d.ProgramaID = :programaID2
        ");
        $stmt->bindParam(':estudianteID1', $estudianteID, PDO::PARAM_INT);
        $stmt->bindParam(':programaID1', $programaID, PDO::PARAM_INT);
        $stmt->bindParam(':estudianteID2', $estudianteID, PDO::PARAM_INT);
        $stmt->bindParam(':programaID2', $programaID, PDO::PARAM_INT);
        $stmt->execute();

        $porTipo = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $porTipo[$fila['TipoDocumento']] = $fila;
        }
        return $porTipo;
    }

    /* Bitácora completa de documentos emitidos, opcionalmente filtrada por CI */
    public static function ListarDocumentosEmitidosModelo($ci = null, $limite = 100)
    {
        $sql = "
            SELECT
                d.*,
                e.Ci,
                e.Complemento,
                u.Usuario as UsuarioEmisor
            FROM documentoemitido d
            LEFT JOIN estudiante e ON d.EstudianteID = e.EstudianteID
            LEFT JOIN usuario u ON d.UsuarioEmisorID = u.ID
        ";
        if (!empty($ci)) {
            $sql .= " WHERE e.Ci LIKE :ci ";
        }
        $sql .= " ORDER BY d.FechaEmision DESC LIMIT " . intval($limite);

        $stmt = Conexion::Conectar()->prepare($sql);
        if (!empty($ci)) {
            $stmt->bindValue(':ci', $ci . '%', PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
