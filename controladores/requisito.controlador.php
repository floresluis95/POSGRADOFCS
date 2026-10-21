<?php
/**
 * Controlador de Requisitos de Matriculación
 * Lo usan Matriculados (ventana "Requisitos") e Inscripción (al validar el
 * voucher se marcan los requisitos que el estudiante trajo ese día).
 */

require_once __DIR__ . '/../modelos/requisito.modelo.php';
require_once __DIR__ . '/../modelos/certificado.modelo.php'; // certificados exigidos para la titulación

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

    /**
     * Al validar el voucher de matrícula: marcar como entregados los requisitos
     * automáticos (los genera el sistema: ficha, documento de compromiso...).
     * @param string|null $fecha Fecha de inscripción (AAAA-MM-DD); hoy si no viene
     */
    public static function MarcarAutomaticosControlador($idInscripcion, $fecha, $responsable)
    {
        $fecha = ($fecha && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) ? $fecha : date('Y-m-d');
        return RequisitoModelo::MarcarAutomaticosModelo((int)$idInscripcion, $fecha, $responsable);
    }

    /**
     * Crear o editar un requisito del catálogo.
     * @param array $entrada idRequisito, nombre, obligatorio ('1'/'0'), automatico ('1'/'0'), orden,
     *                       grados[] (DOCTORADO, MAESTRIA...; vacío = todos los grados)
     */
    public static function GuardarCatalogoControlador($entrada)
    {
        $idRequisito = (int)($entrada['idRequisito'] ?? 0);
        $nombre = trim(preg_replace('/\s+/', ' ', $entrada['nombre'] ?? ''));
        $orden = trim($entrada['orden'] ?? '');

        if ($nombre === '') {
            return ['success' => false, 'mensaje' => 'Ingrese el nombre del requisito'];
        }
        if (mb_strlen($nombre, 'UTF-8') > 150) {
            return ['success' => false, 'mensaje' => 'El nombre no puede pasar de 150 caracteres'];
        }
        if ($orden !== '' && (!ctype_digit($orden) || (int)$orden > 999)) {
            return ['success' => false, 'mensaje' => 'El orden debe ser un número entre 0 y 999'];
        }
        $grados = array_values(array_intersect(RequisitoModelo::GRADOS, (array)($entrada['grados'] ?? [])));
        if (count($grados) === count(RequisitoModelo::GRADOS)) {
            $grados = []; // marcar todos es lo mismo que "todos los grados"
        }
        if (RequisitoModelo::ExisteNombreModelo($nombre, $idRequisito)) {
            return ['success' => false, 'mensaje' => 'Ya existe un requisito con ese nombre'];
        }

        $ok = RequisitoModelo::GuardarCatalogoModelo(
            $idRequisito,
            $nombre,
            ($entrada['obligatorio'] ?? '0') === '1',
            ($entrada['automatico'] ?? '0') === '1',
            $orden === '' ? null : (int)$orden,
            $grados
        );

        return $ok
            ? ['success' => true, 'mensaje' => $idRequisito > 0 ? 'Requisito actualizado' : 'Requisito agregado']
            : ['success' => false, 'mensaje' => 'No se pudo guardar el requisito'];
    }

    public static function CambiarEstadoControlador($idRequisito, $estado)
    {
        $idRequisito = (int)$idRequisito;
        if ($idRequisito <= 0) {
            return ['success' => false, 'mensaje' => 'Requisito no válido'];
        }
        $activar = $estado === '1';
        return RequisitoModelo::CambiarEstadoModelo($idRequisito, $activar)
            ? ['success' => true, 'mensaje' => $activar ? 'Requisito activado' : 'Requisito desactivado']
            : ['success' => false, 'mensaje' => 'No se pudo cambiar el estado'];
    }

    /**
     * Requisitos del estudiante en sesión, agrupados por programa (solo lectura).
     */
    public static function MisRequisitosControlador($estudianteID)
    {
        $programas = [];
        foreach (RequisitoModelo::ListarInscripcionesEstudianteModelo($estudianteID) as $inscripcion) {
            $requisitos = RequisitoModelo::ListarPorInscripcionModelo($inscripcion['idInscripcion']);
            // Al estudiante no se le muestra quién registró la entrega
            $requisitos = array_map(function ($r) {
                return [
                    'Nombre' => $r['Nombre'],
                    'Obligatorio' => (int)$r['Obligatorio'],
                    'Entregado' => (int)$r['Entregado'],
                    'FechaEntrega' => $r['FechaEntrega'],
                    'Observacion' => $r['Observacion'],
                ];
            }, $requisitos);

            // Certificados que su grado exige para titularse y cuáles ya pagó en este
            // programa (solo nombre, precio, fecha y voucher: no puede verlos ni imprimirlos)
            try {
                $titulacion = CertificadoModelo::EstadoTitulacionModelo($estudianteID, $inscripcion['ProgramaID']);
            } catch (PDOException $e) {
                error_log('MisRequisitos (titulación): ' . $e->getMessage());
                $titulacion = ['total' => 0, 'cumplidos' => 0, 'completo' => false, 'certificados' => []];
            }

            $programas[] = [
                'idInscripcion' => (int)$inscripcion['idInscripcion'],
                'programa' => $inscripcion['NombrePrograma'],
                'grado' => $inscripcion['GradoAcademico'],
                'fechaInscripcion' => $inscripcion['FechaInscripcion'],
                'situacion' => self::SituacionPrograma($inscripcion),
                'requisitos' => $requisitos,
                'titulacion' => $titulacion,
            ] + self::Resumen($requisitos);
        }

        // Primero los programas en curso, luego culminados y abandonados
        // (dentro de cada grupo, el más reciente primero: ya viene así).
        $prioridad = ['EN CURSO' => 0, 'CULMINADO' => 1, 'ABANDONO' => 2];
        usort($programas, function ($a, $b) use ($prioridad) {
            return $prioridad[$a['situacion']] <=> $prioridad[$b['situacion']];
        });

        return ['success' => true, 'programas' => $programas];
    }

    /**
     * Situación del programa para el estudiante: ABANDONO si así se marcó la
     * inscripción; CULMINADO si el programa ya tiene todos sus módulos con fecha
     * y el último terminó; si no, EN CURSO.
     */
    private static function SituacionPrograma(array $inscripcion)
    {
        if ($inscripcion['Estado'] === 'ABANDONO') {
            return 'ABANDONO';
        }
        $todosConFecha = (int)$inscripcion['ModulosConFecha'] > 0
            && (int)$inscripcion['ModulosConFecha'] >= (int)$inscripcion['ModulosPrograma'];
        if ($todosConFecha && $inscripcion['FinUltimoModulo'] && $inscripcion['FinUltimoModulo'] < date('Y-m-d')) {
            return 'CULMINADO';
        }
        return 'EN CURSO';
    }

    public static function UsuarioSesion()
    {
        $nombre = trim(($_SESSION['Nombres'] ?? '') . ' ' . ($_SESSION['ApellidoPaterno'] ?? ''));
        return $nombre !== '' ? $nombre : ($_SESSION['Usuario'] ?? null);
    }
}
