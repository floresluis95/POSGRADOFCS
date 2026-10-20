<?php
/**
 * Conexión a la base de datos.
 * Copiar este archivo como config/basedatos.php y completar los datos del servidor.
 * config/basedatos.php NO se sube a git (tiene la contraseña de la base).
 *
 * Si config/basedatos.php no existe, el sistema usa la conexión local de XAMPP
 * (localhost, usuario root sin contraseña, base "proyecto").
 *
 * En producción crear en MariaDB un usuario propio del sistema (no usar root):
 *   CREATE USER 'posgrado'@'localhost' IDENTIFIED BY 'una-contraseña-larga';
 *   GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, LOCK TABLES
 *     ON proyecto.* TO 'posgrado'@'localhost';
 */
return [
    'host' => 'localhost',
    'base' => 'proyecto',
    'usuario' => 'posgrado',
    'password' => 'una-contraseña-larga',
];
