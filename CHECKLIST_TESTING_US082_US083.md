# 🧪 CHECKLIST DE PRUEBAS - MODULO 10: US-082 Y US-083

**Estado:** Listo para Testing  
**Fecha:** 2025-12-29

---

## ✅ VERIFICACIÓN TÉCNICA

### Base de Datos
- [x] Migración `add_descuento_porcentaje_to_productos` ejecutada
- [x] Migración `create_descuentos_volumen_table` ejecutada
- [x] Columna `descuento_porcentaje` en tabla `productos`
- [x] Tabla `descuentos_volumen` creada con índices

### Modelos
- [x] `DescuentoVolumen.php` creado con métodos
- [x] `Producto.php` actualizado con descuento_porcentaje
- [x] Acesores calculados: `precio_con_descuento`, `monto_descuento_producto`

### Controladores
- [x] `DescuentoVolumenController.php` creado (7 métodos)
- [x] `ProductoController.php` actualizado (menuPublico + actualizarDescuento)
- [x] `PedidoController.php` actualizado (aplicar descuentos automáticos)

### Rutas
- [x] 7 rutas de descuentos-volumen registradas
- [x] 1 ruta de producto descuento registrada (PATCH /productos/{id}/descuento)
- [x] 1 ruta vigentes (pública, sin auth)

---

## 🧪 PRUEBAS FUNCIONALES

### Sección 1: Descuentos de Producto (US-082)

#### Prueba 1.1: Actualizar descuento de producto
```bash
PATCH /api/productos/1/descuento
{
  "descuento_porcentaje": 15
}

EXPECTED:
✓ Status 200
✓ Response include:
  - precio_con_descuento = precio_base * 0.85
  - monto_descuento = precio_base * 0.15
```
- [ ] Completada
- [ ] Error encontrado: ________________

#### Prueba 1.2: Ver descuento en menú público
```bash
GET /api/menu

EXPECTED:
✓ Status 200
✓ Cada producto incluye:
  - descuento_porcentaje
  - precio_con_descuento
  - monto_descuento
```
- [ ] Completada
- [ ] Error encontrado: ________________

#### Prueba 1.3: Validación - descuento fuera de rango
```bash
PATCH /api/productos/1/descuento
{
  "descuento_porcentaje": 150  // Inválido
}

EXPECTED:
✓ Status 422
✓ Error message about 0-100% range
```
- [ ] Completada
- [ ] Error encontrado: ________________

#### Prueba 1.4: Crear pedido con producto con descuento
```bash
POST /api/pedidos
{
  "items": [{"producto_id": 1, "cantidad": 2}],
  "cliente_id": 1
}

EXPECTED:
✓ Status 201
✓ Detalles usan precio_con_descuento (no precio_base)
✓ subtotal = cantidad * precio_con_descuento
```
- [ ] Completada
- [ ] Error encontrado: ________________

---

### Sección 2: Descuentos por Volumen (US-083)

#### Prueba 2.1: Crear rango de descuento
```bash
POST /api/descuentos-volumen
{
  "monto_minimo": 500,
  "monto_maximo": 999,
  "porcentaje_descuento": 5,
  "activo": true,
  "descripcion": "Desc 5% para $500-$999"
}

EXPECTED:
✓ Status 201
✓ Response include created object with id
```
- [ ] Completada
- [ ] Error encontrado: ________________

#### Prueba 2.2: Validación - monto_maximo <= monto_minimo
```bash
POST /api/descuentos-volumen
{
  "monto_minimo": 1000,
  "monto_maximo": 500,  // Inválido
  "porcentaje_descuento": 10
}

EXPECTED:
✓ Status 422
✓ Error message
```
- [ ] Completada
- [ ] Error encontrado: ________________

#### Prueba 2.3: Crear rango sin límite máximo (monto_maximo NULL)
```bash
POST /api/descuentos-volumen
{
  "monto_minimo": 2000,
  "monto_maximo": null,  // Sin límite
  "porcentaje_descuento": 15,
  "activo": true
}

EXPECTED:
✓ Status 201
✓ monto_maximo NULL en respuesta
```
- [ ] Completada
- [ ] Error encontrado: ________________

#### Prueba 2.4: Listar descuentos
```bash
GET /api/descuentos-volumen

EXPECTED:
✓ Status 200
✓ Array de descuentos creados en pruebas anteriores
✓ Cada item include informacion_formateada
```
- [ ] Completada
- [ ] Error encontrado: ________________

#### Prueba 2.5: Ver descuentos vigentes (público, sin auth)
```bash
GET /api/descuentos-volumen/vigentes

EXPECTED:
✓ Status 200
✓ NO requiere Authorization header
✓ Solo descuentos con activo=true
✓ Descuentos con informacion_formateada
```
- [ ] Completada
- [ ] Error encontrado: ________________

#### Prueba 2.6: Calcular descuento para monto
```bash
POST /api/descuentos-volumen/calcular
{
  "monto": 750
}

EXPECTED:
✓ Status 200
✓ Retorna descuento aplicable (5% para este monto)
✓ Include descuento_aplicable y monto_final
```
- [ ] Completada
- [ ] Error encontrado: ________________

#### Prueba 2.7: Calcular con monto sin descuento
```bash
POST /api/descuentos-volumen/calcular
{
  "monto": 200
}

EXPECTED:
✓ Status 200
✓ descuento_aplicable = 0
✓ monto_final = 200
✓ Mensaje indicando sin descuento
```
- [ ] Completada
- [ ] Error encontrado: ________________

#### Prueba 2.8: Actualizar descuento
```bash
PUT /api/descuentos-volumen/1
{
  "porcentaje_descuento": 8,
  "activo": false
}

EXPECTED:
✓ Status 200
✓ Actualiza campos
✓ Retorna descuento actualizado
```
- [ ] Completada
- [ ] Error encontrado: ________________

#### Prueba 2.9: Eliminar descuento
```bash
DELETE /api/descuentos-volumen/1

EXPECTED:
✓ Status 204 o 200
✓ Descuento eliminado
✓ GET /vigentes ya no lo incluye
```
- [ ] Completada
- [ ] Error encontrado: ________________

---

### Sección 3: Integración de Descuentos

#### Prueba 3.1: Pedido con SOLO descuento de producto
```
Setup: 
  - Producto A: precio_base=100, descuento_porcentaje=10
  
Request:
POST /api/pedidos
{
  "items": [{"producto_id": A, "cantidad": 1}],
  "cliente_id": 1
}

EXPECTED:
✓ subtotal = 90 (precio_con_descuento)
✓ descuentoProductos = 10
✓ descuentoVolumen = (basado en 90 si aplica)
✓ total incluye descuentos aplicados
```
- [ ] Completada
- [ ] Error encontrado: ________________

#### Prueba 3.2: Pedido con SOLO descuento por volumen
```
Setup:
  - Producto B: precio_base=500, descuento_porcentaje=0
  - DescuentoVolumen: 500-999 = 5%
  
Request:
POST /api/pedidos
{
  "items": [{"producto_id": B, "cantidad": 1}],
  "cliente_id": 1
}

EXPECTED:
✓ subtotal = 500
✓ descuentoVolumen = 25 (5% de 500)
✓ descuentoProductos = 0
✓ total = 500 + impuesto + entrega - 25
```
- [ ] Completada
- [ ] Error encontrado: ________________

#### Prueba 3.3: Pedido con AMBOS descuentos (no-stacking)
```
Setup:
  - Producto C: precio_base=100, descuento=10%
  - DescuentoVolumen: 100+ = 15%
  
Request:
POST /api/pedidos
{
  "items": [{"producto_id": C, "cantidad": 2}],
  "cliente_id": 1
}

EXPECTED:
✓ subtotal = 180 (usando precio_con_descuento = 90)
✓ descuentoProductos = 20 (10% de 200)
✓ descuentoVolumen = 27 (15% de 180)
✓ Descuento FINAL = max(20, 27) = 27 (NO 47)
✓ NO se apilan los descuentos
```
- [ ] Completada
- [ ] Error encontrado: ________________

#### Prueba 3.4: Pedido con cupón + descuento volumen
```
Setup:
  - Mismo setup anterior
  - Cupón con 10% descuento
  
Pasos:
1. POST /api/pedidos (sin cupón)
2. POST /api/pedidos/{id}/cupon (aplicar cupón)

EXPECTED:
✓ Sin cupón: descuento = max(producto, volumen)
✓ Con cupón: descuento = max(cupón, volumen)
✓ NO se suma cupón + volumen
```
- [ ] Completada
- [ ] Error encontrado: ________________

---

### Sección 4: Validaciones

#### Prueba 4.1: Descuento producto - porcentaje negativo
```bash
PATCH /api/productos/1/descuento
{"descuento_porcentaje": -5}

EXPECTED: ✓ Status 422 (error validación)
```
- [ ] Completada
- [ ] Error encontrado: ________________

#### Prueba 4.2: Volumen - monto_minimo negativo
```bash
POST /api/descuentos-volumen
{
  "monto_minimo": -100,
  "porcentaje_descuento": 10
}

EXPECTED: ✓ Status 422
```
- [ ] Completada
- [ ] Error encontrado: ________________

#### Prueba 4.3: Volumen - porcentaje > 100
```bash
POST /api/descuentos-volumen
{
  "monto_minimo": 500,
  "porcentaje_descuento": 150
}

EXPECTED: ✓ Status 422
```
- [ ] Completada
- [ ] Error encontrado: ________________

#### Prueba 4.4: Volumen - campos requeridos
```bash
POST /api/descuentos-volumen
{
  "monto_minimo": 500
  // Falta porcentaje_descuento
}

EXPECTED: ✓ Status 422
```
- [ ] Completada
- [ ] Error encontrado: ________________

---

### Sección 5: Acceso y Seguridad

#### Prueba 5.1: Endpoints con autenticación
```bash
GET /api/descuentos-volumen
(sin Authorization header)

EXPECTED: ✓ Status 401 Unauthorized
```
- [ ] Completada
- [ ] Error encontrado: ________________

#### Prueba 5.2: Endpoint vigentes SIN autenticación
```bash
GET /api/descuentos-volumen/vigentes
(sin Authorization header)

EXPECTED: ✓ Status 200 (público, sin auth)
```
- [ ] Completada
- [ ] Error encontrado: ________________

---

## 📊 Resumen de Pruebas

| Sección | Total | Completadas | Errores |
|---------|-------|-------------|---------|
| 1. Descuentos Producto | 4 | [ ] | [ ] |
| 2. Descuentos Volumen | 9 | [ ] | [ ] |
| 3. Integración | 4 | [ ] | [ ] |
| 4. Validaciones | 4 | [ ] | [ ] |
| 5. Seguridad | 2 | [ ] | [ ] |
| **TOTAL** | **23** | [ ] | [ ] |

---

## 📝 Notas y Observaciones

```
[Espacio para notas durante testing]




```

---

## ✅ SIGN-OFF

- [ ] Todas las pruebas completadas
- [ ] Cero errores críticos
- [ ] Pronto para deployment

**Probador:** ________________  
**Fecha:** ________________  
**Observaciones:** ________________________________________

---

**Generado:** 2025-12-29  
**Versión:** 1.0  
**Estado:** Listo para Testing
