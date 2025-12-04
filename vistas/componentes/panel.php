<?php
    $Validar = new FuncionesControladores();
    $Validar -> ValidarSessionControlador();
    date_default_timezone_set("America/La_Paz");
?>

<style>
/* ========================================
   PANEL PRINCIPAL - ESTILOS MODERNOS
======================================== */

/* Variables de colores */
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --success-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    --text-dark: #2d3436;
    --text-light: #636e72;
    --bg-light: #f8f9fa;
}

/* Fondo del panel */
#kt_body {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
}

/* ========================================
   CARRUSEL 3D
======================================== */
.carousel-3d-container {
    perspective: 1500px;
    margin: 40px auto;
    max-width: 1200px;
    height: 500px;
    position: relative;
}

.carousel-3d {
    width: 100%;
    height: 100%;
    position: relative;
    transform-style: preserve-3d;
    transition: transform 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.carousel-3d-slide {
    position: absolute;
    width: 70%;
    height: 100%;
    left: 50%;
    transform-origin: center;
    transition: all 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.carousel-3d-slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.carousel-3d-slide .slide-content {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 40px;
    background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, transparent 100%);
    color: white;
    transform: translateY(20px);
    opacity: 0;
    transition: all 0.5s ease;
}

.carousel-3d-slide.active .slide-content {
    transform: translateY(0);
    opacity: 1;
}

.slide-content h3 {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 10px;
    text-shadow: 0 2px 10px rgba(0,0,0,0.5);
}

.slide-content p {
    font-size: 16px;
    margin-bottom: 20px;
    opacity: 0.9;
}

.slide-content .btn-info-programa {
    background: white;
    color: #667eea;
    padding: 12px 30px;
    border-radius: 25px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-block;
}

.slide-content .btn-info-programa:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(255,255,255,0.3);
}

/* Controles del carrusel 3D */
.carousel-3d-controls {
    position: absolute;
    bottom: -60px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 15px;
    z-index: 10;
}

.carousel-3d-btn {
    background: white;
    border: none;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    color: #667eea;
    font-size: 20px;
}

.carousel-3d-btn:hover {
    background: var(--primary-gradient);
    color: white;
    transform: scale(1.1);
}

.carousel-3d-indicators {
    position: absolute;
    bottom: -100px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 10px;
}

.carousel-3d-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(102, 126, 234, 0.3);
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
}

.carousel-3d-indicator.active {
    background: var(--primary-gradient);
    width: 30px;
    border-radius: 6px;
}

/* ========================================
   SECCIÓN DE NOTICIAS E INFORMACIONES
======================================== */
.section-title {
    text-align: center;
    margin: 80px 0 40px;
}

.section-title h2 {
    font-size: 36px;
    font-weight: 700;
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 10px;
}

.section-title p {
    color: var(--text-light);
    font-size: 16px;
}

.news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
    margin-bottom: 60px;
}

.news-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    cursor: pointer;
}

.news-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 60px rgba(102, 126, 234, 0.2);
}

.news-card-image {
    width: 100%;
    height: 220px;
    overflow: hidden;
    position: relative;
}

.news-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.news-card:hover .news-card-image img {
    transform: scale(1.1);
}

.news-card-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: var(--primary-gradient);
    color: white;
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.news-card-content {
    padding: 25px;
}

.news-card-meta {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
    font-size: 13px;
    color: var(--text-light);
}

.news-card-meta i {
    color: #667eea;
    margin-right: 5px;
}

.news-card h3 {
    font-size: 20px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 12px;
    line-height: 1.4;
}

.news-card p {
    color: var(--text-light);
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 20px;
}

.news-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 15px;
    border-top: 1px solid #f0f0f0;
}

.btn-read-more {
    color: #667eea;
    font-weight: 600;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: all 0.3s ease;
}

.btn-read-more:hover {
    gap: 10px;
}

/* ========================================
   SECCIÓN DE REDES SOCIALES
======================================== */
.social-section {
    background: white;
    border-radius: 30px;
    padding: 50px;
    margin: 60px 0;
    box-shadow: 0 20px 60px rgba(0,0,0,0.08);
}

.social-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-top: 40px;
}

.social-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
    border-radius: 20px;
    padding: 30px;
    text-align: center;
    transition: all 0.4s ease;
    border: 2px solid transparent;
    position: relative;
    overflow: hidden;
}

.social-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: var(--primary-gradient);
    opacity: 0;
    transition: opacity 0.4s ease;
    z-index: 0;
}

.social-card:hover::before {
    opacity: 1;
}

.social-card:hover {
    transform: translateY(-10px);
    border-color: #667eea;
}

.social-card > * {
    position: relative;
    z-index: 1;
}

.social-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 35px;
    color: white;
    transition: all 0.4s ease;
}

.social-card.facebook .social-icon { background: linear-gradient(135deg, #4267B2 0%, #2d4a8a 100%); }
.social-card.instagram .social-icon { background: linear-gradient(135deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); }
.social-card.youtube .social-icon { background: linear-gradient(135deg, #FF0000 0%, #cc0000 100%); }
.social-card.twitter .social-icon { background: linear-gradient(135deg, #1DA1F2 0%, #0d8bd9 100%); }

.social-card:hover .social-icon {
    background: white !important;
    color: #667eea;
    transform: scale(1.1) rotate(5deg);
}

.social-card h4 {
    font-size: 18px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 8px;
    transition: color 0.4s ease;
}

.social-card:hover h4 {
    color: white;
}

.social-card p {
    font-size: 13px;
    color: var(--text-light);
    margin-bottom: 20px;
    transition: color 0.4s ease;
}

.social-card:hover p {
    color: rgba(255,255,255,0.9);
}

.btn-social {
    background: var(--primary-gradient);
    color: white;
    padding: 10px 25px;
    border-radius: 25px;
    font-weight: 600;
    font-size: 13px;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-block;
}

.social-card:hover .btn-social {
    background: white;
    color: #667eea;
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

/* ========================================
   RESPONSIVE
======================================== */
@media (max-width: 992px) {
    .carousel-3d-container {
        height: 400px;
    }

    .news-grid {
        grid-template-columns: 1fr;
    }

    .social-section {
        padding: 30px 20px;
    }
}

@media (max-width: 768px) {
    .carousel-3d-container {
        height: 350px;
        margin: 20px auto;
    }

    .carousel-3d-slide {
        width: 90%;
    }

    .slide-content h3 {
        font-size: 24px;
    }

    .section-title h2 {
        font-size: 28px;
    }
}

/* Animaciones */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeInUp 0.8s ease forwards;
}

.animate-delay-1 { animation-delay: 0.1s; }
.animate-delay-2 { animation-delay: 0.2s; }
.animate-delay-3 { animation-delay: 0.3s; }
.animate-delay-4 { animation-delay: 0.4s; }
</style>

<body class="kt-page--loading-enabled kt-page--loading kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent kt-aside--enabled kt-aside--left kt-aside--fixed kt-page--loading">
  <div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed ">
    <div class="kt-header-mobile__logo">
      <a href="panel">
      <img alt="Logo" src="vistas/recursos/assets/media/logos/logo0.png" width="40" />
      </a>
    </div>
    <div class="kt-header-mobile__toolbar">
      <button class="kt-header-mobile__toolbar-toggler kt-header-mobile__toolbar-toggler--left" id="kt_aside_mobile_toggler"><span></span></button>
      <button class="kt-header-mobile__toolbar-toggler" id="kt_header_mobile_toggler"><span></span></button>
      <button class="kt-header-mobile__toolbar-topbar-toggler" id="kt_header_mobile_topbar_toggler"><i class="flaticon-more-1"></i></button>
    </div>
  </div>

  <div class="kt-grid kt-grid--hor kt-grid--root">
    <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver kt-page">
      <div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-wrapper" id="kt_wrapper">

		<?php
			$NavBar = new FuncionesControladores();
			$NavBar -> NavBarControlador();
		?>
		<button class="kt-aside-close " id="kt_aside_close_btn"><i class="la la-close"></i></button>
		<?php
			$Sidebar = new FuncionesControladores();
			$Sidebar -> SidebarControlador();
		?>

        <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
          <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <!-- begin:: Subheader -->
            <div class="kt-subheader   kt-grid__item" id="kt_subheader" style="background: transparent;">
              <div class="kt-container ">
                <div class="kt-subheader__main">
                  <h2 style="color: #2d3436;">PANEL PRINCIPAL</h2>
                  <span class="kt-subheader__separator kt-hidden"></span>
                  <div class="kt-subheader__breadcrumbs">
                    <a href="#" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></a>
                    <span class="kt-subheader__breadcrumbs-separator"></span>
                    <h4 style="color: #636e72;">SISTEMA DE REGISTRO Y CONTROL</h4>
                  </div>
                </div>
                <div class="kt-subheader__toolbar">
                  <div class="kt-subheader__wrapper">
                    <div id="lafecha" style="font-size:13pt; color: #2d3436; font-weight: 600;"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- CONTENIDO PRINCIPAL -->
            <div class="kt-container">

              <!-- CARRUSEL 3D DE PROGRAMAS -->
              <div class="carousel-3d-container">
                <div class="carousel-3d" id="carousel3d">
                  <!-- Slide 1 -->
                  <div class="carousel-3d-slide active" data-index="0">
                    <img src="vistas/recursos/assets/media/co/c1.png" alt="Programa 1">
                    <div class="slide-content">
                      <h3>Maestría en Ciencias de la Salud</h3>
                      <p>Forma parte de nuestro programa de excelencia académica con docentes altamente calificados.</p>
                      <button class="btn-info-programa">Más Información</button>
                    </div>
                  </div>

                  <!-- Slide 2 -->
                  <div class="carousel-3d-slide" data-index="1">
                    <img src="vistas/recursos/assets/media/co/c2.png" alt="Programa 2">
                    <div class="slide-content">
                      <h3>Diplomados Especializados</h3>
                      <p>Amplía tus conocimientos con nuestros diplomados en áreas estratégicas de la salud.</p>
                      <button class="btn-info-programa">Más Información</button>
                    </div>
                  </div>

                  <!-- Slide 3 -->
                  <div class="carousel-3d-slide" data-index="2">
                    <img src="vistas/recursos/assets/media/co/c3.png" alt="Programa 3">
                    <div class="slide-content">
                      <h3>Doctorado en Investigación</h3>
                      <p>Alcanza el más alto nivel académico con nuestro programa doctoral de investigación.</p>
                      <button class="btn-info-programa">Más Información</button>
                    </div>
                  </div>
                </div>

                <!-- Controles -->
                <div class="carousel-3d-controls">
                  <button class="carousel-3d-btn" id="prevBtn">
                    <i class="fas fa-chevron-left"></i>
                  </button>
                  <button class="carousel-3d-btn" id="nextBtn">
                    <i class="fas fa-chevron-right"></i>
                  </button>
                </div>

                <!-- Indicadores -->
                <div class="carousel-3d-indicators">
                  <button class="carousel-3d-indicator active" data-index="0"></button>
                  <button class="carousel-3d-indicator" data-index="1"></button>
                  <button class="carousel-3d-indicator" data-index="2"></button>
                </div>
              </div>

              <!-- SECCIÓN DE NOTICIAS E INFORMACIONES -->
              <div class="section-title animate-fade-in animate-delay-2">
                <h2>Noticias e Informaciones</h2>
                <p>Mantente informado sobre las últimas novedades del posgrado</p>
              </div>

              <div class="news-grid">
                <!-- Noticia 1 -->
                <div class="news-card animate-fade-in animate-delay-2">
                  <div class="news-card-image">
                    <img src="vistas/recursos/assets/media/news/news1.jpg" alt="Noticia 1" onerror="this.src='vistas/recursos/assets/media/co/c1.png'">
                    <span class="news-card-badge">NUEVO</span>
                  </div>
                  <div class="news-card-content">
                    <div class="news-card-meta">
                      <span><i class="far fa-calendar"></i> <?php echo date('d/m/Y'); ?></span>
                      <span><i class="far fa-user"></i> Administración</span>
                    </div>
                    <h3>Convocatoria Nuevos Programas 2025</h3>
                    <p>Se encuentran abiertas las inscripciones para los nuevos programas de posgrado. No pierdas esta oportunidad de formación académica de excelencia.</p>
                    <div class="news-card-footer">
                      <a href="#" class="btn-read-more">
                        Leer más <i class="fas fa-arrow-right"></i>
                      </a>
                      <span style="color: #667eea;"><i class="far fa-eye"></i> 245</span>
                    </div>
                  </div>
                </div>

                <!-- Noticia 2 -->
                <div class="news-card animate-fade-in animate-delay-3">
                  <div class="news-card-image">
                    <img src="vistas/recursos/assets/media/news/news2.jpg" alt="Noticia 2" onerror="this.src='vistas/recursos/assets/media/co/c2.png'">
                    <span class="news-card-badge" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">IMPORTANTE</span>
                  </div>
                  <div class="news-card-content">
                    <div class="news-card-meta">
                      <span><i class="far fa-calendar"></i> <?php echo date('d/m/Y', strtotime('-2 days')); ?></span>
                      <span><i class="far fa-user"></i> Secretaría</span>
                    </div>
                    <h3>Cronograma de Defensa de Tesis</h3>
                    <p>Se ha publicado el cronograma oficial para las defensas de tesis del presente semestre. Revisa las fechas y horarios asignados.</p>
                    <div class="news-card-footer">
                      <a href="#" class="btn-read-more">
                        Leer más <i class="fas fa-arrow-right"></i>
                      </a>
                      <span style="color: #667eea;"><i class="far fa-eye"></i> 189</span>
                    </div>
                  </div>
                </div>

                <!-- Noticia 3 -->
                <div class="news-card animate-fade-in animate-delay-4">
                  <div class="news-card-image">
                    <img src="vistas/recursos/assets/media/news/news3.jpg" alt="Noticia 3" onerror="this.src='vistas/recursos/assets/media/co/c3.png'">
                    <span class="news-card-badge" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">EVENTO</span>
                  </div>
                  <div class="news-card-content">
                    <div class="news-card-meta">
                      <span><i class="far fa-calendar"></i> <?php echo date('d/m/Y', strtotime('-5 days')); ?></span>
                      <span><i class="far fa-user"></i> Coordinación</span>
                    </div>
                    <h3>Seminario Internacional de Salud Pública</h3>
                    <p>Te invitamos a participar del seminario internacional con expositores de reconocido prestigio en el ámbito de la salud pública.</p>
                    <div class="news-card-footer">
                      <a href="#" class="btn-read-more">
                        Leer más <i class="fas fa-arrow-right"></i>
                      </a>
                      <span style="color: #667eea;"><i class="far fa-eye"></i> 312</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- SECCIÓN DE REDES SOCIALES -->
              <div class="social-section animate-fade-in">
                <div class="section-title" style="margin: 0 0 20px;">
                  <h2>Síguenos en Redes Sociales</h2>
                  <p>Mantente conectado y no te pierdas ninguna actualización</p>
                </div>

                <div class="social-grid">
                  <!-- Facebook -->
                  <div class="social-card facebook">
                    <div class="social-icon">
                      <i class="fab fa-facebook-f"></i>
                    </div>
                    <h4>Facebook</h4>
                    <p>Síguenos para estar al día con nuestras publicaciones</p>
                    <button class="btn-social" onclick="window.open('https://facebook.com', '_blank')">
                      Seguir
                    </button>
                  </div>

                  <!-- Instagram -->
                  <div class="social-card instagram">
                    <div class="social-icon">
                      <i class="fab fa-instagram"></i>
                    </div>
                    <h4>Instagram</h4>
                    <p>Descubre momentos únicos de nuestro posgrado</p>
                    <button class="btn-social" onclick="window.open('https://instagram.com', '_blank')">
                      Seguir
                    </button>
                  </div>

                  <!-- YouTube -->
                  <div class="social-card youtube">
                    <div class="social-icon">
                      <i class="fab fa-youtube"></i>
                    </div>
                    <h4>YouTube</h4>
                    <p>Videos educativos y transmisiones en vivo</p>
                    <button class="btn-social" onclick="window.open('https://youtube.com', '_blank')">
                      Suscribirse
                    </button>
                  </div>

                  <!-- Twitter -->
                  <div class="social-card twitter">
                    <div class="social-icon">
                      <i class="fab fa-twitter"></i>
                    </div>
                    <h4>Twitter</h4>
                    <p>Noticias y actualizaciones al instante</p>
                    <button class="btn-social" onclick="window.open('https://twitter.com', '_blank')">
                      Seguir
                    </button>
                  </div>
                </div>
              </div>

            </div>
            <!-- FIN CONTENIDO PRINCIPAL -->

          </div>
        </div>

        <?php
			$Footer = new FuncionesControladores();
			$Footer -> FooterControlador();
		?>

      </div>
    </div>
  </div>
</body>

<script>
// ========================================
// CARRUSEL 3D
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('carousel3d');
    const slides = document.querySelectorAll('.carousel-3d-slide');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const indicators = document.querySelectorAll('.carousel-3d-indicator');

    let currentIndex = 0;
    const totalSlides = slides.length;

    // Función para actualizar el carrusel
    function updateCarousel() {
        slides.forEach((slide, index) => {
            slide.classList.remove('active');

            // Calcular la posición 3D
            let offset = index - currentIndex;

            // Normalizar el offset para rotación circular
            if (offset > totalSlides / 2) offset -= totalSlides;
            if (offset < -totalSlides / 2) offset += totalSlides;

            // Aplicar transformaciones 3D
            const angle = offset * (360 / totalSlides);
            const translateZ = offset === 0 ? 200 : 0;
            const scale = offset === 0 ? 1 : 0.7;
            const opacity = offset === 0 ? 1 : 0.5;

            slide.style.transform = `
                translateX(-50%)
                rotateY(${angle}deg)
                translateZ(${translateZ}px)
                scale(${scale})
            `;
            slide.style.opacity = opacity;
            slide.style.zIndex = offset === 0 ? 10 : 1;

            if (offset === 0) {
                slide.classList.add('active');
            }
        });

        // Actualizar indicadores
        indicators.forEach((indicator, index) => {
            indicator.classList.toggle('active', index === currentIndex);
        });
    }

    // Navegación
    function nextSlide() {
        currentIndex = (currentIndex + 1) % totalSlides;
        updateCarousel();
    }

    function prevSlide() {
        currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
        updateCarousel();
    }

    // Event listeners
    nextBtn.addEventListener('click', nextSlide);
    prevBtn.addEventListener('click', prevSlide);

    // Indicadores
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => {
            currentIndex = index;
            updateCarousel();
        });
    });

    // Auto-play (opcional)
    let autoplayInterval = setInterval(nextSlide, 5000);

    // Pausar auto-play al hover
    carousel.addEventListener('mouseenter', () => {
        clearInterval(autoplayInterval);
    });

    carousel.addEventListener('mouseleave', () => {
        autoplayInterval = setInterval(nextSlide, 5000);
    });

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') prevSlide();
        if (e.key === 'ArrowRight') nextSlide();
    });

    // Inicializar
    updateCarousel();
});

// ========================================
// ANIMACIONES DE SCROLL
// ========================================
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observar elementos con animación
document.querySelectorAll('.animate-fade-in').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(30px)';
    observer.observe(el);
});
</script>
