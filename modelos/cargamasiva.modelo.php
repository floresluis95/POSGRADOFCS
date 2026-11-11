<?php
require_once __DIR__ . '/../core/conexion.modelo.php';
require_once __DIR__ . '/certificado.modelo.php';

/**
 * Carga masiva desde Excel de los estudiantes de un programa que ya terminó (para emitir sus
 * certificados sin inscribirlos uno por uno). Acordado con Luis (2026-11-11):
 *  - Una plantilla por programa: sus módulos ya creados son las columnas de notas.
 *  - La inscripción se registra como CARGA HISTÓRICA: pago completo, sin órdenes ni vouchers.
 *  - Trabajo de grado opcional (título, fechas, calificación, tribunal).
 *  - Si el C.I. ya existe se usa ese estudiante SIN cambiar sus datos; las notas o el trabajo
 *    de grado que ya estaban no se reemplazan (solo se avisa).
 *
 * El Excel se arma y se lee en el navegador (vistas/recursos/assets/js/scripts/cargamasiva.js);
 * aquí llegan las filas ya leídas. Analizar() valida sin escribir; con $aplicar = true registra
 * cada fila válida en su propia transacción (una fila con error no detiene a las demás).
 */
class CargaMasivaModelo
{
    const VOUCHER_HISTORICO = 'CARGA HISTÓRICA';
    const MAX_FILAS = 500;
    const EXPEDIDOS = ['LP' => 'LA PAZ', 'CB' => 'COCHABAMBA', 'SC' => 'SANTA CRUZ', 'OR' => 'ORURO', 'PT' => 'POTOSÍ',
        'CH' => 'CHUQUISACA', 'TJ' => 'TARIJA', 'BN' => 'BENI', 'PD' => 'PANDO', 'OTR' => 'OTRO'];

    /* Programas con sus módulos (para elegir de cuál se descarga la plantilla) */
    public static function ProgramasModelo()
    {
        return Conexion::Conectar()->query("
            SELECT p.ProgramaID, p.NombrePrograma, p.GradoAcademico, p.Version, p.Sede, p.FechaInicio,
                   (SELECT COUNT(*) FROM modulos m WHERE m.ProgramaId = p.ProgramaID) AS TotalModulos,
                   (SELECT COUNT(*) FROM estudianteprograma ep WHERE ep.ProgramaID = p.ProgramaID AND ep.Estado = 'ACTIVO') AS Inscritos
            FROM programa p
            ORDER BY p.FechaInicio DESC, p.NombrePrograma")->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Módulos del programa en orden de cursado (el mismo de los certificados) */
    public static function ModulosModelo($programaID)
    {
        $stmt = Conexion::Conectar()->prepare("
            SELECT Idmodulo, codigomodulo, nombremodulo FROM modulos WHERE ProgramaId = ? ORDER BY Idmodulo");
        $stmt->execute([(int) $programaID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Todo lo que necesita el navegador para armar la plantilla */
    public static function DatosPlantillaModelo($programaID)
    {
        $db = Conexion::Conectar();
        $stmt = $db->prepare("SELECT ProgramaID, NombrePrograma, GradoAcademico, Version, Sede FROM programa WHERE ProgramaID = ?");
        $stmt->execute([(int) $programaID]);
        $programa = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$programa) {
            return null;
        }
        return [
            'programa' => $programa,
            'modulos' => self::ModulosModelo($programaID),
            'profesiones' => $db->query("SELECT NombreProfesion FROM profesion ORDER BY NombreProfesion")->fetchAll(PDO::FETCH_COLUMN),
            'universidades' => $db->query("SELECT NombreUniversidad FROM universidad WHERE Estado = 1 ORDER BY NombreUniversidad")->fetchAll(PDO::FETCH_COLUMN),
            'expedidos' => array_keys(self::EXPEDIDOS),
        ];
    }

    /* ------------------------------------------------------------------ */

    /**
     * Valida (y si $aplicar, registra) las filas leídas del Excel.
     * @param array $filas Cada una: fila, ci, complemento, exp, nombre, apaterno, amaterno, fechaNacimiento,
     *                     correo, celular, profesion, universidad, fechaEgreso, fechaInscripcion,
     *                     notas {Idmodulo: nota}, titulo, fechaEntrega, fechaAprobacion, calificacionTesis,
     *                     presidente, tribunal1, tribunal2, secretaria
     * @return array ['success', 'mensaje'?, 'programa', 'filas' => [...], 'resumen' => [...]]
     */
    public static function Analizar($programaID, array $filas, $aplicar, $usuarioID)
    {
        $db = Conexion::Conectar();
        $stmt = $db->prepare("SELECT ProgramaID, NombrePrograma, Version FROM programa WHERE ProgramaID = ?");
        $stmt->execute([(int) $programaID]);
        $programa = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$programa) {
            return ['success' => false, 'mensaje' => 'El programa del Excel no existe en el sistema. Descargue la plantilla otra vez.'];
        }
        if (!$filas) {
            return ['success' => false, 'mensaje' => 'El Excel no tiene filas con datos'];
        }
        if (count($filas) > self::MAX_FILAS) {
            return ['success' => false, 'mensaje' => 'Como máximo ' . self::MAX_FILAS . ' estudiantes por archivo. Divida el Excel en partes'];
        }

        $modulos = [];
        foreach (self::ModulosModelo($programaID) as $m) {
            $modulos[(int) $m['Idmodulo']] = $m;
        }
        $profesiones = self::Catalogo($db, "SELECT IdProfesion, NombreProfesion FROM profesion");
        $universidades = self::Catalogo($db, "SELECT IdUniversidad, NombreUniversidad FROM universidad");

        $resultados = [];
        $ciVistos = [];
        $resumen = ['total' => count($filas), 'validas' => 0, 'conError' => 0, 'registradas' => 0,
            'estudiantesNuevos' => 0, 'inscripcionesNuevas' => 0, 'notas' => 0, 'trabajos' => 0];

        foreach ($filas as $f) {
            $r = self::AnalizarFila($db, (int) $programaID, $f, $modulos, $profesiones, $universidades, $ciVistos);
            if (!$r['errores']) {
                $resumen['validas']++;
                if ($aplicar) {
                    $error = self::RegistrarFila($db, (int) $programaID, $r['plan'], $usuarioID);
                    if ($error === null) {
                        $r['registrada'] = true;
                        $resumen['registradas']++;
                        $resumen['estudiantesNuevos'] += $r['plan']['estudianteID'] ? 0 : 1;
                        $resumen['inscripcionesNuevas'] += $r['plan']['inscripcionID'] ? 0 : 1;
                        $resumen['notas'] += count($r['plan']['notas']);
                        $resumen['trabajos'] += $r['plan']['trabajo'] ? 1 : 0;
                    } else {
                        $r['errores'][] = $error;
                    }
                }
            }
            if ($r['errores']) {
                $resumen['conError']++;
            }
            unset($r['plan']);
            $resultados[] = $r;
        }

        return ['success' => true, 'programa' => $programa, 'filas' => $resultados, 'resumen' => $resumen];
    }

    private static function AnalizarFila(PDO $db, $programaID, array $f, array $modulos, array $profesiones, array $universidades, array &$ciVistos)
    {
        $errores = [];
        $avisos = [];
        $txt = function ($clave, $largo) use ($f) {
            $v = trim(preg_replace('/\s+/u', ' ', (string) ($f[$clave] ?? '')));
            return mb_substr(mb_strtoupper($v, 'UTF-8'), 0, $largo);
        };

        $ci = preg_replace('/[\s.]/', '', (string) ($f['ci'] ?? ''));
        $ci = preg_replace('/\.0+$/', '', $ci); // 1234567.0 (celda numérica)
        $nombre = $txt('nombre', 50);
        $apaterno = $txt('apaterno', 50);
        $amaterno = $txt('amaterno', 50);
        $nombreCompleto = trim("$nombre $apaterno $amaterno");
        $res = ['fila' => (int) ($f['fila'] ?? 0), 'ci' => $ci, 'nombre' => $nombreCompleto, 'errores' => [], 'avisos' => [], 'acciones' => [], 'registrada' => false];

        if (!preg_match('/^\d{4,10}$/', $ci) || (int) $ci > 2147483647) {
            $errores[] = 'C.I. no válido (solo números, de 4 a 10 dígitos; el complemento va en su columna)';
        } elseif (isset($ciVistos[$ci])) {
            $errores[] = 'El C.I. ' . $ci . ' está repetido en el Excel (fila ' . $ciVistos[$ci] . ')';
        } else {
            $ciVistos[$ci] = $res['fila'];
        }

        // ¿Ya existe el estudiante?
        $existente = null;
        if (!$errores) {
            $stmt = $db->prepare("SELECT EstudianteID, Nombre, Apaterno, Amaterno FROM estudiante WHERE Ci = ? LIMIT 1");
            $stmt->execute([(int) $ci]);
            $existente = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        $plan = ['estudianteID' => $existente ? (int) $existente['EstudianteID'] : 0, 'estudiante' => null,
            'inscripcionID' => 0, 'fechaInscripcion' => null, 'notas' => [], 'trabajo' => null];

        if ($existente) {
            $nombreSistema = trim($existente['Nombre'] . ' ' . $existente['Apaterno'] . ' ' . $existente['Amaterno']);
            // Si el apellido no coincide, lo más probable es un C.I. mal escrito: no se mezcla
            $apellidosExcel = array_filter([self::Comparable($apaterno), self::Comparable($amaterno)]);
            $apellidosSistema = array_filter([self::Comparable($existente['Apaterno']), self::Comparable($existente['Amaterno'])]);
            if ($apellidosExcel && !array_intersect($apellidosExcel, $apellidosSistema)) {
                $errores[] = 'El C.I. ' . $ci . ' ya está registrado a nombre de ' . $nombreSistema . '. Revise el C.I. o el nombre';
            } else {
                $res['nombre'] = $nombreSistema;
                $res['acciones'][] = 'Estudiante ya registrado (no se cambian sus datos)';
            }
        } elseif (!$errores) {
            // Estudiante nuevo: datos obligatorios
            if ($nombre === '') $errores[] = 'Falta el nombre';
            if ($apaterno === '' && $amaterno === '') $errores[] = 'Falta al menos un apellido';
            $exp = strtoupper(trim((string) ($f['exp'] ?? '')));
            $expPorNombre = array_search(mb_strtoupper($exp, 'UTF-8'), self::EXPEDIDOS, true);
            if ($expPorNombre !== false) $exp = $expPorNombre;
            if (!isset(self::EXPEDIDOS[$exp])) $errores[] = 'Expedido no válido (use LP, CB, SC, OR, PT, CH, TJ, BN, PD u OTR)';

            $profesion = self::BuscarEnCatalogo($profesiones, $f['profesion'] ?? '');
            if (trim((string) ($f['profesion'] ?? '')) === '') {
                $errores[] = 'Falta la profesión';
            } elseif ($profesion === null) {
                $errores[] = 'La profesión "' . trim($f['profesion']) . '" no está en el catálogo del sistema (elíjala de la lista)';
            }
            $universidad = null;
            if (trim((string) ($f['universidad'] ?? '')) !== '') {
                $universidad = self::BuscarEnCatalogo($universidades, $f['universidad']);
                if ($universidad === null) $errores[] = 'La universidad "' . trim($f['universidad']) . '" no está en el catálogo del sistema (elíjala de la lista)';
            }

            $fechaNac = self::Fecha($f['fechaNacimiento'] ?? '', 'fecha de nacimiento', $errores);
            $fechaEgreso = self::Fecha($f['fechaEgreso'] ?? '', 'fecha de egreso', $errores);
            $correo = trim((string) ($f['correo'] ?? ''));
            if ($correo !== '') {
                if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || mb_strlen($correo) > 100) {
                    $errores[] = 'Correo no válido';
                } else {
                    $stmt = $db->prepare("SELECT CONCAT_WS(' ', Nombre, Apaterno, Amaterno) FROM estudiante WHERE Correo = ? LIMIT 1");
                    $stmt->execute([$correo]);
                    if ($otro = $stmt->fetchColumn()) $errores[] = 'El correo ' . $correo . ' ya es de ' . $otro;
                }
            }
            $celular = preg_replace('/\D/', '', (string) ($f['celular'] ?? ''));
            if ($celular !== '' && (strlen($celular) > 9 || (int) $celular > 2147483647)) {
                $errores[] = 'Celular no válido (hasta 9 dígitos)';
            }

            $plan['estudiante'] = [
                'Ci' => (int) $ci, 'Complemento' => mb_substr($txt('complemento', 5), 0, 5), 'Exp' => $exp,
                'Nombre' => $nombre, 'Apaterno' => $apaterno, 'Amaterno' => $amaterno,
                'FechaNacimiento' => $fechaNac, 'Edad' => $fechaNac ? (int) date_diff(date_create($fechaNac), date_create('today'))->y : 0,
                'Correo' => $correo !== '' ? $correo : null, 'IdProfesion' => $profesion,
                'IdUniversidad' => $universidad, 'FechaEgreso' => $fechaEgreso,
                'Celular' => $celular !== '' ? (int) $celular : 0,
            ];
            $res['acciones'][] = 'Estudiante nuevo';
        }

        // Inscripción al programa
        $plan['fechaInscripcion'] = self::Fecha($f['fechaInscripcion'] ?? '', 'fecha de inscripción', $errores);
        if (!$plan['fechaInscripcion'] && trim((string) ($f['fechaInscripcion'] ?? '')) === '') {
            $errores[] = 'Falta la fecha de inscripción';
        }
        if ($existente && !$errores) {
            $stmt = $db->prepare("SELECT idInscripcion, Estado FROM estudianteprograma WHERE EstudianteID = ? AND ProgramaID = ? ORDER BY Estado = 'ACTIVO' DESC LIMIT 1");
            $stmt->execute([$plan['estudianteID'], $programaID]);
            if ($insc = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($insc['Estado'] !== 'ACTIVO') {
                    $errores[] = 'Ya está inscrito en este programa con estado ' . $insc['Estado'] . ' (revíselo en Matriculados)';
                } else {
                    $plan['inscripcionID'] = (int) $insc['idInscripcion'];
                    $res['acciones'][] = 'Ya estaba inscrito en el programa';
                }
            }
        }
        if (!$plan['inscripcionID']) {
            $res['acciones'][] = 'Inscripción histórica (pago completo, sin vouchers)';
        }

        // Notas
        $notasExistentes = [];
        if ($plan['estudianteID']) {
            $stmt = $db->prepare("SELECT Idmodulo, Nota FROM calificacion WHERE EstudianteID = ? AND ProgramaId = ? AND Nota IS NOT NULL");
            $stmt->execute([$plan['estudianteID'], $programaID]);
            $notasExistentes = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        }
        $conservadas = [];
        foreach ((array) ($f['notas'] ?? []) as $idModulo => $valor) {
            $valor = trim(str_replace(',', '.', (string) $valor));
            if ($valor === '') continue;
            $idModulo = (int) $idModulo;
            if (!isset($modulos[$idModulo])) {
                $errores[] = 'Hay una columna de notas de un módulo que ya no existe en el programa. Descargue la plantilla otra vez';
                break;
            }
            $etiqueta = $modulos[$idModulo]['codigomodulo'] ?: $modulos[$idModulo]['nombremodulo'];
            if (!is_numeric($valor) || (float) $valor < 0 || (float) $valor > 100) {
                $errores[] = 'Nota no válida en ' . $etiqueta . ': "' . $valor . '" (de 0 a 100)';
                continue;
            }
            if (isset($notasExistentes[$idModulo])) {
                $conservadas[] = $etiqueta . ' (' . rtrim(rtrim($notasExistentes[$idModulo], '0'), '.') . ')';
                continue;
            }
            $plan['notas'][$idModulo] = round((float) $valor, 2);
        }
        if ($conservadas) {
            $avisos[] = 'Ya tenía nota en ' . implode(', ', $conservadas) . ': se conserva la del sistema';
        }
        $faltan = count($modulos) - count($plan['notas']) - count($notasExistentes);
        if ($faltan > 0) {
            $avisos[] = 'Sin nota en ' . $faltan . ' de ' . count($modulos) . ' módulos';
        }
        if ($plan['notas']) {
            $res['acciones'][] = count($plan['notas']) . ' nota' . (count($plan['notas']) === 1 ? '' : 's');
        }

        // Trabajo de grado (opcional)
        $titulo = CertificadoModelo::NormalizarTitulo($f['titulo'] ?? '');
        $tribunal = [];
        foreach (['presidente' => 'PRESIDENTE', 'tribunal1' => 'TRIBUNAL', 'tribunal2' => 'TRIBUNAL', 'secretaria' => 'SECRETARIA'] as $clave => $rol) {
            $miembro = trim(preg_replace('/\s+/u', ' ', (string) ($f[$clave] ?? '')));
            if ($miembro !== '') $tribunal[] = [$rol, mb_substr($miembro, 0, 150)];
        }
        $otrosTesis = trim(($f['fechaEntrega'] ?? '') . ($f['fechaAprobacion'] ?? '') . ($f['calificacionTesis'] ?? '')) !== '' || $tribunal;
        if ($titulo === '' && $otrosTesis) {
            $errores[] = 'Hay datos del trabajo de grado pero falta el título';
        } elseif ($titulo !== '') {
            $nombresTribunal = array_map(function ($m) { return self::Comparable($m[1]); }, $tribunal);
            if (count($nombresTribunal) !== count(array_unique($nombresTribunal))) {
                $errores[] = 'Una misma persona está dos veces en el tribunal';
            }
            $calif = trim(str_replace(',', '.', (string) ($f['calificacionTesis'] ?? '')));
            if ($calif !== '' && (!is_numeric($calif) || (float) $calif < 0 || (float) $calif > 100)) {
                $errores[] = 'Calificación del trabajo de grado no válida (de 0 a 100)';
            }
            $fechaEntrega = self::Fecha($f['fechaEntrega'] ?? '', 'fecha de entrega del trabajo', $errores);
            $fechaAprob = self::Fecha($f['fechaAprobacion'] ?? '', 'fecha de aprobación del trabajo', $errores);
            if (!$fechaAprob && trim((string) ($f['fechaAprobacion'] ?? '')) === '') {
                $errores[] = 'Falta la fecha de aprobación del trabajo de grado';
            }
            $yaTiene = false;
            if ($plan['estudianteID']) {
                $yaTiene = (bool) CertificadoModelo::ObtenerTrabajoGradoModelo($plan['estudianteID'], $programaID);
            }
            if ($yaTiene) {
                $avisos[] = 'Ya tenía trabajo de grado registrado: se conserva el del sistema';
            } else {
                $plan['trabajo'] = [
                    'titulo' => $titulo,
                    'tribunal' => implode('|', array_map(function ($m) { return $m[0] . ': ' . $m[1]; }, $tribunal)),
                    'calificacion' => $calif !== '' ? round((float) $calif, 2) : '',
                    'fechaEntrega' => $fechaEntrega, 'fechaAprobacion' => $fechaAprob,
                ];
                $res['acciones'][] = 'Trabajo de grado';
            }
        }

        if (!$plan['notas'] && !$plan['trabajo'] && $plan['inscripcionID'] && !$errores) {
            $avisos[] = 'No hay nada nuevo que registrar para este estudiante';
        }

        $res['errores'] = array_values(array_unique($errores));
        $res['avisos'] = $avisos;
        $res['plan'] = $plan;
        return $res;
    }

    /* Registra una fila ya validada. Devuelve null si todo salió bien o el mensaje de error */
    private static function RegistrarFila(PDO $db, $programaID, array $plan, $usuarioID)
    {
        try {
            $db->beginTransaction();
            $estudianteID = $plan['estudianteID'];
            if (!$estudianteID) {
                $e = $plan['estudiante'];
                $db->prepare("
                    INSERT INTO estudiante (Ci, Complemento, Exp, Nombre, Apaterno, Amaterno, FechaNacimiento, Edad, Lugarn,
                        Correo, IdProfesion, Trabajo, Direccion, Telefono, Celular, IdUniversidad, FechaEgreso, Estado)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, '', ?, ?, '', '', NULL, ?, ?, ?, 1)")
                   ->execute([$e['Ci'], $e['Complemento'], $e['Exp'], $e['Nombre'], $e['Apaterno'], $e['Amaterno'],
                       $e['FechaNacimiento'], $e['Edad'], $e['Correo'], $e['IdProfesion'], $e['Celular'],
                       $e['IdUniversidad'], $e['FechaEgreso']]);
                $estudianteID = (int) $db->lastInsertId();
            }
            if (!$plan['inscripcionID']) {
                $db->prepare("
                    INSERT INTO estudianteprograma (EstudianteID, ProgramaID, costomatricula, montoPagado, pagoCompleto,
                        porcentajeDescuento, montoDescuento, nvauchermatricula, FechaInscripcion, foto, Estado)
                    VALUES (?, ?, 0, 0, 1, 0, 0, ?, ?, NULL, 'ACTIVO')")
                   ->execute([$estudianteID, $programaID, self::VOUCHER_HISTORICO, $plan['fechaInscripcion']]);
            }
            if ($plan['notas']) {
                // INSERT IGNORE: si alguien registró la nota mientras tanto, se conserva la suya
                $stmt = $db->prepare("
                    INSERT IGNORE INTO calificacion (EstudianteID, ProgramaId, Idmodulo, Nota, estado, FechaRegistro, UsuarioRegistroID)
                    VALUES (?, ?, ?, ?, 'REGISTRADO', CURDATE(), ?)");
                foreach ($plan['notas'] as $idModulo => $nota) {
                    $stmt->execute([$estudianteID, $programaID, $idModulo, $nota, $usuarioID ?: null]);
                }
            }
            if ($plan['trabajo']) {
                $t = $plan['trabajo'];
                $db->prepare("
                    INSERT IGNORE INTO trabajogrado (EstudianteID, ProgramaID, TituloTrabajo, TipoTrabajo, Tribunal,
                        CalificacionNumeral, FechaEntrega, FechaAprobacion, UsuarioRegistroID)
                    VALUES (?, ?, ?, 'MEDIO FISICO Y DIGITAL DEL TRABAJO DE TESIS', ?, ?, ?, ?, ?)")
                   ->execute([$estudianteID, $programaID, $t['titulo'], $t['tribunal'] !== '' ? $t['tribunal'] : null,
                       $t['calificacion'] !== '' ? $t['calificacion'] : null, $t['fechaEntrega'], $t['fechaAprobacion'], $usuarioID ?: null]);
            }
            $db->commit();
            return null;
        } catch (PDOException $ex) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('CargaMasivaModelo::RegistrarFila: ' . $ex->getMessage());
            return $ex->getCode() === '23000'
                ? 'El C.I. o el correo ya se registraron para otro estudiante mientras tanto. Vuelva a revisar el Excel'
                : 'No se pudo registrar esta fila (error de la base de datos)';
        }
    }

    /* ---------------- Ayudantes ---------------- */

    /* "15/03/2024", "15-03-2024", "2024-03-15" → "2024-03-15"; vacío → null (con error si no se entiende) */
    private static function Fecha($valor, $nombre, array &$errores)
    {
        $valor = trim((string) $valor);
        if ($valor === '') return null;
        if (preg_match('/^(\d{1,2})[\/.-](\d{1,2})[\/.-](\d{4})$/', $valor, $m)) {
            list(, $d, $mes, $a) = $m;
        } elseif (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})/', $valor, $m)) {
            list(, $a, $mes, $d) = $m;
        } else {
            $errores[] = 'La ' . $nombre . ' "' . $valor . '" no es una fecha (use dd/mm/aaaa)';
            return null;
        }
        if (!checkdate((int) $mes, (int) $d, (int) $a) || $a < 1900 || $a > (int) date('Y') + 1) {
            $errores[] = 'La ' . $nombre . ' "' . $valor . '" no es una fecha válida';
            return null;
        }
        return sprintf('%04d-%02d-%02d', $a, $mes, $d);
    }

    /* Sin tildes, mayúsculas ni espacios de más (para comparar nombres y catálogos) */
    private static function Comparable($texto)
    {
        $t = mb_strtoupper(trim(preg_replace('/\s+/u', ' ', (string) $texto)), 'UTF-8');
        return strtr($t, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U']);
    }

    private static function Catalogo(PDO $db, $sql)
    {
        $lista = [];
        foreach ($db->query($sql)->fetchAll(PDO::FETCH_NUM) as $fila) {
            $lista[self::Comparable($fila[1])] = (int) $fila[0];
        }
        return $lista;
    }

    private static function BuscarEnCatalogo(array $catalogo, $nombre)
    {
        $clave = self::Comparable($nombre);
        return ($clave !== '' && isset($catalogo[$clave])) ? $catalogo[$clave] : null;
    }
}
