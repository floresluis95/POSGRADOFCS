<?php
require_once 'conexion.modelo.php';

/**
 * Modelo del Carrusel del Panel principal.
 * Cada diapositiva está vinculada a un Programa real (para el modal "Más
 * Información": costo, módulos, fecha de inicio, etc.), con imagen y textos
 * editables por Administración/Secretaría.
 */
class CarruselPanelModelo
{
    /**
     * Diapositivas activas con los datos del programa, para el carrusel del Panel.
     * @return array
     */
    public static function ListarActivosModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT
                    cp.IdSlide, cp.ProgramaID, cp.ImagenURL, cp.TituloPersonalizado, cp.DescripcionPersonalizada, cp.Orden,
                    p.NombrePrograma, p.GradoAcademico, p.Codigo, p.Costo, p.CostoMatricula, p.Modulos,
                    p.FechaInicio, p.Sede, p.Detalle, p.DuracionMeses
                 FROM carruselpanel cp
                 INNER JOIN programa p ON p.ProgramaID = cp.ProgramaID
                 WHERE cp.Estado = 'ACTIVO'
                 ORDER BY cp.Orden ASC, cp.IdSlide ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarActivosModelo (carruselpanel): " . $e->getMessage());
            return [];
        }
    }

    /**
     * @param int $idSlide
     * @return array|null
     */
    public static function ObtenerPorIdModelo($idSlide)
    {
        try {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM carruselpanel WHERE IdSlide = :id");
            $stmt->bindParam(":id", $idSlide, PDO::PARAM_INT);
            $stmt->execute();
            $slide = $stmt->fetch(PDO::FETCH_ASSOC);
            return $slide ?: null;
        } catch (PDOException $e) {
            error_log("Error en ObtenerPorIdModelo (carruselpanel): " . $e->getMessage());
            return null;
        }
    }

    /**
     * @param array $datos ProgramaID, ImagenURL, TituloPersonalizado, DescripcionPersonalizada, Orden, CreadoPorUsuarioID
     * @return int|false
     */
    public static function RegistrarModelo($datos)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "INSERT INTO carruselpanel (ProgramaID, ImagenURL, TituloPersonalizado, DescripcionPersonalizada, Orden, CreadoPorUsuarioID)
                 VALUES (:programaID, :imagenURL, :titulo, :descripcion, :orden, :creadoPor)"
            );
            $stmt->bindParam(":programaID", $datos['ProgramaID'], PDO::PARAM_INT);
            if (!empty($datos['ImagenURL'])) {
                $stmt->bindParam(":imagenURL", $datos['ImagenURL'], PDO::PARAM_STR);
            } else {
                $stmt->bindValue(":imagenURL", null, PDO::PARAM_NULL);
            }
            if (!empty($datos['TituloPersonalizado'])) {
                $stmt->bindParam(":titulo", $datos['TituloPersonalizado'], PDO::PARAM_STR);
            } else {
                $stmt->bindValue(":titulo", null, PDO::PARAM_NULL);
            }
            if (!empty($datos['DescripcionPersonalizada'])) {
                $stmt->bindParam(":descripcion", $datos['DescripcionPersonalizada'], PDO::PARAM_STR);
            } else {
                $stmt->bindValue(":descripcion", null, PDO::PARAM_NULL);
            }
            $stmt->bindParam(":orden", $datos['Orden'], PDO::PARAM_INT);
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
            error_log("Error en RegistrarModelo (carruselpanel): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza una diapositiva. Si $datos['ImagenURL'] viene vacío, conserva la imagen actual
     * (el llamador solo la incluye cuando el administrador sube una nueva).
     * @param array $datos IdSlide, ProgramaID, ImagenURL (opcional), TituloPersonalizado, DescripcionPersonalizada, Orden
     * @return bool
     */
    public static function ActualizarModelo($datos)
    {
        try {
            $sql = "UPDATE carruselpanel SET
                        ProgramaID = :programaID,
                        TituloPersonalizado = :titulo,
                        DescripcionPersonalizada = :descripcion,
                        Orden = :orden";
            if (!empty($datos['ImagenURL'])) {
                $sql .= ", ImagenURL = :imagenURL";
            }
            $sql .= " WHERE IdSlide = :id";

            $stmt = Conexion::Conectar()->prepare($sql);
            $stmt->bindParam(":programaID", $datos['ProgramaID'], PDO::PARAM_INT);
            if (!empty($datos['TituloPersonalizado'])) {
                $stmt->bindParam(":titulo", $datos['TituloPersonalizado'], PDO::PARAM_STR);
            } else {
                $stmt->bindValue(":titulo", null, PDO::PARAM_NULL);
            }
            if (!empty($datos['DescripcionPersonalizada'])) {
                $stmt->bindParam(":descripcion", $datos['DescripcionPersonalizada'], PDO::PARAM_STR);
            } else {
                $stmt->bindValue(":descripcion", null, PDO::PARAM_NULL);
            }
            $stmt->bindParam(":orden", $datos['Orden'], PDO::PARAM_INT);
            if (!empty($datos['ImagenURL'])) {
                $stmt->bindParam(":imagenURL", $datos['ImagenURL'], PDO::PARAM_STR);
            }
            $stmt->bindParam(":id", $datos['IdSlide'], PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ActualizarModelo (carruselpanel): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Anular (baja lógica) una diapositiva del carrusel.
     * @param int $idSlide
     * @return bool
     */
    public static function AnularModelo($idSlide)
    {
        try {
            $stmt = Conexion::Conectar()->prepare("UPDATE carruselpanel SET Estado = 'ANULADO' WHERE IdSlide = :id");
            $stmt->bindParam(":id", $idSlide, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en AnularModelo (carruselpanel): " . $e->getMessage());
            return false;
        }
    }
}
