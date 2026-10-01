<?php
/**
 * Controlador de Requisitos de Matriculación
 * Lo usan Matriculados (ventana "Requisitos") e Inscripción (al validar el
 * voucher se marcan los requisitos que el estudiante trajo ese día).
 */

require_once __DIR__ . '/../modelos/requisito.modelo.php';

class RequisitoControlador
{
    /**
     * Requisitos de una inscripción, con el resumen de entregados.
     * @return array
     */
    public static function ObtenerControlador($idInscripcion)
    {
        $idInscripcion = (int)$idInscripcion;
        if ($idInscripcion <= 0 || !RequisitoModelo::ExisteInscripcionModelo($idInscripcion)) {
            return ['success' => false, 'mensaje' => 'La inscripción no existe'];
        }

        $requisitos = RequisitoModelo::ListarPorInscripcionModelo($idInscripcion);
        return ['success' => true, 'requisitos' => $requisitos] + self::Resumen($requisitos);
    }

    /**
     * Guardar los requisitos marcados de una inscripción.
     * @param array $entrada [IdRequisito => ['entregado' => '1'|'0', 'fecha' => 'Y-m-d', 'observacion' => texto]]
     * @return array
     */
    public static function GuardarControlador($idInscripcion, $entrada, $responsable)
    {
        $idInscripcion = (int)$idInscripcion;
        if ($idInscripcion <= 0 || !RequisitoModelo::ExisteInscripcionModelo($idInscripcion)) {
            return ['success' => false, 'mensaje' => 'La inscripción no existe'];
        }

        $requisitos = self::NormalizarEntrada($entrada);
        if ($requisitos === null) {
            return ['success' => false, 'mensaje' => 'Hay una fecha de entrega no válida'];
        }
        if (!RequisitoModelo::GuardarModelo($idInscripcion, $requisitos, $responsable)) {
            return ['success' => false, 'mensaje' => 'No se pudieron guardar los requisitos'];
        }

        $resumen = self::Resumen(RequisitoModelo::ListarPorInscripcionModelo($idInscripcion));
        return ['success' => true, 'mensaje' => 'Requisitos guardados'] + $resumen;
    }

    /**
     * Valida y limpia lo que llega del formulario. Devuelve null si alguna
     * fecha no tiene el formato AAAA-MM-DD o es futura.
     * @return array|null
     */
    public static function NormalizarEntrada($entrada)
    {
        $requisitos = [];
        foreach ((array)$entrada as $idRequisito => $datos) {
            $idRequisito = (int)$idRequisito;
            if ($idRequisito <= 0 || !is_array($datos)) {
                continue;
            }
            $fecha = trim($datos['fecha'] ?? '');
            if ($fecha !== '' && (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || $fecha > date('Y-m-d'))) {
                return null;
            }
            $requisitos[$idRequisito] = [
                'entregado' => ($datos['entregado'] ?? '0') === '1',
                'fecha' => $fecha !== '' ? $fecha : null,
                'observacion' => mb_substr(trim($datos['observacion'] ?? ''), 0, 255),
            ];
        }
        return $requisitos;
    }

    /**
     * Cuántos requisitos obligatorios se entregaron y cuántos hay.
     * @return array ['entregados' => int, 'total' => int, 'completo' => bool]
     */
    private static function Resumen(array $requisitos)
    {
        $obligatorios = array_filter($requisitos, function ($r) { return (int)$r['Obligatorio'] === 1; });
        $entregados = count(array_filter($obligatorios, function ($r) { return (int)$r['Entregado'] === 1; }));
        return [
            'entregados' => $entregados,
            'total' => count($obligatorios),
            'completo' => $entregados === count($obligatorios),
        ];
    }

    public static function UsuarioSesion()
    {
        $nombre = trim(($_SESSION['Nombres'] ?? '') . ' ' . ($_SESSION['ApellidoPaterno'] ?? ''));
        return $nombre !== '' ? $nombre : ($_SESSION['Usuario'] ?? null);
    }
}
