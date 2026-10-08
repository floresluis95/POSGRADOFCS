-- Nombre de usuario único (2026-10-08)
-- Con el nombre de usuario se inicia sesión: si dos cuentas lo comparten, el login
-- solo encuentra a la primera y la segunda nunca puede entrar. El sistema ya lo valida
-- al crear usuarios; este índice es la última barrera (dos registros a la vez, etc.).
-- La misma persona puede tener varias cuentas (personal y estudiante), pero cada
-- cuenta con un nombre de usuario distinto (al estudiante se le agrega "E" si su C.I.
-- ya está en uso, al docente "D").

-- 1) Revisar que no haya repetidos (debe devolver 0 filas; si no, corregirlos antes)
SELECT Usuario, COUNT(*) AS veces FROM usuario GROUP BY Usuario HAVING COUNT(*) > 1;

-- 2) Índice único
ALTER TABLE usuario ADD UNIQUE KEY uq_usuario_usuario (Usuario);
