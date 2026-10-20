<?php
require_once __DIR__ . '/../core/conexion.modelo.php';
require_once __DIR__ . '/configuracion.modelo.php'; // precios de los certificados

// ===========================
//   MODELO CERTIFICADOS
// ===========================

class CertificadoModelo
{
    /**
     * Buscar inscripciones activas por C.I. (si son solo números, el C.I. empieza con
     * ellos) o por nombre y/o apellidos (cada palabra debe aparecer en el nombre
     * completo, en cualquier orden). Para el panel de resultados de los certificados.
     * @param string $termino
     * @return array
     */
    public static function BuscarInscripcionesModelo($termino)
    {
        $termino = trim(preg_replace('/\s+/u', ' ', (string)$termino));
        $sql = "
            SELECT
                ep.idInscripcion, ep.FechaInscripcion,
                e.EstudianteID, e.Ci, e.Complemento, e.Exp, e.Nombre, e.Apaterno, e.Amaterno,
                p.ProgramaID, p.NombrePrograma, p.GradoAcademico, p.Codigo, p.Sede, p.Version
            FROM estudianteprograma ep
            INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
            INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
            WHERE ep.Estado = 'ACTIVO'
        ";
        $parametros = [];
        if (preg_match('/^\d+[A-Za-z0-9-]*$/', $termino)) {
            $sql .= " AND e.Ci LIKE :ci ";
            $parametros[':ci'] = preg_replace('/-.*$/', '', $termino) . '%';
        } else {
            foreach (array_slice(explode(' ', $termino), 0, 5) as $i => $palabra) {
                $sql .= " AND CONCAT_WS(' ', e.Nombre, e.Apaterno, e.Amaterno) LIKE :p$i ";
                $parametros[":p$i"] = '%' . $palabra . '%';
            }
        }
        $sql .= " ORDER BY e.Apaterno, e.Amaterno, e.Nombre, p.NombrePrograma LIMIT 30";

        $stmt = Conexion::Conectar()->prepare($sql);
        $stmt->execute($parametros);
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
                c.FechaRegistro,
                m.FechaInicio,
                m.FechaFinal
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

    /* Cuántos documentos de un tipo se emitieron (correlativo de la demostración) */
    public static function ContarEmitidosModelo($tipo)
    {
        $stmt = Conexion::Conectar()->prepare("SELECT COUNT(*) FROM documentoemitido WHERE TipoDocumento = :tipo");
        $stmt->execute([':tipo' => $tipo]);
        return (int)$stmt->fetchColumn();
    }

    /* Cuántos módulos tiene el programa (para mostrar "3 de 12 módulos con nota") */
    public static function ContarModulosProgramaModelo($programaID)
    {
        $stmt = Conexion::Conectar()->prepare("SELECT COUNT(*) FROM modulos WHERE ProgramaId = :programaID");
        $stmt->execute([':programaID' => (int)$programaID]);
        return (int)$stmt->fetchColumn();
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
    /**
     * Nombres para elegir en el tribunal del Certificado de Aprobación: los docentes
     * activos (con su mención, "Esp. Lic. ...") y las personas ya escritas a mano en
     * tribunales anteriores (así quien no es docente queda registrado para la próxima).
     */
    public static function SugerenciasTribunalModelo()
    {
        $con = Conexion::Conectar();
        $lista = [];
        $docentes = $con->query("SELECT Menciones, Nombre, Apaterno, Amaterno FROM docente WHERE Estado = 1 ORDER BY Apaterno, Amaterno, Nombre");
        foreach ($docentes->fetchAll(PDO::FETCH_ASSOC) as $d) {
            $persona = mb_convert_case(trim(preg_replace('/\s+/', ' ', $d['Nombre'] . ' ' . $d['Apaterno'] . ' ' . $d['Amaterno'])), MB_CASE_TITLE, 'UTF-8');
            $lista[] = ['nombre' => trim(trim($d['Menciones'] ?? '') . ' ' . $persona), 'origen' => 'Docente'];
        }
        $tribunales = $con->query("SELECT Tribunal FROM trabajogrado WHERE Tribunal IS NOT NULL AND Tribunal <> ''");
        foreach ($tribunales->fetchAll(PDO::FETCH_COLUMN) as $tribunal) {
            foreach (explode('|', $tribunal) as $miembro) {
                $partes = explode(':', $miembro, 2);
                $persona = trim(end($partes));
                if ($persona !== '') {
                    $lista[] = ['nombre' => $persona, 'origen' => 'Tribunal anterior'];
                }
            }
        }
        // Sin repetidos (sin importar mayúsculas); gana el primero (el docente)
        $vistos = [];
        return array_values(array_filter($lista, function ($p) use (&$vistos) {
            $clave = mb_strtolower($p['nombre'], 'UTF-8');
            if (isset($vistos[$clave])) {
                return false;
            }
            return $vistos[$clave] = true;
        }));
    }

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
            'REVISION' => 'Certificado de Revisión',
        ];
    }

    /* Grados académicos de los programas (clave de la BD => nombre bien escrito) */
    public static function GradosAcademicos()
    {
        return [
            'DIPLOMADO' => 'Diplomado',
            'ESPECIALIDAD' => 'Especialidad',
            'MAESTRIA' => 'Maestría',
            'DOCTORADO' => 'Doctorado',
        ];
    }

    /**
     * Grado académico tal como se imprime: en MAYÚSCULAS y con tilde (la BD guarda
     * MAESTRIA sin tilde). "MAESTRIA" → "MAESTRÍA".
     */
    public static function GradoLegible($grado)
    {
        $grado = mb_strtoupper(trim((string)$grado), 'UTF-8');
        $clave = self::ClaveGrado($grado);
        return isset(self::GradosAcademicos()[$clave]) ? mb_strtoupper(self::GradosAcademicos()[$clave], 'UTF-8') : $grado;
    }

    /* "Maestría" / "MAESTRÍA" / "MAESTRIA" → "MAESTRIA" (clave de la BD) */
    private static function ClaveGrado($grado)
    {
        return strtr(mb_strtoupper(trim((string)$grado), 'UTF-8'), ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U']);
    }

    /* Clave en configuracion del precio de un tipo de certificado para un grado */
    private static function ClavePrecio($tipo, $grado)
    {
        return 'COSTO_CERT_' . $tipo . '_' . self::ClaveGrado($grado);
    }

    /**
     * Precio de cada tipo de certificado por grado académico (Bs.), definido por el
     * ADM en la pantalla Costos de certificados (configuracion, clave
     * COSTO_CERT_<TIPO>_<GRADO>). null si aún no se definió.
     * @return array [TIPO => [GRADO => float|null]]
     */
    public static function PreciosCertificados()
    {
        $precios = [];
        foreach (array_keys(self::TiposDocumento()) as $tipo) {
            foreach (array_keys(self::GradosAcademicos()) as $grado) {
                $valor = ConfiguracionModelo::Obtener(self::ClavePrecio($tipo, $grado));
                $precios[$tipo][$grado] = ($valor === null || $valor === '') ? null : (float)$valor;
            }
        }
        return $precios;
    }

    /**
     * Guarda el precio de un certificado para un grado ('' = sin precio definido).
     * @return bool|null null si no cambió
     */
    public static function GuardarPrecioCertificado($tipo, $grado, $valor, $responsable)
    {
        $clave = self::ClavePrecio($tipo, $grado);
        $nuevo = $valor === '' ? '' : number_format((float)$valor, 2, '.', '');
        if ($nuevo === (string)ConfiguracionModelo::Obtener($clave, '')) {
            return null;
        }
        return ConfiguracionModelo::Guardar($clave, $nuevo, $responsable);
    }

    /* Último cambio de cualquiera de los precios (quién y cuándo), o null */
    public static function UltimoCambioPrecios()
    {
        $ultima = null;
        foreach (array_keys(self::TiposDocumento()) as $tipo) {
            foreach (array_keys(self::GradosAcademicos()) as $grado) {
                $d = ConfiguracionModelo::Detalle(self::ClavePrecio($tipo, $grado));
                if ($d && (!$ultima || $d['FechaActualizacion'] > $ultima['FechaActualizacion'])) {
                    $ultima = $d;
                }
            }
        }
        return $ultima;
    }

    /* El formulario pidió la vista previa (no se registra la emisión y lleva marca de agua) */
    public static function EsVistaPrevia()
    {
        return !empty($_POST['vistaPrevia']);
    }

    /**
     * Marca de agua "VISTA PREVIA" en todas las hojas, para que no se confunda con el
     * documento emitido. En los formatos pre-impresos avisa además que el papel no se ve.
     * @param TCPDF $pdf
     * @param bool $preImpreso
     */
    public static function MarcarVistaPrevia($pdf, $preImpreso = false)
    {
        $total = $pdf->getNumPages();
        for ($pagina = 1; $pagina <= $total; $pagina++) {
            $pdf->setPage($pagina);
            $ancho = $pdf->getPageWidth();
            $alto = $pdf->getPageHeight();
            $pdf->SetAutoPageBreak(false);
            $pdf->SetTextColor(200, 205, 215);
            $pdf->SetFont('helvetica', 'B', 60);
            $pdf->StartTransform();
            $pdf->Rotate(35, $ancho / 2, $alto / 2);
            $pdf->Text($ancho / 2 - 75, $alto / 2 - 12, 'VISTA PREVIA');
            $pdf->StopTransform();
            if ($preImpreso) {
                $pdf->SetFont('helvetica', '', 8);
                $pdf->SetTextColor(120, 130, 145);
                $pdf->Text(8, 4, 'Vista previa: solo los datos que se imprimen sobre el formato pre-impreso de Posgrado Central.');
            }
            $pdf->SetTextColor(0, 0, 0);
        }
    }

    /**
     * N° de voucher o recibo de un certificado: letras y números (y un guion entre la
     * parte larga y la corta), en MAYÚSCULAS. '' si viene vacío.
     * @return string|false false si tiene otros caracteres
     */
    public static function NormalizarVoucher($vaucher)
    {
        $vaucher = mb_strtoupper(trim((string)$vaucher), 'UTF-8');
        if ($vaucher === '') return '';
        return preg_match('/^[0-9A-ZÑ]+(-[0-9A-ZÑ]+)?$/u', $vaucher) ? $vaucher : false;
    }

    /**
     * Monto del formulario del certificado ("120.50"); null si no se envió o no es válido.
     * @return float|null
     */
    private static function MontoDeDatos(array $datos)
    {
        $monto = str_replace(',', '.', trim((string)($datos['monto'] ?? '')));
        return ($monto !== '' && is_numeric($monto) && (float)$monto >= 0) ? round((float)$monto, 2) : null;
    }

    /* Script PDF que genera cada tipo de documento (para reimprimirlo con sus datos) */
    public static function RutasPdf()
    {
        return [
            'CALIFICACIONES' => 'tcpdf/pdf/certificado-calificaciones-pdf.php',
            'HISTORIAL_ACADEMICO' => 'tcpdf/pdf/certificado-historial-academico-pdf.php',
            'APROBACION_MONOGRAFIA' => 'tcpdf/pdf/certificado-aprobacion-monografia-pdf.php',
            'MEDIO_MAGNETICO' => 'tcpdf/pdf/certificado-medio-magnetico-pdf.php',
            'REVISION' => 'tcpdf/pdf/certificado-revision-pdf.php',
        ];
    }

    /**
     * Registra la emisión de un documento guardando los datos con que se generó
     * (el formulario de su pantalla), para poder reimprimirlo desde Registro de
     * documentos. Si llega $idDocumento es una reimpresión de ese registro: no se
     * crea otro; si los datos cambiaron se guardan los nuevos y, si ya estaba
     * aprobado, vuelve a PENDIENTE (el decano aprobó otro contenido).
     * @return int|false IdDocumento
     */
    public static function RegistrarEmisionModelo($tipoDocumento, $estudianteID, $programaID, $nombreEstudiante, $vaucher, $usuarioID, array $datos, $idDocumento = 0)
    {
        unset($datos['idDocumento'], $datos['vistaPrevia']);
        $datos = array_map(function ($v) { return is_scalar($v) ? (string)$v : ''; }, $datos);
        if (isset($datos['vaucher']) && self::NormalizarVoucher($datos['vaucher']) !== false) {
            $datos['vaucher'] = self::NormalizarVoucher($datos['vaucher']);
        }
        $json = json_encode($datos, JSON_UNESCAPED_UNICODE);
        // Monto pagado: viene en el mismo formulario del certificado (campo "monto")
        $monto = self::MontoDeDatos($datos);
        $pdo = Conexion::Conectar();

        if ($idDocumento > 0) {
            $stmt = $pdo->prepare("SELECT * FROM documentoemitido WHERE IdDocumento = :id");
            $stmt->execute([':id' => $idDocumento]);
            $actual = $stmt->fetch(PDO::FETCH_ASSOC);
            // Solo se reimprime el mismo tipo de documento del mismo estudiante y programa
            if ($actual && $actual['TipoDocumento'] === $tipoDocumento
                && (int)$actual['EstudianteID'] === (int)$estudianteID && (int)$actual['ProgramaID'] === (int)$programaID) {
                if ($actual['Datos'] !== $json) {
                    $stmt = $pdo->prepare("
                        UPDATE documentoemitido
                        SET Datos = :datos, NombreEstudiante = :nombre, NumeroVaucher = :vaucher, Monto = :monto,
                            Estado = 'PENDIENTE', FechaAprobacion = NULL, UsuarioAprobadorID = NULL
                        WHERE IdDocumento = :id
                    ");
                    $stmt->execute([':datos' => $json, ':nombre' => $nombreEstudiante, ':vaucher' => $vaucher, ':monto' => $monto, ':id' => $idDocumento]);
                }
                return (int)$idDocumento;
            }
        }

        $stmt = $pdo->prepare("
            INSERT INTO documentoemitido
                (TipoDocumento, EstudianteID, ProgramaID, NombreEstudiante, NumeroVaucher, Monto, Datos, UsuarioEmisorID)
            VALUES
                (:tipo, :estudianteID, :programaID, :nombre, :vaucher, :monto, :datos, :usuarioID)
        ");
        $ok = $stmt->execute([
            ':tipo' => $tipoDocumento,
            ':estudianteID' => $estudianteID ?: null,
            ':programaID' => $programaID ?: null,
            ':nombre' => $nombreEstudiante,
            ':vaucher' => $vaucher,
            ':monto' => $monto,
            ':datos' => $json,
            ':usuarioID' => $usuarioID,
        ]);
        return $ok ? (int)$pdo->lastInsertId() : false;
    }

    /* Un documento emitido con sus datos guardados */
    public static function ObtenerDocumentoModelo($idDocumento)
    {
        $stmt = Conexion::Conectar()->prepare("SELECT * FROM documentoemitido WHERE IdDocumento = :id");
        $stmt->execute([':id' => $idDocumento]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Aprobación del decano: APROBADO (volvió aprobado y se validó) o, para
     * corregir un error al marcarlo, de vuelta a PENDIENTE.
     */
    public static function CambiarEstadoDocumentoModelo($idDocumento, $aprobado, $usuarioID)
    {
        if ($aprobado) {
            $stmt = Conexion::Conectar()->prepare("
                UPDATE documentoemitido SET Estado = 'APROBADO', FechaAprobacion = NOW(), UsuarioAprobadorID = :usuario
                WHERE IdDocumento = :id");
            $stmt->execute([':usuario' => $usuarioID, ':id' => $idDocumento]);
        } else {
            $stmt = Conexion::Conectar()->prepare("
                UPDATE documentoemitido SET Estado = 'PENDIENTE', FechaAprobacion = NULL, UsuarioAprobadorID = NULL
                WHERE IdDocumento = :id");
            $stmt->execute([':id' => $idDocumento]);
        }
        return $stmt->rowCount() > 0;
    }

    /**
     * Borra un documento emitido que aún no aprobó el decano (p. ej. se generó por error).
     * La condición de estado va en el mismo DELETE: uno ya aprobado nunca se borra.
     * @return bool false si no existe o ya está aprobado
     */
    public static function EliminarDocumentoPendienteModelo($idDocumento)
    {
        $stmt = Conexion::Conectar()->prepare("
            DELETE FROM documentoemitido WHERE IdDocumento = :id AND Estado = 'PENDIENTE'");
        $stmt->execute([':id' => (int)$idDocumento]);
        return $stmt->rowCount() > 0;
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
    public static function ListarDocumentosEmitidosModelo($ci = null, $limite = 100, array $filtros = [])
    {
        $sql = "
            SELECT
                d.IdDocumento, d.TipoDocumento, d.EstudianteID, d.ProgramaID, d.NombreEstudiante,
                d.NumeroVaucher, d.Monto, d.FechaEmision, d.Estado, d.FechaAprobacion,
                p.NombrePrograma,
                (d.Datos IS NOT NULL) AS TieneDatos,
                e.Ci,
                e.Complemento,
                u.Usuario as UsuarioEmisor,
                ua.Usuario as UsuarioAprobador
            FROM documentoemitido d
            LEFT JOIN estudiante e ON d.EstudianteID = e.EstudianteID
            LEFT JOIN usuario u ON d.UsuarioEmisorID = u.ID
            LEFT JOIN usuario ua ON d.UsuarioAprobadorID = ua.ID
            LEFT JOIN programa p ON d.ProgramaID = p.ProgramaID
            WHERE 1 = 1
        ";
        $parametros = [];
        if (!empty($ci)) {
            $sql .= " AND e.Ci LIKE :ci ";
            $parametros[':ci'] = $ci . '%';
        }
        // Filtros del historial de pagos: fechas de emisión y tipo de certificado
        if (!empty($filtros['desde'])) {
            $sql .= " AND d.FechaEmision >= :desde ";
            $parametros[':desde'] = $filtros['desde'] . ' 00:00:00';
        }
        if (!empty($filtros['hasta'])) {
            $sql .= " AND d.FechaEmision <= :hasta ";
            $parametros[':hasta'] = $filtros['hasta'] . ' 23:59:59';
        }
        if (!empty($filtros['tipo'])) {
            $sql .= " AND d.TipoDocumento = :tipo ";
            $parametros[':tipo'] = $filtros['tipo'];
        }
        $sql .= " ORDER BY d.FechaEmision DESC LIMIT " . intval($limite);

        $stmt = Conexion::Conectar()->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
