        <!-- begin:: Footer Resumido -->
<style>
.footer-resumido {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: #bdc3c7;
    padding: 12px 0;
    margin-top: 30px;
    font-size: 12px;
    text-align: center;
}

.footer-resumido a {
    color: #3498db;
    text-decoration: none;
    font-weight: 600;
}

.footer-resumido a:hover {
    text-decoration: underline;
}
</style>

<div class="footer-resumido kt-grid__item" id="kt_footer">
    <div class="kt-container">
        <?php echo date('Y'); ?> &copy;
        <a href="https://www.uto.edu.bo" target="_blank"><?php echo htmlspecialchars(InstitucionModelo::Formal(InstitucionModelo::Universidad())); ?></a>
        - <?php echo htmlspecialchars(InstitucionModelo::Formal(InstitucionModelo::Facultad())); ?>. Todos los derechos reservados.
    </div>
</div>

<script>
// Este parcial se incluye en vistas con estructura de layout distinta (algunas
// no tienen el wrapper flex vertical completo del tema), así que en páginas con
// poco contenido el footer queda pegado justo debajo de la tabla/formulario en
// vez de al fondo de la pantalla. En vez de depender de la jerarquía flex de
// cada vista, se resuelve con JS: si el contenido no llena la ventana, se fija
// el footer al fondo (reservando su alto con padding-bottom en el body para no
// tapar contenido); si el contenido ya tiene scroll propio, se deja fluir
// normalmente después del contenido, como siempre.
(function() {
    function ajustarFooterResumido() {
        var footer = document.getElementById('kt_footer');
        if (!footer) return;

        footer.style.position = '';
        footer.style.left = '';
        footer.style.bottom = '';
        footer.style.width = '';
        footer.style.zIndex = '';
        document.body.style.paddingBottom = '';

        var contenidoCabeEnPantalla = document.documentElement.scrollHeight <= window.innerHeight;
        if (contenidoCabeEnPantalla) {
            var alto = footer.offsetHeight;
            footer.style.position = 'fixed';
            footer.style.left = '0';
            footer.style.bottom = '0';
            footer.style.width = '100%';
            footer.style.zIndex = '5';
            document.body.style.paddingBottom = alto + 'px';
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ajustarFooterResumido);
    } else {
        ajustarFooterResumido();
    }
    window.addEventListener('load', ajustarFooterResumido);
    window.addEventListener('resize', ajustarFooterResumido);
})();
</script>

        <!-- end:: Footer Resumido -->
