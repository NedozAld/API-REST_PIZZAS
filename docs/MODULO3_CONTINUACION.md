# Módulo 3: Productos - Continuación (Fase 4)

## Estado: ✅ COMPLETADO
**Fecha implementación:** 29/12/2025  
**Story Points:** 10 pts  
**Fase:** 4 (Semanas 7-8)

---

## User Stories Implementadas

### ✅ US-013: Categorías Productos (4 pts)
**Como:** Administrador  
**Quiero:** Gestionar categorías de productos  
**Para:** Organizar el menú y facilitar la navegación

**Criterios de aceptación:**
- ✅ Listar todas las categorías
- ✅ Crear nueva categoría
- ✅ Editar categoría existente
- ✅ Eliminar categoría (validando productos asociados)
- ✅ Ver productos por categoría
- ✅ Filtrar categorías por estado
- ✅ Ver estadísticas de categoría

**Endpoints:**
```
GET    /api/categorias                      - Listar categorías
POST   /api/categorias                      - Crear categoría
GET    /api/categorias/{id}                 - Ver detalle
PUT    /api/categorias/{id}                 - Actualizar
DELETE /api/categorias/{id}                 - Eliminar
GET    /api/categorias/{id}/estadisticas    - Estadísticas
```

---

### ✅ US-014: Filtrar por Categoría (3 pts)
**Como:** Cliente/Usuario  
**Quiero:** Filtrar productos por categoría  
**Para:** Encontrar rápidamente lo que busco

**Criterios de aceptación:**
- ✅ Filtrar menú público por categoría
- ✅ Filtrar productos por ID o nombre de categoría
- ✅ Mantener otros filtros (disponible, activo, búsqueda)
- ✅ Respuesta incluye información de categoría

**Endpoints:**
```
GET /api/menu?categoria=pizza              - Menú público filtrado
GET /api/menu?categoria=1                  - Por ID de categoría
GET /api/productos?categoria=bebidas       - Listado completo filtrado
GET /api/productos?categoria=2&activo=true - Múltiples filtros
```

---

### ✅ US-015: Stock Bajo (Alerta) (3 pts)
**Como:** Administrador  
**Quiero:** Recibir alertas de productos con stock bajo  
**Para:** Realizar pedidos a tiempo y evitar quedarse sin inventario

**Criterios de aceptación:**
- ✅ Listar productos donde stock_disponible < stock_minimo
- ✅ Diferenciar entre stock bajo y stock crítico (0)
- ✅ Ordenar por nivel de urgencia (crítico primero)
- ✅ Mostrar diferencia entre stock actual y mínimo
- ✅ Incluir categoría del producto
- ✅ Filtrar productos activos solamente

**Endpoints:**
```
GET /api/productos/stock-bajo              - Listar productos con stock bajo
GET /api/productos?stock_bajo=true         - Filtro en listado general
```

---

## Estructura de Base de Datos

### Tabla: categorias
```sql
CREATE TABLE categorias (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(100) UNIQUE NOT NULL,
    descripcion TEXT NULL,
    estado BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Tabla: productos (campos relevantes)
```sql
CREATE TABLE productos (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(150) UNIQUE NOT NULL,
    categoria_id BIGINT NOT NULL,
    stock_disponible INTEGER DEFAULT 0,
    stock_minimo INTEGER DEFAULT 0,
    disponible BOOLEAN DEFAULT TRUE,
    activo BOOLEAN DEFAULT TRUE,
    -- ... otros campos
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT
);
```

---

## Endpoints Detallados

### 📋 Categorías

#### 1. Listar Categorías
```http
GET /api/categorias
Authorization: Bearer {token}
```

**Query Parameters:**
- `estado` (boolean): Filtrar por estado activo/inactivo
- `con_productos` (boolean): Incluir conteo de productos
- `incluir_productos` (boolean): Incluir lista de productos

**Ejemplos:**
```bash
# Todas las categorías
GET /api/categorias

# Solo activas con conteo
GET /api/categorias?estado=true&con_productos=true

# Con productos incluidos
GET /api/categorias?incluir_productos=true
```

**Response:**
```json
{
  "exito": true,
  "datos": [
    {
      "id": 1,
      "nombre": "Pizzas",
      "descripcion": "Pizzas artesanales",
      "estado": true,
      "productos_count": 12,
      "created_at": "2025-12-29T10:00:00.000000Z",
      "updated_at": "2025-12-29T10:00:00.000000Z"
    }
  ]
}
```

---

#### 2. Crear Categoría
```http
POST /api/categorias
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "nombre": "Pizzas",
  "descripcion": "Pizzas artesanales hechas en horno de leña",
  "estado": true
}
```

**Validaciones:**
- `nombre`: Requerido, máximo 100 caracteres, único
- `descripcion`: Opcional, texto
- `estado`: Opcional, booleano (default: true)

**Response (201):**
```json
{
  "exito": true,
  "mensaje": "Categoría creada exitosamente",
  "datos": {
    "id": 1,
    "nombre": "Pizzas",
    "descripcion": "Pizzas artesanales hechas en horno de leña",
    "estado": true,
    "created_at": "2025-12-29T10:00:00.000000Z",
    "updated_at": "2025-12-29T10:00:00.000000Z"
  }
}
```

---

#### 3. Ver Detalle de Categoría
```http
GET /api/categorias/{id}
Authorization: Bearer {token}
```

**Response:**
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
        "disponible": true
      }
    ]
  }
}
```

---

#### 4. Actualizar Categoría
```http
PUT /api/categorias/{id}
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "nombre": "Pizzas Premium",
  "descripcion": "Nueva descripción",
  "estado": false
}
```

---

#### 5. Eliminar Categoría
```http
DELETE /api/categorias/{id}
Authorization: Bearer {token}
```

**Response (Error si tiene productos):**
```json
{
  "exito": false,
  "mensaje": "No se puede eliminar la categoría porque tiene productos asociados",
  "productos_asociados": 12
}
```

---

#### 6. Estadísticas de Categoría
```http
GET /api/categorias/{id}/estadisticas
Authorization: Bearer {token}
```

**Response:**
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

### 🍕 Productos con Filtros

#### 1. Menú Público Filtrado (US-014)
```http
GET /api/menu?categoria={nombre|id}
```

**Ejemplos:**
```bash
# Por nombre de categoría
GET /api/menu?categoria=pizza
GET /api/menu?categoria=bebidas

# Por ID de categoría
GET /api/menu?categoria=1
GET /api/menu?categoria=2
```

**Response:**
```json
{
  "exito": true,
  "items": [
    {
      "id": 1,
      "nombre": "Pizza Margarita",
      "descripcion": "Tomate, mozzarella y albahaca",
      "precio_base": "120.00",
      "disponible": true,
      "imagen_url": null,
      "categoria": {
        "id": 1,
        "nombre": "Pizzas"
      }
    }
  ]
}
```

---

#### 2. Listar Productos con Filtros
```http
GET /api/productos
Authorization: Bearer {token}
```

**Query Parameters:**
- `categoria` (string|int): Filtrar por nombre o ID de categoría
- `disponible` (boolean): Solo productos disponibles
- `activo` (boolean): Solo productos activos
- `stock_bajo` (boolean): Solo con stock bajo
- `buscar` (string): Buscar por nombre

**Ejemplos:**
```bash
# Productos de categoría Pizza
GET /api/productos?categoria=pizza

# Productos activos y disponibles
GET /api/productos?activo=true&disponible=true

# Productos con stock bajo de categoría Bebidas
GET /api/productos?categoria=bebidas&stock_bajo=true

# Buscar por nombre
GET /api/productos?buscar=margarita
```

**Response:**
```json
{
  "exito": true,
  "total": 5,
  "productos": [
    {
      "id": 1,
      "nombre": "Pizza Margarita",
      "categoria": {
        "id": 1,
        "nombre": "Pizzas"
      },
      "stock_disponible": 10,
      "stock_minimo": 5
    }
  ]
}
```

---

#### 3. Productos con Stock Bajo (US-015)
```http
GET /api/productos/stock-bajo
Authorization: Bearer {token}
```

**Response:**
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
      "stock_minimo": 5,
      "diferencia": 3,
      "alerta": "BAJO"
    },
    {
      "id": 12,
      "nombre": "Coca Cola 2L",
      "categoria": "Bebidas",
      "stock_disponible": 4,
      "stock_minimo": 10,
      "diferencia": 6,
      "alerta": "BAJO"
    }
  ]
}
```

**Niveles de Alerta:**
- `CRITICO`: stock_disponible = 0
- `BAJO`: stock_disponible < stock_minimo
- `NORMAL`: stock_disponible >= stock_minimo

---

## Modelos y Métodos

### Modelo Categoria
```php
class Categoria extends Model
{
    protected $fillable = ['nombre', 'descripcion', 'estado'];
    
    // Relación con productos
    public function productos() {
        return $this->hasMany(Producto::class, 'categoria_id');
    }
}
```

### Modelo Producto
```php
class Producto extends Model
{
    // Relación con categoría
    public function categoria() {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
    
    // Scopes
    public function scopeStockBajo($query) {
        return $query->whereColumn('stock_disponible', '<', 'stock_minimo');
    }
    
    public function scopeDisponibles($query) {
        return $query->where('disponible', true)->where('activo', true);
    }
    
    // Métodos de utilidad
    public function tieneStockCritico(): bool {
        return $this->stock_disponible == 0;
    }
    
    public function tieneStockBajo(): bool {
        return $this->stock_disponible < $this->stock_minimo;
    }
    
    // Atributo calculado
    public function getNivelAlertaAttribute(): string {
        if ($this->stock_disponible == 0) return 'CRITICO';
        if ($this->stock_disponible < $this->stock_minimo) return 'BAJO';
        return 'NORMAL';
    }
}
```

---

## Validaciones

### Crear/Actualizar Categoría
```php
'nombre' => 'required|string|max:100|unique:categorias,nombre',
'descripcion' => 'nullable|string',
'estado' => 'boolean'
```

### Reglas de Negocio
- No se puede eliminar una categoría con productos asociados
- Los productos deben tener una categoría asignada (RESTRICT on delete)
- Las categorías inactivas no afectan la visualización de productos
- El filtro por categoría es case-insensitive (ILIKE)

---

## Casos de Uso

### Caso 1: Crear menú organizado por categorías
```bash
# 1. Crear categorías
POST /api/categorias {"nombre": "Pizzas"}
POST /api/categorias {"nombre": "Bebidas"}
POST /api/categorias {"nombre": "Postres"}

# 2. Crear productos en cada categoría
POST /api/productos {"nombre": "Pizza Margarita", "categoria_id": 1}
POST /api/productos {"nombre": "Coca Cola", "categoria_id": 2}

# 3. Ver menú público filtrado
GET /api/menu?categoria=pizzas
```

---

### Caso 2: Monitorear inventario con alertas
```bash
# Ver todos los productos con stock bajo
GET /api/productos/stock-bajo

# Ver stock bajo de una categoría específica
GET /api/productos?categoria=bebidas&stock_bajo=true

# Ver estadísticas de categoría
GET /api/categorias/2/estadisticas
```

---

### Caso 3: Cliente busca producto específico
```bash
# Ver todas las pizzas disponibles
GET /api/menu?categoria=pizzas

# Buscar producto por nombre
GET /api/productos?buscar=hawaiana&disponible=true
```

---

## Testing Recomendado

### ✅ US-013: Categorías
- Crear categoría válida
- Crear con nombre duplicado → Error
- Listar categorías activas
- Actualizar categoría
- Eliminar categoría vacía → Éxito
- Eliminar categoría con productos → Error
- Ver estadísticas de categoría

### ✅ US-014: Filtrar por Categoría
- Filtrar menú por nombre de categoría
- Filtrar por ID de categoría
- Filtrar con categoría inexistente → Lista vacía
- Combinar filtros (categoría + disponible)
- Case-insensitive en nombre de categoría

### ✅ US-015: Stock Bajo
- Listar productos con stock < mínimo
- Verificar nivel CRITICO (stock = 0)
- Verificar nivel BAJO (stock < mínimo)
- Ordenamiento por urgencia
- Filtrar stock bajo por categoría

---

## Ejemplos de Prueba

### Crear Categorías de Prueba
```bash
curl -X POST http://localhost:8000/api/categorias \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Pizzas",
    "descripcion": "Pizzas artesanales"
  }'

curl -X POST http://localhost:8000/api/categorias \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Bebidas",
    "descripcion": "Bebidas frías y calientes"
  }'
```

### Filtrar Menú por Categoría
```bash
# Por nombre
curl http://localhost:8000/api/menu?categoria=pizzas

# Por ID
curl http://localhost:8000/api/menu?categoria=1
```

### Ver Productos con Stock Bajo
```bash
curl -X GET http://localhost:8000/api/productos/stock-bajo \
  -H "Authorization: Bearer TOKEN"
```

### Simular Stock Bajo
```bash
# Actualizar producto para tener stock bajo
curl -X PATCH http://localhost:8000/api/productos/1 \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "stock_disponible": 2,
    "stock_minimo": 10
  }'

# Verificar en alertas
curl -X GET http://localhost:8000/api/productos/stock-bajo \
  -H "Authorization: Bearer TOKEN"
```

---

## Integración con Otros Módulos

### Con Módulo 2 (Pedidos)
- Al crear pedido, los productos se filtran por categoría
- Se valida stock disponible antes de confirmar pedido
- Alertas de stock se actualizan después de cada pedido

### Con Módulo 7 (Reportes)
- Reportes por categoría de producto
- Productos más vendidos por categoría
- Análisis de stock por categoría

### Con Módulo 10 (Descuentos)
- Futura implementación: Descuentos por categoría
- Ofertas específicas en categorías completas

---

## Archivos Modificados/Creados

### ✅ Controladores
- **Creado:** `app/Http/Controllers/Api/CategoriaController.php`
- **Modificado:** `app/Http/Controllers/Api/ProductoController.php`

### ✅ Modelos
- **Modificado:** `app/Models/Producto.php` (scopes y métodos)
- **Existente:** `app/Models/Categoria.php`

### ✅ Rutas
- **Modificado:** `routes/api.php`

### ✅ Migraciones
- **Existente:** `database/migrations/2025_12_25_011250_create_categorias_productos_tables.php`

---

## Progreso del Módulo

| User Story | Puntos | Estado | Fecha |
|------------|--------|--------|-------|
| US-013: Categorías Productos | 4 pts | ✅ COMPLETADO | 29/12/2025 |
| US-014: Filtrar por Categoría | 3 pts | ✅ COMPLETADO | 29/12/2025 |
| US-015: Stock Bajo (Alerta) | 3 pts | ✅ COMPLETADO | 29/12/2025 |
| **TOTAL MÓDULO 3** | **10 pts** | **✅ COMPLETADO** | |

---

## Notas Importantes

⚠️ **Consideraciones:**
- Las categorías no se pueden eliminar si tienen productos asociados (RESTRICT)
- El filtro por categoría es case-insensitive para mejor UX
- Los productos con stock = 0 se marcan como CRITICO
- Solo se alertan productos activos con stock bajo

💡 **Mejoras Futuras:**
- Dashboard de alertas en tiempo real
- Notificaciones automáticas por email/WhatsApp cuando stock < 5
- Reportes de rotación de inventario por categoría
- Sugerencias automáticas de compra basadas en histórico
- Imágenes para categorías
- Ordenamiento personalizado de categorías

---

**Última actualización:** 29 de diciembre, 2025  
**Responsable:** Equipo de desarrollo Pizzería API  
**Progreso Fase 4:** 19/55 pts (34.5%)
