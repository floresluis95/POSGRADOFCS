-- ==============================================================
-- SCRIPT: MEJORAS PARA TABLA USUARIO Y RELACIÓN CON ESTUDIANTES
-- ==============================================================
-- Ejecuta este script en tu base de datos para aplicar las mejoras
-- NOTA: Estas mejoras son OPCIONALES, tu sistema ya funciona sin ellas
-- ==============================================================

USE proyecto;

-- --------------------------------------------------------------
-- PASO 1: CREAR ÍNDICES PARA MEJORAR RENDIMIENTO
-- --------------------------------------------------------------

-- Verificar si el índice ya existe antes de crearlo
SET @exist_idx_tipo := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = 'proyecto'
    AND table_name = 'usuario'
    AND index_name = 'idx_usuario_tipo'
);

SET @sql_idx_tipo := IF(
    @exist_idx_tipo = 0,
    'CREATE INDEX idx_usuario_tipo ON usuario(Tipo)',
    'SELECT "Índice idx_usuario_tipo ya existe" AS Info'
);

PREPARE stmt FROM @sql_idx_tipo;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Índice para Estado
SET @exist_idx_estado := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = 'proyecto'
    AND table_name = 'usuario'
    AND index_name = 'idx_usuario_estado'
);

SET @sql_idx_estado := IF(
    @exist_idx_estado = 0,
    'CREATE INDEX idx_usuario_estado ON usuario(Estado)',
    'SELECT "Índice idx_usuario_estado ya existe" AS Info'
);

PREPARE stmt FROM @sql_idx_estado;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Índice en tabla estudiante
SET @exist_idx_ci := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = 'proyecto'
    AND table_name = 'estudiante'
    AND index_name = 'idx_estudiante_ci'
);

SET @sql_idx_ci := IF(
    @exist_idx_ci = 0,
    'CREATE INDEX idx_estudiante_ci ON estudiante(Ci)',
    'SELECT "Índice idx_estudiante_ci ya existe" AS Info'
);

PREPARE stmt FROM @sql_idx_ci;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT '✅ PASO 1 COMPLETADO: Índices creados o verificados' AS Resultado;

-- --------------------------------------------------------------
-- PASO 2: CREAR VISTA SIMPLE PARA CONSULTAS FÁCILES
-- --------------------------------------------------------------

CREATE OR REPLACE VIEW vista_usuarios_simple AS
SELECT
    u.IdPersonal,
    u.Usuario,
    u.FechaIngreso,
    u.Estado,
    u.Tipo,
    CASE
        WHEN u.Tipo = 'EST' THEN CONCAT(
            e.Ci,
            CASE WHEN e.Complemento != '' THEN CONCAT('-', e.Complemento) ELSE '' END,
            ' ', e.Exp
        )
        ELSE p.CedulaIdentidad
    END AS CI,
    CASE
        WHEN u.Tipo = 'EST' THEN CONCAT(e.Apaterno, ' ', e.Amaterno, ' ', e.Nombre)
        ELSE CONCAT(p.ApellidoPaterno, ' ', p.ApellidoMaterno, ' ', p.Nombres)
    END AS NombreCompleto,
    CASE
        WHEN u.Tipo = 'EST' THEN e.Correo
        ELSE NULL
    END AS Correo,
    CASE
        WHEN u.Tipo = 'ADM' THEN 'Administrador'
        WHEN u.Tipo = 'SEC' THEN 'Secretaria'
        WHEN u.Tipo = 'DOC' THEN 'Docente'
        WHEN u.Tipo = 'TEC' THEN 'Técnico'
        WHEN u.Tipo = 'EST' THEN 'Estudiante'
        ELSE u.Tipo
    END AS TipoDescripcion
FROM usuario u
LEFT JOIN personal p ON u.IdPersonal = p.IdPersonal AND u.Tipo IN ('ADM', 'SEC', 'DOC', 'TEC')
LEFT JOIN estudiante e ON u.IdPersonal = e.Ci AND u.Tipo = 'EST'
WHERE u.Estado = '1';

SELECT '✅ PASO 2 COMPLETADO: Vista vista_usuarios_simple creada' AS Resultado;

-- --------------------------------------------------------------
-- PASO 3: CREAR VISTA COMPLETA CON TODOS LOS CAMPOS
-- --------------------------------------------------------------

CREATE OR REPLACE VIEW vista_usuarios_completo AS
SELECT
    u.IdPersonal,
    u.Usuario,
    u.Password,
    u.FechaIngreso,
    u.Estado AS EstadoUsuario,
    u.Tipo,
    -- Datos de Personal
    p.CedulaIdentidad AS CI_Personal,
    p.ApellidoPaterno AS ApellidoPaterno_Personal,
    p.ApellidoMaterno AS ApellidoMaterno_Personal,
    p.Nombres AS Nombres_Personal,
    p.Direccion AS Direccion_Personal,
    p.Celular AS Celular_Personal,
    p.Telefono AS Telefono_Personal,
    -- Datos de Estudiante
    e.Ci AS CI_Estudiante,
    e.Complemento,
    e.Exp,
    e.Apaterno AS ApellidoPaterno_Estudiante,
    e.Amaterno AS ApellidoMaterno_Estudiante,
    e.Nombre AS Nombre_Estudiante,
    e.Correo AS Correo_Estudiante,
    e.Direccion AS Direccion_Estudiante,
    e.Celular AS Celular_Estudiante,
    e.Telefono AS Telefono_Estudiante,
    e.FechaNacimiento,
    e.Edad,
    e.Estado AS EstadoEstudiante,
    -- Campos Unificados
    CASE
        WHEN u.Tipo = 'EST' THEN e.Ci
        ELSE p.CedulaIdentidad
    END AS CI_Unificado,
    CASE
        WHEN u.Tipo = 'EST' THEN CONCAT(e.Apaterno, ' ', e.Amaterno, ' ', e.Nombre)
        ELSE CONCAT(p.ApellidoPaterno, ' ', p.ApellidoMaterno, ' ', p.Nombres)
    END AS NombreCompleto_Unificado
FROM usuario u
LEFT JOIN personal p ON u.IdPersonal = p.IdPersonal AND u.Tipo IN ('ADM', 'SEC', 'DOC', 'TEC')
LEFT JOIN estudiante e ON u.IdPersonal = e.Ci AND u.Tipo = 'EST';

SELECT '✅ PASO 3 COMPLETADO: Vista vista_usuarios_completo creada' AS Resultado;

-- --------------------------------------------------------------
-- PASO 4: CREAR VISTA PARA ESTUDIANTES SIN USUARIO
-- --------------------------------------------------------------

CREATE OR REPLACE VIEW vista_estudiantes_sin_usuario AS
SELECT
    e.EstudianteID,
    e.Ci,
    CONCAT(
        e.Ci,
        CASE WHEN e.Complemento != '' THEN CONCAT('-', e.Complemento) ELSE '' END,
        ' ', e.Exp
    ) AS CI_Completo,
    e.Nombre,
    e.Apaterno,
    e.Amaterno,
    CONCAT(e.Apaterno, ' ', e.Amaterno, ' ', e.Nombre) AS NombreCompleto,
    e.Correo,
    e.Celular,
    e.FechaNacimiento,
    e.Edad
FROM estudiante e
LEFT JOIN usuario u ON e.Ci = u.IdPersonal AND u.Tipo = 'EST'
WHERE u.IdPersonal IS NULL
  AND e.Estado = 1
ORDER BY e.Apaterno, e.Amaterno, e.Nombre;

SELECT '✅ PASO 4 COMPLETADO: Vista vista_estudiantes_sin_usuario creada' AS Resultado;

-- --------------------------------------------------------------
-- PASO 5: ELIMINAR TRIGGERS ANTERIORES SI EXISTEN
-- --------------------------------------------------------------

DROP TRIGGER IF EXISTS before_insert_usuario;
DROP TRIGGER IF EXISTS before_update_usuario;

SELECT '✅ PASO 5 COMPLETADO: Triggers anteriores eliminados (si existían)' AS Resultado;

-- --------------------------------------------------------------
-- PASO 6: CREAR TRIGGERS DE VALIDACIÓN
-- --------------------------------------------------------------

DELIMITER $$

CREATE TRIGGER before_insert_usuario
BEFORE INSERT ON usuario
FOR EACH ROW
BEGIN
    DECLARE cuenta INT;

    IF NEW.Tipo = 'EST' THEN
        -- Verificar que el CI existe en tabla estudiante
        SELECT COUNT(*) INTO cuenta FROM estudiante WHERE Ci = NEW.IdPersonal;
        IF cuenta = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ERROR: El CI del estudiante no existe en la tabla estudiante';
        END IF;
    ELSEIF NEW.Tipo IN ('ADM', 'SEC', 'DOC', 'TEC') THEN
        -- Verificar que el IdPersonal existe en tabla personal
        SELECT COUNT(*) INTO cuenta FROM personal WHERE IdPersonal = NEW.IdPersonal;
        IF cuenta = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ERROR: El IdPersonal no existe en la tabla personal';
        END IF;
    END IF;
END$$

CREATE TRIGGER before_update_usuario
BEFORE UPDATE ON usuario
FOR EACH ROW
BEGIN
    DECLARE cuenta INT;

    IF NEW.Tipo = 'EST' THEN
        -- Verificar que el CI existe en tabla estudiante
        SELECT COUNT(*) INTO cuenta FROM estudiante WHERE Ci = NEW.IdPersonal;
        IF cuenta = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ERROR: El CI del estudiante no existe en la tabla estudiante';
        END IF;
    ELSEIF NEW.Tipo IN ('ADM', 'SEC', 'DOC', 'TEC') THEN
        -- Verificar que el IdPersonal existe en tabla personal
        SELECT COUNT(*) INTO cuenta FROM personal WHERE IdPersonal = NEW.IdPersonal;
        IF cuenta = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ERROR: El IdPersonal no existe en la tabla personal';
        END IF;
    END IF;
END$$

DELIMITER ;

SELECT '✅ PASO 6 COMPLETADO: Triggers de validación creados' AS Resultado;

-- --------------------------------------------------------------
-- RESUMEN FINAL
-- --------------------------------------------------------------

SELECT '
╔════════════════════════════════════════════════════════════╗
║                 ✅ MEJORAS APLICADAS                        ║
╠════════════════════════════════════════════════════════════╣
║                                                            ║
║  ✓ Índices creados para mejor rendimiento                 ║
║  ✓ Vista vista_usuarios_simple                            ║
║  ✓ Vista vista_usuarios_completo                          ║
║  ✓ Vista vista_estudiantes_sin_usuario                    ║
║  ✓ Triggers de validación instalados                      ║
║                                                            ║
║  CONSULTAS DE EJEMPLO:                                    ║
║  • SELECT * FROM vista_usuarios_simple;                   ║
║  • SELECT * FROM vista_estudiantes_sin_usuario;           ║
║  • SELECT * FROM vista_usuarios_completo WHERE Tipo=EST;  ║
║                                                            ║
╚════════════════════════════════════════════════════════════╝
' AS '✅ INSTALACIÓN COMPLETADA';

-- Mostrar estadísticas
SELECT
    'Usuarios Activos' AS Categoria,
    COUNT(*) AS Total
FROM usuario
WHERE Estado = '1'
UNION ALL
SELECT
    'Estudiantes con Usuario',
    COUNT(*)
FROM usuario
WHERE Tipo = 'EST' AND Estado = '1'
UNION ALL
SELECT
    'Estudiantes sin Usuario',
    COUNT(*)
FROM vista_estudiantes_sin_usuario;
