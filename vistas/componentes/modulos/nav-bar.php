
<style>
/* ========================================
   NAVBAR MODERNO - ESTILOS MEJORADOS
======================================== */

/* Header principal con gradiente */
#kt_header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

/* Brand/Logo mejorado */
.kt-header__brand {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.kt-header__brand:hover {
    background: rgba(255, 255, 255, 0.15);
}

.kt-header__brand-logo img {
    transition: transform 0.3s ease;
}

.kt-header__brand-logo:hover img {
    transform: scale(1.05);
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

/* Avatar de usuario moderno */
.user-avatar-modern {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 18px;
    margin-right: 12px;
    box-shadow: 0 4px 15px rgba(245, 87, 108, 0.4);
    border: 3px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
    cursor: pointer;
}

.user-avatar-modern:hover {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 0 6px 20px rgba(245, 87, 108, 0.6);
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

/* Dropdown mejorado */
.dropdown-menu-modern {
    background: white;
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    padding: 20px 0;
    min-width: 280px;
    margin-top: 10px;
    animation: dropdownFadeIn 0.3s ease;
}

@keyframes dropdownFadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dropdown-header-modern {
    padding: 0 20px 15px;
    border-bottom: 2px solid #f0f3ff;
    margin-bottom: 15px;
}

.dropdown-user-info {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.dropdown-user-avatar {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 20px;
    margin-right: 15px;
}

.dropdown-user-details h5 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: #464457;
}

.dropdown-user-details p {
    margin: 2px 0 0;
    font-size: 12px;
    color: #74788d;
}

.dropdown-info-item {
    padding: 10px 20px;
    display: flex;
    align-items: center;
    color: #464457;
    font-size: 13px;
    transition: background 0.2s ease;
}

.dropdown-info-item:hover {
    background: #f8f9ff;
}

.dropdown-info-item i {
    width: 25px;
    color: #667eea;
    font-size: 14px;
}

.dropdown-divider-modern {
    margin: 10px 0;
    border-top: 1px solid #f0f3ff;
}

.btn-logout-modern {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: calc(100% - 40px);
    margin: 10px 20px 0;
    padding: 12px;
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-logout-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(245, 87, 108, 0.4);
    background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%);
}

.btn-logout-modern i {
    font-size: 16px;
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
        'DOC' => ['nombre' => 'Docente', 'icono' => 'fas fa-chalkboard-teacher'],
        'EST' => ['nombre' => 'Estudiante', 'icono' => 'fas fa-user-graduate']
    ];
    return $roles[$tipo] ?? ['nombre' => 'Usuario', 'icono' => 'fas fa-user'];
}

// Función helper para obtener menú según tipo de usuario
function obtenerMenuPorTipo($tipo) {
    $menus = [
        'ADM' => [
            ['url' => 'panel', 'icono' => 'fas fa-home', 'texto' => 'INICIO'],
            ['url' => 'reportes', 'icono' => 'fas fa-file-pdf', 'texto' => 'REPORTES PDF']
        ],
        'SEC' => [
            ['url' => 'panel', 'icono' => 'fas fa-home', 'texto' => 'INICIO'],
            ['url' => 'consultas', 'icono' => 'fas fa-search', 'texto' => 'CONSULTAS']
        ],
        'DOC' => [
            ['url' => 'panel', 'icono' => 'fas fa-home', 'texto' => 'INICIO']
        ],
        'EST' => [
            ['url' => 'panel', 'icono' => 'fas fa-home', 'texto' => 'INICIO'],
           
        ]
    ];
    return $menus[$tipo] ?? [['url' => 'panel', 'icono' => 'fas fa-home', 'texto' => 'INICIO']];
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
                <img alt="Logo" src="vistas/recursos/assets/media/logos/logo.png" width="150" />
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

                    <div class="dropdown-info-item">
                        <i class="fas fa-id-badge"></i>
                        <span><?php echo htmlspecialchars($nombres . ' ' . $apellidoPaterno); ?></span>
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
</div>
