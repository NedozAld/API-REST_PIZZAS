# 🍕 MÓDULO 3: PRODUCTOS - GUÍA DE PRUEBAS API

## Endpoints Implementados

| US | Funcionalidad | Método | Endpoint | Auth |
|---|---|---|---|---|
| US-010 | Crear producto | POST | `/api/productos` | ✅ Bearer Token |
| US-012 | Ver menú público | GET | `/api/menu` | ❌ Público |
| US-011 | Editar precio | PATCH | `/api/productos/{id}/precio` | ✅ Bearer Token |
| - | Editar producto completo | PATCH | `/api/productos/{id}` | ✅ Bearer Token |

---

## 🔑 Prerequisitos

### 1. Obtener Token de Autenticación

**Endpoint:** `POST /api/auth/login`

```json
{
  "email": "admin@lapizzeria.ec",
  "password": "Admin@123"
}
```

**Respuesta:**
```json
{
  "exito": true,
  "mensaje": "Login exitoso",
  "usuario": { ... },
  "token": "1|abc123xyz..."
}
```

> **Importante:** Guarda el `token` para usarlo en los headers de las peticiones protegidas.

### 2. Crear Categorías (si no existen)

Primero necesitas categorías en tu base de datos. Ejecuta en PostgreSQL:

```sql
INSERT INTO categorias (nombre, descripcion, estado, created_at, updated_at) VALUES
('Pizzas', 'Pizzas artesanales', true, NOW(), NOW()),
('Bebidas', 'Bebidas frías y calientes', true, NOW(), NOW()),
('Postres', 'Postres caseros', true, NOW(), NOW()),
('Entradas', 'Entradas para compartir', true, NOW(), NOW());
```

O usa el endpoint si lo tienes implementado.

---

## 📋 US-010: Crear Producto

### Request

**Método:** `POST`  
**URL:** `http://127.0.0.1:8000/api/productos`

**Headers:**
```
Authorization: Bearer 1|abc123xyz...
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
  "nombre": "Pizza Margarita",
  "descripcion": "Pizza clásica con mozzarella, tomate y albahaca fresca",
  "precio_base": 7.50,
  "categoria_id": 1,
  "stock_disponible": 20,
  "stock_minimo": 5,
  "disponible": true,
  "imagen_url": "https://example.com/images/margarita.jpg",
  "costo": 4.10,
  "activo": true
}
```

**Campos Opcionales:**
- `descripcion`: null permitido
- `stock_disponible`: default 0
- `stock_minimo`: default 0
- `disponible`: default true
- `imagen_url`: null permitido
- `costo`: null permitido
- `activo`: default true

### Respuesta Exitosa (201)

```json
{
  "exito": true,
  "mensaje": "Producto creado exitosamente",
  "producto": {
    "id": 1,
    "nombre": "Pizza Margarita",
    "descripcion": "Pizza clásica con mozzarella, tomate y albahaca fresca",
    "precio_base": "7.50",
    "categoria_id": 1,
    "stock_disponible": 20,
    "stock_minimo": 5,
    "disponible": true,
    "imagen_url": "https://example.com/images/margarita.jpg",
    "costo": "4.10",
    "activo": true,
    "created_at": "2025-12-25T10:30:00.000000Z",
    "updated_at": "2025-12-25T10:30:00.000000Z",
    "categoria": {
      "id": 1,
      "nombre": "Pizzas",
      "descripcion": "Pizzas artesanales",
      "estado": true,
      "created_at": "2025-12-25T10:00:00.000000Z",
      "updated_at": "2025-12-25T10:00:00.000000Z"
    }
  }
}
```

### Errores Comunes

**401 Unauthorized:**
```json
{
  "message": "Unauthenticated."
}
```
→ Token inválido o no proporcionado.

**422 Validation Error:**
```json
{
  "message": "Ya existe un producto con ese nombre",
  "errors": {
    "nombre": ["Ya existe un producto con ese nombre"]
  }
}
```

**Validaciones:**
- ✓ `nombre`: requerido, único, máx 150 caracteres
- ✓ `precio_base`: requerido, numérico, ≥ 0
- ✓ `categoria_id`: requerido, debe existir en tabla `categorias`
- ✓ `stock_disponible`: entero, ≥ 0
- ✓ `imagen_url`: URL válida, máx 500 caracteres

---

## 🍽️ US-012: Ver Menú Público

### Request

**Método:** `GET`  
**URL:** `http://127.0.0.1:8000/api/menu`

**Headers:**
```
Accept: application/json
```

> **No requiere autenticación** - es un endpoint público

### Respuesta Exitosa (200)

```json
{
  "exito": true,
  "items": [
    {
      "id": 1,
      "nombre": "Pizza Margarita",
      "descripcion": "Pizza clásica con mozzarella, tomate y albahaca fresca",
      "precio_base": "7.50",
      "categoria_id": 1,
      "stock_disponible": 20,
      "stock_minimo": 5,
      "disponible": true,
      "imagen_url": "https://example.com/images/margarita.jpg",
      "costo": "4.10",
      "activo": true,
      "created_at": "2025-12-25T10:30:00.000000Z",
      "updated_at": "2025-12-25T10:30:00.000000Z",
      "categoria": {
        "id": 1,
        "nombre": "Pizzas",
        "descripcion": "Pizzas artesanales",
        "estado": true
      }
    },
    {
      "id": 2,
      "nombre": "Coca Cola",
      "descripcion": "Bebida refrescante 500ml",
      "precio_base": "1.50",
      "categoria_id": 2,
      "disponible": true,
      "activo": true,
      "categoria": {
        "id": 2,
        "nombre": "Bebidas"
      }
    }
  ]
}
```

**Filtros Aplicados Automáticamente:**
- Solo productos con `disponible = true`
- Solo productos con `activo = true`
- Ordenados por `categoria_id` y luego por `nombre`

**Si no hay productos:**
```json
{
  "exito": true,
  "items": []
}
```

---

## 💰 US-011: Editar Precio

### Request

**Método:** `PATCH`  
**URL:** `http://127.0.0.1:8000/api/productos/{id}/precio`  
**Ejemplo:** `http://127.0.0.1:8000/api/productos/1/precio`

**Headers:**
```
Authorization: Bearer 1|abc123xyz...
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
  "precio_base": 8.25
}
```

### Respuesta Exitosa (200)

```json
{
  "exito": true,
  "mensaje": "Precio actualizado exitosamente",
  "producto": {
    "id": 1,
    "nombre": "Pizza Margarita",
    "descripcion": "Pizza clásica con mozzarella, tomate y albahaca fresca",
    "precio_base": "8.25",
    "categoria_id": 1,
    "stock_disponible": 20,
    "stock_minimo": 5,
    "disponible": true,
    "imagen_url": "https://example.com/images/margarita.jpg",
    "costo": "4.10",
    "activo": true,
    "created_at": "2025-12-25T10:30:00.000000Z",
    "updated_at": "2025-12-25T11:45:00.000000Z"
  }
}
```

### Errores Comunes

**404 Not Found:**
```json
{
  "exito": false,
  "mensaje": "Producto no encontrado"
}
```

**422 Validation Error:**
```json
{
  "message": "El precio debe ser numérico",
  "errors": {
    "precio_base": ["El precio debe ser numérico"]
  }
}
```

**Validaciones:**
- ✓ `precio_base`: requerido, numérico, ≥ 0

---

## 📝 Editar Producto Completo

### Request

**Método:** `PATCH`  
**URL:** `http://127.0.0.1:8000/api/productos/{id}`  
**Ejemplo:** `http://127.0.0.1:8000/api/productos/1`

**Headers:**
```
Authorization: Bearer 1|abc123xyz...
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
  "nombre": "Pizza Margarita Premium",
  "descripcion": "Pizza con ingredientes premium",
  "precio_base": 9.50,
  "categoria_id": 1,
  "stock_disponible": 15,
  "stock_minimo": 3,
  "disponible": true,
  "imagen_url": "https://example.com/margarita-premium.jpg",
  "costo": 5.50,
  "activo": true
}
```

> **Nota:** Todos los campos son opcionales. Solo envía los campos que quieres actualizar.

**Ejemplo - Solo actualizar nombre y precio:**
```json
{
  "nombre": "Pizza Margarita Especial",
  "precio_base": 8.75
}
```

### Respuesta Exitosa (200)

```json
{
  "exito": true,
  "mensaje": "Producto actualizado exitosamente",
  "producto": {
    "id": 1,
    "nombre": "Pizza Margarita Premium",
    "descripcion": "Pizza con ingredientes premium",
    "precio_base": "9.50",
    "categoria_id": 1,
    "stock_disponible": 15,
    "stock_minimo": 3,
    "disponible": true,
    "imagen_url": "https://example.com/margarita-premium.jpg",
    "costo": "5.50",
    "activo": true,
    "created_at": "2025-12-25T10:30:00.000000Z",
    "updated_at": "2025-12-25T12:15:00.000000Z",
    "categoria": {
      "id": 1,
      "nombre": "Pizzas",
      "descripcion": "Pizzas artesanales",
      "estado": true
    }
  }
}
```

### Errores Comunes

**404 Not Found:**
```json
{
  "exito": false,
  "mensaje": "Producto no encontrado"
}
```

**422 Validation Error:**
```json
{
  "message": "Ya existe un producto con ese nombre",
  "errors": {
    "nombre": ["Ya existe un producto con ese nombre"]
  }
}
```

**Validaciones:**
- ✓ `nombre`: único (excepto el mismo producto), máx 150 caracteres
- ✓ `precio_base`: numérico, ≥ 0
- ✓ `categoria_id`: debe existir en tabla `categorias`
- ✓ `stock_disponible`: entero, ≥ 0
- ✓ `stock_minimo`: entero, ≥ 0
- ✓ `imagen_url`: URL válida, máx 500 caracteres
- ✓ `costo`: numérico, ≥ 0

---

## 🧪 Casos de Prueba Completos

### Flujo 1: Crear y Ver en Menú

1. **Login** → Obtener token
2. **POST** `/api/productos` → Crear producto con `disponible=true`, `activo=true`
3. **GET** `/api/menu` → Verificar que aparece en el menú
4. **Resultado esperado:** El producto debe estar en la lista del menú

### Flujo 2: Producto No Disponible

1. **POST** `/api/productos` → Crear producto con `disponible=false`
2. **GET** `/api/menu` → Verificar que NO aparece
3. **Resultado esperado:** El producto no debe estar en el menú público

### Flujo 3: Actualizar Precio

1. **POST** `/api/productos` → Crear con precio 7.50
2. **GET** `/api/menu` → Verificar precio inicial
3. **PATCH** `/api/productos/{id}/precio` → Cambiar a 8.99
4. **GET** `/api/menu` → Verificar precio actualizado
5. **Resultado esperado:** El menú debe mostrar el nuevo precio

### Flujo 4: Validaciones

1. **POST** `/api/productos` con `nombre` duplicado → 422
2. **POST** `/api/productos` con `categoria_id` inexistente → 422
3. **POST** `/api/productos` con `precio_base` negativo → 422
4. **PATCH** `/api/productos/9999/precio` (ID inexistente) → 404
5. **Resultado esperado:** Errores de validación apropiados

---

## 🔧 Ejemplos Insomnia/Postman

### Configurar Variables de Entorno

**Variable `base_url`:** `http://127.0.0.1:8000`  
**Variable `token`:** (Se obtiene del login)

### Request 1: Login y Guardar Token

```
POST {{base_url}}/api/auth/login
Content-Type: application/json

{
  "email": "admin@lapizzeria.ec",
  "password": "Admin@123"
}
```

Después del login, extrae el `token` y guárdalo en la variable de entorno `token`.

### Request 2: Crear Producto

```
POST {{base_url}}/api/productos
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "nombre": "Pizza Pepperoni",
  "descripcion": "Con extra pepperoni y queso",
  "precio_base": 9.50,
  "categoria_id": 1,
  "stock_disponible": 15,
  "disponible": true
}
```

### Request 3: Ver Menú

```
GET {{base_url}}/api/menu
Accept: application/json
```

### Request 4: Actualizar Precio

```
PATCH {{base_url}}/api/productos/1/precio
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "precio_base": 10.99
}
```

### Request 5: Actualizar Producto Completo

```
PATCH {{base_url}}/api/productos/1
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "nombre": "Pizza Margarita Premium",
  "precio_base": 9.99,
  "stock_disponible": 12,
  "disponible": true
}
```

---

## 📊 Datos de Prueba Sugeridos

### Productos para Insertar

```json
// Pizza 1
{
  "nombre": "Pizza Hawaiana",
  "descripcion": "Piña y jamón",
  "precio_base": 8.50,
  "categoria_id": 1,
  "stock_disponible": 10,
  "disponible": true
}

// Pizza 2
{
  "nombre": "Pizza Vegetariana",
  "descripcion": "Con vegetales frescos",
  "precio_base": 7.00,
  "categoria_id": 1,
  "stock_disponible": 8,
  "disponible": true
}

// Bebida
{
  "nombre": "Jugo de Naranja Natural",
  "precio_base": 2.50,
  "categoria_id": 2,
  "stock_disponible": 20,
  "disponible": true
}

// Postre
{
  "nombre": "Tiramisú",
  "descripcion": "Postre italiano clásico",
  "precio_base": 4.50,
  "categoria_id": 3,
  "stock_disponible": 5,
  "disponible": true
}
```

---

## ✅ Checklist de Validación

- [ ] Login funciona y devuelve token
- [ ] Crear producto con todos los campos → 201
- [ ] Crear producto solo con campos requeridos → 201
- [ ] Crear producto con nombre duplicado → 422
- [ ] Crear producto sin token → 401
- [ ] Ver menú público sin token → 200
- [ ] Menú solo muestra productos disponibles y activos
- [ ] Actualizar precio con token válido → 200
- [ ] Actualizar precio de producto inexistente → 404
- [ ] Actualizar precio sin token → 401
- [ ] Precio actualizado se refleja en el menú
- [ ] Actualizar producto completo (varios campos) → 200
- [ ] Actualizar solo nombre → 200
- [ ] Actualizar con nombre duplicado → 422
- [ ] Cambios se reflejan en el menú público

---

## 🚀 Comandos Rápidos PowerShell

### Obtener Token
```powershell
$response = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/auth/login" -Method Post -ContentType "application/json" -Body '{"email":"admin@lapizzeria.ec","password":"Admin@123"}'
$token = $response.token
Write-Output "Token: $token"
```

### Crear Producto
```powershell
$headers = @{
    "Authorization" = "Bearer $token"
    "Content-Type" = "application/json"
}
$body = @{
    nombre = "Pizza Margarita"
    precio_base = 7.50
    categoria_id = 1
    disponible = $true
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/productos" -Method Post -Headers $headers -Body $body
```

### Ver Menú
```powershell
Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/menu" -Method Get
```

### Actualizar Precio
```powershell
$headers = @{
    "Authorization" = "Bearer $token"
    "Content-Type" = "application/json"
}
$body = '{"precio_base": 8.99}'

Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/productos/1/precio" -Method Patch -Headers $headers -Body $body
```

### Actualizar Producto Completo
```powershell
$headers = @{
    "Authorization" = "Bearer $token"
    "Content-Type" = "application/json"
}
$body = @{
    nombre = "Pizza Margarita Premium"
    precio_base = 9.99
    stock_disponible = 12
    disponible = $true
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/productos/1" -Method Patch -Headers $headers -Body $body
```

---

## 🐛 Troubleshooting

### Error: "Unauthenticated"
- Verifica que el token esté en el header `Authorization: Bearer <token>`
- Confirma que el token no haya expirado
- Re-login para obtener un nuevo token

### Error: "categoria_id no existe"
- Verifica que la categoría exista en la tabla `categorias`
- Usa `SELECT * FROM categorias;` en PostgreSQL
- Inserta categorías si es necesario

### Error: "Ya existe un producto con ese nombre"
- Los nombres de productos deben ser únicos
- Cambia el nombre o elimina el producto existente

### Menú vacío
- Verifica que haya productos con `disponible=true` y `activo=true`
- Consulta la base de datos: `SELECT * FROM productos WHERE disponible=true AND activo=true;`

### Servidor no responde
- Verifica que el servidor esté corriendo: `php artisan serve`
- Confirma la URL: `http://127.0.0.1:8000`
- Revisa el log en la terminal del servidor

---

## 📝 Notas Importantes

1. **Tokens Sanctum:** El token no expira por defecto, pero se revoca al hacer logout
2. **Categorías:** Deben existir antes de crear productos
3. **Stock:** No se valida automáticamente en pedidos (módulo futuro)
4. **Precios:** Se almacenan con 2 decimales (`decimal(10,2)`)
5. **Imágenes:** Solo se guarda la URL, no se suben archivos en esta versión

---

**Estado:** ✅ Implementado y listo para pruebas  
**Última actualización:** 25/12/2025
