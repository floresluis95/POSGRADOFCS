<?php
/**
 * AJAX de Autoridades (Director y Coordinador de Posgrado).
 * Solo el ADM puede verlas y cambiarlas.
 */

require_once __DIR__ . '/validar-sesion.php';
ExigirSesionAjax(['ADM']);

require_once __DIR__ . '/../controladores/autoridad.controlador.php';

header('Content-Type: application/json; charset=utf-8');

switch ($_POST['accion'] ?? '') {
    case 'listar':
        echo json_encode(['success' => true, 'autoridades' => AutoridadModelo::ListarModelo()]);
        break;

    case 'guardar':
        echo json_encode(AutoridadControlador::GuardarControlador(
            $_POST['autoridades'] ?? null,
            AutoridadControlador::UsuarioSesion()
        ));
        break;

    // Parámetros académicos (tabla configuracion)
    case 'obtenerParametros':
        require_once __DIR__ . '/../modelos/configuracion.modelo.php';
        echo json_encode([
            'success' => true,
            'notaAprobacion' => ConfiguracionModelo::NotaAprobacion(),
            'detalle' => ConfiguracionModelo::Detalle('NOTA_APROBACION'),
        ]);
        break;

    case 'guardarNotaAprobacion':
        require_once __DIR__ . '/../modelos/configuracion.modelo.php';
        $nota = trim($_POST['notaAprobacion'] ?? '');
        if (!ctype_digit($nota) || (int)$nota < 1 || (int)$nota > 100) {
            echo json_encode(['success' => false, 'mensaje' => 'La nota mínima debe ser un número entero entre 1 y 100']);
            break;
        }
        $ok = ConfiguracionModelo::Guardar('NOTA_APROBACION', (int)$nota, AutoridadControlador::UsuarioSesion());
        echo json_encode([
            'success' => $ok,
            'mensaje' => $ok ? 'Nota mínima de aprobación actualizada a ' . (int)$nota : 'No se pudo guardar',
        ]);
        break;

    // Costos de los certificados (configuracion COSTO_CERT_<TIPO>)
    case 'obtenerCostosCertificados':
        require_once __DIR__ . '/../modelos/certificado.modelo.php';
        $tipos = CertificadoModelo::TiposDocumento();
        // Último cambio de cualquiera de los costos (quién y cuándo)
        $ultima = null;
        foreach (array_keys($tipos) as $tipo) {
            $d = ConfiguracionModelo::Detalle('COSTO_CERT_' . $tipo);
            if ($d && (!$ultima || $d['FechaActualizacion'] > $ultima['FechaActualizacion'])) {
                $ultima = $d;
            }
        }
        echo json_encode([
            'success' => true,
            'tipos' => $tipos,
            'precios' => CertificadoModelo::PreciosCertificados(),
            'ultimaActualizacion' => $ultima,
        ]);
        break;

    case 'guardarCostosCertificados':
        require_once __DIR__ . '/../modelos/certificado.modelo.php';
        $costos = is_array($_POST['costos'] ?? null) ? $_POST['costos'] : [];
        $responsable = AutoridadControlador::UsuarioSesion();
        $guardados = 0;
        $error = false;
        foreach (array_keys(CertificadoModelo::TiposDocumento()) as $tipo) {
            if (!array_key_exists($tipo, $costos)) continue;
            $valor = str_replace(',', '.', trim((string)$costos[$tipo]));
            if ($valor !== '' && (!is_numeric($valor) || (float)$valor < 0)) {
                $error = true;
                continue;
            }
            // Vacío = sin precio definido (el formulario del certificado llega en blanco)
            $nuevo = $valor === '' ? '' : number_format((float)$valor, 2, '.', '');
            $actual = ConfiguracionModelo::Obtener('COSTO_CERT_' . $tipo, '');
            if ($nuevo === (string)$actual) continue;
            if (ConfiguracionModelo::Guardar('COSTO_CERT_' . $tipo, $nuevo, $responsable)) {
                $guardados++;
            } else {
                $error = true;
            }
        }
        echo json_encode([
            'success' => !$error,
            'mensaje' => $error
                ? 'Algún costo no es válido o no se pudo guardar'
                : ($guardados ? 'Costos de certificados actualizados' : 'No hubo cambios'),
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
}
