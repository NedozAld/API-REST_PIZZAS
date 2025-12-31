# 🧪 Ejemplos de Uso - Cupones (US-080 y US-081)

## 🔑 Autenticación

Todos los endpoints requieren autenticación con Sanctum. Primero obtén un token:

```bash
# Login como administrador
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@pizzeria.com",
    "password": "password"
  }'
```

**Guardar token de respuesta:**
```json
{
  "token": "1|abc123xyz..."
}
```

---

## 📋 US-080: Crear y Gestionar Cupones

### 1. Crear Cupón de Porcentaje (20% descuento)

```bash
curl -X POST http://localhost:8000/api/cupones \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -d '{
    "codigo": "PIZZA20",
    "descripcion": "20% de descuento en toda la tienda",
    "tipo_descuento": "porcentaje",
    "valor_descuento": 20,
    "descuento_maximo": 50.00,
    "compra_minima": 100.00,
    "usos_maximos": 100,
    "fecha_inicio": "2025-01-01",
    "fecha_fin": "2025-01-31",
    "activo": true
  }'
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Cupón creado exitosamente",
  "data": {
    "id": 1,
    "codigo": "PIZZA20",
    "descripcion": "20% de descuento en toda la tienda",
    "tipo_descuento": "porcentaje",
    "valor_descuento": "20.00",
    "descuento_maximo": "50.00",
    "compra_minima": "100.00",
    "usos_maximos": 100,
    "usos_actuales": 0,
    "fecha_inicio": "2025-01-01",
    "fecha_fin": "2025-01-31",
    "activo": true,
    "created_at": "2025-12-29T15:30:00.000000Z",
    "updated_at": "2025-12-29T15:30:00.000000Z"
  }
}
```

---

### 2. Crear Cupón de Monto Fijo ($30 descuento)

```bash
curl -X POST http://localhost:8000/api/cupones \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -d '{
    "codigo": "ENVIOGRATIS",
    "descripcion": "$30 de descuento - Envío gratis",
    "tipo_descuento": "fijo",
    "valor_descuento": 30,
    "compra_minima": 0,
    "usos_maximos": null,
    "fecha_inicio": "2025-01-01",
    "fecha_fin": "2025-12-31",
    "activo": true
  }'
```

---

### 3. Crear Cupón para Nuevos Clientes (15% sin límites)

```bash
curl -X POST http://localhost:8000/api/cupones \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -d '{
    "codigo": "BIENVENIDA15",
    "descripcion": "15% descuento para nuevos clientes",
    "tipo_descuento": "porcentaje",
    "valor_descuento": 15,
    "descuento_maximo": null,
    "compra_minima": 50.00,
    "usos_maximos": 1000,
    "fecha_inicio": "2025-01-01",
    "fecha_fin": "2025-12-31",
    "activo": true
  }'
```

---

### 4. Listar Todos los Cupones

```bash
curl -X GET http://localhost:8000/api/cupones \
  -H "Authorization: Bearer TU_TOKEN_AQUI"
```

---

### 5. Listar Solo Cupones Activos

```bash
curl -X GET "http://localhost:8000/api/cupones?activo=true" \
  -H "Authorization: Bearer TU_TOKEN_AQUI"
```

---

### 6. Listar Solo Cupones Vigentes

```bash
curl -X GET "http://localhost:8000/api/cupones?vigentes=true" \
  -H "Authorization: Bearer TU_TOKEN_AQUI"
```

---

### 7. Listar Cupones Disponibles (activos + vigentes + con usos)

```bash
curl -X GET "http://localhost:8000/api/cupones?disponibles=true" \
  -H "Authorization: Bearer TU_TOKEN_AQUI"
```

---

### 8. Ver Detalle de un Cupón

```bash
curl -X GET http://localhost:8000/api/cupones/1 \
  -H "Authorization: Bearer TU_TOKEN_AQUI"
```

---

### 9. Actualizar Cupón (Desactivar)

```bash
curl -X PUT http://localhost:8000/api/cupones/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -d '{
    "activo": false
  }'
```

---

### 10. Actualizar Cupón (Extender Fecha)

```bash
curl -X PUT http://localhost:8000/api/cupones/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -d '{
    "fecha_fin": "2025-02-28"
  }'
```

---

### 11. Actualizar Cupón (Aumentar Usos Máximos)

```bash
curl -X PUT http://localhost:8000/api/cupones/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -d '{
    "usos_maximos": 200
  }'
```

---

### 12. Ver Estadísticas de un Cupón

```bash
curl -X GET http://localhost:8000/api/cupones/1/estadisticas \
  -H "Authorization: Bearer TU_TOKEN_AQUI"
```

**Respuesta esperada:**
```json
{
  "success": true,
  "data": {
    "codigo": "PIZZA20",
    "descripcion": "20% de descuento en toda la tienda",
    "usos_totales": 15,
    "usos_maximos": 100,
    "usos_disponibles": 85,
    "porcentaje_uso": 15.00,
    "clientes_unicos": 12,
    "fecha_inicio": "2025-01-01",
    "fecha_fin": "2025-01-31",
    "activo": true,
    "vigente": true
  }
}
```

---

### 13. Eliminar Cupón

```bash
curl -X DELETE http://localhost:8000/api/cupones/1 \
  -H "Authorization: Bearer TU_TOKEN_AQUI"
```

---

## 🎫 US-081: Aplicar Cupones a Pedidos

### 1. Validar Cupón Antes de Aplicar

```bash
curl -X POST http://localhost:8000/api/cupones/validar \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -d '{
    "codigo": "PIZZA20",
    "monto": 250.00,
    "cliente_id": 5
  }'
```

**Respuesta si es válido:**
```json
{
  "success": true,
  "message": "Cupón válido",
  "data": {
    "cupon": {
      "id": 1,
      "codigo": "PIZZA20",
      "descripcion": "20% de descuento en toda la tienda",
      "tipo_descuento": "porcentaje",
      "valor_descuento": "20.00",
      "descuento_maximo": "50.00",
      "compra_minima": "100.00"
    },
    "monto_original": 250.00,
    "descuento": 50.00,
    "monto_final": 200.00,
    "informacion": "Cupón PIZZA20: 20% de descuento (Compra mínima: $100.00) (Máx descuento: $50.00)"
  }
}
```

**Respuesta si no es válido:**
```json
{
  "success": false,
  "message": "El cupón ha expirado"
}
```

---

### 2. Aplicar Cupón a Pedido

```bash
curl -X POST http://localhost:8000/api/pedidos/123/cupon \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -d '{
    "codigo": "PIZZA20"
  }'
```

**Respuesta exitosa:**
```json
{
  "exito": true,
  "mensaje": "Cupón aplicado exitosamente",
  "datos": {
    "pedido_id": 123,
    "cupon": "PIZZA20",
    "descuento_aplicado": 50.00,
    "subtotal": 250.00,
    "total_anterior": 250.00,
    "total_nuevo": 200.00,
    "informacion_cupon": "Cupón PIZZA20: 20% de descuento (Compra mínima: $100.00) (Máx descuento: $50.00)"
  }
}
```

---

### 3. Errores Comunes al Aplicar Cupón

#### Error: Cupón no existe
```bash
# Usar código inexistente
curl -X POST http://localhost:8000/api/pedidos/123/cupon \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -d '{"codigo": "NOEXISTE"}'
```
**Respuesta:**
```json
{
  "exito": false,
  "error": "Cupón no encontrado"
}
```

---

#### Error: Cupón ya usado por el cliente
```bash
# Cliente que ya usó el cupón intenta usarlo de nuevo
curl -X POST http://localhost:8000/api/pedidos/124/cupon \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -d '{"codigo": "PIZZA20"}'
```
**Respuesta:**
```json
{
  "exito": false,
  "error": "Este cliente ya ha usado este cupón anteriormente"
}
```

---

#### Error: Compra no alcanza mínimo
```bash
# Pedido de $80 con cupón que requiere $100 mínimo
curl -X POST http://localhost:8000/api/pedidos/125/cupon \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -d '{"codigo": "PIZZA20"}'
```
**Respuesta:**
```json
{
  "exito": false,
  "error": "La compra debe ser de al menos $100.00"
}
```

---

#### Error: Cupón expirado
```bash
curl -X POST http://localhost:8000/api/pedidos/126/cupon \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -d '{"codigo": "VENCIDO2024"}'
```
**Respuesta:**
```json
{
  "exito": false,
  "error": "El cupón ha expirado"
}
```

---

#### Error: Cupón agotado
```bash
curl -X POST http://localhost:8000/api/pedidos/127/cupon \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -d '{"codigo": "LIMITADO"}'
```
**Respuesta:**
```json
{
  "exito": false,
  "error": "El cupón ha alcanzado su límite de usos"
}
```

---

#### Error: Pedido ya tiene cupón
```bash
curl -X POST http://localhost:8000/api/pedidos/123/cupon \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -d '{"codigo": "OTRO"}'
```
**Respuesta:**
```json
{
  "exito": false,
  "error": "Este pedido ya tiene un cupón aplicado"
}
```

---

## 🧮 Ejemplos de Cálculos

### Ejemplo 1: Descuento Porcentaje con Límite
```
Cupón: 20% descuento, máximo $50
Subtotal: $300
Descuento calculado: $300 * 20% = $60
Descuento aplicado: $50 (por límite máximo)
Total final: $300 - $50 = $250
```

### Ejemplo 2: Descuento Porcentaje sin Límite
```
Cupón: 15% descuento, sin límite
Subtotal: $200
Descuento calculado: $200 * 15% = $30
Descuento aplicado: $30
Total final: $200 - $30 = $170
```

### Ejemplo 3: Descuento Fijo
```
Cupón: $30 descuento fijo
Subtotal: $150
Descuento aplicado: $30
Total final: $150 - $30 = $120
```

### Ejemplo 4: Descuento Fijo Mayor al Subtotal
```
Cupón: $50 descuento fijo
Subtotal: $40
Descuento aplicado: $40 (no puede superar el subtotal)
Total final: $0
```

---

## 🔄 Flujo Completo: Crear Cupón y Aplicarlo

### Paso 1: Crear el cupón
```bash
curl -X POST http://localhost:8000/api/cupones \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN" \
  -d '{
    "codigo": "PROMO2025",
    "descripcion": "Promoción año nuevo 2025",
    "tipo_descuento": "porcentaje",
    "valor_descuento": 25,
    "compra_minima": 150.00,
    "fecha_inicio": "2025-01-01",
    "fecha_fin": "2025-01-15",
    "activo": true
  }'
```

### Paso 2: Crear un pedido
```bash
curl -X POST http://localhost:8000/api/pedidos \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN" \
  -d '{
    "items": [
      {"producto_id": 1, "cantidad": 2, "precio": 50.00},
      {"producto_id": 2, "cantidad": 1, "precio": 80.00}
    ],
    "notas": "Sin cebolla"
  }'
```

### Paso 3: Validar el cupón
```bash
curl -X POST http://localhost:8000/api/cupones/validar \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN" \
  -d '{
    "codigo": "PROMO2025",
    "monto": 180.00,
    "cliente_id": 5
  }'
```

### Paso 4: Aplicar el cupón al pedido
```bash
curl -X POST http://localhost:8000/api/pedidos/128/cupon \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN" \
  -d '{
    "codigo": "PROMO2025"
  }'
```

### Paso 5: Verificar el pedido con descuento
```bash
curl -X GET http://localhost:8000/api/pedidos/128 \
  -H "Authorization: Bearer TU_TOKEN"
```

**Respuesta esperada:**
```json
{
  "exito": true,
  "datos": {
    "id": 128,
    "numero_pedido": "PED-20250101-0128",
    "cliente_id": 5,
    "cupon_id": 1,
    "subtotal": "180.00",
    "monto_descuento": "45.00",
    "total": "135.00",
    "estado": "PENDIENTE",
    "cupon": {
      "id": 1,
      "codigo": "PROMO2025",
      "descripcion": "Promoción año nuevo 2025"
    }
  }
}
```

---

## 📊 Casos de Prueba Recomendados

### ✅ Caso 1: Cupón válido estándar
- Crear cupón activo y vigente
- Aplicar a pedido que cumple requisitos
- Verificar descuento correcto

### ✅ Caso 2: Cupón con compra mínima
- Crear cupón con compra_minima = 100
- Intentar aplicar a pedido de $80 → ERROR
- Aplicar a pedido de $120 → ÉXITO

### ✅ Caso 3: Cupón con descuento máximo
- Crear cupón 20% con máximo $50
- Aplicar a pedido de $300 (20% = $60)
- Verificar descuento limitado a $50

### ✅ Caso 4: Cupón de uso único
- Crear cupón con usos_maximos = 1
- Aplicar primera vez → ÉXITO
- Intentar aplicar segunda vez → ERROR

### ✅ Caso 5: Cliente repite cupón
- Cliente usa cupón en pedido 1 → ÉXITO
- Mismo cliente intenta usar en pedido 2 → ERROR

### ✅ Caso 6: Cupón expirado
- Crear cupón con fecha_fin = ayer
- Intentar aplicar → ERROR

### ✅ Caso 7: Cupón inactivo
- Desactivar cupón (activo = false)
- Intentar aplicar → ERROR

### ✅ Caso 8: Pedido ya con cupón
- Aplicar cupón A a pedido → ÉXITO
- Intentar aplicar cupón B al mismo pedido → ERROR

---

**Última actualización:** 29 de diciembre, 2025  
**Módulo:** 10 - Descuentos y Promociones  
**User Stories:** US-080, US-081
