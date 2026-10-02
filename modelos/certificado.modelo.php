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

    /**
     * El título del trabajo de grado se guarda siempre en MAYÚSCULAS y sin
     * espacios de más, venga de donde venga (certificado de aprobación, defensas,
     * registro de documentos), para que salga igual en todos los documentos.
     */
    public static function NormalizarTitulo($titulo)
    {
        return mb_strtoupper(trim(preg_replace('/\s+/u', ' ', (string)$titulo)), 'UTF-8');
    }

    /**
     * Guardar SOLO el título (crea el trabajo de grado si aún no existe, sin
     * tocar tribunal, calificación ni fechas). Lo usan "Guardar título" de la
     * Aprobación Programa de Posgrado y el Cronograma de defensas.
     * @return bool
     */
    public static function GuardarTituloModelo($estudianteID, $programaID, $titulo, $usuarioID = null)
    {
        $stmt = Conexion::Conectar()->prepare("
            INSERT INTO trabajogrado (EstudianteID, ProgramaID, TituloTrabajo, UsuarioRegistroID)
            VALUES (:estudianteID, :programaID, :titulo, :usuarioID)
            ON DUPLICATE KEY UPDATE TituloTrabajo = VALUES(TituloTrabajo)
        ");
        return $stmt->execute([
            ':estudianteID' => (int)$estudianteID,
            ':programaID' => (int)$programaID,
            ':titulo' => self::NormalizarTitulo($titulo),
            ':usuarioID' => $usuarioID,
        ]);
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
                 CalificacionNumeral, FechaEntrega, HoraEntrega, FechaAprobacion, UsuarioRegistroID)
            VALUES
                (:estudianteID, :programaID, :titulo, :tipoTrabajo, :tribunal,
                 :calificacion, :fechaEntrega, :horaEntrega, :fechaAprobacion, :usuarioID)
            ON DUPLICATE KEY UPDATE
                TituloTrabajo = VALUES(TituloTrabajo),
                TipoTrabajo = VALUES(TipoTrabajo),
                Tribunal = VALUES(Tribunal),
                CalificacionNumeral = VALUES(CalificacionNumeral),
                FechaEntrega = VALUES(FechaEntrega),
                HoraEntrega = VALUES(HoraEntrega),
                FechaAprobacion = VALUES(FechaAprobacion),
                UsuarioRegistroID = VALUES(UsuarioRegistroID)
        ");

        $datos['titulo'] = self::NormalizarTitulo($datos['titulo']);
        $fechaEntrega = !empty($datos['fechaEntrega']) ? $datos['fechaEntrega'] : null;
        // Hora de entrega del informe final (HH:MM); solo tiene sentido con fecha
        $horaEntrega = ($fechaEntrega && !empty($datos['horaEntrega']) && preg_match('/^\d{2}:\d{2}$/', $datos['horaEntrega']))
            ? $datos['horaEntrega'] : null;
        $fechaAprobacion = !empty($datos['fechaAprobacion']) ? $datos['fechaAprobacion'] : date('Y-m-d');
        $calificacion = $datos['calificacion'] !== '' ? $datos['calificacion'] : null;

        $stmt->bindParam(':estudianteID', $datos['estudianteID'], PDO::PARAM_INT);
        $stmt->bindParam(':programaID', $datos['programaID'], PDO::PARAM_INT);
        $stmt->bindParam(':titulo', $datos['titulo'], PDO::PARAM_STR);
        $stmt->bindParam(':tipoTrabajo', $datos['tipoTrabajo'], PDO::PARAM_STR);
        $stmt->bindParam(':tribunal', $datos['tribunal'], PDO::PARAM_STR);
        $stmt->bindParam(':calificacion', $calificacion);
        $stmt->bindParam(':fechaEntrega', $fechaEntrega);
        $stmt->bindParam(':horaEntrega', $horaEntrega);
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
        $titulo = self::NormalizarTitulo($titulo);
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
            'APROBACION_MONOGRAFIA' => 'Aprobación Programa de Posgrado', // la clave se conserva (historial)
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
