# 📝 Instrucciones de Login - Sistema de Posgrado

## ✅ Sistema de Login Corregido y Funcionando

El sistema ahora permite el ingreso de **3 tipos de usuarios**:

---

## 👥 Tipos de Usuarios

### 1. **Personal Administrativo** (ADM / SEC)
**Usuarios que ya existían en el sistema antes de las mejoras**

**Credenciales:**
- **Usuario:** El usuario asignado (ej: `luis123`, `lucero`)
- **Contraseña:** La contraseña asignada

**Ejemplo:**
```
Usuario: luis123
Contraseña: (tu contraseña)
```

---

### 2. **Estudiantes** (EST)
**Estudiantes a los que se les asignó un usuario desde el módulo de estudiantes**

**Credenciales automáticas:**
- **Usuario:** Tu número de CI (sin complemento)
- **Contraseña:** Primera letra de tu nombre + tu CI

**Ejemplo:**
Si tu nombre es **JUAN CARLOS** y tu CI es **63650134**:
```
Usuario: 63650134
Contraseña: J63650134
```

**Otro ejemplo:**
Si tu nombre es **MARÍA** y tu CI es **789654**:
```
Usuario: 789654
Contraseña: M789654
```

---

### 3. **Docentes** (DOC)
**Docentes a los que se les asignó un usuario desde el módulo de docentes**

**Credenciales automáticas:**
- **Usuario:** Tu número de CI (sin complemento)
- **Contraseña:** Primera letra de tu nombre + tu CI

**Ejemplo:**
Si tu nombre es **JUANA** y tu CI es **1245878**:
```
Usuario: 1245878
Contraseña: J1245878
```

**Otro ejemplo:**
Si tu nombre es **CARLOS** y tu CI es **551821595**:
```
Usuario: 551821595
Contraseña: C551821595
```

---

## 🔑 ¿Cómo se genera la contraseña?

### Regla Simple:
**Primera letra del primer nombre (en MAYÚSCULA) + Número de CI completo**

### Ejemplos:

| Nombre Completo | CI | Usuario | Contraseña |
|----------------|-------|---------|------------|
| JUAN CARLOS GARCÍA | 63650134 | 63650134 | **J**63650134 |
| MARÍA LÓPEZ PÉREZ | 789654 | 789654 | **M**789654 |
| JUANA DÍAZ | 1245878 | 1245878 | **J**1245878 |
| CARLOS ROJAS | 551821595 | 551821595 | **C**551821595 |
| ANA MARÍA TORRES | 12345678 | 12345678 | **A**12345678 |

---

## 🚪 ¿Cómo Iniciar Sesión?

### Paso 1: Ir a la página de login
Abre tu navegador y ve a: `http://localhost/POSGRADOFCS/`

### Paso 2: Ingresar credenciales
- En el campo **Usuario**: Ingresa tu usuario
- En el campo **Contraseña**: Ingresa tu contraseña

### Paso 3: Hacer clic en "Ingresar"
Si tus credenciales son correctas, serás redirigido al panel principal.

---

## ❓ Preguntas Frecuentes

### 1. ¿No sé mi usuario?
- **Estudiantes/Docentes:** Tu usuario es tu número de CI
- **Personal:** Pregunta al administrador del sistema

### 2. ¿No sé mi contraseña?
- **Estudiantes/Docentes:** Es la primera letra de tu nombre + tu CI
- **Personal:** Pregunta al administrador del sistema

### 3. ¿Puedo cambiar mi contraseña?
Actualmente el sistema usa las contraseñas generadas automáticamente. Se recomienda implementar un módulo de cambio de contraseña.

### 4. ¿Qué pasa si mi nombre tiene dos palabras?
Se usa solo la **primera letra del primer nombre**.
- **JUAN CARLOS** → Primera letra: **J**
- **ANA MARÍA** → Primera letra: **A**

### 5. ¿La letra debe ser mayúscula?
Sí, la primera letra de la contraseña debe ser **MAYÚSCULA**.

### 6. ¿Y si mi CI tiene complemento (1A, 2B, etc.)?
El usuario es **solo el número del CI**, sin complemento ni expedido.
- CI: 12345678-1A LP → Usuario: **12345678**

---

## 🔍 Verificar si tengo usuario asignado

### Para Estudiantes:
1. El administrador debe ir al módulo **Estudiantes**
2. Buscar tu nombre en la lista
3. En la columna **Usuario** verás:
   - ✅ Verde: Tienes usuario activo
   - ⚠️ Amarillo: No tienes usuario (botón "Asignar")

### Para Docentes:
1. El administrador debe ir al módulo **Docentes**
2. Buscar tu nombre en la lista
3. En la columna **Usuario** verás:
   - ✅ Verde: Tienes usuario activo
   - ⚠️ Amarillo: No tienes usuario (botón "Asignar")

---

## 🛠️ Para Administradores

### Asignar usuario a un Estudiante:
1. Ir a **Estudiantes**
2. Buscar al estudiante sin usuario (badge amarillo)
3. Hacer clic en el botón **"Asignar"**
4. Verificar las credenciales en el modal
5. Hacer clic en **"Confirmar y Crear"**
6. **IMPORTANTE:** Copiar y entregar las credenciales al estudiante

### Asignar usuario a un Docente:
1. Ir a **Docentes**
2. Buscar al docente sin usuario (badge amarillo)
3. Hacer clic en el botón **"Asignar"**
4. Verificar las credenciales en el modal
5. Hacer clic en **"Confirmar y Crear"**
6. **IMPORTANTE:** Copiar y entregar las credenciales al docente

---

## 🔐 Seguridad

- ✅ Todas las contraseñas se guardan **encriptadas** con BCRYPT
- ✅ Las contraseñas **nunca** se muestran después de crearlas
- ✅ Solo usuarios **activos** pueden iniciar sesión
- ✅ El sistema valida usuario y contraseña antes de permitir el acceso

---

## 📞 Soporte

Si tienes problemas para iniciar sesión:
1. Verifica que estés usando el **usuario correcto**
2. Verifica que la **contraseña** tenga la primera letra en MAYÚSCULA
3. Asegúrate de que tu usuario esté **activo** en el sistema
4. Contacta al administrador del sistema

---

## 📊 Usuarios Actuales en el Sistema

Según la última verificación:

| Usuario | Tipo | Nombre |
|---------|------|--------|
| luis123 | Administrador | LUIS UÑO |
| lucero | Secretaria | - |
| 63650134 | Estudiante | JUAN CARLOS GARCIA |
| 789654 | Estudiante | - |
| 1245878 | Docente | JUANA DIAZ |
| 551821595 | Docente | - |

**Total:** 6 usuarios activos

---

**Sistema actualizado el:** 28 de Noviembre de 2025
**Estado:** ✅ Funcionando correctamente
