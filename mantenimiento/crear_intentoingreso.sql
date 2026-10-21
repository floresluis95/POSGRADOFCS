-- Límite de intentos fallidos de ingreso (2026-10-21). Paso 25 del LEEME.
-- Cada contraseña incorrecta deja una fila; con 5 fallos en 15 minutos ese usuario
-- queda bloqueado hasta que pasen los 15 minutos. Al ingresar bien se borran sus filas,
-- y las de más de un día se borran solas.
-- Mientras la tabla no exista, el login funciona igual pero sin el límite.
CREATE TABLE IF NOT EXISTS intentoingreso (
    IdIntento INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Usuario VARCHAR(100) NOT NULL,
    IP VARCHAR(45) NOT NULL DEFAULT '',
    Fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_usuario_fecha (Usuario, Fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
