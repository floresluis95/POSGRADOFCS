<?php
require_once __DIR__ . '/../core/conexion.modelo.php';
require_once __DIR__ . '/configuracion.modelo.php';

/**
 * Préstamo del ejemplar físico de una tesis (tabla prestamotesis,
 * mantenimiento/crear_prestamotesis.sql).
 *
 *   SOLICITADO --(ADM reserva)--> APROBADO --(ADM entrega)--> PRESTADO --(ADM recibe)--> DEVUELTO
 *        |                            |
 *        +--(ADM entrega directo)-----+--> PRESTADO
 *        +--(ADM rechaza / solicitante cancela)--> RECHAZADO / CANCELADO
 *
 * Solicitan los estudiantes y docentes; el resto lo hace solo el Administrador (decidido por
 * Luis, 2026-11-11). Hay UN ejemplar por tesis: mientras esté APROBADO o PRESTADO a alguien,
 * no se puede reservar ni entregar a otro (sí se puede solicitar: queda en espera).
 */
class PrestamoTesisModelo
{
    const ACTIVOS = ['SOLICITADO', 'APROBADO', 'PRESTADO'];
    const OCUPAN_EJEMPLAR = ['APROBADO', 'PRESTADO'];
    const MAX_ACTIVOS_POR_PERSONA = 3;
    const PLAZO_POR_DEFECTO = 7;

    private static $tablaExiste = null;

    public static function TablaExiste()
    {
        if (self::$tablaExiste === null) {
            try {
                self::$tablaExiste = (bool) Conexion::Conectar()->query("SHOW TABLES LIKE 'prestamotesis'")->fetchColumn();
            } catch (PDOException $e) {
                self::$tablaExiste = false;
            }
        }
        return self::$tablaExiste;
    }

    /* Días de préstamo (Configuración > Parámetros académicos), de 1 a 90 */
    public static function PlazoDias()
    {
        $dias = (int) ConfiguracionModelo::Obtener('PLAZO_PRESTAMO_TESIS', self::PLAZO_POR_DEFECTO);
        return ($dias >= 1 && $dias <= 90) ? $dias : self::PLAZO_POR_DEFECTO;
    }

    /* Columna de la persona según el tipo de cuenta */
    private static function ColumnaPersona($tipo)
    {
        return $tipo === 'DOC' ? 'DocenteID' : 'EstudianteID';
    }

    private static function Resultado($ok, $mensaje)
    {
        return ['success' => $ok, 'mensaje' => $mensaje];
    }

    /* ---------------- Estudiante / docente ---------------- */

    public static function Solicitar($idTrabajo, $tipo, $personaID)
    {
        if (!self::TablaExiste()) {
            return self::Resultado(false, 'Los préstamos aún no están habilitados (falta mantenimiento/crear_prestamotesis.sql)');
        }
        $idTrabajo = (int) $idTrabajo;
        $personaID = (int) $personaID;
        if (!in_array($tipo, ['EST', 'DOC'], true) || $personaID <= 0 || $idTrabajo <= 0) {
            return self::Resultado(false, 'Solicitud no válida');
        }
        $col = self::ColumnaPersona($tipo);
        $db = Conexion::Conectar();
        try {
            $db->beginTransaction();
            // Bloquea la tesis: dos clics seguidos no crean dos solicitudes
            $stmt = $db->prepare("SELECT IdTrabajo FROM trabajogrado
                                  WHERE IdTrabajo = ? AND FechaAprobacion IS NOT NULL AND TRIM(TituloTrabajo) <> '' FOR UPDATE");
            $stmt->execute([$idTrabajo]);
            if (!$stmt->fetchColumn()) {
                $db->rollBack();
                return self::Resultado(false, 'Solo se pueden solicitar tesis aprobadas');
            }
            $stmt = $db->prepare("SELECT COUNT(*) FROM prestamotesis WHERE IdTrabajo = ? AND $col = ? AND Estado IN ('SOLICITADO','APROBADO','PRESTADO')");
            $stmt->execute([$idTrabajo, $personaID]);
            if ($stmt->fetchColumn() > 0) {
                $db->rollBack();
                return self::Resultado(false, 'Ya tiene una solicitud o préstamo de esta tesis (vea Mis préstamos)');
            }
            $stmt = $db->prepare("SELECT COUNT(*) FROM prestamotesis WHERE $col = ? AND Estado IN ('SOLICITADO','APROBADO','PRESTADO')");
            $stmt->execute([$personaID]);
            if ($stmt->fetchColumn() >= self::MAX_ACTIVOS_POR_PERSONA) {
                $db->rollBack();
                return self::Resultado(false, 'Puede tener como máximo ' . self::MAX_ACTIVOS_POR_PERSONA . ' tesis solicitadas o prestadas a la vez. Devuelva o cancele alguna antes de pedir otra');
            }
            $db->prepare("INSERT INTO prestamotesis (IdTrabajo, TipoSolicitante, $col, Estado, FechaSolicitud)
                          VALUES (?, ?, ?, 'SOLICITADO', NOW())")
               ->execute([$idTrabajo, $tipo, $personaID]);
            $db->commit();
            return self::Resultado(true, 'Solicitud enviada. Le avisaremos aquí cuando el ejemplar esté listo para recoger');
        } catch (PDOException $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('PrestamoTesisModelo::Solicitar: ' . $e->getMessage());
            return self::Resultado(false, 'No se pudo registrar la solicitud');
        }
    }

    /* El solicitante cancela su solicitud o reserva (todavía no se la entregaron) */
    public static function Cancelar($idPrestamo, $tipo, $personaID)
    {
        if (!self::TablaExiste()) {
            return self::Resultado(false, 'Los préstamos aún no están habilitados');
        }
        $col = self::ColumnaPersona($tipo);
        $stmt = Conexion::Conectar()->prepare("
            UPDATE prestamotesis SET Estado = 'CANCELADO', FechaCierre = NOW(), ResponsableCierre = 'El solicitante'
            WHERE IdPrestamo = ? AND TipoSolicitante = ? AND $col = ? AND Estado IN ('SOLICITADO', 'APROBADO')");
        $stmt->execute([(int) $idPrestamo, $tipo, (int) $personaID]);
        return $stmt->rowCount() > 0
            ? self::Resultado(true, 'Solicitud cancelada')
            : self::Resultado(false, 'Esa solicitud ya no se puede cancelar (quizá ya le entregaron el ejemplar)');
    }

    /* Préstamos de la persona: activos primero, luego el historial */
    public static function MisPrestamos($tipo, $personaID)
    {
        if (!self::TablaExiste()) {
            return [];
        }
        $col = self::ColumnaPersona($tipo);
        $stmt = Conexion::Conectar()->prepare("
            SELECT pt.*, tg.TituloTrabajo, e.Nombre, e.Apaterno, e.Amaterno, p.NombrePrograma, p.GradoAcademico,
                   (SELECT COUNT(*) FROM prestamotesis o WHERE o.IdTrabajo = pt.IdTrabajo
                     AND o.Estado IN ('APROBADO','PRESTADO') AND o.IdPrestamo <> pt.IdPrestamo) AS OcupadaPorOtro
            FROM prestamotesis pt
            INNER JOIN trabajogrado tg ON tg.IdTrabajo = pt.IdTrabajo
            INNER JOIN estudiante e ON e.EstudianteID = tg.EstudianteID
            INNER JOIN programa p ON p.ProgramaID = tg.ProgramaID
            WHERE pt.TipoSolicitante = ? AND pt.$col = ?
            ORDER BY FIELD(pt.Estado, 'PRESTADO', 'APROBADO', 'SOLICITADO') = 0, FIELD(pt.Estado, 'PRESTADO', 'APROBADO', 'SOLICITADO'),
                     pt.FechaSolicitud DESC
            LIMIT 100");
        $stmt->execute([$tipo, (int) $personaID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* IdTrabajo => estado del préstamo activo de la persona (para los botones del catálogo) */
    public static function ActivosDePersona($tipo, $personaID)
    {
        if (!self::TablaExiste()) {
            return [];
        }
        $col = self::ColumnaPersona($tipo);
        $stmt = Conexion::Conectar()->prepare("
            SELECT IdTrabajo, Estado, IdPrestamo, FechaLimite FROM prestamotesis
            WHERE TipoSolicitante = ? AND $col = ? AND Estado IN ('SOLICITADO','APROBADO','PRESTADO')");
        $stmt->execute([$tipo, (int) $personaID]);
        $activos = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $activos[(int) $fila['IdTrabajo']] = $fila;
        }
        return $activos;
    }

    /* ---------------- Administrador ---------------- */

    /**
     * Préstamos para la gestión: 'pendientes' (SOLICITADO y APROBADO), 'prestadas' o
     * 'historial' (cerrados, los 200 últimos).
     */
    public static function ListarGestion($lista)
    {
        if (!self::TablaExiste()) {
            return [];
        }
        $where = [
            'pendientes' => "pt.Estado IN ('SOLICITADO', 'APROBADO')",
            'prestadas' => "pt.Estado = 'PRESTADO'",
            'historial' => "pt.Estado IN ('DEVUELTO', 'RECHAZADO', 'CANCELADO')",
        ][$lista] ?? "pt.Estado IN ('SOLICITADO', 'APROBADO')";
        $orden = [
            'pendientes' => "pt.FechaSolicitud ASC",
            'prestadas' => "pt.FechaLimite ASC",
            'historial' => "COALESCE(pt.FechaDevolucion, pt.FechaCierre) DESC",
        ][$lista] ?? "pt.FechaSolicitud ASC";

        $stmt = Conexion::Conectar()->query("
            SELECT pt.*, tg.TituloTrabajo,
                   CONCAT_WS(' ', ea.Nombre, ea.Apaterno, ea.Amaterno) AS Autor,
                   COALESCE(CONCAT_WS(' ', es.Nombre, es.Apaterno, es.Amaterno), CONCAT_WS(' ', d.Nombre, d.Apaterno, d.Amaterno)) AS Solicitante,
                   COALESCE(es.Ci, d.Ci) AS CiSolicitante,
                   COALESCE(es.Celular, d.Cel) AS CelularSolicitante,
                   (SELECT COUNT(*) FROM prestamotesis o WHERE o.IdTrabajo = pt.IdTrabajo
                     AND o.Estado IN ('APROBADO','PRESTADO') AND o.IdPrestamo <> pt.IdPrestamo) AS OcupadaPorOtro
            FROM prestamotesis pt
            INNER JOIN trabajogrado tg ON tg.IdTrabajo = pt.IdTrabajo
            INNER JOIN estudiante ea ON ea.EstudianteID = tg.EstudianteID
            LEFT JOIN estudiante es ON pt.TipoSolicitante = 'EST' AND es.EstudianteID = pt.EstudianteID
            LEFT JOIN docente d ON pt.TipoSolicitante = 'DOC' AND d.DocenteID = pt.DocenteID
            WHERE $where
            ORDER BY $orden
            LIMIT 200");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Para las pestañas y el menú: solicitudes por atender, prestadas y vencidas */
    public static function Contadores()
    {
        $vacio = ['solicitadas' => 0, 'pendientes' => 0, 'prestadas' => 0, 'vencidas' => 0];
        if (!self::TablaExiste()) {
            return $vacio;
        }
        try {
            $fila = Conexion::Conectar()->query("
                SELECT SUM(Estado = 'SOLICITADO') AS solicitadas,
                       SUM(Estado IN ('SOLICITADO', 'APROBADO')) AS pendientes,
                       SUM(Estado = 'PRESTADO') AS prestadas,
                       SUM(Estado = 'PRESTADO' AND FechaLimite < CURDATE()) AS vencidas
                FROM prestamotesis WHERE Estado IN ('SOLICITADO', 'APROBADO', 'PRESTADO')")->fetch(PDO::FETCH_ASSOC);
            return array_map('intval', $fila ?: $vacio);
        } catch (PDOException $e) {
            return $vacio;
        }
    }

    /**
     * Acciones del Administrador sobre un préstamo:
     *  reservar  SOLICITADO → APROBADO   (el ejemplar debe estar libre)
     *  entregar  SOLICITADO/APROBADO → PRESTADO, fecha límite = hoy + plazo
     *  rechazar  SOLICITADO/APROBADO → RECHAZADO (con motivo)
     *  devolver  PRESTADO → DEVUELTO (observación opcional)
     */
    public static function AccionAdministrador($accion, $idPrestamo, $responsable, $texto = '')
    {
        if (!self::TablaExiste()) {
            return self::Resultado(false, 'Falta crear la tabla de préstamos (mantenimiento/crear_prestamotesis.sql)');
        }
        $desde = [
            'reservar' => ['SOLICITADO'],
            'entregar' => ['SOLICITADO', 'APROBADO'],
            'rechazar' => ['SOLICITADO', 'APROBADO'],
            'devolver' => ['PRESTADO'],
        ];
        if (!isset($desde[$accion])) {
            return self::Resultado(false, 'Acción no reconocida');
        }
        $texto = mb_substr(trim(preg_replace('/\s+/u', ' ', (string) $texto)), 0, 300);
        if ($accion === 'rechazar' && mb_strlen($texto) < 5) {
            return self::Resultado(false, 'Escriba el motivo del rechazo');
        }

        $db = Conexion::Conectar();
        try {
            $db->beginTransaction();
            $stmt = $db->prepare("SELECT IdPrestamo, IdTrabajo, Estado FROM prestamotesis WHERE IdPrestamo = ? FOR UPDATE");
            $stmt->execute([(int) $idPrestamo]);
            $prestamo = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$prestamo || !in_array($prestamo['Estado'], $desde[$accion], true)) {
                $db->rollBack();
                return self::Resultado(false, 'El préstamo cambió de estado mientras tanto. Actualice la página');
            }

            if ($accion === 'reservar' || ($accion === 'entregar' && $prestamo['Estado'] === 'SOLICITADO')) {
                // Un solo ejemplar: se bloquea la tesis para que dos reservas a la vez no pasen las dos
                $db->prepare("SELECT IdTrabajo FROM trabajogrado WHERE IdTrabajo = ? FOR UPDATE")->execute([$prestamo['IdTrabajo']]);
                $stmt = $db->prepare("SELECT COUNT(*) FROM prestamotesis
                                      WHERE IdTrabajo = ? AND Estado IN ('APROBADO', 'PRESTADO') AND IdPrestamo <> ?");
                $stmt->execute([$prestamo['IdTrabajo'], $prestamo['IdPrestamo']]);
                if ($stmt->fetchColumn() > 0) {
                    $db->rollBack();
                    return self::Resultado(false, 'El ejemplar está reservado o prestado a otra persona. Espere a que lo devuelvan');
                }
            }

            $sql = [
                'reservar' => "Estado = 'APROBADO', FechaAprobacion = NOW(), ResponsableAprobacion = :resp",
                'entregar' => "Estado = 'PRESTADO', FechaEntrega = NOW(), FechaLimite = DATE_ADD(CURDATE(), INTERVAL :dias DAY),
                               ResponsableEntrega = :resp, FechaAprobacion = COALESCE(FechaAprobacion, NOW()),
                               ResponsableAprobacion = COALESCE(ResponsableAprobacion, :resp2)",
                'rechazar' => "Estado = 'RECHAZADO', FechaCierre = NOW(), ResponsableCierre = :resp, Motivo = :texto",
                'devolver' => "Estado = 'DEVUELTO', FechaDevolucion = NOW(), ResponsableDevolucion = :resp, Motivo = :texto",
            ][$accion];
            $parametros = [':resp' => $responsable, ':id' => (int) $idPrestamo];
            if ($accion === 'entregar') {
                $parametros[':dias'] = self::PlazoDias();
                $parametros[':resp2'] = $responsable;
            }
            if ($accion === 'rechazar' || $accion === 'devolver') {
                $parametros[':texto'] = $texto !== '' ? $texto : null;
            }
            $db->prepare("UPDATE prestamotesis SET $sql WHERE IdPrestamo = :id")->execute($parametros);
            $db->commit();

            $mensajes = [
                'reservar' => 'Ejemplar reservado: el solicitante verá que puede pasar a recogerlo',
                'entregar' => 'Préstamo registrado. Debe devolverlo hasta el ' . date('d/m/Y', strtotime('+' . self::PlazoDias() . ' days')),
                'rechazar' => 'Solicitud rechazada',
                'devolver' => 'Devolución registrada: el ejemplar vuelve a estar disponible',
            ];
            return self::Resultado(true, $mensajes[$accion]);
        } catch (PDOException $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log("PrestamoTesisModelo::AccionAdministrador($accion): " . $e->getMessage());
            return self::Resultado(false, 'No se pudo guardar');
        }
    }
}
