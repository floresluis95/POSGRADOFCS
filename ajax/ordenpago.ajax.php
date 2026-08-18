<?php
/**
 * AJAX para Orden de Pago - Búsqueda de Estudiantes
 */

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../modelos/estudiantes.modelo.php';
require_once __DIR__ . '/../modelos/modulopagos_core.php';
require_once __DIR__ . '/../modelos/ordenpagoprincipal.modelo.php';
require_once __DIR__ . '/../modelos/planpago.modelo.php';

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

    case 'registrarEstudianteRapido':
        registrarEstudianteRapido();
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

    case 'obtenerCuotaPrograma':
        obtenerCuotaPrograma();
        break;

    case 'registrarPagoCuota':
        registrarPagoCuota();
        break;

    case 'listarMatriculadosSinPlan':
        listarMatriculadosSinPlan();
        break;

    case 'registrarPlanGrupal':
        registrarPlanGrupal();
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
            "Correo"          => htmlspecialchars(trim($_POST['Correo'])),
            "IdProfesion"     => (int)$_POST['IdProfesion'],
            "Trabajo"         => strtoupper(htmlspecialchars(trim($_POST['Trabajo'] ?? ''))),
            "Direccion"       => strtoupper(htmlspecialchars(trim($_POST['Direccion'] ?? ''))),
            "Telefono"        => htmlspecialchars(trim($_POST['Telefono'] ?? '')),
            "Celular"         => htmlspecialchars(trim($_POST['Celular'])),
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
        $preregistro['ProgramaTexto'] = 'PROGRAMA DE POSGRADO EN LA ' . strtoupper($preregistro['GradoAcademico']) .
            ' EN ' . strtoupper($preregistro['NombrePrograma']) .
            (!empty($preregistro['Version']) ? ' "' . strtoupper($preregistro['Version']) . '"' : '') .
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
            $resultado .= $centenas[(int)floor($num / 100)] . ' ';
            $num %= 100;
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
 * Actualizar los datos de facturación de una orden ya generada (pantalla "orden-generada")
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
        'responsable'   => isset($_POST['responsable']) ? htmlspecialchars(trim($_POST['responsable'])) : '',
        'firma'         => isset($_POST['firma']) ? htmlspecialchars(trim($_POST['firma'])) : '',
    ];

    $ok = OrdenPagoModelos::ActualizarFacturacionOrdenModelo($datos);
    echo json_encode([
        'success' => $ok,
        'mensaje' => $ok ? 'Datos de facturación actualizados' : 'No se pudieron actualizar los datos de facturación'
    ]);
}

/**
 * Validar el voucher de la matrícula ya cancelada e inscribir formalmente al estudiante
 * (única escritura en estudianteprograma en todo el flujo de preregistro).
 * Se usa desde Inscripción; acepta opcionalmente una foto del voucher (multipart/form-data).
 */
function validarVoucherMatricula()
{
    if (empty($_POST['idOrdenPago']) || empty($_POST['numeroVoucher'])) {
        echo json_encode(['success' => false, 'mensaje' => 'El N° de voucher es requerido']);
        return;
    }

    $numeroVoucher = htmlspecialchars(trim($_POST['numeroVoucher']));
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
    $planPago = null;
    if (!empty($_POST['planPago'])) {
        $planDecodificado = json_decode($_POST['planPago'], true);
        if (is_array($planDecodificado) && !empty($planDecodificado['Cuotas'])) {
            $planPago = [
                'TipoPlan' => htmlspecialchars(trim($planDecodificado['TipoPlan'] ?? 'REGULAR')),
                'CostoTotalPrograma' => floatval($planDecodificado['CostoTotalPrograma'] ?? 0),
                'PorcentajeDescuento' => floatval($planDecodificado['PorcentajeDescuento'] ?? 0),
                'CodigoGrupo' => !empty($planDecodificado['CodigoGrupo']) ? htmlspecialchars(trim($planDecodificado['CodigoGrupo'])) : null,
                'CantidadInscritosGrupo' => (int)($planDecodificado['CantidadInscritosGrupo'] ?? 1),
                'ResponsableGeneracion' => isset($_SESSION['Nombre']) && isset($_SESSION['Apellido'])
                    ? $_SESSION['Nombre'] . ' ' . $_SESSION['Apellido']
                    : null,
                'Cuotas' => array_map(function ($c) {
                    return [
                        'monto' => floatval($c['monto'] ?? 0),
                        'fecha' => htmlspecialchars(trim($c['fecha'] ?? ''))
                    ];
                }, $planDecodificado['Cuotas'])
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
 */
function registrarPlanPagoPrograma()
{
    if (empty($_POST['idInscripcion']) || empty($_POST['estudianteID']) ||
        empty($_POST['programaID']) || empty($_POST['costoTotalPrograma']) || empty($_POST['cuotas'])) {
        echo json_encode(['success' => false, 'mensaje' => 'Faltan datos requeridos del plan de pago']);
        return;
    }

    $cuotas = json_decode($_POST['cuotas'], true);
    if (!is_array($cuotas) || empty($cuotas)) {
        echo json_encode(['success' => false, 'mensaje' => 'Debe definir al menos una cuota']);
        return;
    }

    $datos = [
        'idInscripcion' => (int)$_POST['idInscripcion'],
        'EstudianteID' => (int)$_POST['estudianteID'],
        'ProgramaID' => (int)$_POST['programaID'],
        'CostoTotalPrograma' => floatval($_POST['costoTotalPrograma']),
        'TipoPlan' => htmlspecialchars(trim($_POST['tipoPlan'] ?? 'REGULAR')),
        'PorcentajeDescuento' => isset($_POST['porcentajeDescuento']) ? floatval($_POST['porcentajeDescuento']) : 0,
        'CodigoGrupo' => !empty($_POST['codigoGrupo']) ? htmlspecialchars(trim($_POST['codigoGrupo'])) : null,
        'CantidadInscritosGrupo' => isset($_POST['cantidadInscritosGrupo']) ? (int)$_POST['cantidadInscritosGrupo'] : 1,
        'ResponsableGeneracion' => isset($_SESSION['Nombre']) && isset($_SESSION['Apellido'])
            ? $_SESSION['Nombre'] . ' ' . $_SESSION['Apellido']
            : null,
        'Cuotas' => array_map(function ($c) {
            return [
                'monto' => floatval($c['monto'] ?? 0),
                'fecha' => htmlspecialchars(trim($c['fecha'] ?? ''))
            ];
        }, $cuotas)
    ];

    $resultado = PagoProgramaModelos::RegistrarPlanModelo($datos);

    echo json_encode([
        'success' => $resultado['status'] === 'exitoso',
        'mensaje' => $resultado['mensaje']
    ]);
}

/**
 * Obtener una cuota puntual (para precargar el modal de "Generar Orden de Pago" de esa cuota)
 */
function obtenerCuotaPrograma()
{
    if (empty($_POST['idCuota'])) {
        echo json_encode(['success' => false, 'mensaje' => 'El ID de la cuota es requerido']);
        return;
    }

    $cuota = PagoProgramaModelos::ObtenerCuotaModelo((int)$_POST['idCuota']);

    if (!$cuota) {
        echo json_encode(['success' => false, 'mensaje' => 'No se encontró la cuota']);
        return;
    }

    $cuota['CiCompleto'] = trim(
        $cuota['Ci'] . (!empty($cuota['Complemento']) ? '-' . $cuota['Complemento'] : '') .
        ' ' . $cuota['Exp']
    );
    $cuota['MontoLiteral'] = numeroALetrasOrdenPago((float)$cuota['MontoCuota']);
    $planTexto = [
        'REGULAR' => 'PLAN REGULAR',
        'DESCUENTO' => 'PLAN AL CONTADO CON DESCUENTO',
        'GRUPAL' => 'PLAN GRUPAL (VARIOS INSCRITOS)'
    ][$cuota['TipoPlan']] ?? $cuota['TipoPlan'];

    $cuota['ProgramaTexto'] = 'PROGRAMA DE POSGRADO EN LA ' . strtoupper($cuota['GradoAcademico']) .
        ' EN ' . strtoupper($cuota['NombrePrograma']) .
        (!empty($cuota['Version']) ? ' "' . strtoupper($cuota['Version']) . '"' : '') .
        ' - CUOTA ' . $cuota['NumeroCuota'] . '/' . $cuota['NumeroCuotas'] . ' (' . $planTexto . ')';

    echo json_encode(['success' => true, 'cuota' => $cuota]);
}

/**
 * Registrar el pago (voucher) de una cuota pendiente del plan de pago del programa.
 */
function registrarPagoCuota()
{
    if (empty($_POST['idCuota']) || empty($_POST['numeroVoucher'])) {
        echo json_encode(['success' => false, 'mensaje' => 'El N° de voucher es requerido']);
        return;
    }

    $fechaPago = !empty($_POST['fechaPago']) ? htmlspecialchars(trim($_POST['fechaPago'])) : null;

    $fotoVoucher = null;
    if (isset($_FILES['fotoVoucher']) && $_FILES['fotoVoucher']['error'] === UPLOAD_ERR_OK) {
        $fotoVoucher = file_get_contents($_FILES['fotoVoucher']['tmp_name']);
    }

    $resultado = PagoProgramaModelos::RegistrarPagoCuotaModelo(
        (int)$_POST['idCuota'],
        htmlspecialchars(trim($_POST['numeroVoucher'])),
        $fechaPago,
        $fotoVoucher
    );

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
    if (empty($_POST['idsInscripcion']) || !isset($_POST['porcentajeDescuento']) || empty($_POST['fechaVencimiento'])) {
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
        htmlspecialchars(trim($_POST['fechaVencimiento'])),
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
?>
