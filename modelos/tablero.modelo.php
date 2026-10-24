<?php
/**
 * Modelo del Tablero (Panel principal)
 * Datos de resumen que el panel muestra según el tipo de usuario:
 *  - Administración/Secretaría: indicadores de gestión, últimos pagos.
 *  - Estudiante: sus programas con el estado de pago (mismo cálculo que el modal
 *    "Gestión de Pagos": PagoModuloModelo::ObtenerResumenPagoProgramaModelo).
 *  - Docente: sus módulos asignados.
 *  - Todos: próximos eventos del calendario académico.
 * Las clínicas se informan aparte y nunca se suman a matrícula ni cuotas.
 */

require_once file_exists(__DIR__ . '/conexion.modelo.php') ? __DIR__ . '/conexion.modelo.php' : __DIR__ . '/../core/conexion.modelo.php';
require_once __DIR__ . '/modulopagos_core.php';

class TableroModelo
{
    /**
     * Indicadores de gestión para Administración/Secretaría.
     * "Recaudado del mes" = matrículas + cuotas cobradas en el mes en curso;
     * las clínicas del mes van en su propio indicador.
     * @return array
     */
    public static function ResumenGestionModelo()
    {
        $vacio = [
            'estudiantesActivos' => 0, 'programasActivos' => 0, 'docentesConModulo' => 0,
            'preregistrosPendientes' => 0, 'solicitudesPendientes' => 0,
            'ordenesPendientes' => 0, 'montoOrdenesPendientes' => 0, 'ordenesVencidas' => 0,
            'recaudadoMes' => 0, 'pagosMes' => 0, 'clinicasMes' => 0,
        ];

        try {
            $pdo = Conexion::Conectar();
            $fila = $pdo->query(
                "SELECT
                    (SELECT COUNT(DISTINCT EstudianteID) FROM estudianteprograma WHERE Estado = 'ACTIVO') AS estudiantesActivos,
                    (SELECT COUNT(*) FROM programa WHERE Estado = 1) AS programasActivos,
                    (SELECT COUNT(DISTINCT DocenteID) FROM modulos WHERE DocenteID IS NOT NULL AND estadomodulo = 'ACTIVO') AS docentesConModulo,
                    (SELECT COUNT(*) FROM ordenpago WHERE Estado = 'PENDIENTE' AND idinscripcion IS NULL) AS preregistrosPendientes,
                    (SELECT COUNT(*) FROM solicitudinscripcion WHERE Estado = 'PENDIENTE') AS solicitudesPendientes,
                    (SELECT COUNT(*) FROM ordenpago WHERE Estado = 'PENDIENTE' AND idinscripcion IS NOT NULL AND ListaModulos IS NULL AND COALESCE(Observaciones, '') <> 'CLINICA') AS ordenesPendientes,
                    (SELECT COALESCE(SUM(MontoTotal), 0) FROM ordenpago WHERE Estado = 'PENDIENTE' AND idinscripcion IS NOT NULL AND ListaModulos IS NULL AND COALESCE(Observaciones, '') <> 'CLINICA') AS montoOrdenesPendientes,
                    (SELECT COUNT(*) FROM ordenpago WHERE Estado = 'PENDIENTE' AND ListaModulos IS NULL AND COALESCE(Observaciones, '') <> 'CLINICA'
                        AND FechaVencimiento IS NOT NULL AND FechaVencimiento < CURDATE()) AS ordenesVencidas,
                    (
                        (SELECT COALESCE(SUM(montocuota), 0) FROM pagomodulo
                         WHERE Estado = 'PAGADO' AND fechapago >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND fechapago <= CURDATE())
                        +
                        (SELECT COALESCE(SUM(montoPagado), 0) FROM estudianteprograma
                         WHERE FechaInscripcion >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND FechaInscripcion < CURDATE() + INTERVAL 1 DAY)
                    ) AS recaudadoMes,
                    (SELECT COUNT(*) FROM pagomodulo
                     WHERE Estado = 'PAGADO' AND fechapago >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND fechapago <= CURDATE()) AS pagosMes,
                    (SELECT COALESCE(SUM(Monto), 0) FROM pagoclinica
                     WHERE Estado = 'PAGADO' AND FechaPago >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND FechaPago <= CURDATE()) AS clinicasMes"
            )->fetch(PDO::FETCH_ASSOC);

            return $fila ? array_merge($vacio, $fila) : $vacio;
        } catch (PDOException $e) {
            error_log("Error en ResumenGestionModelo: " . $e->getMessage());
            return $vacio;
        }
    }

    /**
     * Últimos pagos registrados (cuotas y clínicas), para Administración/Secretaría.
     * @param int $limite
     * @return array
     */
    public static function UltimosPagosModelo($limite = 6)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT * FROM (
                    SELECT 'CUOTA' AS Concepto, pm.montocuota AS Monto, pm.fechapago AS Fecha, pm.FechaRegistro AS Registro,
                           CONCAT(e.Nombre, ' ', e.Apaterno) AS Estudiante, e.Ci, p.NombrePrograma
                    FROM pagomodulo pm
                    INNER JOIN estudianteprograma ep ON ep.idInscripcion = pm.idinscripcion
                    INNER JOIN estudiante e ON e.EstudianteID = ep.EstudianteID
                    INNER JOIN programa p ON p.ProgramaID = ep.ProgramaID
                    WHERE pm.Estado = 'PAGADO'
                    UNION ALL
                    SELECT 'CLINICA', pcl.Monto, pcl.FechaPago, pcl.FechaRegistro,
                           CONCAT(e.Nombre, ' ', e.Apaterno), e.Ci, p.NombrePrograma
                    FROM pagoclinica pcl
                    INNER JOIN estudianteprograma ep ON ep.idInscripcion = pcl.idinscripcion
                    INNER JOIN estudiante e ON e.EstudianteID = ep.EstudianteID
                    INNER JOIN programa p ON p.ProgramaID = ep.ProgramaID
                    WHERE pcl.Estado = 'PAGADO'
                ) pagos
                ORDER BY Registro DESC
                LIMIT :limite"
            );
            $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en UltimosPagosModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Próximos eventos del calendario académico (desde hoy, N días).
     * @param int[]|null $programaIDs Solo estos programas (Estudiante); null = todos
     * @param int|null $docenteID Eventos del docente o de los programas donde dicta (Docente)
     * @param int $dias
     * @param int $limite
     * @return array
     */
    public static function ProximosEventosModelo($programaIDs = null, $docenteID = null, $dias = 30, $limite = 6)
    {
        try {
            $sql = "SELECT ce.IdEvento, ce.TipoEvento, ce.Titulo, ce.FechaInicio, ce.FechaFin, p.NombrePrograma
                    FROM calendario_evento ce
                    INNER JOIN programa p ON p.ProgramaID = ce.ProgramaID
                    WHERE ce.Estado = 'ACTIVO'
                    AND ce.FechaFin >= NOW()
                    AND ce.FechaInicio < CURDATE() + INTERVAL :dias DAY";
            $params = [':dias' => (int)$dias];

            if (is_array($programaIDs)) {
                if (empty($programaIDs)) {
                    return [];
                }
                $marcas = [];
                foreach (array_values($programaIDs) as $i => $id) {
                    $marcas[] = ':p' . $i;
                    $params[':p' . $i] = (int)$id;
                }
                $sql .= " AND ce.ProgramaID IN (" . implode(',', $marcas) . ")";
            }
            if ($docenteID) {
                $sql .= " AND (ce.DocenteID = :doc1 OR ce.ProgramaID IN (SELECT ProgramaId FROM modulos WHERE DocenteID = :doc2))";
                $params[':doc1'] = (int)$docenteID;
                $params[':doc2'] = (int)$docenteID;
            }

            $sql .= " ORDER BY ce.FechaInicio ASC LIMIT " . (int)$limite;

            $stmt = Conexion::Conectar()->prepare($sql);
            foreach ($params as $clave => $valor) {
                $stmt->bindValue($clave, $valor, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ProximosEventosModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Programas del estudiante con su estado de pago y la orden pendiente (si hay).
     * @param int $estudianteID
     * @return array
     */
    public static function ProgramasEstudianteModelo($estudianteID)
    {
        try {
            $pdo = Conexion::Conectar();
            $stmt = $pdo->prepare(
                "SELECT ep.idInscripcion, ep.ProgramaID, ep.FechaInscripcion,
                        p.NombrePrograma, p.GradoAcademico, p.Codigo, p.Sede
                 FROM estudianteprograma ep
                 INNER JOIN programa p ON p.ProgramaID = ep.ProgramaID
                 WHERE ep.EstudianteID = :estudianteID AND ep.Estado = 'ACTIVO'
                 ORDER BY ep.FechaInscripcion DESC"
            );
            $stmt->execute([':estudianteID' => $estudianteID]);
            $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmtOrden = $pdo->prepare(
                "SELECT COUNT(*) AS cantidad, COALESCE(SUM(MontoTotal), 0) AS monto, MIN(FechaVencimiento) AS vence
                 FROM ordenpago
                 WHERE idinscripcion = :idinscripcion AND Estado = 'PENDIENTE' AND ListaModulos IS NULL AND COALESCE(Observaciones, '') <> 'CLINICA'"
            );

            foreach ($programas as &$programa) {
                $programa['resumen'] = PagoModuloModelo::ObtenerResumenPagoProgramaModelo($programa['idInscripcion'], $programa['ProgramaID']);
                $stmtOrden->execute([':idinscripcion' => $programa['idInscripcion']]);
                $programa['ordenes'] = $stmtOrden->fetch(PDO::FETCH_ASSOC);
            }
            unset($programa);

            return $programas;
        } catch (PDOException $e) {
            error_log("Error en ProgramasEstudianteModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Módulos asignados a un docente (los activos primero, luego por fecha).
     * @param int $docenteID
     * @return array
     */
    public static function ModulosDocenteModelo($docenteID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT m.Idmodulo, m.codigomodulo, m.nombremodulo, m.FechaInicio, m.FechaFinal, m.estadomodulo,
                        p.NombrePrograma, p.GradoAcademico
                 FROM modulos m
                 INNER JOIN programa p ON p.ProgramaID = m.ProgramaId
                 WHERE m.DocenteID = :docenteID
                 ORDER BY (m.estadomodulo = 'ACTIVO') DESC, m.FechaInicio DESC
                 LIMIT 8"
            );
            $stmt->execute([':docenteID' => $docenteID]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ModulosDocenteModelo: " . $e->getMessage());
            return [];
        }
    }
}
