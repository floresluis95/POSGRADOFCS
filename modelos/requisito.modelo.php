<?php
/**
 * Modelo de Requisitos de Matriculación
 * `requisito` es el catálogo (Trámite B, fotocopia de carnet, ...) y
 * `requisitoinscripcion` guarda lo que entregó cada inscripción
 * (estudiante + programa). No bloquea la inscripción: solo registra.
 */

require_once file_exists(__DIR__ . '/conexion.modelo.php') ? __DIR__ . '/conexion.modelo.php' : __DIR__ . '/../core/conexion.modelo.php';

class RequisitoModelo
{
    // Grados académicos de los programas (programa.GradoAcademico)
    const GRADOS = ['DIPLOMADO', 'ESPECIALIDAD', 'MAESTRIA', 'DOCTORADO'];

    /**
     * Condición SQL "el requisito r se pide en el grado del programa p": se pide si
     * no tiene grados asignados en `requisitogrado` (aplica a todos) o si uno de
     * ellos es el del programa. Requiere los alias r (requisito) y p (programa).
     */
    public static function CondicionGrado()
    {
        return "(NOT EXISTS (SELECT 1 FROM requisitogrado rg WHERE rg.IdRequisito = r.IdRequisito)
                 OR EXISTS (SELECT 1 FROM requisitogrado rg WHERE rg.IdRequisito = r.IdRequisito
                            AND rg.GradoAcademico = REPLACE(UPPER(TRIM(p.GradoAcademico)), 'Í', 'I')))";
    }

    /**
     * Requisitos activos del catálogo, en su orden. Con $programaID solo los que
     * se piden en el grado académico de ese programa.
     * @return array IdRequisito, Nombre, Obligatorio, Automatico
     */
    public static function ListarActivosModelo($programaID = null)
    {
        try {
            if ($programaID) {
                $stmt = Conexion::Conectar()->prepare(
                    "SELECT r.IdRequisito, r.Nombre, r.Obligatorio, r.Automatico
                     FROM requisito r
                     INNER JOIN programa p ON p.ProgramaID = :programaID
                     WHERE r.Estado = 1 AND " . self::CondicionGrado() . "
                     ORDER BY r.Orden, r.IdRequisito"
                );
                $stmt->execute([':programaID' => (int)$programaID]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            $stmt = Conexion::Conectar()->prepare(
                "SELECT IdRequisito, Nombre, Obligatorio, Automatico FROM requisito WHERE Estado = 1 ORDER BY Orden, IdRequisito"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarActivosModelo (requisitos): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Requisitos activos del grado de la inscripción, con lo entregado (los que
     * aún no tienen registro vienen con Entregado = 0).
     * @param int $idInscripcion
     * @return array
     */
    public static function ListarPorInscripcionModelo($idInscripcion)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT r.IdRequisito, r.Nombre, r.Obligatorio, r.Automatico,
                        COALESCE(ri.Entregado, 0) AS Entregado, ri.FechaEntrega, ri.Observacion,
                        ri.ResponsableRegistro, ri.FechaActualizacion
                 FROM requisito r
                 INNER JOIN estudianteprograma ep ON ep.idInscripcion = :idInscripcionGrado
                 INNER JOIN programa p ON p.ProgramaID = ep.ProgramaID
                 LEFT JOIN requisitoinscripcion ri ON ri.IdRequisito = r.IdRequisito AND ri.idInscripcion = :idInscripcion
                 WHERE r.Estado = 1 AND " . self::CondicionGrado() . "
                 ORDER BY r.Orden, r.IdRequisito"
            );
            $stmt->execute([':idInscripcion' => $idInscripcion, ':idInscripcionGrado' => $idInscripcion]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarPorInscripcionModelo (requisitos): " . $e->getMessage());
            return [];
        }
    }

    public static function ExisteInscripcionModelo($idInscripcion)
    {
        try {
            $stmt = Conexion::Conectar()->prepare("SELECT 1 FROM estudianteprograma WHERE idInscripcion = :id");
            $stmt->execute([':id' => $idInscripcion]);
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en ExisteInscripcionModelo (requisitos): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Guardar el estado de los requisitos de una inscripción, en una sola
     * transacción. Solo se tocan los requisitos recibidos; la fecha de entrega
     * se conserva si ya estaba registrada y vuelve a marcarse.
     * @param int $idInscripcion
     * @param array $requisitos [IdRequisito => ['entregado' => bool, 'fecha' => 'Y-m-d'|null, 'observacion' => string]]
     * @param string|null $responsable
     * @return bool
     */
    public static function GuardarModelo($idInscripcion, array $requisitos, $responsable)
    {
        $pdo = Conexion::Conectar();
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "INSERT INTO requisitoinscripcion
                    (idInscripcion, IdRequisito, Entregado, FechaEntrega, Observacion, ResponsableRegistro)
                 SELECT :idInscripcion, r.IdRequisito, :entregado, :fecha, :observacion, :responsable
                 FROM requisito r WHERE r.IdRequisito = :idRequisito AND r.Estado = 1
                 ON DUPLICATE KEY UPDATE
                    Entregado = VALUES(Entregado),
                    FechaEntrega = VALUES(FechaEntrega),
                    Observacion = VALUES(Observacion),
                    ResponsableRegistro = VALUES(ResponsableRegistro)"
            );

            foreach ($requisitos as $idRequisito => $datos) {
                $entregado = !empty($datos['entregado']);
                $stmt->execute([
                    ':idInscripcion' => $idInscripcion,
                    ':idRequisito' => (int)$idRequisito,
                    ':entregado' => $entregado ? 1 : 0,
                    ':fecha' => $entregado ? ($datos['fecha'] ?: date('Y-m-d')) : null,
                    ':observacion' => $datos['observacion'] !== '' ? $datos['observacion'] : null,
                    ':responsable' => $responsable,
                ]);
            }

            $pdo->commit();
            return true;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en GuardarModelo (requisitos): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marcar como entregados los requisitos automáticos (Ficha de inscripción,
     * Documento de compromiso...) de una inscripción recién validada. Si ya
     * estaban marcados se conserva su fecha de entrega.
     * @return bool
     */
    public static function MarcarAutomaticosModelo($idInscripcion, $fecha, $responsable)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "INSERT INTO requisitoinscripcion (idInscripcion, IdRequisito, Entregado, FechaEntrega, ResponsableRegistro)
                 SELECT ep.idInscripcion, r.IdRequisito, 1, :fecha, :responsable
                 FROM requisito r
                 INNER JOIN estudianteprograma ep ON ep.idInscripcion = :idInscripcion
                 INNER JOIN programa p ON p.ProgramaID = ep.ProgramaID
                 WHERE r.Automatico = 1 AND r.Estado = 1 AND " . self::CondicionGrado() . "
                 ON DUPLICATE KEY UPDATE
                    FechaEntrega = IF(Entregado = 1 AND FechaEntrega IS NOT NULL, FechaEntrega, VALUES(FechaEntrega)),
                    Entregado = 1,
                    ResponsableRegistro = VALUES(ResponsableRegistro)"
            );
            return $stmt->execute([
                ':idInscripcion' => $idInscripcion,
                ':fecha' => $fecha,
                ':responsable' => $responsable,
            ]);
        } catch (PDOException $e) {
            error_log("Error en MarcarAutomaticosModelo (requisitos): " . $e->getMessage());
            return false;
        }
    }

    // ========================================
    // CATÁLOGO (pantalla "Catálogo de Requisitos", solo ADM)
    // ========================================

    /**
     * Todos los requisitos del catálogo, activos e inactivos, con cuántas
     * inscripciones lo tienen entregado.
     * @return array
     */
    public static function ListarCatalogoModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT r.IdRequisito, r.Nombre, r.Obligatorio, r.Automatico, r.Orden, r.Estado,
                        (SELECT COUNT(*) FROM requisitoinscripcion ri
                         WHERE ri.IdRequisito = r.IdRequisito AND ri.Entregado = 1) AS Entregados,
                        (SELECT GROUP_CONCAT(rg.GradoAcademico ORDER BY FIELD(rg.GradoAcademico, 'DIPLOMADO', 'ESPECIALIDAD', 'MAESTRIA', 'DOCTORADO'))
                         FROM requisitogrado rg WHERE rg.IdRequisito = r.IdRequisito) AS Grados
                 FROM requisito r
                 ORDER BY r.Estado DESC, r.Orden, r.IdRequisito"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarCatalogoModelo (requisitos): " . $e->getMessage());
            return [];
        }
    }

    /**
     * ¿Ya hay otro requisito con ese nombre? (sin distinguir mayúsculas)
     */
    public static function ExisteNombreModelo($nombre, $excluirId = 0)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT 1 FROM requisito WHERE LOWER(Nombre) = LOWER(:nombre) AND IdRequisito <> :id"
            );
            $stmt->execute([':nombre' => $nombre, ':id' => (int)$excluirId]);
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en ExisteNombreModelo (requisitos): " . $e->getMessage());
            return true;
        }
    }

    /**
     * Crear (IdRequisito = 0) o editar un requisito del catálogo, con los grados
     * en que se pide (vacío = todos). Uno nuevo va al final si no se indica orden.
     * @param string[] $grados
     * @return bool
     */
    public static function GuardarCatalogoModelo($idRequisito, $nombre, $obligatorio, $automatico, $orden, array $grados)
    {
        $pdo = Conexion::Conectar();
        try {
            $pdo->beginTransaction();
            if ($orden === null) {
                $orden = (int)$pdo->query("SELECT COALESCE(MAX(Orden), 0) + 1 FROM requisito")->fetchColumn();
            }

            if ($idRequisito > 0) {
                $stmt = $pdo->prepare(
                    "UPDATE requisito SET Nombre = :nombre, Obligatorio = :obligatorio, Automatico = :automatico, Orden = :orden
                     WHERE IdRequisito = :id"
                );
                $stmt->bindValue(':id', $idRequisito, PDO::PARAM_INT);
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO requisito (Nombre, Obligatorio, Automatico, Orden)
                     VALUES (:nombre, :obligatorio, :automatico, :orden)"
                );
            }
            $stmt->bindValue(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->bindValue(':obligatorio', $obligatorio ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(':automatico', $automatico ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(':orden', $orden, PDO::PARAM_INT);
            $stmt->execute();

            if ($idRequisito <= 0) {
                $idRequisito = (int)$pdo->lastInsertId();
            }

            // Grados: se reemplazan por los elegidos (ninguno = se pide en todos)
            $pdo->prepare("DELETE FROM requisitogrado WHERE IdRequisito = :id")->execute([':id' => $idRequisito]);
            $stmtGrado = $pdo->prepare("INSERT INTO requisitogrado (IdRequisito, GradoAcademico) VALUES (:id, :grado)");
            foreach ($grados as $grado) {
                $stmtGrado->execute([':id' => $idRequisito, ':grado' => $grado]);
            }

            $pdo->commit();
            return true;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en GuardarCatalogoModelo (requisitos): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Activar o desactivar un requisito. No se borra: las entregas ya
     * registradas se conservan y vuelven a verse si se reactiva.
     * @return bool
     */
    public static function CambiarEstadoModelo($idRequisito, $estado)
    {
        try {
            $stmt = Conexion::Conectar()->prepare("UPDATE requisito SET Estado = :estado WHERE IdRequisito = :id");
            return $stmt->execute([':estado' => $estado ? 1 : 0, ':id' => (int)$idRequisito]);
        } catch (PDOException $e) {
            error_log("Error en CambiarEstadoModelo (requisitos): " . $e->getMessage());
            return false;
        }
    }

    // ========================================
    // PORTAL DEL ESTUDIANTE
    // ========================================

    /**
     * Todas las inscripciones de un estudiante (un estudiante puede cursar dos
     * programas o haber culminado uno y empezado otro), con lo necesario para
     * saber en qué situación está cada programa: estado de la inscripción y
     * fechas de sus módulos.
     * @return array
     */
    public static function ListarInscripcionesEstudianteModelo($estudianteID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT ep.idInscripcion, ep.FechaInscripcion, ep.Estado,
                        p.NombrePrograma, p.GradoAcademico, p.Modulos AS ModulosPrograma,
                        (SELECT COUNT(*) FROM modulos m
                         WHERE m.ProgramaId = p.ProgramaID AND m.FechaFinal IS NOT NULL) AS ModulosConFecha,
                        (SELECT MAX(m.FechaFinal) FROM modulos m WHERE m.ProgramaId = p.ProgramaID) AS FinUltimoModulo
                 FROM estudianteprograma ep
                 INNER JOIN programa p ON p.ProgramaID = ep.ProgramaID
                 WHERE ep.EstudianteID = :id AND ep.Estado IN ('ACTIVO', 'CONFIRMADO', 'ABANDONO')
                 ORDER BY ep.FechaInscripcion DESC, ep.idInscripcion DESC"
            );
            $stmt->execute([':id' => (int)$estudianteID]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarInscripcionesEstudianteModelo (requisitos): " . $e->getMessage());
            return [];
        }
    }
}
