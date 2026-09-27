-- El nombre del módulo se cortaba en 100 caracteres (VARCHAR(100)) y el texto
-- truncado aparecía en el historial académico y en los certificados.
ALTER TABLE modulos MODIFY nombremodulo VARCHAR(255) NOT NULL;
