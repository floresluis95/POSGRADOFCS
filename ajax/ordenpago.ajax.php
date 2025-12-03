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
                m.codigomodulo,
                m.nombremodulo,
                p.NombrePrograma,
                p.GradoAcademico
            FROM pagomodulo pm
            INNER JOIN estudianteprograma ep ON pm.idinscripcion = ep.idInscripcion
            INNER JOIN modulos m ON pm.IdModulo = m.Idmodulo
            INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
            WHERE ep.EstudianteID = :estudianteID
            AND pm.Estado != 'ANULADO'
            ORDER BY pm.fechapago DESC, pm.Idpagomodulo DESC
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
?>
