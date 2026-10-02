<?php
/**
 * Controlador de Módulos
 * Gestiona el registro y consulta de módulos por programa
 */

require_once __DIR__ . '/../modelos/modulo.modelo.php';
require_once file_exists(__DIR__ . '/../modelos/conexion.modelo.php') ? __DIR__ . '/../modelos/conexion.modelo.php' : __DIR__ . '/../core/conexion.modelo.php';

class ModuloControlador
{
    // Debe coincidir con modulos.nombremodulo VARCHAR(255) (ver mantenimiento/ampliar_nombremodulo.sql)
    const LARGO_MAXIMO_NOMBRE = 255;

    /**
     * Deja el nombre del módulo listo para guardar: sin espacios repetidos y en
     * mayúsculas. Se usa mb_strtoupper porque strtoupper no convierte las letras
     * con tilde ("ácido" quedaba "áCIDO" en el historial y los certificados).
     */
    private static function NormalizarNombreModulo($texto)
    {
        $texto = preg_replace('/\s+/u', ' ', trim($texto));
        return htmlspecialchars(mb_strtoupper($texto, 'UTF-8'));
    }

    // Quita tildes para comparar palabras ("HIALURONICO" y "HIALURÓNICO" cuentan como la misma)
    private static function SinTildes($palabra)
    {
        return strtr($palabra, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U']);
    }

    /**
     * Revisa los nombres que se van a guardar y sugiere correcciones comparando
     * cada palabra con las que ya aparecen en otros módulos y programas.
     * Detecta letras de más/de menos/cambiadas ("ODNTOLOGIA" -> "ODONTOLOGÍA")
     * y tildes que faltan. Solo sugiere: el usuario decide si guarda igual.
     *
     * @param array $nombres textos tal como los escribió el usuario
     * @param int|null $excluirIdModulo módulo que se está editando
     * @return array [['nombre' => ..., 'palabra' => ..., 'sugerencia' => ...], ...]
     */
    public static function SugerirCorreccionesNombres($nombres, $excluirIdModulo = null)
    {
        $separarPalabras = function ($texto) {
            $texto = mb_strtoupper(html_entity_decode($texto, ENT_QUOTES, 'UTF-8'), 'UTF-8');
            return preg_split('/[^\p{L}]+/u', $texto, -1, PREG_SPLIT_NO_EMPTY);
        };

        // Vocabulario: cuántas veces aparece cada palabra en lo ya registrado
        $frecuencia = [];
        foreach (ModuloModelo::ListarNombresRegistradosModelo($excluirIdModulo) as $registrado) {
            foreach ($separarPalabras($registrado) as $palabra) {
                $frecuencia[$palabra] = ($frecuencia[$palabra] ?? 0) + 1;
            }
        }

        $sugerencias = [];
        foreach ($nombres as $nombre) {
            foreach (array_unique($separarPalabras($nombre)) as $palabra) {
                $largo = mb_strlen($palabra, 'UTF-8');
                if ($largo < 6) {
                    continue; // palabras cortas ("DE", "LA", "MEDIO") dan demasiadas falsas alarmas
                }

                $vecesPalabra = $frecuencia[$palabra] ?? 0;
                $maxDiferencia = $largo >= 9 ? 2 : 1;
                $palabraBase = self::SinTildes($palabra);
                $mejor = null;

                foreach ($frecuencia as $conocida => $veces) {
                    // Solo se sugiere una palabra que se usa más que la escrita
                    if ($conocida === $palabra || $veces <= $vecesPalabra) {
                        continue;
                    }
                    $conocidaBase = self::SinTildes($conocida);
                    if (abs(strlen($conocidaBase) - strlen($palabraBase)) > $maxDiferencia) {
                        continue;
                    }
                    // Singular/plural no es un error ("ADIPOSO" / "ADIPOSOS")
                    if (in_array($conocidaBase, [$palabraBase . 'S', $palabraBase . 'ES'], true)
                        || in_array($palabraBase, [$conocidaBase . 'S', $conocidaBase . 'ES'], true)) {
                        continue;
                    }
                    // Si solo falta la tilde basta con que la palabra se haya usado una vez;
                    // si cambian letras, debe usarse al menos 2 veces, para no proponer
                    // como "correcta" una palabra que quedó mal escrita en un único módulo.
                    $diferencia = levenshtein($palabraBase, $conocidaBase);
                    if ($diferencia > 0 && $veces < 2) {
                        continue;
                    }
                    if ($diferencia <= $maxDiferencia && ($mejor === null || [$diferencia, -$veces] < [$mejor[1], -$mejor[2]])) {
                        $mejor = [$conocida, $diferencia, $veces];
                    }
                }

                if ($mejor !== null) {
                    $sugerencias[] = ['nombre' => $nombre, 'palabra' => $palabra, 'sugerencia' => $mejor[0]];
                }
            }
        }

        return $sugerencias;
    }

    public function SugerirCorreccionesNombresAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');
        $nombres = isset($_POST['nombres']) && is_array($_POST['nombres']) ? $_POST['nombres'] : [];
        $excluir = !empty($_POST['idmodulo']) ? (int)$_POST['idmodulo'] : null;
        echo json_encode(['sugerencias' => self::SugerirCorreccionesNombres($nombres, $excluir)], JSON_INVALID_UTF8_SUBSTITUTE);
        exit();
    }

    /**
     * Registrar módulos de un programa (por inscripción)
     */
    public function RegistrarModulosControlador()
    {
        if (isset($_POST["registrarModulos"])) {
            // Validar datos requeridos
            if (empty($_POST['idinscripcion']) || empty($_POST['numModulos'])) {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "Faltan datos obligatorios", "error")
                .then(function () {
                    location.href="modulos";
                });
                </script>';
                return;
            }

            $idinscripcion = (int)$_POST['idinscripcion'];
            $numModulos = (int)$_POST['numModulos'];
            $modulos = [];

            // Recopilar módulos del formulario
            for ($i = 1; $i <= $numModulos; $i++) {
                $nombreKey = 'nombremodulo_' . $i;
                $codigoKey = 'codigomodulo_' . $i;

                if (isset($_POST[$nombreKey]) && isset($_POST[$codigoKey])) {
                    $nombremodulo = self::NormalizarNombreModulo($_POST[$nombreKey]);
                    $codigomodulo = (int)$_POST[$codigoKey];

                    if (!empty($nombremodulo) && $codigomodulo > 0) {
                        $modulos[] = [
                            'nombremodulo' => $nombremodulo,
                            'codigomodulo' => $codigomodulo
                        ];
                    }
                }
            }

            if (empty($modulos)) {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "Debe llenar al menos un módulo", "error")
                .then(function () {
                    location.href="modulos";
                });
                </script>';
                return;
            }

            // Preparar datos para el modelo
            $datosModulos = [
                'idinscripcion' => $idinscripcion,
                'modulos' => $modulos
            ];

            // Registrar en la base de datos
            $resultado = ModuloModelo::RegistrarModulosModelo($datosModulos);

            if ($resultado == 'exitoso') {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("EXITOSO!", "Los módulos se registraron correctamente (' . count($modulos) . ' módulos)", "success")
                .then(function () {
                    location.href="modulos";
                });
                </script>';
            } elseif ($resultado == 'duplicado') {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "Ya existen módulos registrados para esta inscripción", "error")
                .then(function () {
                    location.href="modulos";
                });
                </script>';
            } else {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "No se pudieron registrar los módulos", "error")
                .then(function () {
                    location.href="modulos";
                });
                </script>';
            }
        }
    }

    /**
     * Listar módulos en tabla HTML (por inscripción)
     */
    public function ListarModulosControlador()
    {
        $modulos = ModuloModelo::ListarTodosModulosModelo();

        if (empty($modulos)) {
            echo '<tr>
                    <td colspan="7" class="text-center">No hay módulos registrados</td>
                  </tr>';
            return;
        }

        foreach ($modulos as $key => $modulo) {
            $nombreCompleto = $modulo['Nombre'] . ' ' . $modulo['Apaterno'] . ' ' . $modulo['Amaterno'];
            $estadoBadge = $modulo['estadomodulo'] == 'ACTIVO' ? 'badge-success' : 'badge-secondary';

            echo '<tr>
                    <td class="text-center"><strong>' . ($key + 1) . '</strong></td>
                    <td>' . htmlspecialchars($modulo['codigomodulo']) . '</td>
                    <td><strong>' . htmlspecialchars($modulo['nombremodulo']) . '</strong></td>
                    <td>' . htmlspecialchars($nombreCompleto) . '</td>
                    <td><span class="badge badge-secondary">' . htmlspecialchars($modulo['Ci']) . '</span></td>
                    <td>' . htmlspecialchars($modulo['NombrePrograma']) . ' (' . htmlspecialchars($modulo['CodigoPrograma']) . ')</td>
                    <td class="text-center">
                        <span class="badge ' . $estadoBadge . '">' . htmlspecialchars($modulo['estadomodulo']) . '</span>
                    </td>
                  </tr>';
        }
    }

    /**
     * Registrar módulos directamente por ProgramaId
     */
    public function RegistrarModulosPorProgramaControlador()
    {
        if (!isset($_POST["registrarModulosPorPrograma"])) {
            return;
        }

        // Fallback sin JavaScript: procesa el POST tradicional y recarga la página.
        // Cuando hay JavaScript disponible, el modal usa RegistrarModulosPorProgramaAjaxControlador().
        $resultado = $this->ProcesarRegistroModulosPorPrograma();

        if ($resultado['estado'] === 'validacion') {
            echo '
            <script src="vistas/recursos/sweetalert.min.js"></script>
            <script>
            swal("ERROR!", "' . $resultado['mensaje'] . '", "error")
            .then(function () {
                location.href="modulos";
            });
            </script>';
            return;
        }

        if ($resultado['estado'] === 'exitoso') {
            echo '
            <script src="vistas/recursos/sweetalert.min.js"></script>
            <script>
            sessionStorage.setItem("programaIDSeleccionado", "' . $resultado['programaID'] . '");
            swal("EXITOSO!", "' . $resultado['mensaje'] . '", "success")
            .then(function () {
                location.href="modulos";
            });
            </script>';
        } else {
            echo '
            <script src="vistas/recursos/sweetalert.min.js"></script>
            <script>
            swal("ERROR!", "' . $resultado['mensaje'] . '", "error")
            .then(function () {
                location.href="modulos";
            });
            </script>';
        }
    }

    /**
     * Registrar módulos por programa vía AJAX: responde JSON en vez de
     * redirigir, para que el modal se cierre y la tabla se refresque
     * sin recargar la página.
     */
    public function RegistrarModulosPorProgramaAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->ProcesarRegistroModulosPorPrograma());
        exit();
    }

    /**
     * Lógica compartida de validación y registro de módulos por programa.
     * Devuelve ['estado' => 'exitoso'|'duplicado'|'error'|'validacion', 'mensaje' => ..., 'programaID' => ...]
     */
    private function ProcesarRegistroModulosPorPrograma()
    {
        if (empty($_POST['programaID']) || empty($_POST['totalModulos'])) {
            return ['estado' => 'validacion', 'mensaje' => 'Faltan datos obligatorios'];
        }

        $programaID = (int)$_POST['programaID'];
        $totalModulos = (int)$_POST['totalModulos'];
        $modulos = [];

        // Recopilar módulos del formulario
        for ($i = 1; $i <= $totalModulos; $i++) {
            $nombreKey = 'nombremodulo_' . $i;
            $codigoKey = 'codigomodulo_' . $i;
            $docenteKey = 'docentemodulo_' . $i;
            $fechaInicioKey = 'fechainicio_' . $i;
            $fechaFinKey = 'fechafin_' . $i;

            if (isset($_POST[$codigoKey])) {
                $codigomodulo = strtoupper(htmlspecialchars(trim($_POST[$codigoKey])));

                // El nombre es opcional, si está vacío usar el código como nombre
                $nombremodulo = isset($_POST[$nombreKey]) && !empty(trim($_POST[$nombreKey]))
                    ? self::NormalizarNombreModulo($_POST[$nombreKey])
                    : $codigomodulo;

                if (mb_strlen($nombremodulo, 'UTF-8') > self::LARGO_MAXIMO_NOMBRE) {
                    return ['estado' => 'validacion', 'mensaje' => "El nombre del módulo \"$codigomodulo\" supera los " . self::LARGO_MAXIMO_NOMBRE . " caracteres permitidos"];
                }

                $docenteID = isset($_POST[$docenteKey]) && !empty($_POST[$docenteKey])
                    ? (int)$_POST[$docenteKey]
                    : null;

                $fechainicio = isset($_POST[$fechaInicioKey]) && !empty($_POST[$fechaInicioKey])
                    ? trim($_POST[$fechaInicioKey])
                    : null;

                $fechafin = isset($_POST[$fechaFinKey]) && !empty($_POST[$fechaFinKey])
                    ? trim($_POST[$fechaFinKey])
                    : null;

                if ($fechainicio !== null && $fechafin !== null && strtotime($fechafin) < strtotime($fechainicio)) {
                    return ['estado' => 'validacion', 'mensaje' => "La fecha de fin del módulo \"$codigomodulo\" no puede ser anterior a la fecha de inicio"];
                }

                if (!empty($codigomodulo)) {
                    foreach ($modulos as $moduloExistente) {
                        if ($moduloExistente['codigomodulo'] === $codigomodulo) {
                            return ['estado' => 'validacion', 'mensaje' => "El código de módulo \"$codigomodulo\" está repetido"];
                        }
                    }

                    $modulos[] = [
                        'nombremodulo' => $nombremodulo,
                        'codigomodulo' => $codigomodulo,
                        'docenteID' => $docenteID,
                        'fechainicio' => $fechainicio,
                        'fechafin' => $fechafin
                    ];
                }
            }
        }

        if (empty($modulos)) {
            return ['estado' => 'validacion', 'mensaje' => 'Debe llenar al menos un módulo'];
        }

        // Registrar en la base de datos
        $resultado = ModuloModelo::RegistrarModulosPorProgramaModelo([
            'programaID' => $programaID,
            'modulos' => $modulos
        ]);

        $mensajes = [
            'exitoso'   => 'Los módulos se registraron correctamente (' . count($modulos) . ' módulos)',
            'duplicado' => 'Ya existen módulos registrados para este programa',
            'error'     => 'No se pudieron registrar los módulos'
        ];

        return [
            'estado' => $resultado,
            'mensaje' => $mensajes[$resultado] ?? $mensajes['error'],
            'programaID' => $programaID
        ];
    }

    /**
     * Filas de la tabla "Módulos del programa" (se cargan por AJAX al elegir un programa).
     * Columnas: # | Módulo | Periodo | Docente | Estado | Editar.
     * Estado según fechas (Por iniciar / En curso / Culminado) o según validación/cierre.
     * @param int|null $programaID
     */
    public static function TablaModulosPorProgramaControlador($programaID)
    {
        $modulos = ModuloModelo::ListarModulosPorProgramaModelo($programaID);

        if (empty($modulos)) {
            echo '<tr><td colspan="6" class="md-vacio">
                    <i class="fa fa-layer-group"></i>
                    <strong>Este programa aún no tiene módulos registrados</strong>
                    <span>Usa "Agregar módulos" para cargarlos.</span>
                  </td></tr>';
            return;
        }

        $e = function ($valor) {
            return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
        };
        $hoy = date('Y-m-d');

        foreach ($modulos as $i => $modulo) {
            $inicio = $modulo['FechaInicio'] ?? null;
            $fin = $modulo['FechaFinal'] ?? null;
            $estadoRegistro = strtoupper((string)$modulo['estadomodulo']);

            // Fechas mal cargadas (año < 2000, fin antes del inicio o más de 2 años): se marcan para revisar
            $fechasInvalidas = !empty($inicio) && !empty($fin) && (
                (int)substr($inicio, 0, 4) < 2000 || (int)substr($fin, 0, 4) < 2000
                || $fin < $inicio
                || (strtotime($fin) - strtotime($inicio)) > 730 * 86400
            );

            // Periodo y duración
            if ($fechasInvalidas) {
                $periodo = '<strong>' . date('d/m/Y', strtotime($inicio)) . ' – ' . date('d/m/Y', strtotime($fin)) . '</strong>'
                         . '<small class="md-alerta"><i class="fa fa-exclamation-triangle"></i> Revisa las fechas de este módulo</small>';
            } elseif (!empty($inicio) && !empty($fin)) {
                $dias = (int)round((strtotime($fin) - strtotime($inicio)) / 86400) + 1;
                $duracion = $dias >= 14 ? round($dias / 7) . ' semanas' : $dias . ' días';
                $periodo = '<strong>' . date('d/m/Y', strtotime($inicio)) . ' – ' . date('d/m/Y', strtotime($fin)) . '</strong>'
                         . '<small>' . $duracion . '</small>';
            } else {
                $periodo = '<span class="md-muted">Sin fechas definidas</span>';
            }

            // Estado único y claro
            if (in_array($estadoRegistro, ['VALIDADO', 'CERRADO'], true)) {
                $estado = ['Validado', 'is-validado', 'fa-check-double'];
            } elseif ($fechasInvalidas) {
                $estado = ['Revisar fechas', 'is-sin-fechas', 'fa-exclamation-triangle'];
            } elseif (empty($inicio) || empty($fin)) {
                $estado = ['Sin fechas', 'is-sin-fechas', 'fa-calendar-times'];
            } elseif ($hoy < $inicio) {
                $estado = ['Por iniciar', 'is-por-iniciar', 'fa-hourglass-start'];
            } elseif ($hoy > $fin) {
                $estado = ['Culminado', 'is-culminado', 'fa-flag-checkered'];
            } else {
                $estado = ['En curso', 'is-en-curso', 'fa-play-circle'];
            }

            // Docente
            if (!empty($modulo['NombreDocente'])) {
                $docente = '<strong>' . $e($modulo['NombreDocente']) . '</strong>'
                         . (!empty($modulo['EspecialidadDocente']) ? '<small>' . $e($modulo['EspecialidadDocente']) . '</small>' : '');
            } else {
                $docente = '<span class="md-muted"><i class="fa fa-user-slash"></i> Sin asignar</span>';
            }

            // Datos para el modal de edición (mismas claves que antes)
            $datosModulo = $e(json_encode([
                'idmodulo' => $modulo['Idmodulo'],
                'programaId' => $modulo['ProgramaId'],
                'nombremodulo' => $modulo['nombremodulo'],
                'codigomodulo' => $modulo['codigomodulo'],
                'docenteID' => $modulo['DocenteID'],
                'nombreDocente' => $modulo['NombreDocente'] ?? null, // por si está inactivo y no figura en el select
                'nombrePrograma' => $modulo['NombrePrograma'],
                'fechaInicio' => $inicio,
                'fechaFinal' => $fin,
            ]));

            echo '<tr>
                    <td class="md-num">' . ($i + 1) . '</td>
                    <td>
                        <span class="md-codigo">' . $e($modulo['codigomodulo']) . '</span>
                        <div class="md-nombre">' . $e($modulo['nombremodulo']) . '</div>
                    </td>
                    <td class="md-periodo">' . $periodo . '</td>
                    <td class="md-docente">' . $docente . '</td>
                    <td><span class="md-estado ' . $estado[1] . '"><i class="fa ' . $estado[2] . '"></i> ' . $estado[0] . '</span></td>
                    <td class="text-right">
                        <button type="button" class="md-accion btn-editar-modulo" data-modulo=\'' . $datosModulo . '\' title="Editar módulo" aria-label="Editar módulo">
                            <i class="fa fa-pen"></i>
                        </button>
                    </td>
                  </tr>';
        }
    }

    /**
     * Actualizar módulo
     */
    public function ActualizarModuloControlador()
    {
        if (!isset($_POST["actualizarModulo"])) {
            return;
        }

        // Fallback sin JavaScript: procesa el POST tradicional y recarga la página.
        // Cuando hay JavaScript disponible, el modal usa ActualizarModuloAjaxControlador().
        $resultado = $this->ProcesarActualizacionModulo();

        if ($resultado['estado'] === 'exitoso') {
            echo '
            <script src="vistas/recursos/sweetalert.min.js"></script>
            <script>
            swal("EXITOSO!", "' . $resultado['mensaje'] . '", "success")
            .then(function () {
                location.href="modulos";
            });
            </script>';
        } else {
            echo '
            <script src="vistas/recursos/sweetalert.min.js"></script>
            <script>
            swal("ERROR!", "' . $resultado['mensaje'] . '", "error")
            .then(function () {
                location.href="modulos";
            });
            </script>';
        }
    }

    /**
     * Actualizar módulo vía AJAX: responde JSON en vez de redirigir, para
     * que el modal se cierre y la tabla se refresque sin recargar la página.
     */
    public function ActualizarModuloAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->ProcesarActualizacionModulo());
        exit();
    }

    /**
     * Lógica compartida de validación y actualización de un módulo.
     * Devuelve ['estado' => 'exitoso'|'validacion'|'error', 'mensaje' => ..., 'programaID' => ...]
     */
    private function ProcesarActualizacionModulo()
    {
        if (empty($_POST['idmodulo']) || empty($_POST['programaID']) || empty($_POST['nombremodulo']) || empty($_POST['codigomodulo'])) {
            return ['estado' => 'validacion', 'mensaje' => 'Faltan datos obligatorios para actualizar'];
        }

        $idmodulo = (int)$_POST['idmodulo'];
        $programaID = (int)$_POST['programaID'];
        $nombremodulo = self::NormalizarNombreModulo($_POST['nombremodulo']);
        $codigomodulo = strtoupper(htmlspecialchars(trim($_POST['codigomodulo'])));

        if (mb_strlen($nombremodulo, 'UTF-8') > self::LARGO_MAXIMO_NOMBRE) {
            return ['estado' => 'validacion', 'mensaje' => 'El nombre del módulo supera los ' . self::LARGO_MAXIMO_NOMBRE . ' caracteres permitidos'];
        }
        $docenteID = isset($_POST['docenteID']) && !empty($_POST['docenteID']) ? (int)$_POST['docenteID'] : null;
        $fechainicio = isset($_POST['fechainicio']) && !empty($_POST['fechainicio']) ? trim($_POST['fechainicio']) : null;
        $fechafin = isset($_POST['fechafin']) && !empty($_POST['fechafin']) ? trim($_POST['fechafin']) : null;

        if ($fechainicio !== null && $fechafin !== null && strtotime($fechafin) < strtotime($fechainicio)) {
            return ['estado' => 'validacion', 'mensaje' => 'La fecha de fin no puede ser anterior a la fecha de inicio'];
        }

        $resultado = ModuloModelo::ActualizarModuloPorProgramaModelo([
            'idmodulo' => $idmodulo,
            'programaID' => $programaID,
            'nombremodulo' => $nombremodulo,
            'codigomodulo' => $codigomodulo,
            'docenteID' => $docenteID,
            'fechainicio' => $fechainicio,
            'fechafin' => $fechafin
        ]);

        return [
            'estado' => $resultado ? 'exitoso' : 'error',
            'mensaje' => $resultado ? 'El módulo se actualizó correctamente' : 'No se pudo actualizar el módulo',
            'programaID' => $programaID
        ];
    }
}
?>
