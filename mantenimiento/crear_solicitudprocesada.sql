-- Control de envíos repetidos (corte de internet). Paso 23 del LEEME.
-- Cada envío POST a ajax/ llega con un identificador propio (cabecera X-Solicitud-Id).
-- Si la conexión se corta después de guardar y el usuario vuelve a enviar lo mismo,
-- el servidor devuelve la respuesta guardada en vez de registrar todo otra vez.
-- Las filas se borran solas después de 2 días.
CREATE TABLE IF NOT EXISTS solicitudprocesada (
    IdSolicitud CHAR(36) NOT NULL PRIMARY KEY,
    Url VARCHAR(255) NOT NULL,
    Estado ENUM('PROCESANDO', 'TERMINADA') NOT NULL DEFAULT 'PROCESANDO',
    CodigoHttp SMALLINT DEFAULT NULL,
    TipoContenido VARCHAR(100) DEFAULT NULL,
    Respuesta MEDIUMTEXT DEFAULT NULL,
    Fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_fecha (Fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
