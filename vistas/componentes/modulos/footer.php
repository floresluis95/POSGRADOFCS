        <!-- begin:: Footer -->
<style>
.footer-moderno {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    padding: 40px 0 20px 0;
    margin-top: 50px;
    box-shadow: 0 -5px 20px rgba(0,0,0,0.1);
}

.footer-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin-bottom: 30px;
}

.footer-section h4 {
    color: #3498db;
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 15px;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.footer-section p,
.footer-section ul {
    color: #bdc3c7;
    font-size: 13px;
    line-height: 1.8;
    margin: 0;
    padding: 0;
    list-style: none;
}

.footer-section ul li {
    margin-bottom: 8px;
    padding-left: 20px;
    position: relative;
}

.footer-section ul li:before {
    content: "▸";
    position: absolute;
    left: 0;
    color: #3498db;
    font-weight: bold;
}

.footer-section a {
    color: #bdc3c7;
    text-decoration: none;
    transition: all 0.3s ease;
}

.footer-section a:hover {
    color: #3498db;
    padding-left: 5px;
}

.footer-info-badge {
    background: rgba(52, 152, 219, 0.2);
    padding: 8px 15px;
    border-radius: 8px;
    margin-bottom: 10px;
    display: inline-block;
    border-left: 3px solid #3498db;
}

.footer-bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 20px;
    text-align: center;
}

.footer-bottom-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.footer-copyright {
    color: #95a5a6;
    font-size: 13px;
}

.footer-copyright a {
    color: #3498db;
    text-decoration: none;
    font-weight: 600;
}

.footer-copyright a:hover {
    color: #2980b9;
    text-decoration: underline;
}

.footer-tech-info {
    display: flex;
    gap: 20px;
    align-items: center;
    flex-wrap: wrap;
}

.tech-badge {
    background: rgba(52, 152, 219, 0.15);
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 11px;
    color: #3498db;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 5px;
}

.social-links {
    display: flex;
    gap: 10px;
}

.social-link {
    width: 35px;
    height: 35px;
    background: rgba(52, 152, 219, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3498db;
    transition: all 0.3s ease;
    text-decoration: none;
}

.social-link:hover {
    background: #3498db;
    color: white;
    transform: translateY(-3px);
}

@media (max-width: 768px) {
    .footer-content {
        grid-template-columns: 1fr;
    }

    .footer-bottom-content {
        flex-direction: column;
        text-align: center;
    }

    .footer-tech-info {
        justify-content: center;
    }
}
</style>

<div class="footer-moderno kt-grid__item" id="kt_footer">
    <div class="kt-container">
        <div class="footer-content">
            <!-- Sección: Acerca del Sistema -->
            <div class="footer-section">
                <h4>
                    <i class="fas fa-info-circle"></i>
                    Sistema de Gestión
                </h4>
                <p style="margin-bottom: 15px;">
                    Sistema Integral de Gestión Académica para Programas de Posgrado de la Facultad de Ciencias de la Salud - UTO.
                </p>
                <div class="footer-info-badge">
                    <strong>Versión:</strong> 1.0.0
                </div>
            </div>

            <!-- Sección: Información Institucional -->
            <div class="footer-section">
                <h4>
                    <i class="fas fa-university"></i>
                    Institución
                </h4>
                <ul>
                    <li>Universidad Técnica de Oruro</li>
                    <li>Facultad de Ciencias de la Salud</li>
                    <li>Dirección de Posgrado</li>
                    <li>Oruro - Bolivia</li>
                </ul>
            </div>

            <!-- Sección: Módulos del Sistema -->
            <div class="footer-section">
                <h4>
                    <i class="fas fa-th-large"></i>
                    Módulos Principales
                </h4>
                <ul>
                    <li>Gestión de Estudiantes</li>
                    <li>Gestión de Programas</li>
                    <li>Gestión de Docentes</li>
                    <li>Control de Pagos</li>
                    <li>Reportes y Estadísticas</li>
                </ul>
            </div>

            <!-- Sección: Soporte y Contacto -->
            <div class="footer-section">
                <h4>
                    <i class="fas fa-headset"></i>
                    Soporte Técnico
                </h4>
                <p style="margin-bottom: 10px;">
                    <i class="fas fa-envelope" style="color: #3498db; margin-right: 8px;"></i>
                    soporte@fcs.uto.edu.bo
                </p>
                <p style="margin-bottom: 10px;">
                    <i class="fas fa-phone" style="color: #3498db; margin-right: 8px;"></i>
                    (+591) 2-527-0220
                </p>
                <p>
                    <i class="fas fa-clock" style="color: #3498db; margin-right: 8px;"></i>
                    Lun - Vie: 8:00 - 18:00
                </p>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <div class="footer-copyright">
                    <?php echo date('Y'); ?> &copy;
                    <a href="https://www.uto.edu.bo" target="_blank">Universidad Técnica de Oruro</a>
                    - Facultad de Ciencias de la Salud. Todos los derechos reservados.
                </div>

                <div class="footer-tech-info">
                    <div class="tech-badge">
                        <i class="fab fa-php"></i>
                        PHP
                    </div>
                    <div class="tech-badge">
                        <i class="fas fa-database"></i>
                        MySQL
                    </div>
                    <div class="tech-badge">
                        <i class="fab fa-js"></i>
                        JavaScript
                    </div>
                </div>

                <div class="social-links">
                    <a href="https://www.facebook.com/uto.oficial" target="_blank" class="social-link" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://www.uto.edu.bo" target="_blank" class="social-link" title="Sitio Web UTO">
                        <i class="fas fa-globe"></i>
                    </a>
                    <a href="mailto:posgrado@fcs.uto.edu.bo" class="social-link" title="Email">
                        <i class="fas fa-envelope"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

        <!-- end:: Footer -->
