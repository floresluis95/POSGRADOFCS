<?php
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
