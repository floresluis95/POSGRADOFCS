<?php
/**
 * Biblioteca de tesis (todos los roles):
 *  - Catálogo: búsqueda de trabajos de grado (tabla trabajogrado) por autor, título o palabras
 *    clave, con filtros. Estudiantes y docentes solo ven las tesis APROBADAS y sin C.I. ni
 *    calificación del autor.
 *  - Mis préstamos (EST/DOC): solicitar el ejemplar físico y seguir el préstamo.
 *  - Préstamos (solo ADM): reservar, entregar, rechazar y recibir la devolución.
 * El título se registra y corrige en Registro de documentos, no aquí.
 */
$certTitulo = 'BIBLIOTECA DE TESIS';
$certSubtitulo = 'REPOSITORIO DE TRABAJOS DE GRADO · CONSULTA Y PRÉSTAMO';
include __DIR__ . '/modulos/certificado-encabezado.php';
require_once 'modelos/bibliotecatesis.modelo.php';

$bt_tipo = $_SESSION['Tipo'] ?? '';
$bt_esPersonal = in_array($bt_tipo, ['ADM', 'SEC', 'AUX'], true);
$bt_esAdm = $bt_tipo === 'ADM';
$bt_esLector = in_array($bt_tipo, ['EST', 'DOC'], true);
$bt_personaID = (int) ($bt_tipo === 'DOC' ? ($_SESSION['DocenteID'] ?? 0) : ($_SESSION['EstudianteID'] ?? 0));
$bt_hayPrestamos = PrestamoTesisModelo::TablaExiste();

// Pestaña (las de préstamos solo para quien corresponde)
$bt_vistas = ['catalogo'];
if ($bt_esLector) $bt_vistas[] = 'misprestamos';
if ($bt_esAdm) $bt_vistas[] = 'prestamos';
$bt_vista = in_array($_GET['vista'] ?? '', $bt_vistas, true) ? $_GET['vista'] : 'catalogo';

$bt_e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$bt_fecha = function ($f) { return $f ? date('d/m/Y', strtotime($f)) : ''; };
$bt_fechaHora = function ($f) { return $f ? date('d/m/Y H:i', strtotime($f)) : ''; };
// El formulario va por GET a index.php (el .htaccess no conserva el ?query en las URL cortas)
$bt_url = function (array $params) {
    return 'index.php?' . http_build_query(['action' => 'bibliotecatesis'] + array_filter($params, function ($v) {
        return $v !== '' && $v !== null && $v !== 0 && $v !== false;
    }));
};
$bt_grados = ['DIPLOMADO' => ['DIP', 'Diplomado'], 'ESPECIALIDAD' => ['ESP', 'Especialidad'], 'MAESTRIA' => ['MSc', 'Maestría'], 'DOCTORADO' => ['PhD', 'Doctorado']];
$bt_contadores = $bt_esAdm ? PrestamoTesisModelo::Contadores() : null;
$bt_misActivos = $bt_esLector ? PrestamoTesisModelo::ActivosDePersona($bt_tipo, $bt_personaID) : [];
$bt_plazo = PrestamoTesisModelo::PlazoDias();
$bt_hoy = date('Y-m-d');
?>
<style>
    .bt { --bt-azul:#1f3a5f; --bt-azul-2:#2d5b8a; --bt-oro:#b8892b; --bt-papel:#fbfaf6; --bt-borde:#e6e1d6; --bt-texto:#1e293b; --bt-suave:#64748b; }
    .bt { color:var(--bt-texto); }
    .bt-serif { font-family: Georgia, 'Times New Roman', serif; }

    /* Portada */
    .bt-portada { background:linear-gradient(135deg, var(--bt-azul) 0%, var(--bt-azul-2) 100%); color:#fff; border-radius:14px; padding:26px 28px 22px; margin-bottom:18px; position:relative; overflow:hidden; }
    .bt-portada::after { content:"\f02d"; font-family:"Font Awesome 5 Free"; font-weight:900; position:absolute; right:26px; top:10px; font-size:120px; opacity:.07; }
    .bt-portada small { letter-spacing:2px; text-transform:uppercase; font-size:11px; color:#d8c08a; font-weight:600; }
    .bt-portada h2 { font-family:Georgia, 'Times New Roman', serif; font-size:26px; margin:4px 0 4px; font-weight:700; color:#fff; }
    .bt-portada p { margin:0; color:#dbe4ef; font-size:13.5px; }
    .bt-cifras { display:flex; flex-wrap:wrap; gap:10px; margin-top:16px; }
    .bt-cifra { background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.18); border-radius:10px; padding:8px 14px; min-width:110px; }
    .bt-cifra b { display:block; font-size:20px; font-family:Georgia, serif; }
    .bt-cifra span { font-size:11.5px; color:#dbe4ef; }

    /* Pestañas */
    .bt-pestanas { display:flex; gap:6px; border-bottom:2px solid var(--bt-borde); margin-bottom:18px; flex-wrap:wrap; }
    .bt-pestanas a { padding:10px 16px; font-weight:600; font-size:13.5px; color:var(--bt-suave); border-bottom:3px solid transparent; margin-bottom:-2px; text-decoration:none; }
    .bt-pestanas a:hover { color:var(--bt-azul); }
    .bt-pestanas a.activa { color:var(--bt-azul); border-bottom-color:var(--bt-oro); }
    .bt-contador { display:inline-block; min-width:20px; padding:1px 7px; border-radius:20px; font-size:11px; background:#e2e8f0; color:#334155; margin-left:4px; text-align:center; }
    .bt-contador.alerta { background:#dc2626; color:#fff; }

    /* Buscador */
    .bt-buscador { background:#fff; border:1px solid var(--bt-borde); border-radius:14px; padding:18px 20px; margin-bottom:16px; box-shadow:0 1px 3px rgba(15,23,42,.05); }
    .bt-barra { display:flex; gap:10px; }
    .bt-barra .bt-entrada { flex:1; position:relative; }
    .bt-barra .bt-entrada i { position:absolute; left:15px; top:50%; transform:translateY(-50%); color:#94a3b8; }
    .bt-barra input[type=search] { width:100%; height:48px; border:1.5px solid #cbd5e1; border-radius:10px; padding:0 14px 0 42px; font-size:15px; }
    .bt-barra input[type=search]:focus { outline:none; border-color:var(--bt-azul-2); box-shadow:0 0 0 3px rgba(45,91,138,.15); }
    .bt-btn-buscar { height:48px; padding:0 24px; border:0; border-radius:10px; background:var(--bt-azul); color:#fff; font-weight:600; font-size:14px; }
    .bt-btn-buscar:hover { background:var(--bt-azul-2); }
    .bt-campos { display:flex; flex-wrap:wrap; gap:6px; margin:12px 0 4px; align-items:center; }
    .bt-campos > span { font-size:12px; color:var(--bt-suave); margin-right:4px; }
    .bt-chip { position:relative; }
    .bt-chip input { position:absolute; opacity:0; pointer-events:none; }
    .bt-chip label { display:inline-block; padding:5px 13px; border:1px solid #cbd5e1; border-radius:20px; font-size:12.5px; cursor:pointer; margin:0; color:#334155; background:#fff; }
    .bt-chip input:checked + label { background:var(--bt-azul); border-color:var(--bt-azul); color:#fff; }
    .bt-chip input:focus-visible + label { box-shadow:0 0 0 3px rgba(45,91,138,.3); }
    .bt-filtros { display:grid; grid-template-columns:2fr 1fr 1fr 1fr; gap:10px; margin-top:12px; }
    .bt-filtros label { font-size:11.5px; font-weight:600; color:#475569; margin-bottom:3px; display:block; text-transform:uppercase; letter-spacing:.4px; }
    .bt-filtros select { width:100%; height:38px; border:1px solid #cbd5e1; border-radius:8px; padding:0 8px; font-size:13px; background:#fff; }
    .bt-ayuda { font-size:12px; color:var(--bt-suave); margin:10px 0 0; }
    .bt-aprobadas { font-size:13px; color:#475569; margin:10px 0 0; display:inline-flex; gap:6px; align-items:center; }

    /* Resultados */
    .bt-resumen { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin:4px 0 12px; font-size:13.5px; color:#475569; }
    .bt-resumen b { color:var(--bt-texto); }
    .bt-limpiar { font-size:12.5px; }
    .bt-ficha { display:flex; background:var(--bt-papel); border:1px solid var(--bt-borde); border-radius:12px; margin-bottom:12px; overflow:hidden; transition:box-shadow .15s; }
    .bt-ficha:hover { box-shadow:0 4px 14px rgba(15,23,42,.08); }
    .bt-lomo { width:74px; flex:0 0 74px; color:#fff; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:6px; padding:12px 4px; text-align:center; background:var(--bt-azul-2); border-right:4px solid rgba(0,0,0,.12); }
    .bt-lomo.g-DIPLOMADO { background:#2a7f7a; } .bt-lomo.g-ESPECIALIDAD { background:#2d5b8a; } .bt-lomo.g-MAESTRIA { background:#7b2d3b; } .bt-lomo.g-DOCTORADO { background:#2f5d3a; }
    .bt-lomo .sigla { font-family:Georgia, serif; font-size:18px; font-weight:700; }
    .bt-lomo .anio { font-size:12px; opacity:.9; border-top:1px solid rgba(255,255,255,.35); padding-top:5px; width:46px; }
    .bt-cuerpo { flex:1; padding:14px 18px; min-width:0; }
    .bt-tipo { font-size:11.5px; letter-spacing:.6px; text-transform:uppercase; color:var(--bt-oro); font-weight:700; margin-bottom:4px; }
    .bt-titulo { font-family:Georgia, 'Times New Roman', serif; font-size:16.5px; font-weight:700; line-height:1.4; margin:0 0 6px; color:var(--bt-azul); word-break:break-word; }
    .bt-autor { font-size:14px; color:#334155; margin-bottom:6px; }
    .bt-autor i, .bt-datos i { color:#94a3b8; margin-right:5px; }
    .bt-datos { display:flex; flex-wrap:wrap; gap:4px 18px; font-size:12.5px; color:#475569; }
    .bt-desplegables { display:flex; flex-wrap:wrap; gap:6px 16px; margin-top:8px; }
    .bt-desplegables details { font-size:12.5px; }
    .bt-desplegables summary { cursor:pointer; color:var(--bt-azul-2); font-weight:600; list-style:none; }
    .bt-desplegables summary::-webkit-details-marker { display:none; }
    .bt-desplegables summary::before { content:"\25B8  "; }
    .bt-desplegables details[open] summary::before { content:"\25BE  "; }
    .bt-desplegables details[open] { flex-basis:100%; }
    .bt-tribunal { margin:8px 0 0; padding:10px 12px; background:#fff; border:1px dashed var(--bt-borde); border-radius:8px; }
    .bt-tribunal div { padding:2px 0; } .bt-tribunal span { display:inline-block; min-width:110px; color:var(--bt-suave); font-size:11.5px; text-transform:uppercase; }
    .bt-cita { margin:8px 0 0; padding:10px 12px; background:#fff; border-left:3px solid var(--bt-oro); border-radius:0 8px 8px 0; font-family:Georgia, serif; font-size:13px; line-height:1.5; display:flex; gap:10px; align-items:flex-start; }
    .bt-cita p { margin:0; flex:1; }
    .bt-cita button { border:1px solid #cbd5e1; background:#fff; border-radius:6px; font-size:12px; padding:3px 9px; white-space:nowrap; font-family:inherit; }
    .bt-cita-nota { font-family:inherit; color:var(--bt-suave); font-size:11.5px; margin-top:4px; }
    .bt-lateral { flex:0 0 200px; padding:14px 16px; border-left:1px solid var(--bt-borde); display:flex; flex-direction:column; gap:8px; justify-content:center; align-items:stretch; background:#fff; }
    .bt-disp { font-size:12px; font-weight:700; padding:4px 10px; border-radius:20px; text-align:center; }
    .bt-disp.si { background:#dcfce7; color:#166534; } .bt-disp.reservada { background:#e0e7ff; color:#3730a3; }
    .bt-disp.prestada { background:#fef3c7; color:#92400e; } .bt-disp.proceso { background:#f1f5f9; color:#475569; }
    .bt-disp small { display:block; font-weight:500; }
    .bt-accion { border:0; border-radius:8px; padding:8px 10px; font-size:13px; font-weight:600; background:var(--bt-azul); color:#fff; }
    .bt-accion:hover { background:var(--bt-azul-2); }
    .bt-accion.secundaria { background:#fff; color:var(--bt-azul); border:1px solid #cbd5e1; }
    .bt-accion.peligro { background:#fff; color:#b91c1c; border:1px solid #fecaca; }
    .bt-accion.exito { background:#15803d; } .bt-accion.exito:hover { background:#166534; }
    .bt-accion:disabled { opacity:.5; cursor:not-allowed; }
    .bt-propio { font-size:12.5px; text-align:center; color:#334155; }
    .bt-propio a { color:#b91c1c; }
    .bt-ficha mark { background:#fde68a; padding:0 1px; border-radius:2px; }

    .bt-paginas { display:flex; justify-content:center; gap:4px; flex-wrap:wrap; margin:16px 0 4px; }
    .bt-paginas a, .bt-paginas span { min-width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; padding:0 10px; background:#fff; color:#334155; text-decoration:none; }
    .bt-paginas .actual { background:var(--bt-azul); color:#fff; border-color:var(--bt-azul); }
    .bt-paginas .inactivo { opacity:.45; }

    .bt-vacio { text-align:center; padding:40px 20px; color:var(--bt-suave); background:#fff; border:1px dashed var(--bt-borde); border-radius:12px; }
    .bt-vacio i { font-size:36px; color:#cbd5e1; margin-bottom:10px; display:block; }

    /* Préstamos */
    .bt-aviso { background:#fffbeb; border:1px solid #fde68a; border-radius:10px; padding:12px 16px; font-size:13px; color:#78350f; margin-bottom:14px; }
    .bt-aviso i { margin-right:6px; }
    .bt-prestamo { background:#fff; border:1px solid var(--bt-borde); border-radius:12px; padding:14px 18px; margin-bottom:12px; }
    .bt-prestamo.vencido { border-color:#fca5a5; background:#fff7f7; }
    .bt-prestamo-cab { display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; }
    .bt-prestamo h4 { font-family:Georgia, serif; font-size:15px; color:var(--bt-azul); margin:0 0 4px; line-height:1.4; }
    .bt-prestamo .sub { font-size:12.5px; color:var(--bt-suave); }
    .bt-pasos { display:flex; margin:14px 0 4px; }
    .bt-paso { flex:1; text-align:center; position:relative; font-size:11.5px; color:#94a3b8; }
    .bt-paso::before { content:""; position:absolute; top:11px; left:-50%; width:100%; height:2px; background:#e2e8f0; z-index:0; }
    .bt-paso:first-child::before { display:none; }
    .bt-paso .punto { width:24px; height:24px; border-radius:50%; background:#e2e8f0; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:11px; position:relative; z-index:1; }
    .bt-paso.hecho { color:#334155; } .bt-paso.hecho .punto { background:#15803d; } .bt-paso.hecho::before { background:#15803d; }
    .bt-paso.actual .punto { background:var(--bt-oro); box-shadow:0 0 0 4px rgba(184,137,43,.2); }
    .bt-paso small { display:block; color:#94a3b8; }
    .bt-estado { display:inline-block; font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; letter-spacing:.3px; white-space:nowrap; }
    .bt-estado.SOLICITADO { background:#e0f2fe; color:#075985; } .bt-estado.APROBADO { background:#e0e7ff; color:#3730a3; }
    .bt-estado.PRESTADO { background:#fef3c7; color:#92400e; } .bt-estado.DEVUELTO { background:#dcfce7; color:#166534; }
    .bt-estado.RECHAZADO { background:#fee2e2; color:#991b1b; } .bt-estado.CANCELADO { background:#f1f5f9; color:#475569; }
    .bt-estado.VENCIDO { background:#dc2626; color:#fff; }
    .bt-nota { font-size:12.5px; margin-top:8px; color:#475569; }
    .bt-sublistas { display:flex; gap:6px; margin-bottom:14px; flex-wrap:wrap; }
    .bt-sublistas a { padding:6px 14px; border-radius:20px; border:1px solid #cbd5e1; font-size:12.5px; color:#334155; background:#fff; text-decoration:none; }
    .bt-sublistas a.activa { background:var(--bt-azul); color:#fff; border-color:var(--bt-azul); }
    .bt-tabla { width:100%; border-collapse:collapse; background:#fff; font-size:13px; }
    .bt-tabla th { background:#f8fafc; text-align:left; padding:9px 10px; font-size:11.5px; text-transform:uppercase; color:#475569; border-bottom:1px solid var(--bt-borde); }
    .bt-tabla td { padding:10px; border-bottom:1px solid #f1f5f9; vertical-align:top; }
    .bt-tabla tr.vencido td { background:#fff7f7; }
    .bt-tabla .acciones { display:flex; gap:6px; flex-wrap:wrap; }
    .bt-tabla .acciones .bt-accion { padding:6px 10px; font-size:12px; }
    .bt-tabla-envoltura { overflow-x:auto; border:1px solid var(--bt-borde); border-radius:12px; }

    @media (max-width: 991px) { .bt-filtros { grid-template-columns:1fr 1fr; } }
    @media (max-width: 767px) {
        .bt-barra { flex-direction:column; } .bt-filtros { grid-template-columns:1fr; }
        .bt-ficha { flex-wrap:wrap; } .bt-lomo { flex-direction:row; width:100%; flex-basis:100%; padding:6px; border-right:0; }
        .bt-lomo .anio { border-top:0; border-left:1px solid rgba(255,255,255,.35); padding:0 0 0 8px; width:auto; }
        .bt-lateral { flex-basis:100%; border-left:0; border-top:1px solid var(--bt-borde); }
        .bt-portada h2 { font-size:21px; }
    }
</style>

<div class="bt">
    <?php $bt_cifras = BibliotecaTesisModelo::Estadisticas(!$bt_esPersonal); ?>
    <section class="bt-portada">
        <small><?php echo $bt_e(InstitucionModelo::Formal(mb_strtolower(InstitucionModelo::Universidad(), 'UTF-8'))); ?></small>
        <h2>Repositorio de Trabajos de Grado</h2>
        <p>Tesis y trabajos de grado de los programas de <?php echo $bt_e(InstitucionModelo::NombreSistema()); ?>. Busque por autor, título o tema y solicite el ejemplar físico en préstamo.</p>
        <div class="bt-cifras">
            <div class="bt-cifra"><b><?php echo (int) $bt_cifras['Tesis']; ?></b><span><?php echo $bt_esPersonal ? 'trabajos registrados' : 'tesis aprobadas'; ?></span></div>
            <div class="bt-cifra"><b><?php echo (int) $bt_cifras['Programas']; ?></b><span>programas</span></div>
            <div class="bt-cifra"><b><?php echo (int) $bt_cifras['Autores']; ?></b><span>autores</span></div>
            <?php if ($bt_hayPrestamos): ?>
                <div class="bt-cifra"><b><?php echo (int) $bt_cifras['Prestadas']; ?></b><span>en préstamo ahora</span></div>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($bt_esLector || $bt_esAdm): ?>
        <nav class="bt-pestanas" aria-label="Secciones de la biblioteca">
            <a href="<?php echo $bt_url([]); ?>" class="<?php echo $bt_vista === 'catalogo' ? 'activa' : ''; ?>"><i class="fas fa-search"></i> Catálogo</a>
            <?php if ($bt_esLector): ?>
                <a href="<?php echo $bt_url(['vista' => 'misprestamos']); ?>" class="<?php echo $bt_vista === 'misprestamos' ? 'activa' : ''; ?>">
                    <i class="fas fa-book-reader"></i> Mis préstamos
                    <?php if ($bt_misActivos): ?><span class="bt-contador"><?php echo count($bt_misActivos); ?></span><?php endif; ?>
                </a>
            <?php endif; ?>
            <?php if ($bt_esAdm): ?>
                <a href="<?php echo $bt_url(['vista' => 'prestamos']); ?>" class="<?php echo $bt_vista === 'prestamos' ? 'activa' : ''; ?>">
                    <i class="fas fa-exchange-alt"></i> Préstamos
                    <?php if ($bt_contadores['pendientes'] + $bt_contadores['vencidas'] > 0): ?>
                        <span class="bt-contador alerta"><?php echo $bt_contadores['pendientes'] + $bt_contadores['vencidas']; ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>

<?php if ($bt_vista === 'catalogo'):
    /* ======================= CATÁLOGO ======================= */
    $bt_f = BibliotecaTesisModelo::Filtros($_GET, !$bt_esPersonal);
    $bt_total = BibliotecaTesisModelo::ContarTesis($bt_f);
    $bt_paginas = max(1, (int) ceil($bt_total / BibliotecaTesisModelo::POR_PAGINA));
    $bt_pagina = min($bt_paginas, max(1, (int) ($_GET['pag'] ?? 1)));
    $bt_resultados = $bt_total ? BibliotecaTesisModelo::BuscarTesis($bt_f, $bt_pagina) : [];
    $bt_programas = BibliotecaTesisModelo::ProgramasConTesis(!$bt_esPersonal);
    list($bt_gradosHay, $bt_aniosHay) = BibliotecaTesisModelo::GradosYAnios(!$bt_esPersonal);
    $bt_universidad = InstitucionModelo::Universidad();
    $bt_filtrosUrl = ['q' => $bt_f['texto'], 'campo' => $bt_f['campo'] === 'todo' ? '' : $bt_f['campo'], 'programa' => $bt_f['programa'],
        'grado' => $bt_f['grado'], 'anio' => $bt_f['anio'], 'orden' => $bt_f['orden'] === 'recientes' ? '' : $bt_f['orden'],
        'aprobadas' => $bt_esPersonal && $bt_f['aprobadas'] ? 1 : 0];
    $bt_hayFiltros = $bt_f['texto'] !== '' || $bt_f['programa'] || $bt_f['grado'] !== '' || $bt_f['anio'] || ($bt_esPersonal && $bt_f['aprobadas']);

    // Resalta en el texto las palabras buscadas (el texto ya viene escapado)
    $bt_terminos = BibliotecaTesisModelo::Terminos($bt_f);
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
    $bt_campos = ['todo' => 'Todo', 'autor' => 'Autor', 'titulo' => 'Título (frase exacta)', 'palabras' => 'Palabras clave'];
    $bt_ordenes = ['recientes' => 'Más recientes', 'antiguas' => 'Más antiguas', 'titulo' => 'Título (A-Z)', 'autor' => 'Autor (A-Z)'];
?>
    <form class="bt-buscador" method="get" action="index.php" autocomplete="off" role="search">
        <input type="hidden" name="action" value="bibliotecatesis">
        <div class="bt-barra">
            <div class="bt-entrada">
                <i class="fas fa-search"></i>
                <input type="search" id="bt-q" name="q" maxlength="200" value="<?php echo $bt_e($bt_f['texto']); ?>"
                       placeholder="Autor, título o tema: caries infantil, Mamani, implantes..." aria-label="Texto a buscar">
            </div>
            <button type="submit" class="bt-btn-buscar"><i class="fas fa-search"></i> Buscar</button>
        </div>
        <div class="bt-campos" role="radiogroup" aria-label="Buscar en">
            <span>Buscar en:</span>
            <?php foreach ($bt_campos as $valor => $texto): ?>
                <div class="bt-chip">
                    <input type="radio" name="campo" id="bt-campo-<?php echo $valor; ?>" value="<?php echo $valor; ?>" <?php echo $bt_f['campo'] === $valor ? 'checked' : ''; ?>>
                    <label for="bt-campo-<?php echo $valor; ?>"><?php echo $bt_e($texto); ?></label>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="bt-filtros">
            <div>
                <label for="bt-programa">Programa</label>
                <select id="bt-programa" name="programa">
                    <option value="0">Todos los programas</option>
                    <?php foreach ($bt_programas as $p): ?>
                        <option value="<?php echo (int) $p['ProgramaID']; ?>" <?php echo $bt_f['programa'] === (int) $p['ProgramaID'] ? 'selected' : ''; ?>>
                            <?php echo $bt_e($p['NombrePrograma'] . ($p['Version'] ? ' (' . $p['Version'] . ')' : '')); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="bt-grado">Grado académico</label>
                <select id="bt-grado" name="grado">
                    <option value="">Todos</option>
                    <?php foreach ($bt_gradosHay as $g): ?>
                        <option value="<?php echo $bt_e($g); ?>" <?php echo $bt_f['grado'] === $g ? 'selected' : ''; ?>><?php echo $bt_e($bt_grados[$g][1] ?? $g); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="bt-anio">Año</label>
                <select id="bt-anio" name="anio">
                    <option value="0">Todos</option>
                    <?php foreach ($bt_aniosHay as $a): ?>
                        <option value="<?php echo (int) $a; ?>" <?php echo $bt_f['anio'] === (int) $a ? 'selected' : ''; ?>><?php echo (int) $a; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="bt-orden">Ordenar por</label>
                <select id="bt-orden" name="orden">
                    <?php foreach ($bt_ordenes as $valor => $texto): ?>
                        <option value="<?php echo $valor; ?>" <?php echo $bt_f['orden'] === $valor ? 'selected' : ''; ?>><?php echo $bt_e($texto); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <?php if ($bt_esPersonal): ?>
            <label class="bt-aprobadas"><input type="checkbox" name="aprobadas" value="1" <?php echo $bt_f['aprobadas'] ? 'checked' : ''; ?>> Solo tesis aprobadas</label>
        <?php endif; ?>
        <p class="bt-ayuda">No distingue mayúsculas ni tildes. Con <strong>Palabras clave</strong> se muestran las tesis cuyo título tiene <strong>todas</strong> las palabras, en cualquier orden.</p>
    </form>

    <div class="bt-resumen" aria-live="polite">
        <span>
            <b><?php echo $bt_total; ?></b> <?php echo $bt_total === 1 ? 'tesis encontrada' : 'tesis encontradas'; ?>
            <?php if ($bt_paginas > 1): ?> · página <?php echo $bt_pagina; ?> de <?php echo $bt_paginas; ?><?php endif; ?>
        </span>
        <?php if ($bt_hayFiltros): ?>
            <a class="bt-limpiar" href="<?php echo $bt_url([]); ?>"><i class="fas fa-times"></i> Quitar la búsqueda y los filtros</a>
        <?php endif; ?>
    </div>

    <?php if (!$bt_resultados): ?>
        <div class="bt-vacio">
            <i class="fas fa-book-open"></i>
            <?php if ($bt_hayFiltros): ?>
                <p>No se encontraron tesis con esos datos.</p>
                <p style="font-size:13px;">Pruebe con menos palabras, con <strong>Todo</strong> o quitando los filtros.</p>
            <?php else: ?>
                <p>Todavía no hay tesis en el repositorio.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php foreach ($bt_resultados as $t):
        $autorNombre = trim($t['Nombre'] . ' ' . $t['Apaterno'] . ' ' . $t['Amaterno']);
        $aprobada = !empty($t['FechaAprobacion']);
        $tribunal = BibliotecaTesisModelo::TribunalLineas($t['Tribunal']);
        $grado = $bt_grados[$t['GradoAcademico']] ?? [mb_substr($t['GradoAcademico'], 0, 3), $t['GradoAcademico']];
        $mio = $bt_misActivos[(int) $t['IdTrabajo']] ?? null;
        $ejemplar = $t['EstadoEjemplar'];
    ?>
        <article class="bt-ficha bt-tesis" data-id="<?php echo (int) $t['IdTrabajo']; ?>">
            <div class="bt-lomo g-<?php echo $bt_e($t['GradoAcademico']); ?>" title="<?php echo $bt_e($grado[1]); ?>">
                <span class="sigla"><?php echo $bt_e($grado[0]); ?></span>
                <span class="anio"><?php echo (int) $t['Anio']; ?></span>
            </div>
            <div class="bt-cuerpo">
                <div class="bt-tipo"><?php echo $bt_e($grado[1] . ' · ' . $t['NombrePrograma'] . ($t['Version'] ? ' (' . $t['Version'] . ')' : '')); ?></div>
                <h3 class="bt-titulo"><?php echo $bt_resaltar($t['TituloTrabajo']); ?></h3>
                <div class="bt-autor bt-meta"><span><i class="fas fa-user-graduate"></i><?php echo $bt_resaltar($autorNombre); ?><?php if ($bt_esPersonal): ?> · C.I. <?php echo $bt_resaltar($t['Ci']); ?><?php endif; ?></span></div>
                <div class="bt-datos">
                    <?php if ($aprobada): ?>
                        <span><i class="fas fa-calendar-check"></i>Aprobada el <?php echo $bt_fecha($t['FechaAprobacion']); ?></span>
                    <?php elseif ($t['FechaEntrega']): ?>
                        <span><i class="fas fa-calendar"></i>Entregada el <?php echo $bt_fecha($t['FechaEntrega']); ?></span>
                    <?php endif; ?>
                    <?php if ($t['Sede']): ?><span><i class="fas fa-map-marker-alt"></i><?php echo $bt_e($t['Sede']); ?></span><?php endif; ?>
                    <?php if ($bt_esPersonal && $t['CalificacionNumeral'] !== null): ?>
                        <span><i class="fas fa-star"></i>Calificación: <?php echo $bt_e(rtrim(rtrim($t['CalificacionNumeral'], '0'), '.')); ?></span>
                    <?php endif; ?>
                </div>
                <div class="bt-desplegables">
                    <?php if ($tribunal): ?>
                        <details>
                            <summary>Tribunal evaluador</summary>
                            <div class="bt-tribunal">
                                <?php foreach ($tribunal as $linea): ?>
                                    <div><?php if ($linea[0] !== ''): ?><span><?php echo $bt_e($linea[0]); ?></span><?php endif; ?><?php echo $bt_e($linea[1]); ?></div>
                                <?php endforeach; ?>
                            </div>
                        </details>
                    <?php endif; ?>
                    <?php if ($aprobada): ?>
                        <details>
                            <summary>Cómo citar (APA 7)</summary>
                            <div class="bt-cita">
                                <p class="bt-cita-texto"><?php echo $bt_e(BibliotecaTesisModelo::ReferenciaApa($t, $bt_universidad)); ?></p>
                                <button type="button" class="bt-copiar"><i class="far fa-copy"></i> Copiar</button>
                            </div>
                            <div class="bt-cita-nota">Revise las siglas del título: el sistema lo guarda en mayúsculas.</div>
                        </details>
                    <?php endif; ?>
                </div>
            </div>
            <div class="bt-lateral">
                <?php if (!$aprobada): ?>
                    <span class="bt-disp proceso">EN PROCESO<small>aún no aprobada</small></span>
                <?php elseif ($ejemplar === 'PRESTADO'): ?>
                    <span class="bt-disp prestada">PRESTADA<small>vuelve el <?php echo $bt_fecha($t['FechaLimiteEjemplar']); ?></small></span>
                <?php elseif ($ejemplar === 'APROBADO'): ?>
                    <span class="bt-disp reservada">RESERVADA<small>por recoger</small></span>
                <?php else: ?>
                    <span class="bt-disp si"><?php echo $bt_hayPrestamos ? 'DISPONIBLE' : 'APROBADA'; ?><?php if ($bt_hayPrestamos): ?><small>ejemplar en biblioteca</small><?php endif; ?></span>
                <?php endif; ?>

                <?php if ($bt_esLector && $aprobada && $bt_hayPrestamos): ?>
                    <?php if ($mio && $mio['Estado'] === 'PRESTADO'): ?>
                        <div class="bt-propio"><i class="fas fa-book-reader"></i> La tiene usted hasta el <b><?php echo $bt_fecha($mio['FechaLimite']); ?></b></div>
                    <?php elseif ($mio && $mio['Estado'] === 'APROBADO'): ?>
                        <div class="bt-propio"><i class="fas fa-check-circle" style="color:#15803d;"></i> Reservada para usted: pase a recogerla</div>
                    <?php elseif ($mio): ?>
                        <div class="bt-propio"><i class="fas fa-clock"></i> Solicitud enviada<br><a href="#" class="bt-cancelar" data-id="<?php echo (int) $mio['IdPrestamo']; ?>">Cancelar solicitud</a></div>
                    <?php else: ?>
                        <button type="button" class="bt-accion bt-solicitar" data-id="<?php echo (int) $t['IdTrabajo']; ?>"
                                data-ocupada="<?php echo $ejemplar === 'DISPONIBLE' ? '0' : '1'; ?>">
                            <i class="fas fa-hand-holding"></i> Solicitar préstamo
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>

    <?php if ($bt_paginas > 1): ?>
        <nav class="bt-paginas" aria-label="Páginas de resultados">
            <?php
            $bt_enlacePagina = function ($n, $texto, $clase = '') use ($bt_url, $bt_filtrosUrl) {
                return '<a class="' . $clase . '" href="' . $bt_url($bt_filtrosUrl + ['pag' => $n > 1 ? $n : 0]) . '">' . $texto . '</a>';
            };
            echo $bt_pagina > 1 ? $bt_enlacePagina($bt_pagina - 1, '&laquo; Anterior') : '<span class="inactivo">&laquo; Anterior</span>';
            $desde = max(1, $bt_pagina - 2);
            $hasta = min($bt_paginas, $bt_pagina + 2);
            if ($desde > 1) echo $bt_enlacePagina(1, '1') . ($desde > 2 ? '<span class="inactivo">…</span>' : '');
            for ($n = $desde; $n <= $hasta; $n++) {
                echo $n === $bt_pagina ? '<span class="actual" aria-current="page">' . $n . '</span>' : $bt_enlacePagina($n, (string) $n);
            }
            if ($hasta < $bt_paginas) echo ($hasta < $bt_paginas - 1 ? '<span class="inactivo">…</span>' : '') . $bt_enlacePagina($bt_paginas, (string) $bt_paginas);
            echo $bt_pagina < $bt_paginas ? $bt_enlacePagina($bt_pagina + 1, 'Siguiente &raquo;') : '<span class="inactivo">Siguiente &raquo;</span>';
            ?>
        </nav>
    <?php endif; ?>

<?php elseif ($bt_vista === 'misprestamos'):
    /* ======================= MIS PRÉSTAMOS (EST / DOC) ======================= */
    $bt_mis = PrestamoTesisModelo::MisPrestamos($bt_tipo, $bt_personaID);
?>
    <div class="bt-aviso">
        <i class="fas fa-info-circle"></i>
        Cuando su solicitud figure como <strong>Reservada</strong>, recoja el ejemplar en la oficina de Posgrado.
        El préstamo es por <strong><?php echo $bt_plazo; ?> <?php echo $bt_plazo === 1 ? 'día' : 'días'; ?></strong>; puede tener hasta
        <?php echo PrestamoTesisModelo::MAX_ACTIVOS_POR_PERSONA; ?> tesis solicitadas o prestadas a la vez.
    </div>

    <?php if (!$bt_mis): ?>
        <div class="bt-vacio">
            <i class="fas fa-book-reader"></i>
            <p>Todavía no solicitó ninguna tesis.</p>
            <p><a href="<?php echo $bt_url([]); ?>">Buscar en el catálogo</a></p>
        </div>
    <?php endif; ?>

    <?php foreach ($bt_mis as $p):
        $estado = $p['Estado'];
        $vencido = $estado === 'PRESTADO' && $p['FechaLimite'] < $bt_hoy;
        $orden = ['SOLICITADO' => 1, 'APROBADO' => 2, 'PRESTADO' => 3, 'DEVUELTO' => 4];
        $nivel = $orden[$estado] ?? 0;
        $pasos = [
            ['Solicitada', $p['FechaSolicitud']],
            ['Reservada', $p['FechaAprobacion']],
            ['Prestada', $p['FechaEntrega']],
            ['Devuelta', $p['FechaDevolucion']],
        ];
    ?>
        <div class="bt-prestamo <?php echo $vencido ? 'vencido' : ''; ?>">
            <div class="bt-prestamo-cab">
                <div style="min-width:0;flex:1;">
                    <h4><?php echo $bt_e($p['TituloTrabajo']); ?></h4>
                    <div class="sub"><?php echo $bt_e(trim($p['Nombre'] . ' ' . $p['Apaterno'] . ' ' . $p['Amaterno']) . ' · ' . ($bt_grados[$p['GradoAcademico']][1] ?? $p['GradoAcademico']) . ' · ' . $p['NombrePrograma']); ?></div>
                </div>
                <div>
                    <?php if ($vencido): ?>
                        <span class="bt-estado VENCIDO">DEVOLUCIÓN VENCIDA</span>
                    <?php else: ?>
                        <span class="bt-estado <?php echo $bt_e($estado); ?>"><?php echo $bt_e(['SOLICITADO' => 'SOLICITADA', 'APROBADO' => 'RESERVADA', 'PRESTADO' => 'PRESTADA', 'DEVUELTO' => 'DEVUELTA', 'RECHAZADO' => 'RECHAZADA', 'CANCELADO' => 'CANCELADA'][$estado] ?? $estado); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($nivel > 0): ?>
                <div class="bt-pasos">
                    <?php foreach ($pasos as $i => $paso):
                        $clase = ($i + 1 < $nivel || $estado === 'DEVUELTO') ? 'hecho' : (($i + 1 === $nivel) ? 'hecho actual' : '');
                    ?>
                        <div class="bt-paso <?php echo $clase; ?>">
                            <span class="punto"><?php echo $clase ? '<i class="fas fa-check"></i>' : $i + 1; ?></span>
                            <div><?php echo $paso[0]; ?></div>
                            <small><?php echo $bt_fecha($paso[1]); ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="bt-nota">
                <?php if ($estado === 'SOLICITADO'): ?>
                    <i class="fas fa-clock"></i>
                    <?php echo $p['OcupadaPorOtro'] ? 'El ejemplar está prestado a otra persona: su solicitud queda en espera.' : 'Esperando que Posgrado reserve el ejemplar.'; ?>
                <?php elseif ($estado === 'APROBADO'): ?>
                    <i class="fas fa-check-circle" style="color:#15803d;"></i> El ejemplar está reservado para usted: pase a recogerlo a la oficina de Posgrado.
                <?php elseif ($estado === 'PRESTADO'): ?>
                    <i class="fas fa-calendar-alt"></i> Devuélvala hasta el <strong><?php echo $bt_fecha($p['FechaLimite']); ?></strong>.
                    <?php if ($vencido): ?><strong style="color:#b91c1c;"> El plazo ya venció: devuélvala lo antes posible.</strong><?php endif; ?>
                <?php elseif ($estado === 'DEVUELTO'): ?>
                    <i class="fas fa-check"></i> Devuelta el <?php echo $bt_fechaHora($p['FechaDevolucion']); ?>.
                <?php elseif ($estado === 'RECHAZADO'): ?>
                    <i class="fas fa-times-circle" style="color:#b91c1c;"></i> Rechazada el <?php echo $bt_fecha($p['FechaCierre']); ?><?php echo $p['Motivo'] ? ': ' . $bt_e($p['Motivo']) : ''; ?>
                <?php elseif ($estado === 'CANCELADO'): ?>
                    <i class="fas fa-ban"></i> Cancelada el <?php echo $bt_fecha($p['FechaCierre']); ?>.
                <?php endif; ?>
                <?php if (in_array($estado, ['SOLICITADO', 'APROBADO'], true)): ?>
                    · <a href="#" class="bt-cancelar" data-id="<?php echo (int) $p['IdPrestamo']; ?>" style="color:#b91c1c;">Cancelar solicitud</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

<?php elseif ($bt_vista === 'prestamos'):
    /* ======================= GESTIÓN DE PRÉSTAMOS (ADM) ======================= */
    $bt_lista = in_array($_GET['lista'] ?? '', ['pendientes', 'prestadas', 'historial'], true) ? $_GET['lista'] : 'pendientes';
    $bt_filas = PrestamoTesisModelo::ListarGestion($bt_lista);
    $bt_c = $bt_contadores;
?>
    <?php if (!$bt_hayPrestamos): ?>
        <div class="bt-aviso"><i class="fas fa-exclamation-triangle"></i> Falta crear la tabla de préstamos: ejecute <code>mantenimiento/crear_prestamotesis.sql</code>.</div>
    <?php endif; ?>
    <div class="bt-sublistas">
        <a href="<?php echo $bt_url(['vista' => 'prestamos']); ?>" class="<?php echo $bt_lista === 'pendientes' ? 'activa' : ''; ?>">Solicitudes por atender <span class="bt-contador <?php echo $bt_c['solicitadas'] ? 'alerta' : ''; ?>"><?php echo $bt_c['pendientes']; ?></span></a>
        <a href="<?php echo $bt_url(['vista' => 'prestamos', 'lista' => 'prestadas']); ?>" class="<?php echo $bt_lista === 'prestadas' ? 'activa' : ''; ?>">Prestadas <span class="bt-contador <?php echo $bt_c['vencidas'] ? 'alerta' : ''; ?>"><?php echo $bt_c['prestadas']; ?><?php echo $bt_c['vencidas'] ? ' · ' . $bt_c['vencidas'] . ' vencida' . ($bt_c['vencidas'] > 1 ? 's' : '') : ''; ?></span></a>
        <a href="<?php echo $bt_url(['vista' => 'prestamos', 'lista' => 'historial']); ?>" class="<?php echo $bt_lista === 'historial' ? 'activa' : ''; ?>">Historial</a>
    </div>
    <p class="bt-ayuda" style="margin:-4px 0 12px;">Plazo de préstamo: <strong><?php echo $bt_plazo; ?> días</strong> (se cambia en Configuración &gt; Parámetros académicos). Un ejemplar por tesis.</p>

    <?php if (!$bt_filas): ?>
        <div class="bt-vacio"><i class="fas fa-inbox"></i><p><?php echo ['pendientes' => 'No hay solicitudes por atender.', 'prestadas' => 'No hay tesis prestadas.', 'historial' => 'Todavía no hay préstamos cerrados.'][$bt_lista]; ?></p></div>
    <?php else: ?>
        <div class="bt-tabla-envoltura">
            <table class="bt-tabla">
                <thead>
                    <tr>
                        <th>Solicitante</th>
                        <th>Tesis</th>
                        <th><?php echo $bt_lista === 'prestadas' ? 'Entrega / Devolver hasta' : ($bt_lista === 'historial' ? 'Fechas' : 'Solicitada'); ?></th>
                        <th>Estado</th>
                        <?php if ($bt_lista !== 'historial'): ?><th>Acciones</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($bt_filas as $p):
                    $vencido = $p['Estado'] === 'PRESTADO' && $p['FechaLimite'] < $bt_hoy;
                    $diasVencido = $vencido ? (int) ((strtotime($bt_hoy) - strtotime($p['FechaLimite'])) / 86400) : 0;
                ?>
                    <tr class="<?php echo $vencido ? 'vencido' : ''; ?>">
                        <td>
                            <strong><?php echo $bt_e($p['Solicitante']); ?></strong><br>
                            <small class="text-muted"><?php echo $p['TipoSolicitante'] === 'DOC' ? 'Docente' : 'Estudiante'; ?> · C.I. <?php echo $bt_e($p['CiSolicitante']); ?><?php echo $p['CelularSolicitante'] ? ' · Cel. ' . $bt_e($p['CelularSolicitante']) : ''; ?></small>
                        </td>
                        <td style="max-width:380px;">
                            <span class="bt-serif" style="color:#1f3a5f;font-weight:600;"><?php echo $bt_e($p['TituloTrabajo']); ?></span><br>
                            <small class="text-muted">Autor: <?php echo $bt_e($p['Autor']); ?></small>
                        </td>
                        <td style="white-space:nowrap;">
                            <?php if ($bt_lista === 'prestadas'): ?>
                                <?php echo $bt_fecha($p['FechaEntrega']); ?><br>
                                <strong style="<?php echo $vencido ? 'color:#b91c1c;' : ''; ?>"><?php echo $bt_fecha($p['FechaLimite']); ?></strong>
                                <?php if ($vencido): ?><br><small style="color:#b91c1c;">vencida hace <?php echo $diasVencido; ?> día<?php echo $diasVencido === 1 ? '' : 's'; ?></small><?php endif; ?>
                            <?php elseif ($bt_lista === 'historial'): ?>
                                <small>Solicitada: <?php echo $bt_fecha($p['FechaSolicitud']); ?></small><br>
                                <?php if ($p['FechaEntrega']): ?><small>Prestada: <?php echo $bt_fecha($p['FechaEntrega']); ?> (hasta <?php echo $bt_fecha($p['FechaLimite']); ?>)</small><br><?php endif; ?>
                                <?php if ($p['FechaDevolucion']): ?><small>Devuelta: <?php echo $bt_fecha($p['FechaDevolucion']); ?></small><?php endif; ?>
                                <?php if ($p['FechaCierre']): ?><small>Cerrada: <?php echo $bt_fecha($p['FechaCierre']); ?></small><?php endif; ?>
                            <?php else: ?>
                                <?php echo $bt_fechaHora($p['FechaSolicitud']); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="bt-estado <?php echo $vencido ? 'VENCIDO' : $bt_e($p['Estado']); ?>"><?php echo $vencido ? 'VENCIDA' : $bt_e(['SOLICITADO' => 'SOLICITADA', 'APROBADO' => 'RESERVADA', 'PRESTADO' => 'PRESTADA', 'DEVUELTO' => 'DEVUELTA', 'RECHAZADO' => 'RECHAZADA', 'CANCELADO' => 'CANCELADA'][$p['Estado']] ?? $p['Estado']); ?></span>
                            <?php if ($p['Estado'] === 'SOLICITADO' && $p['OcupadaPorOtro']): ?><br><small class="text-muted">Ejemplar ocupado: en espera</small><?php endif; ?>
                            <?php if ($bt_lista === 'historial' && $p['Motivo']): ?><br><small class="text-muted"><?php echo $bt_e($p['Motivo']); ?></small><?php endif; ?>
                            <?php
                            $atendio = $p['ResponsableDevolucion'] ?: ($p['ResponsableCierre'] ?: ($p['ResponsableEntrega'] ?: $p['ResponsableAprobacion']));
                            if ($atendio): ?><br><small class="text-muted">Por: <?php echo $bt_e($atendio); ?></small><?php endif; ?>
                        </td>
                        <?php if ($bt_lista !== 'historial'): ?>
                            <td>
                                <div class="acciones">
                                    <?php if ($p['Estado'] === 'SOLICITADO'): ?>
                                        <button type="button" class="bt-accion secundaria bt-gestion" data-accion="reservar" data-id="<?php echo (int) $p['IdPrestamo']; ?>" <?php echo $p['OcupadaPorOtro'] ? 'disabled title="El ejemplar está reservado o prestado a otra persona"' : ''; ?>><i class="fas fa-bookmark"></i> Reservar</button>
                                    <?php endif; ?>
                                    <?php if (in_array($p['Estado'], ['SOLICITADO', 'APROBADO'], true)): ?>
                                        <button type="button" class="bt-accion bt-gestion" data-accion="entregar" data-id="<?php echo (int) $p['IdPrestamo']; ?>" <?php echo $p['OcupadaPorOtro'] ? 'disabled title="El ejemplar está reservado o prestado a otra persona"' : ''; ?>><i class="fas fa-hand-holding"></i> Entregar</button>
                                        <button type="button" class="bt-accion peligro bt-gestion" data-accion="rechazar" data-id="<?php echo (int) $p['IdPrestamo']; ?>"><i class="fas fa-times"></i> Rechazar</button>
                                    <?php endif; ?>
                                    <?php if ($p['Estado'] === 'PRESTADO'): ?>
                                        <button type="button" class="bt-accion exito bt-gestion" data-accion="devolver" data-id="<?php echo (int) $p['IdPrestamo']; ?>"><i class="fas fa-undo"></i> Registrar devolución</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
<?php endif; ?>
</div>

<?php include __DIR__ . '/modulos/certificado-pie.php'; ?>
<script>
(function () {
    var URL_AJAX = 'ajax/bibliotecatesis.ajax.php';
    var PLAZO = <?php echo (int) $bt_plazo; ?>;

    function enviar(datos, $boton) {
        if ($boton) $boton.prop('disabled', true);
        return $.post(URL_AJAX, datos, null, 'json').done(function (resp) {
            if (resp && resp.success) {
                Swal.fire({ type: 'success', title: resp.mensaje, timer: 2200, showConfirmButton: false })
                    .then(function () { location.reload(); });
            } else {
                Swal.fire('Atención', (resp && resp.mensaje) || 'No se pudo guardar', 'warning');
                if ($boton) $boton.prop('disabled', false);
            }
        }).fail(function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.mensaje) || 'No se pudo conectar con el servidor';
            Swal.fire('Error', msg, 'error');
            if ($boton) $boton.prop('disabled', false);
        });
    }

    // Copiar la referencia APA
    $(document).on('click', '.bt-copiar', function () {
        var $b = $(this);
        var texto = $b.siblings('.bt-cita-texto').text().trim();
        var listo = function () { $b.html('<i class="fas fa-check"></i> Copiada'); setTimeout(function () { $b.html('<i class="far fa-copy"></i> Copiar'); }, 1800); };
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(texto).then(listo);
        } else {
            var $t = $('<textarea>').val(texto).css({ position: 'fixed', opacity: 0 }).appendTo('body');
            $t[0].select();
            try { document.execCommand('copy'); listo(); } catch (e) { /* sin portapapeles */ }
            $t.remove();
        }
    });

    // Estudiante / docente: solicitar
    $(document).on('click', '.bt-solicitar', function () {
        var $b = $(this);
        var titulo = $b.closest('.bt-ficha').find('.bt-titulo').text().trim();
        var ocupada = $b.data('ocupada') == 1;
        Swal.fire({
            type: 'question',
            title: '¿Solicitar esta tesis en préstamo?',
            html: '<p style="font-family:Georgia,serif;">' + $('<div>').text(titulo).html() + '</p>' +
                  '<p style="font-size:13px;">' + (ocupada
                    ? 'El ejemplar está prestado o reservado: su solicitud quedará <b>en espera</b> hasta que lo devuelvan.'
                    : 'Cuando Posgrado lo reserve, recójalo en la oficina. El préstamo es por <b>' + PLAZO + ' días</b>.') + '</p>',
            showCancelButton: true,
            confirmButtonText: 'Sí, solicitar',
            cancelButtonText: 'Cancelar'
        }).then(function (r) {
            if (r.value) enviar({ accion: 'solicitar', idTrabajo: $b.data('id') }, $b);
        });
    });

    // Estudiante / docente: cancelar
    $(document).on('click', '.bt-cancelar', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        Swal.fire({
            type: 'warning', title: '¿Cancelar la solicitud?', showCancelButton: true,
            confirmButtonText: 'Sí, cancelar', cancelButtonText: 'No'
        }).then(function (r) {
            if (r.value) enviar({ accion: 'cancelar', idPrestamo: id });
        });
    });

    // Administrador: reservar, entregar, rechazar, devolver
    $(document).on('click', '.bt-gestion', function () {
        var $b = $(this);
        var accion = $b.data('accion');
        var id = $b.data('id');
        var fila = $b.closest('tr');
        var quien = fila.find('td:first strong').text();
        var config = {
            reservar: { type: 'question', title: '¿Reservar el ejemplar para ' + quien + '?', text: 'Verá en Mis préstamos que puede pasar a recogerlo.', confirmButtonText: 'Reservar' },
            entregar: { type: 'question', title: '¿Entregar el ejemplar a ' + quien + '?', text: 'Debe devolverlo en ' + PLAZO + ' días.', confirmButtonText: 'Sí, entregar' },
            rechazar: { type: 'warning', title: 'Rechazar la solicitud de ' + quien, input: 'textarea', inputPlaceholder: 'Motivo del rechazo (lo verá el solicitante)', confirmButtonText: 'Rechazar',
                        inputValidator: function (v) { return (!v || v.trim().length < 5) ? 'Escriba el motivo' : undefined; } },
            devolver: { type: 'question', title: '¿' + quien + ' devolvió el ejemplar?', input: 'text', inputPlaceholder: 'Observación (opcional): estado del ejemplar...', confirmButtonText: 'Registrar devolución' }
        }[accion];
        Swal.fire($.extend({ showCancelButton: true, cancelButtonText: 'Cancelar' }, config)).then(function (r) {
            if (r.value === undefined || r.dismiss) return; // SweetAlert2 v8
            enviar({ accion: accion, idPrestamo: id, texto: typeof r.value === 'string' ? r.value : '' }, $b);
        });
    });
})();
</script>
</body>
