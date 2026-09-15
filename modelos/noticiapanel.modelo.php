<?php
require_once 'conexion.modelo.php';

/**
 * Modelo de Noticias e Informaciones del Panel principal.
 * Administración/Secretaría puede crear, editar y eliminar (baja lógica);
 * todos los usuarios autenticados las ven en el Panel.
 */
class NoticiaPanelModelo
{
    /**
     * Noticias activas, para mostrarlas en el Panel.
     * @return array
     */
    public static function ListarActivasModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT * FROM noticiapanel
                 WHERE Estado = 'ACTIVO'
                 ORDER BY Orden ASC, FechaPublicacion DESC, IdNoticia DESC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarActivasModelo (noticia): " . $e->getMessage());
            return [];
        }
    }

    /**
     * @param int $idNoticia
     * @return array|null
     */
    public static function ObtenerPorIdModelo($idNoticia)
    {
        try {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM noticiapanel WHERE IdNoticia = :id");
            $stmt->bindParam(":id", $idNoticia, PDO::PARAM_INT);
            $stmt->execute();
            $noticia = $stmt->fetch(PDO::FETCH_ASSOC);
            return $noticia ?: null;
        } catch (PDOException $e) {
            error_log("Error en ObtenerPorIdModelo (noticia): " . $e->getMessage());
            return null;
        }
    }

    /**
     * @param array $datos Titulo, Descripcion, Autor, Etiqueta, Enlace, Orden, FechaPublicacion, CreadoPorUsuarioID
     * @return int|false
     */
    public static function RegistrarModelo($datos)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "INSERT INTO noticiapanel (Titulo, Descripcion, Autor, Etiqueta, Enlace, Orden, FechaPublicacion, CreadoPorUsuarioID)
                 VALUES (:titulo, :descripcion, :autor, :etiqueta, :enlace, :orden, :fechaPublicacion, :creadoPor)"
            );
            $stmt->bindParam(":titulo", $datos['Titulo'], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos['Descripcion'], PDO::PARAM_STR);
            $stmt->bindParam(":autor", $datos['Autor'], PDO::PARAM_STR);
            $stmt->bindParam(":etiqueta", $datos['Etiqueta'], PDO::PARAM_STR);
            if (!empty($datos['Enlace'])) {
                $stmt->bindParam(":enlace", $datos['Enlace'], PDO::PARAM_STR);
            } else {
                $stmt->bindValue(":enlace", null, PDO::PARAM_NULL);
            }
            $stmt->bindParam(":orden", $datos['Orden'], PDO::PARAM_INT);
            $stmt->bindParam(":fechaPublicacion", $datos['FechaPublicacion'], PDO::PARAM_STR);
            if (!empty($datos['CreadoPorUsuarioID'])) {
                $stmt->bindParam(":creadoPor", $datos['CreadoPorUsuarioID'], PDO::PARAM_INT);
            } else {
                $stmt->bindValue(":creadoPor", null, PDO::PARAM_NULL);
            }

            if (!$stmt->execute()) {
                return false;
            }
            return (int)Conexion::Conectar()->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en RegistrarModelo (noticia): " . $e->getMessage());
            return false;
        }
    }

    /**
     * @param array $datos IdNoticia, Titulo, Descripcion, Autor, Etiqueta, Enlace, Orden, FechaPublicacion
     * @return bool
     */
    public static function ActualizarModelo($datos)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "UPDATE noticiapanel SET
                    Titulo = :titulo,
                    Descripcion = :descripcion,
                    Autor = :autor,
                    Etiqueta = :etiqueta,
                    Enlace = :enlace,
                    Orden = :orden,
                    FechaPublicacion = :fechaPublicacion
                 WHERE IdNoticia = :id"
            );
            $stmt->bindParam(":titulo", $datos['Titulo'], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos['Descripcion'], PDO::PARAM_STR);
            $stmt->bindParam(":autor", $datos['Autor'], PDO::PARAM_STR);
            $stmt->bindParam(":etiqueta", $datos['Etiqueta'], PDO::PARAM_STR);
            if (!empty($datos['Enlace'])) {
                $stmt->bindParam(":enlace", $datos['Enlace'], PDO::PARAM_STR);
            } else {
                $stmt->bindValue(":enlace", null, PDO::PARAM_NULL);
            }
            $stmt->bindParam(":orden", $datos['Orden'], PDO::PARAM_INT);
            $stmt->bindParam(":fechaPublicacion", $datos['FechaPublicacion'], PDO::PARAM_STR);
            $stmt->bindParam(":id", $datos['IdNoticia'], PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ActualizarModelo (noticia): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Anular (baja lógica) una noticia.
     * @param int $idNoticia
     * @return bool
     */
    public static function AnularModelo($idNoticia)
    {
        try {
            $stmt = Conexion::Conectar()->prepare("UPDATE noticiapanel SET Estado = 'ANULADO' WHERE IdNoticia = :id");
            $stmt->bindParam(":id", $idNoticia, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en AnularModelo (noticia): " . $e->getMessage());
            return false;
        }
    }
}
