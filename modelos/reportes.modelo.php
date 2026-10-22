<?php
require_once file_exists(__DIR__ . '/conexion.modelo.php') ? __DIR__ . '/conexion.modelo.php' : __DIR__ . '/../core/conexion.modelo.php';
require_once __DIR__ . '/configuracion.modelo.php'; // nota mínima de aprobación
require_once __DIR__ . '/reportemodulos.modelo.php'; // saldos por estudiante (mismo cálculo que el Reporte General)
require_once __DIR__ . '/historialpagos.modelo.php'; // estado de cuenta (mismo armado que Historial de Pagos)

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
    // Cuenta Principal activa de un tipo (MATRICULA, CUOTAS o CLINICA) para el programa de la columna dada
    private static function SqlCuentaPrincipal($tipo, $columnaPrograma)
    {
        return "(SELECT pc.ProgramaCuentaID FROM programacuenta pc
                 WHERE pc.ProgramaID = $columnaPrograma AND pc.TipoCuenta = '$tipo'
                 AND pc.Principal = 1 AND pc.Estado = 1 LIMIT 1)";
    }

    // Orden confirmada con la que se pagó la matrícula de la inscripción "ep"
    const SQL_ORDEN_MATRICULA = "(
                    SELECT o.IdOrdenPago FROM ordenpago o
                    WHERE o.idinscripcion = ep.idInscripcion AND o.Estado = 'CONFIRMADO'
                    AND o.ListaModulos IS NULL AND o.ListaPagosModulo IS NULL
                    AND COALESCE(o.Observaciones, '') NOT IN ('CUOTA PROGRAMA', 'CLINICA')
                    ORDER BY o.IdOrdenPago LIMIT 1
                )";

    public static function RecaudacionPorCuenta($sede = null, $programaID = null, $fechaDesde = null, $fechaHasta = null)
    {
        $principal = [self::class, 'SqlCuentaPrincipal'];

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
                LEFT JOIN ordenpago op ON op.IdOrdenPago = " . self::SQL_ORDEN_MATRICULA . "
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
    /**
     * Cuentas bancarias distintas (banco + número) de los programas activos, para el filtro
     * "Cuenta" de Movimientos e Ingresos por mes. Varias filas de programacuenta pueden ser
     * la misma cuenta del banco usada por varios programas.
     */
    public static function ListaCuentas()
    {
        $stmt = Conexion::Conectar()->prepare(
            "SELECT pc.NumeroCuenta, MAX(pc.Banco) AS Banco,
                    GROUP_CONCAT(DISTINCT pc.TipoCuenta ORDER BY pc.TipoCuenta SEPARATOR ', ') AS Tipos
             FROM programacuenta pc
             INNER JOIN programa p ON p.ProgramaID = pc.ProgramaID
             WHERE pc.Estado = 1 AND p.Estado = 1
             GROUP BY pc.NumeroCuenta
             ORDER BY Banco, pc.NumeroCuenta"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cada pago recibido (matrícula, cuota o clínica), con el estudiante, el voucher y la
     * cuenta a la que fue (misma regla que Recaudación por cuenta). Para Movimientos de
     * cuenta e Ingresos por mes. Los filtros vacíos no filtran.
     * @param array $f sede, programaID, numeroCuenta, concepto, desde, hasta (AAAA-MM-DD)
     * @return array ordenado por fecha de pago
     */
    public static function MovimientosPagados(array $f)
    {
        $condiciones = [];
        $params = [];
        $filtros = [
            'sede' => 'p.Sede = :sede',
            'programaID' => 'p.ProgramaID = :programaID',
            'numeroCuenta' => 'pc.NumeroCuenta = :numeroCuenta',
            'concepto' => 'mov.Concepto = :concepto',
            'desde' => 'mov.Fecha >= :desde',
            'hasta' => 'mov.Fecha <= :hasta',
        ];
        foreach ($filtros as $clave => $condicion) {
            if (!empty($f[$clave])) {
                $condiciones[] = $condicion;
                $params[':' . $clave] = $f[$clave];
            }
        }

        $sql = "
            SELECT mov.Fecha, mov.Concepto, mov.Monto, mov.Voucher, mov.NumeroCuota,
                   p.ProgramaID, p.NombrePrograma, p.Sede, pc.Banco, pc.NumeroCuenta, pc.TipoCuenta,
                   e.Ci, e.Complemento, TRIM(CONCAT_WS(' ', e.Apaterno, e.Amaterno, e.Nombre)) AS Estudiante
            FROM (
                SELECT COALESCE(op.ProgramaCuentaID, " . self::SqlCuentaPrincipal('MATRICULA', 'ep.ProgramaID') . ") AS CuentaID,
                       ep.ProgramaID, ep.EstudianteID, 'MATRICULA' AS Concepto, ep.montoPagado AS Monto,
                       ep.FechaInscripcion AS Fecha, ep.nvauchermatricula AS Voucher, NULL AS NumeroCuota
                FROM estudianteprograma ep
                LEFT JOIN ordenpago op ON op.IdOrdenPago = " . self::SQL_ORDEN_MATRICULA . "
                WHERE ep.montoPagado > 0

                UNION ALL

                SELECT COALESCE(op.ProgramaCuentaID, " . self::SqlCuentaPrincipal('CUOTAS', 'ep.ProgramaID') . "),
                       ep.ProgramaID, ep.EstudianteID, 'CUOTAS', pm.montocuota, pm.fechapago, pm.nvaucher, pm.NumeroCuota
                FROM pagomodulo pm
                INNER JOIN estudianteprograma ep ON ep.idInscripcion = pm.idinscripcion
                LEFT JOIN ordenpago op ON op.IdOrdenPago = pm.IdOrdenPago
                WHERE pm.Estado = 'PAGADO'

                UNION ALL

                SELECT COALESCE(op.ProgramaCuentaID, " . self::SqlCuentaPrincipal('CLINICA', 'op.ProgramaID') . "),
                       op.ProgramaID, ep.EstudianteID, 'CLINICA', pcl.Monto, pcl.FechaPago, pcl.NumeroVoucher, NULL
                FROM pagoclinica pcl
                INNER JOIN ordenpago op ON op.IdOrdenPago = pcl.IdOrdenPago
                INNER JOIN estudianteprograma ep ON ep.idInscripcion = pcl.idinscripcion
                WHERE pcl.Estado = 'PAGADO'
            ) mov
            INNER JOIN programa p ON p.ProgramaID = mov.ProgramaID
            INNER JOIN estudiante e ON e.EstudianteID = mov.EstudianteID
            LEFT JOIN programacuenta pc ON pc.ProgramaCuentaID = mov.CuentaID
            " . ($condiciones ? "WHERE " . implode(' AND ', $condiciones) : "") . "
            ORDER BY mov.Fecha, p.NombrePrograma, Estudiante
        ";
        $stmt = Conexion::Conectar()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Saldos por estudiante de cada programa (costo, pagado, saldo, cuotas pagadas): el
     * mismo cálculo que el Reporte General de Programas, programa por programa.
     * @return array [ ['programa' => fila de programa, 'estudiantes' => [...]], ... ]
     */
    public static function SaldosPorPrograma($sede = null, $programaID = null, $soloConSaldo = false)
    {
        $resultado = [];
        foreach (self::ListaProgramas($sede) as $programa) {
            if ($programaID && (int)$programa['ProgramaID'] !== (int)$programaID) {
                continue;
            }
            $estudiantes = ReporteModulosModelo::ObtenerReportePagosPorEstudianteModelo($programa['ProgramaID']);
            if ($soloConSaldo) {
                $estudiantes = array_values(array_filter($estudiantes, function ($e) {
                    return $e['EstadoInscripcion'] !== 'ABANDONO' && (float)$e['SaldoPendiente'] > 0;
                }));
            }
            if ($estudiantes) {
                $resultado[] = ['programa' => $programa, 'estudiantes' => $estudiantes];
            }
        }
        return $resultado;
    }

    /**
     * Consolidado de notas de un programa: los módulos (columnas) y, por estudiante
     * activo, la nota de cada módulo. Sin nota = módulo aún no calificado.
     * @return array ['programa' => ?array, 'modulos' => [...], 'estudiantes' => [...con 'Notas' => [Idmodulo => nota]]]
     */
    public static function ConsolidadoNotas($programaID)
    {
        $pdo = Conexion::Conectar();
        $stmt = $pdo->prepare("SELECT * FROM programa WHERE ProgramaID = :id");
        $stmt->execute([':id' => $programaID]);
        $programa = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        $stmt = $pdo->prepare(
            "SELECT Idmodulo, codigomodulo AS CodigoModulo, nombremodulo AS NombreModulo
             FROM modulos WHERE ProgramaId = :id AND estadomodulo != 'INACTIVO'
             ORDER BY Idmodulo"
        );
        $stmt->execute([':id' => $programaID]);
        $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare(
            "SELECT e.EstudianteID, e.Ci, e.Complemento, TRIM(CONCAT_WS(' ', e.Apaterno, e.Amaterno, e.Nombre)) AS Estudiante
             FROM estudianteprograma ep
             INNER JOIN estudiante e ON e.EstudianteID = ep.EstudianteID
             WHERE ep.ProgramaID = :id AND ep.Estado = 'ACTIVO'
             ORDER BY e.Apaterno, e.Amaterno, e.Nombre"
        );
        $stmt->execute([':id' => $programaID]);
        $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT EstudianteID, Idmodulo, Nota FROM calificacion WHERE ProgramaId = :id AND Nota IS NOT NULL");
        $stmt->execute([':id' => $programaID]);
        $notas = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $n) {
            $notas[$n['EstudianteID']][$n['Idmodulo']] = (float)$n['Nota'];
        }
        foreach ($estudiantes as &$e) {
            $e['Notas'] = $notas[$e['EstudianteID']] ?? [];
        }
        unset($e);

        return ['programa' => $programa, 'modulos' => $modulos, 'estudiantes' => $estudiantes];
    }

    // Estado de cuenta de un estudiante por C.I.: el mismo armado que Historial de Pagos
    public static function EstadoCuentaEstudiante($ci)
    {
        return HistorialPagosModelos::BuscarHistorialPorCiModelo($ci);
    }
}
?>
