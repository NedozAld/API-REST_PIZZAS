# 🚀 QUICK REFERENCE - MODULO 10: US-082 Y US-083

**Última Actualización:** 2025-12-29  
**Estado:** ✅ COMPLETADO

---

## ⚡ ENDPOINTS RÁPIDOS

### Descuentos de Producto (US-082)

**Actualizar descuento de un producto:**
```bash
PATCH /api/productos/{id}/descuento
Content-Type: application/json

{
  "descuento_porcentaje": 15
}
```

**Ver descuentos en menú:**
```bash
GET /api/menu
```
Respuesta incluye: `descuento_porcentaje`, `precio_con_descuento`, `monto_descuento`

---

### Descuentos por Volumen (US-083)

**Crear descuento por rango:**
```bash
POST /api/descuentos-volumen
{
  "monto_minimo": 500,
  "monto_maximo": 999,
  "porcentaje_descuento": 5,
  "activo": true,
  "descripcion": "Desc 5% para $500-$999"
}
```

**Crear descuento sin límite máximo:**
```bash
POST /api/descuentos-volumen
{
  "monto_minimo": 2000,
  "monto_maximo": null,
  "porcentaje_descuento": 15,
  "activo": true
}
```

**Listar descuentos:**
```bash
GET /api/descuentos-volumen
```

**Ver descuentos vigentes (PÚBLICO):**
```bash
GET /api/descuentos-volumen/vigentes
# No requiere Authorization
```

**Calcular descuento para monto:**
```bash
POST /api/descuentos-volumen/calcular
{
  "monto": 750
}
```

**Actualizar descuento:**
```bash
PUT /api/descuentos-volumen/{id}
{
  "porcentaje_descuento": 8
}
```

**Eliminar descuento:**
```bash
DELETE /api/descuentos-volumen/{id}
```

---

## 📦 MODELOS

### DescuentoVolumen

```php
$descuento = DescuentoVolumen::obtenerDescuentoPara(750);
// Retorna el descuento aplicable para monto 750

if ($descuento) {
    $monto_desc = $descuento->calcularDescuento(750); // 37.5 (5%)
    echo $descuento->informacion_formateada; // "Compra entre $500 y $999 → 5% descuento"
}

// Filtrar activos
$vigentes = DescuentoVolumen::activos()->get();
```

### Producto

```php
$producto = Producto::find(1);

$producto->precio_con_descuento;      // Precio final con descuento
$producto->monto_descuento_producto;  // Monto en pesos del descuento
$producto->tieneDescuentoProducto();  // true/false

// Actualizar
$producto->descuento_porcentaje = 15;
$producto->save();
```

---

## 💡 EJEMPLOS RÁPIDOS

### Ejemplo 1: Crear promoción de volumen

```bash
curl -X POST "http://localhost:8000/api/descuentos-volumen" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Content-Type: application/json" \
  -d '{
    "monto_minimo": 1000,
    "monto_maximo": null,
    "porcentaje_descuento": 10,
    "activo": true,
    "descripcion": "10% descuento en compras mayores a $1000"
  }'
```

### Ejemplo 2: Poner descuento en pizza específica

```bash
curl -X PATCH "http://localhost:8000/api/productos/5/descuento" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Content-Type: application/json" \
  -d '{"descuento_porcentaje": 20}'
```

### Ejemplo 3: Cliente ve promociones disponibles

```bash
curl -X GET "http://localhost:8000/api/descuentos-volumen/vigentes"

# Respuesta:
# "Compra entre $500 y $999 → 5% descuento"
# "Compra mayor a $1000 → 10% descuento"
```

### Ejemplo 4: Cliente calcula su descuento

```bash
curl -X POST "http://localhost:8000/api/descuentos-volumen/calcular" \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"monto": 750}'

# Respuesta: "Aplica 5% = $37.50 de descuento"
```

---

## 🔧 VALIDACIONES

| Campo | Validación |
|-------|-----------|
| `descuento_porcentaje` | 0-100, decimal(5,2) |
| `monto_minimo` | Required, numeric, > 0 |
| `monto_maximo` | Nullable, > monto_minimo |
| `porcentaje_descuento` | 0-100, decimal(5,2) |
| `activo` | Boolean, default true |

---

## 📊 CÁLCULO DE DESCUENTOS

```
ITEM CON DESCUENTO DE PRODUCTO:
  precio_final = precio_base * (1 - porcentaje / 100)

DESCUENTO POR VOLUMEN:
  Se busca automáticamente en tabla descuentos_volumen
  basado en subtotal

NO-STACKING:
  descuento_aplicado = max(cupón, volumen)
  // Se usa el más alto, NO se suman
```

---

## 🗄️ CAMBIOS BD

**Tabla productos:**
```sql
ALTER TABLE productos ADD descuento_porcentaje DECIMAL(5,2) DEFAULT 0;
```

**Nueva tabla:**
```sql
CREATE TABLE descuentos_volumen (
  id BIGINT PRIMARY KEY,
  monto_minimo DECIMAL(10,2),
  monto_maximo DECIMAL(10,2) NULL,
  porcentaje_descuento DECIMAL(5,2),
  activo BOOLEAN DEFAULT true,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

---

## 📄 DOCUMENTACIÓN

- `docs/MODULO10_US082_US083.md` - Documentación técnica completa
- `IMPLEMENTACION_US082_US083.md` - Resumen de cambios
- `CHECKLIST_TESTING_US082_US083.md` - Guía de testing

---

## ✅ STATUS

- [x] US-082: Ofertas por Producto ✅
- [x] US-083: Ofertas por Volumen ✅
- [x] Migraciones ejecutadas ✅
- [x] Rutas registradas ✅
- [x] Documentación completa ✅

**LISTO PARA TESTING**

---

**Generado:** 2025-12-29 | **Versión:** 1.0 | **Estado:** ✅ COMPLETADO
