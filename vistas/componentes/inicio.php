<?php
    /**
     * Portal público (página de inicio del sistema).
     * Muestra el contenido informativo del Panel (carrusel de programas,
     * noticias, cronograma de defensa y redes sociales) en SOLO LECTURA y sin
     * requerir sesión. La administración de ese contenido sigue en "panel",
     * que exige inicio de sesión.
     */
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    date_default_timezone_set("America/La_Paz");

    $haySesion = !empty($_SESSION['Validar']);

    $noticiasPanel = NoticiaPanelModelo::ListarActivasModelo();
    $defensasPanel = CronogramaDefensaModelo::ListarActivasModelo();
    $slidesCarrusel = CarruselPanelModelo::ListarActivosModelo();

    // Imagenes de respaldo (rotan) para noticias sin imagen propia asignada
    $imagenesRespaldoNoticia = [
        'vistas/recursos/assets/media/co/c1.png',
        'vistas/recursos/assets/media/co/c2.png',
        'vistas/recursos/assets/media/co/c3.png',
    ];
    $coloresBadgeEtiqueta = [
        'NUEVO' => 'var(--primary-gradient)',
        'IMPORTANTE' => 'linear-gradient(135deg, #52616e 0%, #37424a 100%)',
        'EVENTO' => 'linear-gradient(135deg, #0f6674 0%, #159895 100%)',
    ];
?>
<link href="vistas/recursos/assets/css/panel-informativo.css?v=<?php echo filemtime('vistas/recursos/assets/css/panel-informativo.css'); ?>" rel="stylesheet" type="text/css" />

<style>
/* ========================================
   PORTAL PÚBLICO - ENCABEZADO Y PORTADA
======================================== */
body.portal-publico {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
    margin: 0;
}

.portal-header {
    position: sticky;
    top: 0;
    z-index: 100;
    background: var(--primary-gradient);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.portal-header .kt-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 12px;
    padding-bottom: 12px;
    gap: 15px;
}

.portal-marca {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #fff;
    text-decoration: none;
}

.portal-marca:hover {
    color: #fff;
    text-decoration: none;
}

/* Fondo blanco para que el texto oscuro del logo se lea sobre el azul */
.portal-marca {
    background: #fff;
    border-radius: 12px;
    padding: 6px 14px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    transition: transform 0.3s ease;
}

.portal-marca:hover {
    transform: scale(1.03);
}

.portal-marca img {
    height: 52px;
    width: auto;
    display: block;
}

.btn-portal-login {
    background: #fff;
    color: #1e3c72;
    border-radius: 25px;
    padding: 10px 24px;
    font-weight: 600;
    font-size: 14px;
    white-space: nowrap;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}

.btn-portal-login:hover {
    color: #1e3c72;
    text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
}

.portal-hero {
    text-align: center;
    padding: 40px 0 10px;
}

.portal-hero h1 {
    font-size: 34px;
    font-weight: 700;
    color: #1e3c72;
    margin-bottom: 8px;
}

.portal-hero p {
    color: var(--text-light);
    font-size: 16px;
    margin: 0;
}

.portal-footer {
    background: #1e3c72;
    color: rgba(255, 255, 255, 0.85);
    text-align: center;
    padding: 20px 15px;
    font-size: 13px;
    margin-top: 40px;
}

@media (max-width: 576px) {
    .portal-marca { padding: 4px 10px; }
    .portal-marca img { height: 38px; }
    .btn-portal-login { padding: 8px 16px; font-size: 13px; }
    .portal-hero h1 { font-size: 24px; }
}
</style>

<body class="portal-publico">

  <!-- ENCABEZADO PÚBLICO -->
  <header class="portal-header">
    <div class="kt-container">
      <a href="inicio" class="portal-marca">
        <img alt="Posgrado Odontología - Universidad Técnica de Oruro" src="vistas/recursos/assets/media/logos/logo.png" />
      </a>
      <?php if ($haySesion): ?>
        <a href="panel" class="btn-portal-login"><i class="fa fa-th-large"></i> Ir al Panel</a>
      <?php else: ?>
        <a href="ingreso" class="btn-portal-login"><i class="fa fa-sign-in-alt"></i> Iniciar Sesión</a>
      <?php endif; ?>
    </div>
  </header>

  <!-- CONTENIDO PRINCIPAL -->
  <div class="kt-container">

    <div class="portal-hero">
      <h1>Bienvenido al Posgrado de la Facultad de Ciencias de la Salud</h1>
      <p>Conoce nuestros programas, noticias y el cronograma de defensas</p>
    </div>

    <!-- CARRUSEL 3D DE PROGRAMAS -->
    <?php if (empty($slidesCarrusel)): ?>
      <p class="text-center text-muted" style="margin: 40px 0;">No hay programas destacados en el carrusel por el momento.</p>
    <?php else: ?>
    <div class="carousel-3d-container">
      <div class="carousel-3d" id="carousel3d">
        <?php foreach ($slidesCarrusel as $i => $s): ?>
        <?php
            $imagenSlide = !empty($s['ImagenURL']) ? $s['ImagenURL'] : $imagenesRespaldoNoticia[$i % count($imagenesRespaldoNoticia)];
            $tituloSlide = !empty($s['TituloPersonalizado']) ? $s['TituloPersonalizado'] : $s['NombrePrograma'];
            $descSlide = !empty($s['DescripcionPersonalizada']) ? $s['DescripcionPersonalizada'] : ($s['Detalle'] ?: 'Programa de posgrado de la Facultad de Ciencias de la Salud.');
            $fechaInicioSlide = !empty($s['FechaInicio']) ? date('d/m/Y', strtotime($s['FechaInicio'])) : 'A definir';
        ?>
        <div class="carousel-3d-slide<?php echo $i === 0 ? ' active' : ''; ?>" data-index="<?php echo $i; ?>">
          <img src="<?php echo htmlspecialchars($imagenSlide); ?>" alt="<?php echo htmlspecialchars($tituloSlide); ?>" onerror="this.src='vistas/recursos/assets/media/co/c1.png'">
          <div class="slide-content">
            <h3><?php echo htmlspecialchars($tituloSlide); ?></h3>
            <p><?php echo htmlspecialchars($descSlide); ?></p>
            <button class="btn-info-programa"
              data-programa-id="<?php echo (int) $s['ProgramaID']; ?>"
              data-programa="<?php echo htmlspecialchars($s['NombrePrograma'], ENT_QUOTES); ?>"
              data-grado="<?php echo htmlspecialchars($s['GradoAcademico'], ENT_QUOTES); ?>"
              data-codigo="<?php echo htmlspecialchars($s['Codigo'], ENT_QUOTES); ?>"
              data-costo="<?php echo htmlspecialchars($s['Costo'], ENT_QUOTES); ?>"
              data-matricula="<?php echo htmlspecialchars($s['CostoMatricula'], ENT_QUOTES); ?>"
              data-modulos="<?php echo htmlspecialchars($s['Modulos'], ENT_QUOTES); ?>"
              data-duracion="<?php echo htmlspecialchars($s['DuracionMeses'] ?? '', ENT_QUOTES); ?>"
              data-fecha="<?php echo htmlspecialchars($fechaInicioSlide, ENT_QUOTES); ?>"
              data-sede="<?php echo htmlspecialchars($s['Sede'], ENT_QUOTES); ?>"
              data-detalle="<?php echo htmlspecialchars($s['Detalle'] ?? '', ENT_QUOTES); ?>">
              Más Información
            </button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Controles -->
      <div class="carousel-3d-controls">
        <button class="carousel-3d-btn" id="prevBtn"><i class="fas fa-chevron-left"></i></button>
        <button class="carousel-3d-btn" id="nextBtn"><i class="fas fa-chevron-right"></i></button>
      </div>

      <!-- Indicadores -->
      <div class="carousel-3d-indicators">
        <?php foreach ($slidesCarrusel as $i => $s): ?>
        <button class="carousel-3d-indicator<?php echo $i === 0 ? ' active' : ''; ?>" data-index="<?php echo $i; ?>"></button>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- SECCIÓN DE NOTICIAS E INFORMACIONES -->
    <div class="section-title animate-fade-in animate-delay-2">
      <h2>Noticias e Informaciones</h2>
      <p>Mantente informado sobre las últimas novedades del posgrado</p>
    </div>

    <div class="news-grid">
      <?php if (empty($noticiasPanel)): ?>
        <p class="text-center text-muted" style="grid-column: 1/-1;">No hay noticias publicadas por el momento.</p>
      <?php endif; ?>
      <?php foreach ($noticiasPanel as $i => $n): ?>
      <?php
          $imagenNoticia = !empty($n['ImagenURL']) ? $n['ImagenURL'] : $imagenesRespaldoNoticia[$i % count($imagenesRespaldoNoticia)];
          $fondoBadge = $coloresBadgeEtiqueta[$n['Etiqueta']] ?? 'var(--primary-gradient)';
          $fechaNoticia = !empty($n['FechaPublicacion']) ? date('d/m/Y', strtotime($n['FechaPublicacion'])) : '';
      ?>
      <div class="news-card animate-fade-in animate-delay-<?php echo min($i + 2, 4); ?>">
        <div class="news-card-image">
          <img src="<?php echo htmlspecialchars($imagenNoticia); ?>" alt="<?php echo htmlspecialchars($n['Titulo']); ?>" onerror="this.src='vistas/recursos/assets/media/co/c1.png'">
          <span class="news-card-badge" style="background: <?php echo $fondoBadge; ?>;"><?php echo htmlspecialchars($n['Etiqueta']); ?></span>
        </div>
        <div class="news-card-content">
          <div class="news-card-meta">
            <span><i class="far fa-calendar"></i> <?php echo htmlspecialchars($fechaNoticia); ?></span>
            <span><i class="far fa-user"></i> <?php echo htmlspecialchars($n['Autor']); ?></span>
          </div>
          <h3><?php echo htmlspecialchars($n['Titulo']); ?></h3>
          <p><?php echo nl2br(htmlspecialchars($n['Descripcion'])); ?></p>
          <?php if (!empty($n['Enlace'])): ?>
          <div class="news-card-footer">
            <a href="<?php echo htmlspecialchars($n['Enlace']); ?>" class="btn-read-more" target="_blank" rel="noopener">
              Leer más <i class="fas fa-arrow-right"></i>
            </a>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- SECCIÓN DE CRONOGRAMA DE DEFENSA -->
    <div class="section-title animate-fade-in">
      <h2>Cronograma de Defensa</h2>
      <p>Fechas, horarios y lugares de defensa de tesis / monografía</p>
    </div>

    <div class="defensa-tabla-wrapper animate-fade-in">
      <table class="defensa-tabla">
        <thead>
          <tr>
            <th>Estudiante</th>
            <th>Programa</th>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Lugar</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($defensasPanel)): ?>
          <tr>
            <td colspan="5" class="text-center text-muted" style="padding: 30px;">
              No hay defensas programadas por el momento.
            </td>
          </tr>
          <?php endif; ?>
          <?php foreach ($defensasPanel as $d): ?>
          <?php
              $fechaDefensa = date('d/m/Y', strtotime($d['FechaDefensa']));
              $horaDefensa = !empty($d['HoraDefensa']) ? date('H:i', strtotime($d['HoraDefensa'])) : '-';
              $programaTexto = !empty($d['NombrePrograma']) ? $d['NombrePrograma'] . ' (' . $d['GradoAcademico'] . ')' : '-';
          ?>
          <tr>
            <td>
              <strong><?php echo htmlspecialchars($d['NombreEstudiante']); ?></strong>
              <?php if (!empty($d['Detalle'])): ?><br><small class="text-muted"><?php echo htmlspecialchars($d['Detalle']); ?></small><?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($programaTexto); ?></td>
            <td><?php echo htmlspecialchars($fechaDefensa); ?></td>
            <td><?php echo htmlspecialchars($horaDefensa); ?></td>
            <td><?php echo htmlspecialchars($d['Lugar']); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- SECCIÓN DE REDES SOCIALES -->
    <div class="social-section animate-fade-in">
      <div class="section-title" style="margin: 0 0 20px;">
        <h2>Síguenos en Redes Sociales</h2>
        <p>Mantente conectado y no te pierdas ninguna actualización</p>
      </div>

      <div class="social-grid">
        <div class="social-card facebook">
          <div class="social-icon"><i class="fab fa-facebook-f"></i></div>
          <h4>Facebook</h4>
          <p>Síguenos para estar al día con nuestras publicaciones</p>
          <button class="btn-social" onclick="window.open('https://facebook.com', '_blank')">Seguir</button>
        </div>
        <div class="social-card instagram">
          <div class="social-icon"><i class="fab fa-instagram"></i></div>
          <h4>Instagram</h4>
          <p>Descubre momentos únicos de nuestro posgrado</p>
          <button class="btn-social" onclick="window.open('https://instagram.com', '_blank')">Seguir</button>
        </div>
        <div class="social-card youtube">
          <div class="social-icon"><i class="fab fa-youtube"></i></div>
          <h4>YouTube</h4>
          <p>Videos educativos y transmisiones en vivo</p>
          <button class="btn-social" onclick="window.open('https://youtube.com', '_blank')">Suscribirse</button>
        </div>
        <div class="social-card twitter">
          <div class="social-icon"><i class="fab fa-twitter"></i></div>
          <h4>Twitter</h4>
          <p>Noticias y actualizaciones al instante</p>
          <button class="btn-social" onclick="window.open('https://twitter.com', '_blank')">Seguir</button>
        </div>
      </div>
    </div>

  </div>
  <!-- FIN CONTENIDO PRINCIPAL -->

  <footer class="portal-footer">
    &copy; <?php echo date('Y'); ?> Universidad Técnica de Oruro · Posgrado Facultad de Ciencias de la Salud
  </footer>

  <!-- Modal: Más Información del Programa (solo lectura) -->
  <div class="modal fade" id="modalInfoPrograma" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff;">
          <h5 class="modal-title text-white"><i class="fa fa-graduation-cap"></i> <span id="infoProgramaTitulo">Programa</span></h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <span class="badge badge-info mb-3" id="infoProgramaGrado" style="font-size: 12px;"></span>
          <div class="row">
            <div class="col-6 mb-3">
              <small class="text-muted d-block">COSTO TOTAL</small>
              <strong id="infoProgramaCosto" style="font-size: 18px; color: #1e3c72;"></strong>
            </div>
            <div class="col-6 mb-3">
              <small class="text-muted d-block">MATRÍCULA</small>
              <strong id="infoProgramaMatricula" style="font-size: 18px; color: #1e3c72;"></strong>
            </div>
            <div class="col-6 mb-3">
              <small class="text-muted d-block">MÓDULOS</small>
              <strong id="infoProgramaModulos"></strong>
            </div>
            <div class="col-6 mb-3">
              <small class="text-muted d-block">DURACIÓN</small>
              <strong id="infoProgramaDuracion"></strong>
            </div>
            <div class="col-6 mb-3">
              <small class="text-muted d-block">FECHA DE INICIO</small>
              <strong id="infoProgramaFecha"></strong>
            </div>
            <div class="col-6 mb-3">
              <small class="text-muted d-block">SEDE</small>
              <strong id="infoProgramaSede"></strong>
            </div>
          </div>
          <div id="infoProgramaDetalleWrap">
            <small class="text-muted d-block">DETALLE</small>
            <p id="infoProgramaDetalle" class="mb-0"></p>
          </div>
        </div>
        <div class="modal-footer">
          <?php if ($haySesion): ?>
            <!-- panel-info-programa.js completa el href con el programa elegido -->
            <a href="autoinscripcion" id="linkInscribirmePrograma" class="btn mr-auto" style="background: var(--primary-gradient); color: #fff;">
              <i class="fa fa-file-signature"></i> Inscribirme
            </a>
          <?php else: ?>
            <!-- La solicitud de inscripción requiere sesión: se envía al login -->
            <a href="ingreso" class="btn mr-auto" style="background: var(--primary-gradient); color: #fff;">
              <i class="fa fa-sign-in-alt"></i> Inicia sesión para inscribirte
            </a>
          <?php endif; ?>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
</body>

<script src="vistas/recursos/assets/js/scripts/panel-carrusel.js?v=<?php echo filemtime('vistas/recursos/assets/js/scripts/panel-carrusel.js'); ?>"></script>
<script src="vistas/recursos/assets/js/scripts/panel-info-programa.js?v=<?php echo filemtime('vistas/recursos/assets/js/scripts/panel-info-programa.js'); ?>"></script>
