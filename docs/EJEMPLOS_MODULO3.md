# 🧪 Ejemplos de Uso - Módulo 3: Productos (Continuación)

## 🔑 Prerequisitos

### 1. Obtener Token de Autenticación
```bash
# Login como administrador
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@pizzeria.com",
    "password": "password"
  }'
```

**Guardar el token devuelto:**
```json
{
  "token": "1|abc123xyz...",
  "user": {...}
}
```

**En los ejemplos siguientes, reemplaza `TU_TOKEN` con el token obtenido.**

---

## 📋 US-013: Categorías de Productos

### 1. Crear Categoría de Pizzas

```bash
curl -X POST http://localhost:8000/api/categorias \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN" \
  -d '{
    "nombre": "Pizzas",
    "descripcion": "Pizzas artesanales hechas en horno de leña",
    "estado": true
  }'
```

**Respuesta esperada:**
```json
{
  "exito": true,
  "mensaje": "Categoría creada exitosamente",
  "datos": {
    "id": 1,
    "nombre": "Pizzas",
    "descripcion": "Pizzas artesanales hechas en horno de leña",
    "estado": true,
    "created_at": "2025-12-29T16:00:00.000000Z",
    "updated_at": "2025-12-29T16:00:00.000000Z"
  }
}
```

---

### 2. Crear Más Categorías

```bash
# Bebidas
curl -X POST http://localhost:8000/api/categorias \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN" \
  -d '{
    "nombre": "Bebidas",
    "descripcion": "Bebidas frías y calientes"
  }'

# Postres
curl -X POST http://localhost:8000/api/categorias \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN" \
  -d '{
    "nombre": "Postres",
    "descripcion": "Postres caseros"
  }'

# Entradas
curl -X POST http://localhost:8000/api/categorias \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN" \
  -d '{
    "nombre": "Entradas",
    "descripcion": "Aperitivos y entradas"
  }'
```

---

### 3. Listar Todas las Categorías

```bash
curl -X GET http://localhost:8000/api/categorias \
  -H "Authorization: Bearer TU_TOKEN"
```

**Respuesta:**
```json
{
  "exito": true,
  "datos": [
    {
      "id": 1,
      "nombre": "Pizzas",
      "descripcion": "Pizzas artesanales hechas en horno de leña",
      "estado": true
    },
    {
      "id": 2,
      "nombre": "Bebidas",
      "descripcion": "Bebidas frías y calientes",
      "estado": true
    }
  ]
}
```

---

### 4. Listar Categorías con Conteo de Productos

```bash
curl -X GET "http://localhost:8000/api/categorias?con_productos=true" \
  -H "Authorization: Bearer TU_TOKEN"
```

**Respuesta:**
```json
{
  "exito": true,
  "datos": [
    {
      "id": 1,
      "nombre": "Pizzas",
      "descripcion": "Pizzas artesanales",
      "estado": true,
      "productos_count": 12
    }
  ]
}
```

---

### 5. Listar Categorías Activas con Productos Incluidos

```bash
curl -X GET "http://localhost:8000/api/categorias?estado=true&incluir_productos=true" \
  -H "Authorization: Bearer TU_TOKEN"
```

---

### 6. Ver Detalle de Categoría

```bash
curl -X GET http://localhost:8000/api/categorias/1 \
  -H "Authorization: Bearer TU_TOKEN"
```

**Respuesta:**
```json
{
  "exito": true,
  "datos": {
    "id": 1,
    "nombre": "Pizzas",
    "descripcion": "Pizzas artesanales",
    "estado": true,
    "productos": [
      {
        "id": 1,
        "nombre": "Pizza Margarita",
        "precio_base": "120.00",
        "stock_disponible": 15,
        "disponible": true,
        "activo": true
      }
    ]
  }
}
```

---

### 7. Actualizar Categoría

```bash
curl -X PUT http://localhost:8000/api/categorias/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN" \
  -d '{
    "nombre": "Pizzas Premium",
    "descripcion": "Pizzas artesanales premium con ingredientes importados"
  }'
```

---

### 8. Desactivar Categoría

```bash
curl -X PUT http://localhost:8000/api/categorias/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN" \
  -d '{
    "estado": false
  }'
```

---

### 9. Ver Estadísticas de Categoría

```bash
curl -X GET http://localhost:8000/api/categorias/1/estadisticas \
  -H "Authorization: Bearer TU_TOKEN"
```

**Respuesta:**
```json
{
  "exito": true,
  "datos": {
    "categoria": "Pizzas",
    "total_productos": 15,
    "productos_activos": 14,
    "productos_disponibles": 12,
    "productos_stock_bajo": 3
  }
}
```

---

### 10. Intentar Eliminar Categoría con Productos

```bash
curl -X DELETE http://localhost:8000/api/categorias/1 \
  -H "Authorization: Bearer TU_TOKEN"
```

**Respuesta (Error esperado):**
```json
{
  "exito": false,
  "mensaje": "No se puede eliminar la categoría porque tiene productos asociados",
  "productos_asociados": 12
}
```

---

### 11. Eliminar Categoría Vacía

```bash
# Primero crear una categoría de prueba
curl -X POST http://localhost:8000/api/categorias \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN" \
  -d '{"nombre": "Categoría Prueba"}'

# Luego eliminarla (asumiendo ID 5)
curl -X DELETE http://localhost:8000/api/categorias/5 \
  -H "Authorization: Bearer TU_TOKEN"
```

**Respuesta:**
```json
{
  "exito": true,
  "mensaje": "Categoría eliminada exitosamente"
}
```

---

## 🍕 US-014: Filtrar por Categoría

### 1. Ver Menú Completo (Sin Filtro)

```bash
curl -X GET http://localhost:8000/api/menu
```

---

### 2. Filtrar Menú por Nombre de Categoría

```bash
# Ver solo pizzas
curl -X GET "http://localhost:8000/api/menu?categoria=pizzas"

# Ver solo bebidas
curl -X GET "http://localhost:8000/api/menu?categoria=bebidas"

# Ver solo postres
curl -X GET "http://localhost:8000/api/menu?categoria=postres"
```

**Respuesta:**
```json
{
  "exito": true,
  "items": [
    {
      "id": 1,
      "nombre": "Pizza Margarita",
      "descripcion": "Tomate, mozzarella y albahaca fresca",
      "precio_base": "120.00",
      "categoria_id": 1,
      "stock_disponible": 15,
      "disponible": true,
      "activo": true,
      "categoria": {
        "id": 1,
        "nombre": "Pizzas",
        "descripcion": "Pizzas artesanales"
      }
    }
  ]
}
```

---

### 3. Filtrar Menú por ID de Categoría

```bash
# Por ID 1 (Pizzas)
curl -X GET "http://localhost:8000/api/menu?categoria=1"

# Por ID 2 (Bebidas)
curl -X GET "http://localhost:8000/api/menu?categoria=2"
```

---

### 4. Listar Productos con Filtro de Categoría

```bash
# Todos los productos de categoría Pizzas
curl -X GET "http://localhost:8000/api/productos?categoria=pizzas" \
  -H "Authorization: Bearer TU_TOKEN"

# Productos activos de categoría Bebidas
curl -X GET "http://localhost:8000/api/productos?categoria=bebidas&activo=true" \
  -H "Authorization: Bearer TU_TOKEN"
```

---

### 5. Combinar Múltiples Filtros

```bash
# Pizzas disponibles y activas
curl -X GET "http://localhost:8000/api/productos?categoria=pizzas&disponible=true&activo=true" \
  -H "Authorization: Bearer TU_TOKEN"

# Bebidas con stock bajo
curl -X GET "http://localhost:8000/api/productos?categoria=bebidas&stock_bajo=true" \
  -H "Authorization: Bearer TU_TOKEN"
```

---

### 6. Buscar Producto por Nombre en Categoría

```bash
# Buscar "hawaiana" en todas las categorías
curl -X GET "http://localhost:8000/api/productos?buscar=hawaiana" \
  -H "Authorization: Bearer TU_TOKEN"

# Buscar "coca" solo en bebidas
curl -X GET "http://localhost:8000/api/productos?categoria=bebidas&buscar=coca" \
  -H "Authorization: Bearer TU_TOKEN"
```

---

## 🚨 US-015: Stock Bajo (Alertas)

### 1. Crear Productos con Stock Bajo para Pruebas

```bash
# Pizza con stock crítico (0)
curl -X POST http://localhost:8000/api/productos \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN" \
  -d '{
    "nombre": "Pizza Hawaiana",
    "descripcion": "Piña, jamón y queso",
    "precio_base": 130.00,
    "categoria_id": 1,
    "stock_disponible": 0,
    "stock_minimo": 5,
    "disponible": true,
    "activo": true
  }'

# Pizza con stock bajo (2 < 10)
curl -X POST http://localhost:8000/api/productos \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN" \
  -d '{
    "nombre": "Pizza Pepperoni",
    "descripcion": "Pepperoni y queso mozzarella",
    "precio_base": 140.00,
    "categoria_id": 1,
    "stock_disponible": 2,
    "stock_minimo": 10,
    "disponible": true,
    "activo": true
  }'

# Bebida con stock bajo
curl -X POST http://localhost:8000/api/productos \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN" \
  -d '{
    "nombre": "Coca Cola 2L",
    "descripcion": "Bebida gaseosa 2 litros",
    "precio_base": 35.00,
    "categoria_id": 2,
    "stock_disponible": 3,
    "stock_minimo": 15,
    "disponible": true,
    "activo": true
  }'
```

---

### 2. Ver Todos los Productos con Stock Bajo

```bash
curl -X GET http://localhost:8000/api/productos/stock-bajo \
  -H "Authorization: Bearer TU_TOKEN"
```

**Respuesta esperada:**
```json
{
  "exito": true,
  "total": 3,
  "productos": [
    {
      "id": 5,
      "nombre": "Pizza Hawaiana",
      "categoria": "Pizzas",
      "stock_disponible": 0,
      "stock_minimo": 5,
      "diferencia": 5,
      "alerta": "CRITICO"
    },
    {
      "id": 8,
      "nombre": "Pizza Pepperoni",
      "categoria": "Pizzas",
      "stock_disponible": 2,
      "stock_minimo": 10,
      "diferencia": 8,
      "alerta": "BAJO"
    },
    {
      "id": 12,
      "nombre": "Coca Cola 2L",
      "categoria": "Bebidas",
      "stock_disponible": 3,
      "stock_minimo": 15,
      "diferencia": 12,
      "alerta": "BAJO"
    }
  ]
}
```

---

### 3. Filtrar Stock Bajo por Categoría

```bash
# Stock bajo solo de Pizzas
curl -X GET "http://localhost:8000/api/productos?categoria=pizzas&stock_bajo=true" \
  -H "Authorization: Bearer TU_TOKEN"

# Stock bajo solo de Bebidas
curl -X GET "http://localhost:8000/api/productos?categoria=bebidas&stock_bajo=true" \
  -H "Authorization: Bearer TU_TOKEN"
```

---

### 4. Actualizar Stock de Producto

```bash
# Reponer stock de Pizza Hawaiana
curl -X PATCH http://localhost:8000/api/productos/5 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN" \
  -d '{
    "stock_disponible": 20
  }'
```

---

### 5. Verificar que Salió de Alertas

```bash
# Ahora debería tener un producto menos en la lista
curl -X GET http://localhost:8000/api/productos/stock-bajo \
  -H "Authorization: Bearer TU_TOKEN"
```

---

### 6. Simular Venta que Baja Stock

```bash
# Reducir stock de un producto
curl -X PATCH http://localhost:8000/api/productos/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN" \
  -d '{
    "stock_disponible": 3
  }'

# Verificar que apareció en alertas
curl -X GET http://localhost:8000/api/productos/stock-bajo \
  -H "Authorization: Bearer TU_TOKEN"
```

---

## 🔄 Flujo Completo de Prueba

### Paso 1: Crear Estructura de Categorías

```bash
# Pizzas
curl -X POST http://localhost:8000/api/categorias \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN" \
  -d '{"nombre": "Pizzas", "descripcion": "Pizzas artesanales"}'

# Bebidas
curl -X POST http://localhost:8000/api/categorias \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN" \
  -d '{"nombre": "Bebidas", "descripcion": "Bebidas frías y calientes"}'

# Postres
curl -X POST http://localhost:8000/api/categorias \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN" \
  -d '{"nombre": "Postres", "descripcion": "Postres caseros"}'
```

---

### Paso 2: Crear Productos en Categorías

```bash
# Pizza 1 - Stock normal
curl -X POST http://localhost:8000/api/productos \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN" \
  -d '{
    "nombre": "Pizza Margarita",
    "descripcion": "Tomate, mozzarella y albahaca",
    "precio_base": 120.00,
    "categoria_id": 1,
    "stock_disponible": 20,
    "stock_minimo": 5
  }'

# Pizza 2 - Stock bajo
curl -X POST http://localhost:8000/api/productos \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN" \
  -d '{
    "nombre": "Pizza Pepperoni",
    "descripcion": "Pepperoni y queso",
    "precio_base": 140.00,
    "categoria_id": 1,
    "stock_disponible": 2,
    "stock_minimo": 10
  }'

# Bebida - Stock crítico
curl -X POST http://localhost:8000/api/productos \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN" \
  -d '{
    "nombre": "Coca Cola 2L",
    "descripcion": "Bebida gaseosa",
    "precio_base": 35.00,
    "categoria_id": 2,
    "stock_disponible": 0,
    "stock_minimo": 15
  }'
```

---

### Paso 3: Probar Filtros

```bash
# Ver todas las categorías
curl -X GET http://localhost:8000/api/categorias \
  -H "Authorization: Bearer TU_TOKEN"

# Ver menú de pizzas
curl -X GET "http://localhost:8000/api/menu?categoria=pizzas"

# Ver productos con stock bajo
curl -X GET http://localhost:8000/api/productos/stock-bajo \
  -H "Authorization: Bearer TU_TOKEN"

# Ver estadísticas de categoría Pizzas
curl -X GET http://localhost:8000/api/categorias/1/estadisticas \
  -H "Authorization: Bearer TU_TOKEN"
```

---

### Paso 4: Actualizar y Verificar

```bash
# Reponer stock de Coca Cola
curl -X PATCH http://localhost:8000/api/productos/3 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN" \
  -d '{"stock_disponible": 20}'

# Verificar alertas actualizadas
curl -X GET http://localhost:8000/api/productos/stock-bajo \
  -H "Authorization: Bearer TU_TOKEN"
```

---

## 📊 Casos de Prueba Específicos

### Caso 1: Categoría no existe en filtro
```bash
curl -X GET "http://localhost:8000/api/menu?categoria=inexistente"
```
**Resultado esperado:** Lista vacía

---

### Caso 2: Filtro case-insensitive
```bash
# Todas estas deberían funcionar igual
curl -X GET "http://localhost:8000/api/menu?categoria=PIZZAS"
curl -X GET "http://localhost:8000/api/menu?categoria=Pizzas"
curl -X GET "http://localhost:8000/api/menu?categoria=pizzas"
```

---

### Caso 3: Producto sin stock mínimo definido
```bash
# Crear producto sin stock_minimo
curl -X POST http://localhost:8000/api/productos \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN" \
  -d '{
    "nombre": "Producto Prueba",
    "precio_base": 50.00,
    "categoria_id": 1,
    "stock_disponible": 0,
    "stock_minimo": 0
  }'

# No debería aparecer en alertas (0 >= 0)
curl -X GET http://localhost:8000/api/productos/stock-bajo \
  -H "Authorization: Bearer TU_TOKEN"
```

---

## 🎯 Verificaciones de Integridad

### Verificar que categorías inactivas no afectan productos
```bash
# Desactivar categoría
curl -X PUT http://localhost:8000/api/categorias/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN" \
  -d '{"estado": false}'

# Los productos siguen apareciendo si son activos
curl -X GET "http://localhost:8000/api/menu?categoria=pizzas"
```

---

### Verificar protección de eliminación
```bash
# Intentar eliminar categoría con productos
curl -X DELETE http://localhost:8000/api/categorias/1 \
  -H "Authorization: Bearer TU_TOKEN"

# Debería devolver error 400
```

---

**Última actualización:** 29 de diciembre, 2025  
**Módulo:** 3 - Productos (Continuación)  
**User Stories:** US-013, US-014, US-015
