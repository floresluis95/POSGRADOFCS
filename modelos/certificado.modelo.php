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

    /**
     * Persona del tribunal repetida (la misma no puede ser presidente y vocal, etc.).
     * Compara sin tildes, mayúsculas ni el grado del inicio ("M.Sc. Dr.", "Esp. Lic."),
     * así "Esp. Lic. Ana Pérez" y "M.Sc. ana perez" cuentan como la misma.
     * Devuelve el nombre repetido o null.
     */
    public static function TribunalRepetidoModelo(array $nombres)
    {
        $vistos = [];
        foreach ($nombres as $nombre) {
            $clave = strtr(mb_strtolower(trim($nombre), 'UTF-8'), ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
            $clave = trim(preg_replace(['/^(\s*\S+\.)+\s*/u', '/\s+/u'], ['', ' '], $clave));
            if ($clave === '') {
                continue;
            }
            if (isset($vistos[$clave])) {
                return trim($nombre);
            }
            $vistos[$clave] = true;
        }
        return null;
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

    /* Certificados que el sistema no genera: solo se registra el pago (paso 26 del LEEME) */
    const TIPO_SIN_DOCUMENTO = 'SIN_DOCUMENTO';

    /**
     * Los tipos generados más "Otros certificados" (sin documento), para mostrar y filtrar.
     * TiposDocumento() queda solo con los que se generan: con él se arman los precios y PDFs.
     */
    public static function TiposParaListado()
    {
        return self::TiposDocumento() + [self::TIPO_SIN_DOCUMENTO => 'Otros certificados'];
    }

    /** Catálogo de certificados sin documento (solo activos, o todos para el ADM) */
    public static function ListarSinDocumentoModelo($soloActivos = true)
    {
        $stmt = Conexion::Conectar()->prepare(
            "SELECT IdCertificadoSD, Nombre, Monto, Activo FROM certificadosindocumento" . ($soloActivos ? " WHERE Activo = 1" : "") . " ORDER BY Nombre"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Agrega o cambia un certificado del catálogo (nombre en MAYÚSCULAS, sin repetir).
     * @return array ['success' => bool, 'mensaje' => string]
     */
    public static function GuardarSinDocumentoModelo($id, $nombre, $monto, $activo)
    {
        $nombre = mb_strtoupper(trim(preg_replace('/\s+/u', ' ', (string)$nombre)), 'UTF-8');
        $monto = str_replace(',', '.', trim((string)$monto));
        if ($nombre === '' || mb_strlen($nombre) > 150) {
            return ['success' => false, 'mensaje' => 'Escriba el nombre del certificado'];
        }
        if ($monto === '' || !is_numeric($monto) || (float)$monto < 0) {
            return ['success' => false, 'mensaje' => 'El precio no es válido'];
        }
        $pdo = Conexion::Conectar();
        $stmt = $pdo->prepare("SELECT 1 FROM certificadosindocumento WHERE Nombre = :nombre AND IdCertificadoSD <> :id");
        $stmt->execute([':nombre' => $nombre, ':id' => (int)$id]);
        if ($stmt->fetchColumn()) {
            return ['success' => false, 'mensaje' => 'Ya existe un certificado con ese nombre'];
        }
        if ($id) {
            $stmt = $pdo->prepare("UPDATE certificadosindocumento SET Nombre = :nombre, Monto = :monto, Activo = :activo WHERE IdCertificadoSD = :id");
            $stmt->execute([':nombre' => $nombre, ':monto' => round((float)$monto, 2), ':activo' => $activo ? 1 : 0, ':id' => (int)$id]);
            return ['success' => true, 'mensaje' => 'Certificado actualizado'];
        }
        $stmt = $pdo->prepare("INSERT INTO certificadosindocumento (Nombre, Monto) VALUES (:nombre, :monto)");
        $stmt->execute([':nombre' => $nombre, ':monto' => round((float)$monto, 2)]);
        return ['success' => true, 'mensaje' => 'Certificado agregado'];
    }

    /**
     * Registra el pago de un certificado sin documento para una inscripción (estudiante +
     * programa: si el estudiante está en dos programas, cada pago queda en el suyo).
     * La fecha de registro es FechaEmision (ahora).
     * @return array ['success' => bool, 'mensaje' => string, 'idDocumento' => int]
     */
    public static function RegistrarSinDocumentoModelo($estudianteID, $programaID, $idCertificadoSD, $vaucher, $monto, $fechaPago, $usuarioID)
    {
        $pdo = Conexion::Conectar();
        $stmt = $pdo->prepare("
            SELECT CONCAT_WS(' ', e.Nombre, e.Apaterno, e.Amaterno)
            FROM estudianteprograma ep INNER JOIN estudiante e ON e.EstudianteID = ep.EstudianteID
            WHERE ep.EstudianteID = :estudianteID AND ep.ProgramaID = :programaID LIMIT 1
        ");
        $stmt->execute([':estudianteID' => (int)$estudianteID, ':programaID' => (int)$programaID]);
        $nombreEstudiante = $stmt->fetchColumn();
        if ($nombreEstudiante === false) {
            return ['success' => false, 'mensaje' => 'Elija al estudiante y su programa'];
        }
        $stmt = $pdo->prepare("SELECT Nombre FROM certificadosindocumento WHERE IdCertificadoSD = :id AND Activo = 1");
        $stmt->execute([':id' => (int)$idCertificadoSD]);
        $nombreCertificado = $stmt->fetchColumn();
        if ($nombreCertificado === false) {
            return ['success' => false, 'mensaje' => 'Elija el certificado'];
        }
        $vaucher = self::NormalizarVoucher($vaucher);
        if ($vaucher === false || $vaucher === '') {
            return ['success' => false, 'mensaje' => $vaucher === '' ? 'Falta el N° de voucher' : 'El N° de voucher solo puede tener letras y números (y un guion entre las dos partes)'];
        }
        if ($repetido = self::VoucherRepetidoMensaje($vaucher)) {
            return ['success' => false, 'mensaje' => $repetido];
        }
        $datos = ['nombreCertificado' => $nombreCertificado, 'vaucher' => $vaucher, 'monto' => trim((string)$monto), 'fechaPago' => trim((string)$fechaPago)];
        if ($datos['monto'] !== '' && self::MontoDeDatos($datos) === null) {
            return ['success' => false, 'mensaje' => 'El monto no es válido'];
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $datos['fechaPago']) || $datos['fechaPago'] > date('Y-m-d')) {
            return ['success' => false, 'mensaje' => 'La fecha de pago del voucher no es válida (no puede ser posterior a hoy)'];
        }

        $stmt = $pdo->prepare("
            INSERT INTO documentoemitido
                (TipoDocumento, NombreCertificado, IdCertificadoSD, EstudianteID, ProgramaID, NombreEstudiante, NumeroVaucher, Monto, FechaPago, Datos, UsuarioEmisorID)
            VALUES
                (:tipo, :nombreCertificado, :idCertificadoSD, :estudianteID, :programaID, :nombre, :vaucher, :monto, :fechaPago, :datos, :usuarioID)
        ");
        $stmt->execute([
            ':tipo' => self::TIPO_SIN_DOCUMENTO,
            ':nombreCertificado' => $nombreCertificado,
            ':idCertificadoSD' => (int)$idCertificadoSD,
            ':estudianteID' => (int)$estudianteID,
            ':programaID' => (int)$programaID,
            ':nombre' => mb_strtoupper($nombreEstudiante, 'UTF-8'),
            ':vaucher' => $vaucher,
            ':monto' => self::MontoDeDatos($datos),
            ':fechaPago' => $datos['fechaPago'],
            ':datos' => json_encode($datos, JSON_UNESCAPED_UNICODE),
            ':usuarioID' => $usuarioID,
        ]);
        return ['success' => true, 'mensaje' => 'Certificado registrado', 'idDocumento' => (int)$pdo->lastInsertId()];
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
     * El voucher de un certificado sigue la regla de todo el sistema: no puede estar ya
     * usado en otro certificado, matrícula, cuota o clínica. Al reimprimir se pasa el
     * IdDocumento para que el documento no choque con su propio voucher.
     * @return string|null Mensaje para el usuario, o null si el voucher está libre
     */
    public static function VoucherRepetidoMensaje($vaucher, $idDocumento = 0)
    {
        require_once __DIR__ . '/voucher.modelo.php';
        $donde = VoucherModelo::VoucherYaUsadoModelo($vaucher, null, (int)$idDocumento);
        return $donde ? VoucherModelo::MensajeVoucherRepetido($vaucher, $donde) : null;
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
        // Marca del envío (la pone la pantalla del certificado): se anota qué documento
        // quedó registrado para que Registro de documentos lo abra y lo resalte
        $tokenEmision = (string)($datos['tokenEmision'] ?? '');
        unset($datos['tokenEmision']);
        $id = self::GuardarEmision($tipoDocumento, $estudianteID, $programaID, $nombreEstudiante, $vaucher, $usuarioID, $datos, $idDocumento);
        if ($id && preg_match('/^[a-z0-9]{8,40}$/', $tokenEmision) && session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['emisionesCertificado'][$tokenEmision] = $id;
            // Solo las últimas: no hace falta guardar más
            $_SESSION['emisionesCertificado'] = array_slice($_SESSION['emisionesCertificado'], -20, null, true);
        }
        return $id;
    }

    /** Documento que registró el envío con esa marca (en esta sesión), o null si aún no está. */
    public static function DocumentoDeEmision($tokenEmision)
    {
        return isset($_SESSION['emisionesCertificado'][$tokenEmision]) ? (int)$_SESSION['emisionesCertificado'][$tokenEmision] : null;
    }

    /* Campos de un documento que se pueden corregir desde Registro de documentos */
    const CAMPOS_EDITABLES = [
        'nombre', 'programa', 'nivel', 'anio', 'vaucher', 'monto', 'fecha', 'curso', 'titulo', 'tribunal',
        'entrega', 'horaEntrega', 'calificacion', 'tratamiento', 'tipotrabajo', 'coordinador', 'director', 'fechaPago',
    ];

    /**
     * Corrige los datos guardados de un documento sin imprimirlo. Solo cambia los campos
     * que el documento ya tenía; si algo cambió y estaba aprobado, vuelve a PENDIENTE
     * (igual que al reimprimirlo corregido).
     * @return array ['success' => bool, 'mensaje' => string, 'cambio' => bool]
     */
    public static function EditarDocumentoModelo($idDocumento, array $cambios)
    {
        $actual = self::ObtenerDocumentoModelo($idDocumento);
        if (!$actual || !$actual['Datos']) {
            return ['success' => false, 'mensaje' => 'Este documento no tiene datos guardados: vuelva a generarlo desde su pantalla'];
        }
        $datos = json_decode($actual['Datos'], true) ?: [];
        foreach (self::CAMPOS_EDITABLES as $campo) {
            if (array_key_exists($campo, $cambios) && array_key_exists($campo, $datos)) {
                $datos[$campo] = trim((string)$cambios[$campo]);
            }
        }

        if (array_key_exists('nombre', $datos) && $datos['nombre'] === '') {
            return ['success' => false, 'mensaje' => 'Falta el nombre del estudiante'];
        }
        $vaucher = self::NormalizarVoucher($datos['vaucher'] ?? '');
        if ($vaucher === false) {
            return ['success' => false, 'mensaje' => 'El N° de voucher solo puede tener letras y números (y un guion entre las dos partes)'];
        }
        if ($vaucher !== '' && ($repetido = self::VoucherRepetidoMensaje($vaucher, $idDocumento))) {
            return ['success' => false, 'mensaje' => $repetido];
        }
        if (isset($datos['monto']) && $datos['monto'] !== '' && self::MontoDeDatos($datos) === null) {
            return ['success' => false, 'mensaje' => 'El monto no es válido'];
        }
        foreach (['fecha', 'entrega', 'fechaPago'] as $campoFecha) {
            if (!empty($datos[$campoFecha]) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $datos[$campoFecha])) {
                return ['success' => false, 'mensaje' => 'La fecha no es válida'];
            }
        }

        if (!empty($datos['fechaPago']) && $datos['fechaPago'] > date('Y-m-d')) {
            return ['success' => false, 'mensaje' => 'La fecha de pago del voucher no puede ser posterior a hoy'];
        }

        self::GuardarEmision(
            $actual['TipoDocumento'], (int)$actual['EstudianteID'], (int)$actual['ProgramaID'],
            $datos['nombre'] ?? $actual['NombreEstudiante'], $vaucher, null, $datos, (int)$idDocumento
        );
        // Certificado sin documento: la fecha de pago del voucher también va en su columna
        if (array_key_exists('fechaPago', $datos)) {
            $stmt = Conexion::Conectar()->prepare("UPDATE documentoemitido SET FechaPago = :fecha WHERE IdDocumento = :id");
            $stmt->execute([':fecha' => $datos['fechaPago'] ?: null, ':id' => (int)$idDocumento]);
        }
        $despues = self::ObtenerDocumentoModelo($idDocumento);
        $cambio = $despues && $despues['Datos'] !== $actual['Datos'];
        return [
            'success' => true,
            'cambio' => $cambio,
            'mensaje' => !$cambio ? 'No hubo cambios'
                : ($actual['Estado'] === 'APROBADO' ? 'Cambios guardados. El documento volvió a quedar por aprobar' : 'Cambios guardados'),
        ];
    }

    private static function GuardarEmision($tipoDocumento, $estudianteID, $programaID, $nombreEstudiante, $vaucher, $usuarioID, array $datos, $idDocumento = 0)
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

    /**
     * Pagos de certificados de un estudiante, para "Mis certificados" (cuenta del estudiante).
     * Solo lo que él puede ver: tipo, programa, fecha, voucher y monto; nunca los datos del
     * documento (no puede ver ni imprimir el certificado ni el recibo). Del más reciente al más antiguo.
     */
    public static function PagosCertificadosEstudianteModelo($estudianteID)
    {
        $stmt = Conexion::Conectar()->prepare("
            SELECT d.TipoDocumento, d.NombreCertificado, d.FechaEmision, d.FechaPago, d.NumeroVaucher, d.Monto,
                   d.ProgramaID, p.NombrePrograma, p.GradoAcademico, p.Codigo
            FROM documentoemitido d
            LEFT JOIN programa p ON p.ProgramaID = d.ProgramaID
            WHERE d.EstudianteID = :estudianteID
            ORDER BY d.FechaEmision DESC, d.IdDocumento DESC
        ");
        $stmt->execute([':estudianteID' => (int)$estudianteID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ===================== Certificados exigidos para la titulación =====================
       (tabla certificadotitulacion, paso 27 del LEEME). Clave: el tipo generado
       (CALIFICACIONES, ...) o 'SD:<IdCertificadoSD>' para uno sin documento. Solo avisa. */

    /** Qué exige cada grado: ['CALIFICACIONES' => ['MAESTRIA' => true, ...], 'SD:3' => [...]] */
    public static function RequisitosTitulacionModelo()
    {
        $mapa = [];
        try {
            $stmt = Conexion::Conectar()->query("SELECT Clave, GradoAcademico FROM certificadotitulacion");
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
                $mapa[$fila['Clave']][$fila['GradoAcademico']] = true;
            }
        } catch (PDOException $e) {
            error_log('RequisitosTitulacionModelo: ' . $e->getMessage()); // migración pendiente: nada exigido
        }
        return $mapa;
    }

    /**
     * Marca o desmarca un certificado como exigido para la titulación en un grado.
     * @return array ['success' => bool, 'mensaje' => string]
     */
    public static function GuardarRequisitoTitulacionModelo($clave, $grado, $exigir)
    {
        $clave = (string)$clave;
        $grado = self::ClaveGrado($grado);
        if (!array_key_exists($grado, self::GradosAcademicos())) {
            return ['success' => false, 'mensaje' => 'Grado académico no válido'];
        }
        $pdo = Conexion::Conectar();
        if (preg_match('/^SD:(\d+)$/', $clave, $m)) {
            $stmt = $pdo->prepare("SELECT 1 FROM certificadosindocumento WHERE IdCertificadoSD = :id");
            $stmt->execute([':id' => (int)$m[1]]);
            if (!$stmt->fetchColumn()) {
                return ['success' => false, 'mensaje' => 'Certificado no válido'];
            }
        } elseif (!array_key_exists($clave, self::TiposDocumento())) {
            return ['success' => false, 'mensaje' => 'Certificado no válido'];
        }
        $stmt = $pdo->prepare($exigir
            ? "INSERT IGNORE INTO certificadotitulacion (Clave, GradoAcademico) VALUES (:clave, :grado)"
            : "DELETE FROM certificadotitulacion WHERE Clave = :clave AND GradoAcademico = :grado");
        $stmt->execute([':clave' => $clave, ':grado' => $grado]);
        return ['success' => true, 'mensaje' => $exigir ? 'Se exige para la titulación' : 'Ya no se exige para la titulación'];
    }

    /**
     * Para un estudiante en un programa: qué certificados exige su grado para titularse y
     * cuáles ya tiene (generados o registrados en ESE programa).
     * @return array ['grado', 'total', 'cumplidos', 'completo', 'certificados' => [
     *                 ['clave', 'nombre', 'precio', 'pagado', 'fecha', 'voucher'], ...]]
     */
    public static function EstadoTitulacionModelo($estudianteID, $programaID)
    {
        $pdo = Conexion::Conectar();
        $stmt = $pdo->prepare("SELECT GradoAcademico FROM programa WHERE ProgramaID = :id");
        $stmt->execute([':id' => (int)$programaID]);
        $grado = self::ClaveGrado($stmt->fetchColumn());

        // Lo que ya tiene en este programa (el más reciente de cada certificado)
        $stmt = $pdo->prepare("
            SELECT TipoDocumento, IdCertificadoSD, NumeroVaucher, FechaPago, FechaEmision
            FROM documentoemitido
            WHERE EstudianteID = :estudianteID AND ProgramaID = :programaID
            ORDER BY FechaEmision DESC, IdDocumento DESC
        ");
        $stmt->execute([':estudianteID' => (int)$estudianteID, ':programaID' => (int)$programaID]);
        $tiene = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $doc) {
            $clave = $doc['TipoDocumento'] === self::TIPO_SIN_DOCUMENTO ? 'SD:' . (int)$doc['IdCertificadoSD'] : $doc['TipoDocumento'];
            $tiene[$clave] = $tiene[$clave] ?? $doc;
        }

        $precios = self::PreciosCertificados();
        $catalogo = [];
        foreach (self::ListarSinDocumentoModelo(false) as $c) {
            $catalogo['SD:' . $c['IdCertificadoSD']] = $c;
        }

        $certificados = [];
        foreach (self::RequisitosTitulacionModelo() as $clave => $grados) {
            if (empty($grados[$grado])) continue;
            if (isset(self::TiposDocumento()[$clave])) {
                $nombre = self::TiposDocumento()[$clave];
                $precio = $precios[$clave][$grado] ?? null;
            } elseif (isset($catalogo[$clave])) {
                $nombre = $catalogo[$clave]['Nombre'];
                $precio = (float)$catalogo[$clave]['Monto'] ?: null;
            } else {
                continue;
            }
            $doc = $tiene[$clave] ?? null;
            $certificados[] = [
                'clave' => $clave,
                'nombre' => $nombre,
                'precio' => $precio,
                'pagado' => (bool)$doc,
                'fecha' => $doc ? ($doc['FechaPago'] ?: substr($doc['FechaEmision'], 0, 10)) : null,
                'voucher' => $doc['NumeroVaucher'] ?? null,
            ];
        }
        // Primero lo que falta
        usort($certificados, function ($a, $b) { return [$a['pagado'], $a['nombre']] <=> [$b['pagado'], $b['nombre']]; });
        $cumplidos = count(array_filter($certificados, function ($c) { return $c['pagado']; }));
        return [
            'grado' => $grado,
            'total' => count($certificados),
            'cumplidos' => $cumplidos,
            'completo' => $certificados && $cumplidos === count($certificados),
            'certificados' => $certificados,
        ];
    }

    /* Programas para el filtro del reporte de pagos (también los ya cerrados: sus egresados piden certificados) */
    public static function ProgramasFiltroModelo()
    {
        $stmt = Conexion::Conectar()->prepare("SELECT ProgramaID, NombrePrograma, Codigo, Sede, GradoAcademico FROM programa ORDER BY FechaInicio DESC, NombrePrograma");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Bitácora de documentos emitidos, opcionalmente filtrada por CI, por un estudiante+programa
       (todas sus emisiones, también varias del mismo tipo), por fechas y por tipo */
    public static function ListarDocumentosEmitidosModelo($ci = null, $limite = 100, array $filtros = [])
    {
        $sql = "
            SELECT
                d.IdDocumento, d.TipoDocumento, d.NombreCertificado, d.EstudianteID, d.ProgramaID, d.NombreEstudiante,
                d.NumeroVaucher, d.Monto, d.FechaPago, d.FechaEmision, d.Estado, d.FechaAprobacion,
                p.NombrePrograma, p.GradoAcademico,
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
        // Sede y grado académico: los del programa del documento
        if (!empty($filtros['sede'])) {
            $sql .= " AND p.Sede = :sede ";
            $parametros[':sede'] = $filtros['sede'];
        }
        if (!empty($filtros['grado'])) {
            $sql .= " AND p.GradoAcademico = :grado ";
            $parametros[':grado'] = $filtros['grado'];
        }
        if (!empty($filtros['idDocumento'])) {
            $sql .= " AND d.IdDocumento = :idDocumento ";
            $parametros[':idDocumento'] = (int)$filtros['idDocumento'];
        }
        if (!empty($filtros['estudianteID'])) {
            $sql .= " AND d.EstudianteID = :estudianteID ";
            $parametros[':estudianteID'] = (int)$filtros['estudianteID'];
        }
        if (!empty($filtros['programaID'])) {
            $sql .= " AND d.ProgramaID = :programaID ";
            $parametros[':programaID'] = (int)$filtros['programaID'];
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
