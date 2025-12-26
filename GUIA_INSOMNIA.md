# 🚀 GUÍA INSOMNIA - MÓDULO 1: AUTENTICACIÓN

## ✅ Estado del Servidor

**El servidor está CORRIENDO en:**
```
http://localhost:8000
```

---

## 📥 IMPORTAR COLECCIÓN EN INSOMNIA

### Paso 1: Abrir Insomnia

Abre tu aplicación de **Insomnia**

### Paso 2: Importar colección

1. Click en **Create** → **Import**
2. Selecciona **From File**
3. Busca el archivo: `insomnia-auth-collection.json`
4. Click **Import**

### Paso 3: Configurar variables

Insomnia importará estas variables automáticamente:
- `base_url`: http://localhost:8000
- `token`: (se obtiene al hacer login)

---

## 🧪 FLUJO DE PRUEBAS RECOMENDADO

### 1️⃣ LOGIN (Obtener Token)

**Request:** `POST /api/auth/login`

```json
{
  "email": "admin@lapizzeria.ec",
  "password": "Admin@123456"
}
```

**Respuesta:**
```json
{
  "exito": true,
  "mensaje": "Login exitoso",
  "usuario": { ... },
  "token": "eyJhbGciOiJIUzI1NiIs..."
}
```

**Copiar el token y guardarlo en la variable `{{ token }}`**

### 2️⃣ GET ME (Verificar usuario autenticado)

**Request:** `GET /api/auth/me`

Headers incluyen: `Authorization: Bearer {{ token }}`

**Respuesta:**
```json
{
  "exito": true,
  "usuario": { ... },
  "rol": { ... }
}
```

### 3️⃣ VERIFY TOKEN (Validar token)

**Request:** `GET /api/auth/verify-token`

**Respuesta:**
```json
{
  "exito": true,
  "mensaje": "Token válido",
  "usuario_id": 1
}
```

### 4️⃣ CHANGE PASSWORD (Cambiar contraseña)

**Request:** `POST /api/auth/change-password`

```json
{
  "password_actual": "Admin@123456",
  "password_nueva": "NewPass@789",
  "password_nueva_confirmation": "NewPass@789"
}
```

### 5️⃣ LOGOUT (Cerrar sesión)

**Request:** `POST /api/auth/logout`

**Respuesta:**
```json
{
  "exito": true,
  "mensaje": "Logout exitoso"
}
```

---

## 📝 USUARIOS DE PRUEBA

Usa cualquiera de estos para login:

| Email | Contraseña | Rol |
|-------|-----------|-----|
| admin@lapizzeria.ec | Admin@123456 | ADMINISTRADOR |
| usuario@lapizzeria.ec | Usuario@123456 | USUARIO |
| cocinero@lapizzeria.ec | Cocinero@123456 | OPERADOR_COCINA |
| repartidor@lapizzeria.ec | Repartidor@123456 | OPERADOR_DELIVERY |

---

## 🔧 CÓMO USAR VARIABLES EN INSOMNIA

### Guardar Token Automáticamente

1. En la respuesta del **Login**, busca el campo `token`
2. Click derecho en el valor del token
3. Click en **Set Variable** → **token**
4. Listo, ya está guardado

### O Manualmente

1. Click en **Environment**
2. Busca `token`
3. Pega el token completo
4. Listo

### Usar el Token

Todas las requests protegidas lo usan automáticamente:
```
Authorization: Bearer {{ token }}
```

---

## ✨ ENDPOINTS DISPONIBLES

### 🔓 SIN AUTENTICACIÓN

```
POST   /api/auth/register          (Registrar usuario)
POST   /api/auth/login             (Login)
POST   /api/auth/forgot-password   (Olvidé contraseña)
POST   /api/auth/reset-password    (Reset con token)
```

### 🔐 CON AUTENTICACIÓN (requieren {{ token }})

```
GET    /api/auth/me                (Datos del usuario)
GET    /api/auth/verify-token      (Verificar token)
POST   /api/auth/change-password   (Cambiar contraseña)
POST   /api/auth/logout            (Logout)
```

---

## 🚨 PROBLEMAS COMUNES

### Error: "No autenticado"
→ Falta el token o es inválido
→ Solución: Haz login primero

### Error: "Credenciales inválidas"
→ Email o contraseña incorrectos
→ Verifica mayúsculas/minúsculas

### Error: "Cuenta bloqueada"
→ Demasiados intentos fallidos (5 en 15 min)
→ Solución: Usa otro usuario o espera 15 minutos

### El servidor no responde
→ Verifica que esté corriendo: `php artisan serve`
→ Verifica URL: http://localhost:8000

---

## 💡 TIPS

1. **Guarda los cambios** después de cada operación
2. **Usa variables** para no repetir datos
3. **Copia respuestas** en Request Body para reutilizar
4. **Verifica headers** antes de enviar requests
5. **Lee los mensajes de error** - son descriptivos

---

## 📊 FLUJO VISUAL

```
┌──────────────┐
│ 1. LOGIN     │ → Obtener token
└────┬─────────┘
     │
     ▼
┌──────────────┐
│ 2. GET ME    │ → Verificar datos
└────┬─────────┘
     │
     ▼
┌──────────────────────┐
│ 3. VERIFY TOKEN      │ → Token válido?
└────┬─────────────────┘
     │
     ├─→ 4. CHANGE PASSWORD (opcional)
     │
     ├─→ 5. OTROS ENDPOINTS
     │
     ▼
┌──────────────┐
│ 6. LOGOUT    │ → Cerrar sesión
└──────────────┘
```

---

## 🎯 PRUEBA RÁPIDA (5 MINUTOS)

1. **Importa** `insomnia-auth-collection.json`
2. **Haz click** en **Login**
3. **Click** en **Send**
4. **Copia el token** que recibes
5. **En Environment**, pega el token en `{{ token }}`
6. **Haz click** en **Get Me**
7. **Click** en **Send**
8. ¡Listo! Ves tus datos 🎉

---

## 📚 MÁS INFORMACIÓN

- Documentación completa: [API_AUTHENTICATION.md](API_AUTHENTICATION.md)
- Guía de instalación: [README_MODULO1.md](README_MODULO1.md)
- Índice de archivos: [INDICE.md](INDICE.md)

---

**¡Tu API está lista para probar!** 🚀

El servidor está corriendo en: http://localhost:8000
