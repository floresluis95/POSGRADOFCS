<?php
/**
 * Biblioteca de tesis: consulta de los trabajos de grado registrados (tabla trabajogrado).
 * SOLO CONSULTA: no guarda ni modifica nada. El título se registra y corrige en
 * Registro de documentos / certificados, no aquí.
 */
$certTitulo = 'BIBLIOTECA DE TESIS';
$certSubtitulo = 'CONSULTA DE TRABAJOS DE GRADO · POR AUTOR, TÍTULO O PALABRAS CLAVE';
include __DIR__ . '/modulos/certificado-encabezado.php';
require_once 'modelos/bibliotecatesis.modelo.php';

// El formulario va por GET a index.php (el .htaccess no conserva el ?query en las URL cortas)
$bt_texto = trim((string) ($_GET['q'] ?? ''));
$bt_campo = in_array($_GET['campo'] ?? '', BibliotecaTesisModelo::CAMPOS, true) ? $_GET['campo'] : 'todo';
$bt_programa = (int) ($_GET['programa'] ?? 0);
$bt_aprobadas = !empty($_GET['aprobadas']);
$bt_buscado = isset($_GET['q']);

$bt_programas = BibliotecaTesisModelo::ProgramasConTesis();
$bt_resultados = $bt_buscado ? BibliotecaTesisModelo::BuscarTesis($bt_texto, $bt_campo, $bt_programa, $bt_aprobadas) : [];

$bt_e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$bt_fecha = function ($f) { return $f ? date('d/m/Y', strtotime($f)) : ''; };

// Resalta en el texto las palabras buscadas (el texto ya viene escapado)
$bt_terminos = [];
if ($bt_texto !== '') {
    $bt_terminos = $bt_campo === 'titulo' ? [$bt_texto] : array_filter(explode(' ', preg_replace('/\s+/u', ' ', $bt_texto)), function ($p) { return mb_strlen($p) >= 2; });
}
$bt_resaltar = function ($texto) use ($bt_e, $bt_terminos) {
    $html = $bt_e($texto);
    if (!$bt_terminos) {
        return $html;
    }
    // Una sola pasada (si no, una palabra como "ma" marcaría dentro de "<mark>")
    $partes = array_map(function ($t) use ($bt_e) { return preg_quote($bt_e($t), '/'); }, $bt_terminos);
    usort($partes, function ($a, $b) { return mb_strlen($b) - mb_strlen($a); });
    return preg_replace('/(' . implode('|', $partes) . ')/iu', '<mark>$1</mark>', $html);
};
$bt_opciones = ['todo' => 'Todo (autor y título)', 'autor' => 'Nombre del autor', 'titulo' => 'Título de la tesis (frase exacta)', 'palabras' => 'Palabras clave del título'];
?>
<style>
    .bt-form { display:grid; grid-template-columns: 2fr 1fr 1.4fr auto; gap:12px; align-items:end; }
    .bt-form label { font-size:12px; font-weight:600; color:#475569; margin-bottom:4px; display:block; }
    .bt-form .form-control { height:40px; }
    .bt-aprobadas { margin-top:10px; font-size:13px; color:#475569; }
    .bt-ayuda { font-size:12px; color:#64748b; margin:8px 0 0; }
    .bt-resumen { font-size:13px; color:#475569; margin-bottom:12px; }
    .bt-tesis { border:1px solid #e2e8f0; border-radius:10px; padding:14px 18px; margin-bottom:12px; background:#fff; }
    .bt-tesis h4 { font-size:15px; font-weight:700; color:#1e293b; margin:0 0 8px; line-height:1.4; }
    .bt-meta { display:flex; flex-wrap:wrap; gap:6px 18px; font-size:13px; color:#475569; }
    .bt-meta i { color:#94a3b8; margin-right:4px; }
    .bt-estado { display:inline-block; font-size:11px; font-weight:700; padding:2px 8px; border-radius:20px; }
    .bt-estado.aprobada { background:#dcfce7; color:#166534; }
    .bt-estado.proceso { background:#fef3c7; color:#92400e; }
    .bt-tribunal { margin-top:8px; font-size:12px; color:#64748b; }
    .bt-tesis mark { background:#fde68a; padding:0 1px; border-radius:2px; }
    @media (max-width: 768px) { .bt-form { grid-template-columns: 1fr; } }
</style>

                            <div class="card-certificado">
                                <h3><i class="fas fa-book"></i> Buscar tesis</h3>
                                <div class="certificado-cuerpo">
                                    <form method="get" action="index.php" autocomplete="off">
                                        <input type="hidden" name="action" value="bibliotecatesis">
                                        <div class="bt-form">
                                            <div>
                                                <label for="bt-q">Texto a buscar</label>
                                                <input type="text" id="bt-q" name="q" class="form-control" maxlength="200"
                                                       value="<?php echo $bt_e($bt_texto); ?>" placeholder="Ej.: caries infantil, Mamani, implantes...">
                                            </div>
                                            <div>
                                                <label for="bt-campo">Buscar en</label>
                                                <select id="bt-campo" name="campo" class="form-control">
                                                    <?php foreach ($bt_opciones as $valor => $texto): ?>
                                                        <option value="<?php echo $valor; ?>" <?php echo $bt_campo === $valor ? 'selected' : ''; ?>><?php echo $bt_e($texto); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div>
                                                <label for="bt-programa">Programa</label>
                                                <select id="bt-programa" name="programa" class="form-control">
                                                    <option value="0">Todos los programas</option>
                                                    <?php foreach ($bt_programas as $p): ?>
                                                        <option value="<?php echo (int) $p['ProgramaID']; ?>" <?php echo $bt_programa === (int) $p['ProgramaID'] ? 'selected' : ''; ?>>
                                                            <?php echo $bt_e($p['NombrePrograma'] . ($p['Version'] ? ' (' . $p['Version'] . ')' : '')); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div>
                                                <button type="submit" class="btn-generar-certificado" style="height:40px;"><i class="fas fa-search"></i> Buscar</button>
                                            </div>
                                        </div>
                                        <label class="bt-aprobadas">
                                            <input type="checkbox" name="aprobadas" value="1" <?php echo $bt_aprobadas ? 'checked' : ''; ?>> Solo tesis aprobadas
                                        </label>
                                        <p class="bt-ayuda">No distingue mayúsculas ni tildes. Con "Palabras clave" se muestran las tesis cuyo título tiene <strong>todas</strong> las palabras, en cualquier orden. Deje el texto vacío y pulse Buscar para ver todas.</p>
                                    </form>
                                </div>
                            </div>

                            <div class="card-certificado">
                                <h3><i class="fas fa-list"></i> Resultados</h3>
                                <div class="certificado-cuerpo">
                                    <?php if (!$bt_buscado): ?>
                                        <div class="cert-vacio">
                                            <i class="fas fa-book-open"></i>
                                            <p>Escriba el nombre del autor, el título o palabras clave y pulse Buscar.</p>
                                        </div>
                                    <?php elseif (!$bt_resultados): ?>
                                        <div class="cert-vacio">
                                            <i class="fas fa-search"></i>
                                            <p>No se encontraron tesis con esos datos.</p>
                                        </div>
                                    <?php else: ?>
                                        <p class="bt-resumen">
                                            <?php echo count($bt_resultados) === 1 ? '1 tesis encontrada' : count($bt_resultados) . ' tesis encontradas'; ?>
                                            <?php echo count($bt_resultados) >= 300 ? ' (se muestran las primeras 300; precise la búsqueda)' : ''; ?>
                                        </p>
                                        <?php foreach ($bt_resultados as $t):
                                            $autorNombre = trim($t['Nombre'] . ' ' . $t['Apaterno'] . ' ' . $t['Amaterno']);
                                            $aprobada = !empty($t['FechaAprobacion']);
                                            $tribunal = BibliotecaTesisModelo::TribunalLineas($t['Tribunal']);
                                        ?>
                                            <div class="bt-tesis">
                                                <h4><?php echo $bt_resaltar($t['TituloTrabajo']); ?></h4>
                                                <div class="bt-meta">
                                                    <span><i class="fas fa-user"></i><?php echo $bt_resaltar($autorNombre); ?> · C.I. <?php echo $bt_e($t['Ci']); ?></span>
                                                    <span><i class="fas fa-graduation-cap"></i><?php echo $bt_e(CertificadoModelo::GradoLegible($t['GradoAcademico']) . ' · ' . $t['NombrePrograma'] . ($t['Version'] ? ' (' . $t['Version'] . ')' : '')); ?></span>
                                                    <?php if ($aprobada): ?>
                                                        <span><i class="fas fa-calendar-check"></i>Aprobada el <?php echo $bt_fecha($t['FechaAprobacion']); ?></span>
                                                    <?php elseif ($t['FechaEntrega']): ?>
                                                        <span><i class="fas fa-calendar"></i>Entregada el <?php echo $bt_fecha($t['FechaEntrega']); ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($t['CalificacionNumeral'] !== null): ?>
                                                        <span><i class="fas fa-star"></i>Calificación: <?php echo $bt_e(rtrim(rtrim($t['CalificacionNumeral'], '0'), '.')); ?></span>
                                                    <?php endif; ?>
                                                    <span class="bt-estado <?php echo $aprobada ? 'aprobada' : 'proceso'; ?>"><?php echo $aprobada ? 'APROBADA' : 'EN PROCESO'; ?></span>
                                                </div>
                                                <?php if ($tribunal): ?>
                                                    <div class="bt-tribunal"><i class="fas fa-users"></i> <?php echo $bt_e(implode(' · ', $tribunal)); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

<?php include __DIR__ . '/modulos/certificado-pie.php'; ?>
</body>
