<?php
require_once __DIR__ . '/../core/conexion.modelo.php';
require_once __DIR__ . '/voucher.modelo.php';
require_once __DIR__ . '/requisito.modelo.php';
require_once __DIR__ . '/configuracion.modelo.php'; // nota mínima de aprobación // RequisitoModelo::CondicionGrado() (conteo de requisitos por grado)

/**
 * Modelo de Inscripción a Módulos
 * Gestiona el registro de estudiantes en módulos específicos
 */
class InscripcionModuloModelos
{
    /**
     * Listar estudiantes matriculados con sus programas
     * @param int|null $programaID (opcional) - Si se proporciona, filtra por ese programa
     * @param string|null $sede (opcional)
     * @param string $estado 'ACTIVO' (por defecto), 'ABANDONO', o 'TODOS' para no filtrar por estado
     * @return array
     */
    public static function ListarEstudiantesMatriculadosModelo($programaID = null, $sede = null, $estado = 'ACTIVO', $requisitos = null, $grado = null)
    {
        try {
            $sql = "SELECT
                    ep.idInscripcion,
                    ep.EstudianteID,
                    ep.ProgramaID,
                    ep.FechaInscripcion,
                    ep.costomatricula,
                    ep.montoPagado,
                    ep.pagoCompleto,
                    ep.porcentajeDescuento,
                    ep.montoDescuento,
                    ep.nvauchermatricula,
                    " . VoucherModelo::SqlFechaOrdenMatricula('ep.idInscripcion') . " AS FechaOrdenMatricula,
                    ep.Estado,
                    e.Nombre,
                    e.Apaterno,
                    e.Amaterno,
                    e.Ci,
                    e.Complemento,
                    e.Exp,
                    e.Correo,
                    e.Celular,
                    prof.NombreProfesion,
                    p.NombrePrograma,
                    p.GradoAcademico,
                    p.Codigo as CodigoPrograma,
                    p.Version,
                    p.NumeroTramite,
                    p.Costo as CostoPrograma,
                    p.Sede,
                    -- Para saber si el programa ya concluyó (aviso de titulación)
                    p.FechaInicio,
                    p.DuracionMeses,
                    p.Estado AS EstadoPrograma,
                    -- Plan Grupal: grupo armado en Pre-Registro (NULL si no es grupal)
                    (SELECT pp.CodigoGrupo FROM pagoprograma pp
                     WHERE pp.idInscripcion = ep.idInscripcion AND pp.Estado != 'ANULADO'
                     ORDER BY pp.IdPagoPrograma DESC LIMIT 1) AS CodigoGrupo,
                    -- Solo cuentan los requisitos obligatorios del grado académico del programa
                    (SELECT COUNT(*) FROM requisito r WHERE r.Estado = 1 AND r.Obligatorio = 1
                     AND " . RequisitoModelo::CondicionGrado() . ") AS RequisitosTotal,
                    (SELECT COUNT(*) FROM requisitoinscripcion ri
                     INNER JOIN requisito r ON r.IdRequisito = ri.IdRequisito
                     WHERE ri.idInscripcion = ep.idInscripcion AND ri.Entregado = 1
                     AND r.Estado = 1 AND r.Obligatorio = 1
                     AND " . RequisitoModelo::CondicionGrado() . ") AS RequisitosEntregados
                FROM estudianteprograma ep
                INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
                INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
                LEFT JOIN profesion prof ON e.IdProfesion = prof.IdProfesion
                WHERE 1 = 1";

            if ($estado !== null && strtoupper($estado) !== 'TODOS') {
                $sql .= " AND ep.Estado = :estado";
            }

            // Si se proporciona programaID, agregar filtro
            if ($programaID !== null && $programaID != '') {
                $sql .= " AND ep.ProgramaID = :programaID";
            }

            // Si se proporciona sede, agregar filtro
            if ($sede !== null && $sede !== '') {
                $sql .= " AND p.Sede = :sede";
            }

            // Grado académico del programa (Especialidad, Maestría, Diplomado...)
            if ($grado !== null && $grado !== '') {
                $sql .= " AND p.GradoAcademico = :grado";
            }

            // Requisitos de matriculación entregados (ver requisito.modelo.php)
            if ($requisitos === 'INCOMPLETOS') {
                $sql .= " HAVING RequisitosEntregados < RequisitosTotal";
            } elseif ($requisitos === 'COMPLETOS') {
                $sql .= " HAVING RequisitosEntregados >= RequisitosTotal";
            }

            $sql .= " ORDER BY ep.FechaInscripcion DESC, ep.idInscripcion DESC";

            $stmt = Conexion::Conectar()->prepare($sql);

            if ($estado !== null && strtoupper($estado) !== 'TODOS') {
                $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            }

            if ($programaID !== null && $programaID != '') {
                $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            }

            if ($sede !== null && $sede !== '') {
                $stmt->bindParam(":sede", $sede, PDO::PARAM_STR);
            }

            if ($grado !== null && $grado !== '') {
                $stmt->bindParam(":grado", $grado, PDO::PARAM_STR);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarEstudiantesMatriculadosModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener módulos de un programa específico
     * @param int $programaID
     * @return array
     */
    public static function ObtenerModulosPorProgramaModelo($programaID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    ModuloID,
                    NombreModulo,
                    Codigo,
                    Creditos,
                    HorasTeoricas,
                    HorasPracticas,
                    Costo
                FROM modulo
                WHERE ProgramaID = :programaID
                AND Estado = 1
                ORDER BY Codigo"
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
     * Registrar inscripción de estudiante a módulo
     * @param array $datos
     * @return string
     */
    public static function RegistrarInscripcionModuloModelo($datos)
    {
        try {
            $pdo = Conexion::Conectar();
            $pdo->beginTransaction();

            // Verificar si ya está inscrito en ese módulo
            $stmtCheck = $pdo->prepare(
                "SELECT idEstudianteModulo FROM estudiantemodulo
                 WHERE EstudianteID = :estudianteID
                 AND ModuloID = :moduloID
                 AND Estado = 'ACTIVO'"
            );
            $stmtCheck->bindParam(":estudianteID", $datos['EstudianteID'], PDO::PARAM_INT);
            $stmtCheck->bindParam(":moduloID", $datos['ModuloID'], PDO::PARAM_INT);
            $stmtCheck->execute();

            if ($stmtCheck->fetch()) {
                $pdo->rollBack();
                return "duplicado";
            }

            // Insertar inscripción
            $stmt = $pdo->prepare(
                "INSERT INTO estudiantemodulo
                (EstudianteID, ModuloID, costomodulo, nvauchermodulo, FechaInscripcion, Estado)
                VALUES (:estudianteID, :moduloID, :costo, :nvaucher, :fecha, 'ACTIVO')"
            );

            $stmt->bindParam(":estudianteID", $datos['EstudianteID'], PDO::PARAM_INT);
            $stmt->bindParam(":moduloID", $datos['ModuloID'], PDO::PARAM_INT);
            $stmt->bindParam(":costo", $datos['costo'], PDO::PARAM_STR);
            $stmt->bindParam(":nvaucher", $datos['nvaucher'], PDO::PARAM_STR);
            $stmt->bindParam(":fecha", $datos['fecha'], PDO::PARAM_STR);

            if ($stmt->execute()) {
                $pdo->commit();
                return "exitoso";
            } else {
                $pdo->rollBack();
                error_log("Error al ejecutar INSERT: " . print_r($stmt->errorInfo(), true));
                return "error";
            }

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en RegistrarInscripcionModuloModelo: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Listar módulos inscritos de un estudiante con información del docente y calificación
     * @param int $estudianteID - ID del estudiante
     * @param int|null $programaID - ID del programa (opcional, para filtrar por programa específico)
     * @param int|null $idInscripcion - ID de la inscripción (opcional, para filtrar por inscripción específica)
     * @return array
     */
    public static function ObtenerModulosInscritosEstudianteModelo($estudianteID, $programaID = null, $idInscripcion = null)
    {
        try {
            // Si no se proporciona programaID ni idInscripcion, obtener el programa del estudiante (comportamiento antiguo)
            if ($programaID === null && $idInscripcion === null) {
                $stmtPrograma = Conexion::Conectar()->prepare(
                    "SELECT ProgramaID FROM estudianteprograma
                     WHERE EstudianteID = :estudianteID
                     AND Estado = 'ACTIVO'
                     LIMIT 1"
                );
                $stmtPrograma->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
                $stmtPrograma->execute();
                $programa = $stmtPrograma->fetch(PDO::FETCH_ASSOC);

                if (!$programa) {
                    return [];
                }

                $programaID = $programa['ProgramaID'];
            }

            // Los módulos son puramente académicos (ya no están ligados al pago,
            // que ahora es por cuotas libres del programa): se listan todos los
            // módulos activos del programa, con su calificación si existe.
            $sql = "SELECT
                    m.Idmodulo,
                    m.nombremodulo as NombreModulo,
                    m.codigomodulo as Codigo,
                    m.FechaInicio,
                    m.FechaFinal,
                    CONCAT(d.Nombre, ' ', d.Apaterno, ' ', d.Amaterno) as NombreDocente,
                    d.Especialidad as EspecialidadDocente,
                    c.Nota,
                    CASE
                        WHEN c.Nota IS NULL THEN 'SIN CALIFICACIÓN'
                        WHEN c.Nota >= " . ConfiguracionModelo::NotaAprobacion() . " THEN 'APROBADO'
                        ELSE 'REPROBADO'
                    END as EstadoAprobacion,
                    -- Orden de cuota que cubre el módulo (solo control; ver ordenpagomodulo)
                    (SELECT op.NumeroOrden FROM ordenpagomodulo opm
                     INNER JOIN ordenpago op ON op.IdOrdenPago = opm.IdOrdenPago
                     WHERE opm.IdModulo = m.Idmodulo AND op.idinscripcion = :idInscripcionCuota AND op.Estado <> 'ANULADO'
                     ORDER BY op.IdOrdenPago DESC LIMIT 1) AS CuotaOrden,
                    (SELECT op.Estado FROM ordenpagomodulo opm
                     INNER JOIN ordenpago op ON op.IdOrdenPago = opm.IdOrdenPago
                     WHERE opm.IdModulo = m.Idmodulo AND op.idinscripcion = :idInscripcionCuota2 AND op.Estado <> 'ANULADO'
                     ORDER BY op.IdOrdenPago DESC LIMIT 1) AS CuotaEstado
                FROM modulos m
                LEFT JOIN docente d ON m.DocenteID = d.DocenteID
                LEFT JOIN calificacion c ON c.EstudianteID = :estudianteID
                    AND c.Idmodulo = m.Idmodulo
                    AND c.ProgramaId = :programaID
                WHERE m.ProgramaId = :programaIDFiltro
                AND m.estadomodulo = 'ACTIVO'
                ORDER BY m.Idmodulo ASC"; // por número de módulo (como texto, IX quedaba antes que V)

            $stmt = Conexion::Conectar()->prepare($sql);
            $stmt->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
            $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmt->bindParam(":programaIDFiltro", $programaID, PDO::PARAM_INT);
            $stmt->bindValue(":idInscripcionCuota", $idInscripcion, $idInscripcion ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(":idInscripcionCuota2", $idInscripcion, $idInscripcion ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerModulosInscritosEstudianteModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener datos completos del estudiante matriculado para PDF
     * @param int $idInscripcion - ID de la inscripción específica
     * @return array|null
     */
    public static function ObtenerDatosCompletosEstudianteModelo($idInscripcion)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    ep.idInscripcion,
                    ep.EstudianteID,
                    ep.ProgramaID,
                    ep.FechaInscripcion,
                    ep.costomatricula,
                    ep.montoPagado,
                    ep.nvauchermatricula,
                    " . VoucherModelo::SqlFechaOrdenMatricula('ep.idInscripcion') . " AS FechaOrdenMatricula,
                    ep.Estado,
                    e.Nombre,
                    e.Apaterno,
                    e.Amaterno,
                    e.Ci,
                    e.Complemento,
                    e.Exp,
                    e.Correo,
                    e.Celular,
                    e.Telefono,
                    e.Direccion,
                    e.FechaNacimiento,
                    e.Edad,
                    e.Trabajo,
                    e.FechaEgreso,
                    prof.NombreProfesion,
                    univ.NombreUniversidad,
                    univ.TipoUniversidad,
                    p.NombrePrograma,
                    p.GradoAcademico,
                    p.Codigo as CodigoPrograma,
                    p.DuracionMeses,
                    p.Modulos,
                    p.Costo,
                    p.CostoMatricula,
                    p.Version,
                    p.NumeroTramite,
                    ep.pagoCompleto,
                    pp.TipoPlan,
                    pp.NumeroCuotas,
                    pp.MontoCuota AS PlanMontoCuota,
                    pp.PorcentajeDescuento AS PlanPorcentajeDescuento,
                    pp.MontoTotalPagar AS PlanMontoTotal
                FROM estudianteprograma ep
                INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
                INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
                LEFT JOIN profesion prof ON e.IdProfesion = prof.IdProfesion
                LEFT JOIN universidad univ ON e.IdUniversidad = univ.IdUniversidad
                LEFT JOIN pagoprograma pp ON pp.idInscripcion = ep.idInscripcion AND pp.Estado != 'ANULADO'
                WHERE ep.idInscripcion = :idInscripcion
                AND ep.Estado = 'ACTIVO'"
            );
            $stmt->bindParam(":idInscripcion", $idInscripcion, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerDatosCompletosEstudianteModelo: " . $e->getMessage());
            return null;
        }
    }

}
?>
