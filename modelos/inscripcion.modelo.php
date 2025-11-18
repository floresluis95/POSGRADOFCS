<?php
require_once 'conexion.modelo.php';

/**
 * Modelo de Inscripción
 * Gestiona inscripciones, planes de pago, cuotas y vouchers
 * Compatible con PHP 8 - PDO
 */
class InscripcionModelos
{
    /**
     * Obtener detalles de un programa por ID
     * @param int $programaID
     * @return array|false
     */
    public static function ObtenerDetalleProgramaModelo($programaID)
    {
        $stmt = Conexion::Conectar()->prepare(
            "SELECT ProgramaID, NombrePrograma, GradoAcademico, Codigo, DuracionMeses,
                    Modulos, FechaInicio, Sede, Costo, Detalle, Estado
             FROM programa
             WHERE ProgramaID = :programaID AND Estado = 1"
        );
        $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Listar programas por grado académico
     * @param string $gradoAcademico
     * @return array
     */
    public static function ListarProgramasPorGradoModelo($gradoAcademico)
    {
        try {
            error_log("ListarProgramasPorGradoModelo - Buscando: " . $gradoAcademico);

            // Normalizar el grado académico (sin acentos)
            $gradoNormalizado = str_replace(['Í', 'í'], ['I', 'i'], $gradoAcademico);
            error_log("ListarProgramasPorGradoModelo - Grado normalizado: " . $gradoNormalizado);

            $pdo = Conexion::Conectar();

            // Buscar con ambas variantes (con y sin tilde)
            $stmt = $pdo->prepare(
                "SELECT ProgramaID, NombrePrograma, GradoAcademico, Codigo, Costo, Modulos, Sede
                 FROM programa
                 WHERE (GradoAcademico = :gradoAcademico
                    OR GradoAcademico = :gradoNormalizado
                    OR REPLACE(REPLACE(GradoAcademico, 'Í', 'I'), 'í', 'i') = :gradoNormalizado)
                    AND Estado = 1
                 ORDER BY NombrePrograma ASC"
            );
            $stmt->bindParam(":gradoAcademico", $gradoAcademico, PDO::PARAM_STR);
            $stmt->bindParam(":gradoNormalizado", $gradoNormalizado, PDO::PARAM_STR);
            $stmt->execute();

            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("ListarProgramasPorGradoModelo - Resultados encontrados: " . count($resultados));

            if (count($resultados) > 0) {
                error_log("ListarProgramasPorGradoModelo - Primer resultado: " . print_r($resultados[0], true));
            }

            return $resultados;
        } catch (PDOException $e) {
            error_log("Error en ListarProgramasPorGradoModelo: " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());
            return [];
        }
    }

    /**
     * Registrar inscripción de estudiante a programa
     * @param array $datos - Array con EstudianteID, ProgramaID, FechaInscripcion
     * @return int|false ID de inscripción o false si falla
     */
    public static function RegistrarInscripcionModelo($datos)
    {
        try {
            $pdo = Conexion::Conectar();
            $pdo->beginTransaction();

            // 1. Verificar si ya está inscrito en el mismo programa
            $stmtCheck = $pdo->prepare(
                "SELECT idInscripcion FROM estudianteprograma
                 WHERE EstudianteID = :estudianteID AND ProgramaID = :programaID"
            );
            $stmtCheck->bindParam(":estudianteID", $datos['EstudianteID'], PDO::PARAM_INT);
            $stmtCheck->bindParam(":programaID", $datos['ProgramaID'], PDO::PARAM_INT);
            $stmtCheck->execute();

            if ($stmtCheck->fetch()) {
                $pdo->rollBack();
                throw new Exception("El estudiante ya está inscrito en este programa");
            }

            // 2. Verificar que el estudiante exista y esté activo
            $stmtEstudiante = $pdo->prepare(
                "SELECT EstudianteID FROM estudiante WHERE EstudianteID = :estudianteID AND Estado = 1"
            );
            $stmtEstudiante->bindParam(":estudianteID", $datos['EstudianteID'], PDO::PARAM_INT);
            $stmtEstudiante->execute();

            if (!$stmtEstudiante->fetch()) {
                $pdo->rollBack();
                throw new Exception("El estudiante no existe o está inactivo");
            }

            // 3. Verificar que el programa exista y esté activo
            $stmtPrograma = $pdo->prepare(
                "SELECT ProgramaID FROM programa WHERE ProgramaID = :programaID AND Estado = 1"
            );
            $stmtPrograma->bindParam(":programaID", $datos['ProgramaID'], PDO::PARAM_INT);
            $stmtPrograma->execute();

            if (!$stmtPrograma->fetch()) {
                $pdo->rollBack();
                throw new Exception("El programa no existe o está inactivo");
            }

            // 4. Insertar inscripción en tabla estudianteprograma
            $stmt = $pdo->prepare(
                "INSERT INTO estudianteprograma (EstudianteID, ProgramaID, FechaInscripcion)
                 VALUES (:estudianteID, :programaID, :fechaInscripcion)"
            );
            $stmt->bindParam(":estudianteID", $datos['EstudianteID'], PDO::PARAM_INT);
            $stmt->bindParam(":programaID", $datos['ProgramaID'], PDO::PARAM_INT);
            $stmt->bindParam(":fechaInscripcion", $datos['FechaInscripcion'], PDO::PARAM_STR);

            if (!$stmt->execute()) {
                $pdo->rollBack();
                throw new Exception("Error al registrar inscripción en la base de datos");
            }

            $idInscripcion = $pdo->lastInsertId();

            $pdo->commit();
            return $idInscripcion;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en RegistrarInscripcionModelo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear plan de pagos para una inscripción
     * @param array $datos
     * @return int|false PlanPagoID o false si falla
     */
    public static function CrearPlanPagosModelo($datos)
    {
        try {
            $pdo = Conexion::Conectar();
            $pdo->beginTransaction();

            // 1. Crear plan de pagos
            $stmt = $pdo->prepare(
                "INSERT INTO plan_pagos
                 (idInscripcion, CostoTotal, MontoPagoInicial, MontoModulos, CantidadModulos, CostoPorModulo)
                 VALUES (:idInscripcion, :costoTotal, :montoPagoInicial, :montoModulos, :cantidadModulos, :costoPorModulo)"
            );

            $stmt->bindParam(":idInscripcion", $datos['idInscripcion'], PDO::PARAM_INT);
            $stmt->bindParam(":costoTotal", $datos['CostoTotal'], PDO::PARAM_STR);
            $stmt->bindParam(":montoPagoInicial", $datos['MontoPagoInicial'], PDO::PARAM_STR);
            $stmt->bindParam(":montoModulos", $datos['MontoModulos'], PDO::PARAM_STR);
            $stmt->bindParam(":cantidadModulos", $datos['CantidadModulos'], PDO::PARAM_INT);
            $stmt->bindParam(":costoPorModulo", $datos['CostoPorModulo'], PDO::PARAM_STR);

            if (!$stmt->execute()) {
                throw new Exception("Error al crear plan de pagos");
            }

            $planPagoID = $pdo->lastInsertId();

            // 2. Crear cuotas (una por módulo)
            $stmtCuota = $pdo->prepare(
                "INSERT INTO cuota
                 (PlanPagoID, NumeroModulo, NombreModulo, MontoCuota, FechaVencimiento)
                 VALUES (:planPagoID, :numeroModulo, :nombreModulo, :montoCuota, :fechaVencimiento)"
            );

            for ($i = 1; $i <= $datos['CantidadModulos']; $i++) {
                // Calcular fecha de vencimiento (mes a mes desde la inscripción)
                $fechaVencimiento = date('Y-m-d', strtotime("+{$i} month", strtotime($datos['FechaInicio'])));

                // Nombre del módulo
                $nombreModulo = "MÓDULO " . romano($i);

                // Monto de la cuota (distribuir equitativamente)
                if ($i == $datos['CantidadModulos']) {
                    // Última cuota: ajustar por redondeos
                    $montoCuota = $datos['MontoModulos'] - ($datos['CostoPorModulo'] * ($datos['CantidadModulos'] - 1));
                } else {
                    $montoCuota = $datos['CostoPorModulo'];
                }

                $stmtCuota->bindParam(":planPagoID", $planPagoID, PDO::PARAM_INT);
                $stmtCuota->bindParam(":numeroModulo", $i, PDO::PARAM_INT);
                $stmtCuota->bindParam(":nombreModulo", $nombreModulo, PDO::PARAM_STR);
                $stmtCuota->bindParam(":montoCuota", $montoCuota, PDO::PARAM_STR);
                $stmtCuota->bindParam(":fechaVencimiento", $fechaVencimiento, PDO::PARAM_STR);

                if (!$stmtCuota->execute()) {
                    throw new Exception("Error al crear cuota " . $i);
                }
            }

            $pdo->commit();
            return $planPagoID;

        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Error en CrearPlanPagosModelo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registrar voucher de pago
     * @param array $datos
     * @return int|false VoucherID o false si falla
     */
    public static function RegistrarVoucherModelo($datos)
    {
        try {
            $pdo = Conexion::Conectar();
            $pdo->beginTransaction();

            // 1. Insertar voucher
            $stmt = $pdo->prepare(
                "INSERT INTO voucher
                 (CuotaID, CodigoVoucher, MontoPago, FechaPago, MetodoPago, NumeroTransaccion,
                  ArchivoVoucher, RegistradoPor, Observaciones)
                 VALUES (:cuotaID, :codigoVoucher, :montoPago, :fechaPago, :metodoPago,
                         :numeroTransaccion, :archivoVoucher, :registradoPor, :observaciones)"
            );

            $stmt->bindParam(":cuotaID", $datos['CuotaID'], PDO::PARAM_INT);
            $stmt->bindParam(":codigoVoucher", $datos['CodigoVoucher'], PDO::PARAM_STR);
            $stmt->bindParam(":montoPago", $datos['MontoPago'], PDO::PARAM_STR);
            $stmt->bindParam(":fechaPago", $datos['FechaPago'], PDO::PARAM_STR);
            $stmt->bindParam(":metodoPago", $datos['MetodoPago'], PDO::PARAM_STR);
            $stmt->bindParam(":numeroTransaccion", $datos['NumeroTransaccion'], PDO::PARAM_STR);
            $stmt->bindParam(":archivoVoucher", $datos['ArchivoVoucher'], PDO::PARAM_STR);
            $stmt->bindParam(":registradoPor", $datos['RegistradoPor'], PDO::PARAM_INT);
            $stmt->bindParam(":observaciones", $datos['Observaciones'], PDO::PARAM_STR);

            if (!$stmt->execute()) {
                throw new Exception("Error al registrar voucher");
            }

            $voucherID = $pdo->lastInsertId();

            // 2. Actualizar monto pagado de la cuota
            $stmtUpdate = $pdo->prepare(
                "UPDATE cuota
                 SET MontoPagado = MontoPagado + :montoPago,
                     FechaPago = :fechaPago,
                     EstadoPago = CASE
                         WHEN (MontoPagado + :montoPago) >= MontoCuota THEN 'PAGADO'
                         WHEN (MontoPagado + :montoPago) > 0 THEN 'PARCIAL'
                         ELSE EstadoPago
                     END
                 WHERE CuotaID = :cuotaID"
            );

            $stmtUpdate->bindParam(":montoPago", $datos['MontoPago'], PDO::PARAM_STR);
            $stmtUpdate->bindParam(":fechaPago", $datos['FechaPago'], PDO::PARAM_STR);
            $stmtUpdate->bindParam(":cuotaID", $datos['CuotaID'], PDO::PARAM_INT);

            if (!$stmtUpdate->execute()) {
                throw new Exception("Error al actualizar cuota");
            }

            $pdo->commit();
            return $voucherID;

        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Error en RegistrarVoucherModelo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener cuotas de un plan de pagos
     * @param int $planPagoID
     * @return array
     */
    public static function ObtenerCuotasPlanModelo($planPagoID)
    {
        $stmt = Conexion::Conectar()->prepare(
            "SELECT CuotaID, NumeroModulo, NombreModulo, MontoCuota, FechaVencimiento,
                    EstadoPago, MontoPagado, FechaPago, Observaciones
             FROM cuota
             WHERE PlanPagoID = :planPagoID
             ORDER BY NumeroModulo ASC"
        );
        $stmt->bindParam(":planPagoID", $planPagoID, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener vouchers de una cuota
     * @param int $cuotaID
     * @return array
     */
    public static function ObtenerVouchersCuotaModelo($cuotaID)
    {
        $stmt = Conexion::Conectar()->prepare(
            "SELECT v.*, CONCAT(p.Nombres, ' ', p.ApellidoPaterno) AS RegistradoPorNombre
             FROM voucher v
             LEFT JOIN personal p ON v.RegistradoPor = p.IdPersonal
             WHERE v.CuotaID = :cuotaID
             ORDER BY v.FechaPago DESC"
        );
        $stmt->bindParam(":cuotaID", $cuotaID, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener estado de pagos de un estudiante
     * @param int $estudianteID
     * @return array
     */
    public static function ObtenerEstadoPagosEstudianteModelo($estudianteID)
    {
        $stmt = Conexion::Conectar()->prepare(
            "SELECT * FROM vista_estado_pagos WHERE EstudianteID = :estudianteID"
        );
        $stmt->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generar código único de voucher
     * @return string
     */
    public static function GenerarCodigoVoucherModelo()
    {
        // Formato: VOU-YYYYMMDD-HHMMSS-RAND
        return 'VOU-' . date('Ymd-His') . '-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 4));
    }

    /**
     * Listar todas las inscripciones
     * @return array
     */
    public static function ListarInscripcionesModelo()
    {
        $stmt = Conexion::Conectar()->prepare(
            "SELECT ep.idInscripcion, ep.FechaInscripcion,
                    e.EstudianteID, CONCAT(e.Nombre, ' ', e.Apaterno, ' ', e.Amaterno) AS NombreCompleto, e.Ci,
                    p.ProgramaID, p.NombrePrograma, p.GradoAcademico, p.Sede
             FROM estudianteprograma ep
             INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
             INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
             ORDER BY ep.FechaInscripcion DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener inscripción por ID
     * @param int $idInscripcion
     * @return array|false
     */
    public static function ObtenerInscripcionPorIdModelo($idInscripcion)
    {
        $stmt = Conexion::Conectar()->prepare(
            "SELECT ep.idInscripcion, ep.EstudianteID, ep.ProgramaID, ep.FechaInscripcion,
                    e.Nombre, e.Apaterno, e.Amaterno, e.Ci, e.Correo, e.Celular,
                    p.NombrePrograma, p.GradoAcademico, p.Costo, p.Modulos, p.Sede
             FROM estudianteprograma ep
             INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
             INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
             WHERE ep.idInscripcion = :idInscripcion"
        );
        $stmt->bindParam(":idInscripcion", $idInscripcion, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

/**
 * Función auxiliar: Convertir número a romano
 * @param int $numero
 * @return string
 */
function romano($numero)
{
    $romanos = array(
        'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X',
        'XI', 'XII', 'XIII', 'XIV', 'XV', 'XVI', 'XVII', 'XVIII', 'XIX', 'XX'
    );
    return isset($romanos[$numero - 1]) ? $romanos[$numero - 1] : $numero;
}
