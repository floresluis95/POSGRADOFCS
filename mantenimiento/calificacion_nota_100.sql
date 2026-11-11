-- calificacion.Nota era DECIMAL(4,2): el máximo era 99.99. Una nota de 100 se guardaba como
-- 99.99 sin aviso (XAMPP) o daba error (MySQL estricto en el servidor). Con DECIMAL(5,2) cabe 100.
-- Solo ensancha la columna: las notas ya guardadas no cambian.
ALTER TABLE calificacion MODIFY Nota DECIMAL(5,2) NULL DEFAULT NULL;
