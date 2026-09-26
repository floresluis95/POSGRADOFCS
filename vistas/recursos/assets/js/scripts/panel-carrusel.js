/**
 * Carrusel 3D de programas y animaciones de scroll.
 * Compartido por el Panel (con sesión) y el Portal público (inicio).
 */
// ========================================
// CARRUSEL 3D
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('carousel3d');
    const slides = document.querySelectorAll('.carousel-3d-slide');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const indicators = document.querySelectorAll('.carousel-3d-indicator');

    // Sin diapositivas activas no se renderiza el carrusel
    if (!carousel || slides.length === 0) return;

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
