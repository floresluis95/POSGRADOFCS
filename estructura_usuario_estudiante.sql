-- ==============================================================
-- DOCUMENTACIÓN: ESTRUCTURA DE TABLA USUARIO
-- Y RELACIÓN CON ESTUDIANTES
-- ==============================================================

-- --------------------------------------------------------------
-- ESTRUCTURA ACTUAL DE LA TABLA USUARIO
-- --------------------------------------------------------------

/*
CREATE TABLE `usuario` (
  `IdPersonal` int(11) NOT NULL,           -- FK que apunta a personal o estudiante según el Tipo
  `Usuario` varchar(20) NOT NULL,          -- Nombre de usuario para login
  `Password` text NOT NULL,                -- Contraseña encriptada con bcrypt
  `FechaIngreso` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Estado` char(1) NOT NULL DEFAULT '1',   -- 1=Activo, 0=Inactivo
  `Tipo` varchar(3) NOT NULL,              -- ADM, SEC, DOC, TEC, EST
  PRIMARY KEY (`IdPersonal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
*/

-- --------------------------------------------------------------
-- CÓMO FUNCIONA LA RELACIÓN ACTUAL
-- --------------------------------------------------------------

/*
IMPORTANTE: La tabla usuario utiliza una relación POLIMÓRFICA basada en el campo 'Tipo'

1. Para personal (ADM, SEC, DOC, TEC):
   - IdPersonal → personal.IdPersonal
   - Se busca en la tabla 'personal'

2. Para estudiantes (EST):
   - IdPersonal → estudiante.Ci
   - Se busca en la tabla 'estudiante' usando el campo Ci

Ejemplo:
  - Usuario con Tipo='ADM' e IdPersonal=1 → Busca en personal WHERE IdPersonal=1
  - Usuario con Tipo='EST' e IdPersonal=12345678 → Busca en estudiante WHERE Ci=12345678
*/

-- --------------------------------------------------------------
-- VISTA UNIFICADA: TODOS LOS USUARIOS CON SUS DATOS
-- --------------------------------------------------------------

-- Esta vista combina datos de personal y estudiantes
CREATE OR REPLACE VIEW vista_usuarios_completo AS
SELECT
    u.IdPersonal,
    u.Usuario,
    u.Password,
    u.FechaIngreso,
    u.Estado AS EstadoUsuario,
    u.Tipo,
    -- Datos de Personal (si aplica)
    p.CedulaIdentidad AS CI_Personal,
    CONCAT(p.ApellidoPaterno, ' ', p.ApellidoMaterno, ' ', p.Nombres) AS NombreCompleto_Personal,
    p.Direccion AS Direccion_Personal,
    p.Celular AS Celular_Personal,
    p.Telefono AS Telefono_Personal,
    -- Datos de Estudiante (si aplica)
    e.Ci AS CI_Estudiante,
    CONCAT(e.Ci,
           CASE WHEN e.Complemento != '' THEN CONCAT('-', e.Complemento) ELSE '' END,
           ' ', e.Exp) AS CI_Completo_Estudiante,
    CONCAT(e.Apaterno, ' ', e.Amaterno, ' ', e.Nombre) AS NombreCompleto_Estudiante,
    e.Correo AS Correo_Estudiante,
    e.Direccion AS Direccion_Estudiante,
    e.Celular AS Celular_Estudiante,
    e.Telefono AS Telefono_Estudiante,
    e.Estado AS EstadoEstudiante,
    -- Campos unificados
    CASE
        WHEN u.Tipo = 'EST' THEN e.Ci
        ELSE p.CedulaIdentidad
    END AS CI_Unificado,
    CASE
        WHEN u.Tipo = 'EST' THEN CONCAT(e.Apaterno, ' ', e.Amaterno, ' ', e.Nombre)
        ELSE CONCAT(p.ApellidoPaterno, ' ', p.ApellidoMaterno, ' ', p.Nombres)
    END AS NombreCompleto_Unificado,
    CASE
        WHEN u.Tipo = 'EST' THEN e.Correo
        ELSE NULL
    END AS Correo_Unificado
FROM usuario u
LEFT JOIN personal p ON u.IdPersonal = p.IdPersonal AND u.Tipo IN ('ADM', 'SEC', 'DOC', 'TEC')
LEFT JOIN estudiante e ON u.IdPersonal = e.Ci AND u.Tipo = 'EST';

-- --------------------------------------------------------------
-- VISTA SIMPLIFICADA: SOLO DATOS NECESARIOS
-- --------------------------------------------------------------

CREATE OR REPLACE VIEW vista_usuarios_simple AS
SELECT
    u.IdPersonal,
    u.Usuario,
    u.FechaIngreso,
    u.Estado,
    u.Tipo,
    CASE
        WHEN u.Tipo = 'EST' THEN CONCAT(e.Ci,
                                        CASE WHEN e.Complemento != '' THEN CONCAT('-', e.Complemento) ELSE '' END,
                                        ' ', e.Exp)
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

-- --------------------------------------------------------------
-- ÍNDICES RECOMENDADOS PARA MEJORAR RENDIMIENTO
-- --------------------------------------------------------------

-- Índice en tabla usuario
CREATE INDEX idx_usuario_tipo ON usuario(Tipo);
CREATE INDEX idx_usuario_estado ON usuario(Estado);

-- Índice en tabla estudiante para búsquedas por CI
CREATE INDEX idx_estudiante_ci ON estudiante(Ci);

-- --------------------------------------------------------------
-- CONSTRAINT CONDICIONAL CON TRIGGER (OPCIONAL)
-- --------------------------------------------------------------

-- Este trigger valida que:
-- 1. Si Tipo='EST', el IdPersonal debe existir en estudiante.Ci
-- 2. Si Tipo es otro, el IdPersonal debe existir en personal.IdPersonal

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
            SET MESSAGE_TEXT = 'Error: El CI del estudiante no existe en la tabla estudiante';
        END IF;
    ELSE
        -- Verificar que el IdPersonal existe en tabla personal
        SELECT COUNT(*) INTO cuenta FROM personal WHERE IdPersonal = NEW.IdPersonal;
        IF cuenta = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Error: El IdPersonal no existe en la tabla personal';
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
            SET MESSAGE_TEXT = 'Error: El CI del estudiante no existe en la tabla estudiante';
        END IF;
    ELSE
        -- Verificar que el IdPersonal existe en tabla personal
        SELECT COUNT(*) INTO cuenta FROM personal WHERE IdPersonal = NEW.IdPersonal;
        IF cuenta = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Error: El IdPersonal no existe en la tabla personal';
        END IF;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------------
-- PROCEDIMIENTO ALMACENADO: CREAR USUARIO PARA ESTUDIANTE
-- --------------------------------------------------------------

DELIMITER $$

CREATE PROCEDURE sp_crear_usuario_estudiante(
    IN p_ci INT,
    IN p_primer_nombre VARCHAR(50),
    OUT p_usuario VARCHAR(20),
    OUT p_password_texto VARCHAR(30),
    OUT p_resultado VARCHAR(50)
)
BEGIN
    DECLARE v_existe INT;
    DECLARE v_password_hash TEXT;

    -- Verificar si el estudiante existe
    SELECT COUNT(*) INTO v_existe FROM estudiante WHERE Ci = p_ci;

    IF v_existe = 0 THEN
        SET p_resultado = 'ERROR: Estudiante no encontrado';
        SET p_usuario = NULL;
        SET p_password_texto = NULL;
    ELSE
        -- Verificar si ya tiene usuario
        SELECT COUNT(*) INTO v_existe FROM usuario WHERE IdPersonal = p_ci AND Tipo = 'EST';

        IF v_existe > 0 THEN
            SET p_resultado = 'ERROR: El estudiante ya tiene usuario';
            SET p_usuario = NULL;
            SET p_password_texto = NULL;
        ELSE
            -- Generar credenciales
            SET p_usuario = CAST(p_ci AS CHAR);
            SET p_password_texto = CONCAT(UPPER(SUBSTRING(p_primer_nombre, 1, 1)), p_ci);

            -- En producción, usar una función de hash real
            -- Por ahora, esto es solo un ejemplo
            SET v_password_hash = p_password_texto; -- NOTA: Debe encriptarse con bcrypt en PHP

            -- Insertar usuario
            INSERT INTO usuario (IdPersonal, Usuario, Password, Tipo, Estado)
            VALUES (p_ci, p_usuario, v_password_hash, 'EST', '1');

            SET p_resultado = 'EXITOSO';
        END IF;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------------
-- CONSULTAS ÚTILES
-- --------------------------------------------------------------

-- 1. Ver todos los usuarios con sus datos completos
-- SELECT * FROM vista_usuarios_simple;

-- 2. Ver solo estudiantes con usuario
-- SELECT * FROM vista_usuarios_simple WHERE Tipo = 'EST';

-- 3. Ver estudiantes SIN usuario
/*
SELECT
    e.Ci,
    CONCAT(e.Ci,
           CASE WHEN e.Complemento != '' THEN CONCAT('-', e.Complemento) ELSE '' END,
           ' ', e.Exp) AS CI_Completo,
    CONCAT(e.Apaterno, ' ', e.Amaterno, ' ', e.Nombre) AS NombreCompleto,
    e.Correo
FROM estudiante e
LEFT JOIN usuario u ON e.Ci = u.IdPersonal AND u.Tipo = 'EST'
WHERE u.IdPersonal IS NULL
  AND e.Estado = 1;
*/

-- 4. Obtener datos completos de un usuario específico
-- SELECT * FROM vista_usuarios_completo WHERE Usuario = 'carlos123';

-- 5. Contar usuarios por tipo
/*
SELECT
    Tipo,
    TipoDescripcion,
    COUNT(*) as Total
FROM vista_usuarios_simple
GROUP BY Tipo, TipoDescripcion;
*/

-- --------------------------------------------------------------
-- NOTAS IMPORTANTES
-- --------------------------------------------------------------

/*
1. NO ES NECESARIO MODIFICAR LA ESTRUCTURA DE LA TABLA USUARIO
   La tabla actual ya soporta la relación con estudiantes usando IdPersonal

2. PARA ESTUDIANTES:
   - IdPersonal almacena el CI del estudiante (estudiante.Ci)
   - Tipo debe ser 'EST'

3. PARA PERSONAL:
   - IdPersonal almacena el ID del personal (personal.IdPersonal)
   - Tipo puede ser 'ADM', 'SEC', 'DOC', 'TEC', etc.

4. LAS VISTAS CREADAS FACILITAN LAS CONSULTAS
   - vista_usuarios_completo: Todos los campos de ambas tablas
   - vista_usuarios_simple: Solo campos esenciales

5. LOS TRIGGERS SON OPCIONALES
   - Agregan validación a nivel de base de datos
   - Previenen inserciones incorrectas
   - Pueden ejecutarse opcionalmente

6. ÍNDICES MEJORAN EL RENDIMIENTO
   - Especialmente importante cuando hay muchos usuarios
*/
