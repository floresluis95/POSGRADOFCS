<?php
require_once file_exists(__DIR__ . '/conexion.modelo.php') ? __DIR__ . '/conexion.modelo.php' : __DIR__ . '/../core/conexion.modelo.php';

/**
 * Modelo de Autoridades
 * Director y Coordinador de Posgrado que firman los documentos (por ahora el
 * Documento Privado de Compromiso). Una fila por cargo en la tabla `autoridad`.
 */
class AutoridadModelo
{
    // Cargos que existen; el orden es el de la pantalla
    const CARGOS = ['DIRECTOR', 'COORDINADOR'];

    /**
     * Las autoridades registradas, indexadas por cargo.
     * @return array ['DIRECTOR' => [...], 'COORDINADOR' => [...]]
     */
    public static function ListarModelo()
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "SELECT Cargo, Titulo, Nombre, FechaActualizacion, ResponsableActualizacion FROM autoridad"
            );
            $stmt->execute();

            $autoridades = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
                $autoridades[$fila['Cargo']] = $fila;
            }
            return $autoridades;
        } catch (PDOException $e) {
            error_log("Error en AutoridadModelo::ListarModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Guardar el título y nombre de un cargo (lo crea si aún no existe).
     * @return bool
     */
    public static function GuardarModelo($cargo, $titulo, $nombre, $responsable)
    {
        try {
            $stmt = Conexion::Conectar()->prepare(
                "INSERT INTO autoridad (Cargo, Titulo, Nombre, FechaActualizacion, ResponsableActualizacion)
                 VALUES (:cargo, :titulo, :nombre, NOW(), :responsable)
                 ON DUPLICATE KEY UPDATE Titulo = VALUES(Titulo), Nombre = VALUES(Nombre),
                    FechaActualizacion = NOW(), ResponsableActualizacion = VALUES(ResponsableActualizacion)"
            );
            $stmt->bindParam(":cargo", $cargo, PDO::PARAM_STR);
            $stmt->bindParam(":titulo", $titulo, PDO::PARAM_STR);
            $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
            $stmt->bindParam(":responsable", $responsable, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en AutoridadModelo::GuardarModelo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Nombre para imprimir: "MSc. Ing. Carlos Antezana García".
     * Si el cargo no está registrado devuelve una línea para llenar a mano.
     */
    public static function NombreParaDocumento(array $autoridades, $cargo)
    {
        if (empty($autoridades[$cargo]['Nombre'])) {
            return '................................................';
        }
        return trim($autoridades[$cargo]['Titulo'] . ' ' . $autoridades[$cargo]['Nombre']);
    }
}
