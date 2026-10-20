<?php
require_once __DIR__ . '/../core/conexion.modelo.php';

/**
 * Modelo del Plazo de Cuota por Programa.
 *
 * Administración/Secretaría define, para cada Programa, una fecha límite
 * (plazo) de pago de la cuota vigente. Se usa para:
 *  - Mostrar la fecha en el Calendario Académico (todos los roles).
 *  - Avisar al Estudiante al ingresar al sistema si tiene una cuota
 *    pendiente y el plazo de su programa ya venció.
 *  - Bloquear la consulta de sus calificaciones mientras no regularice.
 */
class PlazoCuotaProgramaModelo
{
    /**
     * Listar los plazos activos, con datos del programa (para el panel de
     * administración en el Calendario Académico).
     * @return array
     */
    public static function ListarPlazosModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    pc.IdPlazo, pc.ProgramaID, pc.FechaPlazo, pc.Descripcion, pc.Estado,
                    p.NombrePrograma, p.Codigo AS CodigoPrograma, p.GradoAcademico, p.Sede
                 FROM plazocuotaprograma pc
                 INNER JOIN programa p ON p.ProgramaID = pc.ProgramaID
                 WHERE pc.Estado = 'ACTIVO'
                 ORDER BY pc.FechaPlazo DESC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarPlazosModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * @param int $idPlazo
     * @return array|null
     */
    public static function ObtenerPlazoPorIdModelo($idPlazo)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT * FROM plazocuotaprograma WHERE IdPlazo = :id"
            );
            $stmt->bindParam(":id", $idPlazo, PDO::PARAM_INT);
            $stmt->execute();
            $plazo = $stmt->fetch(PDO::FETCH_ASSOC);
            return $plazo ?: null;
        } catch (PDOException $e) {
            error_log("Error en ObtenerPlazoPorIdModelo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * @param array $datos ProgramaID, FechaPlazo, Descripcion, CreadoPorUsuarioID
     * @return int|false
     */
    public static function RegistrarPlazoModelo($datos)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "INSERT INTO plazocuotaprograma (ProgramaID, FechaPlazo, Descripcion, CreadoPorUsuarioID)
                 VALUES (:programaID, :fechaPlazo, :descripcion, :creadoPor)"
            );
            $stmt->bindParam(":programaID", $datos['ProgramaID'], PDO::PARAM_INT);
            $stmt->bindParam(":fechaPlazo", $datos['FechaPlazo'], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos['Descripcion'], PDO::PARAM_STR);
            if (!empty($datos['CreadoPorUsuarioID'])) {
                $stmt->bindParam(":creadoPor", $datos['CreadoPorUsuarioID'], PDO::PARAM_INT);
            } else {
                $stmt->bindValue(":creadoPor", null, PDO::PARAM_NULL);
            }

            if (!$stmt->execute()) {
                return false;
            }
            return (int)Conexion::Conectar()->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en RegistrarPlazoModelo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @param array $datos IdPlazo, ProgramaID, FechaPlazo, Descripcion
     * @return bool
     */
    public static function ActualizarPlazoModelo($datos)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "UPDATE plazocuotaprograma SET
                    ProgramaID = :programaID,
                    FechaPlazo = :fechaPlazo,
                    Descripcion = :descripcion
                 WHERE IdPlazo = :id"
            );
            $stmt->bindParam(":programaID", $datos['ProgramaID'], PDO::PARAM_INT);
            $stmt->bindParam(":fechaPlazo", $datos['FechaPlazo'], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos['Descripcion'], PDO::PARAM_STR);
            $stmt->bindParam(":id", $datos['IdPlazo'], PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ActualizarPlazoModelo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Anular (baja lógica) un plazo de cuota.
     * @param int $idPlazo
     * @return bool
     */
    public static function AnularPlazoModelo($idPlazo)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "UPDATE plazocuotaprograma SET Estado = 'ANULADO' WHERE IdPlazo = :id"
            );
            $stmt->bindParam(":id", $idPlazo, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en AnularPlazoModelo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Plazos de cuota dentro de un rango de fechas, para mostrarlos como
     * marcadores en el Calendario Académico.
     * @param string|null $desde
     * @param string|null $hasta
     * @param int|null $programaID
     * @param string|null $sede
     * @param int[]|null $programaIDs Solo estos programas (Estudiante: sus programas inscritos).
     *        null = sin restricción; un array vacío no devuelve nada.
     * @return array
     */
    public static function ObtenerPlazosParaCalendarioModelo($desde = null, $hasta = null, $programaID = null, $sede = null, $programaIDs = null)
    {
        if (is_array($programaIDs) && empty($programaIDs)) {
            return [];
        }
        try {
            $sql = "SELECT
                        pc.IdPlazo, pc.ProgramaID, pc.FechaPlazo, pc.Descripcion,
                        p.NombrePrograma, p.Codigo AS CodigoPrograma, p.GradoAcademico, p.Sede
                    FROM plazocuotaprograma pc
                    INNER JOIN programa p ON p.ProgramaID = pc.ProgramaID
                    WHERE pc.Estado = 'ACTIVO'";
            $params = [];

            if (!empty($desde)) {
                $sql .= " AND pc.FechaPlazo >= :desde";
                $params[':desde'] = substr($desde, 0, 10);
            }
            if (!empty($hasta)) {
                $sql .= " AND pc.FechaPlazo <= :hasta";
                $params[':hasta'] = substr($hasta, 0, 10);
            }
            if (!empty($programaID)) {
                $sql .= " AND pc.ProgramaID = :programaID";
                $params[':programaID'] = $programaID;
            }
            if (!empty($sede)) {
                $sql .= " AND p.Sede = :sede";
                $params[':sede'] = $sede;
            }
            if (is_array($programaIDs)) {
                $marcas = [];
                foreach (array_values($programaIDs) as $i => $pid) {
                    $marcas[] = ':pid' . $i;
                    $params[':pid' . $i] = (int)$pid;
                }
                $sql .= " AND pc.ProgramaID IN (" . implode(',', $marcas) . ")";
            }

            $sql .= " ORDER BY pc.FechaPlazo ASC";

            $stmt = Conexion::Conectar()->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerPlazosParaCalendarioModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Programas del estudiante con una cuota vencida y sin cancelar. Se usa
     * para el aviso al ingresar al sistema y para bloquear la consulta de
     * calificaciones.
     *
     * Combina dos fuentes de fecha límite (puede haber una orden vencida sin
     * que el programa tenga un "Plazo de Cuota" configurado en Calendario
     * Académico, que es el caso más común):
     *  - El plazo general del PROGRAMA en `plazocuotaprograma` (Calendario
     *    Académico, configurado a mano por Administración/Secretaría).
     *  - La fecha límite propia de cada orden de cuota (`ordenpago.FechaVencimiento`,
     *    la que el administrador pone en "Fecha límite de pago" al generarla).
     * Si un programa aparece por ambas fuentes, se queda con la más vencida.
     * @param int $estudianteID
     * @return array<int, array{ProgramaID:int, NombrePrograma:string, FechaPlazo:string, DiasVencido:int}>
     */
    public static function ObtenerAvisoEstudianteModelo($estudianteID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    p.ProgramaID, p.NombrePrograma, pc.FechaPlazo,
                    DATEDIFF(CURDATE(), pc.FechaPlazo) AS DiasVencido
                 FROM estudianteprograma ep
                 INNER JOIN programa p ON p.ProgramaID = ep.ProgramaID
                 INNER JOIN plazocuotaprograma pc ON pc.ProgramaID = ep.ProgramaID AND pc.Estado = 'ACTIVO'
                 WHERE ep.EstudianteID = :estudianteID1
                 AND ep.Estado = 'ACTIVO'
                 AND pc.FechaPlazo < CURDATE()
                 AND EXISTS (
                     SELECT 1 FROM ordenpago op
                     WHERE op.EstudianteID = ep.EstudianteID
                     AND op.ProgramaID = ep.ProgramaID
                     AND op.Estado = 'PENDIENTE'
                     AND op.ListaModulos IS NULL AND COALESCE(op.Observaciones, '') <> 'CLINICA'
                 )
                 UNION
                 SELECT
                    p.ProgramaID, p.NombrePrograma, op.FechaVencimiento AS FechaPlazo,
                    DATEDIFF(CURDATE(), op.FechaVencimiento) AS DiasVencido
                 FROM ordenpago op
                 INNER JOIN programa p ON p.ProgramaID = op.ProgramaID
                 WHERE op.EstudianteID = :estudianteID2
                 AND op.Estado = 'PENDIENTE'
                 AND op.ListaModulos IS NULL AND COALESCE(op.Observaciones, '') <> 'CLINICA'
                 AND op.FechaVencimiento IS NOT NULL
                 AND op.FechaVencimiento < CURDATE()"
            );
            $stmt->bindParam(":estudianteID1", $estudianteID, PDO::PARAM_INT);
            $stmt->bindParam(":estudianteID2", $estudianteID, PDO::PARAM_INT);
            $stmt->execute();
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Puede haber una fila por cada fuente para el mismo programa: nos
            // quedamos con la más vencida (mayor DiasVencido) por programa.
            $porPrograma = [];
            foreach ($filas as $fila) {
                $id = (int)$fila['ProgramaID'];
                if (!isset($porPrograma[$id]) || (int)$fila['DiasVencido'] > (int)$porPrograma[$id]['DiasVencido']) {
                    $porPrograma[$id] = $fila;
                }
            }
            usort($porPrograma, function ($a, $b) {
                return strcmp($a['FechaPlazo'], $b['FechaPlazo']);
            });

            return array_values($porPrograma);
        } catch (PDOException $e) {
            error_log("Error en ObtenerAvisoEstudianteModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Plazo de cuota por Programa del estudiante, con una orden pendiente de
     * pago, para el aviso al ingresar al sistema. A diferencia de
     * ObtenerAvisoEstudianteModelo(), se muestra desde que Administración
     * registra el plazo (venza o no), no solo cuando ya está vencido; sirve
     * únicamente para notificar, nunca para bloquear calificaciones.
     * @param int $estudianteID
     * @return array<int, array{ProgramaID:int, NombrePrograma:string, FechaPlazo:string, DiasVencido:int}>
     */
    public static function ObtenerAvisosProgramaModelo($estudianteID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    p.ProgramaID, p.NombrePrograma, pc.FechaPlazo,
                    DATEDIFF(CURDATE(), pc.FechaPlazo) AS DiasVencido
                 FROM estudianteprograma ep
                 INNER JOIN programa p ON p.ProgramaID = ep.ProgramaID
                 INNER JOIN plazocuotaprograma pc ON pc.ProgramaID = ep.ProgramaID AND pc.Estado = 'ACTIVO'
                 WHERE ep.EstudianteID = :estudianteID
                 AND ep.Estado = 'ACTIVO'
                 AND EXISTS (
                     SELECT 1 FROM ordenpago op
                     WHERE op.EstudianteID = ep.EstudianteID
                     AND op.ProgramaID = ep.ProgramaID
                     AND op.Estado = 'PENDIENTE'
                     AND op.ListaModulos IS NULL AND COALESCE(op.Observaciones, '') <> 'CLINICA'
                 )
                 ORDER BY pc.FechaPlazo ASC"
            );
            $stmt->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerAvisosProgramaModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Si el estudiante debe quedar bloqueado para ver sus calificaciones de
     * un programa puntual (plazo de cuota vencido y con orden pendiente).
     * @param int $estudianteID
     * @param int $programaID
     * @return bool
     */
    public static function TieneBloqueoProgramaModelo($estudianteID, $programaID)
    {
        $avisos = self::ObtenerAvisoEstudianteModelo($estudianteID);
        foreach ($avisos as $a) {
            if ((int)$a['ProgramaID'] === (int)$programaID) {
                return true;
            }
        }
        return false;
    }
}
