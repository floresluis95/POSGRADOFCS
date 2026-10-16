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

    // Firma del coordinador y sello de Posgrado (imágenes) para las órdenes de pago
    // que genera el estudiante
    case 'estadoImagenesFirma':
        require_once __DIR__ . '/../modelos/imagenfirma.modelo.php';
        $imagenes = [];
        foreach (ImagenFirmaModelo::Estado() as $clave => $detalle) {
            $png = $detalle ? ImagenFirmaModelo::Obtener($clave) : null;
            $imagenes[$clave] = [
                'nombre' => ImagenFirmaModelo::CLAVES[$clave],
                'imagen' => $png ? 'data:image/png;base64,' . base64_encode($png) : null,
                'actualizado' => $detalle,
            ];
        }
        echo json_encode(['success' => true, 'imagenes' => $imagenes]);
        break;

    case 'subirImagenFirma':
        require_once __DIR__ . '/../modelos/imagenfirma.modelo.php';
        $clave = $_POST['clave'] ?? '';
        $archivo = $_FILES['imagen'] ?? null;
        if (!$archivo || $archivo['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'mensaje' => 'Elija la imagen (PNG o JPG)']);
            break;
        }
        if ($archivo['size'] > 3 * 1024 * 1024) {
            echo json_encode(['success' => false, 'mensaje' => 'La imagen es muy grande (máx. 3 MB)']);
            break;
        }
        $resultado = ImagenFirmaModelo::Guardar($clave, file_get_contents($archivo['tmp_name']), AutoridadControlador::UsuarioSesion());
        echo json_encode(['success' => $resultado === true, 'mensaje' => $resultado === true ? 'Imagen guardada' : $resultado]);
        break;

    case 'quitarImagenFirma':
        require_once __DIR__ . '/../modelos/imagenfirma.modelo.php';
        $ok = isset(ImagenFirmaModelo::CLAVES[$_POST['clave'] ?? '']) && ImagenFirmaModelo::Quitar($_POST['clave']);
        echo json_encode(['success' => (bool)$ok, 'mensaje' => $ok ? 'Imagen quitada' : 'No se pudo quitar la imagen']);
        break;

    // Costos de los certificados por grado académico (configuracion COSTO_CERT_<TIPO>_<GRADO>),
    // pantalla Costos de certificados
    case 'obtenerCostosCertificados':
        require_once __DIR__ . '/../modelos/certificado.modelo.php';
        echo json_encode([
            'success' => true,
            'tipos' => CertificadoModelo::TiposDocumento(),
            'grados' => CertificadoModelo::GradosAcademicos(),
            'precios' => CertificadoModelo::PreciosCertificados(),
            'ultimaActualizacion' => CertificadoModelo::UltimoCambioPrecios(),
        ]);
        break;

    case 'guardarCostosCertificados':
        require_once __DIR__ . '/../modelos/certificado.modelo.php';
        $costos = is_array($_POST['costos'] ?? null) ? $_POST['costos'] : [];
        $responsable = AutoridadControlador::UsuarioSesion();
        $guardados = 0;
        $error = false;
        foreach (array_keys(CertificadoModelo::TiposDocumento()) as $tipo) {
            foreach (array_keys(CertificadoModelo::GradosAcademicos()) as $grado) {
                if (!isset($costos[$tipo]) || !is_array($costos[$tipo]) || !array_key_exists($grado, $costos[$tipo])) continue;
                $valor = str_replace(',', '.', trim((string)$costos[$tipo][$grado]));
                if ($valor !== '' && (!is_numeric($valor) || (float)$valor < 0)) {
                    $error = true;
                    continue;
                }
                // Vacío = sin precio definido (el formulario del certificado llega en blanco)
                $resultado = CertificadoModelo::GuardarPrecioCertificado($tipo, $grado, $valor, $responsable);
                if ($resultado === true) {
                    $guardados++;
                } elseif ($resultado === false) {
                    $error = true;
                }
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
