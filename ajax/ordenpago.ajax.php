<?php
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

    case 'listarMatriculadosSinPlan':
        listarMatriculadosSinPlan();
        break;

    case 'registrarPlanGrupal':
        registrarPlanGrupal();
        break;

    case 'misOrdenesPago':
        misOrdenesPago();
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
function registrarProfesionRapida()
{
    if (!isset($_POST['nombreProfesion']) || trim($_POST['nombreProfesion']) === '') {
        echo json_encode(['success' => false, 'mensaje' => 'El nombre de la profesión es requerido']);
        return;
    }

    $nombre = strtoupper(htmlspecialchars(trim($_POST['nombreProfesion'])));
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

    $nombre = strtoupper(htmlspecialchars(trim($_POST['nombreUniversidad'])));
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

    try {
        $DatosEstudiante = array(
            "Ci"              => htmlspecialchars(trim($_POST['Ci'])),
            "Complemento"     => strtoupper(htmlspecialchars(trim($_POST['Complemento'] ?? ''))),
            "Exp"             => strtoupper(htmlspecialchars(trim($_POST['Exp']))),
            "Nombre"          => strtoupper(htmlspecialchars(trim($_POST['Nombre']))),
            "Apaterno"        => strtoupper(htmlspecialchars(trim($_POST['Apaterno']))),
            "Amaterno"        => strtoupper(htmlspecialchars(trim($_POST['Amaterno'] ?? ''))),
            "FechaNacimiento" => htmlspecialchars(trim($_POST['FechaNacimiento'])),
            "Edad"            => (int)$_POST['Edad'],
            "Lugarn"          => strtoupper(htmlspecialchars(trim($_POST['Lugarn']))),
            "Correo"          => strtolower(htmlspecialchars(trim($_POST['Correo']))),
            "IdProfesion"     => (int)$_POST['IdProfesion'],
            "Trabajo"         => strtoupper(htmlspecialchars(trim($_POST['Trabajo'] ?? ''))),
            "Direccion"       => strtoupper(htmlspecialchars(trim($_POST['Direccion'] ?? ''))),
            "Telefono"        => htmlspecialchars(trim($_POST['Telefono'] ?? '')),
            "Celular"         => htmlspecialchars(trim($_POST['Celular'])),
            // Universidad de egreso: opcional, no todos los flujos de alta la piden.
            "IdUniversidad"   => (!empty($_POST['IdUniversidad']) && is_numeric($_POST['IdUniversidad'])) ? (int)$_POST['IdUniversidad'] : null,
            "FechaEgreso"     => !empty($_POST['FechaEgreso']) ? htmlspecialchars(trim($_POST['FechaEgreso'])) : null,
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
                'mensaje' => 'No se pudo registrar al estudiante'
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

    $EstudianteID = (int)$_POST['EstudianteID'];

    try {
        $existeProf = ProfesionModelos::ObtenerPorId((int)$_POST['IdProfesion']);
        if (!$existeProf) {
            echo json_encode(['success' => false, 'mensaje' => 'Profesión no válida (no existe)']);
            return;
        }

        $DatosEstudiante = array(
            "Complemento"     => strtoupper(htmlspecialchars(trim($_POST['Complemento'] ?? ''))),
            "Exp"             => strtoupper(htmlspecialchars(trim($_POST['Exp']))),
            "Nombre"          => strtoupper(htmlspecialchars(trim($_POST['Nombre']))),
            "Apaterno"        => strtoupper(htmlspecialchars(trim($_POST['Apaterno']))),
            "Amaterno"        => strtoupper(htmlspecialchars(trim($_POST['Amaterno'] ?? ''))),
            "FechaNacimiento" => htmlspecialchars(trim($_POST['FechaNacimiento'])),
            "Edad"            => (int)$_POST['Edad'],
            "Lugarn"          => strtoupper(htmlspecialchars(trim($_POST['Lugarn']))),
            "Correo"          => strtolower(htmlspecialchars(trim($_POST['Correo']))),
            "IdProfesion"     => (int)$_POST['IdProfesion'],
            "Trabajo"         => strtoupper(htmlspecialchars(trim($_POST['Trabajo'] ?? ''))),
            "Direccion"       => strtoupper(htmlspecialchars(trim($_POST['Direccion'] ?? ''))),
            "Telefono"        => htmlspecialchars(trim($_POST['Telefono'] ?? '')),
            "Celular"         => htmlspecialchars(trim($_POST['Celular'])),
            "IdUniversidad"   => (!empty($_POST['IdUniversidad']) && is_numeric($_POST['IdUniversidad'])) ? (int)$_POST['IdUniversidad'] : null,
            "FechaEgreso"     => !empty($_POST['FechaEgreso']) ? htmlspecialchars(trim($_POST['FechaEgreso'])) : null,
        );

        $resultado = EstudiantesModelos::ActualizarEstudianteModelo($EstudianteID, $DatosEstudiante);

        if ($resultado === 'exitoso') {
            echo json_encode(['success' => true, 'mensaje' => 'Datos del estudiante actualizados correctamente']);
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'No se pudo actualizar al estudiante']);
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
        $responsable = isset($_POST['responsable']) ? $_POST['responsable'] : '';
        $nombreFactura = isset($_POST['nombreFactura']) ? $_POST['nombreFactura'] : '';
        $nitCiFactura = isset($_POST['nitCiFactura']) ? $_POST['nitCiFactura'] : '';

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
            'mensaje' => 'Error al registrar la orden de pago: ' . $e->getMessage()
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
 * Obtener la orden de pago PENDIENTE de una cuota del programa (si existe) para
 * esta inscripción. Las cuotas ya no se ligan a módulos: solo puede haber una
 * orden de cuota pendiente a la vez por inscripción.
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
            SELECT op.IdOrdenPago, op.NumeroOrden, op.MontoTotal, op.FechaGeneracion, op.NombreFactura, op.NitCiFactura, op.Firma,
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
            ORDER BY op.FechaGeneracion DESC
            LIMIT 1
        ");
        $stmt->bindParam(':idinscripcion', $idinscripcion, PDO::PARAM_INT);
        $stmt->execute();
        $orden = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'orden' => $orden ?: null]);
    } catch (Exception $e) {
        error_log("Error en obtenerOrdenPendienteCuotaPrograma: " . $e->getMessage());
        echo json_encode(['success' => false, 'mensaje' => 'Error al obtener la orden de pago pendiente']);
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
        $responsable = isset($_SESSION['Nombre']) && isset($_SESSION['Apellido'])
            ? $_SESSION['Nombre'] . ' ' . $_SESSION['Apellido']
            : null;
        // Nombre del responsable de la firma (coordinador/a), elegido del catálogo de
        // firmantes: es el que se imprime en la orden, junto a la línea de firma.
        $firma = isset($_POST['firma']) ? htmlspecialchars(trim($_POST['firma'])) : '';
        $nombreFactura = isset($_POST['nombreFactura']) ? $_POST['nombreFactura'] : '';
        $nitCiFactura = isset($_POST['nitCiFactura']) ? $_POST['nitCiFactura'] : '';
        $fechaVencimiento = !empty($_POST['fechaVencimiento']) ? trim($_POST['fechaVencimiento']) : null;
        $programaCuentaID = !empty($_POST['programaCuentaID']) ? (int)$_POST['programaCuentaID'] : null;

        if ($montoTotal <= 0) {
            echo json_encode(['success' => false, 'mensaje' => 'El monto de la cuota debe ser mayor a 0']);
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

        $numeroOrden = 'ORD-' . str_pad($estudianteID, 4, '0', STR_PAD_LEFT) . '-' .
                       str_pad($programaID, 3, '0', STR_PAD_LEFT) . '-' .
                       date('YmdHis');

        $stmt = $conexion->prepare("
            INSERT INTO ordenpago
            (EstudianteID, idinscripcion, ProgramaID, ProgramaCuentaID, MontoTotal, FechaVencimiento,
             ResponsableGeneracion, Firma, NombreFactura, NitCiFactura, NumeroOrden, FechaGeneracion, Observaciones, Estado)
            VALUES
            (:estudianteID, :idinscripcion, :programaID, :programaCuentaID, :montoTotal, :fechaVencimiento,
             :responsable, :firma, :nombreFactura, :nitCiFactura, :numeroOrden, NOW(), 'CUOTA PROGRAMA', 'PENDIENTE')
        ");

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
        error_log("Error en registrarOrdenPagoCuotaPrograma: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error al registrar la orden de pago: ' . $e->getMessage()
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
        $responsable = isset($_POST['responsable']) ? $_POST['responsable'] : '';
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
            'mensaje' => 'Error al registrar la orden de pago: ' . $e->getMessage()
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
        // No se imprime la versión "V-1": es la versión base/por defecto y no aporta
        // información distinta al nombre del programa. Solo se muestran versiones
        // reales (V-2, V-3, etc.).
        $mostrarVersion = !empty($preregistro['Version']) && strtoupper($preregistro['Version']) !== 'V-1';
        $preregistro['ProgramaTexto'] = 'PROGRAMA DE POSGRADO EN LA ' . strtoupper($preregistro['GradoAcademico']) .
            ' EN ' . strtoupper($preregistro['NombrePrograma']) .
            ($mostrarVersion ? ' "' . strtoupper($preregistro['Version']) . '"' : '') .
            ' ' . ((int)$preregistro['PagoCompleto'] === 1 ? 'PROGRAMA COMPLETO' : 'MATRICULA');

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
    $especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
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

    $datos = [
        'idOrdenPago'         => (int)$_POST['idOrdenPago'],
        'pagoCompleto'        => isset($_POST['pagoCompleto']) ? (int)$_POST['pagoCompleto'] : 0,
        'montoPagado'         => floatval($_POST['montoPagado']),
        'porcentajeDescuento' => isset($_POST['porcentajeDescuento']) ? floatval($_POST['porcentajeDescuento']) : 0,
        'montoDescuento'      => isset($_POST['montoDescuento']) ? floatval($_POST['montoDescuento']) : 0,
        'FechaInscripcion'    => htmlspecialchars(trim($_POST['fechaInscripcion'])),
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

    $resultado = OrdenPagoModelos::AnularPreregistroModelo((int)$_POST['idOrdenPago']);
    echo json_encode([
        'success' => $resultado['status'] === 'exitoso',
        'mensaje' => $resultado['mensaje']
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

    $resultado = FirmanteModelos::RegistrarFirmanteModelo(htmlspecialchars(trim($_POST['nombre'])));
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
    // "Matricular sin pago de matrícula": no hay voucher real que exigir, el programa
    // completo se cobrará después desde Matriculados.
    $sinMatricula = isset($_POST['sinMatricula']) && $_POST['sinMatricula'] == '1';

    if (empty($_POST['idOrdenPago']) || (!$sinMatricula && empty($_POST['numeroVoucher']))) {
        echo json_encode(['success' => false, 'mensaje' => 'El N° de voucher es requerido']);
        return;
    }

    if (!$sinMatricula && (empty($_FILES['fotoVoucher']) || $_FILES['fotoVoucher']['error'] !== UPLOAD_ERR_OK)) {
        echo json_encode(['success' => false, 'mensaje' => 'La foto del voucher es obligatoria']);
        return;
    }

    $numeroVoucher = !empty($_POST['numeroVoucher']) ? htmlspecialchars(trim($_POST['numeroVoucher'])) : 'SIN MATRÍCULA';
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
                'ResponsableGeneracion' => isset($_SESSION['Nombre']) && isset($_SESSION['Apellido'])
                    ? $_SESSION['Nombre'] . ' ' . $_SESSION['Apellido']
                    : null,
            ];
        }
    }

    $resultado = OrdenPagoModelos::ValidarVoucherMatriculaModelo(
        (int)$_POST['idOrdenPago'],
        $numeroVoucher,
        $fechaInscripcion,
        $fotoVoucher,
        $planPago,
        $sinMatricula
    );

    echo json_encode([
        'success' => $resultado['status'] === 'exitoso',
        'mensaje' => $resultado['mensaje'],
        'estudianteID' => $resultado['estudianteID'] ?? null,
        'programaID' => $resultado['programaID'] ?? null
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
        'ResponsableGeneracion' => isset($_SESSION['Nombre']) && isset($_SESSION['Apellido'])
            ? $_SESSION['Nombre'] . ' ' . $_SESSION['Apellido']
            : null,
    ];

    $resultado = PagoProgramaModelos::RegistrarPlanModelo($datos);

    echo json_encode([
        'success' => $resultado['status'] === 'exitoso',
        'mensaje' => $resultado['mensaje']
    ]);
}

/**
 * Listar estudiantes matriculados sin plan de pago del programa aún (candidatos a un Plan Grupal)
 */
function listarMatriculadosSinPlan()
{
    $programaID = !empty($_POST['programaID']) ? (int)$_POST['programaID'] : null;
    $lista = PagoProgramaModelos::ListarMatriculadosSinPlanModelo($programaID);
    echo json_encode(['success' => true, 'matriculados' => $lista]);
}

/**
 * Registrar un Plan Grupal para varios estudiantes matriculados seleccionados a la vez
 */
function registrarPlanGrupal()
{
    if (empty($_POST['idsInscripcion']) || !isset($_POST['porcentajeDescuento'])) {
        echo json_encode(['success' => false, 'mensaje' => 'Faltan datos requeridos del plan grupal']);
        return;
    }

    $ids = json_decode($_POST['idsInscripcion'], true);
    if (!is_array($ids) || count($ids) < 2) {
        echo json_encode(['success' => false, 'mensaje' => 'Seleccione al menos 2 estudiantes']);
        return;
    }

    $responsable = isset($_SESSION['Nombre']) && isset($_SESSION['Apellido'])
        ? $_SESSION['Nombre'] . ' ' . $_SESSION['Apellido']
        : null;

    $resultado = PagoProgramaModelos::RegistrarPlanGrupalModelo(
        $ids,
        floatval($_POST['porcentajeDescuento']),
        $responsable
    );

    echo json_encode([
        'success' => $resultado['status'] === 'exitoso',
        'mensaje' => $resultado['mensaje']
    ]);
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

    $resultado = array_map(function ($o) {
        $hoy = date('Y-m-d');
        // Tipo de la orden: MODULOS (legado, ligado a ListaModulos), CUOTA (cuota del
        // programa, marcada con Observaciones='CUOTA PROGRAMA') o MATRICULA (el resto,
        // preregistro).
        if (!empty($o['ListaModulos'])) {
            $tipo = 'MODULOS';
        } elseif ($o['Observaciones'] === 'CUOTA PROGRAMA') {
            $tipo = 'CUOTA';
        } elseif ($o['Observaciones'] === 'CLINICA') {
            $tipo = 'CLINICA';
        } else {
            $tipo = 'MATRICULA';
        }

        return [
            'idOrdenPago' => (int)$o['IdOrdenPago'],
            'numeroOrden' => $o['NumeroOrden'],
            'programaID' => (int)$o['ProgramaID'],
            'nombrePrograma' => $o['NombrePrograma'],
            'codigoPrograma' => $o['CodigoPrograma'],
            'gradoAcademico' => $o['GradoAcademico'],
            'sede' => $o['Sede'],
            'tipo' => $tipo,
            // Las órdenes de matrícula/preregistro guardan el monto en MontoFinal;
            // las de cuota del programa lo guardan en MontoTotal (MontoFinal les queda en 0).
            'monto' => (float)($o['MontoFinal'] > 0 ? $o['MontoFinal'] : $o['MontoTotal']),
            'fechaGeneracion' => $o['FechaGeneracion'],
            'fechaVencimiento' => $o['FechaVencimiento'],
            'estado' => $o['Estado'],
            'vencida' => !empty($o['FechaVencimiento']) && $o['Estado'] === 'PENDIENTE' && $o['FechaVencimiento'] < $hoy,
        ];
    }, $ordenes);

    echo json_encode(['success' => true, 'ordenes' => $resultado]);
}
?>
