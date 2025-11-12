<?php
    class Conexion
    {
        public static function Conectar()
        {
            try {
                $link = new PDO("mysql:host=localhost;dbname=proyecto", "root", "");
                $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $link->exec("set names utf8");
                return $link;
            } catch (PDOException $e) {
                die("Error de conexión: " . $e->getMessage());
            }
        }
    }