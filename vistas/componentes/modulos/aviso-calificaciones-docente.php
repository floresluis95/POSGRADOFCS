<?php
/**
 * Aviso al docente: el Administrador le pidió registrar las calificaciones de módulos ya
 * terminados (Registrar Notas > Calificaciones pendientes). Se incluye en el panel y en
 * Reporte de calificaciones; "Entendido" lo marca como visto. Si ya calificó el módulo,
 * el aviso deja de mostrarse solo.
 */
if (($_SESSION['Tipo'] ?? '') === 'DOC') {
    require_once __DIR__ . '/../../../modelos/avisocalificacion.modelo.php';
    $avisosCalificacion = AvisoCalificacionModelo::AvisosDocenteModelo($_SESSION['DocenteID'] ?? 0);
}
if (!empty($avisosCalificacion)):
    $ultimoAviso = $avisosCalificacion[0];
    $modulosAviso = [];
    foreach ($avisosCalificacion as $av) {
        $modulosAviso[$av['Idmodulo']] = $av; // un módulo una sola vez aunque tenga varios avisos
    }
?>
<style>
.av-cal { display: flex; gap: 14px; background: #fff; border: 1px solid #f3c9c9; border-left: 4px solid #b91c1c; border-radius: 8px;
    padding: 14px 16px; margin: 0 0 16px; color: #1f2937; }
.av-cal-icono { flex: 0 0 36px; height: 36px; border-radius: 50%; background: #fef2f2; color: #b91c1c; display: flex; align-items: center; justify-content: center; }
.av-cal-cuerpo { flex: 1; min-width: 0; }
.av-cal h4 { margin: 0 0 4px; font-size: 15px; font-weight: 700; color: #b91c1c; }
.av-cal-mensaje { margin: 0 0 8px; font-size: 13px; }
.av-cal-de { font-size: 11.5px; color: #64748b; }
.av-cal ul { margin: 0 0 10px; padding-left: 18px; font-size: 13px; }
.av-cal li small { color: #64748b; }
.av-cal-acciones { display: flex; flex-wrap: wrap; gap: 8px; }
.av-cal-acciones .btn { font-size: 12.5px; font-weight: 600; padding: 5px 12px; border-radius: 6px; }
.av-cal-ir { background: #b91c1c; border: 1px solid #b91c1c; color: #fff; }
.av-cal-ir:hover { background: #991b1b; color: #fff; }
.av-cal-ok { background: #fff; border: 1px solid #cbd5e1; color: #1f2937; }
</style>
<div class="av-cal" id="avisoCalificaciones" role="alert">
    <span class="av-cal-icono"><i class="fas fa-bell"></i></span>
    <div class="av-cal-cuerpo">
        <h4>Debe registrar calificaciones</h4>
        <p class="av-cal-mensaje"><?php echo htmlspecialchars($ultimoAviso['Mensaje']); ?></p>
        <ul>
            <?php foreach ($modulosAviso as $mod): ?>
                <li><strong><?php echo htmlspecialchars(trim($mod['CodigoModulo'] . ' · ' . $mod['NombreModulo'])); ?></strong>
                    <small> — <?php echo htmlspecialchars($mod['NombrePrograma']); ?> · terminó el <?php echo date('d/m/Y', strtotime($mod['FechaFinal'])); ?>
                    · <?php echo (int)$mod['TotalCalificados']; ?> de <?php echo (int)$mod['TotalEstudiantes']; ?> calificados</small></li>
            <?php endforeach; ?>
        </ul>
        <div class="av-cal-acciones">
            <a class="btn av-cal-ir" href="reportecalificaciones"><i class="fas fa-edit"></i> Registrar calificaciones</a>
            <button type="button" class="btn av-cal-ok" id="avisoCalificacionesOk"><i class="fas fa-check"></i> Entendido</button>
            <span class="av-cal-de align-self-center">Enviado por <?php echo htmlspecialchars($ultimoAviso['EnviadoPor'] ?: 'Coordinación'); ?> el <?php echo date('d/m/Y H:i', strtotime($ultimoAviso['FechaEnvio'])); ?></span>
        </div>
    </div>
</div>
<script>
document.getElementById('avisoCalificacionesOk').addEventListener('click', function () {
    var datos = new FormData();
    datos.append('accion', 'entendido');
    fetch('ajax/avisocalificacion.ajax.php', { method: 'POST', body: datos, credentials: 'same-origin' })
        .catch(function () {})
        .then(function () { document.getElementById('avisoCalificaciones').remove(); });
});
</script>
<?php endif; ?>
