<?php
require_once file_exists(__DIR__ . '/conexion.modelo.php') ? __DIR__ . '/conexion.modelo.php' : __DIR__ . '/../core/conexion.modelo.php';
require_once __DIR__ . '/configuracion.modelo.php'; // nota mínima de aprobación

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

    // Estudiantes por programa y/o sede específicos
    public static function EstudiantesPorPrograma($programaID = null, $sede = null)
    {
        $condiciones = ["ep.Estado = 'ACTIVO'"];
        $params = [];

        if ($programaID) {
            $condiciones[] = "pr.ProgramaID = :programaID";
            $params[':programaID'] = $programaID;
        }
        if ($sede) {
            $condiciones[] = "pr.Sede = :sede";
            $params[':sede'] = $sede;
        }

        $sql = "
            SELECT e.*, p.NombreProfesion, pr.NombrePrograma, pr.Sede, pr.GradoAcademico, ep.FechaInscripcion
            FROM estudianteprograma ep
            INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
            INNER JOIN programa pr ON ep.ProgramaID = pr.ProgramaID
            INNER JOIN profesion p ON e.IdProfesion = p.IdProfesion
            WHERE " . implode(' AND ', $condiciones) . "
            ORDER BY pr.Sede, pr.NombrePrograma, e.Apaterno, e.Amaterno
        ";
        $stmt = Conexion::Conectar()->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Módulos con matriculados, calificados y estado de aprobación.
     * (Corregido: la version anterior apuntaba a una tabla `modulo`/`estudiantemodulo`
     * que no existe en la base real; usa `modulos` + `calificacion`.)
     */
    public static function ModulosConMatriculados($programaID = null, $sede = null)
    {
        $condiciones = ["m.estadomodulo != 'INACTIVO'"];
        $params = [];

        if ($programaID) {
            $condiciones[] = "m.ProgramaId = :programaID";
            $params[':programaID'] = $programaID;
        }
        if ($sede) {
            $condiciones[] = "p.Sede = :sede";
            $params[':sede'] = $sede;
        }

        $sql = "
            SELECT
                m.Idmodulo,
                m.codigomodulo as CodigoModulo,
                m.nombremodulo as NombreModulo,
                m.estadomodulo as EstadoModulo,
                p.NombrePrograma,
                p.Sede,
                CONCAT(d.Nombre, ' ', d.Apaterno) as Docente,
                (SELECT COUNT(*) FROM calificacion c WHERE c.Idmodulo = m.Idmodulo AND c.Nota IS NOT NULL) as TotalCalificados,
                (SELECT COUNT(*) FROM calificacion c WHERE c.Idmodulo = m.Idmodulo AND c.Nota >= " . ConfiguracionModelo::NotaAprobacion() . ") as TotalAprobados,
                (SELECT COUNT(*) FROM calificacion c WHERE c.Idmodulo = m.Idmodulo AND c.Nota IS NOT NULL AND c.Nota < " . ConfiguracionModelo::NotaAprobacion() . ") as TotalReprobados
            FROM modulos m
            INNER JOIN programa p ON m.ProgramaId = p.ProgramaID
            LEFT JOIN docente d ON m.DocenteID = d.DocenteID
            WHERE " . implode(' AND ', $condiciones) . "
            ORDER BY p.Sede, p.NombrePrograma, m.Idmodulo
        ";
        $stmt = Conexion::Conectar()->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Detalle de estudiantes inscritos en un módulo específico (vía calificación registrada o no)
    public static function EstudiantesEnModulo($idmodulo)
    {
        $stmt = Conexion::Conectar()->prepare("
            SELECT
                e.Ci, e.Complemento, e.Exp,
                e.Nombre, e.Apaterno, e.Amaterno,
                e.Celular, e.Correo,
                c.Nota,
                c.estado as EstadoCalificacion,
                c.FechaRegistro
            FROM calificacion c
            INNER JOIN estudiante e ON c.EstudianteID = e.EstudianteID
            WHERE c.Idmodulo = :idmodulo AND e.Estado = 1
            ORDER BY e.Apaterno, e.Amaterno, e.Nombre
        ");
        $stmt->bindParam(":idmodulo", $idmodulo, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lista de programas (opcionalmente filtrada por sede)
    public static function ListaProgramas($sede = null)
    {
        if ($sede) {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM programa WHERE Estado = 1 AND Sede = :sede ORDER BY NombrePrograma");
            $stmt->bindParam(':sede', $sede, PDO::PARAM_STR);
        } else {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM programa WHERE Estado = 1 ORDER BY NombrePrograma");
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lista de sedes distintas entre los programas activos
    public static function ListaSedes()
    {
        $stmt = Conexion::Conectar()->prepare("SELECT DISTINCT Sede FROM programa WHERE Estado = 1 AND Sede IS NOT NULL AND Sede != '' ORDER BY Sede");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Reporte ejecutivo: KPIs por programa (matrícula, cobranza, aprobación,
     * módulos, docentes) para apoyar decisiones administrativas. Se puede
     * acotar por Sede y/o Programa.
     */
    public static function ReporteEjecutivoPorPrograma($sede = null, $programaID = null)
    {
        $condiciones = ["p.Estado = 1"];
        $params = [];

        if ($sede) {
            $condiciones[] = "p.Sede = :sede";
            $params[':sede'] = $sede;
        }
        if ($programaID) {
            $condiciones[] = "p.ProgramaID = :programaID";
            $params[':programaID'] = $programaID;
        }

        $sql = "
            SELECT
                p.ProgramaID,
                p.NombrePrograma,
                p.GradoAcademico,
                p.Sede,
                p.Codigo,
                p.CostoMatricula,

                -- Matrícula
                (SELECT COUNT(*) FROM estudianteprograma ep WHERE ep.ProgramaID = p.ProgramaID AND ep.Estado = 'ACTIVO') as TotalMatriculados,

                -- Dinero comprometido por matrícula de inscripciones activas
                -- (costomatricula ya es el monto con descuento: no se resta otra vez)
                (SELECT COALESCE(SUM(ep.costomatricula), 0)
                    FROM estudianteprograma ep
                    WHERE ep.ProgramaID = p.ProgramaID AND ep.Estado = 'ACTIVO') as MontoComprometido,

                -- Dinero efectivamente recaudado (cuotas pagadas, vía pagomodulo)
                (SELECT COALESCE(SUM(pm.montocuota), 0)
                    FROM pagomodulo pm
                    INNER JOIN estudianteprograma ep ON pm.idinscripcion = ep.idInscripcion
                    WHERE ep.ProgramaID = p.ProgramaID AND pm.Estado = 'PAGADO') as MontoRecaudado,

                -- Módulos
                (SELECT COUNT(*) FROM modulos m WHERE m.ProgramaId = p.ProgramaID) as TotalModulos,
                (SELECT COUNT(*) FROM modulos m WHERE m.ProgramaId = p.ProgramaID AND m.estadomodulo = 'ACTIVO') as ModulosActivos,
                (SELECT COUNT(*) FROM modulos m WHERE m.ProgramaId = p.ProgramaID AND m.estadomodulo IN ('VALIDADO', 'CERRADO')) as ModulosValidados,
                (SELECT COUNT(DISTINCT m.DocenteID) FROM modulos m WHERE m.ProgramaId = p.ProgramaID AND m.DocenteID IS NOT NULL) as TotalDocentes,

                -- Calificaciones
                (SELECT COUNT(*) FROM calificacion c WHERE c.ProgramaId = p.ProgramaID AND c.Nota IS NOT NULL) as TotalCalificados,
                (SELECT COUNT(*) FROM calificacion c WHERE c.ProgramaId = p.ProgramaID AND c.Nota >= " . ConfiguracionModelo::NotaAprobacion() . ") as TotalAprobados,
                (SELECT COUNT(*) FROM calificacion c WHERE c.ProgramaId = p.ProgramaID AND c.Nota IS NOT NULL AND c.Nota < " . ConfiguracionModelo::NotaAprobacion() . ") as TotalReprobados

            FROM programa p
            WHERE " . implode(' AND ', $condiciones) . "
            ORDER BY p.Sede, p.NombrePrograma
        ";

        $stmt = Conexion::Conectar()->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recaudación por cuenta bancaria (programacuenta): cuánto dinero ingresó a
     * cada cuenta, cuántos pagos recibió y cuánto está por cobrar (órdenes
     * pendientes que se depositarán en ella).
     *
     * A qué cuenta va cada pago sigue la misma regla que al imprimir la orden:
     * la cuenta elegida en la orden o, si no se eligió, la Principal activa del
     * tipo correspondiente (MATRICULA, CUOTAS o CLINICA).
     *  - Matrícula pagada: estudianteprograma.montoPagado (0 si se matriculó sin pago).
     *  - Cuota pagada: pagomodulo con Estado PAGADO.
     *  - Clínica pagada: pagoclinica con Estado PAGADO.
     *  - Por cobrar: órdenes PENDIENTES de matrícula (MontoFinal), de cuota y de clínica (MontoTotal).
     * No se suman las órdenes de módulos ni los comprobantes de módulos ya pagados
     * (ListaModulos / ListaPagosModulo): esos pagos ya están en pagomodulo.
     *
     * El rango de fechas se aplica solo a lo recaudado (fecha de pago); lo "por
     * cobrar" siempre es lo pendiente a la fecha.
     *
     * @return array filas con CuentaID (NULL si el programa no tiene cuenta
     *               configurada para ese concepto), datos de la cuenta y totales.
     */
    public static function RecaudacionPorCuenta($sede = null, $programaID = null, $fechaDesde = null, $fechaHasta = null)
    {
        $principal = function ($tipo, $columnaPrograma) {
            return "(SELECT pc.ProgramaCuentaID FROM programacuenta pc
                     WHERE pc.ProgramaID = $columnaPrograma AND pc.TipoCuenta = '$tipo'
                     AND pc.Principal = 1 AND pc.Estado = 1 LIMIT 1)";
        };

        $condiciones = [];
        $params = [];

        if ($sede) {
            $condiciones[] = "p.Sede = :sede";
            $params[':sede'] = $sede;
        }
        if ($programaID) {
            $condiciones[] = "p.ProgramaID = :programaID";
            $params[':programaID'] = $programaID;
        }
        if ($fechaDesde) {
            $condiciones[] = "(mov.Pagado = 0 OR mov.Fecha >= :fechaDesde)";
            $params[':fechaDesde'] = $fechaDesde;
        }
        if ($fechaHasta) {
            $condiciones[] = "(mov.Pagado = 0 OR mov.Fecha <= :fechaHasta)";
            $params[':fechaHasta'] = $fechaHasta;
        }

        $sql = "
            SELECT
                mov.CuentaID,
                mov.Concepto,
                p.ProgramaID,
                p.NombrePrograma,
                p.Sede,
                pc.TipoCuenta,
                pc.NumeroCuenta,
                pc.Banco,
                pc.Principal,
                pc.Estado AS EstadoCuenta,
                COALESCE(SUM(CASE WHEN mov.Pagado = 1 THEN mov.Monto END), 0) AS Recaudado,
                COALESCE(SUM(mov.Pagado = 1), 0) AS NumPagos,
                COALESCE(SUM(CASE WHEN mov.Pagado = 0 THEN mov.Monto END), 0) AS PorCobrar,
                COALESCE(SUM(mov.Pagado = 0), 0) AS NumPendientes,
                MAX(CASE WHEN mov.Pagado = 1 THEN mov.Fecha END) AS UltimoPago
            FROM (
                -- Matrícula pagada
                SELECT COALESCE(op.ProgramaCuentaID, " . $principal('MATRICULA', 'ep.ProgramaID') . ") AS CuentaID,
                       ep.ProgramaID, 'MATRICULA' AS Concepto, ep.montoPagado AS Monto,
                       ep.FechaInscripcion AS Fecha, 1 AS Pagado
                FROM estudianteprograma ep
                LEFT JOIN ordenpago op ON op.IdOrdenPago = (
                    SELECT o.IdOrdenPago FROM ordenpago o
                    WHERE o.idinscripcion = ep.idInscripcion AND o.Estado = 'CONFIRMADO'
                    AND o.ListaModulos IS NULL AND o.ListaPagosModulo IS NULL
                    AND COALESCE(o.Observaciones, '') NOT IN ('CUOTA PROGRAMA', 'CLINICA')
                    ORDER BY o.IdOrdenPago LIMIT 1
                )
                WHERE ep.montoPagado > 0

                UNION ALL

                -- Cuotas pagadas
                SELECT COALESCE(op.ProgramaCuentaID, " . $principal('CUOTAS', 'ep.ProgramaID') . "),
                       ep.ProgramaID, 'CUOTAS', pm.montocuota, pm.fechapago, 1
                FROM pagomodulo pm
                INNER JOIN estudianteprograma ep ON ep.idInscripcion = pm.idinscripcion
                LEFT JOIN ordenpago op ON op.IdOrdenPago = pm.IdOrdenPago
                WHERE pm.Estado = 'PAGADO'

                UNION ALL

                -- Matrícula por cobrar (preregistros pendientes)
                SELECT COALESCE(op.ProgramaCuentaID, " . $principal('MATRICULA', 'op.ProgramaID') . "),
                       op.ProgramaID, 'MATRICULA', op.MontoFinal, DATE(op.FechaGeneracion), 0
                FROM ordenpago op
                WHERE op.Estado = 'PENDIENTE' AND op.ListaModulos IS NULL AND op.ListaPagosModulo IS NULL
                AND COALESCE(op.Observaciones, '') NOT IN ('CUOTA PROGRAMA', 'CLINICA')

                UNION ALL

                -- Cuotas por cobrar
                SELECT COALESCE(op.ProgramaCuentaID, " . $principal('CUOTAS', 'op.ProgramaID') . "),
                       op.ProgramaID, 'CUOTAS', op.MontoTotal, DATE(op.FechaGeneracion), 0
                FROM ordenpago op
                WHERE op.Estado = 'PENDIENTE' AND op.Observaciones = 'CUOTA PROGRAMA'

                UNION ALL

                -- Clínicas pagadas (voucher registrado en pagoclinica)
                SELECT COALESCE(op.ProgramaCuentaID, " . $principal('CLINICA', 'op.ProgramaID') . "),
                       op.ProgramaID, 'CLINICA', pcl.Monto, pcl.FechaPago, 1
                FROM pagoclinica pcl
                INNER JOIN ordenpago op ON op.IdOrdenPago = pcl.IdOrdenPago
                WHERE pcl.Estado = 'PAGADO'

                UNION ALL

                -- Clínicas por cobrar
                SELECT COALESCE(op.ProgramaCuentaID, " . $principal('CLINICA', 'op.ProgramaID') . "),
                       op.ProgramaID, 'CLINICA', op.MontoTotal, DATE(op.FechaGeneracion), 0
                FROM ordenpago op
                WHERE op.Estado = 'PENDIENTE' AND op.Observaciones = 'CLINICA'
            ) mov
            INNER JOIN programa p ON p.ProgramaID = mov.ProgramaID
            LEFT JOIN programacuenta pc ON pc.ProgramaCuentaID = mov.CuentaID
            " . ($condiciones ? "WHERE " . implode(' AND ', $condiciones) : "") . "
            GROUP BY mov.CuentaID, mov.Concepto, p.ProgramaID, p.NombrePrograma, p.Sede,
                     pc.TipoCuenta, pc.NumeroCuenta, pc.Banco, pc.Principal, pc.Estado
        ";

        $stmt = Conexion::Conectar()->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Unir por cuenta (una cuenta podría recibir ambos conceptos) y agregar
        // las cuentas activas sin movimientos, para que también se vean las que
        // no se están usando.
        $cuentas = [];
        foreach ($movimientos as $m) {
            $clave = $m['CuentaID'] !== null ? 'C' . $m['CuentaID'] : 'SIN-' . $m['ProgramaID'] . '-' . $m['Concepto'];
            if (!isset($cuentas[$clave])) {
                $cuentas[$clave] = $m;
                continue;
            }
            foreach (['Recaudado', 'NumPagos', 'PorCobrar', 'NumPendientes'] as $campo) {
                $cuentas[$clave][$campo] += $m[$campo];
            }
            if ($m['UltimoPago'] > $cuentas[$clave]['UltimoPago']) {
                $cuentas[$clave]['UltimoPago'] = $m['UltimoPago'];
            }
        }

        $condicionesCuentas = ["pc.Estado = 1"];
        $paramsCuentas = [];
        if ($sede) {
            $condicionesCuentas[] = "p.Sede = :sede";
            $paramsCuentas[':sede'] = $sede;
        }
        if ($programaID) {
            $condicionesCuentas[] = "p.ProgramaID = :programaID";
            $paramsCuentas[':programaID'] = $programaID;
        }
        $stmtCuentas = Conexion::Conectar()->prepare(
            "SELECT pc.ProgramaCuentaID AS CuentaID, pc.TipoCuenta AS Concepto, p.ProgramaID, p.NombrePrograma, p.Sede,
                    pc.TipoCuenta, pc.NumeroCuenta, pc.Banco, pc.Principal, pc.Estado AS EstadoCuenta
             FROM programacuenta pc
             INNER JOIN programa p ON p.ProgramaID = pc.ProgramaID
             WHERE " . implode(' AND ', $condicionesCuentas)
        );
        foreach ($paramsCuentas as $key => $val) {
            $stmtCuentas->bindValue($key, $val);
        }
        $stmtCuentas->execute();
        foreach ($stmtCuentas->fetchAll(PDO::FETCH_ASSOC) as $c) {
            $clave = 'C' . $c['CuentaID'];
            if (!isset($cuentas[$clave])) {
                $cuentas[$clave] = $c + ['Recaudado' => 0, 'NumPagos' => 0, 'PorCobrar' => 0, 'NumPendientes' => 0, 'UltimoPago' => null];
            }
        }

        // Mayor recaudación primero; a igual monto, más pagos primero
        $cuentas = array_values($cuentas);
        usort($cuentas, function ($a, $b) {
            return [$b['Recaudado'], $b['NumPagos'], $b['PorCobrar']] <=> [$a['Recaudado'], $a['NumPagos'], $a['PorCobrar']];
        });

        return $cuentas;
    }
}
?>
