<?php
/**
 * Modelo de Pago de Módulos
 * Gestiona el registro de pagos de módulos por estudiante
 */

require_once 'conexion.modelo.php';

class PagoModuloModelo
{
    /**
     * Distribuir el costo total del programa entre sus módulos activos
     * Si el programa tiene 2 o más módulos, divide el costo equitativamente
     * @param int $programaID
     * @return bool
     */
    public static function DistribuirCostoProgramaEnModulosModelo($programaID)
    {
        try {
            $pdo = Conexion::Conectar();
            $pdo->beginTransaction();

            // 1. Obtener el costo total del programa
            $stmtPrograma = $pdo->prepare(
                "SELECT CostoMatricula FROM programa WHERE ProgramaID = :programaID"
            );
            $stmtPrograma->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmtPrograma->execute();
            $programa = $stmtPrograma->fetch(PDO::FETCH_ASSOC);

            if (!$programa || !isset($programa['CostoMatricula'])) {
                $pdo->rollBack();
                error_log("Programa no encontrado o sin costo: " . $programaID);
                return false;
            }

            $costoTotalPrograma = floatval($programa['CostoMatricula']);

            // 2. Contar módulos activos del programa
            $stmtCount = $pdo->prepare(
                "SELECT COUNT(*) as total FROM modulos
                 WHERE ProgramaId = :programaID AND estadomodulo = 'ACTIVO'"
            );
            $stmtCount->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmtCount->execute();
            $count = $stmtCount->fetch(PDO::FETCH_ASSOC);
            $totalModulos = intval($count['total']);

            if ($totalModulos === 0) {
                $pdo->rollBack();
                error_log("No hay módulos activos para el programa: " . $programaID);
                return false;
            }

            // 3. Calcular costo por módulo
            $costoPorModulo = $costoTotalPrograma / $totalModulos;

            // 4. Actualizar todos los módulos con el costo distribuido
            $stmtUpdate = $pdo->prepare(
                "UPDATE modulos
                 SET costomodulo = :costo
                 WHERE ProgramaId = :programaID AND estadomodulo = 'ACTIVO'"
            );
            $stmtUpdate->bindParam(":costo", $costoPorModulo, PDO::PARAM_STR);
            $stmtUpdate->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmtUpdate->execute();

            $pdo->commit();

            error_log("Costos distribuidos exitosamente: Programa ID $programaID, $totalModulos módulos, Bs. $costoPorModulo c/u");
            return true;

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en DistribuirCostoProgramaEnModulosModelo: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Obtener módulos de un programa con estado de pago por inscripción
     * Usa la tabla modulos (con 's') que tiene la estructura: ProgramaId, nombremodulo, codigomodulo
     * @param int $programaID
     * @param int $idinscripcion
     * @return array
     */
    public static function ObtenerModulosConEstadoPagoModelo($programaID, $idinscripcion)
    {
        // NOTA: Ya no es necesario distribuir costos porque los módulos
        // ya tienen su costo asignado cuando se registran
        // self::DistribuirCostoProgramaEnModulosModelo($programaID);

        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    m.Idmodulo as ModuloID,
                    m.nombremodulo as NombreModulo,
                    m.codigomodulo as Codigo,
                    m.costomodulo as Costo,
                    m.estadomodulo,
                    CONCAT(d.Nombre, ' ', d.Apaterno, ' ', d.Amaterno) as NombreDocente,
                    d.Especialidad as EspecialidadDocente,
                    pm.Idpagomodulo,
                    pm.costomodulo as CostoPagado,
                    pm.fechapago as FechaPago,
                    pm.nvaucher as NumeroVaucher,
                    pm.Estado as EstadoPago,
                    CASE
                        WHEN pm.Idpagomodulo IS NOT NULL THEN 1
                        ELSE 0
                    END as Pagado
                FROM modulos m
                LEFT JOIN docente d ON m.DocenteID = d.DocenteID
                LEFT JOIN pagomodulo pm ON m.Idmodulo = pm.IdModulo
                    AND pm.idinscripcion = :idinscripcion
                    AND pm.Estado != 'ANULADO'
                WHERE m.ProgramaId = :programaID
                AND m.estadomodulo = 'ACTIVO'
                ORDER BY m.codigomodulo ASC"
            );
            $stmt->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmt->bindParam(":idinscripcion", $idinscripcion, PDO::PARAM_INT);
            $stmt->execute();
            $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            self::AjustarUltimoModuloPendienteAlTotal($modulos, $programaID);

            return $modulos;
        } catch (PDOException $e) {
            error_log("Error en ObtenerModulosConEstadoPagoModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Si queda un único módulo pendiente de pago, ajusta su costo (solo para mostrarlo
     * en las tarjetas) para que la suma de todo lo pagado + este último módulo cierre
     * exactamente el costo total del programa.
     * @param array $modulos (por referencia) resultado de ObtenerModulosConEstadoPagoModelo
     * @param int $programaID
     * @return void
     */
    private static function AjustarUltimoModuloPendienteAlTotal(&$modulos, $programaID)
    {
        if (empty($modulos)) {
            return;
        }

        $pendientes = [];
        $sumaPagada = 0.0;

        foreach ($modulos as $i => $modulo) {
            if ((int)$modulo['Pagado'] === 1) {
                $sumaPagada += floatval($modulo['CostoPagado']);
            } else {
                $pendientes[] = $i;
            }
        }

        // Solo se ajusta cuando queda exactamente UN módulo pendiente: es el último
        // que falta cancelar para completar el programa.
        if (count($pendientes) !== 1) {
            return;
        }

        try {
            $stmtPrograma = Conexion::Conectar()->prepare(
                "SELECT Costo FROM programa WHERE ProgramaID = :programaID"
            );
            $stmtPrograma->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmtPrograma->execute();
            $programa = $stmtPrograma->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener costo del programa en AjustarUltimoModuloPendienteAlTotal: " . $e->getMessage());
            return;
        }

        if (!$programa || $programa['Costo'] === null) {
            return;
        }

        $costoTotalPrograma = floatval($programa['Costo']);
        $montoRestante = round($costoTotalPrograma - $sumaPagada, 2);

        if ($montoRestante < 0) {
            $montoRestante = 0.0;
        }

        $idxUltimo = $pendientes[0];
        $costoOriginal = floatval($modulos[$idxUltimo]['Costo']);

        // Solo se marca como "ajustado" si realmente cambia el monto (falta o sobra algo)
        if (abs($montoRestante - $costoOriginal) > 0.01) {
            $modulos[$idxUltimo]['CostoOriginal'] = $costoOriginal;
            $modulos[$idxUltimo]['Costo'] = $montoRestante;
            $modulos[$idxUltimo]['AjustadoAlTotal'] = true;
        } else {
            $modulos[$idxUltimo]['AjustadoAlTotal'] = false;
        }
    }

    /**
     * Ajusta, dentro de un lote de pago de módulos que se está registrando, el monto del
     * ÚLTIMO módulo (por orden de código) cuando ese lote termina de cubrir TODOS los
     * módulos que le quedaban pendientes a la inscripción. Así la boleta/recibo de cada
     * módulo pagado en el lote suma exacto al costo total del programa, sin importar si
     * los costos cargados por módulo no cerraban exacto por redondeo.
     *
     * Si el lote no cubre todos los pendientes (quedará algo por pagar después), no se
     * toca nada: se devuelven los costos tal como se enviaron.
     *
     * @param int $programaID
     * @param int $idinscripcion
     * @param array $costosModulos [IdModulo => costo] tal como llega del formulario
     * @return array [IdModulo => costo] posiblemente con el último ajustado
     */
    public static function AjustarUltimoModuloDelPagoAlTotalModelo($programaID, $idinscripcion, $costosModulos)
    {
        if (empty($costosModulos)) {
            return $costosModulos;
        }

        try {
            $pdo = Conexion::Conectar();

            // Módulos activos del programa, en el mismo orden que se muestran en las tarjetas.
            // PDO devuelve las columnas como string por defecto: se castean a int para comparar bien.
            $stmtModulos = $pdo->prepare(
                "SELECT Idmodulo FROM modulos
                 WHERE ProgramaId = :programaID AND estadomodulo = 'ACTIVO'
                 ORDER BY codigomodulo ASC"
            );
            $stmtModulos->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmtModulos->execute();
            $todosLosModulos = array_map('intval', array_column($stmtModulos->fetchAll(PDO::FETCH_ASSOC), 'Idmodulo'));

            if (empty($todosLosModulos)) {
                return $costosModulos;
            }

            // Módulos ya pagados (no anulados) de esta inscripción
            $stmtPagados = $pdo->prepare(
                "SELECT IdModulo, costomodulo FROM pagomodulo
                 WHERE idinscripcion = :idinscripcion AND Estado != 'ANULADO'"
            );
            $stmtPagados->bindParam(":idinscripcion", $idinscripcion, PDO::PARAM_INT);
            $stmtPagados->execute();
            $pagados = $stmtPagados->fetchAll(PDO::FETCH_ASSOC);

            $idsPagados = array_map('intval', array_column($pagados, 'IdModulo'));
            $sumaYaPagada = array_sum(array_map('floatval', array_column($pagados, 'costomodulo')));

            $pendientesAntes = array_values(array_diff($todosLosModulos, $idsPagados));
            $idsEsteLote = array_map('intval', array_keys($costosModulos));

            // Comparar como conjuntos: este lote debe cubrir EXACTAMENTE todo lo pendiente
            sort($pendientesAntes);
            $idsEsteLoteOrdenado = $idsEsteLote;
            sort($idsEsteLoteOrdenado);

            if ($pendientesAntes !== $idsEsteLoteOrdenado) {
                // Este pago no cierra el programa (queda algo pendiente para después): no se ajusta
                return $costosModulos;
            }

            // Obtener el costo total del programa
            $stmtPrograma = $pdo->prepare("SELECT Costo FROM programa WHERE ProgramaID = :programaID");
            $stmtPrograma->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmtPrograma->execute();
            $programa = $stmtPrograma->fetch(PDO::FETCH_ASSOC);

            if (!$programa || $programa['Costo'] === null) {
                return $costosModulos;
            }

            $costoTotalPrograma = floatval($programa['Costo']);
            $montoRestanteTotal = round($costoTotalPrograma - $sumaYaPagada, 2);
            if ($montoRestanteTotal < 0) {
                $montoRestanteTotal = 0.0;
            }

            // El "último" módulo del lote es el de mayor orden (codigomodulo) entre los que se están pagando ahora
            $ultimoModuloID = null;
            foreach ($todosLosModulos as $idModulo) {
                if (in_array($idModulo, $idsEsteLote, true)) {
                    $ultimoModuloID = $idModulo;
                }
            }

            if ($ultimoModuloID === null) {
                return $costosModulos;
            }

            // Sumar el resto del lote (todos menos el último) para calcular cuánto le toca al último
            $sumaRestoDelLote = 0.0;
            foreach ($costosModulos as $idModulo => $costo) {
                if ((int)$idModulo !== $ultimoModuloID) {
                    $sumaRestoDelLote += floatval($costo);
                }
            }

            $montoUltimo = round($montoRestanteTotal - $sumaRestoDelLote, 2);
            if ($montoUltimo < 0) {
                $montoUltimo = 0.0;
            }

            $costosModulos[$ultimoModuloID] = $montoUltimo;

            return $costosModulos;
        } catch (PDOException $e) {
            error_log("Error en AjustarUltimoModuloDelPagoAlTotalModelo: " . $e->getMessage());
            return $costosModulos;
        }
    }

    /**
     * Obtener módulos disponibles de un programa (sin estado de pago)
     * @param int $programaID
     * @return array
     */
    public static function ObtenerModulosPorProgramaModelo($programaID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    m.Idmodulo as ModuloID,
                    m.nombremodulo as NombreModulo,
                    m.codigomodulo as Codigo,
                    m.costomodulo as Costo,
                    m.estadomodulo as Estado,
                    CONCAT(d.Nombre, ' ', d.Apaterno, ' ', d.Amaterno) as NombreDocente
                FROM modulos m
                LEFT JOIN docente d ON m.DocenteID = d.DocenteID
                WHERE m.ProgramaId = :programaID
                AND m.estadomodulo = 'ACTIVO'
                ORDER BY m.codigomodulo ASC"
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
     * Registrar pago de módulo
     * Ahora usa IdModulo como FK a la tabla modulos
     * @param array $datos - debe incluir: idinscripcion, IdModulo, costomodulo, fechapago, nvaucher, fmodulo
     * @return string
     */
    public static function RegistrarPagoModuloModelo($datos)
    {
        try {
            $pdo = Conexion::Conectar();
            $pdo->beginTransaction();

            // Verificar si ya existe un pago para este módulo en esta inscripción
            $stmtCheck = $pdo->prepare(
                "SELECT Idpagomodulo FROM pagomodulo
                 WHERE idinscripcion = :idinscripcion
                 AND IdModulo = :idModulo
                 AND Estado != 'ANULADO'"
            );
            $stmtCheck->bindParam(":idinscripcion", $datos['idinscripcion'], PDO::PARAM_INT);
            $stmtCheck->bindParam(":idModulo", $datos['IdModulo'], PDO::PARAM_INT);
            $stmtCheck->execute();

            if ($stmtCheck->fetch()) {
                $pdo->rollBack();
                return "duplicado";
            }

            // Preparar el archivo (si existe)
            $fmodulo = null;
            if (!empty($datos['fmodulo'])) {
                $fmodulo = $datos['fmodulo'];
            }

            // Insertar el pago del módulo
            $stmt = $pdo->prepare(
                "INSERT INTO pagomodulo
                (idinscripcion, IdModulo, costomodulo, fechapago, nvaucher, fmodulo, Estado)
                VALUES (:idinscripcion, :idModulo, :costomodulo, :fechapago, :nvaucher, :fmodulo, 'PAGADO')"
            );

            $stmt->bindParam(":idinscripcion", $datos['idinscripcion'], PDO::PARAM_INT);
            $stmt->bindParam(":idModulo", $datos['IdModulo'], PDO::PARAM_INT);
            $stmt->bindParam(":costomodulo", $datos['costomodulo'], PDO::PARAM_STR);
            $stmt->bindParam(":fechapago", $datos['fechapago'], PDO::PARAM_STR);
            $stmt->bindParam(":nvaucher", $datos['nvaucher'], PDO::PARAM_STR);
            $stmt->bindParam(":fmodulo", $fmodulo, PDO::PARAM_LOB);

            if ($stmt->execute()) {
                $pdo->commit();
                return "exitoso";
            } else {
                $pdo->rollBack();
                error_log("Error al ejecutar INSERT en pagomodulo: " . print_r($stmt->errorInfo(), true));
                return "error";
            }

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en RegistrarPagoModuloModelo: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Obtener pagos de módulos por inscripción
     * @param int $idinscripcion
     * @return array
     */
    public static function ObtenerPagosModulosPorInscripcionModelo($idinscripcion)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    pm.Idpagomodulo,
                    pm.idinscripcion,
                    pm.IdModulo,
                    m.nombremodulo as nmodulo,
                    m.codigomodulo,
                    pm.costomodulo,
                    pm.fechapago,
                    pm.nvaucher,
                    pm.Estado,
                    pm.FechaRegistro
                FROM pagomodulo pm
                LEFT JOIN modulos m ON pm.IdModulo = m.Idmodulo
                WHERE pm.idinscripcion = :idinscripcion
                AND pm.Estado != 'ANULADO'
                ORDER BY pm.fechapago DESC"
            );
            $stmt->bindParam(":idinscripcion", $idinscripcion, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerPagosModulosPorInscripcionModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener pagos de módulos por estudiante
     * @param int $estudianteID
     * @return array
     */
    public static function ObtenerPagosModulosPorEstudianteModelo($estudianteID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    pm.Idpagomodulo,
                    pm.IdModulo,
                    m.nombremodulo as nmodulo,
                    m.codigomodulo,
                    pm.costomodulo,
                    pm.fechapago,
                    pm.nvaucher,
                    pm.Estado,
                    pm.FechaRegistro,
                    ep.ProgramaID,
                    p.NombrePrograma,
                    p.GradoAcademico
                FROM pagomodulo pm
                LEFT JOIN modulos m ON pm.IdModulo = m.Idmodulo
                INNER JOIN estudianteprograma ep ON pm.idinscripcion = ep.idInscripcion
                INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
                WHERE ep.EstudianteID = :estudianteID
                AND pm.Estado != 'ANULADO'
                ORDER BY pm.fechapago DESC"
            );
            $stmt->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerPagosModulosPorEstudianteModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener archivo/foto de voucher
     * @param int $idmodulo
     * @return mixed
     */
    public static function ObtenerArchivoVoucherModelo($idmodulo)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT fmodulo FROM pagomodulo WHERE Idmodulo = :idmodulo"
            );
            $stmt->bindParam(":idmodulo", $idmodulo, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['fmodulo'] : null;
        } catch (PDOException $e) {
            error_log("Error en ObtenerArchivoVoucherModelo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Anular pago de módulo
     * @param int $idmodulo
     * @return bool
     */
    public static function AnularPagoModuloModelo($idmodulo)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "UPDATE pagomodulo SET Estado = 'ANULADO' WHERE Idmodulo = :idmodulo"
            );
            $stmt->bindParam(":idmodulo", $idmodulo, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en AnularPagoModuloModelo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener programas inscritos del estudiante con ID de inscripción
     * @param int $estudianteID
     * @return array
     */
    public static function ObtenerProgramasEstudianteConInscripcionModelo($estudianteID)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    ep.idInscripcion,
                    ep.ProgramaID,
                    p.NombrePrograma,
                    p.GradoAcademico,
                    p.CostoMatricula,
                    ep.FechaInscripcion,
                    ep.Estado as EstadoInscripcion
                FROM estudianteprograma ep
                INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
                WHERE ep.EstudianteID = :estudianteID
                AND ep.Estado = 'ACTIVO'
                ORDER BY ep.FechaInscripcion DESC"
            );
            $stmt->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ObtenerProgramasEstudianteConInscripcionModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener detalle de módulos con estado de pago para el estudiante
     * @param int $estudianteID
     * @param int $programaID
     * @return array con módulos pagados y pendientes
     */
    public static function ObtenerDetalleModulosEstudianteModelo($estudianteID, $programaID)
    {
        try {
            // Primero obtener el ID de inscripción del estudiante en el programa
            $stmtInsc = Conexion::Conectar()->prepare(
                "SELECT idInscripcion FROM estudianteprograma
                 WHERE EstudianteID = :estudianteID
                 AND ProgramaID = :programaID
                 AND Estado = 'ACTIVO'
                 LIMIT 1"
            );
            $stmtInsc->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
            $stmtInsc->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmtInsc->execute();
            $inscripcion = $stmtInsc->fetch(PDO::FETCH_ASSOC);

            if (!$inscripcion) {
                return [
                    'modulosPagados' => [],
                    'modulosPendientes' => [],
                    'resumen' => [
                        'totalModulos' => 0,
                        'modulosPagados' => 0,
                        'modulosPendientes' => 0,
                        'costoTotal' => 0,
                        'montoPagado' => 0,
                        'montoPendiente' => 0
                    ]
                ];
            }

            $idinscripcion = $inscripcion['idInscripcion'];

            // Obtener todos los módulos con estado de pago
            $modulos = self::ObtenerModulosConEstadoPagoModelo($programaID, $idinscripcion);

            $modulosPagados = [];
            $modulosPendientes = [];
            $totalCosto = 0;
            $totalPagado = 0;

            foreach ($modulos as $modulo) {
                $totalCosto += floatval($modulo['Costo']);

                if ($modulo['Pagado'] == 1) {
                    $modulosPagados[] = $modulo;
                    $totalPagado += floatval($modulo['CostoPagado']);
                } else {
                    $modulosPendientes[] = $modulo;
                }
            }

            return [
                'modulosPagados' => $modulosPagados,
                'modulosPendientes' => $modulosPendientes,
                'resumen' => [
                    'totalModulos' => count($modulos),
                    'modulosPagados' => count($modulosPagados),
                    'modulosPendientes' => count($modulosPendientes),
                    'costoTotal' => $totalCosto,
                    'montoPagado' => $totalPagado,
                    'montoPendiente' => $totalCosto - $totalPagado
                ]
            ];

        } catch (PDOException $e) {
            error_log("Error en ObtenerDetalleModulosEstudianteModelo: " . $e->getMessage());
            return [
                'modulosPagados' => [],
                'modulosPendientes' => [],
                'resumen' => [
                    'totalModulos' => 0,
                    'modulosPagados' => 0,
                    'modulosPendientes' => 0,
                    'costoTotal' => 0,
                    'montoPagado' => 0,
                    'montoPendiente' => 0
                ]
            ];
        }
    }
}
