<?php
/**
 * Modelo de Reportes de Módulos
 * Gestiona las consultas para reportes de inscritos
 */

require_once 'conexion.modelo.php';

class ReporteModulosModelo
{
    /**
     * Obtener grados académicos únicos
     * @return array
     */
    public static function ObtenerGradosAcademicosModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT DISTINCT GradoAcademico
                FROM programa
                WHERE Estado = 1
                ORDER BY GradoAcademico ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerGradosAcademicosModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener programas por grado académico
     * @param string $grado
     * @return array
     */
    public static function ObtenerProgramasPorGradoModelo($grado)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    ProgramaID,
                    NombrePrograma,
                    Codigo,
                    GradoAcademico
                FROM programa
                WHERE GradoAcademico = :grado
                AND Estado = 1
                ORDER BY NombrePrograma ASC"
            );
            $stmt->bindParam(":grado", $grado, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerProgramasPorGradoModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener módulos por programa
     * @param int $programaID
     * @return array
     */
    public static function ObtenerModulosPorProgramaModelo($programaID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    Idmodulo,
                    codigomodulo,
                    nombremodulo
                FROM modulos
                WHERE ProgramaId = :programaID
                AND estadomodulo = 'ACTIVO'
                ORDER BY codigomodulo ASC"
            );
            $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerModulosPorProgramaModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estadísticas generales del sistema
     * @return array
     */
    public static function ObtenerEstadisticasGeneralesModelo()
    {
        try {
            $conexion = Conexion::Conectar();

            // Contar programas activos
            $stmtProgramas = $conexion->prepare("SELECT COUNT(*) as total FROM programa WHERE Estado = 1");
            $stmtProgramas->execute();
            $totalProgramas = $stmtProgramas->fetch(PDO::FETCH_ASSOC)['total'];

            // Contar módulos activos
            $stmtModulos = $conexion->prepare("SELECT COUNT(*) as total FROM modulos WHERE estadomodulo = 'ACTIVO'");
            $stmtModulos->execute();
            $totalModulos = $stmtModulos->fetch(PDO::FETCH_ASSOC)['total'];

            // Contar estudiantes inscritos
            $stmtEstudiantes = $conexion->prepare("SELECT COUNT(DISTINCT EstudianteID) as total FROM estudianteprograma");
            $stmtEstudiantes->execute();
            $totalEstudiantes = $stmtEstudiantes->fetch(PDO::FETCH_ASSOC)['total'];

            // Contar docentes activos
            $stmtDocentes = $conexion->prepare("SELECT COUNT(*) as total FROM docente WHERE Estado = 1");
            $stmtDocentes->execute();
            $totalDocentes = $stmtDocentes->fetch(PDO::FETCH_ASSOC)['total'];

            // Contar pagos de módulos
            $stmtPagos = $conexion->prepare("SELECT COUNT(*) as total FROM pagomodulo WHERE Estado != 'ANULADO'");
            $stmtPagos->execute();
            $totalPagos = $stmtPagos->fetch(PDO::FETCH_ASSOC)['total'];

            // Docentes con al menos un módulo asignado
            $docentesAsignados = $conexion->query("SELECT COUNT(DISTINCT DocenteID) FROM modulos WHERE DocenteID IS NOT NULL")->fetchColumn();

            return [
                'totalProgramas' => $totalProgramas,
                'docentesAsignados' => (int)$docentesAsignados,
                'totalModulos' => $totalModulos,
                'totalEstudiantes' => $totalEstudiantes,
                'totalDocentes' => $totalDocentes,
                'totalPagos' => $totalPagos
            ];
        } catch (PDOException $e) {
            error_log("Error en ObtenerEstadisticasGeneralesModelo: " . $e->getMessage());
            return [
                'totalProgramas' => 0,
                'totalModulos' => 0,
                'totalEstudiantes' => 0,
                'totalDocentes' => 0,
                'totalPagos' => 0
            ];
        }
    }

    /**
     * Obtener conteo de módulos e inscritos por programa
     * @return array
     */
    public static function ObtenerConteoModulosPorProgramaModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    p.ProgramaID,
                    p.NombrePrograma,
                    p.Codigo,
                    p.GradoAcademico,
                    p.Sede,
                    COUNT(DISTINCT CASE WHEN m.estadomodulo = 'ACTIVO' THEN m.Idmodulo END) as TotalModulos,
                    COUNT(DISTINCT m.Idmodulo) as TotalModulosRegistrados,
                    COUNT(DISTINCT m.DocenteID) as TotalDocentes,
                    COUNT(DISTINCT ep.EstudianteID) as TotalInscritos,
                    -- Avance académico por fechas (o validación/cierre del módulo)
                    COUNT(DISTINCT CASE WHEN m.FechaFinal < CURDATE() OR m.estadomodulo IN ('VALIDADO', 'CERRADO') THEN m.Idmodulo END) as ModulosFinalizados,
                    COUNT(DISTINCT CASE WHEN CURDATE() BETWEEN m.FechaInicio AND m.FechaFinal
                                         AND m.estadomodulo NOT IN ('VALIDADO', 'CERRADO') THEN m.Idmodulo END) as ModulosEnCurso,
                    -- Periodo del programa (se ignoran fechas mal cargadas, anteriores al año 2000)
                    MIN(CASE WHEN YEAR(m.FechaInicio) >= 2000 THEN m.FechaInicio END) as FechaInicioPrograma,
                    MAX(CASE WHEN YEAR(m.FechaFinal) >= 2000 THEN m.FechaFinal END) as FechaFinPrograma
                FROM programa p
                LEFT JOIN modulos m ON p.ProgramaID = m.ProgramaId
                LEFT JOIN estudianteprograma ep ON p.ProgramaID = ep.ProgramaID
                WHERE p.Estado = 1
                GROUP BY p.ProgramaID, p.NombrePrograma, p.Codigo, p.GradoAcademico, p.Sede
                ORDER BY p.GradoAcademico ASC, p.NombrePrograma ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerConteoModulosPorProgramaModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Reporte de pagos por programa: cuántos estudiantes inscritos tiene cada programa,
     * cuántos con cada tipo de plan (Regular/Contado con descuento/Grupal), cuántos ya
     * completaron su pago, y cuánto se ha cobrado vs. cuánto falta cobrar en total.
     * El plan del programa vive en `pagoprograma` (uno por inscripción); lo pagado se
     * suma desde `pagomodulo` (las "cuotas del programa", ya no ligadas a módulos).
     * @param string|null $grado Filtrar por grado académico (opcional)
     * @return array
     */
    public static function ObtenerReportePagosPorProgramaModelo($grado = null)
    {
        try {
            $sql = "SELECT
                        d.ProgramaID,
                        d.NombrePrograma,
                        d.Codigo,
                        d.GradoAcademico,
                        d.Sede,
                        COUNT(d.idInscripcion) AS TotalInscritos,
                        SUM(CASE WHEN d.EstadoInscripcion != 'ABANDONO' AND d.TipoPlan = 'REGULAR' THEN 1 ELSE 0 END) AS CountRegular,
                        SUM(CASE WHEN d.EstadoInscripcion != 'ABANDONO' AND d.TipoPlan = 'DESCUENTO' THEN 1 ELSE 0 END) AS CountDescuento,
                        SUM(CASE WHEN d.EstadoInscripcion != 'ABANDONO' AND d.TipoPlan = 'GRUPAL' THEN 1 ELSE 0 END) AS CountGrupal,
                        SUM(CASE WHEN d.EstadoInscripcion != 'ABANDONO' AND d.idInscripcion IS NOT NULL AND d.TipoPlan IS NULL THEN 1 ELSE 0 END) AS CountSinPlan,
                        SUM(CASE WHEN d.EstadoInscripcion = 'ABANDONO' THEN 1 ELSE 0 END) AS CountAbandono,
                        SUM(CASE WHEN d.EstadoInscripcion != 'ABANDONO' THEN d.Completado ELSE 0 END) AS CountCompletados,
                        SUM(CASE WHEN d.EstadoInscripcion != 'ABANDONO' AND d.idInscripcion IS NOT NULL AND d.Completado = 0 THEN 1 ELSE 0 END) AS CountPendientes,
                        SUM(d.TieneDescuento) AS CountConDescuento,
                        COALESCE(SUM(d.CostoTotalPrograma), 0) AS CostoTotalProgramado,
                        COALESCE(SUM(d.TotalPagado), 0) AS TotalPagado,
                        COALESCE(SUM(d.CostoMatricula), 0) AS TotalMatriculas,
                        COALESCE(SUM(d.SaldoPendiente), 0) AS SaldoPendiente,
                        COALESCE(SUM(d.MontoDescuentoTotal), 0) AS MontoTotalDescuentos,
                        -- Clínicas: se reportan aparte, NO se suman a lo programado/pagado/saldo
                        -- de matrícula y cuotas (ver PagoClinicaModelo)
                        EXISTS (SELECT 1 FROM programacuenta pc
                                WHERE pc.ProgramaID = d.ProgramaID AND pc.TipoCuenta = 'CLINICA' AND pc.Estado = 1) AS TieneClinica,
                        (SELECT COALESCE(SUM(pcl.Monto), 0) FROM pagoclinica pcl
                         INNER JOIN ordenpago op ON op.IdOrdenPago = pcl.IdOrdenPago
                         WHERE op.ProgramaID = d.ProgramaID AND pcl.Estado = 'PAGADO') AS ClinicaCobrado,
                        (SELECT COUNT(*) FROM pagoclinica pcl
                         INNER JOIN ordenpago op ON op.IdOrdenPago = pcl.IdOrdenPago
                         WHERE op.ProgramaID = d.ProgramaID AND pcl.Estado = 'PAGADO') AS ClinicaNumPagos,
                        (SELECT COALESCE(SUM(op.MontoTotal), 0) FROM ordenpago op
                         WHERE op.ProgramaID = d.ProgramaID AND op.Observaciones = 'CLINICA' AND op.Estado = 'PENDIENTE') AS ClinicaPorCobrar,
                        (SELECT COUNT(*) FROM ordenpago op
                         WHERE op.ProgramaID = d.ProgramaID AND op.Observaciones = 'CLINICA' AND op.Estado = 'PENDIENTE') AS ClinicaNumPendientes
                    FROM (
                        SELECT
                            p.ProgramaID, p.NombrePrograma, p.Codigo, p.GradoAcademico, p.Sede,
                            ep.idInscripcion,
                            ep.Estado AS EstadoInscripcion,
                            pp.TipoPlan,
                            (COALESCE(pp.MontoTotalPagar, 0) + COALESCE(ep.costomatricula, 0)) AS CostoTotalPrograma,
                            (COALESCE(pagado.TotalPagadoInscripcion, 0) + COALESCE(ep.costomatricula, 0)) AS TotalPagado,
                            COALESCE(ep.costomatricula, 0) AS CostoMatricula,
                            GREATEST(COALESCE(pp.MontoTotalPagar, 0) - COALESCE(pagado.TotalPagadoInscripcion, 0), 0) AS SaldoPendiente,
                            (COALESCE(ep.montoDescuento, 0) + COALESCE(pp.MontoDescuento, 0)) AS MontoDescuentoTotal,
                            CASE WHEN (COALESCE(ep.montoDescuento, 0) > 0 OR COALESCE(pp.PorcentajeDescuento, 0) > 0) THEN 1 ELSE 0 END AS TieneDescuento,
                            CASE
                                WHEN pp.idInscripcion IS NOT NULL AND COALESCE(pagado.TotalPagadoInscripcion, 0) >= pp.MontoTotalPagar THEN 1
                                ELSE 0
                            END AS Completado
                        FROM programa p
                        LEFT JOIN estudianteprograma ep ON ep.ProgramaID = p.ProgramaID AND ep.Estado IN ('ACTIVO', 'CONFIRMADO', 'ABANDONO')
                        LEFT JOIN pagoprograma pp ON pp.idInscripcion = ep.idInscripcion
                        LEFT JOIN (
                            SELECT idinscripcion, SUM(montocuota) AS TotalPagadoInscripcion
                            FROM pagomodulo
                            WHERE Estado = 'PAGADO'
                            GROUP BY idinscripcion
                        ) pagado ON pagado.idinscripcion = ep.idInscripcion
                        WHERE p.Estado = 1";

            if (!empty($grado)) {
                $sql .= " AND p.GradoAcademico = :grado";
            }

            $sql .= "
                    ) d
                    GROUP BY d.ProgramaID, d.NombrePrograma, d.Codigo, d.GradoAcademico, d.Sede
                    ORDER BY d.GradoAcademico ASC, d.NombrePrograma ASC";

            $stmt = Conexion::Conectar()->prepare($sql);
            if (!empty($grado)) {
                $stmt->bindParam(":grado", $grado, PDO::PARAM_STR);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerReportePagosPorProgramaModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Reporte de pagos por estudiante, para un programa específico: plan asignado,
     * cuánto debe pagar en total, cuánto pagó y cuánto le falta.
     * @param int $programaID
     * @return array
     */
    public static function ObtenerReportePagosPorEstudianteModelo($programaID)
    {
        try {
            $sql = "SELECT
                        ep.idInscripcion,
                        e.EstudianteID,
                        CONCAT(e.Nombre, ' ', e.Apaterno, ' ', IFNULL(e.Amaterno, '')) AS NombreCompleto,
                        e.Ci,
                        e.Celular,
                        ep.Estado AS EstadoInscripcion,
                        pp.TipoPlan,
                        pp.NumeroCuotas,
                        pp.PorcentajeDescuento,
                        (COALESCE(ep.montoDescuento, 0) + COALESCE(pp.MontoDescuento, 0)) AS MontoDescuentoTotal,
                        COALESCE(ep.costomatricula, 0) AS CostoMatricula,
                        (COALESCE(pp.MontoTotalPagar, 0) + COALESCE(ep.costomatricula, 0)) AS CostoTotal,
                        (COALESCE(pagado.TotalPagado, 0) + COALESCE(ep.costomatricula, 0)) AS TotalPagado,
                        GREATEST(COALESCE(pp.MontoTotalPagar, 0) - COALESCE(pagado.TotalPagado, 0), 0) AS SaldoPendiente,
                        COALESCE(pagado.CuotasPagadas, 0) AS CuotasPagadas,
                        -- Clínicas del estudiante (aparte de matrícula y cuotas)
                        (SELECT COALESCE(SUM(pcl.Monto), 0) FROM pagoclinica pcl
                         WHERE pcl.idinscripcion = ep.idInscripcion AND pcl.Estado = 'PAGADO') AS ClinicaCobrado,
                        (SELECT COALESCE(SUM(op.MontoTotal), 0) FROM ordenpago op
                         WHERE op.idinscripcion = ep.idInscripcion AND op.Observaciones = 'CLINICA' AND op.Estado = 'PENDIENTE') AS ClinicaPorCobrar
                    FROM estudianteprograma ep
                    INNER JOIN estudiante e ON e.EstudianteID = ep.EstudianteID
                    LEFT JOIN pagoprograma pp ON pp.idInscripcion = ep.idInscripcion
                    LEFT JOIN (
                        SELECT idinscripcion, SUM(montocuota) AS TotalPagado, COUNT(*) AS CuotasPagadas
                        FROM pagomodulo
                        WHERE Estado = 'PAGADO'
                        GROUP BY idinscripcion
                    ) pagado ON pagado.idinscripcion = ep.idInscripcion
                    WHERE ep.ProgramaID = :programaID
                    AND ep.Estado IN ('ACTIVO', 'CONFIRMADO', 'ABANDONO')
                    ORDER BY (ep.Estado = 'ABANDONO') ASC, e.Apaterno ASC, e.Nombre ASC";

            $stmt = Conexion::Conectar()->prepare($sql);
            $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerReportePagosPorEstudianteModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener conteo de programas por sede
     * @return array
     */
    public static function ObtenerProgramasPorSedeModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    Sede,
                    COUNT(*) as TotalProgramas,
                    COUNT(DISTINCT GradoAcademico) as TiposGrado
                FROM programa
                WHERE Estado = 1
                GROUP BY Sede
                ORDER BY TotalProgramas DESC, Sede ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerProgramasPorSedeModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estadísticas por categoría (Grado Académico)
     * @return array
     */
    public static function ObtenerEstadisticasPorCategoriaModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    GradoAcademico,
                    COUNT(*) as TotalProgramas,
                    (SELECT COUNT(DISTINCT ep.EstudianteID)
                     FROM estudianteprograma ep
                     INNER JOIN programa p2 ON ep.ProgramaID = p2.ProgramaID
                     WHERE p2.GradoAcademico = p.GradoAcademico) as TotalEstudiantes,
                    (SELECT COUNT(*)
                     FROM modulos m
                     INNER JOIN programa p3 ON m.ProgramaId = p3.ProgramaID
                     WHERE p3.GradoAcademico = p.GradoAcademico
                     AND m.estadomodulo = 'ACTIVO') as TotalModulos
                FROM programa p
                WHERE Estado = 1
                GROUP BY GradoAcademico
                ORDER BY GradoAcademico ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerEstadisticasPorCategoriaModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Datos de la pestaña "Gráficos" del Reporte General (series por mes y órdenes
     * por cobrar). Lo cobrado sigue el mismo criterio que el reporte de pagos: la
     * matrícula cuenta como cobrada al inscribirse (FechaInscripcion), las cuotas por
     * su fecha de pago y las clínicas aparte.
     * @param string|null $grado Filtrar por grado académico (opcional)
     * @param int $meses Meses hacia atrás, incluido el actual
     * @return array
     */
    public static function ObtenerDatosGraficosModelo($grado, $meses)
    {
        try {
            $conexion = Conexion::Conectar();
            $desde = date('Y-m-01', strtotime('-' . ($meses - 1) . ' months', strtotime(date('Y-m-01'))));
            $filtroGrado = !empty($grado) ? ' AND p.GradoAcademico = :grado' : '';
            $ejecutar = function ($sql, $conFecha = true) use ($conexion, $grado, $desde) {
                $stmt = $conexion->prepare($sql);
                if ($conFecha) $stmt->bindValue(':desde', $desde);
                if (!empty($grado)) $stmt->bindValue(':grado', $grado);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            };

            $matriculas = $ejecutar(
                "SELECT DATE_FORMAT(ep.FechaInscripcion, '%Y-%m') AS Mes,
                        COALESCE(SUM(ep.costomatricula), 0) AS Monto, COUNT(*) AS Inscritos
                 FROM estudianteprograma ep
                 INNER JOIN programa p ON p.ProgramaID = ep.ProgramaID
                 WHERE ep.Estado IN ('ACTIVO', 'CONFIRMADO', 'ABANDONO') AND ep.FechaInscripcion >= :desde" . $filtroGrado . "
                 GROUP BY Mes"
            );
            $cuotas = $ejecutar(
                "SELECT DATE_FORMAT(pm.fechapago, '%Y-%m') AS Mes, COALESCE(SUM(pm.montocuota), 0) AS Monto
                 FROM pagomodulo pm
                 INNER JOIN estudianteprograma ep ON ep.idInscripcion = pm.idinscripcion
                 INNER JOIN programa p ON p.ProgramaID = ep.ProgramaID
                 WHERE pm.Estado = 'PAGADO' AND pm.fechapago >= :desde" . $filtroGrado . "
                 GROUP BY Mes"
            );
            $clinicas = $ejecutar(
                "SELECT DATE_FORMAT(pcl.FechaPago, '%Y-%m') AS Mes, COALESCE(SUM(pcl.Monto), 0) AS Monto
                 FROM pagoclinica pcl
                 INNER JOIN ordenpago op ON op.IdOrdenPago = pcl.IdOrdenPago
                 INNER JOIN programa p ON p.ProgramaID = op.ProgramaID
                 WHERE pcl.Estado = 'PAGADO' AND pcl.FechaPago >= :desde" . $filtroGrado . "
                 GROUP BY Mes"
            );

            // Una fila por mes, también los meses sin movimiento
            $porMes = [];
            for ($i = 0; $i < $meses; $i++) {
                $mes = date('Y-m', strtotime('+' . $i . ' months', strtotime($desde)));
                $porMes[$mes] = ['mes' => $mes, 'matricula' => 0, 'cuotas' => 0, 'clinicas' => 0, 'inscritos' => 0];
            }
            foreach ($matriculas as $f) {
                if (isset($porMes[$f['Mes']])) {
                    $porMes[$f['Mes']]['matricula'] = (float)$f['Monto'];
                    $porMes[$f['Mes']]['inscritos'] = (int)$f['Inscritos'];
                }
            }
            foreach ($cuotas as $f) {
                if (isset($porMes[$f['Mes']])) $porMes[$f['Mes']]['cuotas'] = (float)$f['Monto'];
            }
            foreach ($clinicas as $f) {
                if (isset($porMes[$f['Mes']])) $porMes[$f['Mes']]['clinicas'] = (float)$f['Monto'];
            }

            // Órdenes pendientes. Las de cuota y clínica tienen fecha límite; las de
            // matrícula son pre-registros por validar (su monto final ya trae el descuento).
            $ordenes = $ejecutar(
                "SELECT
                    CASE
                        WHEN op.Observaciones NOT IN ('CUOTA PROGRAMA', 'CLINICA') THEN 'matricula'
                        WHEN op.FechaVencimiento IS NOT NULL AND op.FechaVencimiento < CURDATE() THEN 'vencidas'
                        WHEN op.FechaVencimiento IS NOT NULL AND op.FechaVencimiento <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'proximas'
                        ELSE 'aldia'
                    END AS Grupo,
                    COUNT(*) AS Cantidad,
                    COALESCE(SUM(CASE WHEN op.MontoFinal > 0 THEN op.MontoFinal ELSE op.MontoTotal END), 0) AS Monto
                 FROM ordenpago op
                 INNER JOIN programa p ON p.ProgramaID = op.ProgramaID
                 WHERE op.Estado = 'PENDIENTE'" . $filtroGrado . "
                 GROUP BY Grupo",
                false
            );
            $resumenOrdenes = [];
            foreach (['vencidas', 'proximas', 'aldia', 'matricula'] as $g) {
                $resumenOrdenes[$g] = ['cantidad' => 0, 'monto' => 0];
            }
            foreach ($ordenes as $f) {
                $resumenOrdenes[$f['Grupo']] = ['cantidad' => (int)$f['Cantidad'], 'monto' => (float)$f['Monto']];
            }

            return ['meses' => array_values($porMes), 'ordenes' => $resumenOrdenes];
        } catch (PDOException $e) {
            error_log("Error en ObtenerDatosGraficosModelo: " . $e->getMessage());
            return ['meses' => [], 'ordenes' => []];
        }
    }
}
?>
