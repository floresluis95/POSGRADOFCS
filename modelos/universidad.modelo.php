<?php
require_once __DIR__ . '/../core/conexion.modelo.php';

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
     * Clave para comparar nombres de universidad: sin lo que va entre paréntesis
     * (la sigla), sin tildes ni signos y sin espacios de más, en MAYÚSCULAS.
     * "Universidad Privada de Oruro" y "UNIVERSIDAD PRIVADA DE ORURO (UNIOR)" => misma clave.
     */
    public static function ClaveNombre($nombre)
    {
        $texto = preg_replace('/\([^)]*\)/u', ' ', html_entity_decode((string)$nombre, ENT_QUOTES, 'UTF-8'));
        return self::SinTildesNiSignos($texto);
    }

    /** Sigla escrita entre paréntesis ("... (UNIOR)" => "UNIOR"), o '' si no tiene. */
    public static function SiglaNombre($nombre)
    {
        return preg_match('/\(([^)]*)\)/u', html_entity_decode((string)$nombre, ENT_QUOTES, 'UTF-8'), $m)
            ? self::SinTildesNiSignos($m[1])
            : '';
    }

    private static function SinTildesNiSignos($texto)
    {
        $texto = strtr(mb_strtoupper($texto, 'UTF-8'), ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U']);
        $texto = preg_replace('/[^A-Z0-9Ñ ]+/u', ' ', $texto);
        return trim(preg_replace('/\s+/u', ' ', $texto));
    }

    /**
     * Universidad ya registrada con otro nombre que es la misma: igual salvo la sigla
     * entre paréntesis, con la misma sigla, o cuando se escribió solo la sigla.
     * @return array|false
     */
    public static function BuscarParecidaModelo($nombre)
    {
        $clave = self::ClaveNombre($nombre);
        $sigla = self::SiglaNombre($nombre);

        $stmt = Conexion::Conectar()->prepare("SELECT * FROM universidad");
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $u) {
            $siglaExistente = self::SiglaNombre($u['NombreUniversidad']);
            if ($clave !== '' && $clave === self::ClaveNombre($u['NombreUniversidad'])) return $u;
            if ($siglaExistente !== '' && ($siglaExistente === $sigla || $siglaExistente === $clave)) return $u;
        }
        return false;
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

        $existente = self::BuscarPorNombreModelo($nombre) ?: self::BuscarParecidaModelo($nombre);
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
            // Índice único: otro usuario la registró al mismo tiempo => se devuelve esa
            if ($e->getCode() === '23000' && ($existente = self::BuscarPorNombreModelo($nombre))) {
                return ['status' => 'duplicado', 'mensaje' => 'Esa universidad ya existe', 'universidad' => $existente];
            }
            error_log("Error en RegistrarUniversidadModelo: " . $e->getMessage());
            return ['status' => 'error', 'mensaje' => 'Error en el servidor: ' . $e->getMessage()];
        }
    }
}
