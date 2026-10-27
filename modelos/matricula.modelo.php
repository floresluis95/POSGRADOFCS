<?php
require_once __DIR__ . '/../core/conexion.modelo.php';

/**
 * Modelo de Matrícula/Inscripción
 * Gestiona el registro de inscripciones en la tabla 'inscripcion'
 */
class MatriculaModelos
{
    /**
     * Registrar nueva inscripción/matriculación
     * @param array $datos - Datos de la inscripción
     * @return string - 'exitoso' o 'error'
     */
    public static function RegistrarMatriculaModelo($datos)
    {
        try {
            $pdo = Conexion::Conectar();
            $pdo->beginTransaction();

            // Verificar si el estudiante ya está inscrito en el mismo programa
            $stmtCheck = $pdo->prepare(
                "SELECT idInscripcion FROM estudianteprograma
                 WHERE EstudianteID = :estudianteID
                 AND ProgramaID = :programaID
                 AND Estado = 'ACTIVO'"
            );
            $stmtCheck->bindParam(":estudianteID", $datos['EstudianteID'], PDO::PARAM_INT);
            $stmtCheck->bindParam(":programaID", $datos['ProgramaID'], PDO::PARAM_INT);
            $stmtCheck->execute();

            if ($stmtCheck->fetch()) {
                $pdo->rollBack();
                return "duplicado";
            }

            // Insertar la inscripción
            $stmt = $pdo->prepare(
                "INSERT INTO estudianteprograma
                (EstudianteID, ProgramaID, costomatricula, montoPagado, pagoCompleto, porcentajeDescuento, montoDescuento, nvauchermatricula, FechaInscripcion, foto, Estado)
                VALUES (:estudianteID, :programaID, :costomatricula, :montoPagado, :pagoCompleto, :porcentajeDescuento, :montoDescuento, :nvaucher, :fechaInscripcion, :foto, 'ACTIVO')"
            );

            $stmt->bindParam(":estudianteID", $datos['EstudianteID'], PDO::PARAM_INT);
            $stmt->bindParam(":programaID", $datos['ProgramaID'], PDO::PARAM_INT);
            $stmt->bindParam(":costomatricula", $datos['costomatricula']);
            $stmt->bindParam(":montoPagado", $datos['montoPagado']);
            $stmt->bindParam(":pagoCompleto", $datos['pagoCompleto'], PDO::PARAM_INT);
            $stmt->bindParam(":porcentajeDescuento", $datos['porcentajeDescuento']);
            $stmt->bindParam(":montoDescuento", $datos['montoDescuento']);
            $stmt->bindParam(":nvaucher", $datos['nvauchermatricula'], PDO::PARAM_STR);
            $stmt->bindParam(":fechaInscripcion", $datos['FechaInscripcion'], PDO::PARAM_STR);
            $stmt->bindParam(":foto", $datos['foto'], PDO::PARAM_LOB);

            if (!$stmt->execute()) {
                $pdo->rollBack();
                error_log("Error al ejecutar INSERT: " . print_r($stmt->errorInfo(), true));
                return "error";
            }

            // Obtener el ID de la inscripción recién creada
            $inscripcionID = $pdo->lastInsertId();

            // Si es pago completo, inscribir automáticamente en todos los módulos del programa
            // y registrar los pagos correspondientes
            if ($datos['pagoCompleto'] == 1) {
                // Obtener todos los módulos del programa (usando tabla 'modulos' con 's')
                $stmtModulos = $pdo->prepare(
                    "SELECT Idmodulo, nombremodulo
                     FROM modulos
                     WHERE ProgramaId = :programaID AND estadomodulo = 'ACTIVO'"
                );
                $stmtModulos->bindParam(":programaID", $datos['ProgramaID'], PDO::PARAM_INT);
                $stmtModulos->execute();
                $modulos = $stmtModulos->fetchAll(PDO::FETCH_ASSOC);

                if (count($modulos) > 0) {
                    $totalModulos = count($modulos);

                    // NOTA: este flujo de "pago completo" está desactualizado (las cuotas ya
                    // no están ligadas a módulos); se deja funcional a nivel de columnas para
                    // no romper si se reactiva, pero registra el pago como una sola cuota del
                    // programa en vez de una fila por módulo.
                    $stmtPagoModulo = $pdo->prepare(
                        "INSERT INTO pagomodulo
                        (idinscripcion, NumeroCuota, montocuota, fechapago, nvaucher, Estado)
                        VALUES (:idinscripcion, 1, :montocuota, :fechapago, :nvaucher, 'PAGADO')"
                    );

                    $stmtPagoModulo->bindParam(":idinscripcion", $inscripcionID, PDO::PARAM_INT);
                    $stmtPagoModulo->bindParam(":montocuota", $datos['montoPagado'], PDO::PARAM_STR);
                    $stmtPagoModulo->bindParam(":fechapago", $datos['FechaInscripcion'], PDO::PARAM_STR);
                    $stmtPagoModulo->bindParam(":nvaucher", $datos['nvauchermatricula'], PDO::PARAM_STR);

                    $modulosInscritos = 0;
                    if ($stmtPagoModulo->execute()) {
                        $modulosInscritos = $totalModulos;
                    } else {
                        error_log("Error al registrar pago completo del programa: " . print_r($stmtPagoModulo->errorInfo(), true));
                    }

                    error_log("PAGO COMPLETO: Estudiante {$datos['EstudianteID']} inscrito y pagado en {$modulosInscritos} de {$totalModulos} módulos del programa {$datos['ProgramaID']}");
                } else {
                    error_log("ADVERTENCIA: Programa {$datos['ProgramaID']} no tiene módulos activos para pago completo");
                }
            }

            $pdo->commit();
            return "exitoso";

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en RegistrarMatriculaModelo: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Verificar si un estudiante ya está inscrito en un programa
     * @param int $estudianteID
     * @param int $programaID
     * @return bool
     */
    public static function VerificarInscripcionExistente($estudianteID, $programaID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT idInscripcion FROM estudianteprograma
                 WHERE EstudianteID = :estudianteID
                 AND ProgramaID = :programaID
                 AND Estado = 'ACTIVO'"
            );
            $stmt->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
            $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            error_log("Error en VerificarInscripcionExistente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Listar todas las inscripciones
     * @return array
     */
    public static function ListarInscripcionesModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT i.idInscripcion, i.FechaInscripcion, i.costomatricula, i.montoPagado, i.pagoCompleto, i.porcentajeDescuento, i.montoDescuento, i.nvauchermatricula, i.Estado,
                        e.EstudianteID, e.Nombre, e.Apaterno, e.Amaterno, e.Ci,
                        p.ProgramaID, p.NombrePrograma, p.GradoAcademico, p.Codigo, p.Sede, p.Version,
                        COALESCE(
                            (SELECT pc.NumeroCuenta FROM programacuenta pc
                             WHERE pc.ProgramaID = p.ProgramaID AND pc.TipoCuenta = 'MATRICULA' AND pc.Principal = 1 AND pc.Estado = 1
                             LIMIT 1),
                            p.NumeroTramite
                        ) AS NumeroTramite
                 FROM estudianteprograma i
                 INNER JOIN estudiante e ON i.EstudianteID = e.EstudianteID
                 INNER JOIN programa p ON i.ProgramaID = p.ProgramaID
                 ORDER BY i.FechaInscripcion DESC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarInscripcionesModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener inscripción por ID
     * @param int $idInscripcion
     * @return array|false
     */
    public static function ObtenerInscripcionPorIdModelo($idInscripcion)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT i.*,
                        e.Nombre, e.Apaterno, e.Amaterno, e.Ci, e.Correo, e.Celular,
                        p.NombrePrograma, p.GradoAcademico, p.Codigo, p.Costo, p.Sede, p.Version,
                        COALESCE(
                            (SELECT pc.NumeroCuenta FROM programacuenta pc
                             WHERE pc.ProgramaID = p.ProgramaID AND pc.TipoCuenta = 'MATRICULA' AND pc.Principal = 1 AND pc.Estado = 1
                             LIMIT 1),
                            p.NumeroTramite
                        ) AS NumeroTramite
                 FROM estudianteprograma i
                 INNER JOIN estudiante e ON i.EstudianteID = e.EstudianteID
                 INNER JOIN programa p ON i.ProgramaID = p.ProgramaID
                 WHERE i.idInscripcion = :idInscripcion"
            );
            $stmt->bindParam(":idInscripcion", $idInscripcion, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerInscripcionPorIdModelo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar estado de inscripción
     * @param int $idInscripcion
     * @param string $estado
     * @return bool
     */
    public static function ActualizarEstadoInscripcionModelo($idInscripcion, $estado, $idModuloAbandono = null, $motivo = null, $responsable = null)
    {
        try {
            // Al reactivar se borran los datos del abandono
            $abandono = $estado === 'ABANDONO';
            $stmt = Conexion::Conectar()->prepare(
                "UPDATE estudianteprograma
                 SET Estado = :estado, IdModuloAbandono = :modulo, MotivoAbandono = :motivo,
                     FechaAbandono = " . ($abandono ? "NOW()" : "NULL") . ", ResponsableAbandono = :responsable
                 WHERE idInscripcion = :idInscripcion"
            );
            return $stmt->execute([
                ':estado' => $estado,
                ':modulo' => $abandono ? $idModuloAbandono : null,
                ':motivo' => $abandono ? $motivo : null,
                ':responsable' => $abandono ? $responsable : null,
                ':idInscripcion' => $idInscripcion,
            ]);
        } catch (PDOException $e) {
            error_log("Error en ActualizarEstadoInscripcionModelo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Condición SQL: la inscripción `$alias` cursa el módulo `$modulo` (expresión SQL
     * interna, p. ej. ':moduloID' o 'm.Idmodulo'; nunca texto del usuario).
     * Activo: sí. Abandono: solo en los módulos anteriores al de abandono, o donde
     * ya tenga nota. Un abandono sin módulo (antiguo) = desde el inicio.
     * Los módulos van en el orden en que se crearon (Idmodulo), como en la situación académica.
     */
    public static function SqlCursaModulo($alias, $modulo)
    {
        return "($alias.Estado = 'ACTIVO' OR ($alias.Estado = 'ABANDONO' AND ("
            . "$modulo < $alias.IdModuloAbandono"
            . " OR EXISTS (SELECT 1 FROM calificacion cm WHERE cm.EstudianteID = $alias.EstudianteID"
            . " AND cm.Idmodulo = $modulo AND cm.Nota IS NOT NULL))))";
    }

    /**
     * Módulos del programa de una inscripción, para elegir desde cuál abandona:
     * con su nota (si la tiene). También devuelve el estado actual del abandono.
     * @return array|null ['inscripcion' => [...], 'modulos' => [...]] o null si no existe
     */
    public static function ModulosParaAbandonoModelo($idInscripcion)
    {
        try {
            $pdo = Conexion::Conectar();
            $stmt = $pdo->prepare(
                "SELECT idInscripcion, EstudianteID, ProgramaID, Estado, IdModuloAbandono
                 FROM estudianteprograma WHERE idInscripcion = :id"
            );
            $stmt->execute([':id' => $idInscripcion]);
            $inscripcion = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$inscripcion) {
                return null;
            }
            $stmt = $pdo->prepare(
                "SELECT m.Idmodulo, m.codigomodulo AS CodigoModulo, m.nombremodulo AS NombreModulo, m.FechaInicio,
                        (SELECT c.Nota FROM calificacion c WHERE c.Idmodulo = m.Idmodulo
                         AND c.EstudianteID = :est AND c.Nota IS NOT NULL LIMIT 1) AS Nota
                 FROM modulos m
                 WHERE m.ProgramaId = :prog AND m.estadomodulo != 'INACTIVO'
                 ORDER BY m.Idmodulo"
            );
            $stmt->execute([':est' => $inscripcion['EstudianteID'], ':prog' => $inscripcion['ProgramaID']]);
            return ['inscripcion' => $inscripcion, 'modulos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            error_log("Error en ModulosParaAbandonoModelo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Abandonos con lo que dejaron de pagar, para el reporte: costo pactado, pagado y
     * saldo (mismos montos que Saldos por estudiante / Reporte General), módulo desde
     * el que abandonó y motivo. Filtros opcionales: sede, grado, programa, C.I.
     */
    public static function ListarAbandonosModelo($filtros = [])
    {
        $condiciones = ["ep.Estado = 'ABANDONO'"];
        $params = [];
        if (!empty($filtros['sede'])) {
            $condiciones[] = 'p.Sede = :sede';
            $params[':sede'] = $filtros['sede'];
        }
        if (!empty($filtros['grado'])) {
            $condiciones[] = 'p.GradoAcademico = :grado';
            $params[':grado'] = $filtros['grado'];
        }
        if (!empty($filtros['programaID'])) {
            $condiciones[] = 'p.ProgramaID = :programa';
            $params[':programa'] = (int)$filtros['programaID'];
        }
        if (!empty($filtros['ci'])) {
            $condiciones[] = 'e.Ci = :ci';
            $params[':ci'] = $filtros['ci'];
        }
        $stmt = Conexion::Conectar()->prepare(
            "SELECT ep.idInscripcion, ep.FechaInscripcion, ep.FechaAbandono, ep.MotivoAbandono, ep.ResponsableAbandono,
                    e.EstudianteID, e.Ci, e.Celular,
                    TRIM(CONCAT_WS(' ', e.Apaterno, e.Amaterno, e.Nombre)) AS NombreLista,
                    p.ProgramaID, p.NombrePrograma, p.GradoAcademico, p.Sede, p.Version,
                    ma.codigomodulo AS CodigoModuloAbandono, ma.nombremodulo AS ModuloAbandono,
                    (SELECT COUNT(*) FROM calificacion c WHERE c.EstudianteID = ep.EstudianteID
                     AND c.ProgramaId = ep.ProgramaID AND c.Nota IS NOT NULL) AS ModulosConNota,
                    (SELECT COUNT(*) FROM modulos mt WHERE mt.ProgramaId = ep.ProgramaID AND mt.estadomodulo != 'INACTIVO') AS ModulosPrograma,
                    pp.TipoPlan,
                    COALESCE(ep.costomatricula, 0) AS CostoMatricula,
                    COALESCE(pp.MontoTotalPagar, 0) AS CostoPrograma,
                    COALESCE(pagado.TotalPagado, 0) AS CuotasCobrado,
                    (COALESCE(pp.MontoTotalPagar, 0) + COALESCE(ep.costomatricula, 0)) AS CostoTotal,
                    (COALESCE(pagado.TotalPagado, 0) + COALESCE(ep.costomatricula, 0)) AS TotalPagado,
                    GREATEST(COALESCE(pp.MontoTotalPagar, 0) - COALESCE(pagado.TotalPagado, 0), 0) AS SaldoPendiente
             FROM estudianteprograma ep
             INNER JOIN estudiante e ON e.EstudianteID = ep.EstudianteID
             INNER JOIN programa p ON p.ProgramaID = ep.ProgramaID
             LEFT JOIN modulos ma ON ma.Idmodulo = ep.IdModuloAbandono
             LEFT JOIN pagoprograma pp ON pp.idInscripcion = ep.idInscripcion
             LEFT JOIN (
                 SELECT idinscripcion, SUM(montocuota) AS TotalPagado
                 FROM pagomodulo WHERE Estado = 'PAGADO' GROUP BY idinscripcion
             ) pagado ON pagado.idinscripcion = ep.idInscripcion
             WHERE " . implode(' AND ', $condiciones) . "
             ORDER BY p.Sede, p.GradoAcademico, p.NombrePrograma, e.Apaterno, e.Amaterno, e.Nombre"
        );
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener inscripciones de un estudiante
     * @param int $estudianteID
     * @return array
     */
    public static function ObtenerInscripcionesPorEstudianteModelo($estudianteID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT i.idInscripcion, i.FechaInscripcion, i.costomatricula, i.montoPagado, i.pagoCompleto, i.Estado,
                        p.NombrePrograma, p.GradoAcademico, p.Codigo, p.Sede
                 FROM estudianteprograma i
                 INNER JOIN programa p ON i.ProgramaID = p.ProgramaID
                 WHERE i.EstudianteID = :estudianteID
                 ORDER BY i.FechaInscripcion DESC"
            );
            $stmt->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerInscripcionesPorEstudianteModelo: " . $e->getMessage());
            return [];
        }
    }
}
