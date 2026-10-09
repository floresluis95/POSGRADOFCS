-- Datos del estudiante en MAYÚSCULAS, también las letras con tilde (2026-10-09)
-- PHP strtoupper() no convierte las letras con tilde: "Potosí" quedaba guardado como
-- "POTOSí" y "José" como "JOSé". El sistema ya usa mb_strtoupper(); esto corrige lo
-- guardado antes. El correo NO se toca (va en minúsculas).
-- El select "Lugar de nacimiento" ahora usa el valor "POTOSÍ".

-- 1) Ver qué filas cambian
SELECT EstudianteID, Nombre, Apaterno, Amaterno, Lugarn, Trabajo, Direccion, Complemento, Exp
FROM estudiante
WHERE BINARY CONCAT_WS('|', Nombre, Apaterno, Amaterno, Lugarn, Trabajo, Direccion, Complemento, Exp)
   <> BINARY UPPER(CONCAT_WS('|', Nombre, Apaterno, Amaterno, Lugarn, Trabajo, Direccion, Complemento, Exp));

-- 2) Corregir
UPDATE estudiante SET
    Nombre      = UPPER(Nombre),
    Apaterno    = UPPER(Apaterno),
    Amaterno    = UPPER(Amaterno),
    Lugarn      = UPPER(Lugarn),
    Trabajo     = UPPER(Trabajo),
    Direccion   = UPPER(Direccion),
    Complemento = UPPER(Complemento),
    Exp         = UPPER(Exp)
WHERE BINARY CONCAT_WS('|', Nombre, Apaterno, Amaterno, Lugarn, Trabajo, Direccion, Complemento, Exp)
   <> BINARY UPPER(CONCAT_WS('|', Nombre, Apaterno, Amaterno, Lugarn, Trabajo, Direccion, Complemento, Exp));
