<?php
require_once __DIR__ . '/../core/conexion.modelo.php';
require_once __DIR__ . '/universidad.modelo.php';

/**
 * Corrección de los catálogos que se llenan desde el formulario del estudiante con el
 * botón "+" (Universidades y Profesiones). Solo el ADM (pedido de Luis 2026-11-13):
 * la secretaría a veces los agrega con errores de tipeo y no había dónde corregirlos.
 *
 * - Editar: corrige el nombre (y el tipo/estado de la universidad).
 * - Unir: cuando el nombre correcto ya existe (duplicado por tipeo), pasa los estudiantes
 *   del registro mal escrito al correcto y borra el mal escrito.
 * - Eliminar: solo si ningún estudiante lo usa.
 * Sin cambios de estructura: profesion no tiene Estado, por eso no se "desactiva".
 */
class CatalogosModelo
{
    // Tabla, clave, columna del nombre y largo de cada catálogo (columnas fijas, no vienen del usuario)
    const CATALOGOS = [
        'universidad' => ['tabla' => 'universidad', 'id' => 'IdUniversidad', 'nombre' => 'NombreUniversidad', 'largo' => 150, 'etiqueta' => 'universidad'],
        'profesion'   => ['tabla' => 'profesion',   'id' => 'IdProfesion',   'nombre' => 'NombreProfesion',   'largo' => 100, 'etiqueta' => 'profesión'],
    ];
    const TIPOS_UNIVERSIDAD = ['PUBLICA', 'PRIVADA', 'EXTRANJERA', 'CONVENIO'];

    private static function Config($catalogo)
    {
        if (!isset(self::CATALOGOS[$catalogo])) {
            throw new InvalidArgumentException('Catálogo no válido');
        }
        return self::CATALOGOS[$catalogo];
    }

    /**
     * Nombre como lo guarda el botón "+" (normalizarNombreCatalogo de ordenpago.ajax.php):
     * sin espacios de más, en MAYÚSCULAS y con htmlspecialchars.
     */
    public static function NormalizarNombre($nombre)
    {
        $nombre = html_entity_decode(trim((string) $nombre), ENT_QUOTES, 'UTF-8');
        return htmlspecialchars(mb_strtoupper(preg_replace('/\s+/u', ' ', $nombre), 'UTF-8'));
    }

    /** Lista con cuántos estudiantes usan cada registro */
    public static function ListarModelo($catalogo)
    {
        $c = self::Config($catalogo);
        $extra = $catalogo === 'universidad' ? ', t.TipoUniversidad, t.Estado' : ', 1 AS Estado';
        $stmt = Conexion::Conectar()->prepare("
            SELECT t.{$c['id']} AS Id, t.{$c['nombre']} AS Nombre{$extra},
                   (SELECT COUNT(*) FROM estudiante e WHERE e.{$c['id']} = t.{$c['id']}) AS Estudiantes
            FROM {$c['tabla']} t
            ORDER BY t.{$c['nombre']}");
        $stmt->execute();
        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as &$fila) {
            // En pantalla y en el formulario, el texto tal cual (la vista lo escapa)
            $fila['Nombre'] = html_entity_decode($fila['Nombre'], ENT_QUOTES, 'UTF-8');
        }
        return $filas;
    }

    private static function Obtener(PDO $db, array $c, $id, $bloquear = false)
    {
        $stmt = $db->prepare("SELECT * FROM {$c['tabla']} WHERE {$c['id']} = ?" . ($bloquear ? ' FOR UPDATE' : ''));
        $stmt->execute([(int) $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Otro registro que ya tiene ese nombre (exacto, o la misma universidad escrita de otra forma).
     * @return array|false
     */
    private static function Repetido(PDO $db, $catalogo, array $c, $nombre, $id)
    {
        $stmt = $db->prepare("SELECT * FROM {$c['tabla']} WHERE {$c['nombre']} = ? AND {$c['id']} <> ?");
        $stmt->execute([$nombre, (int) $id]);
        if ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $fila;
        }
        if ($catalogo === 'universidad') {
            $parecida = UniversidadModelos::BuscarParecidaModelo($nombre);
            if ($parecida && (int) $parecida['IdUniversidad'] !== (int) $id) {
                return $parecida;
            }
        }
        return false;
    }

    /**
     * Corrige nombre (y para universidad: tipo y estado).
     * @return array ['success' => bool, 'mensaje' => string, 'repetidoId' => int|null]
     */
    public static function EditarModelo($catalogo, $id, $nombre, $tipo = null, $estado = null)
    {
        $c = self::Config($catalogo);
        $nombre = self::NormalizarNombre($nombre);
        if ($nombre === '') {
            return ['success' => false, 'mensaje' => 'Escriba el nombre'];
        }
        if (mb_strlen($nombre, 'UTF-8') > $c['largo']) {
            return ['success' => false, 'mensaje' => 'El nombre no puede pasar de ' . $c['largo'] . ' caracteres'];
        }
        if ($catalogo === 'universidad' && !in_array($tipo, self::TIPOS_UNIVERSIDAD, true)) {
            return ['success' => false, 'mensaje' => 'Elija el tipo de universidad'];
        }

        $db = Conexion::Conectar();
        if (!self::Obtener($db, $c, $id)) {
            return ['success' => false, 'mensaje' => 'El registro ya no existe (recargue la página)'];
        }
        if ($repetido = self::Repetido($db, $catalogo, $c, $nombre, $id)) {
            return [
                'success' => false,
                'mensaje' => 'Ya existe "' . $repetido[$c['nombre']] . '". Si es la misma ' . $c['etiqueta']
                    . ', use "Unir con..." para pasar sus estudiantes a esa y quitar la mal escrita.',
                'repetidoId' => (int) $repetido[$c['id']],
            ];
        }

        try {
            if ($catalogo === 'universidad') {
                $db->prepare("UPDATE universidad SET NombreUniversidad = ?, TipoUniversidad = ?, Estado = ? WHERE IdUniversidad = ?")
                   ->execute([$nombre, $tipo, (int) $estado === 0 ? 0 : 1, (int) $id]);
            } else {
                $db->prepare("UPDATE profesion SET NombreProfesion = ? WHERE IdProfesion = ?")
                   ->execute([$nombre, (int) $id]);
            }
            return ['success' => true, 'mensaje' => 'Corregido'];
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                return ['success' => false, 'mensaje' => 'Ya existe otra ' . $c['etiqueta'] . ' con ese nombre: use "Unir con..."'];
            }
            error_log('Error en CatalogosModelo::EditarModelo: ' . $e->getMessage());
            return ['success' => false, 'mensaje' => 'Error en el servidor. Intente nuevamente'];
        }
    }

    /**
     * Pasa los estudiantes de $origenId a $destinoId y borra $origenId (duplicado por tipeo).
     */
    public static function UnirModelo($catalogo, $origenId, $destinoId)
    {
        $c = self::Config($catalogo);
        $origenId = (int) $origenId;
        $destinoId = (int) $destinoId;
        if ($origenId <= 0 || $destinoId <= 0 || $origenId === $destinoId) {
            return ['success' => false, 'mensaje' => 'Elija la ' . $c['etiqueta'] . ' correcta (distinta de la que se quita)'];
        }

        $db = Conexion::Conectar();
        try {
            $db->beginTransaction();
            $origen = self::Obtener($db, $c, $origenId, true);
            $destino = self::Obtener($db, $c, $destinoId, true);
            if (!$origen || !$destino) {
                $db->rollBack();
                return ['success' => false, 'mensaje' => 'Uno de los registros ya no existe (recargue la página)'];
            }
            $stmt = $db->prepare("UPDATE estudiante SET {$c['id']} = ? WHERE {$c['id']} = ?");
            $stmt->execute([$destinoId, $origenId]);
            $movidos = $stmt->rowCount();
            if ($catalogo === 'universidad' && (int) $destino['Estado'] !== 1) {
                // La que queda se usa: que vuelva a aparecer en las listas
                $db->prepare("UPDATE universidad SET Estado = 1 WHERE IdUniversidad = ?")->execute([$destinoId]);
            }
            $db->prepare("DELETE FROM {$c['tabla']} WHERE {$c['id']} = ?")->execute([$origenId]);
            $db->commit();
            return [
                'success' => true,
                'mensaje' => '"' . $origen[$c['nombre']] . '" se unió con "' . $destino[$c['nombre']] . '"'
                    . ($movidos ? ' (' . $movidos . ' estudiante' . ($movidos === 1 ? '' : 's') . ' actualizado' . ($movidos === 1 ? '' : 's') . ')' : ''),
            ];
        } catch (PDOException $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('Error en CatalogosModelo::UnirModelo: ' . $e->getMessage());
            return ['success' => false, 'mensaje' => 'Error en el servidor. Intente nuevamente'];
        }
    }

    /** Borra un registro que ningún estudiante usa */
    public static function EliminarModelo($catalogo, $id)
    {
        $c = self::Config($catalogo);
        $db = Conexion::Conectar();
        try {
            $db->beginTransaction();
            $fila = self::Obtener($db, $c, $id, true);
            if (!$fila) {
                $db->rollBack();
                return ['success' => false, 'mensaje' => 'El registro ya no existe (recargue la página)'];
            }
            $stmt = $db->prepare("SELECT COUNT(*) FROM estudiante WHERE {$c['id']} = ?");
            $stmt->execute([(int) $id]);
            if ((int) $stmt->fetchColumn() > 0) {
                $db->rollBack();
                return ['success' => false, 'mensaje' => 'Hay estudiantes con esta ' . $c['etiqueta'] . ': use "Unir con..." para pasarlos a la correcta'];
            }
            $db->prepare("DELETE FROM {$c['tabla']} WHERE {$c['id']} = ?")->execute([(int) $id]);
            $db->commit();
            return ['success' => true, 'mensaje' => '"' . $fila[$c['nombre']] . '" eliminada'];
        } catch (PDOException $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('Error en CatalogosModelo::EliminarModelo: ' . $e->getMessage());
            return ['success' => false, 'mensaje' => 'Error en el servidor. Intente nuevamente'];
        }
    }
}
