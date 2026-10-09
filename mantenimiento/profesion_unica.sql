-- Profesión sin repetidos (2026-10-09)
-- NombreProfesion era VARCHAR(25) y la base no está en modo estricto: un nombre más largo
-- se cortaba en silencio al guardarlo (p. ej. "LICENCIADO EN ODONTOLOGIA PEDIATRICA" quedaba
-- como "LICENCIADO EN ODONTOLOGIA", que ya existía) y la profesión quedaba repetida.
-- Se amplía a 100 (lo que ya permiten los formularios) y se agrega un índice único, como
-- el que ya tiene universidad.NombreUniversidad. El sistema ya lo valida al registrar;
-- el índice es la última barrera (dos registros a la vez, etc.).

-- 1) Revisar que no haya repetidos (debe devolver 0 filas; si no, corregirlos antes)
SELECT NombreProfesion, COUNT(*) AS veces FROM profesion GROUP BY NombreProfesion HAVING COUNT(*) > 1;

-- 2) Ampliar el nombre e índice único
ALTER TABLE profesion
    MODIFY NombreProfesion VARCHAR(100) NOT NULL,
    ADD UNIQUE KEY uq_profesion_nombre (NombreProfesion);
