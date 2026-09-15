<?php
/**
 * Controlador de Noticias e Informaciones del Panel principal.
 * Permisos: ADM y SEC pueden crear/editar/anular. Cualquier usuario
 * autenticado puede listarlas (se muestran a todos en el Panel).
 */

require_once __DIR__ . '/../modelos/noticiapanel.modelo.php';

class NoticiaPanelControlador
{
    private static function puedeEscribir()
    {
        $tipo = $_SESSION['Tipo'] ?? '';
        return in_array($tipo, ['ADM', 'SEC'], true);
    }

    public function ListarAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'data' => NoticiaPanelModelo::ListarActivasModelo()]);
        exit();
    }

    public function RegistrarAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!self::puedeEscribir()) {
            echo json_encode(['success' => false, 'mensaje' => 'No tiene permiso para registrar noticias']);
            exit();
        }

        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $autor = trim($_POST['autor'] ?? '') ?: 'Administración';
        $etiqueta = trim($_POST['etiqueta'] ?? '') ?: 'NUEVO';
        $enlace = trim($_POST['enlace'] ?? '');
        $orden = isset($_POST['orden']) && $_POST['orden'] !== '' ? (int)$_POST['orden'] : 0;
        $fechaPublicacion = trim($_POST['fechaPublicacion'] ?? '') ?: date('Y-m-d');

        if (empty($titulo) || empty($descripcion)) {
            echo json_encode(['success' => false, 'mensaje' => 'El título y la descripción son obligatorios']);
            exit();
        }

        $idNoticia = NoticiaPanelModelo::RegistrarModelo([
            'Titulo' => $titulo,
            'Descripcion' => $descripcion,
            'Autor' => $autor,
            'Etiqueta' => $etiqueta,
            'Enlace' => $enlace,
            'Orden' => $orden,
            'FechaPublicacion' => $fechaPublicacion,
            'CreadoPorUsuarioID' => $_SESSION['IdUsuario'] ?? null,
        ]);

        if ($idNoticia === false) {
            echo json_encode(['success' => false, 'mensaje' => 'No se pudo registrar la noticia']);
            exit();
        }

        echo json_encode(['success' => true, 'idNoticia' => $idNoticia, 'mensaje' => 'Noticia registrada correctamente']);
        exit();
    }

    public function ActualizarAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!self::puedeEscribir()) {
            echo json_encode(['success' => false, 'mensaje' => 'No tiene permiso para editar noticias']);
            exit();
        }

        $idNoticia = isset($_POST['idNoticia']) ? (int)$_POST['idNoticia'] : 0;
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $autor = trim($_POST['autor'] ?? '') ?: 'Administración';
        $etiqueta = trim($_POST['etiqueta'] ?? '') ?: 'NUEVO';
        $enlace = trim($_POST['enlace'] ?? '');
        $orden = isset($_POST['orden']) && $_POST['orden'] !== '' ? (int)$_POST['orden'] : 0;
        $fechaPublicacion = trim($_POST['fechaPublicacion'] ?? '') ?: date('Y-m-d');

        if (empty($idNoticia) || empty($titulo) || empty($descripcion)) {
            echo json_encode(['success' => false, 'mensaje' => 'El título y la descripción son obligatorios']);
            exit();
        }

        $ok = NoticiaPanelModelo::ActualizarModelo([
            'IdNoticia' => $idNoticia,
            'Titulo' => $titulo,
            'Descripcion' => $descripcion,
            'Autor' => $autor,
            'Etiqueta' => $etiqueta,
            'Enlace' => $enlace,
            'Orden' => $orden,
            'FechaPublicacion' => $fechaPublicacion,
        ]);

        echo json_encode([
            'success' => $ok,
            'mensaje' => $ok ? 'Noticia actualizada correctamente' : 'No se pudo actualizar la noticia'
        ]);
        exit();
    }

    public function AnularAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!self::puedeEscribir()) {
            echo json_encode(['success' => false, 'mensaje' => 'No tiene permiso para eliminar noticias']);
            exit();
        }

        $idNoticia = isset($_POST['idNoticia']) ? (int)$_POST['idNoticia'] : 0;
        if (empty($idNoticia)) {
            echo json_encode(['success' => false, 'mensaje' => 'La noticia no existe']);
            exit();
        }

        $ok = NoticiaPanelModelo::AnularModelo($idNoticia);

        echo json_encode([
            'success' => $ok,
            'mensaje' => $ok ? 'Noticia eliminada correctamente' : 'No se pudo eliminar la noticia'
        ]);
        exit();
    }
}
