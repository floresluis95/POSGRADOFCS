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

            return [
                'totalProgramas' => $totalProgramas,
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
                    COUNT(DISTINCT m.Idmodulo) as TotalModulos,
                    COUNT(DISTINCT ep.EstudianteID) as TotalInscritos
                FROM programa p
                LEFT JOIN modulos m ON p.ProgramaID = m.ProgramaId AND m.estadomodulo = 'ACTIVO'
                LEFT JOIN estudianteprograma ep ON p.ProgramaID = ep.ProgramaID
                WHERE p.Estado = 1
                GROUP BY p.ProgramaID, p.NombrePrograma, p.Codigo, p.GradoAcademico
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
}
?>
