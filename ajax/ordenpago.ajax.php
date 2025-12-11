<?php
/**
 * AJAX para Orden de Pago - Búsqueda de Estudiantes
 */

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../modelos/estudiantes.modelo.php';
require_once __DIR__ . '/../modelos/pagomodulo.modelo.php';

// Configurar cabecera JSON
header('Content-Type: application/json; charset=utf-8');

// Validar sesión
if (!isset($_SESSION['Validar']) || !$_SESSION['Validar']) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Sesión no válida'
    ]);
    exit;
}

// Manejar acciones
if (!isset($_POST['accion'])) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'No se especificó ninguna acción'
    ]);
    exit;
}

$accion = $_POST['accion'];

switch ($accion) {
    case 'cargarEstudiantes':
        cargarEstudiantes();
        break;

    case 'obtenerDatosEstudiante':
        obtenerDatosEstudiante();
        break;

    case 'obtenerProgramasEstudiante':
        obtenerProgramasEstudiante();
        break;

    case 'obtenerModulosPagados':
        obtenerModulosPagados();
        break;

    case 'registrarOrdenPago':
        registrarOrdenPago();
        break;

    default:
        echo json_encode([
            'success' => false,
            'mensaje' => 'Acción no válida'
        ]);
        break;
}

/**
 * Cargar lista de todos los estudiantes
 */
function cargarEstudiantes()
{
    try {
        require_once __DIR__ . '/../modelos/conexion.modelo.php';
        $conexion = Conexion::Conectar();

        $stmt = $conexion->prepare("
            SELECT
                e.EstudianteID,
                e.Ci,
                e.Complemento,
                e.Exp,
                e.Nombre,
                e.Apaterno,
                e.Amaterno
            FROM estudiante e
            WHERE e.Estado = 1
            ORDER BY e.Apaterno, e.Amaterno, e.Nombre
        ");
        $stmt->execute();
        $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'estudiantes' => $estudiantes
        ]);
    } catch (Exception $e) {
        error_log("Error en cargarEstudiantes: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error al cargar estudiantes'
        ]);
    }
}

/**
 * Obtener datos completos del estudiante por ID
 */
function obtenerDatosEstudiante()
{
    if (!isset($_POST['estudianteID']) || empty($_POST['estudianteID'])) {
        echo json_encode([
            'success' => false,
            'mensaje' => 'El ID del estudiante es requerido'
        ]);
        return;
    }

    $estudianteID = (int)$_POST['estudianteID'];

    try {
        require_once __DIR__ . '/../modelos/conexion.modelo.php';
        $conexion = Conexion::Conectar();

        $stmt = $conexion->prepare("
            SELECT
                e.*,
                p.NombreProfesion
            FROM estudiante e
            LEFT JOIN profesion p ON e.IdProfesion = p.IdProfesion
            WHERE e.EstudianteID = :estudianteID
        ");
        $stmt->bindParam(':estudianteID', $estudianteID, PDO::PARAM_INT);
        $stmt->execute();
        $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($estudiante) {
            echo json_encode([
                'success' => true,
                'estudiante' => $estudiante
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'mensaje' => 'No se encontró el estudiante'
            ]);
        }
    } catch (Exception $e) {
        error_log("Error en obtenerDatosEstudiante: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error al obtener datos del estudiante'
        ]);
    }
}

/**
 * Obtener programas inscritos del estudiante
 */
function obtenerProgramasEstudiante()
{
    if (!isset($_POST['estudianteID']) || empty($_POST['estudianteID'])) {
        echo json_encode([
            'success' => false,
            'mensaje' => 'El ID del estudiante es requerido'
        ]);
        return;
    }

    $estudianteID = (int)$_POST['estudianteID'];

    try {
        require_once __DIR__ . '/../modelos/conexion.modelo.php';
        $conexion = Conexion::Conectar();

        $stmt = $conexion->prepare("
            SELECT
                ep.idInscripcion,
                ep.EstudianteID,
                ep.ProgramaID,
                ep.Estado,
                p.NombrePrograma,
                p.Codigo,
                p.GradoAcademico
            FROM estudianteprograma ep
            INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
            WHERE ep.EstudianteID = :estudianteID
            ORDER BY ep.idInscripcion DESC
        ");
        $stmt->bindParam(':estudianteID', $estudianteID, PDO::PARAM_INT);
        $stmt->execute();
        $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'programas' => $programas
        ]);
    } catch (Exception $e) {
        error_log("Error en obtenerProgramasEstudiante: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error al obtener los programas'
        ]);
    }
}

/**
 * Obtener módulos pagados del estudiante
 */
function obtenerModulosPagados()
{
    if (!isset($_POST['estudianteID']) || empty($_POST['estudianteID'])) {
        echo json_encode([
            'success' => false,
            'mensaje' => 'El ID del estudiante es requerido'
        ]);
        return;
    }

    $estudianteID = (int)$_POST['estudianteID'];

    try {
        require_once __DIR__ . '/../modelos/conexion.modelo.php';
        $conexion = Conexion::Conectar();

        $stmt = $conexion->prepare("
            SELECT
                pm.Idpagomodulo,
                pm.IdModulo,
                pm.costomodulo,
                pm.fechapago,
                pm.Estado,
                pm.idinscripcion,
                m.codigomodulo,
                m.nombremodulo,
                p.ProgramaID,
                p.NombrePrograma,
                p.GradoAcademico,
                p.Codigo as CodigoPrograma,
                p.Version,
                p.NumeroTramite,
                (SELECT op.IdOrdenPago
                 FROM ordenpago op
                 WHERE FIND_IN_SET(pm.Idpagomodulo, op.ListaPagosModulo) > 0
                 LIMIT 1) as OrdenPagoID,
                (SELECT op.NumeroOrden
                 FROM ordenpago op
                 WHERE FIND_IN_SET(pm.Idpagomodulo, op.ListaPagosModulo) > 0
                 LIMIT 1) as NumeroOrden,
                (SELECT op.FechaGeneracion
                 FROM ordenpago op
                 WHERE FIND_IN_SET(pm.Idpagomodulo, op.ListaPagosModulo) > 0
                 LIMIT 1) as FechaOrdenGenerada
            FROM pagomodulo pm
            INNER JOIN estudianteprograma ep ON pm.idinscripcion = ep.idInscripcion
            INNER JOIN modulos m ON pm.IdModulo = m.Idmodulo
            INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
            WHERE ep.EstudianteID = :estudianteID
            AND pm.Estado != 'ANULADO'
            ORDER BY p.NombrePrograma, pm.fechapago DESC, pm.Idpagomodulo DESC
        ");
        $stmt->bindParam(':estudianteID', $estudianteID, PDO::PARAM_INT);
        $stmt->execute();
        $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'modulos' => $modulos
        ]);
    } catch (Exception $e) {
        error_log("Error en obtenerModulosPagados: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error al obtener los módulos pagados'
        ]);
    }
}

/**
 * Registrar orden de pago generada
 */
function registrarOrdenPago()
{
    if (!isset($_POST['estudianteID']) || !isset($_POST['idinscripcion']) ||
        !isset($_POST['programaID']) || !isset($_POST['listaPagosModulo']) ||
        !isset($_POST['montoTotal'])) {
        echo json_encode([
            'success' => false,
            'mensaje' => 'Faltan datos requeridos'
        ]);
        return;
    }

    try {
        require_once __DIR__ . '/../modelos/conexion.modelo.php';
        $conexion = Conexion::Conectar();

        $estudianteID = (int)$_POST['estudianteID'];
        $idinscripcion = (int)$_POST['idinscripcion'];
        $programaID = (int)$_POST['programaID'];
        $listaPagosModulo = $_POST['listaPagosModulo']; // Ya viene como string separado por comas
        $montoTotal = (float)$_POST['montoTotal'];
        $responsable = isset($_POST['responsable']) ? $_POST['responsable'] : '';
        $nombreFactura = isset($_POST['nombreFactura']) ? $_POST['nombreFactura'] : '';
        $nitCiFactura = isset($_POST['nitCiFactura']) ? $_POST['nitCiFactura'] : '';

        // Generar número de orden único
        $numeroOrden = 'ORD-' . str_pad($estudianteID, 4, '0', STR_PAD_LEFT) . '-' .
                       str_pad($programaID, 3, '0', STR_PAD_LEFT) . '-' .
                       date('YmdHis');

        // Insertar registro de orden de pago
        $stmt = $conexion->prepare("
            INSERT INTO ordenpago
            (EstudianteID, idinscripcion, ProgramaID, ListaPagosModulo, MontoTotal,
             ResponsableGeneracion, NombreFactura, NitCiFactura, NumeroOrden, FechaGeneracion)
            VALUES
            (:estudianteID, :idinscripcion, :programaID, :listaPagosModulo, :montoTotal,
             :responsable, :nombreFactura, :nitCiFactura, :numeroOrden, NOW())
        ");

        $stmt->bindParam(':estudianteID', $estudianteID, PDO::PARAM_INT);
        $stmt->bindParam(':idinscripcion', $idinscripcion, PDO::PARAM_INT);
        $stmt->bindParam(':programaID', $programaID, PDO::PARAM_INT);
        $stmt->bindParam(':listaPagosModulo', $listaPagosModulo, PDO::PARAM_STR);
        $stmt->bindParam(':montoTotal', $montoTotal);
        $stmt->bindParam(':responsable', $responsable, PDO::PARAM_STR);
        $stmt->bindParam(':nombreFactura', $nombreFactura, PDO::PARAM_STR);
        $stmt->bindParam(':nitCiFactura', $nitCiFactura, PDO::PARAM_STR);
        $stmt->bindParam(':numeroOrden', $numeroOrden, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'mensaje' => 'Orden de pago registrada exitosamente',
                'numeroOrden' => $numeroOrden,
                'idOrdenPago' => $conexion->lastInsertId()
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al registrar la orden de pago'
            ]);
        }

    } catch (Exception $e) {
        error_log("Error en registrarOrdenPago: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error al registrar la orden de pago: ' . $e->getMessage()
        ]);
    }
}
?>
