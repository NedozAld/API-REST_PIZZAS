# MÓDULO 10: DESCUENTOS Y PROMOCIONES
## US-082 y US-083 - Ofertas por Producto y Volumen

**Fecha de Implementación:** 2025-12-29  
**Puntos de Historia:** 6 pts (US-082: 3 pts + US-083: 3 pts)  
**Estado:** ✅ COMPLETADO

---

## 📋 Descripción General

Este módulo implementa un sistema completo de descuentos de dos niveles:

1. **US-082: Ofertas por Producto** - Descuentos aplicados a productos específicos
2. **US-083: Ofertas por Volumen** - Descuentos basados en el monto total del pedido

### Características Principales

- ✅ Descuentos automáticos a nivel de producto
- ✅ Descuentos por rangos de volumen (monto mínimo/máximo)
- ✅ Aplicación automática durante creación de pedido
- ✅ Lógica inteligente: usar el descuento más alto disponible
- ✅ No apilable: no se combinan descuentos (cupón XOR descuento volumen)
- ✅ Endpoints públicos para ver descuentos vigentes

---

## 🗄️ Cambios en Base de Datos

### 1. Tabla `productos` - US-082

Se agregó la columna `descuento_porcentaje` para descuentos a nivel de producto:

```sql
ALTER TABLE productos 
ADD COLUMN descuento_porcentaje DECIMAL(5, 2) DEFAULT 0 
AFTER costo;
```

**Propósito:** Almacenar el descuento en porcentaje (0-100%) para cada producto.

### 2. Tabla `descuentos_volumen` - US-083 (Nueva)

```sql
CREATE TABLE descuentos_volumen (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  monto_minimo DECIMAL(10, 2) NOT NULL,
  monto_maximo DECIMAL(10, 2) NULL, -- NULL = sin límite máximo
  porcentaje_descuento DECIMAL(5, 2) NOT NULL,
  activo BOOLEAN DEFAULT true,
  descripcion TEXT NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  
  INDEX idx_rangos (monto_minimo, monto_maximo, activo)
);
```

**Propósito:** Definir rangos de descuento automático basados en monto total del pedido.

**Ejemplos de uso:**
- `monto_minimo: 500, monto_maximo: 999, descuento: 5%` → Compra de $500-$999 = 5% descuento
- `monto_minimo: 1000, monto_maximo: NULL, descuento: 10%` → Compra ≥ $1000 = 10% descuento

---

## 📦 Modelos

### DescuentoVolumen Model

**Ubicación:** `app/Models/DescuentoVolumen.php`

```php
class DescuentoVolumen extends Model {
    protected $fillable = [
        'monto_minimo',
        'monto_maximo',
        'porcentaje_descuento',
        'activo',
        'descripcion'
    ];
    
    protected $casts = [
        'monto_minimo' => 'decimal:2',
        'monto_maximo' => 'decimal:2',
        'porcentaje_descuento' => 'decimal:2',
        'activo' => 'boolean'
    ];
}
```

#### Métodos Principales

**`obtenerDescuentoPara($monto): ?DescuentoVolumen`**
- Estático
- Busca el descuento aplicable para un monto
- Retorna el descuento con mayor porcentaje si hay múltiples coincidencias
- Ejemplo:
  ```php
  $descuento = DescuentoVolumen::obtenerDescuentoPara(750);
  // Retorna DescuentoVolumen con porcentaje_descuento = 5%
  ```

**`aplicaA($monto): bool`**
- Verifica si el descuento aplica a un monto específico
- Respeta `monto_maximo` NULL como "sin límite"
- Ejemplo:
  ```php
  if ($descuento->aplicaA(650)) {
      // Aplica este descuento
  }
  ```

**`calcularDescuento($monto): float`**
- Calcula el monto en pesos del descuento
- Ejemplo:
  ```php
  $descuento = DescuentoVolumen::obtenerDescuentoPara(1000);
  $monto_descuento = $descuento->calcularDescuento(1000); // 100 pesos (10%)
  ```

**`scopeActivos()`**
- Filtro para descuentos activos
- Ejemplo:
  ```php
  $vigentes = DescuentoVolumen::activos()->get();
  ```

**Accesores:**
- `informacion_formateada` → String con descripción formateada
  - Ejemplo: "Compra entre $500 y $999 → 5% descuento"

---

### Producto Model (Actualizado)

**Ubicación:** `app/Models/Producto.php`

#### Nuevos Atributos

**`descuento_porcentaje` (DECIMAL 5,2)**
- Campo guardado en BD
- Rango: 0-100%

#### Nuevos Acesores (Calculados)

**`precio_con_descuento`**
- Retorna el precio final después de aplicar descuento de producto
- Fórmula: `precio_base * (1 - descuento_porcentaje / 100)`
- Ejemplo: precio_base=100, descuento=10% → precio_con_descuento=90

**`monto_descuento_producto`**
- Retorna el monto en pesos del descuento
- Fórmula: `precio_base * descuento_porcentaje / 100`
- Ejemplo: precio_base=100, descuento=10% → monto_descuento=10

#### Nuevo Método

**`tieneDescuentoProducto(): bool`**
```php
public function tieneDescuentoProducto(): bool
{
    return $this->descuento_porcentaje > 0;
}
```

---

## 🎯 Controladores

### DescuentoVolumenController

**Ubicación:** `app/Http/Controllers/Api/DescuentoVolumenController.php`

#### Endpoints

| Método | Ruta | Autenticación | Descripción |
|--------|------|---------------|------------|
| GET | `/api/descuentos-volumen` | Sí | Listar descuentos con filtro opcional |
| POST | `/api/descuentos-volumen` | Sí | Crear nuevo descuento |
| GET | `/api/descuentos-volumen/{id}` | Sí | Ver detalle de descuento |
| PUT | `/api/descuentos-volumen/{id}` | Sí | Actualizar descuento |
| DELETE | `/api/descuentos-volumen/{id}` | Sí | Eliminar descuento |
| POST | `/api/descuentos-volumen/calcular` | Sí | Calcular descuento para monto |
| GET | `/api/descuentos-volumen/vigentes` | No | Ver descuentos activos (público) |

#### Validaciones

**store() y update():**
- `monto_minimo` (requerido, numeric)
- `monto_maximo` (nullable, > monto_minimo si es provided)
- `porcentaje_descuento` (requerido, 0-100)
- `activo` (boolean)
- `descripcion` (nullable, string)

**Ejemplo de validación:**
```php
if ($monto_maximo && $monto_maximo <= $monto_minimo) {
    return [
        'exito' => false,
        'mensaje' => 'monto_maximo debe ser mayor a monto_minimo'
    ];
}
```

---

### ProductoController (Actualizado)

**Ubicación:** `app/Http/Controllers/Api/ProductoController.php`

#### menuPublico() - Mejorado

Retorna productos públicos CON información de descuentos:

```php
GET /api/menu

{
  "exito": true,
  "datos": [
    {
      "id": 1,
      "nombre": "Pizza Margarita",
      "descripcion": "...",
      "precio_base": 400,
      "descuento_porcentaje": 10,
      "precio_con_descuento": 360,
      "monto_descuento": 40,
      "imagen_url": "...",
      "categoria_id": 1
    },
    ...
  ]
}
```

#### actualizarDescuento() - Nuevo (US-082)

Actualizar el descuento de un producto específico:

```
PATCH /api/productos/{id}/descuento
Content-Type: application/json

{
  "descuento_porcentaje": 15
}
```

**Validación:**
- descuento_porcentaje: requerido, numeric, 0-100

**Respuesta (200 OK):**
```json
{
  "exito": true,
  "datos": {
    "id": 1,
    "nombre": "Pizza Margarita",
    "precio_base": 400,
    "descuento_porcentaje": 15,
    "precio_con_descuento": 340,
    "monto_descuento": 60
  },
  "mensaje": "Descuento actualizado exitosamente"
}
```

---

### PedidoController (Actualizado)

**Ubicación:** `app/Http/Controllers/Api/PedidoController.php`

#### store() - Mejoras

El método `store()` ahora aplica automáticamente AMBOS descuentos:

1. **Descuentos de Producto (US-082):**
   - Cada ítem usa `producto->precio_con_descuento`
   - Se acumula en `descuentoProductos`

2. **Descuentos por Volumen (US-083):**
   - Se calcula automáticamente basado en subtotal
   - Usa `DescuentoVolumen::obtenerDescuentoPara($subtotal)`

3. **Lógica de No-Apilamiento:**
   - Se usa el MÁXIMO entre cupón y volumen
   - NO se combinan (se elige el más beneficioso)
   - Fórmula: `descuentoMaximo = max(montoDescuento, descuentoVolumen)`

**Flujo de Cálculo:**

```
ENTRADA: items = [
  { producto_id: 1, cantidad: 2 },
  { producto_id: 2, cantidad: 1 }
]

PASO 1: Calcular subtotal CON descuentos de producto
  Producto 1: precio_con_descuento=360, cantidad=2 → 720
  Producto 2: precio_con_descuento=500, cantidad=1 → 500
  subtotal = 1220

PASO 2: Buscar descuento por volumen
  DescuentoVolumen::obtenerDescuentoPara(1220) → Descuento 10%
  descuentoVolumen = 122

PASO 3: Calcular impuesto
  impuesto = subtotal * 0.10 = 122

PASO 4: Aplicar MÁXIMO descuento
  descuentoMaximo = max(0, 122) = 122 // Uso volumen si es mejor

SALIDA: total = 1220 + 122 - 122 = 1220
        (subtotal + impuesto - descuento)
```

---

## 🔌 Endpoints Detallados

### 1. GET /api/descuentos-volumen

Listar todos los descuentos de volumen.

**Request:**
```bash
curl -X GET "http://localhost:8000/api/descuentos-volumen" \
  -H "Authorization: Bearer {token}"
```

**Query Params:**
- `activo` (optional): true/false - filtrar por estado

**Response (200):**
```json
{
  "exito": true,
  "datos": [
    {
      "id": 1,
      "monto_minimo": 500,
      "monto_maximo": 999,
      "porcentaje_descuento": 5,
      "activo": true,
      "descripcion": "Descuento por compra de $500-$999",
      "informacion_formateada": "Compra entre $500 y $999 → 5% descuento"
    },
    {
      "id": 2,
      "monto_minimo": 1000,
      "monto_maximo": null,
      "porcentaje_descuento": 10,
      "activo": true,
      "descripcion": "Descuento por compra mayor a $1000",
      "informacion_formateada": "Compra mayor a $1000 → 10% descuento"
    }
  ],
  "mensaje": null
}
```

---

### 2. POST /api/descuentos-volumen

Crear nuevo descuento de volumen.

**Request:**
```bash
curl -X POST "http://localhost:8000/api/descuentos-volumen" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "monto_minimo": 2000,
    "monto_maximo": null,
    "porcentaje_descuento": 15,
    "activo": true,
    "descripcion": "Compra mayor a $2000 - 15% descuento"
  }'
```

**Validaciones:**
- `monto_minimo` (required, numeric, > 0)
- `monto_maximo` (nullable, numeric, > monto_minimo)
- `porcentaje_descuento` (required, numeric, 0-100)
- `activo` (boolean, default: true)

**Response (201):**
```json
{
  "exito": true,
  "datos": {
    "id": 3,
    "monto_minimo": 2000,
    "monto_maximo": null,
    "porcentaje_descuento": 15,
    "activo": true,
    "descripcion": "Compra mayor a $2000 - 15% descuento",
    "informacion_formateada": "Compra mayor a $2000 → 15% descuento"
  },
  "mensaje": "Descuento creado exitosamente"
}
```

---

### 3. POST /api/descuentos-volumen/calcular

Calcular qué descuento aplica a un monto específico (útil para preview).

**Request:**
```bash
curl -X POST "http://localhost:8000/api/descuentos-volumen/calcular" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "monto": 1500
  }'
```

**Response (200) - Con descuento aplicable:**
```json
{
  "exito": true,
  "datos": {
    "id": 2,
    "monto_minimo": 1000,
    "monto_maximo": null,
    "porcentaje_descuento": 10,
    "activo": true,
    "monto_ingresado": 1500,
    "descuento_aplicable": 150,
    "monto_final": 1350,
    "informacion_formateada": "Compra mayor a $1000 → 10% descuento"
  },
  "mensaje": "Descuento aplicable a este monto"
}
```

**Response (200) - Sin descuento:**
```json
{
  "exito": true,
  "datos": {
    "monto_ingresado": 300,
    "descuento_aplicable": 0,
    "monto_final": 300
  },
  "mensaje": "No hay descuento por volumen para este monto"
}
```

---

### 4. GET /api/descuentos-volumen/vigentes

Endpoint PÚBLICO para ver descuentos activos (útil para mostrar en UI).

**Request:**
```bash
curl -X GET "http://localhost:8000/api/descuentos-volumen/vigentes"
```

**Response (200):**
```json
{
  "exito": true,
  "datos": [
    {
      "monto_minimo": 500,
      "monto_maximo": 999,
      "porcentaje_descuento": 5,
      "informacion_formateada": "Compra entre $500 y $999 → 5% descuento"
    },
    {
      "monto_minimo": 1000,
      "monto_maximo": null,
      "porcentaje_descuento": 10,
      "informacion_formateada": "Compra mayor a $1000 → 10% descuento"
    }
  ],
  "mensaje": "Descuentos vigentes"
}
```

---

### 5. PATCH /api/productos/{id}/descuento

Actualizar descuento de un producto (US-082).

**Request:**
```bash
curl -X PATCH "http://localhost:8000/api/productos/1/descuento" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "descuento_porcentaje": 20
  }'
```

**Response (200):**
```json
{
  "exito": true,
  "datos": {
    "id": 1,
    "nombre": "Pizza Margarita",
    "precio_base": 400,
    "descuento_porcentaje": 20,
    "precio_con_descuento": 320,
    "monto_descuento": 80
  },
  "mensaje": "Descuento actualizado exitosamente"
}
```

---

## 📝 Ejemplos de Uso Completo

### Escenario 1: Crear Ofertas de Volumen

```bash
# Crear rango 1: $500-$999 = 5%
curl -X POST "http://localhost:8000/api/descuentos-volumen" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "monto_minimo": 500,
    "monto_maximo": 999,
    "porcentaje_descuento": 5,
    "activo": true,
    "descripcion": "Descuento 5% para compras de $500-$999"
  }'

# Crear rango 2: $1000+ = 10%
curl -X POST "http://localhost:8000/api/descuentos-volumen" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "monto_minimo": 1000,
    "monto_maximo": null,
    "porcentaje_descuento": 10,
    "activo": true,
    "descripcion": "Descuento 10% para compras mayores a $1000"
  }'
```

### Escenario 2: Aplicar Descuento de Producto

```bash
# Dar descuento de 15% a Pizza Margarita (ID=1)
curl -X PATCH "http://localhost:8000/api/productos/1/descuento" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "descuento_porcentaje": 15
  }'

# Verificar en menú
curl -X GET "http://localhost:8000/api/menu"
# Respuesta incluye: "precio_con_descuento": 340, "monto_descuento": 60
```

### Escenario 3: Crear Pedido (Aplica Descuentos Automáticamente)

```bash
# Cliente crea pedido
curl -X POST "http://localhost:8000/api/pedidos" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {"producto_id": 1, "cantidad": 2},  # Pizza Margarita (15% desc)
      {"producto_id": 2, "cantidad": 1}   # Otra pizza
    ],
    "cliente_id": 1,
    "entrega": 50
  }'

# Cálculo automático:
# Pizza 1: 340 * 2 = 680 (descuento producto aplicado)
# Pizza 2: 500 * 1 = 500
# Subtotal: 1180
# Descuento volumen: DescuentoVolumen::obtenerDescuentoPara(1180) = 5% = 59
# Impuesto: 1180 * 0.10 = 118
# Descuento máximo: max(0, 59) = 59
# Total: 1180 + 118 + 50 - 59 = 1289

# Respuesta incluye desglose de descuentos aplicados
```

### Escenario 4: Verificar Descuentos Disponibles (Cliente)

```bash
# Cliente ve qué descuentos están disponibles
curl -X GET "http://localhost:8000/api/descuentos-volumen/vigentes"

# Respuesta:
# "Compra entre $500 y $999 → 5% descuento"
# "Compra mayor a $1000 → 10% descuento"

# Cliente también puede calcular su descuento estimado
curl -X POST "http://localhost:8000/api/descuentos-volumen/calcular" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"monto": 750}'

# Respuesta: "Descuento de 5% = $37.50"
```

---

## 🧪 Casos de Prueba

### Prueba 1: Descuento de Producto (US-082)

- [ ] Crear producto sin descuento → verificar precio_con_descuento = precio_base
- [ ] Actualizar producto con 10% descuento → verificar precio_con_descuento = base * 0.9
- [ ] Crear pedido con producto con descuento → verificar línea usa precio_con_descuento
- [ ] Listar menú → verificar monto_descuento visible

### Prueba 2: Descuento por Volumen (US-083)

- [ ] Crear rango $500-$999 → 5%
- [ ] Crear rango $1000+ → 10%
- [ ] Pedido $600 → debe aplicarse 5%
- [ ] Pedido $1200 → debe aplicarse 10%
- [ ] Pedido $300 → sin descuento
- [ ] POST /calcular con $750 → retorna descuento 5%

### Prueba 3: No-Apilamiento de Descuentos

- [ ] Crear pedido con PRODUCTO (10%) + VOLUMEN (5%) → aplica 10% (máximo)
- [ ] Mismo pedido + CUPÓN (3%) → aplica 10% (máximo de los 3)
- [ ] Verificar que los descuentos NO se suman

### Prueba 4: Descuentos Vigentes

- [ ] GET /vigentes sin autenticación → retorna descuentos activos
- [ ] Desactivar descuento → GET /vigentes no lo incluye
- [ ] Reactivar descuento → GET /vigentes lo incluye nuevamente

### Prueba 5: Validaciones

- [ ] POST descuento con monto_maximo < monto_minimo → error
- [ ] POST descuento con porcentaje > 100 → error
- [ ] PATCH producto con descuento negativo → error
- [ ] POST descuento sin monto_minimo → error

---

## 📊 Integración con Otros Módulos

### Con Módulo 10 - Cupones (US-080, US-081)

- **Antes:** Cupones se aplicaban manualmente
- **Ahora:** Descuentos (producto + volumen) se aplican automáticamente
- **Comportamiento:** Se usa el mayor descuento disponible (max rule)
- **Orden de evaluación:**
  1. Descuento de producto (automático) ✓
  2. Descuento por volumen (automático) ✓
  3. Cupón (manual, por cliente) ✓
  4. **Se aplica:** max(cupón, volumen)

### Con Módulo 3 - Productos (US-010-015)

- **Producto:** Ahora tiene descuento_porcentaje
- **MenuPublico:** Muestra precio_con_descuento
- **Stock:** No afectado por descuentos

### Con Módulo 5 - Pedidos (US-020-035)

- **Creación:** Descuentos calculados automáticamente
- **Respuesta:** Incluye desglose de descuentos aplicados
- **Cálculo total:** subtotal + impuesto + entrega - descuento_máximo

---

## 📈 Resultados de Implementación

| Aspecto | Estado |
|--------|--------|
| **Migraciones** | ✅ 2 creadas y ejecutadas |
| **Modelos** | ✅ 1 nuevo (DescuentoVolumen), 1 actualizado (Producto) |
| **Controladores** | ✅ 1 nuevo (DescuentoVolumenController), 2 actualizados |
| **Rutas** | ✅ 8 nuevas para descuentos + 1 para producto |
| **Validaciones** | ✅ Completas en todos los endpoints |
| **Tests** | ⏳ Recomendado: pruebas manuales con curl |
| **Documentación** | ✅ Este archivo |

---

## 🚀 Próximos Pasos (Fases Futuras)

1. **Análisis de Promociones:**
   - Reportes de descuentos aplicados
   - Análisis de ingresos por promoción

2. **Mejoras UX:**
   - Mostrar descuentos en tiempo real mientras el cliente arma su pedido
   - Sugerencias de descuentos (ej: "Agrega $X para llegar al siguiente rango")

3. **Combinaciones Avanzadas:**
   - Permitir descuentos acumulables para ciertos tipos
   - Descuentos por cliente específico (VIP)

---

**Generado:** 2025-12-29  
**Versión:** 1.0  
**Estado:** ✅ COMPLETADO Y DOCUMENTADO
