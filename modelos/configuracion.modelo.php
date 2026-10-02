<?php
require_once file_exists(__DIR__ . '/conexion.modelo.php') ? __DIR__ . '/conexion.modelo.php' : __DIR__ . '/../core/conexion.modelo.php';

/**
 * Parámetros del sistema (tabla `configuracion`, clave/valor), editables por el
 * ADM desde la pantalla Configuración.
 */
class ConfiguracionModelo
{
    // Nota mínima de aprobación si la tabla aún no existe o no tiene el valor
    const NOTA_APROBACION_POR_DEFECTO = 76;

    private static $cache = [];

    /**
     * Valor de un parámetro (se lee una sola vez por petición).
     */
    public static function Obtener($clave, $porDefecto = null)
    {
        if (array_key_exists($clave, self::$cache)) {
            return self::$cache[$clave];
        }
        try {
            $stmt = Conexion::Conectar()->prepare("SELECT Valor FROM configuracion WHERE Clave = :clave");
            $stmt->execute([':clave' => $clave]);
            $valor = $stmt->fetchColumn();
            return self::$cache[$clave] = ($valor === false ? $porDefecto : $valor);
        } catch (PDOException $e) {
            error_log("Error en ConfiguracionModelo::Obtener($clave): " . $e->getMessage());
            return $porDefecto;
        }
    }

    /**
     * Nota mínima para aprobar un módulo (entero 1-100). Todo el sistema la usa
     * desde aquí: consultas SQL, PDFs y, vía window.NOTA_APROBACION, el JavaScript.
     */
    public static function NotaAprobacion()
    {
        $nota = (int)self::Obtener('NOTA_APROBACION', self::NOTA_APROBACION_POR_DEFECTO);
        return ($nota >= 1 && $nota <= 100) ? $nota : self::NOTA_APROBACION_POR_DEFECTO;
    }

    /**
     * Guardar un parámetro.
     * @return bool
     */
    public static function Guardar($clave, $valor, $responsable)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "INSERT INTO configuracion (Clave, Valor, FechaActualizacion, ResponsableActualizacion)
                 VALUES (:clave, :valor, NOW(), :responsable)
                 ON DUPLICATE KEY UPDATE Valor = VALUES(Valor), FechaActualizacion = NOW(),
                    ResponsableActualizacion = VALUES(ResponsableActualizacion)"
            );
            $ok = $stmt->execute([':clave' => $clave, ':valor' => (string)$valor, ':responsable' => $responsable]);
            unset(self::$cache[$clave]);
            return $ok;
        } catch (PDOException $e) {
            error_log("Error en ConfiguracionModelo::Guardar($clave): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fila completa de un parámetro (para mostrar quién y cuándo lo cambió).
     * @return array|null
     */
    public static function Detalle($clave)
    {
        try {
            $stmt = Conexion::Conectar()->prepare("SELECT * FROM configuracion WHERE Clave = :clave");
            $stmt->execute([':clave' => $clave]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error en ConfiguracionModelo::Detalle($clave): " . $e->getMessage());
            return null;
        }
    }
}
