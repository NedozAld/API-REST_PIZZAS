# Módulo 4: Pedidos (Continuación) - Guía de Pruebas

**Base URL:** `http://localhost:8000/api`  
**Autenticación:** Requiere token Sanctum (clientes y usuarios)

---

## US-026: Marcar Pedido como Entregado ✅

**Endpoint:** `PATCH /api/pedidos/{id}/entregado`

**Auth:** Required

**Body JSON (opcional):**
```json
{
  "fecha_entrega": "2025-12-29",
  "comentario": "Entregado sin problemas"
}
```

**Respuesta 200:**
```json
{
  "exito": true,
  "mensaje": "Pedido marcado como entregado",
  "pedido": {
    "id": 1,
    "numero_pedido": "PED-20251229-123456",
    "estado": "ENTREGADO",
    "fecha_entrega": "2025-12-29T00:00:00.000000Z"
  }
}
```

**Curl:**
```bash
# Sin fecha específica (usa la actual)
curl -X PATCH http://localhost:8000/api/pedidos/1/entregado \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{}'

# Con fecha específica
curl -X PATCH http://localhost:8000/api/pedidos/1/entregado \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "fecha_entrega": "2025-12-29",
    "comentario": "Entregado en buen estado"
  }'
```

**Validaciones:**
- El pedido debe estar en estado CONFIRMADO
- fecha_entrega: opcional, debe ser fecha válida, igual o posterior a hoy

**Features:**
- ✅ Cambia estado a ENTREGADO
- ✅ Registra fecha de entrega
- ✅ Crea notificación automática al cliente
- ✅ Transacción de BD

---

## US-027: Agregar Notas al Pedido ✅

**Endpoint:** `PUT /api/pedidos/{id}/notas`

**Auth:** Required

**Body JSON:**
```json
{
  "notas": "Favor dejar en la puerta, no están en casa. Llevar después de las 5pm"
}
```

**Respuesta 200:**
```json
{
  "exito": true,
  "mensaje": "Notas actualizado exitosamente",
  "pedido": {
    "id": 1,
    "numero_pedido": "PED-20251229-123456",
    "notas": "Favor dejar en la puerta, no están en casa. Llevar después de las 5pm"
  }
}
```

**Curl:**
```bash
curl -X PUT http://localhost:8000/api/pedidos/1/notas \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "notas": "Instrucciones especiales para la entrega"
  }'
```

**Validaciones:**
- notas: opcional, máx 1000 caracteres

**Features:**
- ✅ Actualiza campo notas en pedido
- ✅ Disponible en cualquier estado
- ✅ Permite instrucciones especiales para entrega

---

## US-028: Búsqueda Avanzada de Pedidos ✅

**Endpoint:** `GET /api/pedidos/buscar?q=...&estado=...&cliente_id=...`

**Auth:** Required

**Parámetros Query (todos opcionales):**
```
q:             Búsqueda por número de pedido o nombre/email del cliente
estado:        PENDIENTE, CONFIRMADO, ENTREGADO, CANCELADO
cliente_id:    ID del cliente
fecha_desde:   YYYY-MM-DD
fecha_hasta:   YYYY-MM-DD
precio_min:    Precio mínimo
precio_max:    Precio máximo
```

**Respuesta 200:**
```json
{
  "exito": true,
  "total": 15,
  "por_pagina": 15,
  "pagina_actual": 1,
  "total_paginas": 1,
  "filtros": {
    "buscar": "PED-2025",
    "estado": "CONFIRMADO",
    "cliente_id": null,
    "fecha_desde": null,
    "fecha_hasta": null,
    "precio_min": null,
    "precio_max": null
  },
  "datos": [
    {
      "id": 1,
      "numero_pedido": "PED-20251229-123456",
      "cliente": {...},
      "estado": {...},
      "total": 150.50,
      "fecha_creacion": "2025-12-29T10:30:00.000000Z"
    }
  ]
}
```

**Ejemplos Curl:**

```bash
# Búsqueda simple por número
curl -X GET "http://localhost:8000/api/pedidos/buscar?q=PED-2025" \
  -H "Authorization: Bearer TOKEN"

# Búsqueda por estado
curl -X GET "http://localhost:8000/api/pedidos/buscar?estado=CONFIRMADO" \
  -H "Authorization: Bearer TOKEN"

# Búsqueda por cliente
curl -X GET "http://localhost:8000/api/pedidos/buscar?cliente_id=5" \
  -H "Authorization: Bearer TOKEN"

# Búsqueda por rango de fechas
curl -X GET "http://localhost:8000/api/pedidos/buscar?fecha_desde=2025-12-20&fecha_hasta=2025-12-29" \
  -H "Authorization: Bearer TOKEN"

# Búsqueda por rango de precios
curl -X GET "http://localhost:8000/api/pedidos/buscar?precio_min=100&precio_max=500" \
  -H "Authorization: Bearer TOKEN"

# Búsqueda combinada
curl -X GET "http://localhost:8000/api/pedidos/buscar?q=juan&estado=CONFIRMADO&precio_min=50&precio_max=200" \
  -H "Authorization: Bearer TOKEN"
```

**Validaciones:**
- Búsqueda case-insensitive
- Paginación automática (15 por página)
- Todos los filtros son opcionales

**Features:**
- ✅ Búsqueda por número de pedido
- ✅ Búsqueda por nombre/email del cliente
- ✅ Filtro por estado
- ✅ Filtro por rango de fechas
- ✅ Filtro por rango de precios
- ✅ Múltiples filtros simultáneamente

---

## US-029: Reasumir Pedido (Repetir Último Pedido) ✅

**Endpoint:** `POST /api/pedidos/repetir/{pedido_id}`

**Auth:** Required (Cliente)

**Body:** No requiere body

**Respuesta 201:**
```json
{
  "exito": true,
  "mensaje": "Pedido repetido exitosamente",
  "pedido": {
    "id": 25,
    "numero_pedido": "PED-20251229-654321",
    "total": 150.50,
    "fecha_creacion": "2025-12-29T16:45:00.000000Z",
    "items": 3
  }
}
```

**Curl:**
```bash
# Repetir un pedido anterior (ej: pedido con ID 1)
curl -X POST http://localhost:8000/api/pedidos/repetir/1 \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json"
```

**Validaciones:**
- El pedido debe pertenencer al cliente autenticado
- Se copia toda la información del pedido original
- El stock se reduce nuevamente

**Features:**
- ✅ Crea nuevo pedido con mismos items
- ✅ Copia cantidad y precio unitario
- ✅ Copia montos (impuesto, envío, descuento)
- ✅ Reduce stock nuevamente
- ✅ Crea notificación al cliente
- ✅ Transacción de BD con rollback

---

## US-044: Múltiples Direcciones de Cliente ✅

**Endpoint:** `GET/POST/PUT/DELETE /api/clientes/{cliente_id}/direcciones`

**Auth:** Required

### Listar Direcciones
```bash
GET /api/clientes/{cliente_id}/direcciones

curl -X GET http://localhost:8000/api/clientes/5/direcciones \
  -H "Authorization: Bearer TOKEN"

Respuesta:
{
  "exito": true,
  "total": 2,
  "datos": [
    {
      "id": 1,
      "nombre_direccion": "Casa",
      "calle": "Calle Principal",
      "numero": "123",
      "apartamento": null,
      "ciudad": "Madrid",
      "codigo_postal": "28001",
      "provincia": "Madrid",
      "referencia": "Frente al parque",
      "favorita": true,
      "direccion_completo": "Calle Principal 123, Madrid, Madrid 28001"
    }
  ]
}
```

### Crear Dirección
```bash
POST /api/clientes/{cliente_id}/direcciones

curl -X POST http://localhost:8000/api/clientes/5/direcciones \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre_direccion": "Oficina",
    "calle": "Calle del Trabajo",
    "numero": "456",
    "apartamento": "3B",
    "ciudad": "Barcelona",
    "codigo_postal": "08002",
    "provincia": "Barcelona",
    "referencia": "Edificio azul, lado derecho",
    "favorita": true
  }'

Respuesta:
{
  "exito": true,
  "mensaje": "Dirección agregada exitosamente",
  "direccion": {
    "id": 2,
    "nombre_direccion": "Oficina",
    "calle": "Calle del Trabajo",
    "numero": "456",
    "apartamento": "3B",
    "ciudad": "Barcelona",
    "codigo_postal": "08002",
    "provincia": "Barcelona",
    "referencia": "Edificio azul, lado derecho",
    "favorita": true,
    "direccion_completo": "Calle del Trabajo 456 Apt. 3B, Barcelona, Barcelona 08002"
  }
}
```

### Obtener Dirección Específica
```bash
GET /api/clientes/{cliente_id}/direcciones/{id}

curl -X GET http://localhost:8000/api/clientes/5/direcciones/1 \
  -H "Authorization: Bearer TOKEN"
```

### Actualizar Dirección
```bash
PUT /api/clientes/{cliente_id}/direcciones/{id}

curl -X PUT http://localhost:8000/api/clientes/5/direcciones/1 \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre_direccion": "Casa (Actualizado)",
    "calle": "Calle Principal",
    "numero": "123-A",
    "ciudad": "Madrid"
  }'
```

### Marcar como Favorita
```bash
PATCH /api/clientes/{cliente_id}/direcciones/{id}/favorita

curl -X PATCH http://localhost:8000/api/clientes/5/direcciones/1/favorita \
  -H "Authorization: Bearer TOKEN"
```

### Obtener Dirección Favorita
```bash
GET /api/clientes/{cliente_id}/direcciones/favorita/obtener

curl -X GET http://localhost:8000/api/clientes/5/direcciones/favorita/obtener \
  -H "Authorization: Bearer TOKEN"
```

### Eliminar Dirección (Soft Delete)
```bash
DELETE /api/clientes/{cliente_id}/direcciones/{id}

curl -X DELETE http://localhost:8000/api/clientes/5/direcciones/1 \
  -H "Authorization: Bearer TOKEN"
```

**Validaciones Crear/Actualizar:**
```
nombre_direccion: required, string, max 100
calle:            required, string, max 255
numero:           required, string, max 20
apartamento:      nullable, string, max 20
ciudad:           required, string, max 100
codigo_postal:    required, string, max 20
provincia:        nullable, string, max 100
referencia:       nullable, string, max 500
favorita:         nullable, boolean
```

**Features:**
- ✅ Guardar múltiples direcciones por cliente
- ✅ Marcar dirección como favorita
- ✅ Solo una dirección favorita por cliente
- ✅ Soft delete (marcar como inactiva)
- ✅ Mostrar dirección completa formateada
- ✅ Ordenar por favorita primero
- ✅ Transacciones de BD

---

## Flujo de Ejemplo Completo

```bash
# 1. Cliente crea una dirección
curl -X POST http://localhost:8000/api/clientes/5/direcciones \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre_direccion": "Casa",
    "calle": "Calle Principal",
    "numero": "123",
    "ciudad": "Madrid",
    "codigo_postal": "28001",
    "favorita": true
  }'

# 2. Cliente crea un pedido
curl -X POST http://localhost:8000/api/pedidos \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{...}'

# 3. Admin agrega notas al pedido
curl -X PUT http://localhost:8000/api/pedidos/1/notas \
  -H "Authorization: Bearer TOKEN" \
  -d '{"notas": "Llamar antes de entregar"}'

# 4. Buscar pedidos confirmados
curl -X GET "http://localhost:8000/api/pedidos/buscar?estado=CONFIRMADO" \
  -H "Authorization: Bearer TOKEN"

# 5. Marcar como entregado
curl -X PATCH http://localhost:8000/api/pedidos/1/entregado \
  -H "Authorization: Bearer TOKEN"

# 6. Cliente repite su último pedido
curl -X POST http://localhost:8000/api/pedidos/repetir/1 \
  -H "Authorization: Bearer TOKEN"

# 7. Cliente consulta sus direcciones
curl -X GET http://localhost:8000/api/clientes/5/direcciones \
  -H "Authorization: Bearer TOKEN"

# 8. Obtener dirección favorita
curl -X GET http://localhost:8000/api/clientes/5/direcciones/favorita/obtener \
  -H "Authorization: Bearer TOKEN"
```

---

## Integración con Frontend

```javascript
// Crear dirección
fetch('http://localhost:8000/api/clientes/5/direcciones', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    nombre_direccion: 'Casa',
    calle: 'Calle Principal',
    numero: '123',
    ciudad: 'Madrid',
    codigo_postal: '28001',
    favorita: true
  })
})
.then(r => r.json())
.then(data => console.log('Dirección creada:', data.direccion));

// Listar direcciones
fetch('http://localhost:8000/api/clientes/5/direcciones', {
  headers: { 'Authorization': `Bearer ${token}` }
})
.then(r => r.json())
.then(data => {
  console.log('Total direcciones:', data.total);
  data.datos.forEach(dir => {
    console.log(`${dir.nombre_direccion}: ${dir.direccion_completo}`);
  });
});

// Marcar como favorita
fetch(`http://localhost:8000/api/clientes/5/direcciones/1/favorita`, {
  method: 'PATCH',
  headers: { 'Authorization': `Bearer ${token}` }
})
.then(r => r.json())
.then(data => console.log('Dirección favorita actualizada'));

// Buscar pedidos
fetch('http://localhost:8000/api/pedidos/buscar?q=PED-2025&estado=CONFIRMADO', {
  headers: { 'Authorization': `Bearer ${token}` }
})
.then(r => r.json())
.then(data => {
  console.log('Total encontrados:', data.total);
  data.datos.forEach(pedido => {
    console.log(`${pedido.numero_pedido}: $${pedido.total}`);
  });
});

// Repetir pedido
fetch('http://localhost:8000/api/pedidos/repetir/1', {
  method: 'POST',
  headers: { 'Authorization': `Bearer ${token}` }
})
.then(r => r.json())
.then(data => console.log('Pedido repetido:', data.pedido));

// Marcar como entregado
fetch('http://localhost:8000/api/pedidos/1/entregado', {
  method: 'PATCH',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({ fecha_entrega: '2025-12-29' })
})
.then(r => r.json())
.then(data => console.log('Pedido entregado:', data.pedido));

// Agregar notas
fetch('http://localhost:8000/api/pedidos/1/notas', {
  method: 'PUT',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({ notas: 'Instrucciones especiales' })
})
.then(r => r.json())
.then(data => console.log('Notas agregadas'));
```

---

## Códigos de Error

| Código | Descripción |
|--------|-------------|
| 200    | Operación exitosa |
| 201    | Recurso creado |
| 400    | Validación fallida |
| 403    | Acceso denegado |
| 404    | Recurso no encontrado |
| 500    | Error interno del servidor |

---

## Notas Técnicas

- **Transacciones:** Todas las operaciones de escritura usan transacciones
- **Auditoría:** Cambios de estado se registran en auditoría
- **Notificaciones:** Se crean automáticamente en eventos importantes
- **Soft Delete:** Las direcciones se marcan como inactivas, no se eliminan
- **Favoritas:** Solo una dirección puede ser favorita por cliente
- **Stock:** Se reduce al crear/repetir pedido y se restaura al cancelar
