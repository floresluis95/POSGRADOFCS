# 📋 GUÍA: Relación entre Usuario y Estudiante

## 📊 Estructura Actual de tu Tabla `usuario`

```sql
CREATE TABLE `usuario` (
  `IdPersonal` int(11) NOT NULL PRIMARY KEY,
  `Usuario` varchar(20) NOT NULL,
  `Password` text NOT NULL,
  `FechaIngreso` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Estado` char(1) NOT NULL DEFAULT '1',
  `Tipo` varchar(3) NOT NULL
)
```

### Campos:
- **IdPersonal**: Clave primaria (FK polimórfica)
- **Usuario**: Nombre de usuario para login
- **Password**: Contraseña encriptada (bcrypt)
- **FechaIngreso**: Fecha de creación del usuario
- **Estado**: `1` = Activo, `0` = Inactivo
- **Tipo**: Tipo de usuario
  - `ADM` - Administrador
  - `SEC` - Secretaria
  - `DOC` - Docente
  - `TEC` - Técnico
  - `EST` - **Estudiante** ⭐

---

## 🔗 Cómo se Relaciona con Estudiantes

### ⚠️ **IMPORTANTE: Relación Polimórfica**

La tabla `usuario` usa una **relación polimórfica** basada en el campo `Tipo`:

| Tipo de Usuario | IdPersonal apunta a | Tabla de Referencia |
|----------------|---------------------|---------------------|
| ADM, SEC, DOC, TEC | `personal.IdPersonal` | `personal` |
| **EST** (Estudiante) | `estudiante.Ci` | `estudiante` |

### 📝 Ejemplo Visual:

```
Tabla: usuario
┌─────────────┬──────────┬──────────┬─────┐
│ IdPersonal  │ Usuario  │ Password │ Tipo│
├─────────────┼──────────┼──────────┼─────┤
│ 1           │ luis123  │ ****     │ ADM │ → Busca en personal WHERE IdPersonal = 1
│ 2           │ lucero123│ ****     │ SEC │ → Busca en personal WHERE IdPersonal = 2
│ 12345678    │ 12345678 │ ****     │ EST │ → Busca en estudiante WHERE Ci = 12345678
└─────────────┴──────────┴──────────┴─────┘
```

---

## ✅ **NO NECESITAS MODIFICAR LA ESTRUCTURA**

Tu tabla **ya está bien diseñada** para manejar tanto personal como estudiantes.

### Lo que ya funciona:
✅ Personal usa `IdPersonal` de la tabla `personal`
✅ Estudiantes usan `Ci` de la tabla `estudiante` almacenado en `IdPersonal`
✅ El campo `Tipo` diferencia entre ambos
✅ Tu código PHP ya implementa esta lógica correctamente

---

## 🚀 Mejoras Recomendadas (Opcionales)

### 1. **Crear Vistas para Consultas Fáciles**

#### Vista Simple (recomendada):
```sql
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
    END AS Correo
FROM usuario u
LEFT JOIN personal p ON u.IdPersonal = p.IdPersonal AND u.Tipo IN ('ADM', 'SEC', 'DOC', 'TEC')
LEFT JOIN estudiante e ON u.IdPersonal = e.Ci AND u.Tipo = 'EST'
WHERE u.Estado = '1';
```

**Uso:**
```sql
-- Ver todos los usuarios activos
SELECT * FROM vista_usuarios_simple;

-- Ver solo estudiantes
SELECT * FROM vista_usuarios_simple WHERE Tipo = 'EST';
```

---

### 2. **Índices para Mejor Rendimiento**

```sql
-- Índice en tabla usuario
CREATE INDEX idx_usuario_tipo ON usuario(Tipo);
CREATE INDEX idx_usuario_estado ON usuario(Estado);

-- Índice en tabla estudiante para búsquedas por CI
CREATE INDEX idx_estudiante_ci ON estudiante(Ci);
```

---

### 3. **Triggers de Validación (Opcional)**

Estos triggers validan que:
- Si `Tipo='EST'`, el `IdPersonal` debe existir en `estudiante.Ci`
- Si `Tipo` es otro, el `IdPersonal` debe existir en `personal.IdPersonal`

```sql
DELIMITER $$

CREATE TRIGGER before_insert_usuario
BEFORE INSERT ON usuario
FOR EACH ROW
BEGIN
    DECLARE cuenta INT;

    IF NEW.Tipo = 'EST' THEN
        SELECT COUNT(*) INTO cuenta FROM estudiante WHERE Ci = NEW.IdPersonal;
        IF cuenta = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Error: El CI del estudiante no existe';
        END IF;
    ELSE
        SELECT COUNT(*) INTO cuenta FROM personal WHERE IdPersonal = NEW.IdPersonal;
        IF cuenta = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Error: El IdPersonal no existe en personal';
        END IF;
    END IF;
END$$

DELIMITER ;
```

---

## 📚 Consultas Útiles

### Ver estudiantes SIN usuario asignado:
```sql
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
```

### Contar usuarios por tipo:
```sql
SELECT
    Tipo,
    CASE
        WHEN Tipo = 'ADM' THEN 'Administrador'
        WHEN Tipo = 'SEC' THEN 'Secretaria'
        WHEN Tipo = 'DOC' THEN 'Docente'
        WHEN Tipo = 'TEC' THEN 'Técnico'
        WHEN Tipo = 'EST' THEN 'Estudiante'
    END AS Descripcion,
    COUNT(*) as Total
FROM usuario
WHERE Estado = '1'
GROUP BY Tipo;
```

---

## 🎯 Resumen

### ✅ Lo que YA tienes:
1. ✅ Tabla `usuario` que soporta personal y estudiantes
2. ✅ Campo `Tipo` que diferencia el tipo de usuario
3. ✅ Campo `IdPersonal` que almacena el ID o CI según el tipo
4. ✅ Código PHP que implementa esta lógica correctamente

### 📦 Lo que puedes agregar (opcional):
1. 📊 **Vistas SQL** - Para consultas más fáciles
2. 🔍 **Índices** - Para mejor rendimiento
3. 🛡️ **Triggers** - Para validación automática en la BD

### 🎓 Para Estudiantes específicamente:
- **Usuario**: Número de Carnet (CI)
- **Contraseña**: Primera letra del nombre + CI
- **Tipo**: `'EST'`
- **IdPersonal**: Almacena el `Ci` del estudiante

---

## 📁 Archivos Creados

1. **estructura_usuario_estudiante.sql** - Scripts SQL completos
2. **GUIA_RELACION_USUARIO_ESTUDIANTE.md** - Esta guía

---

## 💡 Recomendación Final

**NO necesitas modificar tu tabla `usuario`**. La estructura actual es correcta y funcional. Las mejoras sugeridas (vistas, índices, triggers) son **opcionales** y solo mejoran la mantenibilidad y rendimiento, pero el sistema ya funciona bien sin ellas.

Tu implementación actual en PHP ya maneja correctamente la diferenciación entre personal y estudiantes usando el campo `Tipo`.
