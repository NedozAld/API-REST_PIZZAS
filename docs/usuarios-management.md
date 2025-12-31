# Módulo 8: Gestión de Usuarios - Guía de Pruebas

Base URL: `http://localhost:8000/api`  
Autenticación: Todas las rutas requieren token Sanctum (admin/usuario)

---

## US-060: Crear Usuario (Admin) ✅

**Endpoint:** `POST /api/usuarios`

**Auth:** Required (Admin token)

**Body JSON:**
```json
{
  "nombre": "Juan Pérez",
  "email": "juan.perez@example.com",
  "password": "SecurePass123",
  "password_confirmation": "SecurePass123",
  "rol_id": 1,
  "telefono": "+34912345678"
}
```

**Respuesta 201:**
```json
{
  "exito": true,
  "mensaje": "Usuario creado exitosamente",
  "usuario": {
    "id": 15,
    "nombre": "Juan Pérez",
    "email": "juan.perez@example.com",
    "rol_id": 1,
    "estado": "activo"
  }
}
```

**Curl:**
```bash
curl -X POST http://localhost:8000/api/usuarios \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Juan Pérez",
    "email": "juan.perez@example.com",
    "password": "SecurePass123",
    "password_confirmation": "SecurePass123",
    "rol_id": 1,
    "telefono": "+34912345678"
  }'
```

**Validaciones:**
- nombre: requerido, máx 120 caracteres
- email: requerido, válido, único en base de datos
- password: requerido, mín 8 caracteres, confirmación coincide
- rol_id: requerido, debe existir en tabla roles
- telefono: opcional, máx 20 caracteres

**Registro de Auditoría:** Automáticamente se crea registro en tabla `auditoria` con tipo_accion = 'CREAR'

---

## US-062: Ver Usuarios (Listado) ✅

**Endpoint:** `GET /api/usuarios`

**Auth:** Required

**Parámetros Query:**
- `page`: número de página (default: 1)
- `per_page`: registros por página (default: 15)

**Respuesta 200:**
```json
{
  "exito": true,
  "total": 45,
  "por_pagina": 15,
  "pagina_actual": 1,
  "total_paginas": 3,
  "datos": [
    {
      "id": 1,
      "nombre": "Admin Usuario",
      "email": "admin@example.com",
      "telefono": "+34912345678",
      "rol_id": 1,
      "estado": "activo",
      "fecha_creacion": "2025-12-01T10:30:00.000000Z",
      "ultima_conexion": "2025-12-29T15:45:00.000000Z",
      "rol": {
        "id": 1,
        "nombre": "Admin",
        "descripcion": "Administrador del sistema",
        "activo": true
      }
    }
  ]
}
```

**Curl:**
```bash
# Página 1
curl -X GET http://localhost:8000/api/usuarios \
  -H "Authorization: Bearer TOKEN"

# Página 2 con 20 registros por página
curl -X GET "http://localhost:8000/api/usuarios?page=2&per_page=20" \
  -H "Authorization: Bearer TOKEN"
```

**Información incluida:**
- Datos del usuario
- Información del rol relacionado

---

## US-061: Asignar Rol ✅

**Endpoint:** `PUT /api/usuarios/{id}/rol`

**Auth:** Required (Admin)

**Body JSON:**
```json
{
  "rol_id": 2
}
```

**Respuesta 200:**
```json
{
  "exito": true,
  "mensaje": "Rol asignado exitosamente",
  "usuario": {
    "id": 5,
    "nombre": "Juan Pérez",
    "email": "juan.perez@example.com",
    "rol_id": 2,
    "estado": "activo",
    "rol": {
      "id": 2,
      "nombre": "Gerente",
      "descripcion": "Gerente de operaciones",
      "activo": true
    }
  }
}
```

**Curl:**
```bash
curl -X PUT http://localhost:8000/api/usuarios/5/rol \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "rol_id": 2
  }'
```

**Validaciones:**
- rol_id: requerido, debe existir en tabla roles

**Registro de Auditoría:** tipo_accion = 'ACTUALIZAR' con datos_anteriores y datos_nuevos

---

## US-063: Desactivar Usuario (Cambiar Estado) ✅

**Endpoint:** `PATCH /api/usuarios/{id}/estado`

**Auth:** Required (Admin)

**Body JSON:**
```json
{
  "estado": "inactivo"
}
```

**Respuesta 200:**
```json
{
  "exito": true,
  "mensaje": "Usuario inactivo",
  "usuario": {
    "id": 5,
    "nombre": "Juan Pérez",
    "email": "juan.perez@example.com",
    "rol_id": 1,
    "estado": "inactivo",
    "fecha_creacion": "2025-12-01T10:30:00.000000Z",
    "ultima_conexion": "2025-12-29T15:45:00.000000Z"
  }
}
```

**Curl - Desactivar:**
```bash
curl -X PATCH http://localhost:8000/api/usuarios/5/estado \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "estado": "inactivo"
  }'
```

**Curl - Activar:**
```bash
curl -X PATCH http://localhost:8000/api/usuarios/5/estado \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "estado": "activo"
  }'
```

**Estados válidos:** activo | inactivo

**Registro de Auditoría:** tipo_accion = 'ACTUALIZAR' con cambio de estado

---

## US-064: Auditoría de Acciones ✅

**Endpoint:** `GET /api/auditoria`

**Auth:** Required

**Parámetros Query (Filtros):**
- `usuario_id`: filtrar por usuario que realizó la acción
- `tipo_accion`: CREAR, ACTUALIZAR, ELIMINAR
- `tabla_afectada`: nombre de tabla (usuarios, pedidos, etc.)
- `fecha_desde`: YYYY-MM-DD
- `fecha_hasta`: YYYY-MM-DD
- `page`: número de página
- `per_page`: registros por página

**Respuesta 200:**
```json
{
  "exito": true,
  "total": 234,
  "por_pagina": 20,
  "pagina_actual": 1,
  "total_paginas": 12,
  "filtros": {
    "usuario_id": null,
    "tipo_accion": null,
    "tabla_afectada": null,
    "fecha_desde": null,
    "fecha_hasta": null
  },
  "datos": [
    {
      "id": 234,
      "usuario_id": 1,
      "nombre_usuario": "Admin",
      "tabla_afectada": "usuarios",
      "tipo_accion": "CREAR",
      "registro_id": 15,
      "datos_nuevos": {
        "nombre": "Juan Pérez",
        "email": "juan.perez@example.com",
        "rol_id": 1
      },
      "descripcion": "Usuario creado: Juan Pérez",
      "fecha_accion": "2025-12-29T16:20:00.000000Z",
      "ip_address": "192.168.1.100",
      "user_agent": "Mozilla/5.0..."
    }
  ]
}
```

**Curl - Historial completo:**
```bash
curl -X GET http://localhost:8000/api/auditoria \
  -H "Authorization: Bearer TOKEN"
```

**Curl - Filtrar por usuario:**
```bash
curl -X GET "http://localhost:8000/api/auditoria?usuario_id=1" \
  -H "Authorization: Bearer TOKEN"
```

**Curl - Filtrar por tipo de acción:**
```bash
curl -X GET "http://localhost:8000/api/auditoria?tipo_accion=CREAR" \
  -H "Authorization: Bearer TOKEN"
```

**Curl - Filtrar por tabla:**
```bash
curl -X GET "http://localhost:8000/api/auditoria?tabla_afectada=usuarios" \
  -H "Authorization: Bearer TOKEN"
```

**Curl - Filtrar por rango de fechas:**
```bash
curl -X GET "http://localhost:8000/api/auditoria?fecha_desde=2025-12-20&fecha_hasta=2025-12-29" \
  -H "Authorization: Bearer TOKEN"
```

**Curl - Combinación de filtros:**
```bash
curl -X GET "http://localhost:8000/api/auditoria?usuario_id=1&tipo_accion=ACTUALIZAR&tabla_afectada=usuarios&fecha_desde=2025-12-01" \
  -H "Authorization: Bearer TOKEN"
```

---

## Endpoints Adicionales (Bonus)

### Obtener usuario por ID
```bash
GET /api/usuarios/{id}

curl -X GET http://localhost:8000/api/usuarios/5 \
  -H "Authorization: Bearer TOKEN"

Respuesta:
{
  "exito": true,
  "usuario": {
    "id": 5,
    "nombre": "Juan Pérez",
    "email": "juan.perez@example.com",
    "rol_id": 1,
    "estado": "activo",
    "rol": {...}
  }
}
```

### Estadísticas de Auditoría
```bash
GET /api/auditoria/estadisticas

curl -X GET http://localhost:8000/api/auditoria/estadisticas \
  -H "Authorization: Bearer TOKEN"

Respuesta:
{
  "exito": true,
  "total_acciones": 234,
  "acciones_ultimas_24h": 15,
  "acciones_por_tipo": [
    { "tipo_accion": "CREAR", "total": 45 },
    { "tipo_accion": "ACTUALIZAR", "total": 180 },
    { "tipo_accion": "ELIMINAR", "total": 9 }
  ],
  "usuarios_mas_activos": [
    { "usuario_id": 1, "nombre_usuario": "Admin", "total_acciones": 120 }
  ],
  "tablas_afectadas": [
    { "tabla_afectada": "usuarios", "total": 50 }
  ]
}
```

### Auditoría de usuario específico
```bash
GET /api/auditoria/usuario/{usuario_id}

curl -X GET http://localhost:8000/api/auditoria/usuario/1 \
  -H "Authorization: Bearer TOKEN"

Respuesta:
{
  "exito": true,
  "usuario_id": 1,
  "total": 120,
  "datos": [...]
}
```

---

## Flujo de Ejemplo Completo

```bash
# 1. Crear un nuevo usuario
curl -X POST http://localhost:8000/api/usuarios \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Laura García",
    "email": "laura.garcia@example.com",
    "password": "SecurePass456",
    "password_confirmation": "SecurePass456",
    "rol_id": 2
  }'

# Respuesta: ID 20

# 2. Ver lista de usuarios
curl -X GET http://localhost:8000/api/usuarios \
  -H "Authorization: Bearer TOKEN"

# 3. Asignar nuevo rol
curl -X PUT http://localhost:8000/api/usuarios/20/rol \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{ "rol_id": 3 }'

# 4. Desactivar usuario
curl -X PATCH http://localhost:8000/api/usuarios/20/estado \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{ "estado": "inactivo" }'

# 5. Ver auditoría de cambios
curl -X GET "http://localhost:8000/api/auditoria?usuario_id=20" \
  -H "Authorization: Bearer TOKEN"

# 6. Ver estadísticas
curl -X GET http://localhost:8000/api/auditoria/estadisticas \
  -H "Authorization: Bearer TOKEN"
```

---

## Seguridad y Validaciones

### Autorización
- Solo usuarios con rol activo pueden crear/modificar usuarios
- Se valida existencia del usuario antes de actualizar
- Se registra usuario y IP en auditoría para trazabilidad

### Campos Protegidos
- `password_hash`: nunca se devuelve en respuesta
- `remember_token`: nunca se devuelve
- Contraseña hasheada con `Hash::make()`

### Auditoría Automática
Cada operación registra:
- usuario_id: quién realizó la acción
- nombre_usuario: nombre del usuario
- tabla_afectada: qué tabla fue modificada
- tipo_accion: CREAR, ACTUALIZAR, ELIMINAR
- datos_anteriores: valores antes del cambio
- datos_nuevos: valores después del cambio
- fecha_accion: cuándo sucedió
- ip_address: desde dónde se realizó
- user_agent: qué navegador/cliente usó

---

## Integración con Frontend

```javascript
// Crear usuario
fetch('http://localhost:8000/api/usuarios', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    nombre: 'Laura García',
    email: 'laura@example.com',
    password: 'SecurePass456',
    password_confirmation: 'SecurePass456',
    rol_id: 2
  })
})
  .then(r => r.json())
  .then(data => console.log('Usuario creado:', data.usuario));

// Listar usuarios
fetch('http://localhost:8000/api/usuarios', {
  headers: { 'Authorization': `Bearer ${token}` }
})
  .then(r => r.json())
  .then(data => {
    console.log('Total usuarios:', data.total);
    console.log('Usuarios:', data.datos);
  });

// Cambiar estado
fetch(`http://localhost:8000/api/usuarios/${id}/estado`, {
  method: 'PATCH',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({ estado: 'inactivo' })
})
  .then(r => r.json())
  .then(data => console.log('Estado actualizado'));

// Ver auditoría con filtros
fetch('http://localhost:8000/api/auditoria?usuario_id=1&tipo_accion=CREAR', {
  headers: { 'Authorization': `Bearer ${token}` }
})
  .then(r => r.json())
  .then(data => {
    console.log('Total auditoría:', data.total);
    data.datos.forEach(entry => {
      console.log(`${entry.fecha_accion}: ${entry.tipo_accion} en ${entry.tabla_afectada}`);
    });
  });
```

---

## Códigos de Error

| Código | Descripción |
|--------|-------------|
| 201    | Usuario creado exitosamente |
| 200    | Operación exitosa |
| 400    | Validación fallida (email único, formato, etc.) |
| 404    | Usuario o recurso no encontrado |
| 401    | No autenticado |
| 403    | No tiene permisos (no es admin) |
| 500    | Error interno del servidor |

---

## Notas Técnicas

- **Contraseñas:** Hasheadas con BCRYPT (Laravel automático)
- **Paginación:** 15 usuarios por defecto, 20 auditorías por defecto
- **Timestamps:** Tabla usuarios tiene `fecha_creacion`, `ultima_conexion`
- **Relaciones:** User hasMany Auditoria, User belongsTo Rol
- **Transacciones:** Cada operación usa transacción para integridad de datos
- **Filtros de Auditoría:** Case-insensitive para tipo_accion
