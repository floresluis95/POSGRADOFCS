<?php
/**
 * Panel principal: tarjetas "Accesos rápidos" y "Próximos eventos", comunes a
 * todos los tipos de usuario. Se incluye desde panel.php y usa $tablero
 * (TableroControlador::DatosTableroControlador).
 */
$coloresEvento = ['CLASE' => 'is-azul', 'ACTIVIDAD' => 'is-ambar', 'CLINICA' => 'is-clinica'];
$nombresEvento = ['CLASE' => 'Clase', 'ACTIVIDAD' => 'Actividad', 'CLINICA' => 'Clínica'];
?>
<?php if (!empty($tablero['accesos'])): ?>
<div class="pn-card">
  <div class="pn-card-head">
    <h3><i class="fas fa-bolt"></i> Accesos rápidos</h3>
  </div>
  <div class="pn-accesos">
    <?php foreach ($tablero['accesos'] as [$ruta, $icono, $titulo, $descripcion]): ?>
    <a href="<?php echo htmlspecialchars($ruta); ?>" class="pn-acceso">
      <span class="pn-acceso-icono"><i class="fas <?php echo htmlspecialchars($icono); ?>"></i></span>
      <span>
        <strong><?php echo htmlspecialchars($titulo); ?></strong>
        <small><?php echo htmlspecialchars($descripcion); ?></small>
      </span>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<div class="pn-card">
  <div class="pn-card-head">
    <h3><i class="far fa-calendar-alt"></i> Próximos eventos</h3>
    <a href="calendario" class="pn-link">Ver calendario</a>
  </div>
  <?php if (empty($tablero['eventos'])): ?>
    <div class="pn-vacio"><i class="far fa-calendar-check"></i>No hay eventos en los próximos 30 días.</div>
  <?php else: ?>
  <ul class="pn-lista">
    <?php foreach ($tablero['eventos'] as $ev): ?>
    <?php $tsEvento = strtotime($ev['FechaInicio']); ?>
    <li class="pn-item">
      <span class="pn-fecha-caja <?php echo $coloresEvento[$ev['TipoEvento']] ?? ''; ?>">
        <strong><?php echo date('d', $tsEvento); ?></strong>
        <small><?php echo $mesesCortos[(int)date('n', $tsEvento) - 1]; ?></small>
      </span>
      <div class="pn-item-cuerpo">
        <strong><?php echo htmlspecialchars($ev['Titulo']); ?></strong>
        <small><?php echo $nombresEvento[$ev['TipoEvento']] ?? htmlspecialchars($ev['TipoEvento']); ?> · <?php echo date('H:i', $tsEvento); ?> · <?php echo htmlspecialchars($ev['NombrePrograma']); ?></small>
      </div>
    </li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</div>
