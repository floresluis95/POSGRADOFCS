<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión - Posgrado FCS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            /* Solo se oculta el desborde horizontal (partículas): en móviles la
               tarjeta es más alta que la pantalla y debe poder desplazarse. */
            overflow-x: hidden;
            overflow-y: auto;
            min-height: 100vh;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        }

        /* Partículas de fondo animadas */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float linear infinite;
        }

        @keyframes float {
            from {
                transform: translateY(100vh) rotate(0deg);
                opacity: 1;
            }
            to {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }

        /* Container principal */
        .login-container {
            position: relative;
            z-index: 2;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            /* El tema hace del body un flex en columna de alto fijo: sin esto el
               contenedor se encoge y en móviles la tarjeta queda cortada arriba. */
            flex-shrink: 0;
        }

        /* Card de login */
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            width: 100%;
            max-width: 950px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 600px;
            animation: slideIn 0.8s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Panel izquierdo con imagen */
        .login-left {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 15s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1) rotate(0deg);
            }
            50% {
                transform: scale(1.1) rotate(180deg);
            }
        }

        .logo-container {
            position: relative;
            z-index: 2;
            text-align: center;
            margin-bottom: 40px;
        }

        /* Fondo blanco para que el texto oscuro del logo se lea sobre el azul */
        .logo-container .logo-fondo {
            display: inline-block;
            background: #fff;
            border-radius: 16px;
            padding: 12px 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            animation: logoFloat 3s ease-in-out infinite;
        }

        .logo-container img {
            display: block;
            width: 100%;
            max-width: 300px;
            height: auto;
        }

        @keyframes logoFloat {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .welcome-text {
            position: relative;
            z-index: 2;
            color: white;
            text-align: center;
        }

        .welcome-text h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 15px;
            text-shadow: 0 4px 15px rgba(0,0,0,0.2);
            line-height: 1.3;
        }

        .welcome-text p {
            font-size: 16px;
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .features {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            gap: 15px;
            width: 100%;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 15px;
            color: white;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .feature-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(10px);
        }

        .feature-icon {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .feature-text {
            flex: 1;
        }

        .feature-text strong {
            display: block;
            font-size: 14px;
            margin-bottom: 3px;
        }

        .feature-text span {
            font-size: 12px;
            opacity: 0.9;
        }

        /* Panel derecho con formulario */
        .login-right {
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            margin-bottom: 40px;
        }

        .login-header h2 {
            font-size: 32px;
            color: #333;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .login-header p {
            color: #666;
            font-size: 15px;
        }

        /* Formulario */
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .form-group {
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #444;
            font-weight: 600;
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #1e3c72;
            font-size: 18px;
            z-index: 1;
        }

        .form-control {
            width: 100%;
            padding: 16px 20px 16px 55px;
            border: 2px solid #e8ecef;
            border-radius: 15px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            outline: none;
            border-color: #1e3c72;
            background: white;
            box-shadow: 0 0 0 4px rgba(30, 60, 114, 0.1);
        }

        .form-control::placeholder {
            color: #aaa;
        }

        /* Checkbox Remember Me */
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: -10px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #1e3c72;
        }

        .remember-me label {
            font-size: 14px;
            color: #666;
            cursor: pointer;
            margin: 0;
        }

        .forgot-password {
            color: #1e3c72;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .forgot-password:hover {
            color: #2a5298;
            text-decoration: underline;
        }

        /* Botón "ojito" para mostrar/ocultar la contraseña */
        .form-control.con-toggle {
            padding-right: 55px;
        }

        /* Ocultar el ojito nativo de Edge para que no se duplique con el nuestro */
        .form-control.con-toggle::-ms-reveal,
        .form-control.con-toggle::-ms-clear {
            display: none;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 8px;
            color: #888;
            font-size: 17px;
            cursor: pointer;
            z-index: 1;
            transition: color 0.2s ease;
        }

        .toggle-password:hover,
        .toggle-password:focus {
            color: #1e3c72;
            outline: none;
        }

        /* Modal "¿Olvidó su contraseña?" */
        .modal-olvido {
            position: fixed;
            inset: 0;
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
            background: rgba(15, 30, 60, 0.6);
        }

        .modal-olvido.abierto {
            display: flex;
        }

        .modal-olvido-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 420px;
            width: 100%;
            padding: 35px 30px 30px;
            text-align: center;
            animation: slideIn 0.3s ease;
        }

        .modal-olvido-icono {
            width: 70px;
            height: 70px;
            margin: 0 auto 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(30, 60, 114, 0.1);
            color: #1e3c72;
            font-size: 30px;
        }

        .modal-olvido-card h3 {
            color: #1e3c72;
            font-size: 21px;
            margin-bottom: 12px;
        }

        .modal-olvido-card p {
            color: #555;
            font-size: 15px;
            line-height: 1.5;
            margin-bottom: 25px;
        }

        .modal-olvido-card .btn-cerrar-olvido {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 12px 35px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }

        /* Botón de login */
        .btn-login {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
            box-shadow: 0 10px 30px rgba(30, 60, 114, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(30, 60, 114, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login i {
            margin-left: 8px;
        }

        /* Footer */
        .login-footer {
            margin-top: 30px;
            text-align: center;
            color: #999;
            font-size: 13px;
        }

        .link-volver-inicio {
            display: inline-block;
            margin-top: 18px;
            color: #2a5298;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
        }

        .link-volver-inicio:hover {
            text-decoration: underline;
        }

        /* Loading state */
        .btn-login.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .btn-login.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-card {
                grid-template-columns: 1fr;
                max-width: 450px;
            }

            .login-left {
                padding: 40px 30px;
                min-height: auto;
            }

            .welcome-text h1 {
                font-size: 22px;
            }

            .welcome-text p {
                font-size: 14px;
            }

            .features {
                display: none;
            }

            .login-right {
                padding: 40px 30px;
            }

            .login-header h2 {
                font-size: 26px;
            }

            .logo-container {
                margin-bottom: 20px;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 12px;
            }

            .login-card {
                border-radius: 20px;
            }

            .login-left {
                padding: 30px 20px;
            }

            .login-right {
                padding: 30px 20px;
            }
        }

        /* Alertas mejoradas */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-error {
            background: #fee;
            color: #c33;
            border: 2px solid #fcc;
        }

        .alert-success {
            background: #efe;
            color: #3c3;
            border: 2px solid #cfc;
        }

        .alert i {
            font-size: 20px;
        }
    </style>
</head>
<body>
    <!-- Partículas de fondo -->
    <div class="particles" id="particles"></div>

    <!-- Container principal -->
    <div class="login-container">
        <div class="login-card">
            <!-- Panel izquierdo -->
            <div class="login-left">
                <div class="logo-container">
                    <div class="logo-fondo">
                        <img src="vistas/recursos/assets/media/logos/logo.png" alt="Posgrado Odontología - Universidad Técnica de Oruro">
                    </div>
                </div>

                <div class="welcome-text">
                    <h1>Universidad Técnica de Oruro</h1>
                    <p>Posgrado Facultad Ciencias de la Salud<br>"ODONTOLOGÍA"</p>
                </div>

                <div class="features">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="feature-text">
                            <strong>Excelencia Académica</strong>
                            <span>Programas de calidad</span>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="feature-text">
                            <strong>Comunidad Profesional</strong>
                            <span>Docentes especializados</span>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <div class="feature-text">
                            <strong>Certificación</strong>
                            <span>Títulos reconocidos</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel derecho -->
            <div class="login-right">
                <div class="login-header">
                    <h2>Bienvenido</h2>
                    <p>Ingresa tus credenciales para acceder al sistema</p>
                </div>

                <form class="login-form" method="POST" id="loginForm">
                    <div class="form-group">
                        <label for="usuario">Usuario</label>
                        <div class="input-wrapper">
                            <i class="input-icon fas fa-user"></i>
                            <input
                                type="text"
                                class="form-control"
                                id="usuario"
                                name="IUsuario"
                                placeholder="Ingrese su usuario"
                                autocomplete="username"
                                required
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <div class="input-wrapper">
                            <i class="input-icon fas fa-lock"></i>
                            <input
                                type="password"
                                class="form-control con-toggle"
                                id="password"
                                name="IPassword"
                                placeholder="Ingrese su contraseña"
                                autocomplete="current-password"
                                required
                            >
                            <button type="button" class="toggle-password" id="togglePassword" aria-label="Mostrar contraseña" title="Mostrar contraseña">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="remember-forgot">
                        <div class="remember-me">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Recordarme</label>
                        </div>
                        <a href="#" class="forgot-password" id="linkOlvidoPassword">¿Olvidó su contraseña?</a>
                    </div>

                    <button type="submit" class="btn-login" id="btnLogin">
                        <span>Iniciar Sesión</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>

                <?php
                $Ingreso = new IngresoControladores();
                $Ingreso->ValidarIngresoControlador();
                ?>

                <div class="login-footer">
                    <a href="inicio" class="link-volver-inicio"><i class="fas fa-arrow-left"></i> Volver al inicio</a>
                    <p style="margin-top: 12px;">&copy; <?php echo date('Y'); ?> Universidad Técnica de Oruro. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal "¿Olvidó su contraseña?": el restablecimiento lo hace el administrador -->
    <div class="modal-olvido" id="modalOlvidoPassword" role="dialog" aria-modal="true" aria-labelledby="tituloOlvidoPassword">
        <div class="modal-olvido-card">
            <div class="modal-olvido-icono"><i class="fas fa-user-shield"></i></div>
            <h3 id="tituloOlvidoPassword">¿Olvidó su contraseña?</h3>
            <p>Para recuperar el acceso, comuníquese con el <strong>administrador del sistema</strong>, quien restablecerá su contraseña y le entregará una nueva.</p>
            <button type="button" class="btn-cerrar-olvido" id="btnCerrarOlvido">Entendido</button>
        </div>
    </div>

    <script>
        // Crear partículas animadas
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 30;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';

                const size = Math.random() * 60 + 20;
                const left = Math.random() * 100;
                const duration = Math.random() * 15 + 10;
                const delay = Math.random() * 5;

                particle.style.width = size + 'px';
                particle.style.height = size + 'px';
                particle.style.left = left + '%';
                particle.style.animationDuration = duration + 's';
                particle.style.animationDelay = delay + 's';

                particlesContainer.appendChild(particle);
            }
        }

        createParticles();

        // Efecto de loading en el botón
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('btnLogin');
            btn.classList.add('loading');
            btn.querySelector('span').textContent = 'Iniciando sesión...';
        });

        // Focus automático en el campo de usuario
        document.getElementById('usuario').focus();

        // Enter en el campo de usuario pasa a contraseña
        document.getElementById('usuario').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('password').focus();
            }
        });

        // Mostrar/ocultar la contraseña con el ojito
        document.getElementById('togglePassword').addEventListener('click', function() {
            const input = document.getElementById('password');
            const icono = this.querySelector('i');
            const mostrar = input.type === 'password';

            input.type = mostrar ? 'text' : 'password';
            icono.classList.toggle('fa-eye', !mostrar);
            icono.classList.toggle('fa-eye-slash', mostrar);
            this.title = mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña';
            this.setAttribute('aria-label', this.title);
            input.focus();
        });

        // Modal "¿Olvidó su contraseña?"
        const modalOlvido = document.getElementById('modalOlvidoPassword');

        function cerrarModalOlvido() {
            modalOlvido.classList.remove('abierto');
        }

        document.getElementById('linkOlvidoPassword').addEventListener('click', function(e) {
            e.preventDefault();
            modalOlvido.classList.add('abierto');
            document.getElementById('btnCerrarOlvido').focus();
        });

        document.getElementById('btnCerrarOlvido').addEventListener('click', cerrarModalOlvido);

        // Cerrar al hacer clic fuera de la tarjeta o con Escape
        modalOlvido.addEventListener('click', function(e) {
            if (e.target === modalOlvido) cerrarModalOlvido();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') cerrarModalOlvido();
        });
    </script>
</body>
</html>
