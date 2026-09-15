<?php
/**
 * Controlador del Carrusel del Panel principal.
 * Permisos: ADM y SEC pueden crear/editar/anular diapositivas (incluida la
 * imagen). Cualquier usuario autenticado puede listarlas (se muestran a
 * todos en el Panel).
 */

require_once __DIR__ . '/../modelos/carruselpanel.modelo.php';

class CarruselPanelControlador
{
    /** Carpeta donde se guardan las imágenes subidas para el carrusel */
    const CARPETA_IMAGENES = 'vistas/recursos/assets/media/carrusel/';
    const EXTENSIONES_PERMITIDAS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    const TAMANIO_MAXIMO = 4 * 1024 * 1024; // 4 MB

    private static function puedeEscribir()
    {
        $tipo = $_SESSION['Tipo'] ?? '';
        return in_array($tipo, ['ADM', 'SEC'], true);
    }

    /**
     * Sube la imagen de $_FILES['imagen'] (si viene) y devuelve la ruta
     * relativa guardada, o null si no se envió ninguna imagen.
     * @return string|null|false null = no se envió imagen; false = error de validación
     */
    private static function procesarImagenSubida()
    {
        if (empty($_FILES['imagen']) || $_FILES['imagen']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $archivo = $_FILES['imagen'];

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        if ($archivo['size'] > self::TAMANIO_MAXIMO) {
            return false;
        }

        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::EXTENSIONES_PERMITIDAS, true)) {
            return false;
        }

        // Validar que sea realmente una imagen (no solo la extensión)
        $info = @getimagesize($archivo['tmp_name']);
        if ($info === false) {
            return false;
        }

        $carpetaAbsoluta = __DIR__ . '/../' . self::CARPETA_IMAGENES;
        if (!is_dir($carpetaAbsoluta)) {
            mkdir($carpetaAbsoluta, 0755, true);
        }

        $nombreArchivo = 'slide_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $rutaDestino = $carpetaAbsoluta . $nombreArchivo;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            return false;
        }

        return self::CARPETA_IMAGENES . $nombreArchivo;
    }

    public function ListarAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'data' => CarruselPanelModelo::ListarActivosModelo()]);
        exit();
    }

    public function RegistrarAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!self::puedeEscribir()) {
            echo json_encode(['success' => false, 'mensaje' => 'No tiene permiso para gestionar el carrusel']);
            exit();
        }

        $programaID = isset($_POST['programaID']) ? (int)$_POST['programaID'] : 0;
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $orden = isset($_POST['orden']) && $_POST['orden'] !== '' ? (int)$_POST['orden'] : 0;

        if (empty($programaID)) {
            echo json_encode(['success' => false, 'mensaje' => 'Debe seleccionar un programa']);
            exit();
        }

        $imagenURL = self::procesarImagenSubida();
        if ($imagenURL === false) {
            echo json_encode(['success' => false, 'mensaje' => 'La imagen no es válida (formatos permitidos: jpg, png, webp, gif; máx. 4MB)']);
            exit();
        }

        $idSlide = CarruselPanelModelo::RegistrarModelo([
            'ProgramaID' => $programaID,
            'ImagenURL' => $imagenURL,
            'TituloPersonalizado' => $titulo,
            'DescripcionPersonalizada' => $descripcion,
            'Orden' => $orden,
            'CreadoPorUsuarioID' => $_SESSION['IdUsuario'] ?? null,
        ]);

        if ($idSlide === false) {
            echo json_encode(['success' => false, 'mensaje' => 'No se pudo registrar la diapositiva']);
            exit();
        }

        echo json_encode(['success' => true, 'idSlide' => $idSlide, 'mensaje' => 'Diapositiva registrada correctamente']);
        exit();
    }

    public function ActualizarAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!self::puedeEscribir()) {
            echo json_encode(['success' => false, 'mensaje' => 'No tiene permiso para gestionar el carrusel']);
            exit();
        }

        $idSlide = isset($_POST['idSlide']) ? (int)$_POST['idSlide'] : 0;
        $programaID = isset($_POST['programaID']) ? (int)$_POST['programaID'] : 0;
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $orden = isset($_POST['orden']) && $_POST['orden'] !== '' ? (int)$_POST['orden'] : 0;

        if (empty($idSlide) || empty($programaID)) {
            echo json_encode(['success' => false, 'mensaje' => 'Debe seleccionar un programa']);
            exit();
        }

        $imagenURL = self::procesarImagenSubida();
        if ($imagenURL === false) {
            echo json_encode(['success' => false, 'mensaje' => 'La imagen no es válida (formatos permitidos: jpg, png, webp, gif; máx. 4MB)']);
            exit();
        }

        $ok = CarruselPanelModelo::ActualizarModelo([
            'IdSlide' => $idSlide,
            'ProgramaID' => $programaID,
            'ImagenURL' => $imagenURL, // null = no se subió una nueva, se conserva la actual
            'TituloPersonalizado' => $titulo,
            'DescripcionPersonalizada' => $descripcion,
            'Orden' => $orden,
        ]);

        echo json_encode([
            'success' => $ok,
            'mensaje' => $ok ? 'Diapositiva actualizada correctamente' : 'No se pudo actualizar la diapositiva'
        ]);
        exit();
    }

    public function AnularAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!self::puedeEscribir()) {
            echo json_encode(['success' => false, 'mensaje' => 'No tiene permiso para gestionar el carrusel']);
            exit();
        }

        $idSlide = isset($_POST['idSlide']) ? (int)$_POST['idSlide'] : 0;
        if (empty($idSlide)) {
            echo json_encode(['success' => false, 'mensaje' => 'La diapositiva no existe']);
            exit();
        }

        $ok = CarruselPanelModelo::AnularModelo($idSlide);

        echo json_encode([
            'success' => $ok,
            'mensaje' => $ok ? 'Diapositiva eliminada correctamente' : 'No se pudo eliminar la diapositiva'
        ]);
        exit();
    }
}
