<?php
    /**
     * Portal público (página de inicio del sistema).
     * Muestra el contenido informativo del Panel (carrusel de programas,
     * noticias, cronograma de defensa y redes sociales) en SOLO LECTURA y sin
     * requerir sesión. La administración de ese contenido sigue en "panel",
     * que exige inicio de sesión.
     *
     * Estilos propios con prefijo pi- (no usa panel-informativo.css ni
     * panel-carrusel.js, que siguen siendo del panel interno).
     */
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    date_default_timezone_set("America/La_Paz");

    $haySesion = !empty($_SESSION['Validar']);

    $noticiasPanel = NoticiaPanelModelo::ListarActivasModelo();
    $defensasPanel = CronogramaDefensaModelo::ListarActivasModelo();
    $slidesCarrusel = CarruselPanelModelo::ListarActivosModelo();

    // Imagenes de respaldo (rotan) para noticias/programas sin imagen propia asignada
    $imagenesRespaldoNoticia = [
        'vistas/recursos/assets/media/co/c1.png',
        'vistas/recursos/assets/media/co/c2.png',
        'vistas/recursos/assets/media/co/c3.png',
    ];
    // Clase de color de cada etiqueta de noticia
    $clasesEtiqueta = [
        'NUEVO' => 'is-nuevo',
        'IMPORTANTE' => 'is-importante',
        'EVENTO' => 'is-evento',
    ];
?>
<style>
/* ========================================
   PORTAL PÚBLICO (prefijo pi-)
======================================== */
body.portal-publico {
    --pi-primario: #1e3c72;
    --pi-primario-2: #2a5298;
    --pi-primario-suave: #eef2f9;
    --pi-acento: #0f766e;
    --pi-texto: #1f2937;
    --pi-texto-2: #6b7280;
    --pi-borde: #e5e7eb;
    --pi-fondo: #f6f7fb;
    --primary-gradient: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    background: var(--pi-fondo);
    color: var(--pi-texto);
    margin: 0;
    min-height: 100vh;
}
.pi-contenedor { max-width: 1180px; margin: 0 auto; padding: 0 24px; }

/* Encabezado */
.pi-header {
    position: sticky;
    top: 0;
    z-index: 100;
    background: rgba(255, 255, 255, .96);
    backdrop-filter: saturate(180%) blur(8px);
    border-bottom: 1px solid var(--pi-borde);
}
.pi-header .pi-contenedor {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    height: 72px;
}
.pi-marca img { height: 48px; width: auto; display: block; }
.pi-nav { display: flex; align-items: center; gap: 28px; }
.pi-nav a:not(.pi-btn) {
    color: var(--pi-texto-2);
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: color .2s;
}
.pi-nav a:not(.pi-btn):hover { color: var(--pi-primario); }
.pi-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: 8px;
    padding: 10px 20px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid transparent;
    cursor: pointer;
    transition: background .2s, color .2s, border-color .2s, box-shadow .2s;
    white-space: nowrap;
}
.pi-btn-primario { background: var(--pi-primario); color: #fff; }
.pi-btn-primario:hover { background: #16305c; color: #fff; text-decoration: none; box-shadow: 0 6px 16px rgba(30, 60, 114, .25); }
.pi-btn-claro { background: #fff; color: var(--pi-primario); }
.pi-btn-claro:hover { background: var(--pi-primario-suave); color: var(--pi-primario); text-decoration: none; }
.pi-btn-contorno { border-color: rgba(255, 255, 255, .45); color: #fff; background: transparent; }
.pi-btn-contorno:hover { background: rgba(255, 255, 255, .12); color: #fff; text-decoration: none; }

/* Portada */
.pi-hero {
    background:
        radial-gradient(circle at 85% 20%, rgba(255, 255, 255, .08) 0, transparent 40%),
        linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    color: #fff;
    padding: 56px 0 64px;
}
.pi-hero .pi-contenedor {
    display: grid;
    grid-template-columns: minmax(0, 5fr) minmax(0, 6fr);
    gap: 48px;
    align-items: center;
}
.pi-eyebrow {
    display: inline-block;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, .75);
    margin-bottom: 14px;
}
.pi-hero h1 {
    font-size: 38px;
    line-height: 1.2;
    font-weight: 700;
    color: #fff;
    margin: 0 0 16px;
}
.pi-hero-texto { font-size: 16px; line-height: 1.6; color: rgba(255, 255, 255, .82); margin: 0 0 28px; max-width: 460px; }
.pi-hero-acciones { display: flex; gap: 12px; flex-wrap: wrap; }
.pi-hero-datos { display: flex; gap: 32px; margin-top: 36px; padding-top: 24px; border-top: 1px solid rgba(255, 255, 255, .15); }
.pi-hero-datos strong { display: block; font-size: 24px; font-weight: 700; color: #fff; }
.pi-hero-datos span { font-size: 13px; color: rgba(255, 255, 255, .7); }

/* Carrusel de programas (compacto, transición por desvanecido) */
.pi-slider {
    position: relative;
    aspect-ratio: 16 / 10;
    max-height: 380px;
    width: 100%;
    border-radius: 16px;
    overflow: hidden;
    background: #16305c;
    box-shadow: 0 24px 48px rgba(0, 0, 0, .25);
}
.pi-slide {
    position: absolute;
    inset: 0;
    opacity: 0;
    visibility: hidden;
    transition: opacity .7s ease, visibility .7s;
}
.pi-slide.is-activa { opacity: 1; visibility: visible; }
.pi-slide img { width: 100%; height: 100%; object-fit: cover; display: block; }
.pi-slide-info {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    padding: 56px 24px 22px;
    background: linear-gradient(to top, rgba(10, 20, 45, .92) 0%, rgba(10, 20, 45, .55) 55%, transparent 100%);
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 16px;
}
.pi-slide-grado {
    display: inline-block;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .06em;
    text-transform: uppercase;
    background: rgba(255, 255, 255, .18);
    border-radius: 999px;
    padding: 3px 10px;
    margin-bottom: 8px;
}
.pi-slide-info h3 { font-size: 20px; font-weight: 700; color: #fff; margin: 0 0 4px; line-height: 1.3; }
.pi-slide-info p {
    font-size: 13px;
    color: rgba(255, 255, 255, .8);
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.pi-slide-info .pi-btn { padding: 8px 14px; font-size: 13px; flex-shrink: 0; }
.pi-slider-flecha {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: 0;
    background: rgba(255, 255, 255, .9);
    color: var(--pi-primario);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    opacity: 0;
    transition: opacity .2s, background .2s;
    z-index: 2;
}
.pi-slider:hover .pi-slider-flecha,
.pi-slider-flecha:focus-visible { opacity: 1; }
.pi-slider-flecha:hover { background: #fff; }
.pi-slider-flecha.is-prev { left: 14px; }
.pi-slider-flecha.is-next { right: 14px; }
.pi-slider-puntos {
    position: absolute;
    top: 16px;
    right: 16px;
    display: flex;
    gap: 6px;
    z-index: 2;
}
.pi-slider-punto {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    border: 0;
    padding: 0;
    background: rgba(255, 255, 255, .5);
    cursor: pointer;
    transition: width .3s, background .3s;
}
.pi-slider-punto.is-activa { width: 22px; background: #fff; }
.pi-slider-vacio {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: rgba(255, 255, 255, .75);
    font-size: 14px;
    gap: 10px;
}
.pi-slider-vacio i { font-size: 32px; }

/* Secciones */
.pi-seccion { padding: 64px 0 8px; scroll-margin-top: 72px; }
.pi-seccion-cabecera { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; margin-bottom: 24px; }
.pi-seccion-eyebrow { font-size: 12px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: var(--pi-acento); }
.pi-seccion h2 { font-size: 26px; font-weight: 700; color: var(--pi-texto); margin: 4px 0 0; }
.pi-seccion-sub { font-size: 14px; color: var(--pi-texto-2); margin: 6px 0 0; }

/* Noticias */
.pi-noticias { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 24px; }
.pi-noticia {
    background: #fff;
    border: 1px solid var(--pi-borde);
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: box-shadow .25s, transform .25s;
}
.pi-noticia:hover { box-shadow: 0 12px 28px rgba(30, 60, 114, .12); transform: translateY(-3px); }
.pi-noticia-imagen { position: relative; aspect-ratio: 16 / 9; background: var(--pi-primario-suave); overflow: hidden; }
.pi-noticia-imagen img { width: 100%; height: 100%; object-fit: cover; display: block; }
.pi-etiqueta {
    position: absolute;
    top: 12px;
    left: 12px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .04em;
    padding: 4px 10px;
    border-radius: 6px;
    color: #fff;
    background: var(--pi-primario);
}
.pi-etiqueta.is-importante { background: #b45309; }
.pi-etiqueta.is-evento { background: var(--pi-acento); }
.pi-noticia-cuerpo { padding: 18px 20px 20px; display: flex; flex-direction: column; flex: 1; }
.pi-noticia-meta { display: flex; gap: 14px; font-size: 12px; color: var(--pi-texto-2); margin-bottom: 10px; }
.pi-noticia-meta i { margin-right: 4px; }
.pi-noticia h3 { font-size: 16px; font-weight: 700; line-height: 1.4; color: var(--pi-texto); margin: 0 0 8px; }
.pi-noticia p {
    font-size: 14px;
    line-height: 1.6;
    color: var(--pi-texto-2);
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.pi-leer-mas { margin-top: auto; padding-top: 14px; font-size: 14px; font-weight: 600; color: var(--pi-primario); text-decoration: none; }
.pi-leer-mas:hover { text-decoration: underline; color: var(--pi-primario); }

/* Defensas */
.pi-tabla-wrap { background: #fff; border: 1px solid var(--pi-borde); border-radius: 12px; overflow: hidden; }
.pi-tabla { width: 100%; border-collapse: collapse; font-size: 14px; }
.pi-tabla th {
    background: var(--pi-fondo);
    color: var(--pi-texto-2);
    font-size: 12px;
    font-weight: 600;
    letter-spacing: .04em;
    text-transform: uppercase;
    text-align: left;
    padding: 12px 20px;
    border-bottom: 1px solid var(--pi-borde);
}
.pi-tabla td { padding: 14px 20px; border-bottom: 1px solid var(--pi-borde); vertical-align: middle; }
.pi-tabla tbody tr:last-child td { border-bottom: 0; }
.pi-tabla tbody tr:hover td { background: #fafbfd; }
.pi-fecha-defensa { display: inline-flex; flex-direction: column; align-items: center; min-width: 54px; padding: 6px 8px; border-radius: 8px; background: var(--pi-primario-suave); color: var(--pi-primario); line-height: 1.1; }
.pi-fecha-defensa strong { font-size: 18px; }
.pi-fecha-defensa small { font-size: 11px; text-transform: uppercase; }
.pi-vacio { text-align: center; color: var(--pi-texto-2); padding: 40px 20px !important; }
.pi-vacio i { display: block; font-size: 28px; color: #c7cdd8; margin-bottom: 10px; }

/* Pie de página */
.pi-footer { background: #14264a; color: rgba(255, 255, 255, .75); margin-top: 72px; font-size: 14px; }
.pi-footer-grid { display: grid; grid-template-columns: 2fr 2fr 1fr; gap: 40px; padding: 48px 0 36px; }
.pi-footer h4 { font-size: 13px; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; color: #fff; margin: 0 0 14px; }
.pi-footer p { margin: 0 0 8px; line-height: 1.6; }
.pi-footer i { width: 18px; color: rgba(255, 255, 255, .5); }
.pi-footer-marca { background: #fff; display: inline-block; border-radius: 10px; padding: 6px 12px; margin-bottom: 14px; }
.pi-footer-marca img { height: 40px; display: block; }
.pi-redes { display: flex; gap: 10px; }
.pi-redes a {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, .1);
    color: #fff;
    text-decoration: none;
    transition: background .2s;
}
.pi-redes a i { width: auto; color: #fff; }
.pi-redes a:hover { background: rgba(255, 255, 255, .22); }
.pi-footer-base { border-top: 1px solid rgba(255, 255, 255, .1); padding: 18px 0; font-size: 13px; color: rgba(255, 255, 255, .55); }

/* Modal "Más información" */
#modalInfoPrograma .modal-content { border: 0; border-radius: 12px; overflow: hidden; }
#modalInfoPrograma .modal-header { background: var(--pi-primario); border: 0; }
#modalInfoPrograma .modal-content .modal-header .modal-title { color: #fff; }

@media (max-width: 991.98px) {
    .pi-hero .pi-contenedor { grid-template-columns: 1fr; gap: 32px; }
    .pi-hero h1 { font-size: 30px; }
    .pi-noticias { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .pi-footer-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 767.98px) {
    .pi-contenedor { padding: 0 16px; }
    .pi-nav a:not(.pi-btn) { display: none; }
    .pi-marca img { height: 40px; }
    .pi-hero { padding: 36px 0 44px; }
    .pi-hero h1 { font-size: 26px; }
    .pi-hero-datos { gap: 20px; }
    .pi-slide-info { flex-direction: column; align-items: flex-start; padding: 48px 16px 16px; }
    .pi-slider-flecha { opacity: 1; width: 32px; height: 32px; top: 32%; }
    .pi-noticias { grid-template-columns: 1fr; }
    .pi-seccion { padding-top: 44px; }
    .pi-tabla thead { display: none; }
    .pi-tabla, .pi-tabla tbody, .pi-tabla tr, .pi-tabla td { display: block; width: 100%; }
    .pi-tabla tr { border-bottom: 1px solid var(--pi-borde); padding: 8px 0; }
    .pi-tabla td { border: 0; padding: 4px 16px; }
    .pi-footer-grid { grid-template-columns: 1fr; gap: 28px; }
}
@media (prefers-reduced-motion: reduce) {
    .pi-slide, .pi-noticia { transition: none; }
}
</style>

<body class="portal-publico">

  <!-- ENCABEZADO -->
  <header class="pi-header">
    <div class="pi-contenedor">
      <a href="inicio" class="pi-marca">
        <img alt="<?php echo htmlspecialchars(InstitucionModelo::NombreSistema() . " - " . InstitucionModelo::Universidad()); ?>" src="<?php echo InstitucionModelo::LogoUrl('LOGO_SISTEMA'); ?>" />
      </a>
      <nav class="pi-nav">
        <a href="#programas">Programas</a>
        <a href="#noticias">Noticias</a>
        <a href="#defensas">Defensas</a>
        <a href="#contacto">Contacto</a>
        <?php if ($haySesion): ?>
          <a href="panel" class="pi-btn pi-btn-primario"><i class="fa fa-th-large"></i> Ir al Panel</a>
        <?php else: ?>
          <a href="ingreso" class="pi-btn pi-btn-primario"><i class="fa fa-sign-in-alt"></i> Iniciar sesión</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <!-- PORTADA + CARRUSEL DE PROGRAMAS -->
  <section class="pi-hero" id="programas">
    <div class="pi-contenedor">
      <div>
        <span class="pi-eyebrow"><?php echo htmlspecialchars(InstitucionModelo::Formal(InstitucionModelo::Universidad())); ?> · <?php echo htmlspecialchars(InstitucionModelo::Formal(InstitucionModelo::Facultad())); ?></span>
        <h1>Posgrado en <?php echo htmlspecialchars(InstitucionModelo::Area()); ?></h1>
        <p class="pi-hero-texto">
          Especialidades, maestrías y diplomados para profesionales de la salud.
          Conoce nuestros programas, las últimas noticias y el cronograma de defensas.
        </p>
        <div class="pi-hero-acciones">
          <a href="#noticias" class="pi-btn pi-btn-claro">Ver novedades</a>
          <?php if ($haySesion): ?>
            <a href="panel" class="pi-btn pi-btn-contorno">Ir al Panel</a>
          <?php else: ?>
            <a href="ingreso" class="pi-btn pi-btn-contorno">Iniciar sesión</a>
          <?php endif; ?>
        </div>
        <div class="pi-hero-datos">
          <div><strong><?php echo count($slidesCarrusel); ?></strong><span>Programas destacados</span></div>
          <div><strong><?php echo count($noticiasPanel); ?></strong><span>Noticias publicadas</span></div>
          <div><strong><?php echo count($defensasPanel); ?></strong><span>Defensas programadas</span></div>
        </div>
      </div>

      <div class="pi-slider" id="piSlider" aria-roledescription="carrusel" aria-label="Programas destacados">
        <?php if (empty($slidesCarrusel)): ?>
          <div class="pi-slider-vacio">
            <i class="fa fa-graduation-cap"></i>
            No hay programas destacados por el momento.
          </div>
        <?php else: ?>
          <?php foreach ($slidesCarrusel as $i => $s): ?>
          <?php
              $imagenSlide = !empty($s['ImagenURL']) ? $s['ImagenURL'] : $imagenesRespaldoNoticia[$i % count($imagenesRespaldoNoticia)];
              $tituloSlide = !empty($s['TituloPersonalizado']) ? $s['TituloPersonalizado'] : $s['NombrePrograma'];
              $descSlide = !empty($s['DescripcionPersonalizada']) ? $s['DescripcionPersonalizada'] : ($s['Detalle'] ?: 'Programa de posgrado de la ' . InstitucionModelo::Formal(InstitucionModelo::Facultad()) . '.');
              $fechaInicioSlide = !empty($s['FechaInicio']) ? date('d/m/Y', strtotime($s['FechaInicio'])) : 'A definir';
          ?>
          <div class="pi-slide<?php echo $i === 0 ? ' is-activa' : ''; ?>" role="group" aria-label="<?php echo ($i + 1) . ' de ' . count($slidesCarrusel); ?>">
            <img src="<?php echo htmlspecialchars($imagenSlide); ?>" alt="<?php echo htmlspecialchars($tituloSlide); ?>" onerror="this.src='vistas/recursos/assets/media/co/c1.png'">
            <div class="pi-slide-info">
              <div>
                <?php if (!empty($s['GradoAcademico'])): ?>
                  <span class="pi-slide-grado"><?php echo htmlspecialchars($s['GradoAcademico']); ?></span>
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($tituloSlide); ?></h3>
                <p><?php echo htmlspecialchars($descSlide); ?> · Inicio: <?php echo htmlspecialchars($fechaInicioSlide); ?></p>
              </div>
              <button type="button" class="pi-btn pi-btn-claro btn-info-programa"
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
                Más información
              </button>
            </div>
          </div>
          <?php endforeach; ?>

          <?php if (count($slidesCarrusel) > 1): ?>
            <button type="button" class="pi-slider-flecha is-prev" aria-label="Anterior"><i class="fas fa-chevron-left"></i></button>
            <button type="button" class="pi-slider-flecha is-next" aria-label="Siguiente"><i class="fas fa-chevron-right"></i></button>
            <div class="pi-slider-puntos">
              <?php foreach ($slidesCarrusel as $i => $s): ?>
                <button type="button" class="pi-slider-punto<?php echo $i === 0 ? ' is-activa' : ''; ?>" data-index="<?php echo $i; ?>" aria-label="Ir al programa <?php echo $i + 1; ?>"></button>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <main class="pi-contenedor">

    <!-- NOTICIAS -->
    <section class="pi-seccion" id="noticias">
      <div class="pi-seccion-cabecera">
        <div>
          <span class="pi-seccion-eyebrow">Novedades</span>
          <h2>Noticias e informaciones</h2>
          <p class="pi-seccion-sub">Las últimas novedades del posgrado</p>
        </div>
      </div>

      <?php if (empty($noticiasPanel)): ?>
        <div class="pi-tabla-wrap pi-vacio"><i class="far fa-newspaper"></i>No hay noticias publicadas por el momento.</div>
      <?php else: ?>
      <div class="pi-noticias">
        <?php foreach ($noticiasPanel as $i => $n): ?>
        <?php
            $imagenNoticia = !empty($n['ImagenURL']) ? $n['ImagenURL'] : $imagenesRespaldoNoticia[$i % count($imagenesRespaldoNoticia)];
            $claseEtiqueta = $clasesEtiqueta[$n['Etiqueta']] ?? '';
            $fechaNoticia = !empty($n['FechaPublicacion']) ? date('d/m/Y', strtotime($n['FechaPublicacion'])) : '';
        ?>
        <article class="pi-noticia">
          <div class="pi-noticia-imagen">
            <img src="<?php echo htmlspecialchars($imagenNoticia); ?>" alt="<?php echo htmlspecialchars($n['Titulo']); ?>" loading="lazy" onerror="this.src='vistas/recursos/assets/media/co/c1.png'">
            <?php if (!empty($n['Etiqueta'])): ?>
              <span class="pi-etiqueta <?php echo $claseEtiqueta; ?>"><?php echo htmlspecialchars($n['Etiqueta']); ?></span>
            <?php endif; ?>
          </div>
          <div class="pi-noticia-cuerpo">
            <div class="pi-noticia-meta">
              <?php if ($fechaNoticia): ?><span><i class="far fa-calendar"></i><?php echo htmlspecialchars($fechaNoticia); ?></span><?php endif; ?>
              <?php if (!empty($n['Autor'])): ?><span><i class="far fa-user"></i><?php echo htmlspecialchars($n['Autor']); ?></span><?php endif; ?>
            </div>
            <h3><?php echo htmlspecialchars($n['Titulo']); ?></h3>
            <p><?php echo nl2br(htmlspecialchars($n['Descripcion'])); ?></p>
            <?php if (!empty($n['Enlace'])): ?>
              <a href="<?php echo htmlspecialchars($n['Enlace']); ?>" class="pi-leer-mas" target="_blank" rel="noopener">Leer más <i class="fas fa-arrow-right"></i></a>
            <?php endif; ?>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </section>

    <!-- CRONOGRAMA DE DEFENSA -->
    <section class="pi-seccion" id="defensas">
      <div class="pi-seccion-cabecera">
        <div>
          <span class="pi-seccion-eyebrow">Agenda</span>
          <h2>Cronograma de defensas</h2>
          <p class="pi-seccion-sub">Fechas, horarios y lugares de defensa de tesis y monografías</p>
        </div>
      </div>

      <div class="pi-tabla-wrap">
        <table class="pi-tabla">
          <thead>
            <tr>
              <th style="width:90px">Fecha</th>
              <th>Estudiante</th>
              <th>Programa</th>
              <th style="width:90px">Hora</th>
              <th>Lugar</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($defensasPanel)): ?>
            <tr>
              <td colspan="5" class="pi-vacio"><i class="far fa-calendar-alt"></i>No hay defensas programadas por el momento.</td>
            </tr>
            <?php endif; ?>
            <?php
                $mesesCortos = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
            ?>
            <?php foreach ($defensasPanel as $d): ?>
            <?php
                $tsDefensa = strtotime($d['FechaDefensa']);
                $horaDefensa = !empty($d['HoraDefensa']) ? date('H:i', strtotime($d['HoraDefensa'])) : '-';
                $programaTexto = !empty($d['NombrePrograma']) ? $d['NombrePrograma'] . ' (' . $d['GradoAcademico'] . ')' : '-';
            ?>
            <tr>
              <td>
                <span class="pi-fecha-defensa" title="<?php echo date('d/m/Y', $tsDefensa); ?>">
                  <strong><?php echo date('d', $tsDefensa); ?></strong>
                  <small><?php echo $mesesCortos[(int) date('n', $tsDefensa) - 1] . ' ' . date('Y', $tsDefensa); ?></small>
                </span>
              </td>
              <td>
                <strong><?php echo htmlspecialchars($d['NombreEstudiante']); ?></strong>
                <?php if (!empty($d['TituloDefensa'])): ?><br><small class="text-muted"><?php echo htmlspecialchars($d['TituloDefensa']); ?></small><?php endif; ?>
              </td>
              <td><?php echo htmlspecialchars($programaTexto); ?></td>
              <td><i class="far fa-clock text-muted"></i> <?php echo htmlspecialchars($horaDefensa); ?></td>
              <td><?php echo htmlspecialchars($d['Lugar']); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

  </main>

  <!-- PIE DE PÁGINA: contacto y redes sociales -->
  <footer class="pi-footer" id="contacto">
    <div class="pi-contenedor">
      <div class="pi-footer-grid">
        <div>
          <div class="pi-footer-marca"><img src="<?php echo InstitucionModelo::LogoUrl('LOGO_SISTEMA'); ?>" alt="<?php echo htmlspecialchars(InstitucionModelo::NombreSistema()); ?>"></div>
          <p><?php echo htmlspecialchars(InstitucionModelo::Formal(InstitucionModelo::Unidad())); ?><br><?php echo htmlspecialchars(InstitucionModelo::Formal(InstitucionModelo::Facultad())); ?> · <?php echo htmlspecialchars(InstitucionModelo::Formal(InstitucionModelo::Universidad())); ?></p>
        </div>
        <div>
          <h4>Contacto</h4>
          <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars(implode(', ', array_filter([InstitucionModelo::Direccion(), InstitucionModelo::Ciudad()], 'strlen'))); ?></p>
          <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars(InstitucionModelo::Telefonos()); ?></p>
        </div>
        <div>
          <h4>Síguenos</h4>
          <div class="pi-redes">
            <a href="https://facebook.com" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="https://instagram.com" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="https://youtube.com" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
            <a href="https://twitter.com" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
          </div>
        </div>
      </div>
    </div>
    <div class="pi-footer-base">
      <div class="pi-contenedor">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(InstitucionModelo::Formal(InstitucionModelo::Universidad())); ?> · Posgrado <?php echo htmlspecialchars(InstitucionModelo::Formal(InstitucionModelo::Facultad())); ?></div>
    </div>
  </footer>

  <!-- Modal: Más Información del Programa (solo lectura) -->
  <div class="modal fade" id="modalInfoPrograma" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-graduation-cap"></i> <span id="infoProgramaTitulo">Programa</span></h5>
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
            <a href="autoinscripcion" id="linkInscribirmePrograma" class="pi-btn pi-btn-primario mr-auto">
              <i class="fa fa-file-signature"></i> Inscribirme
            </a>
          <?php else: ?>
            <!-- La solicitud de inscripción requiere sesión: se envía al login -->
            <a href="ingreso" class="pi-btn pi-btn-primario mr-auto">
              <i class="fa fa-sign-in-alt"></i> Inicia sesión para inscribirte
            </a>
          <?php endif; ?>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
</body>

<script>
/* Carrusel de programas del portal: desvanecido automático cada 6 s, con pausa
   al pasar el mouse o al enfocar; flechas, puntos y deslizamiento táctil. */
(function () {
    var slider = document.getElementById('piSlider');
    if (!slider) return;
    var slides = slider.querySelectorAll('.pi-slide');
    var puntos = slider.querySelectorAll('.pi-slider-punto');
    if (slides.length < 2) return;

    var actual = 0;
    var timer = null;
    var reducirMovimiento = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function mostrar(indice) {
        actual = (indice + slides.length) % slides.length;
        slides.forEach(function (s, i) { s.classList.toggle('is-activa', i === actual); });
        puntos.forEach(function (p, i) { p.classList.toggle('is-activa', i === actual); });
    }
    function iniciar() {
        if (reducirMovimiento) return;
        detener();
        timer = setInterval(function () { mostrar(actual + 1); }, 6000);
    }
    function detener() { if (timer) { clearInterval(timer); timer = null; } }

    slider.querySelector('.is-prev').addEventListener('click', function () { mostrar(actual - 1); iniciar(); });
    slider.querySelector('.is-next').addEventListener('click', function () { mostrar(actual + 1); iniciar(); });
    puntos.forEach(function (p) {
        p.addEventListener('click', function () { mostrar(parseInt(p.getAttribute('data-index'), 10)); iniciar(); });
    });

    slider.addEventListener('mouseenter', detener);
    slider.addEventListener('mouseleave', iniciar);
    slider.addEventListener('focusin', detener);
    slider.addEventListener('focusout', iniciar);

    var inicioX = null;
    slider.addEventListener('touchstart', function (e) { inicioX = e.touches[0].clientX; }, { passive: true });
    slider.addEventListener('touchend', function (e) {
        if (inicioX === null) return;
        var dx = e.changedTouches[0].clientX - inicioX;
        if (Math.abs(dx) > 40) { mostrar(actual + (dx < 0 ? 1 : -1)); iniciar(); }
        inicioX = null;
    });

    iniciar();
})();
</script>
<script src="vistas/recursos/assets/js/scripts/panel-info-programa.js?v=<?php echo filemtime('vistas/recursos/assets/js/scripts/panel-info-programa.js'); ?>"></script>
