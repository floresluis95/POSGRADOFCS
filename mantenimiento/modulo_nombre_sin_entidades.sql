-- =============================================================================
-- Nombres de módulo con comillas guardados como "&quot;" (2026-11-24)
--
-- Hasta ahora el sistema guardaba el nombre y el código del módulo pasados por
-- htmlspecialchars: una comilla quedaba "&quot;" (o "&#039;"), un "&" quedaba
-- "&amp;", etc. En las pantallas no se notaba, pero los PDF (Situación académica,
-- reportes) imprimían el texto tal cual: SEMINARIO &quot;TALLER&quot;.
-- Desde este cambio se guarda el texto tal cual y cada pantalla lo escapa al mostrarlo.
--
-- Este script devuelve a su carácter los nombres y códigos ya guardados.
-- "&amp;" va al final de cada pasada. Se repite 3 veces porque un módulo con comillas
-- que se volvió a editar quedó codificado dos veces ("&amp;quot;"): cada pasada quita una capa.
-- Se puede volver a ejecutar sin problema. Ejecutar con respaldo previo.
-- =============================================================================

-- Pasada 1
UPDATE modulos
SET nombremodulo = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(nombremodulo,
        '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&lt;', '<'), '&gt;', '>'), '&amp;', '&')
WHERE nombremodulo REGEXP '&(quot|#0?39|lt|gt|amp);';

UPDATE modulos
SET codigomodulo = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(codigomodulo,
        '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&lt;', '<'), '&gt;', '>'), '&amp;', '&')
WHERE codigomodulo REGEXP '&(quot|#0?39|lt|gt|amp);';

-- Pasada 2
UPDATE modulos
SET nombremodulo = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(nombremodulo,
        '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&lt;', '<'), '&gt;', '>'), '&amp;', '&')
WHERE nombremodulo REGEXP '&(quot|#0?39|lt|gt|amp);';

UPDATE modulos
SET codigomodulo = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(codigomodulo,
        '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&lt;', '<'), '&gt;', '>'), '&amp;', '&')
WHERE codigomodulo REGEXP '&(quot|#0?39|lt|gt|amp);';

-- Pasada 3
UPDATE modulos
SET nombremodulo = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(nombremodulo,
        '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&lt;', '<'), '&gt;', '>'), '&amp;', '&')
WHERE nombremodulo REGEXP '&(quot|#0?39|lt|gt|amp);';

UPDATE modulos
SET codigomodulo = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(codigomodulo,
        '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&lt;', '<'), '&gt;', '>'), '&amp;', '&')
WHERE codigomodulo REGEXP '&(quot|#0?39|lt|gt|amp);';


-- Comprobación: debe devolver 0
SELECT COUNT(*) AS pendientes FROM modulos
WHERE nombremodulo REGEXP '&(quot|#0?39|lt|gt|amp);' OR codigomodulo REGEXP '&(quot|#0?39|lt|gt|amp);';
