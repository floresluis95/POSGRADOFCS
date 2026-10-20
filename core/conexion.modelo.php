<?php
    // Hora de Bolivia para todo el sistema (también los ajax). El php.ini de XAMPP trae
    // Europe/Berlin: de 18:00 a 24:00 date() ya devolvía el día siguiente.
    date_default_timezone_set('America/La_Paz');

    class Conexion
    {
        public static function Conectar()
        {
            // Datos del servidor en config/basedatos.php (fuera de git; plantilla en
            // config/basedatos.ejemplo.php). Sin ese archivo: XAMPP local, root sin contraseña.
            $config = self::Configuracion();
            try {
                // POSGRADO_BD solo la definen las pruebas (copia proyecto_pruebas)
                $baseDatos = getenv('POSGRADO_BD') ?: $config['base'];
                $link = new PDO("mysql:host=" . $config['host'] . ";dbname=" . $baseDatos, $config['usuario'], $config['password']);
                $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $link->exec("set names utf8");
                return $link;
            } catch (PDOException $e) {
                // El detalle (usuario, servidor) va al log de PHP, no a la pantalla
                error_log("Error de conexión a la base de datos: " . $e->getMessage());
                http_response_code(500);
                die("No se pudo conectar con la base de datos. Intente nuevamente en unos minutos.");
            }
        }

        private static function Configuracion()
        {
            static $config = null;
            if ($config === null) {
                $local = ['host' => 'localhost', 'base' => 'proyecto', 'usuario' => 'root', 'password' => ''];
                $archivo = __DIR__ . '/../config/basedatos.php';
                $config = is_file($archivo) ? array_merge($local, (array) require $archivo) : $local;
            }
            return $config;
        }
    }
