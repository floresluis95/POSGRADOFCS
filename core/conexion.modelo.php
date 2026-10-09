<?php
    // Hora de Bolivia para todo el sistema (también los ajax). El php.ini de XAMPP trae
    // Europe/Berlin: de 18:00 a 24:00 date() ya devolvía el día siguiente.
    date_default_timezone_set('America/La_Paz');

    class Conexion
    {
        public static function Conectar()
        {
            try {
                // POSGRADO_BD solo la definen las pruebas PHPUnit (copia proyecto_pruebas);
                // el sistema siempre usa la base "proyecto".
                $baseDatos = getenv('POSGRADO_BD') ?: 'proyecto';
                $link = new PDO("mysql:host=localhost;dbname=" . $baseDatos, "root", "");
                $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $link->exec("set names utf8");
                return $link;
            } catch (PDOException $e) {
                die("Error de conexión: " . $e->getMessage());
            }
        }
    }
