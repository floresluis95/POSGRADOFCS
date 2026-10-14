
<style>
/* ========================================
   NAVBAR MODERNO - ESTILOS MEJORADOS
======================================== */

/* Header principal con gradiente */
#kt_header {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

/* Brand/Logo: el logo lleva texto oscuro, por eso va sobre un recuadro
   blanco (igual que en el portal público y el login) y la barra conserva
   el azul institucional. */
.kt-header__brand {
    background: transparent;
}

.kt-header__brand .kt-header__brand-logo {
    align-items: center;
    background: #fff;
    border-radius: 12px;
    padding: 6px 16px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.kt-header__brand .kt-header__brand-logo:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
}

.kt-header__brand-logo img {
    display: block;
    height: 52px;
    width: auto;
    transition: height 0.3s ease;
}

/* Cabecera minimizada al hacer scroll (60px de alto) */
.kt-header--minimize .kt-header__brand-logo img {
    height: 38px;
}

/* Menú de navegación mejorado */
.kt-menu__nav .kt-menu__item {
    margin: 0 5px;
    transition: all 0.3s ease;
}

.kt-menu__nav .kt-menu__link {
    color: rgba(255, 255, 255, 0.9) !important;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.kt-menu__nav .kt-menu__link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.1);
    transition: left 0.3s ease;
}

.kt-menu__nav .kt-menu__link:hover::before {
    left: 0;
}

.kt-menu__nav .kt-menu__link:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    color: #fff !important;
}

.kt-menu__link-icon {
    margin-right: 8px;
    font-size: 16px;
}

/* El texto de cada opción del encabezado en un solo renglón ("REPORTES PDF") */
#kt_header_menu .kt-menu__link-text {
    white-space: nowrap;
}

/* Avatar de usuario moderno */
.user-avatar-modern {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #52616e 0%, #37424a 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 18px;
    margin-right: 12px;
    box-shadow: 0 4px 15px rgba(55, 66, 74, 0.4);
    border: 3px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
    cursor: pointer;
}

.user-avatar-modern:hover {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 0 6px 20px rgba(55, 66, 74, 0.6);
}

.kt-header__topbar-wrapper {
    cursor: pointer;
    padding: 8px 15px;
    border-radius: 30px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
}

.kt-header__topbar-wrapper:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.user-info-modern {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.user-name-modern {
    color: white;
    font-weight: 700;
    font-size: 14px;
    line-height: 1.2;
    margin: 0;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.user-role-modern {
    color: rgba(255, 255, 255, 0.8);
    font-size: 11px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 2px;
}

.role-badge-modern {
    background: rgba(255, 255, 255, 0.3);
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.3px;
}

/* Menú desplegable del usuario. La animación solo cambia la opacidad: Bootstrap
   (Popper) lo posiciona con transform, y si la animación también usa transform
   el menú aparece primero a la izquierda y luego salta a la derecha. */
.dropdown-menu-modern {
    background: white;
    border: 1px solid #e3e6ef;
    border-radius: 6px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
    padding: 0;
    min-width: 230px;
    margin-top: 6px;
    overflow: hidden;
    animation: dropdownFadeIn 0.15s ease;
}

@keyframes dropdownFadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}

.dropdown-header-modern {
    padding: 12px 14px;
    background: #f7f8fa;
    border-bottom: 1px solid #e3e6ef;
}

.dropdown-user-info {
    display: flex;
    align-items: center;
}

.dropdown-user-avatar {
    flex-shrink: 0;
    width: 36px;
    height: 36px;
    background: #1e3c72;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 14px;
    margin-right: 10px;
}

.dropdown-user-details h5 {
    margin: 0;
    font-size: 13px;
    font-weight: 600;
    color: #3d4152;
    line-height: 1.3;
}

.dropdown-user-details p {
    margin: 2px 0 0;
    font-size: 11px;
    color: #74788d;
}

.dropdown-info-item {
    padding: 8px 14px;
    display: flex;
    align-items: center;
    color: #464457;
    font-size: 12px;
}

.dropdown-info-item i {
    width: 20px;
    color: #74788d;
    font-size: 12px;
}

.dropdown-divider-modern {
    margin: 0;
    border-top: 1px solid #e3e6ef;
}

.btn-logout-modern {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 9px 14px;
    color: #3d4152;
    font-size: 12px;
    font-weight: 500;
    transition: background 0.15s ease, color 0.15s ease;
}

.btn-logout-modern:hover {
    background: #f7f8fa;
    color: #b42318;
    text-decoration: none;
}

.btn-logout-modern i {
    width: 20px;
    font-size: 12px;
}

/* Botón de menú móvil */
.kt-aside-toggler {
    color: white;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    padding: 8px 12px;
    transition: all 0.3s ease;
}

.kt-aside-toggler:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.05);
}

/* Responsive */
@media (max-width: 768px) {
    .user-info-modern {
        display: none;
    }

    .user-avatar-modern {
        margin-right: 0;
    }
}

/* Hasta 1024px el tema convierte el menú del header en un panel lateral de
   fondo blanco: ahí el texto blanco de arriba no se vería, se usa el azul
   institucional. */
@media (max-width: 1024px) {
    #kt_header_menu .kt-menu__nav .kt-menu__item {
        margin: 2px 10px;
    }

    #kt_header_menu .kt-menu__nav .kt-menu__link,
    #kt_header_menu .kt-menu__nav .kt-menu__link .kt-menu__link-text,
    #kt_header_menu .kt-menu__nav .kt-menu__link .kt-menu__link-icon {
        color: #1e3c72 !important;
    }

    #kt_header_menu .kt-menu__nav .kt-menu__link:hover {
        background: #eef2f9;
        transform: none;
        box-shadow: none;
    }

    #kt_header_menu .kt-menu__nav .kt-menu__link::before {
        display: none;
    }
}
</style>

<?php
// Función helper para obtener iniciales
function obtenerIniciales($nombres, $apellidoPaterno) {
    $inicial1 = !empty($nombres) ? substr($nombres, 0, 1) : '';
    $inicial2 = !empty($apellidoPaterno) ? substr($apellidoPaterno, 0, 1) : '';
    return strtoupper($inicial1 . $inicial2);
}

// Función helper para obtener nombre completo
function obtenerNombreCompleto($nombres, $apellidoPaterno, $apellidoMaterno) {
    return trim($nombres . ' ' . $apellidoPaterno . ' ' . $apellidoMaterno);
}

// Función helper para obtener rol formateado
function obtenerRolFormateado($tipo) {
    $roles = [
        'ADM' => ['nombre' => 'Administrador', 'icono' => 'fas fa-user-shield'],
        'SEC' => ['nombre' => 'Secretaría', 'icono' => 'fas fa-user-tie'],
        'AUX' => ['nombre' => 'Auxiliar', 'icono' => 'fas fa-user-cog'],
        'DOC' => ['nombre' => 'Docente', 'icono' => 'fas fa-chalkboard-teacher'],
        'EST' => ['nombre' => 'Estudiante', 'icono' => 'fas fa-user-graduate']
    ];
    return $roles[$tipo] ?? ['nombre' => 'Usuario', 'icono' => 'fas fa-user'];
}

// Función helper para obtener menú según tipo de usuario
function obtenerMenuPorTipo($tipo) {
    // Inicio y Calendario para todos los roles (todos tienen acceso al calendario)
    $comunes = [
        ['url' => 'panel', 'icono' => 'fas fa-home', 'texto' => 'INICIO'],
        ['url' => 'calendario', 'icono' => 'fas fa-calendar-alt', 'texto' => 'CALENDARIO']
    ];
    $extras = [
        'ADM' => [
            ['url' => 'reportes', 'icono' => 'fas fa-file-pdf', 'texto' => 'REPORTES PDF']
        ],
        'SEC' => [
            ['url' => 'consultas', 'icono' => 'fas fa-search', 'texto' => 'CONSULTAS']
        ]
    ];
    return array_merge($comunes, $extras[$tipo] ?? []);
}

// Obtener datos de sesión
$tipoUsuario = $_SESSION["Tipo"] ?? '';
$nombres = $_SESSION['Nombres'] ?? '';
$apellidoPaterno = $_SESSION['ApellidoPaterno'] ?? '';
$apellidoMaterno = $_SESSION['ApellidoMaterno'] ?? '';
$usuario = $_SESSION['Usuario'] ?? '';

$iniciales = obtenerIniciales($nombres, $apellidoPaterno);
$nombreCompleto = obtenerNombreCompleto($nombres, $apellidoPaterno, $apellidoMaterno);
$rolInfo = obtenerRolFormateado($tipoUsuario);
$menuItems = obtenerMenuPorTipo($tipoUsuario);
?>

<div id="kt_header" class="kt-header kt-header--fixed" data-ktheader-minimize="on">
    <div class="kt-container kt-container--fluid">
        <!-- begin: Header Menu -->
        <button class="kt-header-menu-wrapper-close" id="kt_header_menu_mobile_close_btn">
            <i class="la la-close"></i>
        </button>

        <div class="kt-header-menu-wrapper kt-grid__item kt-grid__item--fluid" id="kt_header_menu_wrapper">
            <button class="kt-aside-toggler kt-aside-toggler--left" id="kt_aside_toggler">
                <span></span>
            </button>

            <div id="kt_header_menu" class="kt-header-menu kt-header-menu-mobile">
                <ul class="kt-menu__nav">
                    <?php foreach ($menuItems as $item): ?>
                    <li class="kt-menu__item" aria-haspopup="true">
                        <a href="<?php echo $item['url']; ?>" class="kt-menu__link">
                            <i class="kt-menu__link-icon <?php echo $item['icono']; ?>"></i>
                            <span class="kt-menu__link-text"><?php echo $item['texto']; ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <!-- end: Header Menu -->

        <!-- begin:: Brand -->
        <div class="kt-header__brand kt-grid__item" id="kt_header_brand">
            <a class="kt-header__brand-logo" href="panel">
                <img alt="Posgrado Odontología - Universidad Técnica de Oruro" src="vistas/recursos/assets/media/logos/logo.png" />
            </a>
        </div>
        <!-- end:: Brand -->

        <!-- begin:: Header Topbar -->
        <div class="kt-header__topbar kt-grid__item">
            <div class="kt-header__topbar-item kt-header__topbar-item--user">
                <div class="kt-header__topbar-wrapper" data-toggle="dropdown" data-offset="10px,0px">
                    <div class="user-avatar-modern">
                        <?php echo $iniciales; ?>
                    </div>
                    <div class="user-info-modern">
                        <div class="user-name-modern"><?php echo htmlspecialchars($nombreCompleto); ?></div>
                        <div class="user-role-modern">
                            <i class="<?php echo $rolInfo['icono']; ?>"></i>
                            <span class="role-badge-modern"><?php echo $rolInfo['nombre']; ?></span>
                        </div>
                    </div>
                    <i class="fas fa-chevron-down" style="color: white; margin-left: 10px; font-size: 12px;"></i>
                </div>

                <!-- Dropdown Mejorado -->
                <div class="dropdown-menu dropdown-menu-right dropdown-menu-modern">
                    <div class="dropdown-header-modern">
                        <div class="dropdown-user-info">
                            <div class="dropdown-user-avatar">
                                <?php echo $iniciales; ?>
                            </div>
                            <div class="dropdown-user-details">
                                <h5><?php echo htmlspecialchars($nombreCompleto); ?></h5>
                                <p><i class="<?php echo $rolInfo['icono']; ?>"></i> <?php echo $rolInfo['nombre']; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="dropdown-info-item">
                        <i class="fas fa-user"></i>
                        <span><?php echo htmlspecialchars($usuario); ?></span>
                    </div>

                    <div class="dropdown-divider-modern"></div>

                    <a href="salir" class="btn-logout-modern">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar Sesión</span>
                    </a>
                </div>
            </div>
        </div>
        <!-- end:: Header Topbar -->
    </div>

    <?php if ($tipoUsuario === 'EST'): ?>
    <!-- Aviso de cuota vencida para el Estudiante (una vez por sesión de navegador) -->
    <script src="vistas/recursos/assets/js/scripts/aviso-cuota-estudiante.js?v=<?php echo time(); ?>"></script>
    <?php endif; ?>
</div>
