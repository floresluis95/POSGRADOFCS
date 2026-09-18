<?php
require_once 'conexion.modelo.php';

/**
 * Modelo de Universidades
 * Catálogo de universidades de las que egresó el estudiante antes de
 * inscribirse al posgrado. Cada universidad tiene un tipo fijo (pública,
 * privada, extranjera o de convenio) que se define al registrarla.
 */
class UniversidadModelos
{
    /**
     * Listar universidades activas, para el select del formulario de datos
     * del estudiante (Estudiantes y Pre-Registro).
     * @return array
     */
    public static function ListarUniversidadesModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT IdUniversidad, NombreUniversidad, TipoUniversidad
                 FROM universidad WHERE Estado = 1 ORDER BY NombreUniversidad ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ListarUniversidadesModelo: " . $e->getMessage());
            return [];
        }
    }

    public static function ObtenerPorIdModelo($id)
    {
        $stmt = Conexion::Conectar()->prepare("SELECT * FROM universidad WHERE IdUniversidad = :id LIMIT 1");
        $stmt->bindValue(":id", (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function BuscarPorNombreModelo($nombre)
    {
        $stmt = Conexion::Conectar()->prepare("SELECT * FROM universidad WHERE NombreUniversidad = :nombre LIMIT 1");
        $stmt->bindValue(":nombre", $nombre, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Registrar una universidad nueva (botón "+" del select de Universidad),
     * o reactivarla si ya existía pero estaba dada de baja. El tipo es
     * obligatorio: es lo que permite luego filtrar/validar según sea pública,
     * privada, extranjera o de convenio.
     * @param string $nombre
     * @param string $tipo PUBLICA|PRIVADA|EXTRANJERA|CONVENIO
     * @return array ['status' => 'exitoso'/'duplicado'/'error', 'universidad' => array|null]
     */
    public static function RegistrarUniversidadModelo($nombre, $tipo)
    {
        $nombre = trim($nombre);
        $tiposValidos = ['PUBLICA', 'PRIVADA', 'EXTRANJERA', 'CONVENIO'];

        if ($nombre === '') {
            return ['status' => 'error', 'mensaje' => 'El nombre de la universidad es requerido'];
        }
        if (!in_array($tipo, $tiposValidos, true)) {
            return ['status' => 'error', 'mensaje' => 'Seleccione un tipo de universidad válido'];
        }

        $existente = self::BuscarPorNombreModelo($nombre);
        if ($existente) {
            return ['status' => 'duplicado', 'mensaje' => 'Esa universidad ya existe', 'universidad' => $existente];
        }

        try {
            $pdo = Conexion::Conectar();
            $stmt = $pdo->prepare(
                "INSERT INTO universidad (NombreUniversidad, TipoUniversidad) VALUES (:nombre, :tipo)"
            );
            $stmt->bindValue(":nombre", $nombre, PDO::PARAM_STR);
            $stmt->bindValue(":tipo", $tipo, PDO::PARAM_STR);

            if (!$stmt->execute()) {
                return ['status' => 'error', 'mensaje' => 'No se pudo registrar la universidad'];
            }

            $id = $pdo->lastInsertId();
            return [
                'status' => 'exitoso',
                'mensaje' => 'Universidad registrada correctamente',
                'universidad' => ['IdUniversidad' => $id, 'NombreUniversidad' => $nombre, 'TipoUniversidad' => $tipo]
            ];
        } catch (PDOException $e) {
            error_log("Error en RegistrarUniversidadModelo: " . $e->getMessage());
            return ['status' => 'error', 'mensaje' => 'Error en el servidor: ' . $e->getMessage()];
        }
    }
}
