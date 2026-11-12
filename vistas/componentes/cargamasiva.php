<?php
/**
 * Carga masiva desde Excel (solo ADM): estudiantes, notas y trabajo de grado de un programa
 * que ya terminó, para emitir sus certificados sin inscribirlos uno por uno.
 * La plantilla se arma y el Excel se lee en el navegador (scripts/cargamasiva.js);
 * el servidor valida y registra (ajax/cargamasiva.ajax.php, modelos/cargamasiva.modelo.php).
 */
$certTitulo = 'CARGA MASIVA DESDE EXCEL';
$certSubtitulo = 'ESTUDIANTES, NOTAS Y TRABAJOS DE GRADO DE PROGRAMAS TERMINADOS';
include __DIR__ . '/modulos/certificado-encabezado.php';
require_once 'modelos/cargamasiva.modelo.php';

$cm_e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$cm_programas = CargaMasivaModelo::ProgramasModelo();
?>
<style>
    .cm-pasos { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    .cm-paso { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:18px 20px; }
    .cm-paso h3 { font-size:15px; font-weight:700; color:#1f3a5f; margin:0 0 12px; display:flex; align-items:center; gap:10px; }
    .cm-num { width:28px; height:28px; border-radius:50%; background:#1f3a5f; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:13px; flex:0 0 28px; }
    .cm-paso p, .cm-paso li { font-size:13px; color:#475569; }
    .cm-paso ul { padding-left:18px; margin:0 0 4px; }
    .cm-paso select { width:100%; height:40px; border:1px solid #cbd5e1; border-radius:8px; padding:0 8px; font-size:13px; }
    .cm-info { font-size:12.5px; color:#475569; margin:8px 0 12px; min-height:18px; }
    .cm-btn { border:0; border-radius:8px; padding:10px 18px; font-weight:600; font-size:13.5px; color:#fff; background:#1f3a5f; }
    .cm-btn:hover { background:#2d5b8a; }
    .cm-btn:disabled { opacity:.5; cursor:not-allowed; }
    .cm-btn.verde { background:#15803d; } .cm-btn.verde:hover { background:#166534; }
    .cm-colores { display:flex; flex-wrap:wrap; gap:6px 14px; font-size:12px; color:#475569; margin-top:8px; }
    .cm-colores span::before { content:""; display:inline-block; width:12px; height:12px; border-radius:3px; margin-right:5px; vertical-align:-2px; background:var(--c); }
    .cm-subir { border:2px dashed #cbd5e1; border-radius:12px; padding:22px; text-align:center; background:#f8fafc; }
    .cm-subir i.fa-file-excel { font-size:34px; color:#15803d; display:block; margin-bottom:8px; }
    .cm-subir label { display:inline-block; cursor:pointer; padding:8px 16px; border:1px solid #cbd5e1; border-radius:8px; background:#fff; font-size:13px; font-weight:600; margin:6px 0; }
    .cm-subir input { position:absolute; opacity:0; width:1px; height:1px; }
    .cm-subir input:focus-visible + label { box-shadow:0 0 0 3px rgba(45,91,138,.3); }
    #cm-nombre-archivo { display:block; font-size:12.5px; color:#475569; margin-bottom:12px; }

    #cm-resultado { margin-top:18px; background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:18px 20px; }
    .cm-programa { font-size:13.5px; color:#1f3a5f; margin-bottom:12px; }
    .cm-cifras { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:14px; }
    .cm-cifra { border:1px solid #e2e8f0; border-radius:10px; padding:8px 14px; min-width:120px; background:#f8fafc; }
    .cm-cifra b { display:block; font-size:22px; color:#1e293b; }
    .cm-cifra span { font-size:12px; color:#64748b; }
    .cm-cifra.ok { border-color:#bbf7d0; background:#f0fdf4; } .cm-cifra.ok b { color:#15803d; }
    .cm-cifra.error { border-color:#fecaca; background:#fef2f2; } .cm-cifra.error b { color:#b91c1c; }
    .cm-confirmar { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:12px 16px; margin-bottom:14px; font-size:13px; }
    .cm-filtro { display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; font-size:13px; gap:10px; flex-wrap:wrap; }
    .cm-filtro label { margin:0; display:inline-flex; gap:6px; align-items:center; }
    .cm-tabla-envoltura { overflow-x:auto; border:1px solid #e2e8f0; border-radius:10px; max-height:560px; overflow-y:auto; }
    .cm-tabla { width:100%; border-collapse:collapse; font-size:12.5px; }
    .cm-tabla th { position:sticky; top:0; background:#f1f5f9; text-align:left; padding:8px 10px; font-size:11.5px; text-transform:uppercase; color:#475569; z-index:1; }
    .cm-tabla td { padding:8px 10px; border-top:1px solid #f1f5f9; vertical-align:top; }
    .cm-tabla tr.cm-error td { background:#fef2f2; }
    .cm-tabla tr.cm-aviso td { background:#fffbeb; }
    .cm-msg { margin-bottom:2px; } .cm-msg.error { color:#b91c1c; } .cm-msg.aviso { color:#92400e; }
    .cm-badge { display:inline-block; font-size:10.5px; font-weight:700; padding:2px 8px; border-radius:20px; }
    .cm-badge.error { background:#fee2e2; color:#991b1b; } .cm-badge.ok { background:#dcfce7; color:#166534; }
    @media (max-width: 991px) { .cm-pasos { grid-template-columns:1fr; } }
</style>

<div class="cm-pasos">
    <section class="cm-paso">
        <h3><span class="cm-num">1</span> Descargar la plantilla del programa</h3>
        <label for="cm-programa" class="sr-only">Programa</label>
        <select id="cm-programa">
            <option value="">Elija el programa...</option>
            <?php foreach ($cm_programas as $p): ?>
                <option value="<?php echo (int) $p['ProgramaID']; ?>" data-modulos="<?php echo (int) $p['TotalModulos']; ?>" data-inscritos="<?php echo (int) $p['Inscritos']; ?>">
                    <?php echo $cm_e($p['NombrePrograma'] . ($p['Version'] ? ' (' . $p['Version'] . ')' : '') . ($p['Sede'] ? ' · ' . $p['Sede'] : '')); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="cm-info" id="cm-info-programa"></div>
        <button type="button" class="cm-btn" id="cm-descargar" disabled><i class="fas fa-file-download"></i> Descargar plantilla Excel</button>
        <div class="cm-colores">
            <span style="--c:#1f3a5f">Obligatorio</span><span style="--c:#5b7fa6">Opcional</span>
            <span style="--c:#b8892b">Nota de cada módulo</span><span style="--c:#2f5d3a">Trabajo de grado (opcional)</span>
        </div>
    </section>

    <section class="cm-paso">
        <h3><span class="cm-num">2</span> Llenar y subir el Excel</h3>
        <ul>
            <li>Una fila por estudiante desde la fila 3; fechas en dd/mm/aaaa; notas de 0 a 100.</li>
            <li>Si el C.I. ya existe, se usa ese estudiante sin cambiar sus datos.</li>
            <li>La inscripción queda como <b>carga histórica</b> (sin órdenes de pago).</li>
            <li>Pagos (opcionales): total a pagar del programa, matrícula y cada cuota con su monto, fecha y voucher. Con ellos el saldo y los reportes de costos salen correctos; sin ellos, el estudiante figura con saldo igual al costo del programa.</li>
            <li>Primero se <b>revisa</b> (no se guarda nada); luego usted decide registrar.</li>
        </ul>
        <div class="cm-subir">
            <i class="fas fa-file-excel"></i>
            <input type="file" id="cm-archivo" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
            <label for="cm-archivo"><i class="fas fa-folder-open"></i> Elegir archivo .xlsx</label>
            <span id="cm-nombre-archivo">Ningún archivo elegido</span>
            <button type="button" class="cm-btn verde" id="cm-revisar" disabled><i class="fas fa-search"></i> Revisar Excel</button>
        </div>
    </section>
</div>

<div id="cm-resultado" style="display:none;" aria-live="polite"></div>

<?php include __DIR__ . '/modulos/certificado-pie.php'; ?>
<script src="vistas/recursos/assets/js/scripts/cargamasiva.js?v=<?php echo filemtime(__DIR__ . '/../recursos/assets/js/scripts/cargamasiva.js'); ?>"></script>
</body>
