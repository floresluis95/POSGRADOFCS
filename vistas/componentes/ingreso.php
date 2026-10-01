<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión - Posgrado FCS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        /* ========================================
           LOGIN (prefijo lg-)
           Fondo: escena 3D (three.js) con una esfera de puntos conectados que gira
           y sigue al mouse. Encima: texto institucional y tarjeta de acceso con
           inclinación 3D. Sin WebGL queda el degradado azul de respaldo.
        ======================================== */
        body.lg-body {
            --lg-primario: #1e3c72;
            --lg-primario-2: #2a5298;
            --lg-acento: #7fb2ff;
            --lg-texto: #1f2937;
            --lg-texto-2: #6b7280;
            --lg-borde: #dfe4ec;
            --lg-peligro: #b91c1c;
            margin: 0;
            min-height: 100vh;
            color: var(--lg-texto);
            background:
                radial-gradient(ellipse at 30% 40%, #25498a 0%, transparent 55%),
                radial-gradient(ellipse at 90% 90%, #1b3a78 0%, transparent 50%),
                linear-gradient(160deg, #0b1a3a 0%, #122a5a 55%, #0d1f45 100%);
            background-attachment: fixed;
            /* Solo se oculta el desborde horizontal: en móviles la tarjeta puede
               ser más alta que la pantalla y debe poder desplazarse. */
            overflow-x: hidden;
            overflow-y: auto;
        }
        .lg-body *, .lg-body *::before, .lg-body *::after { box-sizing: border-box; }

        /* Lienzo 3D a pantalla completa, detrás del contenido */
        #lgEscena {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            display: block;
            pointer-events: none;
        }
        /* Viñeta para dar profundidad y legibilidad al texto */
        .lg-vineta {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background: radial-gradient(ellipse at center, transparent 40%, rgba(5, 12, 30, .55) 100%);
        }

        .lg-pantalla {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            width: 100%;
            max-width: 1240px;
            margin: 0 auto;
            padding: 40px 48px;
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(0, 440px);
            gap: 64px;
            align-items: center;
            /* El tema hace del body un flex en columna de alto fijo: sin esto el
               contenedor se encoge y en móviles la tarjeta queda cortada. */
            flex-shrink: 0;
        }

        /* Texto institucional (izquierda) */
        .lg-intro { color: #fff; animation: lg-aparecer .9s ease both; }
        .lg-marca {
            display: flex;
            width: max-content;
            align-items: center;
            background: rgba(255, 255, 255, .95);
            border-radius: 14px;
            padding: 8px 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .25);
            margin-bottom: 44px;
        }
        .lg-marca img { height: 46px; width: auto; display: block; }
        .lg-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--lg-acento);
            margin-bottom: 16px;
        }
        .lg-eyebrow::before { content: ''; width: 28px; height: 2px; border-radius: 2px; background: var(--lg-acento); }
        .lg-intro h1 {
            text-shadow: 0 2px 24px rgba(5, 12, 30, .5);
            font-size: 46px;
            line-height: 1.12;
            font-weight: 700;
            color: #fff;
            margin: 0 0 18px;
            max-width: 560px;
            letter-spacing: -.01em;
        }
        .lg-intro h1 span {
            background: linear-gradient(90deg, #ffffff 0%, #a9c8ff 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: none;
        }
        .lg-intro-texto {
            font-size: 16px;
            line-height: 1.7;
            color: rgba(255, 255, 255, .75);
            margin: 0 0 32px;
            max-width: 480px;
        }
        .lg-chips { display: flex; flex-wrap: wrap; gap: 10px; max-width: 520px; }
        .lg-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 16px;
            border-radius: 999px;
            font-size: 13px;
            color: rgba(255, 255, 255, .9);
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .14);
            backdrop-filter: blur(6px);
        }
        .lg-chip i { color: var(--lg-acento); }

        /* Tarjeta de acceso (derecha) con inclinación 3D */
        .lg-tarjeta-escena { perspective: 1200px; animation: lg-aparecer-tarjeta 1s cubic-bezier(.2, .8, .2, 1) both .15s; }
        .lg-tarjeta {
            position: relative;
            background: rgba(255, 255, 255, .97);
            border-radius: 24px;
            padding: 40px 38px 30px;
            box-shadow:
                0 1px 0 rgba(255, 255, 255, .6) inset,
                0 30px 60px rgba(3, 10, 30, .45),
                0 0 0 1px rgba(255, 255, 255, .25);
            transform-style: preserve-3d;
            transition: transform .25s ease-out;
            will-change: transform;
        }
        /* Brillo que sigue al mouse sobre la tarjeta */
        .lg-tarjeta::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            pointer-events: none;
            background: radial-gradient(circle at var(--brillo-x, 50%) var(--brillo-y, 0%), rgba(127, 178, 255, .16) 0%, transparent 45%);
        }
        .lg-tarjeta > * { position: relative; z-index: 1; }
        .lg-icono-acceso {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: #fff;
            font-size: 20px;
            margin-bottom: 20px;
            box-shadow: 0 10px 24px rgba(30, 60, 114, .35);
            transform: translateZ(30px);
        }
        .lg-tarjeta h2 { font-size: 26px; font-weight: 700; color: var(--lg-texto); margin: 0 0 6px; }
        .lg-subtitulo { font-size: 14px; color: var(--lg-texto-2); margin: 0 0 26px; }

        .login-form { display: flex; flex-direction: column; gap: 18px; }
        .lg-campo label { display: block; font-size: 13px; font-weight: 600; color: var(--lg-texto); margin-bottom: 7px; }
        .input-wrapper { position: relative; }
        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9aa3b2;
            font-size: 14px;
            pointer-events: none;
            transition: color .2s;
        }
        .lg-body .form-control {
            display: block;
            width: 100%;
            height: 50px;
            padding: 0 16px 0 44px;
            border: 1.5px solid var(--lg-borde);
            border-radius: 14px;
            font-size: 14px;
            color: var(--lg-texto);
            background: #f7f9fc;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }
        .lg-body .form-control:hover { border-color: #c9d1de; }
        .lg-body .form-control:focus {
            outline: none;
            border-color: var(--lg-primario-2);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(42, 82, 152, .14);
        }
        .input-wrapper:focus-within .input-icon { color: var(--lg-primario); }
        .lg-body .form-control::placeholder { color: #a8b0bd; }

        /* Botón "ojito" para mostrar/ocultar la contraseña */
        .lg-body .form-control.con-toggle { padding-right: 50px; }
        /* Ocultar el ojito nativo de Edge para que no se duplique con el nuestro */
        .form-control.con-toggle::-ms-reveal,
        .form-control.con-toggle::-ms-clear { display: none; }
        .toggle-password {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: 0;
            border-radius: 10px;
            width: 36px;
            height: 36px;
            color: #9aa3b2;
            font-size: 15px;
            cursor: pointer;
        }
        .toggle-password:hover, .toggle-password:focus-visible { color: var(--lg-primario); outline: none; background: #eef2f9; }

        .remember-forgot { display: flex; justify-content: space-between; align-items: center; gap: 12px; }
        .remember-me { display: flex; align-items: center; gap: 8px; }
        .remember-me input[type="checkbox"] { width: 16px; height: 16px; margin: 0; cursor: pointer; accent-color: var(--lg-primario); }
        .remember-me label { font-size: 13px; color: var(--lg-texto-2); cursor: pointer; margin: 0; }
        .forgot-password { color: var(--lg-primario-2); text-decoration: none; font-size: 13px; font-weight: 600; }
        .forgot-password:hover { text-decoration: underline; color: var(--lg-primario); }

        .btn-login {
            position: relative;
            overflow: hidden;
            width: 100%;
            height: 52px;
            margin-top: 6px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: #fff;
            border: 0;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 12px 24px rgba(30, 60, 114, .3);
            transition: transform .2s, box-shadow .2s;
        }
        /* Reflejo que cruza el botón al pasar el mouse */
        .btn-login::after {
            content: '';
            position: absolute;
            top: 0;
            left: -60%;
            width: 40%;
            height: 100%;
            background: linear-gradient(100deg, transparent, rgba(255, 255, 255, .25), transparent);
            transform: skewX(-20deg);
            transition: left .6s ease;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 16px 30px rgba(30, 60, 114, .38); }
        .btn-login:hover::after { left: 120%; }
        .btn-login:focus-visible { outline: 3px solid rgba(42, 82, 152, .4); outline-offset: 2px; }
        .btn-login i { transition: transform .2s; }
        .btn-login:hover i { transform: translateX(3px); }
        .btn-login.loading { pointer-events: none; opacity: .9; }
        .btn-login.loading i { display: none; }
        .btn-login.loading::before {
            content: '';
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, .35);
            border-top-color: #fff;
            border-radius: 50%;
            animation: lg-giro .8s linear infinite;
        }
        @keyframes lg-giro { to { transform: rotate(360deg); } }

        /* Mensaje de error del controlador (alerta del tema) con estilo del login */
        .lg-tarjeta .alert {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 0;
            line-height: 1.4;
            padding: 12px 14px;
            margin: 0 0 20px;
            border-radius: 12px;
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: var(--lg-peligro);
            font-size: 13px;
            animation: lg-sacudir .4s ease;
        }
        /* El ícono del tema (flaticon) no carga en esta página: se reemplaza por el de Font Awesome */
        .lg-tarjeta .alert .alert-icon { padding: 0; margin: 0; }
        .lg-tarjeta .alert .alert-icon i { display: none; }
        .lg-tarjeta .alert .alert-icon::before {
            content: "\f06a";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            font-size: 15px;
            color: var(--lg-peligro);
        }
        .lg-tarjeta .alert .alert-text { flex: 1; padding: 0; color: var(--lg-peligro); font-weight: 500; }
        .lg-tarjeta .alert .alert-close { padding: 0; margin: 0; }
        .lg-tarjeta .alert .alert-close .close { color: var(--lg-peligro); opacity: .7; padding: 0; margin: 0; line-height: 1; }
        .lg-tarjeta .alert .alert-close .close i { display: none; }
        .lg-tarjeta .alert .alert-close .close span::before {
            content: "\f00d";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            font-size: 13px;
        }
        @keyframes lg-sacudir {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            50% { transform: translateX(5px); }
            75% { transform: translateX(-3px); }
        }

        .lg-pie {
            margin-top: 26px;
            padding-top: 18px;
            border-top: 1px solid #eef0f4;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            font-size: 12px;
            color: #9aa3b2;
        }
        .link-volver-inicio { color: var(--lg-primario-2); font-size: 13px; font-weight: 600; text-decoration: none; }
        .link-volver-inicio:hover { text-decoration: underline; color: var(--lg-primario); }

        .lg-direccion { position: fixed; left: 48px; bottom: 22px; z-index: 1; font-size: 12px; color: rgba(255, 255, 255, .45); }
        .lg-direccion i { margin-right: 6px; }

        @keyframes lg-aparecer {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: none; }
        }
        @keyframes lg-aparecer-tarjeta {
            from { opacity: 0; transform: translateY(30px) rotateX(8deg); }
            to { opacity: 1; transform: none; }
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
            background: rgba(5, 12, 30, .6);
            backdrop-filter: blur(4px);
        }
        .modal-olvido.abierto { display: flex; }
        .modal-olvido-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, .35);
            max-width: 400px;
            width: 100%;
            padding: 34px 28px 26px;
            text-align: center;
            animation: lg-aparecer .3s ease;
        }
        .modal-olvido-icono {
            width: 60px;
            height: 60px;
            margin: 0 auto 16px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: #fff;
            font-size: 24px;
            box-shadow: 0 10px 24px rgba(30, 60, 114, .3);
        }
        .modal-olvido-card h3 { color: var(--lg-texto); font-size: 19px; font-weight: 700; margin: 0 0 10px; }
        .modal-olvido-card p { color: var(--lg-texto-2); font-size: 14px; line-height: 1.6; margin: 0 0 22px; }
        .modal-olvido-card .btn-cerrar-olvido {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: #fff;
            border: 0;
            border-radius: 12px;
            padding: 12px 30px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }
        .modal-olvido-card .btn-cerrar-olvido:disabled { opacity: .6; cursor: wait; }
        .olvido-paso { display: flex; flex-direction: column; gap: 10px; }
        .olvido-paso[hidden] { display: none; }
        .olvido-paso p { margin-bottom: 6px !important; }
        .olvido-input {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #d8dde8;
            border-radius: 12px;
            font-size: 15px;
            outline: none;
        }
        .olvido-input:focus { border-color: #2a5298; box-shadow: 0 0 0 3px rgba(42, 82, 152, .15); }
        .olvido-codigo { text-align: center; font-size: 22px; font-weight: 700; letter-spacing: 8px; }
        .olvido-codigo::placeholder { font-size: 15px; font-weight: 400; letter-spacing: normal; }
        .olvido-ayuda { color: var(--lg-texto-2); font-size: 12px; text-align: left; }
        .olvido-error { color: #c62828; font-size: 13px; min-height: 0; }
        .olvido-error:empty { display: none; }
        .olvido-prueba {
            background: #fff8e1;
            border: 1px dashed #f0b400;
            border-radius: 10px;
            padding: 8px;
            font-size: 13px;
            color: #6d5200;
        }
        .olvido-link {
            background: none;
            border: 0;
            color: #2a5298;
            font-size: 13px;
            margin-top: 8px;
            cursor: pointer;
        }
        .olvido-link:hover { text-decoration: underline; }

        @media (max-width: 991.98px) {
            .lg-pantalla { grid-template-columns: 1fr; gap: 32px; padding: 32px 20px 56px; max-width: 520px; align-content: center; }
            .lg-intro { text-align: center; }
            .lg-marca { margin: 0 auto 24px; }
            .lg-eyebrow { justify-content: center; }
            .lg-intro h1 { font-size: 30px; margin-left: auto; margin-right: auto; }
            .lg-intro-texto, .lg-chips { display: none; }
            .lg-direccion { display: none; }
        }
        @media (max-width: 480px) {
            .lg-tarjeta { padding: 30px 22px 22px; border-radius: 20px; }
            .lg-intro h1 { font-size: 26px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .lg-intro, .lg-tarjeta-escena, .modal-olvido-card, .lg-tarjeta .alert { animation: none; }
            .lg-tarjeta { transition: none; }
        }
    </style>
</head>
<body class="lg-body">

    <canvas id="lgEscena" aria-hidden="true"></canvas>
    <div class="lg-vineta" aria-hidden="true"></div>

    <div class="lg-pantalla">
        <!-- Texto institucional -->
        <section class="lg-intro">
            <a href="inicio" class="lg-marca">
                <img src="vistas/recursos/assets/media/logos/logo.png" alt="Posgrado Odontología - Universidad Técnica de Oruro">
            </a>
            <span class="lg-eyebrow">Universidad Técnica de Oruro</span>
            <h1>Posgrado <span>Facultad de Ciencias de la Salud</span></h1>
            <p class="lg-intro-texto">
                Sistema académico de la Coordinación de Posgrado en Odontología:
                inscripciones, pagos, calificaciones y certificados en un solo lugar.
            </p>
            <div class="lg-chips">
                <span class="lg-chip"><i class="fas fa-graduation-cap"></i> Especialidades y maestrías</span>
                <span class="lg-chip"><i class="fas fa-file-invoice"></i> Órdenes de pago</span>
                <span class="lg-chip"><i class="fas fa-award"></i> Certificados en línea</span>
            </div>
        </section>

        <!-- Tarjeta de acceso -->
        <div class="lg-tarjeta-escena">
            <main class="lg-tarjeta" id="lgTarjeta">
                <div class="lg-icono-acceso"><i class="fas fa-user-lock"></i></div>
                <h2>Iniciar sesión</h2>
                <p class="lg-subtitulo">Ingresa tus credenciales para acceder al sistema</p>

                <?php
                // Valida el envío del formulario: si es correcto redirige al panel; si no,
                // imprime aquí (arriba del formulario) la alerta de usuario o contraseña incorrectos.
                $Ingreso = new IngresoControladores();
                $Ingreso->ValidarIngresoControlador();
                ?>

                <form class="login-form" method="POST" id="loginForm">
                    <div class="lg-campo">
                        <label for="usuario">Usuario</label>
                        <div class="input-wrapper">
                            <input
                                type="text"
                                class="form-control"
                                id="usuario"
                                name="IUsuario"
                                placeholder="Ingrese su usuario"
                                autocomplete="username"
                                required
                            >
                            <i class="input-icon fas fa-user"></i>
                        </div>
                    </div>

                    <div class="lg-campo">
                        <label for="password">Contraseña</label>
                        <div class="input-wrapper">
                            <input
                                type="password"
                                class="form-control con-toggle"
                                id="password"
                                name="IPassword"
                                placeholder="Ingrese su contraseña"
                                autocomplete="current-password"
                                required
                            >
                            <i class="input-icon fas fa-lock"></i>
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
                        <span>Ingresar</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>

                <div class="lg-pie">
                    <a href="inicio" class="link-volver-inicio"><i class="fas fa-arrow-left"></i> Volver al inicio</a>
                    <span>&copy; <?php echo date('Y'); ?> Universidad Técnica de Oruro</span>
                </div>
            </main>
        </div>
    </div>

    <div class="lg-direccion"><i class="fas fa-map-marker-alt"></i>Calle Presidente Montes entre Murguía y Aldana, Edificio UTO 2do Piso · Telf: 5237317 · Oruro - Bolivia</div>

    <!-- Modal "¿Olvidó su contraseña?": recuperación con un código enviado al correo
         registrado (ajax/recuperarpassword.ajax.php). Paso 1: usuario o C.I.;
         paso 2: código + contraseña nueva; paso 3: confirmación. -->
    <div class="modal-olvido" id="modalOlvidoPassword" role="dialog" aria-modal="true" aria-labelledby="tituloOlvidoPassword">
        <div class="modal-olvido-card">
            <div class="modal-olvido-icono"><i class="fas fa-key"></i></div>
            <h3 id="tituloOlvidoPassword">¿Olvidó su contraseña?</h3>

            <form id="olvidoPaso1" class="olvido-paso" novalidate>
                <p>Escriba su usuario o C.I. y le enviaremos un código a su <strong>correo registrado</strong>.</p>
                <input type="text" id="olvidoIdentificador" class="olvido-input" placeholder="Usuario o C.I." autocomplete="username" required>
                <div class="olvido-error" id="olvidoError1"></div>
                <button type="submit" class="btn-cerrar-olvido" id="btnOlvidoEnviar">Enviar código</button>
            </form>

            <form id="olvidoPaso2" class="olvido-paso" novalidate hidden>
                <p id="olvidoMensaje2"></p>
                <div class="olvido-prueba" id="olvidoPrueba" hidden></div>
                <input type="text" id="olvidoCodigo" class="olvido-input olvido-codigo" placeholder="Código de 6 dígitos" inputmode="numeric" maxlength="6" autocomplete="one-time-code" required>
                <input type="password" id="olvidoPassword" class="olvido-input" placeholder="Nueva contraseña" autocomplete="new-password" required>
                <input type="password" id="olvidoConfirmar" class="olvido-input" placeholder="Confirmar contraseña" autocomplete="new-password" required>
                <small class="olvido-ayuda">Mínimo 8 caracteres, con al menos una letra y un número</small>
                <div class="olvido-error" id="olvidoError2"></div>
                <button type="submit" class="btn-cerrar-olvido" id="btnOlvidoCambiar">Cambiar contraseña</button>
                <button type="button" class="olvido-link" id="btnOlvidoReenviar">Solicitar otro código</button>
            </form>

            <div id="olvidoPaso3" class="olvido-paso" hidden>
                <p id="olvidoMensaje3"></p>
                <button type="button" class="btn-cerrar-olvido" id="btnOlvidoListo">Ir a ingresar</button>
            </div>

            <button type="button" class="olvido-link" id="btnCerrarOlvido">Cancelar</button>
        </div>
    </div>

    <script>
        // Estado de carga en el botón al enviar
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('btnLogin');
            btn.classList.add('loading');
            btn.querySelector('span').textContent = 'Ingresando...';
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

        // Modal "¿Olvidó su contraseña?" (recuperación por correo)
        const modalOlvido = document.getElementById('modalOlvidoPassword');

        function mostrarPasoOlvido(numero) {
            [1, 2, 3].forEach(function(n) {
                document.getElementById('olvidoPaso' + n).hidden = n !== numero;
            });
            document.getElementById('btnCerrarOlvido').hidden = numero === 3;
        }

        function cerrarModalOlvido() {
            modalOlvido.classList.remove('abierto');
        }

        function abrirModalOlvido() {
            ['olvidoCodigo', 'olvidoPassword', 'olvidoConfirmar'].forEach(function(id) {
                document.getElementById(id).value = '';
            });
            document.getElementById('olvidoError1').textContent = '';
            document.getElementById('olvidoError2').textContent = '';
            // Si ya escribió su usuario en el login, se aprovecha
            const usuarioLogin = document.getElementById('usuario').value.trim();
            if (usuarioLogin) document.getElementById('olvidoIdentificador').value = usuarioLogin;
            mostrarPasoOlvido(1);
            modalOlvido.classList.add('abierto');
            document.getElementById('olvidoIdentificador').focus();
        }

        function enviarOlvido(datos) {
            const cuerpo = new FormData();
            Object.keys(datos).forEach(function(k) { cuerpo.append(k, datos[k]); });
            return fetch('ajax/recuperarpassword.ajax.php', { method: 'POST', body: cuerpo })
                .then(function(r) { return r.json(); })
                .catch(function() { return { success: false, mensaje: 'No se pudo conectar con el servidor' }; });
        }

        function solicitarCodigoOlvido() {
            const identificador = document.getElementById('olvidoIdentificador').value.trim();
            const error = document.getElementById('olvidoError1');
            if (!identificador) {
                error.textContent = 'Ingrese su usuario o C.I.';
                return;
            }
            const btn = document.getElementById('btnOlvidoEnviar');
            btn.disabled = true;
            btn.textContent = 'Enviando...';
            error.textContent = '';

            enviarOlvido({ accion: 'solicitarCodigo', identificador: identificador }).then(function(resp) {
                btn.disabled = false;
                btn.textContent = 'Enviar código';
                if (!resp.success) {
                    error.textContent = resp.mensaje;
                    return;
                }
                document.getElementById('olvidoMensaje2').textContent = resp.mensaje;
                const prueba = document.getElementById('olvidoPrueba');
                prueba.hidden = !resp.codigoPrueba;
                prueba.textContent = resp.codigoPrueba ? 'Modo prueba (no se envió correo). Código: ' + resp.codigoPrueba : '';
                mostrarPasoOlvido(2);
                document.getElementById('olvidoCodigo').focus();
            });
        }

        document.getElementById('linkOlvidoPassword').addEventListener('click', function(e) {
            e.preventDefault();
            abrirModalOlvido();
        });

        document.getElementById('olvidoPaso1').addEventListener('submit', function(e) {
            e.preventDefault();
            solicitarCodigoOlvido();
        });

        document.getElementById('btnOlvidoReenviar').addEventListener('click', function() {
            mostrarPasoOlvido(1);
            solicitarCodigoOlvido();
        });

        document.getElementById('olvidoCodigo').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 6);
        });

        document.getElementById('olvidoPaso2').addEventListener('submit', function(e) {
            e.preventDefault();
            const codigo = document.getElementById('olvidoCodigo').value.trim();
            const password = document.getElementById('olvidoPassword').value;
            const confirmacion = document.getElementById('olvidoConfirmar').value;
            const error = document.getElementById('olvidoError2');

            // Misma regla que el servidor (UsuarioControladores::ValidarPasswordPersonal)
            if (!/^\d{6}$/.test(codigo)) { error.textContent = 'El código debe tener 6 dígitos'; return; }
            if (password.length < 8) { error.textContent = 'La contraseña debe tener al menos 8 caracteres'; return; }
            if (!/[A-Za-z]/.test(password) || !/[0-9]/.test(password)) { error.textContent = 'La contraseña debe tener al menos una letra y un número'; return; }
            if (password !== confirmacion) { error.textContent = 'Las contraseñas no coinciden'; return; }

            const btn = document.getElementById('btnOlvidoCambiar');
            btn.disabled = true;
            btn.textContent = 'Guardando...';
            error.textContent = '';

            enviarOlvido({
                accion: 'cambiarPassword',
                identificador: document.getElementById('olvidoIdentificador').value.trim(),
                codigo: codigo,
                password: password,
                confirmacion: confirmacion
            }).then(function(resp) {
                btn.disabled = false;
                btn.textContent = 'Cambiar contraseña';
                if (!resp.success) {
                    error.textContent = resp.mensaje;
                    return;
                }
                document.getElementById('olvidoMensaje3').textContent = resp.mensaje;
                mostrarPasoOlvido(3);
            });
        });

        document.getElementById('btnOlvidoListo').addEventListener('click', function() {
            cerrarModalOlvido();
            document.getElementById('usuario').value = document.getElementById('olvidoIdentificador').value.trim();
            document.getElementById('password').value = '';
            document.getElementById('password').focus();
        });

        document.getElementById('btnCerrarOlvido').addEventListener('click', cerrarModalOlvido);

        // Cerrar al hacer clic fuera de la tarjeta o con Escape
        modalOlvido.addEventListener('click', function(e) {
            if (e.target === modalOlvido) cerrarModalOlvido();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') cerrarModalOlvido();
        });

        // Inclinación 3D de la tarjeta según la posición del mouse (solo escritorio)
        (function () {
            const tarjeta = document.getElementById('lgTarjeta');
            const reducir = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const puntero = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
            if (!tarjeta || reducir || !puntero) return;

            tarjeta.addEventListener('mousemove', function (e) {
                const r = tarjeta.getBoundingClientRect();
                const x = (e.clientX - r.left) / r.width;
                const y = (e.clientY - r.top) / r.height;
                tarjeta.style.transform = 'rotateY(' + ((x - .5) * 8).toFixed(2) + 'deg) rotateX(' + ((.5 - y) * 8).toFixed(2) + 'deg)';
                tarjeta.style.setProperty('--brillo-x', (x * 100).toFixed(1) + '%');
                tarjeta.style.setProperty('--brillo-y', (y * 100).toFixed(1) + '%');
            });
            tarjeta.addEventListener('mouseleave', function () {
                tarjeta.style.transform = '';
            });
        })();
    </script>

    <!-- Escena 3D de fondo. Si three.js no carga (sin internet o sin WebGL) queda el degradado. -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script>
        (function () {
            const lienzo = document.getElementById('lgEscena');
            if (!lienzo || typeof THREE === 'undefined') return;

            let renderer;
            try {
                renderer = new THREE.WebGLRenderer({ canvas: lienzo, antialias: true, alpha: true });
            } catch (e) {
                return;
            }

            const reducir = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const movil = window.innerWidth < 992;
            renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.5));

            const escena = new THREE.Scene();
            const camara = new THREE.PerspectiveCamera(45, 1, 0.1, 100);
            camara.position.z = 9;

            // Grupo principal: esfera de puntos conectados + anillos orbitales
            const grupo = new THREE.Group();
            escena.add(grupo);

            // Puntos distribuidos uniformemente sobre una esfera (espiral de Fibonacci)
            const total = movil ? 260 : 420;
            const radio = 3;
            const posiciones = new Float32Array(total * 3);
            const vectores = [];
            const oro = Math.PI * (3 - Math.sqrt(5));
            for (let i = 0; i < total; i++) {
                const y = 1 - (i / (total - 1)) * 2;
                const r = Math.sqrt(1 - y * y);
                const t = oro * i;
                const v = new THREE.Vector3(Math.cos(t) * r * radio, y * radio, Math.sin(t) * r * radio);
                vectores.push(v);
                posiciones.set([v.x, v.y, v.z], i * 3);
            }

            const geoPuntos = new THREE.BufferGeometry();
            geoPuntos.setAttribute('position', new THREE.BufferAttribute(posiciones, 3));
            const puntos = new THREE.Points(geoPuntos, new THREE.PointsMaterial({
                color: 0xbad3ff,
                size: 0.055,
                transparent: true,
                opacity: 0.95,
                blending: THREE.AdditiveBlending,
                depthWrite: false
            }));
            grupo.add(puntos);

            // Líneas entre puntos vecinos (red)
            const umbral = radio * (movil ? 0.3 : 0.24);
            const lineas = [];
            for (let i = 0; i < total; i++) {
                for (let j = i + 1; j < total; j++) {
                    if (vectores[i].distanceTo(vectores[j]) < umbral) {
                        lineas.push(vectores[i].x, vectores[i].y, vectores[i].z, vectores[j].x, vectores[j].y, vectores[j].z);
                    }
                }
            }
            const geoLineas = new THREE.BufferGeometry();
            geoLineas.setAttribute('position', new THREE.Float32BufferAttribute(lineas, 3));
            grupo.add(new THREE.LineSegments(geoLineas, new THREE.LineBasicMaterial({
                color: 0x7fb2ff,
                transparent: true,
                opacity: 0.18,
                blending: THREE.AdditiveBlending,
                depthWrite: false
            })));

            // Núcleo luminoso tenue
            grupo.add(new THREE.Mesh(
                new THREE.SphereGeometry(radio * 0.96, 32, 32),
                new THREE.MeshBasicMaterial({ color: 0x2a5298, transparent: true, opacity: 0.12 })
            ));

            // Anillos orbitales inclinados
            function anillo(radioAnillo, inclinacionX, inclinacionY, opacidad) {
                const m = new THREE.Mesh(
                    new THREE.TorusGeometry(radioAnillo, 0.008, 8, 160),
                    new THREE.MeshBasicMaterial({ color: 0x9fc2ff, transparent: true, opacity: opacidad })
                );
                m.rotation.x = inclinacionX;
                m.rotation.y = inclinacionY;
                return m;
            }
            const anillo1 = anillo(radio * 1.35, 1.25, 0.3, 0.35);
            const anillo2 = anillo(radio * 1.6, 1.0, -0.6, 0.18);
            grupo.add(anillo1, anillo2);

            // Satélite que recorre el primer anillo
            const satelite = new THREE.Mesh(new THREE.SphereGeometry(0.07, 16, 16), new THREE.MeshBasicMaterial({ color: 0xffffff }));
            anillo1.add(satelite);

            // Polvo estelar de fondo
            const totalPolvo = movil ? 250 : 500;
            const polvo = new Float32Array(totalPolvo * 3);
            for (let i = 0; i < totalPolvo; i++) {
                polvo.set([(Math.random() - .5) * 40, (Math.random() - .5) * 24, -Math.random() * 20 - 2], i * 3);
            }
            const geoPolvo = new THREE.BufferGeometry();
            geoPolvo.setAttribute('position', new THREE.BufferAttribute(polvo, 3));
            const estrellas = new THREE.Points(geoPolvo, new THREE.PointsMaterial({ color: 0xffffff, size: 0.04, transparent: true, opacity: 0.5, depthWrite: false }));
            escena.add(estrellas);

            // En escritorio la esfera queda detrás de la tarjeta (asoma alrededor del formulario
            // y deja el texto sobre fondo limpio); en móvil, arriba y más pequeña
            function ajustar() {
                const w = window.innerWidth, h = window.innerHeight;
                renderer.setSize(w, h, false);
                camara.aspect = w / h;
                camara.updateProjectionMatrix();
                const esMovil = w < 992;
                grupo.position.set(esMovil ? 0 : 3.3, esMovil ? 2.6 : 0, esMovil ? 0 : -1.5);
                const escala = esMovil ? 0.75 : 1.2;
                grupo.scale.set(escala, escala, escala);
            }
            ajustar();
            window.addEventListener('resize', ajustar);

            // El mouse inclina suavemente la escena
            let objetivoX = 0, objetivoY = 0;
            window.addEventListener('mousemove', function (e) {
                objetivoX = (e.clientX / window.innerWidth - .5);
                objetivoY = (e.clientY / window.innerHeight - .5);
            });

            let anguloSatelite = 0;
            function dibujar() {
                grupo.rotation.y += 0.0016;
                grupo.rotation.x += (objetivoY * 0.35 - grupo.rotation.x) * 0.04;
                grupo.rotation.z += (-objetivoX * 0.15 - grupo.rotation.z) * 0.04;
                anillo1.rotation.z += 0.002;
                anillo2.rotation.z -= 0.0012;
                anguloSatelite += 0.012;
                satelite.position.set(Math.cos(anguloSatelite) * radio * 1.35, Math.sin(anguloSatelite) * radio * 1.35, 0);
                estrellas.rotation.y += 0.0002;
                renderer.render(escena, camara);
            }

            if (reducir) {
                dibujar();
                return;
            }

            // Pausar cuando la pestaña no está visible (ahorra batería/CPU)
            let activo = true;
            document.addEventListener('visibilitychange', function () {
                activo = !document.hidden;
                if (activo) requestAnimationFrame(bucle);
            });
            function bucle() {
                if (!activo) return;
                dibujar();
                requestAnimationFrame(bucle);
            }
            requestAnimationFrame(bucle);
        })();
    </script>
</body>
</html>
