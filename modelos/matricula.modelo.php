<?php
require_once 'conexion.modelo.php';

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
                (EstudianteID, ProgramaID, costomatricula, montoPagado, pagoCompleto, nvauchermatricula, FechaInscripcion, foto, Estado)
                VALUES (:estudianteID, :programaID, :costomatricula, :montoPagado, :pagoCompleto, :nvaucher, :fechaInscripcion, :foto, 'ACTIVO')"
            );

            $stmt->bindParam(":estudianteID", $datos['EstudianteID'], PDO::PARAM_INT);
            $stmt->bindParam(":programaID", $datos['ProgramaID'], PDO::PARAM_INT);
            $stmt->bindParam(":costomatricula", $datos['costomatricula']);
            $stmt->bindParam(":montoPagado", $datos['montoPagado']);
            $stmt->bindParam(":pagoCompleto", $datos['pagoCompleto'], PDO::PARAM_INT);
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
                    "SELECT Idmodulo, nombremodulo, costomodulo
                     FROM modulos
                     WHERE ProgramaId = :programaID AND estadomodulo = 'ACTIVO'"
                );
                $stmtModulos->bindParam(":programaID", $datos['ProgramaID'], PDO::PARAM_INT);
                $stmtModulos->execute();
                $modulos = $stmtModulos->fetchAll(PDO::FETCH_ASSOC);

                if (count($modulos) > 0) {
                    // Calcular costo por módulo si es necesario
                    // Si los módulos no tienen costo, distribuir el monto pagado entre ellos
                    $totalModulos = count($modulos);
                    $costoPorModulo = $datos['montoPagado'] / $totalModulos;

                    // Preparar statement para registrar pagos de módulos
                    $stmtPagoModulo = $pdo->prepare(
                        "INSERT INTO pagomodulo
                        (idinscripcion, IdModulo, costomodulo, fechapago, nvaucher, Estado)
                        VALUES (:idinscripcion, :idModulo, :costomodulo, :fechapago, :nvaucher, 'PAGADO')"
                    );

                    $modulosInscritos = 0;
                    $pagosFechaInscripcion = $datos['FechaInscripcion'];

                    foreach ($modulos as $modulo) {
                        // Determinar el costo del módulo
                        $costoModulo = !empty($modulo['costomodulo']) && floatval($modulo['costomodulo']) > 0
                            ? floatval($modulo['costomodulo'])
                            : $costoPorModulo;

                        // Registrar pago del módulo
                        $stmtPagoModulo->bindParam(":idinscripcion", $inscripcionID, PDO::PARAM_INT);
                        $stmtPagoModulo->bindParam(":idModulo", $modulo['Idmodulo'], PDO::PARAM_INT);
                        $stmtPagoModulo->bindParam(":costomodulo", $costoModulo, PDO::PARAM_STR);
                        $stmtPagoModulo->bindParam(":fechapago", $pagosFechaInscripcion, PDO::PARAM_STR);
                        $stmtPagoModulo->bindParam(":nvaucher", $datos['nvauchermatricula'], PDO::PARAM_STR);

                        if ($stmtPagoModulo->execute()) {
                            $modulosInscritos++;
                        } else {
                            error_log("Error al registrar pago de módulo: " . print_r($stmtPagoModulo->errorInfo(), true));
                        }
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
                "SELECT i.idInscripcion, i.FechaInscripcion, i.costomatricula, i.montoPagado, i.pagoCompleto, i.nvauchermatricula, i.Estado,
                        e.EstudianteID, e.Nombre, e.Apaterno, e.Amaterno, e.Ci,
                        p.ProgramaID, p.NombrePrograma, p.GradoAcademico, p.Codigo, p.Sede, p.Version, p.NumeroTramite
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
                        p.NombrePrograma, p.GradoAcademico, p.Codigo, p.Costo, p.Sede, p.Version, p.NumeroTramite
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
    public static function ActualizarEstadoInscripcionModelo($idInscripcion, $estado)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "UPDATE estudianteprograma SET Estado = :estado WHERE idInscripcion = :idInscripcion"
            );
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            $stmt->bindParam(":idInscripcion", $idInscripcion, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ActualizarEstadoInscripcionModelo: " . $e->getMessage());
            return false;
        }
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
