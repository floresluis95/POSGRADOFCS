<?php
    require_once 'conexion.modelo.php';
    class HeredadoModelos
    {
        // Método privado para validar nombres de tablas y columnas
        private static function ValidarNombre($nombre)
        {
            // Solo permite letras, números y guiones bajos
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $nombre)) {
                throw new Exception("Nombre de tabla o columna inválido");
            }
            return $nombre;
        }

        public static function UltimoIdModelo($item, $tabla)
        {
            // Validar nombres de tabla y columna
            $item = self::ValidarNombre($item);
            $tabla = self::ValidarNombre($tabla);

            // Usar backticks para nombres de tablas/columnas
            $stmt = Conexion::Conectar()->prepare("SELECT MAX(`$item`) AS UltimoId FROM `$tabla`");
            $stmt -> setFetchMode(PDO::FETCH_ASSOC);
            $stmt -> execute();
            while ($row = $stmt -> fetch())
            {
                return $UltimoId = $row['UltimoId'];
            }
        }

        public static function TraerCampoModelo($item, $tabla, $id, $value)
        {
            // Validar nombres de tabla y columnas
            $item = self::ValidarNombre($item);
            $tabla = self::ValidarNombre($tabla);
            $id = self::ValidarNombre($id);

            // Usar parámetros preparados para el valor
            $stmt = Conexion::Conectar()->prepare("SELECT `$item` AS item FROM `$tabla` WHERE `$id` = :value");
            $stmt -> bindParam(":value", $value, PDO::PARAM_STR);
            $stmt -> setFetchMode(PDO::FETCH_ASSOC);
            $stmt -> execute();
            while ($row = $stmt -> fetch())
            {
                return $row['item'];
            }
        }
    }