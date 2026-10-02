-- Requisitos por grado académico (2026-10-02)
-- Ejecutar DESPUÉS de ampliar_requisitos.sql.
--
-- `requisitogrado` dice a qué grados (DIPLOMADO, ESPECIALIDAD, MAESTRIA,
-- DOCTORADO) se le pide cada requisito. Un requisito SIN filas aquí se pide en
-- todos los grados, así los requisitos que ya existían no cambian. El grado de
-- cada matriculado sale de su programa (programa.GradoAcademico).

CREATE TABLE IF NOT EXISTS requisitogrado (
    IdRequisito INT(11) NOT NULL,
    GradoAcademico VARCHAR(20) NOT NULL,
    PRIMARY KEY (IdRequisito, GradoAcademico),
    CONSTRAINT fk_reqgrado_requisito FOREIGN KEY (IdRequisito) REFERENCES requisito (IdRequisito) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
