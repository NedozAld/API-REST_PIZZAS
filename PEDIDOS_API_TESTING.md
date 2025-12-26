# PEDIDOS API - Guía de Testing

## Información General
Endpoints para gestión de pedidos en la pizzería. Incluye creación, confirmación y consulta de estado de pedidos.

---

## Tabla de Endpoints

| Método | Endpoint | Autenticación | Descripción |
|--------|----------|---------------|-------------|
| POST | `/api/pedidos` | Requerida | US-020: Crear nuevo pedido |
| GET | `/api/pedidos/{id}` | Requerida | US-022: Ver estado de un pedido |
| PATCH | `/api/pedidos/{id}/confirmar` | Requerida | US-021: Confirmar pedido manualmente |
| GET | `/api/pedidos` | Requerida | Listar pedidos (con paginación) |

---

## Prerequisitos

### 1. Autenticarse
Primero debes obtener un token de autenticación:

```http
POST http://127.0.0.1:8000/api/auth/login
Content-Type: application/json

{
  "email": "admin@pizzeria.com",
  "password": "Admin123!"
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Login exitoso",
  "data": {
    "token": "1|xyz...",
    "usuario": {...}
  }
}
```

**Guarda el token** para usarlo en las siguientes peticiones con el header:
```
Authorization: Bearer 1|xyz...
```

### 2. Verificar Productos Disponibles
Lista productos activos y disponibles:

```http
GET http://127.0.0.1:8000/api/menu
```

**Respuesta (ejemplo):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nombre": "Pizza Margarita",
      "precio_base": "45.00",
      "stock_disponible": 50,
      "disponible": true
    },
    {
      "id": 2,
      "nombre": "Pizza Pepperoni",
      "precio_base": "55.00",
      "stock_disponible": 30,
      "disponible": true
    }
  ]
}
```

---

## US-020: Crear Pedido

### Endpoint
```
POST /api/pedidos
```

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Body (JSON)
```json
{
  "items": [
    {
      "producto_id": 1,
      "cantidad": 2,
      "notas": "Sin cebolla"
    },
    {
      "producto_id": 2,
      "cantidad": 1
    }
  ],
  "notas": "Entregar en el primer piso",
  "costo_entrega": 15.00,
  "monto_descuento": 5.00
}
```

### Campos Requeridos
- `items` (array): Lista de productos a pedir
  - `producto_id` (integer): ID del producto
  - `cantidad` (integer): Cantidad (mínimo 1)
  - `notas` (string, opcional): Notas específicas del item

### Campos Opcionales
- `notas` (string): Notas generales del pedido
- `costo_entrega` (decimal): Costo de entrega (default: 0)
- `monto_descuento` (decimal): Descuento aplicado (default: 0)

### Respuesta Exitosa (201 Created)
```json
{
  "success": true,
  "message": "Pedido creado exitosamente",
  "data": {
    "pedido": {
      "id": 1,
      "numero_pedido": "PED-20250127-0001",
      "cliente_id": null,
      "subtotal": "145.00",
      "impuesto": "14.50",
      "costo_entrega": "15.00",
      "monto_descuento": "5.00",
      "total": "169.50",
      "estado": "PENDIENTE",
      "notas": "Entregar en el primer piso",
      "created_at": "2025-01-27T10:30:00.000000Z",
      "detalles": [
        {
          "id": 1,
          "producto_id": 1,
          "cantidad": 2,
          "precio_unitario": "45.00",
          "subtotal": "90.00",
          "notas": "Sin cebolla",
          "producto": {
            "id": 1,
            "nombre": "Pizza Margarita"
          }
        },
        {
          "id": 2,
          "producto_id": 2,
          "cantidad": 1,
          "precio_unitario": "55.00",
          "subtotal": "55.00",
          "notas": null,
          "producto": {
            "id": 2,
            "nombre": "Pizza Pepperoni"
          }
        }
      ]
    }
  }
}
```

### Errores Comunes
**Stock insuficiente (422):**
```json
{
  "message": "The items.0.cantidad field is invalid.",
  "errors": {
    "items.0.cantidad": [
      "Stock insuficiente para 'Pizza Margarita'. Disponible: 1, solicitado: 2."
    ]
  }
}
```

**Producto no disponible (422):**
```json
{
  "message": "The items.0.producto_id field is invalid.",
  "errors": {
    "items.0.producto_id": [
      "El producto 'Pizza Hawaiana' no está disponible actualmente."
    ]
  }
}
```

---

## US-022: Ver Estado de Pedido

### Endpoint
```
GET /api/pedidos/{id}
```

### Headers
```
Authorization: Bearer {token}
```

### Ejemplo
```http
GET http://127.0.0.1:8000/api/pedidos/1
Authorization: Bearer 1|xyz...
```

### Respuesta Exitosa (200 OK)
```json
{
  "success": true,
  "data": {
    "pedido": {
      "id": 1,
      "numero_pedido": "PED-20250127-0001",
      "cliente_id": null,
      "subtotal": "145.00",
      "impuesto": "14.50",
      "costo_entrega": "15.00",
      "monto_descuento": "5.00",
      "total": "169.50",
      "estado": "PENDIENTE",
      "notas": "Entregar en el primer piso",
      "fecha_confirmacion": null,
      "created_at": "2025-01-27T10:30:00.000000Z",
      "detalles": [
        {
          "id": 1,
          "cantidad": 2,
          "precio_unitario": "45.00",
          "subtotal": "90.00",
          "producto": {
            "id": 1,
            "nombre": "Pizza Margarita"
          }
        }
      ]
    }
  }
}
```

### Errores
**Pedido no encontrado (404):**
```json
{
  "success": false,
  "message": "Pedido no encontrado"
}
```

**Sin permisos (403):**
```json
{
  "success": false,
  "message": "No tiene permisos para ver este pedido"
}
```

---

## US-021: Confirmar Pedido Manual

### Endpoint
```
PATCH /api/pedidos/{id}/confirmar
```

### Headers
```
Authorization: Bearer {token}
```

### Ejemplo
```http
PATCH http://127.0.0.1:8000/api/pedidos/1/confirmar
Authorization: Bearer 1|xyz...
```

### Respuesta Exitosa (200 OK)
```json
{
  "success": true,
  "message": "Pedido confirmado exitosamente",
  "data": {
    "pedido": {
      "id": 1,
      "numero_pedido": "PED-20250127-0001",
      "estado": "CONFIRMADO",
      "fecha_confirmacion": "2025-01-27T10:35:00.000000Z",
      "metodo_confirmacion": "manual",
      "total": "169.50"
    }
  }
}
```

### Errores
**Estado inválido (400):**
```json
{
  "success": false,
  "message": "Solo se pueden confirmar pedidos en estado PENDIENTE o TICKET_ENVIADO"
}
```

---

## Listar Pedidos (Opcional)

### Endpoint
```
GET /api/pedidos
```

### Headers
```
Authorization: Bearer {token}
```

### Query Parameters
- `estado` (string, opcional): Filtrar por estado (PENDIENTE, CONFIRMADO, etc.)
- `page` (integer, opcional): Número de página (default: 1)

### Ejemplo
```http
GET http://127.0.0.1:8000/api/pedidos?estado=PENDIENTE&page=1
Authorization: Bearer 1|xyz...
```

### Respuesta (200 OK)
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "numero_pedido": "PED-20250127-0001",
        "estado": "PENDIENTE",
        "total": "169.50",
        "created_at": "2025-01-27T10:30:00.000000Z"
      }
    ],
    "per_page": 15,
    "total": 1
  }
}
```

---

## Testing con Insomnia/Postman

### Colección de Requests

#### 1. Login
```
POST http://127.0.0.1:8000/api/auth/login
Content-Type: application/json

{
  "email": "admin@pizzeria.com",
  "password": "Admin123!"
}
```

#### 2. Crear Pedido
```
POST http://127.0.0.1:8000/api/pedidos
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "items": [
    {"producto_id": 1, "cantidad": 2}
  ]
}
```

#### 3. Ver Pedido
```
GET http://127.0.0.1:8000/api/pedidos/1
Authorization: Bearer {{token}}
```

#### 4. Confirmar Pedido
```
PATCH http://127.0.0.1:8000/api/pedidos/1/confirmar
Authorization: Bearer {{token}}
```

---

## Testing con PowerShell

### Script Completo
```powershell
# 1. Login
$loginResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/auth/login" `
  -Method POST `
  -ContentType "application/json" `
  -Body '{"email":"admin@pizzeria.com","password":"Admin123!"}'

$token = $loginResponse.data.token
Write-Host "Token obtenido: $token"

# 2. Ver menú
$menu = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/menu" `
  -Method GET

Write-Host "Productos disponibles:"
$menu.data | Format-Table id, nombre, precio_base, stock_disponible

# 3. Crear pedido
$pedidoBody = @{
  items = @(
    @{producto_id = 1; cantidad = 2; notas = "Sin cebolla"}
  )
  notas = "Entregar en el primer piso"
  costo_entrega = 15.00
} | ConvertTo-Json

$pedidoResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/pedidos" `
  -Method POST `
  -Headers @{Authorization = "Bearer $token"} `
  -ContentType "application/json" `
  -Body $pedidoBody

Write-Host "Pedido creado:"
$pedidoResponse.data.pedido | Format-List numero_pedido, total, estado

$pedidoId = $pedidoResponse.data.pedido.id

# 4. Ver estado del pedido
$estadoPedido = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/pedidos/$pedidoId" `
  -Method GET `
  -Headers @{Authorization = "Bearer $token"}

Write-Host "Estado del pedido:"
$estadoPedido.data.pedido | Format-List numero_pedido, estado, total

# 5. Confirmar pedido
$confirmarResponse = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/pedidos/$pedidoId/confirmar" `
  -Method PATCH `
  -Headers @{Authorization = "Bearer $token"}

Write-Host "Pedido confirmado:"
$confirmarResponse.data.pedido | Format-List numero_pedido, estado, fecha_confirmacion
```

---

## Estados del Pedido

El flujo de estados es:

1. **PENDIENTE** → Estado inicial al crear el pedido
2. **TICKET_ENVIADO** → Ticket enviado a la cocina (automático vía WhatsApp en futuro)
3. **CONFIRMADO** → Pedido confirmado manualmente o por WhatsApp (US-021)
4. **EN_PREPARACION** → En preparación por la cocina
5. **LISTO** → Listo para entrega
6. **EN_ENTREGA** → En camino al cliente
7. **ENTREGADO** → Entregado al cliente
8. **CANCELADO** → Pedido cancelado

---

## Validaciones Automáticas

El sistema valida automáticamente:

- ✅ Producto existe en la base de datos
- ✅ Producto está disponible (`disponible = true`)
- ✅ Producto está activo (`activo = true`)
- ✅ Stock suficiente para la cantidad solicitada
- ✅ Cantidad mínima de 1 unidad
- ✅ Al menos 1 item en el pedido

---

## Troubleshooting

### Problema: "Stock insuficiente"
**Causa:** El producto no tiene suficiente stock disponible.

**Solución:** 
1. Verificar stock actual: `GET /api/menu`
2. Reducir la cantidad solicitada
3. O actualizar el stock del producto (como admin)

### Problema: "Producto no disponible"
**Causa:** El producto tiene `disponible = false` o `activo = false`.

**Solución:**
1. Verificar en el menú público solo muestra productos disponibles
2. Como admin, activar el producto si es necesario

### Problema: "No tiene permisos para ver este pedido"
**Causa:** Usuario cliente intentando ver un pedido de otro cliente.

**Solución:**
- Los clientes solo pueden ver sus propios pedidos
- Los usuarios (trabajadores) pueden ver todos los pedidos

### Problema: "Solo se pueden confirmar pedidos en estado PENDIENTE"
**Causa:** Intentar confirmar un pedido que ya está confirmado o en otro estado.

**Solución:**
- Verificar el estado actual del pedido con `GET /api/pedidos/{id}`
- Solo pedidos en estado PENDIENTE o TICKET_ENVIADO pueden confirmarse

---

## Checklist de Testing

- [ ] Login exitoso y token obtenido
- [ ] Ver menú público (productos disponibles)
- [ ] Crear pedido con 1 producto
- [ ] Crear pedido con múltiples productos
- [ ] Crear pedido con notas personalizadas
- [ ] Verificar cálculo correcto de totales (subtotal + impuesto + entrega - descuento)
- [ ] Ver estado del pedido creado
- [ ] Confirmar pedido manualmente
- [ ] Listar pedidos con paginación
- [ ] Filtrar pedidos por estado
- [ ] Validación: producto inexistente (debe fallar)
- [ ] Validación: stock insuficiente (debe fallar)
- [ ] Validación: producto no disponible (debe fallar)
- [ ] Verificar reducción automática de stock al crear pedido

---

## Cálculo de Totales

El sistema calcula automáticamente:

1. **Subtotal**: Suma de (precio_unitario × cantidad) de cada item
2. **Impuesto**: 10% del subtotal
3. **Total**: subtotal + impuesto + costo_entrega - monto_descuento

**Ejemplo:**
- Item 1: 2 × $45.00 = $90.00
- Item 2: 1 × $55.00 = $55.00
- **Subtotal**: $145.00
- **Impuesto** (10%): $14.50
- **Costo entrega**: $15.00
- **Descuento**: -$5.00
- **TOTAL**: $169.50

---

## Notas Adicionales

- Los pedidos se numeran automáticamente: `PED-YYYYMMDD-####`
- El stock se reduce automáticamente al crear el pedido
- Los clientes solo ven sus propios pedidos
- Los trabajadores (usuarios) ven todos los pedidos
- El impuesto está configurado al 10% (puede cambiar en configuración)
