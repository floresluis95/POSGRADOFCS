-- Script para modificar la columna DocenteID y permitir valores NULL
-- Esto permite que los módulos puedan registrarse sin un docente asignado

ALTER TABLE `modulos`
MODIFY COLUMN `DocenteID` int(11) NULL DEFAULT NULL;

-- Verificar la modificación
DESCRIBE modulos;
