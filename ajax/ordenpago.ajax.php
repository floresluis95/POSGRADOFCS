<?php
require_once __DIR__ . '/control-solicitud.php'; // corte de internet: sin envíos duplicados
/**
 * AJAX para Orden de Pago - Búsqueda de Estudiantes
 */

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../modelos/estudiantes.modelo.php';
require_once __DIR__ . '/../modelos/universidad.modelo.php';
require_once __DIR__ . '/../modelos/usuario.modelo.php';
require_once __DIR__ . '/../controladores/usuario.controlador.php';
require_once __DIR__ . '/../modelos/modulopagos_core.php';
require_once __DIR__ . '/../modelos/ordenpagoprincipal.modelo.php';
require_once __DIR__ . '/../modelos/planpago.modelo.php';
require_once __DIR__ . '/../modelos/texto-orden-pago.php'; // texto del programa en la orden de matrícula
require_once __DIR__ . '/../modelos/firmante.modelo.php';

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

// Permisos por acción: el estudiante solo ve SUS órdenes (misOrdenesPago) y genera la
// de su siguiente cuota (calcularMiCuota, generarMiCuota); todo lo demás (pre-registro,
// validar vouchers, planes, firmantes...) es del personal.
$tipoSesionOrden = $_SESSION['Tipo'] ?? '';
$permitidoOrden = $tipoSesionOrden === 'EST'
    ? in_array($_POST['accion'] ?? '', ['misOrdenesPago', 'calcularMiCuota', 'generarMiCuota'], true)
    : in_array($tipoSesionOrden, ['ADM', 'SEC', 'AUX'], true);
if (!$permitidoOrden) {
    http_response_code(403);
    echo json_encode(['success' => false, 'mensaje' => 'No tiene permisos para esta acción']);
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

    case 'buscarEstudiantePorCi':
        buscarEstudiantePorCi();
        break;

    case 'registrarProfesionRapida':
        registrarProfesionRapida();
        break;

    case 'registrarUniversidadRapida':
        registrarUniversidadRapida();
        break;

    case 'registrarEstudianteRapido':
        registrarEstudianteRapido();
        break;

    case 'actualizarEstudiante':
        actualizarEstudiante();
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

    case 'obtenerOrdenesModulosPendientes':
        obtenerOrdenesModulosPendientes();
        break;

    case 'obtenerOrdenesVencidas':
        obtenerOrdenesVencidas();
        break;

    case 'registrarOrdenPagoModulosPendientes':
        registrarOrdenPagoModulosPendientes();
        break;

    case 'obtenerOrdenPendienteCuotaPrograma':
        obtenerOrdenPendienteCuotaPrograma();
        break;

    case 'modulosParaCuota':
        modulosParaCuota();
        break;

    case 'registrarOrdenPagoCuotaPrograma':
        registrarOrdenPagoCuotaPrograma();
        break;

    case 'listarPreregistros':
        listarPreregistros();
        break;

    case 'obtenerPreregistro':
        obtenerPreregistro();
        break;

    case 'actualizarPreregistro':
        actualizarPreregistro();
        break;

    case 'anularPreregistro':
        anularPreregistro();
        break;

    case 'marcarVoucherExtraviado':
        marcarVoucherExtraviado();
        break;

    case 'actualizarFacturacion':
        actualizarFacturacion();
        break;

    case 'validarVoucherMatricula':
        validarVoucherMatricula();
        break;

    case 'buscarPreregistrosPendientes':
        buscarPreregistrosPendientes();
        break;

    case 'obtenerPlanPagoPrograma':
        obtenerPlanPagoPrograma();
        break;

    case 'registrarPlanPagoPrograma':
        registrarPlanPagoPrograma();
        break;

    case 'listarGruposPrograma':
        listarGruposPrograma();
        break;

    case 'misOrdenesPago':
        misOrdenesPago();
        break;

    // El estudiante genera la orden de su siguiente cuota (Mis órdenes de pago)
    case 'calcularMiCuota':
        miCuota(false);
        break;

    case 'generarMiCuota':
        miCuota(true);
        break;

    case 'listarOrdenesGeneradas':
        listarOrdenesGeneradas();
        break;

    case 'listarFirmantes':
        listarFirmantes();
        break;

    case 'registrarFirmante':
        registrarFirmante();
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
        require_once file_exists(__DIR__ . '/../modelos/conexion.modelo.php') ? __DIR__ . '/../modelos/conexion.modelo.php' : __DIR__ . '/../core/conexion.modelo.php';
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
        require_once file_exists(__DIR__ . '/../modelos/conexion.modelo.php') ? __DIR__ . '/../modelos/conexion.modelo.php' : __DIR__ . '/../core/conexion.modelo.php';
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
 * Buscar un estudiante por CI exacto (búsqueda manual del preregistro).
 * Si existe, se devuelven sus datos completos para autocompletar el formulario.
 */
function buscarEstudiantePorCi()
{
    if (!isset($_POST['ci']) || trim($_POST['ci']) === '') {
        echo json_encode([
            'success' => false,
            'mensaje' => 'El CI a buscar es requerido'
        ]);
        return;
    }

    $ci = trim($_POST['ci']);

    try {
        $estudiante = EstudiantesModelos::BuscarEstudianteModelo($ci);

        if ($estudiante) {
            echo json_encode([
                'success' => true,
                'encontrado' => true,
                'estudiante' => $estudiante
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'encontrado' => false
            ]);
        }
    } catch (Exception $e) {
        error_log("Error en buscarEstudiantePorCi: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error al buscar el estudiante'
        ]);
    }
}

/**
 * Registrar una profesión nueva desde el botón "+" del select de Profesión en
 * Pre-Registro, cuando la profesión buscada no está en la lista.
 */
/**
 * Nombre para los catálogos de profesión y universidad: sin espacios de más (también
 * los de en medio) y en MAYÚSCULAS, incluidas las letras con tilde (strtoupper no las
 * convierte). Así "odontólogo  general" y "ODONTÓLOGO GENERAL" son el mismo nombre y
 * no se registran dos veces (la base ya compara sin distinguir mayúsculas ni tildes).
 */
function normalizarNombreCatalogo($texto)
{
    $texto = preg_replace('/\s+/u', ' ', trim((string)$texto));
    return htmlspecialchars(mb_strtoupper($texto, 'UTF-8'));
}

function registrarProfesionRapida()
{
    if (!isset($_POST['nombreProfesion']) || trim($_POST['nombreProfesion']) === '') {
        echo json_encode(['success' => false, 'mensaje' => 'El nombre de la profesión es requerido']);
        return;
    }

    $nombre = normalizarNombreCatalogo($_POST['nombreProfesion']);
    if (mb_strlen($nombre, 'UTF-8') > 100) {
        echo json_encode(['success' => false, 'mensaje' => 'El nombre de la profesión no puede tener más de 100 caracteres']);
        return;
    }
    $resultado = ProfesionModelos::RegistrarProfesionModelo($nombre);

    if ($resultado['status'] === 'exitoso' || $resultado['status'] === 'duplicado') {
        echo json_encode([
            'success' => true,
            'duplicado' => $resultado['status'] === 'duplicado',
            'mensaje' => $resultado['mensaje'],
            'profesion' => $resultado['profesion']
        ]);
    } else {
        echo json_encode(['success' => false, 'mensaje' => $resultado['mensaje']]);
    }
}

/**
 * Registrar una universidad nueva desde el botón "+" del select de Universidad
 * de Egreso (Estudiantes y Pre-Registro), cuando la universidad buscada no
 * está en la lista. El tipo (pública/privada/extranjera/convenio) es obligatorio.
 */
function registrarUniversidadRapida()
{
    if (!isset($_POST['nombreUniversidad']) || trim($_POST['nombreUniversidad']) === '') {
        echo json_encode(['success' => false, 'mensaje' => 'El nombre de la universidad es requerido']);
        return;
    }
    if (!isset($_POST['tipoUniversidad']) || trim($_POST['tipoUniversidad']) === '') {
        echo json_encode(['success' => false, 'mensaje' => 'Seleccione el tipo de universidad']);
        return;
    }

    $nombre = normalizarNombreCatalogo($_POST['nombreUniversidad']);
    if (mb_strlen($nombre, 'UTF-8') > 150) {
        echo json_encode(['success' => false, 'mensaje' => 'El nombre de la universidad no puede tener más de 150 caracteres']);
        return;
    }
    $tipo = strtoupper(htmlspecialchars(trim($_POST['tipoUniversidad'])));
    $resultado = UniversidadModelos::RegistrarUniversidadModelo($nombre, $tipo);

    if ($resultado['status'] === 'exitoso' || $resultado['status'] === 'duplicado') {
        echo json_encode([
            'success' => true,
            'duplicado' => $resultado['status'] === 'duplicado',
            'mensaje' => $resultado['mensaje'],
            'universidad' => $resultado['universidad']
        ]);
    } else {
        echo json_encode(['success' => false, 'mensaje' => $resultado['mensaje']]);
    }
}

/**
 * Registrar un estudiante nuevo directamente desde el formulario de preregistro
 * (cuando la búsqueda manual por CI no encontró datos existentes).
 */
function registrarEstudianteRapido()
{
    $camposRequeridos = ['Ci', 'Exp', 'Nombre', 'Apaterno', 'FechaNacimiento', 'Edad', 'Lugarn', 'Correo', 'IdProfesion', 'Celular'];
    foreach ($camposRequeridos as $campo) {
        if (!isset($_POST[$campo]) || trim((string)$_POST[$campo]) === '') {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Faltan datos obligatorios del estudiante'
            ]);
            return;
        }
    }

    if (!is_numeric($_POST['IdProfesion'])) {
        echo json_encode([
            'success' => false,
            'mensaje' => 'Seleccione una profesión válida'
        ]);
        return;
    }

    $errorEgreso = EstudiantesModelos::ValidarDatosEgresoModelo($_POST['IdUniversidad'] ?? '', $_POST['FechaEgreso'] ?? '', $_POST['FechaNacimiento']);
    if ($errorEgreso !== null) {
        echo json_encode(['success' => false, 'mensaje' => $errorEgreso]);
        return;
    }

    try {
        $DatosEstudiante = array(
            "Ci"              => htmlspecialchars(trim($_POST['Ci'])),
            "Complemento"     => mb_strtoupper(htmlspecialchars(trim($_POST['Complemento'] ?? '')), 'UTF-8'),
            "Exp"             => mb_strtoupper(htmlspecialchars(trim($_POST['Exp'])), 'UTF-8'),
            "Nombre"          => mb_strtoupper(htmlspecialchars(trim($_POST['Nombre'])), 'UTF-8'),
            "Apaterno"        => mb_strtoupper(htmlspecialchars(trim($_POST['Apaterno'])), 'UTF-8'),
            "Amaterno"        => mb_strtoupper(htmlspecialchars(trim($_POST['Amaterno'] ?? '')), 'UTF-8'),
            "FechaNacimiento" => htmlspecialchars(trim($_POST['FechaNacimiento'])),
            "Edad"            => (int)$_POST['Edad'],
            "Lugarn"          => mb_strtoupper(htmlspecialchars(trim($_POST['Lugarn'])), 'UTF-8'),
            "Correo"          => strtolower(htmlspecialchars(trim($_POST['Correo']))),
            "IdProfesion"     => (int)$_POST['IdProfesion'],
            "Trabajo"         => mb_strtoupper(htmlspecialchars(trim($_POST['Trabajo'] ?? '')), 'UTF-8'),
            "Direccion"       => mb_strtoupper(htmlspecialchars(trim($_POST['Direccion'] ?? '')), 'UTF-8'),
            "Telefono"        => htmlspecialchars(trim($_POST['Telefono'] ?? '')),
            "Celular"         => htmlspecialchars(trim($_POST['Celular'])),
            // Universidad y fecha de egreso: obligatorias (ya validadas arriba)
            "IdUniversidad"   => (int)$_POST['IdUniversidad'],
            "FechaEgreso"     => trim($_POST['FechaEgreso']),
        );

        $existe = EstudiantesModelos::BuscarEstudianteModelo($DatosEstudiante['Ci']);
        if ($existe) {
            echo json_encode([
                'success' => true,
                'estudianteID' => (int)$existe['EstudianteID'],
                'mensaje' => 'El estudiante ya estaba registrado, se usaron sus datos existentes'
            ]);
            return;
        }

        $existeProf = ProfesionModelos::ObtenerPorId($DatosEstudiante['IdProfesion']);
        if (!$existeProf) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Profesión no válida (no existe)'
            ]);
            return;
        }

        // Si el C.I. o el correo ya los tiene otro estudiante, el modelo devuelve el motivo
        $resultado = EstudiantesModelos::RegistrarEstudianteModelo($DatosEstudiante);

        if ($resultado === 'exitoso') {
            $nuevo = EstudiantesModelos::BuscarEstudianteModelo($DatosEstudiante['Ci']);
            echo json_encode([
                'success' => true,
                'estudianteID' => (int)$nuevo['EstudianteID'],
                'mensaje' => 'Estudiante registrado exitosamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'mensaje' => $resultado
            ]);
        }
    } catch (Exception $e) {
        error_log("Error en registrarEstudianteRapido: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error al registrar el estudiante'
        ]);
    }
}

/**
 * Actualizar los datos de un estudiante ya existente (módulo Estudiantes,
 * botón "Editar"). No permite cambiar el Ci: es el identificador con el que
 * se lo busca/relaciona en el resto del sistema (matrícula, pagos, etc.).
 */
function actualizarEstudiante()
{
    if (!isset($_POST['EstudianteID']) || !is_numeric($_POST['EstudianteID'])) {
        echo json_encode(['success' => false, 'mensaje' => 'El ID del estudiante es requerido']);
        return;
    }

    $camposRequeridos = ['Exp', 'Nombre', 'Apaterno', 'FechaNacimiento', 'Edad', 'Lugarn', 'Correo', 'IdProfesion', 'Celular'];
    foreach ($camposRequeridos as $campo) {
        if (!isset($_POST[$campo]) || trim((string)$_POST[$campo]) === '') {
            echo json_encode(['success' => false, 'mensaje' => 'Faltan datos obligatorios del estudiante']);
            return;
        }
    }

    if (!is_numeric($_POST['IdProfesion'])) {
        echo json_encode(['success' => false, 'mensaje' => 'Seleccione una profesión válida']);
        return;
    }

    $errorEgreso = EstudiantesModelos::ValidarDatosEgresoModelo($_POST['IdUniversidad'] ?? '', $_POST['FechaEgreso'] ?? '', $_POST['FechaNacimiento']);
    if ($errorEgreso !== null) {
        echo json_encode(['success' => false, 'mensaje' => $errorEgreso]);
        return;
    }

    $EstudianteID = (int)$_POST['EstudianteID'];

    try {
        $existeProf = ProfesionModelos::ObtenerPorId((int)$_POST['IdProfesion']);
        if (!$existeProf) {
            echo json_encode(['success' => false, 'mensaje' => 'Profesión no válida (no existe)']);
            return;
        }

        $DatosEstudiante = array(
            "Complemento"     => mb_strtoupper(htmlspecialchars(trim($_POST['Complemento'] ?? '')), 'UTF-8'),
            "Exp"             => mb_strtoupper(htmlspecialchars(trim($_POST['Exp'])), 'UTF-8'),
            "Nombre"          => mb_strtoupper(htmlspecialchars(trim($_POST['Nombre'])), 'UTF-8'),
            "Apaterno"        => mb_strtoupper(htmlspecialchars(trim($_POST['Apaterno'])), 'UTF-8'),
            "Amaterno"        => mb_strtoupper(htmlspecialchars(trim($_POST['Amaterno'] ?? '')), 'UTF-8'),
            "FechaNacimiento" => htmlspecialchars(trim($_POST['FechaNacimiento'])),
            "Edad"            => (int)$_POST['Edad'],
            "Lugarn"          => mb_strtoupper(htmlspecialchars(trim($_POST['Lugarn'])), 'UTF-8'),
            "Correo"          => strtolower(htmlspecialchars(trim($_POST['Correo']))),
            "IdProfesion"     => (int)$_POST['IdProfesion'],
            "Trabajo"         => mb_strtoupper(htmlspecialchars(trim($_POST['Trabajo'] ?? '')), 'UTF-8'),
            "Direccion"       => mb_strtoupper(htmlspecialchars(trim($_POST['Direccion'] ?? '')), 'UTF-8'),
            "Telefono"        => htmlspecialchars(trim($_POST['Telefono'] ?? '')),
            "Celular"         => htmlspecialchars(trim($_POST['Celular'])),
            // Universidad y fecha de egreso: obligatorias (ya validadas arriba)
            "IdUniversidad"   => (int)$_POST['IdUniversidad'],
            "FechaEgreso"     => trim($_POST['FechaEgreso']),
        );

        $resultado = EstudiantesModelos::ActualizarEstudianteModelo($EstudianteID, $DatosEstudiante);

        if ($resultado === 'exitoso') {
            echo json_encode(['success' => true, 'mensaje' => 'Datos del estudiante actualizados correctamente']);
        } else {
            echo json_encode(['success' => false, 'mensaje' => $resultado]);
        }
    } catch (Exception $e) {
        error_log("Error en actualizarEstudiante: " . $e->getMessage());
        echo json_encode(['success' => false, 'mensaje' => 'Error al actualizar el estudiante']);
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
        require_once file_exists(__DIR__ . '/../modelos/conexion.modelo.php') ? __DIR__ . '/../modelos/conexion.modelo.php' : __DIR__ . '/../core/conexion.modelo.php';
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
        require_once file_exists(__DIR__ . '/../modelos/conexion.modelo.php') ? __DIR__ . '/../modelos/conexion.modelo.php' : __DIR__ . '/../core/conexion.modelo.php';
        $conexion = Conexion::Conectar();

        $stmt = $conexion->prepare("
            SELECT
                pm.Idpagomodulo,
                pm.IdModulo,
                pm.NumeroCuota,
                pm.montocuota,
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
            LEFT JOIN modulos m ON pm.IdModulo = m.Idmodulo
            INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
            WHERE ep.EstudianteID = :estudianteID
            AND pm.Estado != 'ANULADO'
            ORDER BY p.NombrePrograma, pm.fechapago DESC, pm.Idpagomodulo DESC
        ");
        $stmt->bindParam(':estudianteID', $estudianteID, PDO::PARAM_INT);
        $stmt->execute();
        $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Las cuotas del programa (rediseño de pagos) ya no están ligadas a un módulo
        // específico: pm.IdModulo queda NULL. Se rellena un nombre/código de
        // referencia para que la tabla no muestre celdas vacías.
        foreach ($modulos as &$fila) {
            if (empty($fila['IdModulo'])) {
                $fila['codigomodulo'] = 'CUOTA';
                $fila['nombremodulo'] = 'Cuota' . (!empty($fila['NumeroCuota']) ? ' N° ' . $fila['NumeroCuota'] : '') . ' del programa';
            }
        }
        unset($fila);

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
 * Estudiantes con una orden de pago de cuota vencida (mora), con enlace de
 * WhatsApp directo al celular registrado. Usado en el aviso al ingresar al
 * sistema (Administrador/Secretaría).
 */
function obtenerOrdenesVencidas()
{
    // Información de cobranza (nombres, celulares, montos): solo Administrador y Secretaría.
    if (!in_array($_SESSION['Tipo'] ?? '', ['ADM', 'SEC', 'AUX'], true)) {
        echo json_encode(['success' => false, 'mensaje' => 'No tiene permiso para ver esta información']);
        return;
    }

    try {
        $ordenes = OrdenPagoModelos::ObtenerOrdenesVencidasModelo();

        $resultado = array_map(function ($o) {
            $nombreCompleto = trim($o['Nombre'] . ' ' . $o['Apaterno'] . ' ' . ($o['Amaterno'] ?? ''));
            $diasVencido = (int)floor((strtotime('today') - strtotime($o['FechaVencimiento'])) / 86400);

            $celular = preg_replace('/\D/', '', $o['Celular'] ?? '');
            $whatsapp = null;
            if (!empty($celular)) {
                $mensaje = "Hola {$o['Nombre']}, le escribimos de la Coordinación de Posgrado FCS-UTO. " .
                    "Su cuota del programa \"{$o['NombrePrograma']}\" (orden {$o['NumeroOrden']}, Bs. " .
                    number_format((float)$o['MontoTotal'], 2) . ") venció el " .
                    date('d/m/Y', strtotime($o['FechaVencimiento'])) . ". Le agradecemos regularizar su pago.";
                $whatsapp = 'https://wa.me/591' . $celular . '?text=' . rawurlencode($mensaje);
            }

            return [
                'idOrdenPago' => $o['IdOrdenPago'],
                'numeroOrden' => $o['NumeroOrden'],
                'nombreEstudiante' => $nombreCompleto,
                'ci' => $o['Ci'],
                'nombrePrograma' => $o['NombrePrograma'],
                'sede' => $o['Sede'],
                'monto' => (float)$o['MontoTotal'],
                'fechaVencimiento' => $o['FechaVencimiento'],
                'diasVencido' => max($diasVencido, 0),
                'whatsapp' => $whatsapp
            ];
        }, $ordenes);

        echo json_encode(['success' => true, 'ordenes' => $resultado]);
    } catch (Exception $e) {
        error_log("Error en obtenerOrdenesVencidas: " . $e->getMessage());
        echo json_encode(['success' => false, 'mensaje' => 'No se pudo obtener el listado de mora']);
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
        require_once file_exists(__DIR__ . '/../modelos/conexion.modelo.php') ? __DIR__ . '/../modelos/conexion.modelo.php' : __DIR__ . '/../core/conexion.modelo.php';
        $conexion = Conexion::Conectar();

        $estudianteID = (int)$_POST['estudianteID'];
        $idinscripcion = (int)$_POST['idinscripcion'];
        $programaID = (int)$_POST['programaID'];
        $listaPagosModulo = $_POST['listaPagosModulo']; // Ya viene como string separado por comas
        $montoTotal = (float)$_POST['montoTotal'];
        $responsable = OrdenPagoModelos::ResponsableSesion() ?? '';
        $nombreFactura = isset($_POST['nombreFactura']) ? $_POST['nombreFactura'] : '';
        $nitCiFactura = isset($_POST['nitCiFactura']) ? $_POST['nitCiFactura'] : '';

        // La orden nace CONFIRMADA: no puede ser sin pagos de módulo ni en Bs. 0 o menos
        if (trim($listaPagosModulo) === '' || $montoTotal <= 0) {
            echo json_encode(['success' => false, 'mensaje' => 'La orden debe incluir al menos un pago de módulo y un monto mayor a 0']);
            return;
        }

        // Generar número de orden único
        $numeroOrden = 'ORD-' . str_pad($estudianteID, 4, '0', STR_PAD_LEFT) . '-' .
                       str_pad($programaID, 3, '0', STR_PAD_LEFT) . '-' .
                       date('YmdHis');

        // Insertar registro de orden de pago
        // Estado se marca como CONFIRMADO porque este comprobante se emite sobre modulos
        // que ya fueron pagados (pagomodulo); si quedara en PENDIENTE (valor por defecto de
        // la columna) apareceria mezclado con los preregistros de matricula aun sin pagar
        // en la lista de "Preregistros Pendientes" de la vista Orden de Pago.
        $stmt = $conexion->prepare("
            INSERT INTO ordenpago
            (EstudianteID, idinscripcion, ProgramaID, ListaPagosModulo, MontoTotal,
             ResponsableGeneracion, NombreFactura, NitCiFactura, NumeroOrden, FechaGeneracion, Estado)
            VALUES
            (:estudianteID, :idinscripcion, :programaID, :listaPagosModulo, :montoTotal,
             :responsable, :nombreFactura, :nitCiFactura, :numeroOrden, NOW(), 'CONFIRMADO')
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
            'mensaje' => 'Error al registrar la orden de pago'
        ]);
    }
}

/**
 * Ordenes de pago de modulos PENDIENTES (generadas antes del pago) de una inscripcion.
 * Se usan para saber que modulos ya tienen una orden emitida (y no ofrecer generar otra)
 * y para poder reimprimir la orden.
 */
function obtenerOrdenesModulosPendientes()
{
    if (!isset($_POST['idinscripcion']) || empty($_POST['idinscripcion'])) {
        echo json_encode(['success' => false, 'mensaje' => 'El ID de inscripción es requerido']);
        return;
    }

    $idinscripcion = (int)$_POST['idinscripcion'];

    try {
        require_once file_exists(__DIR__ . '/../modelos/conexion.modelo.php') ? __DIR__ . '/../modelos/conexion.modelo.php' : __DIR__ . '/../core/conexion.modelo.php';
        $conexion = Conexion::Conectar();

        $stmt = $conexion->prepare("
            SELECT IdOrdenPago, NumeroOrden, ListaModulos, MontoTotal, FechaGeneracion,
                   Observaciones, NombreFactura, NitCiFactura
            FROM ordenpago
            WHERE idinscripcion = :idinscripcion
            AND Estado = 'PENDIENTE'
            AND ListaModulos IS NOT NULL
            ORDER BY FechaGeneracion DESC
        ");
        $stmt->bindParam(':idinscripcion', $idinscripcion, PDO::PARAM_INT);
        $stmt->execute();
        $ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'ordenes' => $ordenes]);
    } catch (Exception $e) {
        error_log("Error en obtenerOrdenesModulosPendientes: " . $e->getMessage());
        echo json_encode(['success' => false, 'mensaje' => 'Error al obtener las órdenes de pago pendientes']);
    }
}

/**
 * Orden de cuota PENDIENTE que toca validar: la de N° de cuota menor (si el
 * estudiante adelantó varias, primero la 10, luego la 11). Trae su N° de cuota y
 * los módulos que cubre; 'siguientes' son las demás pendientes, en orden.
 */
function obtenerOrdenPendienteCuotaPrograma()
{
    if (empty($_POST['idinscripcion'])) {
        echo json_encode(['success' => false, 'mensaje' => 'El ID de inscripción es requerido']);
        return;
    }

    $idinscripcion = (int)$_POST['idinscripcion'];

    try {
        require_once file_exists(__DIR__ . '/../modelos/conexion.modelo.php') ? __DIR__ . '/../modelos/conexion.modelo.php' : __DIR__ . '/../core/conexion.modelo.php';
        $conexion = Conexion::Conectar();

        $stmt = $conexion->prepare("
            SELECT op.IdOrdenPago, op.NumeroOrden, op.MontoTotal, op.FechaGeneracion, op.FechaVencimiento, op.NombreFactura, op.NitCiFactura, op.Firma,
                (SELECT GROUP_CONCAT(m.codigomodulo ORDER BY m.Idmodulo SEPARATOR ', ')
                 FROM ordenpagomodulo opm INNER JOIN modulos m ON m.Idmodulo = opm.IdModulo
                 WHERE opm.IdOrdenPago = op.IdOrdenPago) AS ModulosCubiertos,
                COALESCE(
                    (SELECT pc.NumeroCuenta FROM programacuenta pc WHERE pc.ProgramaCuentaID = op.ProgramaCuentaID),
                    (SELECT pc.NumeroCuenta FROM programacuenta pc
                     WHERE pc.ProgramaID = op.ProgramaID AND pc.TipoCuenta = 'CUOTAS' AND pc.Principal = 1 AND pc.Estado = 1
                     LIMIT 1)
                ) AS NumeroCuentaDeposito
            FROM ordenpago op
            WHERE op.idinscripcion = :idinscripcion
            AND op.Estado = 'PENDIENTE'
            AND op.ListaModulos IS NULL AND COALESCE(op.Observaciones, '') <> 'CLINICA'
            ORDER BY op.IdOrdenPago ASC
        ");
        $stmt->bindParam(':idinscripcion', $idinscripcion, PDO::PARAM_INT);
        $stmt->execute();
        $pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($pendientes as &$p) {
            $p['NumeroCuota'] = PagoModuloModelo::NumeroCuotaDeOrdenModelo($conexion, $p['IdOrdenPago']);
        }
        unset($p);

        echo json_encode([
            'success' => true,
            'orden' => $pendientes[0] ?? null,
            'siguientes' => array_map(function ($p) {
                return ['NumeroOrden' => $p['NumeroOrden'], 'NumeroCuota' => $p['NumeroCuota'], 'ModulosCubiertos' => $p['ModulosCubiertos']];
            }, array_slice($pendientes, 1)),
        ]);
    } catch (Exception $e) {
        error_log("Error en obtenerOrdenPendienteCuotaPrograma: " . $e->getMessage());
        echo json_encode(['success' => false, 'mensaje' => 'Error al obtener la orden de pago pendiente']);
    }
}

/**
 * Módulos del programa con la orden de cuota vigente que cubre a cada uno (si
 * hay), para elegir a qué módulo(s) corresponde la cuota que se va a generar.
 */
function modulosParaCuota()
{
    $idinscripcion = (int)($_POST['idinscripcion'] ?? 0);
    $programaID = (int)($_POST['programaID'] ?? 0);
    if (!$idinscripcion || !$programaID) {
        echo json_encode(['success' => false, 'mensaje' => 'Faltan datos de la inscripción']);
        return;
    }

    try {
        require_once file_exists(__DIR__ . '/../modelos/conexion.modelo.php') ? __DIR__ . '/../modelos/conexion.modelo.php' : __DIR__ . '/../core/conexion.modelo.php';
        $stmt = Conexion::Conectar()->prepare(
            "SELECT m.Idmodulo, m.codigomodulo AS Codigo, m.nombremodulo AS Nombre,
                    op.NumeroOrden, op.Estado AS EstadoOrden
             FROM modulos m
             LEFT JOIN ordenpagomodulo opm ON opm.IdModulo = m.Idmodulo
                AND opm.IdOrdenPago IN (SELECT IdOrdenPago FROM ordenpago
                                        WHERE idinscripcion = :idinscripcion AND Estado <> 'ANULADO')
             LEFT JOIN ordenpago op ON op.IdOrdenPago = opm.IdOrdenPago
             WHERE m.ProgramaId = :programaID AND m.estadomodulo = 'ACTIVO'
             ORDER BY m.Idmodulo"
        );
        $stmt->execute([':idinscripcion' => $idinscripcion, ':programaID' => $programaID]);
        echo json_encode(['success' => true, 'modulos' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) {
        error_log("Error en modulosParaCuota: " . $e->getMessage());
        echo json_encode(['success' => false, 'mensaje' => 'No se pudieron cargar los módulos']);
    }
}

/**
 * Registrar una orden de pago de una cuota del programa (Estado PENDIENTE), sin
 * ligarla a módulos. El estudiante presenta esta orden para pagar; el pago en sí
 * se registra después en "Pago de Módulos" (con su voucher), lo que confirma esta orden.
 */
function registrarOrdenPagoCuotaPrograma()
{
    if (!isset($_POST['estudianteID']) || !isset($_POST['idinscripcion']) ||
        !isset($_POST['programaID']) || !isset($_POST['montoTotal'])) {
        echo json_encode(['success' => false, 'mensaje' => 'Faltan datos requeridos']);
        return;
    }

    try {
        require_once file_exists(__DIR__ . '/../modelos/conexion.modelo.php') ? __DIR__ . '/../modelos/conexion.modelo.php' : __DIR__ . '/../core/conexion.modelo.php';
        $conexion = Conexion::Conectar();

        $estudianteID = (int)$_POST['estudianteID'];
        $idinscripcion = (int)$_POST['idinscripcion'];
        $programaID = (int)$_POST['programaID'];
        $montoTotal = (float)$_POST['montoTotal'];
        // El responsable de generar la orden es siempre el usuario que inició sesión
        // (no un campo editable desde el formulario): igual que en Pre-Registro.
        $responsable = OrdenPagoModelos::ResponsableSesion();
        // Nombre del responsable de la firma (coordinador/a), elegido del catálogo de
        // firmantes: es el que se imprime en la orden, junto a la línea de firma.
        $firma = isset($_POST['firma']) ? htmlspecialchars(trim($_POST['firma'])) : '';
        $nombreFactura = isset($_POST['nombreFactura']) ? $_POST['nombreFactura'] : '';
        $nitCiFactura = isset($_POST['nitCiFactura']) ? $_POST['nitCiFactura'] : '';
        $fechaVencimiento = !empty($_POST['fechaVencimiento']) ? trim($_POST['fechaVencimiento']) : null;
        $programaCuentaID = !empty($_POST['programaCuentaID']) ? (int)$_POST['programaCuentaID'] : null;
        // Fecha de la orden: hoy, salvo que se marque "Modificar fecha" (nunca futura)
        $fechaGeneracion = OrdenPagoModelos::FechaGeneracionDesdeFormulario($_POST['fechaGeneracion'] ?? '');

        if ($montoTotal <= 0) {
            echo json_encode(['success' => false, 'mensaje' => 'El monto de la cuota debe ser mayor a 0']);
            return;
        }
        if ($fechaGeneracion === false) {
            echo json_encode(['success' => false, 'mensaje' => 'La fecha de la orden no es válida o es posterior a hoy']);
            return;
        }

        // La cuota no puede superar lo que falta pagar del programa (mismo saldo que
        // muestra Gestión de Pagos: costo con el descuento del plan menos lo PAGADO)
        // Lo que está en órdenes con voucher extraviado ya se pagó: no se vuelve a pedir
        $resumen = PagoModuloModelo::ObtenerResumenPagoProgramaModelo($idinscripcion, $programaID);
        if ($montoTotal - $resumen['disponibleOrden'] > 0.01) {
            echo json_encode(['success' => false, 'mensaje' => $resumen['montoEnEspera'] > 0
                ? sprintf(
                    'El monto de la cuota (Bs. %.2f) supera lo que falta pedir (Bs. %.2f): saldo Bs. %.2f menos Bs. %.2f en órdenes con voucher por validar',
                    $montoTotal, $resumen['disponibleOrden'], $resumen['saldoPendiente'], $resumen['montoEnEspera']
                )
                : sprintf(
                    'El monto de la cuota (Bs. %.2f) supera el saldo pendiente del programa (Bs. %.2f)',
                    $montoTotal, $resumen['saldoPendiente']
                )]);
            return;
        }

        // Validar que la cuenta elegida (a la que el estudiante debe depositar) exista,
        // esté activa y pertenezca efectivamente a este programa (tipo CUOTAS).
        if ($programaCuentaID) {
            $stmtCuenta = $conexion->prepare(
                "SELECT ProgramaCuentaID FROM programacuenta
                 WHERE ProgramaCuentaID = :id AND ProgramaID = :programaID
                 AND TipoCuenta = 'CUOTAS' AND Estado = 1"
            );
            $stmtCuenta->bindParam(":id", $programaCuentaID, PDO::PARAM_INT);
            $stmtCuenta->bindParam(":programaID", $programaID, PDO::PARAM_INT);
            $stmtCuenta->execute();

            if (!$stmtCuenta->fetch()) {
                echo json_encode(['success' => false, 'mensaje' => 'La cuenta seleccionada no es válida para este programa']);
                return;
            }
        }

        // Módulo(s) a los que corresponde esta cuota: SOLO para control de los
        // módulos del estudiante (opcional). No cambia la orden (monto, PDF).
        // Deben ser módulos activos del programa y no estar ya cubiertos por otra
        // orden vigente (las anuladas no cuentan).
        $modulosCuota = array_values(array_unique(array_filter(array_map('intval', (array)($_POST['modulos'] ?? [])))));
        if ($modulosCuota) {
            $marcadores = implode(',', array_fill(0, count($modulosCuota), '?'));
            $stmtMod = $conexion->prepare(
                "SELECT m.Idmodulo,
                        (SELECT op2.NumeroOrden FROM ordenpagomodulo opm2
                         INNER JOIN ordenpago op2 ON op2.IdOrdenPago = opm2.IdOrdenPago
                         WHERE opm2.IdModulo = m.Idmodulo AND op2.idinscripcion = ? AND op2.Estado <> 'ANULADO'
                         LIMIT 1) AS YaCubiertoPor
                 FROM modulos m
                 WHERE m.ProgramaId = ? AND m.estadomodulo = 'ACTIVO' AND m.Idmodulo IN ($marcadores)"
            );
            $stmtMod->execute(array_merge([$idinscripcion, $programaID], $modulosCuota));
            $validos = $stmtMod->fetchAll(PDO::FETCH_ASSOC);
            if (count($validos) !== count($modulosCuota)) {
                echo json_encode(['success' => false, 'mensaje' => 'Alguno de los módulos elegidos no pertenece a este programa']);
                return;
            }
            foreach ($validos as $v) {
                if (!empty($v['YaCubiertoPor'])) {
                    echo json_encode(['success' => false, 'mensaje' => 'Uno de los módulos ya está cubierto por la orden ' . $v['YaCubiertoPor'] . '. Recargue la pestaña e intente de nuevo']);
                    return;
                }
            }
        }

        // Solo una orden de cuota pendiente a la vez por inscripción
        $stmtCheck = $conexion->prepare(
            "SELECT IdOrdenPago FROM ordenpago
             WHERE idinscripcion = :idinscripcion AND Estado = 'PENDIENTE' AND ListaModulos IS NULL AND COALESCE(Observaciones, '') <> 'CLINICA'"
        );
        $stmtCheck->bindParam(':idinscripcion', $idinscripcion, PDO::PARAM_INT);
        $stmtCheck->execute();
        if ($stmtCheck->fetch()) {
            echo json_encode(['success' => false, 'mensaje' => 'Ya existe una orden de pago pendiente para esta inscripción. Valide o anule esa orden antes de generar otra']);
            return;
        }

        $numeroOrden = OrdenPagoModelos::NumeroOrdenLibre($conexion,
            'ORD-' . str_pad($estudianteID, 4, '0', STR_PAD_LEFT) . '-' .
            str_pad($programaID, 3, '0', STR_PAD_LEFT) . '-' .
            date('YmdHis'));

        $stmt = $conexion->prepare("
            INSERT INTO ordenpago
            (EstudianteID, idinscripcion, ProgramaID, ProgramaCuentaID, MontoTotal, FechaVencimiento,
             ResponsableGeneracion, Firma, NombreFactura, NitCiFactura, NumeroOrden, FechaGeneracion, Observaciones, Estado)
            VALUES
            (:estudianteID, :idinscripcion, :programaID, :programaCuentaID, :montoTotal, :fechaVencimiento,
             :responsable, :firma, :nombreFactura, :nitCiFactura, :numeroOrden, :fechaGeneracion, 'CUOTA PROGRAMA', 'PENDIENTE')
        ");
        $stmt->bindParam(':fechaGeneracion', $fechaGeneracion, PDO::PARAM_STR);

        $stmt->bindParam(':estudianteID', $estudianteID, PDO::PARAM_INT);
        $stmt->bindParam(':idinscripcion', $idinscripcion, PDO::PARAM_INT);
        $stmt->bindParam(':programaID', $programaID, PDO::PARAM_INT);
        if ($programaCuentaID) {
            $stmt->bindParam(':programaCuentaID', $programaCuentaID, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':programaCuentaID', null, PDO::PARAM_NULL);
        }
        $stmt->bindParam(':montoTotal', $montoTotal);
        if ($fechaVencimiento !== null) {
            $stmt->bindParam(':fechaVencimiento', $fechaVencimiento, PDO::PARAM_STR);
        } else {
            $stmt->bindValue(':fechaVencimiento', null, PDO::PARAM_NULL);
        }
        if ($responsable !== null) {
            $stmt->bindParam(':responsable', $responsable, PDO::PARAM_STR);
        } else {
            $stmt->bindValue(':responsable', null, PDO::PARAM_NULL);
        }
        $stmt->bindParam(':firma', $firma, PDO::PARAM_STR);
        $stmt->bindParam(':nombreFactura', $nombreFactura, PDO::PARAM_STR);
        $stmt->bindParam(':nitCiFactura', $nitCiFactura, PDO::PARAM_STR);
        $stmt->bindParam(':numeroOrden', $numeroOrden, PDO::PARAM_STR);

        // La orden y sus módulos se guardan juntos: si falla una parte no queda la otra
        $conexion->beginTransaction();
        if ($stmt->execute()) {
            $idOrdenPago = (int)$conexion->lastInsertId();
            if ($modulosCuota) {
                $stmtOpm = $conexion->prepare("INSERT INTO ordenpagomodulo (IdOrdenPago, IdModulo) VALUES (:orden, :modulo)");
                foreach ($modulosCuota as $idModulo) {
                    $stmtOpm->execute([':orden' => $idOrdenPago, ':modulo' => $idModulo]);
                }
            }
            $conexion->commit();
            echo json_encode([
                'success' => true,
                'mensaje' => 'Orden de pago registrada exitosamente',
                'numeroOrden' => $numeroOrden,
                'idOrdenPago' => $idOrdenPago
            ]);
        } else {
            $conexion->rollBack();
            echo json_encode(['success' => false, 'mensaje' => 'Error al registrar la orden de pago']);
        }
    } catch (Exception $e) {
        if (isset($conexion) && $conexion->inTransaction()) {
            $conexion->rollBack();
        }
        error_log("Error en registrarOrdenPagoCuotaPrograma: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error al registrar la orden de pago'
        ]);
    }
}

/**
 * Registrar una orden de pago de modulos ANTES de que se paguen (Estado PENDIENTE).
 * El estudiante debe presentar esta orden para pagar; el pago en si se registra
 * despues, por separado, en "Pago de Modulos" (con su voucher).
 * @deprecated Las cuotas del programa ya no se ligan a módulos: usar
 * registrarOrdenPagoCuotaPrograma(). Se mantiene por compatibilidad si quedan
 * órdenes antiguas con ListaModulos.
 */
function registrarOrdenPagoModulosPendientes()
{
    if (!isset($_POST['estudianteID']) || !isset($_POST['idinscripcion']) ||
        !isset($_POST['programaID']) || !isset($_POST['listaModulos']) ||
        !isset($_POST['montoTotal'])) {
        echo json_encode(['success' => false, 'mensaje' => 'Faltan datos requeridos']);
        return;
    }

    try {
        require_once file_exists(__DIR__ . '/../modelos/conexion.modelo.php') ? __DIR__ . '/../modelos/conexion.modelo.php' : __DIR__ . '/../core/conexion.modelo.php';
        $conexion = Conexion::Conectar();

        $estudianteID = (int)$_POST['estudianteID'];
        $idinscripcion = (int)$_POST['idinscripcion'];
        $programaID = (int)$_POST['programaID'];
        $listaModulos = $_POST['listaModulos']; // Idmodulo separados por coma
        $montoTotal = (float)$_POST['montoTotal'];
        $responsable = OrdenPagoModelos::ResponsableSesion() ?? '';
        $nombreFactura = isset($_POST['nombreFactura']) ? $_POST['nombreFactura'] : '';
        $nitCiFactura = isset($_POST['nitCiFactura']) ? $_POST['nitCiFactura'] : '';
        // Detalle (codigo/nombre/costo por modulo) como snapshot para poder imprimir
        // exactamente lo cotizado, aunque luego cambie el costo del modulo o el descuento
        $detalleModulos = isset($_POST['detalleModulos']) ? $_POST['detalleModulos'] : null;

        $numeroOrden = 'ORD-' . str_pad($estudianteID, 4, '0', STR_PAD_LEFT) . '-' .
                       str_pad($programaID, 3, '0', STR_PAD_LEFT) . '-' .
                       date('YmdHis');

        $stmt = $conexion->prepare("
            INSERT INTO ordenpago
            (EstudianteID, idinscripcion, ProgramaID, ListaModulos, MontoTotal,
             ResponsableGeneracion, NombreFactura, NitCiFactura, NumeroOrden, FechaGeneracion, Observaciones, Estado)
            VALUES
            (:estudianteID, :idinscripcion, :programaID, :listaModulos, :montoTotal,
             :responsable, :nombreFactura, :nitCiFactura, :numeroOrden, NOW(), :detalleModulos, 'PENDIENTE')
        ");

        $stmt->bindParam(':estudianteID', $estudianteID, PDO::PARAM_INT);
        $stmt->bindParam(':idinscripcion', $idinscripcion, PDO::PARAM_INT);
        $stmt->bindParam(':programaID', $programaID, PDO::PARAM_INT);
        $stmt->bindParam(':listaModulos', $listaModulos, PDO::PARAM_STR);
        $stmt->bindParam(':montoTotal', $montoTotal);
        $stmt->bindParam(':responsable', $responsable, PDO::PARAM_STR);
        $stmt->bindParam(':nombreFactura', $nombreFactura, PDO::PARAM_STR);
        $stmt->bindParam(':nitCiFactura', $nitCiFactura, PDO::PARAM_STR);
        $stmt->bindParam(':numeroOrden', $numeroOrden, PDO::PARAM_STR);
        $stmt->bindParam(':detalleModulos', $detalleModulos, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'mensaje' => 'Orden de pago registrada exitosamente',
                'numeroOrden' => $numeroOrden,
                'idOrdenPago' => $conexion->lastInsertId()
            ]);
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'Error al registrar la orden de pago']);
        }
    } catch (Exception $e) {
        error_log("Error en registrarOrdenPagoModulosPendientes: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error al registrar la orden de pago'
        ]);
    }
}

/**
 * Listar preregistros (CRUD - Read) para refrescar la tabla sin recargar la página
 */
function listarPreregistros()
{
    try {
        $preregistros = OrdenPagoModelos::ListarPreregistrosModelo();
        echo json_encode([
            'success' => true,
            'preregistros' => $preregistros
        ]);
    } catch (Exception $e) {
        error_log("Error en listarPreregistros: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error al listar los preregistros'
        ]);
    }
}

/**
 * Obtener un preregistro puntual (para precargar el modal de edición / generar la orden de pago)
 */
function obtenerPreregistro()
{
    if (empty($_POST['idOrdenPago'])) {
        echo json_encode(['success' => false, 'mensaje' => 'El ID del preregistro es requerido']);
        return;
    }

    $preregistro = OrdenPagoModelos::ObtenerPreregistroModelo((int)$_POST['idOrdenPago']);

    if ($preregistro) {
        // Campos calculados para imprimir la Orden de Pago (formato oficial UTO)
        $preregistro['CiCompleto'] = trim(
            $preregistro['Ci'] . (!empty($preregistro['Complemento']) ? '-' . $preregistro['Complemento'] : '') .
            ' ' . $preregistro['Exp']
        );
        $preregistro['MontoLiteral'] = numeroALetrasOrdenPago((float)$preregistro['MontoFinal']);
        $preregistro['ProgramaTexto'] = textoProgramaOrdenPago(
            $preregistro['GradoAcademico'], $preregistro['NombrePrograma'], $preregistro['Version'], (int)$preregistro['PagoCompleto'] === 1
        );

        echo json_encode(['success' => true, 'preregistro' => $preregistro]);
    } else {
        echo json_encode(['success' => false, 'mensaje' => 'No se encontró el preregistro (o ya no está pendiente)']);
    }
}

/**
 * Convertir un monto numérico a su representación literal en bolivianos (para la Orden de Pago)
 */
function numeroALetrasOrdenPago($numero)
{
    $entero = (int)floor($numero);
    $decimales = (int)round(($numero - $entero) * 100);

    $unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
    $decenas = ['', '', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
    $especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
    $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

    $convertirEntero = function ($num) use (&$convertirEntero, $unidades, $decenas, $especiales, $centenas) {
        if ($num == 0) return 'CERO';
        if ($num == 100) return 'CIEN';

        $resultado = '';

        if ($num >= 1000000) {
            $millones = (int)floor($num / 1000000);
            $resultado .= ($millones == 1) ? 'UN MILLON ' : $convertirEntero($millones) . ' MILLONES ';
            $num %= 1000000;
        }

        if ($num >= 1000) {
            $miles = (int)floor($num / 1000);
            $resultado .= ($miles == 1) ? 'MIL ' : $convertirEntero($miles) . ' MIL ';
            $num %= 1000;
        }

        if ($num >= 100) {
            // "CIEN" (no "CIENTO") cuando el resto es exactamente 100, es decir,
            // no queda nada más después (decenas/unidades) que agregar.
            if ($num == 100) {
                $resultado .= 'CIEN ';
                $num = 0;
            } else {
                $resultado .= $centenas[(int)floor($num / 100)] . ' ';
                $num %= 100;
            }
        }

        if ($num >= 10 && $num < 20) {
            $resultado .= $especiales[$num - 10] . ' ';
        } elseif ($num >= 20) {
            $resultado .= $decenas[(int)floor($num / 10)];
            if ($num % 10 > 0) $resultado .= ' Y ' . $unidades[$num % 10];
            $resultado .= ' ';
        } elseif ($num > 0) {
            $resultado .= $unidades[$num] . ' ';
        }

        return trim($resultado);
    };

    $letras = $convertirEntero($entero);

    return $decimales > 0
        ? strtoupper($letras . ' CON ' . $decimales . '/100 BOLIVIANOS')
        : strtoupper($letras . ' 00/100 BOLIVIANOS');
}

/**
 * Actualizar (CRUD - Update) monto/descuento/fecha de un preregistro pendiente
 */
function actualizarPreregistro()
{
    if (empty($_POST['idOrdenPago']) || !isset($_POST['montoPagado']) || empty($_POST['fechaInscripcion'])) {
        echo json_encode(['success' => false, 'mensaje' => 'Faltan datos requeridos']);
        return;
    }

    // El tipo de orden (matrícula / programa completo) y el descuento NO se reciben:
    // el modelo los toma de la orden guardada y valida el monto contra ellos.
    $datos = [
        'idOrdenPago'      => (int)$_POST['idOrdenPago'],
        'montoPagado'      => floatval($_POST['montoPagado']),
        'FechaInscripcion' => htmlspecialchars(trim($_POST['fechaInscripcion'])),
    ];

    $resultado = OrdenPagoModelos::ActualizarPreregistroModelo($datos);
    echo json_encode([
        'success' => $resultado['status'] === 'exitoso',
        'mensaje' => $resultado['mensaje']
    ]);
}

/**
 * Anular (CRUD - Delete lógico) un preregistro pendiente
 */
function anularPreregistro()
{
    if (empty($_POST['idOrdenPago'])) {
        echo json_encode(['success' => false, 'mensaje' => 'El ID del preregistro es requerido']);
        return;
    }

    // Matriculados (cuotas y clínicas) envía el motivo y es obligatorio; Pre-Registro no lo envía
    $motivo = null;
    if (isset($_POST['motivo'])) {
        $motivo = trim($_POST['motivo']);
        if (mb_strlen($motivo) < 5) {
            echo json_encode(['success' => false, 'mensaje' => 'Escriba el motivo de la anulación']);
            return;
        }
        $motivo = mb_substr($motivo, 0, 255); // texto tal cual: las pantallas lo escapan al mostrarlo
    }

    $resultado = OrdenPagoModelos::AnularPreregistroModelo((int)$_POST['idOrdenPago'], $motivo, $motivo !== null ? OrdenPagoModelos::ResponsableSesion() : null);
    echo json_encode([
        'success' => $resultado['status'] === 'exitoso',
        'mensaje' => $resultado['mensaje']
    ]);
}

/**
 * Orden de cuota o clínica cuyo voucher el estudiante extravió: pasa a EXTRAVIADO con su
 * motivo (obligatorio). Deja de contar como pendiente, así se puede generar la siguiente;
 * su voucher se registra cuando Tesoro da el código.
 */
function marcarVoucherExtraviado()
{
    $idOrdenPago = (int)($_POST['idOrdenPago'] ?? 0);
    $motivo = trim($_POST['motivo'] ?? '');
    if ($idOrdenPago <= 0) {
        echo json_encode(['success' => false, 'mensaje' => 'La orden es requerida']);
        return;
    }
    if (mb_strlen($motivo) < 5) {
        echo json_encode(['success' => false, 'mensaje' => 'Escriba el motivo (por ejemplo: perdió el voucher, se pidió copia a Tesoro)']);
        return;
    }

    $resultado = OrdenPagoModelos::MarcarVoucherExtraviadoModelo(
        $idOrdenPago,
        mb_substr($motivo, 0, 255), // texto tal cual: las pantallas lo escapan al mostrarlo
        OrdenPagoModelos::ResponsableSesion()
    );
    echo json_encode([
        'success' => $resultado,
        'mensaje' => $resultado
            ? 'La orden quedó con voucher por validar. Ya puede generar la orden siguiente'
            : 'Solo una orden de cuota o clínica pendiente puede marcarse con voucher por validar'
    ]);
}

/**
 * Actualizar los datos de facturación de una orden ya generada (pantalla "orden-generada").
 * El "responsable" (usuario que generó la orden en el sistema) NO se recibe aquí: se
 * captura una sola vez, de la sesión, al crear la orden, y no se vuelve a exponer ni
 * a editar desde el formulario. Lo único editable es la "firma" (el nombre del
 * coordinador/responsable que aparece impreso junto a la línea de firma).
 */
function actualizarFacturacion()
{
    if (empty($_POST['idOrdenPago'])) {
        echo json_encode(['success' => false, 'mensaje' => 'El ID de la orden es requerido']);
        return;
    }

    $datos = [
        'idOrdenPago'  => (int)$_POST['idOrdenPago'],
        'nombreFactura' => isset($_POST['nombreFactura']) ? htmlspecialchars(trim($_POST['nombreFactura'])) : '',
        'nitCiFactura'  => isset($_POST['nitCiFactura']) ? htmlspecialchars(trim($_POST['nitCiFactura'])) : '',
        'firma'         => isset($_POST['firma']) ? htmlspecialchars(trim($_POST['firma'])) : '',
    ];

    $ok = OrdenPagoModelos::ActualizarFacturacionOrdenModelo($datos);
    echo json_encode([
        'success' => $ok,
        'mensaje' => $ok ? 'Datos de facturación actualizados' : 'No se pudieron actualizar los datos de facturación'
    ]);
}

/**
 * Listar el catálogo de firmantes (nombres que pueden aparecer en la línea de firma
 * de las órdenes de pago impresas), para el select del formulario.
 */
function listarFirmantes()
{
    echo json_encode([
        'success' => true,
        'firmantes' => FirmanteModelos::ListarFirmantesModelo()
    ]);
}

/**
 * Agregar un nuevo nombre al catálogo de firmantes.
 */
function registrarFirmante()
{
    if (empty($_POST['nombre'])) {
        echo json_encode(['success' => false, 'mensaje' => 'El nombre es requerido']);
        return;
    }

    // El modelo lo pasa a MAYÚSCULAS y lo escapa
    $resultado = FirmanteModelos::RegistrarFirmanteModelo($_POST['nombre']);
    echo json_encode([
        'success' => $resultado['status'] === 'exitoso',
        'mensaje' => $resultado['mensaje'] ?? '',
        'idFirmante' => $resultado['idFirmante'] ?? null,
        'nombre' => $resultado['nombre'] ?? null
    ]);
}

/**
 * Validar el voucher de la matrícula ya cancelada e inscribir formalmente al estudiante
 * (única escritura en estudianteprograma en todo el flujo de preregistro).
 * Se usa desde Inscripción; acepta opcionalmente una foto del voucher (multipart/form-data).
 */
function validarVoucherMatricula()
{
    // Si se pide o no el voucher lo decide la orden (matrícula exonerada en Pre-Registro =
    // sin voucher); el modelo lo valida con la orden ya bloqueada en la transacción.
    if (empty($_POST['idOrdenPago'])) {
        echo json_encode(['success' => false, 'mensaje' => 'El ID de la orden es requerido']);
        return;
    }

    $numeroVoucher = !empty($_POST['numeroVoucher']) ? htmlspecialchars(trim($_POST['numeroVoucher'])) : '';
    $fechaInscripcion = !empty($_POST['fechaInscripcion']) ? htmlspecialchars(trim($_POST['fechaInscripcion'])) : null;

    $fotoVoucher = null;
    if (isset($_FILES['fotoVoucher']) && $_FILES['fotoVoucher']['error'] === UPLOAD_ERR_OK) {
        $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $tipoArchivo = $_FILES['fotoVoucher']['type'];
        $tamanioMaximo = 5 * 1024 * 1024; // 5MB

        if (!in_array($tipoArchivo, $tiposPermitidos)) {
            echo json_encode(['success' => false, 'mensaje' => 'Formato de imagen no permitido. Solo JPG, PNG, GIF o WEBP']);
            return;
        }
        if ($_FILES['fotoVoucher']['size'] > $tamanioMaximo) {
            echo json_encode(['success' => false, 'mensaje' => 'La imagen es muy grande. Máximo 5MB']);
            return;
        }

        $fotoVoucher = file_get_contents($_FILES['fotoVoucher']['tmp_name']);
    }

    // Plan de pago del programa (opcional), definido antes de validar el voucher.
    // Viene como JSON en el campo 'planPago' (mismo POST, multipart/form-data).
    // El pago en si se registra por modulo desde "Registrar Pago de Modulo"; aqui
    // solo se guarda el tipo de plan y su % de descuento.
    $planPago = null;
    if (!empty($_POST['planPago'])) {
        $planDecodificado = json_decode($_POST['planPago'], true);
        if (is_array($planDecodificado) && !empty($planDecodificado['TipoPlan'])) {
            $planPago = [
                'TipoPlan' => htmlspecialchars(trim($planDecodificado['TipoPlan'])),
                'CostoTotalPrograma' => floatval($planDecodificado['CostoTotalPrograma'] ?? 0),
                'PorcentajeDescuento' => floatval($planDecodificado['PorcentajeDescuento'] ?? 0),
                'CodigoGrupo' => !empty($planDecodificado['CodigoGrupo']) ? htmlspecialchars(trim($planDecodificado['CodigoGrupo'])) : null,
                'CantidadInscritosGrupo' => (int)($planDecodificado['CantidadInscritosGrupo'] ?? 1),
                'ResponsableGeneracion' => OrdenPagoModelos::ResponsableSesion(),
            ];
        }
    }

    $resultado = OrdenPagoModelos::ValidarVoucherMatriculaModelo(
        (int)$_POST['idOrdenPago'],
        $numeroVoucher,
        $fechaInscripcion,
        $fotoVoucher,
        $planPago
    );

    // Requisitos de matriculación que el estudiante entregó al inscribirse
    // (requisitos[IdRequisito][entregado|fecha]). Se guardan aparte: si fallan,
    // la inscripción ya hecha no se revierte, solo se avisa.
    $requisitos = null;
    if ($resultado['status'] === 'exitoso' && !empty($resultado['idInscripcion'])) {
        require_once __DIR__ . '/../controladores/requisito.controlador.php';
        if (!empty($_POST['requisitos'])) {
            $requisitos = RequisitoControlador::GuardarControlador(
                $resultado['idInscripcion'],
                $_POST['requisitos'],
                RequisitoControlador::UsuarioSesion()
            );
        }
        // Ficha de inscripción, Documento de compromiso y demás requisitos
        // automáticos: quedan entregados siempre al validar el voucher.
        RequisitoControlador::MarcarAutomaticosControlador(
            $resultado['idInscripcion'],
            $fechaInscripcion,
            RequisitoControlador::UsuarioSesion()
        );
    }

    echo json_encode([
        'success' => $resultado['status'] === 'exitoso',
        'mensaje' => $resultado['mensaje'],
        'estudianteID' => $resultado['estudianteID'] ?? null,
        'programaID' => $resultado['programaID'] ?? null,
        'requisitos' => $requisitos
    ]);
}

/**
 * Obtener el plan de pago del programa (y sus cuotas) de una inscripción, si existe.
 * Se usa desde Matriculados para mostrar el estado de cuotas y generar órdenes de pago.
 */
function obtenerPlanPagoPrograma()
{
    if (empty($_POST['idInscripcion'])) {
        echo json_encode(['success' => false, 'mensaje' => 'El ID de inscripción es requerido']);
        return;
    }

    $plan = PagoProgramaModelos::ObtenerPlanPorInscripcionModelo((int)$_POST['idInscripcion']);

    echo json_encode([
        'success' => true,
        'tienePlan' => $plan !== null,
        'plan' => $plan
    ]);
}

/**
 * Registrar el plan de pago del programa cuando se define DESPUÉS de la matrícula
 * (desde Matriculados), en vez de en el mismo momento de validar el voucher.
 * El pago en si se hace por modulo desde "Registrar Pago de Modulo"; aqui solo
 * se define el tipo de plan y su % de descuento.
 */
function registrarPlanPagoPrograma()
{
    if (empty($_POST['idInscripcion']) || empty($_POST['estudianteID']) ||
        empty($_POST['programaID']) || empty($_POST['costoTotalPrograma'])) {
        echo json_encode(['success' => false, 'mensaje' => 'Faltan datos requeridos del plan de pago']);
        return;
    }

    $datos = [
        'idInscripcion' => (int)$_POST['idInscripcion'],
        'EstudianteID' => (int)$_POST['estudianteID'],
        'ProgramaID' => (int)$_POST['programaID'],
        'CostoTotalPrograma' => floatval($_POST['costoTotalPrograma']),
        'TipoPlan' => htmlspecialchars(trim($_POST['tipoPlan'] ?? 'REGULAR')),
        'NumeroCuotas' => !empty($_POST['numeroCuotas']) ? (int)$_POST['numeroCuotas'] : null,
        'PorcentajeDescuento' => isset($_POST['porcentajeDescuento']) ? floatval($_POST['porcentajeDescuento']) : 0,
        'CodigoGrupo' => !empty($_POST['codigoGrupo']) ? htmlspecialchars(trim($_POST['codigoGrupo'])) : null,
        'CantidadInscritosGrupo' => isset($_POST['cantidadInscritosGrupo']) ? (int)$_POST['cantidadInscritosGrupo'] : 1,
        'ResponsableGeneracion' => OrdenPagoModelos::ResponsableSesion(),
    ];

    $resultado = PagoProgramaModelos::RegistrarPlanModelo($datos);

    echo json_encode([
        'success' => $resultado['status'] === 'exitoso',
        'mensaje' => $resultado['mensaje']
    ]);
}

/**
 * Plan Grupal: grupos ya armados de un programa (Pre-Registro elige a cuál se suma el estudiante)
 */
function listarGruposPrograma()
{
    $programaID = !empty($_POST['programaID']) ? (int)$_POST['programaID'] : 0;
    echo json_encode(['success' => true, 'grupos' => OrdenPagoModelos::ListarGruposProgramaModelo($programaID)]);
}

/**
 * Buscar preregistros pendientes por CI, nombre o apellidos (usado en Inscripción
 * para localizar al estudiante cuyo voucher de matrícula se va a validar).
 */
function buscarPreregistrosPendientes()
{
    $termino = isset($_POST['termino']) ? trim($_POST['termino']) : '';

    if ($termino === '' || mb_strlen($termino) < 2) {
        echo json_encode(['success' => false, 'mensaje' => 'Ingrese al menos 2 caracteres para buscar']);
        return;
    }

    $resultados = OrdenPagoModelos::BuscarPreregistrosPendientesModelo($termino);
    echo json_encode(['success' => true, 'resultados' => $resultados]);
}

/**
 * Todas las órdenes de pago del estudiante logueado (matrícula/cuota del
 * programa y pago de módulos), para que él mismo las consulte e imprima
 * la que le corresponda. Usa exclusivamente el EstudianteID de la sesión:
 * un estudiante nunca puede pedir las órdenes de otro.
 */
function misOrdenesPago()
{
    if (($_SESSION['Tipo'] ?? '') !== 'EST' || empty($_SESSION['EstudianteID'])) {
        echo json_encode(['success' => false, 'mensaje' => 'No hay un estudiante asociado a esta sesión']);
        return;
    }

    $estudianteID = (int)$_SESSION['EstudianteID'];
    $ordenes = OrdenPagoModelos::ListarOrdenesPorEstudianteModelo($estudianteID);

    echo json_encode(['success' => true, 'ordenes' => array_map('datosOrdenParaLista', $ordenes)]);
}

/**
 * El estudiante genera la orden de su siguiente cuota, o de varias para pagar por
 * adelantado ($_POST['cantidad']): una orden por cuota. Solo elige cuántas; el monto
 * (la cuota de su plan; la última, lo que falta), N° de cuota, cuenta de cuotas del
 * programa, módulo que cubre y fecha límite los pone el sistema. Solo si tiene plan,
 * saldo y ninguna orden de cuota sin pagar. Con $generar = false solo calcula (para
 * mostrarle el resumen antes de confirmar). Las órdenes quedan marcadas como generadas
 * por el estudiante: al imprimirlas llevan la firma del coordinador y el sello.
 */
function miCuota($generar)
{
    $estudianteID = (int)($_SESSION['EstudianteID'] ?? 0);
    $idInscripcion = (int)($_POST['idinscripcion'] ?? 0);
    $responder = function ($exito, $mensaje, $extra = []) {
        echo json_encode(['success' => $exito, 'mensaje' => $mensaje] + $extra);
    };

    try {
        $pdo = Conexion::Conectar();
        $stmt = $pdo->prepare(
            "SELECT ep.idInscripcion, ep.ProgramaID, p.NombrePrograma, e.Nombre, e.Apaterno, e.Amaterno, e.Ci, e.Complemento
             FROM estudianteprograma ep
             INNER JOIN programa p ON p.ProgramaID = ep.ProgramaID
             INNER JOIN estudiante e ON e.EstudianteID = ep.EstudianteID
             WHERE ep.idInscripcion = :id AND ep.EstudianteID = :est AND ep.Estado = 'ACTIVO'"
        );
        $stmt->execute([':id' => $idInscripcion, ':est' => $estudianteID]);
        $insc = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$insc) {
            $responder(false, 'No se encontró su inscripción');
            return;
        }
        $programaID = (int)$insc['ProgramaID'];

        // Una sola orden de cuota sin pagar a la vez (pendiente o vencida)
        $stmt = $pdo->prepare(
            "SELECT NumeroOrden FROM ordenpago WHERE idinscripcion = :id AND Observaciones = 'CUOTA PROGRAMA'
             AND Estado IN ('PENDIENTE', 'VENCIDO') LIMIT 1"
        );
        $stmt->execute([':id' => $idInscripcion]);
        if ($pendiente = $stmt->fetchColumn()) {
            $responder(false, 'Ya tiene una orden de cuota por pagar (' . $pendiente . '). Pague esa primero o imprímala de nuevo.');
            return;
        }

        $resumen = PagoModuloModelo::ObtenerResumenPagoProgramaModelo($idInscripcion, $programaID);
        if (!$resumen['tienePlan']) {
            $responder(false, 'Su programa todavía no tiene plan de pago. Consulte en Secretaría de Posgrado.');
            return;
        }
        $disponible = round((float)$resumen['disponibleOrden'], 2);
        if ($resumen['completado'] || $disponible <= 0) {
            $responder(false, $resumen['montoEnEspera'] > 0
                ? 'No tiene cuotas por generar: lo que falta ya está en órdenes con voucher por validar.'
                : 'Su programa ya está pagado en su totalidad.');
            return;
        }

        // Cuotas que puede adelantar: las que faltan según su plan (sin plan de N° de
        // cuotas, una). Una orden por cuota, con N° correlativo.
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM ordenpago WHERE idinscripcion = :id AND Observaciones = 'CUOTA PROGRAMA' AND Estado <> 'ANULADO'");
        $stmt->execute([':id' => $idInscripcion]);
        $primeraCuota = (int)$stmt->fetchColumn() + 1;
        $totalCuotas = (int)($resumen['numeroCuotasPlan'] ?? 0);
        $maxCantidad = $totalCuotas > 0 ? max(1, $totalCuotas - $primeraCuota + 1) : 1;
        $cantidad = min($maxCantidad, max(1, (int)($_POST['cantidad'] ?? 1)));

        // Monto de cada una: la cuota del plan; la última (o si ya no alcanza), lo que falta
        $montoCuota = (float)($resumen['montoCuotaPlan'] ?? 0);
        if ($montoCuota <= 0) {
            $montoCuota = round($disponible / $maxCantidad, 2);
        }
        // Todas las que puede adelantar (la pantalla muestra el detalle y el total según
        // la cantidad elegida); se generan las primeras $cantidad
        $posibles = [];
        $queda = $disponible;
        for ($i = 0; $i < $maxCantidad && $queda > 0.004; $i++) {
            $numero = $primeraCuota + $i;
            $monto = ($totalCuotas > 0 && $numero >= $totalCuotas) ? $queda : min($montoCuota, $queda);
            $monto = round($monto, 2);
            $posibles[] = ['numeroCuota' => $numero, 'monto' => $monto];
            $queda = round($queda - $monto, 2);
        }
        // Lo que alcanza el saldo puede ser menos que lo pedido
        $maxCantidad = max(1, count($posibles));
        $cuotas = array_slice($posibles, 0, min($cantidad, $maxCantidad));

        // Cuenta de cuotas del programa (la principal, si hay varias)
        $stmt = $pdo->prepare("SELECT ProgramaCuentaID FROM programacuenta WHERE ProgramaID = :p AND TipoCuenta = 'CUOTAS' AND Estado = 1
                               ORDER BY Principal DESC, ProgramaCuentaID LIMIT 1");
        $stmt->execute([':p' => $programaID]);
        $programaCuentaID = $stmt->fetchColumn() ?: null;

        // Módulos que cubren: los primeros del programa que aún no tienen orden vigente
        // (uno por cuota, en orden)
        $stmt = $pdo->prepare(
            "SELECT m.Idmodulo FROM modulos m
             WHERE m.ProgramaId = :p AND m.estadomodulo = 'ACTIVO'
             AND NOT EXISTS (SELECT 1 FROM ordenpagomodulo opm INNER JOIN ordenpago op ON op.IdOrdenPago = opm.IdOrdenPago
                             WHERE opm.IdModulo = m.Idmodulo AND op.idinscripcion = :id AND op.Estado <> 'ANULADO')
             ORDER BY m.Idmodulo"
        );
        $stmt->execute([':p' => $programaID, ':id' => $idInscripcion]);
        $modulosLibres = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $fechaVencimiento = date('Y-m-d', strtotime('+5 days'));
        $calculo = [
            'cuotas' => $cuotas,
            'posibles' => $posibles,
            'total' => round(array_sum(array_column($cuotas, 'monto')), 2),
            'maxCantidad' => $maxCantidad,
            'totalCuotas' => $totalCuotas ?: null,
            'programa' => $insc['NombrePrograma'],
            'fechaVencimiento' => $fechaVencimiento,
            'saldo' => (float)$resumen['saldoPendiente'],
            // la primera (lo que muestra el recuadro "Su siguiente cuota")
            'numeroCuota' => $cuotas[0]['numeroCuota'],
            'monto' => $cuotas[0]['monto'],
        ];
        if (!$generar) {
            $responder(true, '', ['cuota' => $calculo]);
            return;
        }

        // Nombre que acompaña la firma: el coordinador vigente (pantalla Autoridades)
        require_once __DIR__ . '/../modelos/autoridad.modelo.php';
        $autoridades = AutoridadModelo::ListarModelo();
        $firma = !empty($autoridades['COORDINADOR']['Nombre']) ? mb_strtoupper(trim($autoridades['COORDINADOR']['Nombre']), 'UTF-8') : '';
        $nombreFactura = trim($insc['Apaterno'] . ' ' . $insc['Amaterno'] . ' ' . $insc['Nombre']);
        $nitCi = $insc['Ci'] . (!empty($insc['Complemento']) ? '-' . $insc['Complemento'] : '');

        $stmtOrden = $pdo->prepare(
            "INSERT INTO ordenpago
             (EstudianteID, idinscripcion, ProgramaID, ProgramaCuentaID, MontoTotal, FechaVencimiento,
              ResponsableGeneracion, GeneradaPorEstudiante, Firma, NombreFactura, NitCiFactura, NumeroOrden, FechaGeneracion, Observaciones, Estado)
             VALUES
             (:est, :id, :p, :cuenta, :monto, :vence, :responsable, 1, :firma, :nombre, :nit, :numero, NOW(), 'CUOTA PROGRAMA', 'PENDIENTE')"
        );
        $stmtModulo = $pdo->prepare("INSERT INTO ordenpagomodulo (IdOrdenPago, IdModulo) VALUES (:o, :m)");
        $generadas = [];

        // Todas las órdenes juntas: si falla una, no queda ninguna
        $pdo->beginTransaction();
        foreach ($cuotas as $i => $cuota) {
            $numeroOrden = OrdenPagoModelos::NumeroOrdenLibre($pdo,
                'ORD-' . str_pad($estudianteID, 4, '0', STR_PAD_LEFT) . '-' . str_pad($programaID, 3, '0', STR_PAD_LEFT) . '-' . date('YmdHis'));
            $stmtOrden->execute([
                ':est' => $estudianteID, ':id' => $idInscripcion, ':p' => $programaID, ':cuenta' => $programaCuentaID,
                ':monto' => $cuota['monto'], ':vence' => $fechaVencimiento, ':responsable' => OrdenPagoModelos::ResponsableSesion(),
                ':firma' => $firma, ':nombre' => $nombreFactura, ':nit' => $nitCi, ':numero' => $numeroOrden,
            ]);
            $idOrdenPago = (int)$pdo->lastInsertId();
            if (!empty($modulosLibres[$i])) {
                $stmtModulo->execute([':o' => $idOrdenPago, ':m' => (int)$modulosLibres[$i]]);
            }
            $generadas[] = ['idOrdenPago' => $idOrdenPago, 'numeroOrden' => $numeroOrden] + $cuota;
        }
        $pdo->commit();

        $responder(true, count($generadas) === 1 ? 'Orden generada' : count($generadas) . ' órdenes generadas', [
            'ordenes' => $generadas,
            // la primera, para quien solo genera una
            'idOrdenPago' => $generadas[0]['idOrdenPago'],
            'numeroOrden' => $generadas[0]['numeroOrden'],
            'cuota' => $calculo,
        ]);
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
        error_log("Error en miCuota: " . $e->getMessage());
        $responder(false, 'No se pudo generar la orden. Intente de nuevo o consulte en Secretaría de Posgrado.');
    }
}

/**
 * Datos de una orden para las listas "Mis órdenes de pago" (estudiante) y
 * "Órdenes de pago" (personal).
 */
function datosOrdenParaLista($o)
{
    $tipo = OrdenPagoModelos::TipoDeOrden($o);
    // Las órdenes de matrícula guardan el monto a pagar en MontoFinal (0 si la
    // matrícula se exoneró); las demás lo guardan en MontoTotal.
    $monto = $tipo === 'MATRICULA'
        ? (float)$o['MontoFinal']
        : (float)($o['MontoFinal'] > 0 ? $o['MontoFinal'] : $o['MontoTotal']);

    return [
        'idOrdenPago' => (int)$o['IdOrdenPago'],
        'numeroOrden' => $o['NumeroOrden'],
        'programaID' => (int)$o['ProgramaID'],
        'nombrePrograma' => $o['NombrePrograma'],
        'codigoPrograma' => $o['CodigoPrograma'],
        'gradoAcademico' => $o['GradoAcademico'],
        'sede' => $o['Sede'],
        'tipo' => $tipo,
        'pagoCompleto' => (int)($o['PagoCompleto'] ?? 0) === 1,
        'monto' => $monto,
        'fechaGeneracion' => $o['FechaGeneracion'],
        'fechaVencimiento' => $o['FechaVencimiento'],
        'fechaConfirmacion' => $o['FechaConfirmacion'] ?? null,
        'estado' => $o['Estado'],
        // Cuota: su N° y el total del plan ("Cuota 3 de 10"); en las dos listas
        'numeroCuota' => isset($o['NumeroCuota']) ? (int)$o['NumeroCuota'] : null,
        'cuotasPlan' => isset($o['CuotasPlan']) ? (int)$o['CuotasPlan'] : null,
        // Voucher extraviado: motivo, cuándo y quién lo marcó
        'motivoExtraviado' => $o['MotivoExtraviado'] ?? null,
        'fechaExtraviado' => $o['FechaExtraviado'] ?? null,
        'responsableExtraviado' => $o['ResponsableExtraviado'] ?? null,
        'vencida' => $o['Estado'] === 'VENCIDO'
            || (!empty($o['FechaVencimiento']) && $o['Estado'] === 'PENDIENTE' && $o['FechaVencimiento'] < date('Y-m-d')),
    ];
}

/**
 * Todas las órdenes de pago generadas, para la pantalla "Órdenes de pago" del
 * personal (ADM, SEC, AUX): control de lo que ya se emitió y reimpresión. El
 * permiso por rol ya se revisó al inicio de este archivo.
 */
function listarOrdenesGeneradas()
{
    $fecha = function ($clave) {
        $valor = trim($_POST[$clave] ?? '');
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor) ? $valor : null;
    };

    $ordenes = OrdenPagoModelos::ListarOrdenesGeneradasModelo($fecha('desde'), $fecha('hasta'));

    $resultado = array_map(function ($o) {
        $fila = datosOrdenParaLista($o);
        $ci = trim($o['Ci'] . ($o['Complemento'] ? '-' . $o['Complemento'] : '') . ' ' . ($o['Exp'] ?? ''));
        $fila['estudiante'] = trim($o['Apaterno'] . ' ' . $o['Amaterno'] . ' ' . $o['Nombre']);
        $fila['ci'] = $ci;
        // C.I. tal como se guarda (sin complemento ni expedición): con este llega
        // el enlace desde Matriculados (ordenespago?ci=...)
        $fila['ciBase'] = trim((string)$o['Ci']);
        $fila['responsable'] = $o['ResponsableGeneracion'];
        // Anulada desde Matriculados: por qué, quién y cuándo (solo para el personal)
        $fila['motivoAnulacion'] = $o['MotivoAnulacion'] ?? null;
        $fila['responsableAnulacion'] = $o['ResponsableAnulacion'] ?? null;
        $fila['fechaAnulacion'] = $o['FechaAnulacion'] ?? null;
        // Para validar el voucher desde esta pantalla (cuotas y clínicas se
        // confirman por inscripción; la cuota exige el monto exacto de la orden)
        $fila['idInscripcion'] = (int)($o['idinscripcion'] ?? 0);
        $fila['montoTotal'] = (float)$o['MontoTotal'];
        return $fila;
    }, $ordenes);

    echo json_encode(['success' => true, 'ordenes' => $resultado]);
}
?>
