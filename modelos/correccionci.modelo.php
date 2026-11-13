<?php
require_once __DIR__ . '/../core/conexion.modelo.php';

/**
 * Corregir el C.I. mal escrito de un estudiante, docente o personal. Solo el ADM
 * (pedido de Luis 2026-11-13): en los formularios de edición el C.I. no se puede cambiar.
 *
 * Todo el sistema enlaza por ID (EstudianteID, DocenteID, IdPersonal), no por C.I., así
 * que basta con cambiar la columna. Lo único que lleva el C.I. copiado es la cuenta: si
 * su usuario era el C.I. anterior (como se crean), pasa a ser el nuevo, para que siga
 * entrando con su C.I.
 */
class CorreccionCiModelo
{
    const TIPOS = [
        'estudiante' => ['tabla' => 'estudiante', 'id' => 'EstudianteID', 'ci' => 'Ci',              'cuenta' => 'EstudianteID', 'etiqueta' => 'estudiante'],
        'docente'    => ['tabla' => 'docente',    'id' => 'DocenteID',    'ci' => 'Ci',              'cuenta' => 'DocenteID',    'etiqueta' => 'docente'],
        'personal'   => ['tabla' => 'personal',   'id' => 'IdPersonal',   'ci' => 'CedulaIdentidad', 'cuenta' => 'IdPersonal',   'etiqueta' => 'personal'],
    ];

    /**
     * @return array ['success' => bool, 'mensaje' => string, 'ci' => string]
     */
    public static function CorregirModelo($tipo, $id, $nuevoCi)
    {
        if (!isset(self::TIPOS[$tipo])) {
            return ['success' => false, 'mensaje' => 'Tipo no válido'];
        }
        $t = self::TIPOS[$tipo];
        $id = (int) $id;
        $nuevoCi = strtoupper(preg_replace('/[\s.]/', '', (string) $nuevoCi));

        $db = Conexion::Conectar();
        try {
            $db->beginTransaction();
            $stmt = $db->prepare("SELECT * FROM {$t['tabla']} WHERE {$t['id']} = ? FOR UPDATE");
            $stmt->execute([$id]);
            $registro = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$registro) {
                $db->rollBack();
                return ['success' => false, 'mensaje' => 'El registro ya no existe (recargue la página)'];
            }
            $anterior = trim((string) $registro[$t['ci']]);

            // Formato: el de los formularios de registro (docente extranjero: documento con letras)
            $extranjero = $tipo === 'docente' && (int) ($registro['EsExtranjero'] ?? 0) === 1;
            $valido = $extranjero ? preg_match('/^[A-Z0-9-]{4,20}$/', $nuevoCi) : preg_match('/^[0-9]{4,12}$/', $nuevoCi);
            if (!$valido) {
                $db->rollBack();
                return ['success' => false, 'mensaje' => $extranjero
                    ? 'Documento no válido (letras, números o guion; de 4 a 20 caracteres)'
                    : 'C.I. no válido (solo números, de 4 a 12 dígitos; el complemento va en su campo)'];
            }
            // estudiante.Ci es INT: un 0 adelante se perdería y más de 2147483647 falla en el servidor (MySQL estricto)
            if ($tipo === 'estudiante' && ($nuevoCi[0] === '0' || (float) $nuevoCi > 2147483647)) {
                $db->rollBack();
                return ['success' => false, 'mensaje' => 'C.I. no válido: no puede empezar con 0 ni pasar de 2147483647'];
            }
            if ($nuevoCi === $anterior) {
                $db->rollBack();
                return ['success' => false, 'mensaje' => 'Es el mismo C.I. que ya tiene'];
            }

            // Que no sea de otro registro del mismo tipo
            $stmt = $db->prepare("SELECT * FROM {$t['tabla']} WHERE TRIM({$t['ci']}) = ? AND {$t['id']} <> ? LIMIT 1");
            $stmt->execute([$nuevoCi, $id]);
            if ($otro = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $db->rollBack();
                return ['success' => false, 'mensaje' => 'El C.I. ' . $nuevoCi . ' ya pertenece a otro ' . $t['etiqueta'] . ': ' . self::Nombre($tipo, $otro)];
            }

            // Cuenta cuyo usuario es el C.I. anterior: pasa al nuevo si ese usuario está libre.
            // Si ya lo usa otra cuenta (p. ej. la misma persona es también personal), el C.I. se
            // corrige igual y el usuario se queda como estaba.
            $stmt = $db->prepare("SELECT ID FROM usuario WHERE {$t['cuenta']} = ? AND Usuario = ?");
            $stmt->execute([$id, $anterior]);
            $cuentas = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $avisoCuenta = '';
            if ($cuentas) {
                $stmt = $db->prepare("SELECT 1 FROM usuario WHERE Usuario = ? LIMIT 1");
                $stmt->execute([$nuevoCi]);
                if ($stmt->fetchColumn() !== false) {
                    $avisoCuenta = '. Su usuario sigue siendo ' . $anterior . ' porque ' . $nuevoCi . ' ya es el usuario de otra cuenta';
                } else {
                    $db->prepare("UPDATE usuario SET Usuario = ? WHERE ID IN (" . implode(',', array_map('intval', $cuentas)) . ")")
                       ->execute([$nuevoCi]);
                    $avisoCuenta = '. Ahora inicia sesión con el usuario ' . $nuevoCi . ' (la contraseña no cambia)';
                }
            }

            $db->prepare("UPDATE {$t['tabla']} SET {$t['ci']} = ? WHERE {$t['id']} = ?")->execute([$nuevoCi, $id]);
            $db->commit();

            return [
                'success' => true,
                'ci' => $nuevoCi,
                'mensaje' => 'C.I. corregido: ' . $anterior . ' → ' . $nuevoCi . $avisoCuenta,
            ];
        } catch (PDOException $e) {
            if ($db->inTransaction()) $db->rollBack();
            if ($e->getCode() === '23000') {
                return ['success' => false, 'mensaje' => 'El C.I. ' . $nuevoCi . ' ya está registrado'];
            }
            error_log('Error en CorreccionCiModelo::CorregirModelo: ' . $e->getMessage());
            return ['success' => false, 'mensaje' => 'Error en el servidor. Intente nuevamente'];
        }
    }

    private static function Nombre($tipo, array $r)
    {
        return $tipo === 'personal'
            ? trim($r['Nombres'] . ' ' . $r['ApellidoPaterno'] . ' ' . $r['ApellidoMaterno'])
            : trim($r['Nombre'] . ' ' . $r['Apaterno'] . ' ' . $r['Amaterno']);
    }
}
