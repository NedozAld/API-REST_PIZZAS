# Módulo 10: Descuentos y Promociones

## Estado: ✅ EN IMPLEMENTACIÓN
**Fecha actualización:** 29/12/2025  
**Story Points:** 15 pts  
**Fase:** 4 (Semanas 7-8)

---

## User Stories Implementadas

### ✅ US-080: Crear Cupón (4 pts)
**Como:** Administrador  
**Quiero:** Crear cupones de descuento con condiciones específicas  
**Para:** Ofrecer promociones a clientes y aumentar ventas

**Criterios de aceptación:**
- ✅ Crear cupón con código único
- ✅ Definir tipo de descuento (porcentaje o fijo)
- ✅ Establecer valor del descuento
- ✅ Configurar compra mínima (opcional)
- ✅ Establecer descuento máximo para porcentajes (opcional)
- ✅ Definir usos máximos (opcional - ilimitado por defecto)
- ✅ Establecer fecha de inicio y fin
- ✅ Activar/desactivar cupón

**Endpoints:**
```
POST   /api/cupones              - Crear cupón nuevo
GET    /api/cupones              - Listar cupones (con filtros)
GET    /api/cupones/{id}         - Ver detalle de cupón
PUT    /api/cupones/{id}         - Actualizar cupón
DELETE /api/cupones/{id}         - Eliminar cupón
GET    /api/cupones/{id}/estadisticas - Ver estadísticas de uso
```

**Ejemplo Request - Crear Cupón:**
```json
POST /api/cupones
{
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
}
```

**Ejemplo Response:**
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
    "created_at": "2025-12-29T15:00:00.000000Z",
    "updated_at": "2025-12-29T15:00:00.000000Z"
  }
}
```

---

### ✅ US-081: Aplicar Cupón (5 pts)
**Como:** Cliente  
**Quiero:** Aplicar cupones de descuento a mis pedidos  
**Para:** Obtener descuentos y ahorrar dinero

**Criterios de aceptación:**
- ✅ Aplicar cupón a pedido pendiente o confirmado
- ✅ Validar que el cupón existe
- ✅ Verificar que el cupón está activo
- ✅ Validar fechas de vigencia
- ✅ Verificar usos disponibles
- ✅ Validar compra mínima
- ✅ Verificar que el cliente no haya usado el cupón antes
- ✅ Calcular descuento según tipo (porcentaje o fijo)
- ✅ Respetar descuento máximo en porcentajes
- ✅ Actualizar total del pedido
- ✅ Registrar uso del cupón
- ✅ Impedir aplicar múltiples cupones al mismo pedido

**Endpoint:**
```
POST /api/pedidos/{id}/cupon - Aplicar cupón a pedido
POST /api/cupones/validar    - Validar cupón antes de aplicar
```

**Ejemplo Request - Aplicar Cupón:**
```json
POST /api/pedidos/123/cupon
{
  "codigo": "PIZZA20"
}
```

**Ejemplo Response - Éxito:**
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

**Ejemplo Response - Error:**
```json
{
  "exito": false,
  "error": "Este cliente ya ha usado este cupón anteriormente"
}
```

**Validaciones:**
- Cupón no existe → 404
- Cupón inactivo → 400
- Cupón expirado → 400
- Compra no alcanza mínimo → 400
- Cupón agotado (usos máximos) → 400
- Cliente ya lo usó → 400
- Pedido ya tiene cupón → 400
- Pedido no en estado válido → 400

---

### 🔄 US-082: Ofertas por Producto (3 pts)
**Como:** Administrador  
**Quiero:** Aplicar descuentos automáticos a productos específicos  
**Para:** Crear promociones permanentes o temporales en productos

**Criterios de aceptación:**
- [ ] Agregar campo descuento_porcentaje a productos
- [ ] Calcular precio con descuento automáticamente
- [ ] Mostrar precio original y precio con descuento
- [ ] Aplicar descuento en cálculo de pedidos
- [ ] Diferenciar visualmente productos en oferta

**Estado:** PENDIENTE

---

### 🔄 US-083: Ofertas por Volumen (3 pts)
**Como:** Administrador  
**Quiero:** Ofrecer descuentos según el monto total del pedido  
**Para:** Incentivar compras de mayor valor

**Criterios de aceptación:**
- [ ] Crear tabla descuentos_volumen
- [ ] Definir rangos de compra y porcentajes
- [ ] Aplicar automáticamente en pedidos
- [ ] Mostrar descuento aplicado en resumen
- [ ] Combinar con cupones (aplicar el mayor descuento)

**Ejemplos:**
- Compra > $200 → 5% descuento
- Compra > $500 → 10% descuento
- Compra > $1000 → 15% descuento

**Estado:** PENDIENTE

---

## Estructura de Base de Datos

### Tabla: cupones
```sql
CREATE TABLE cupones (
    id BIGSERIAL PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    tipo_descuento VARCHAR(20) NOT NULL, -- 'porcentaje' o 'fijo'
    valor_descuento DECIMAL(10,2) NOT NULL,
    descuento_maximo DECIMAL(10,2) NULL,
    compra_minima DECIMAL(10,2) DEFAULT 0,
    usos_maximos INTEGER NULL,
    usos_actuales INTEGER DEFAULT 0,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Tabla: cupones_clientes
```sql
CREATE TABLE cupones_clientes (
    id BIGSERIAL PRIMARY KEY,
    cupon_id BIGINT NOT NULL,
    cliente_id BIGINT NOT NULL,
    fecha_uso TIMESTAMP NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (cupon_id) REFERENCES cupones(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);
```

### Modificación tabla pedidos
```sql
ALTER TABLE pedidos ADD COLUMN cupon_id BIGINT NULL;
ALTER TABLE pedidos ADD CONSTRAINT fk_pedidos_cupon 
    FOREIGN KEY (cupon_id) REFERENCES cupones(id) ON DELETE SET NULL;
```

---

## Modelos y Controladores

### Archivos creados:
- ✅ `app/Models/Cupon.php`
- ✅ `app/Http/Controllers/CuponController.php`
- ✅ `app/Http/Requests/CrearCuponRequest.php`
- ✅ `app/Http/Requests/ActualizarCuponRequest.php`
- ✅ `app/Http/Requests/AplicarCuponRequest.php`

### Migraciones:
- ✅ `2025_12_25_011300_create_cupones_table.php`
- ✅ `2025_12_25_011310_create_cupones_clientes_table.php`
- ✅ `2025_12_29_150000_add_cupon_id_to_pedidos.php`

---

## Lógica de Negocio

### Validaciones de Cupón:
```php
// En Cupon::esValido($montoCompra)
1. Verificar si está activo
2. Verificar si está dentro del rango de fechas
3. Verificar si tiene usos disponibles
4. Verificar si cumple compra mínima
```

### Cálculo de Descuento:
```php
// En Cupon::calcularDescuento($montoCompra)
if (tipo_descuento === 'porcentaje') {
    descuento = (monto * valor_descuento) / 100
    if (descuento_maximo) {
        descuento = min(descuento, descuento_maximo)
    }
} else {
    descuento = min(valor_descuento, monto)
}
```

### Registro de Uso:
```php
// En Cupon::registrarUso($clienteId)
1. Incrementar usos_actuales
2. Crear registro en cupones_clientes
3. Guardar fecha_uso
```

---

## Filtros y Consultas

### Listar cupones con filtros:
```
GET /api/cupones?activo=true           - Solo activos
GET /api/cupones?vigentes=true         - Solo vigentes
GET /api/cupones?disponibles=true      - Activos, vigentes y con usos
```

### Scopes del modelo:
- `activos()` - Cupones activos
- `vigentes()` - Dentro de rango de fechas
- `disponibles()` - Activos, vigentes y con usos disponibles

---

## Reglas de Validación

### Crear/Actualizar Cupón:
```php
'codigo' => 'required|string|max:50|unique:cupones,codigo',
'descripcion' => 'required|string|max:255',
'tipo_descuento' => 'required|in:porcentaje,fijo',
'valor_descuento' => 'required|numeric|min:0',
'descuento_maximo' => 'nullable|numeric|min:0',
'compra_minima' => 'nullable|numeric|min:0',
'usos_maximos' => 'nullable|integer|min:1',
'fecha_inicio' => 'required|date|after_or_equal:today',
'fecha_fin' => 'required|date|after:fecha_inicio',
'activo' => 'boolean'
```

### Aplicar Cupón:
```php
'codigo' => 'required|string|exists:cupones,codigo'
```

---

## Testing Recomendado

### Casos de prueba US-080:
- ✅ Crear cupón de porcentaje sin límites
- ✅ Crear cupón de porcentaje con descuento máximo
- ✅ Crear cupón de monto fijo
- ✅ Crear cupón con compra mínima
- ✅ Crear cupón con usos limitados
- ✅ Validar código único
- ✅ Validar fechas (inicio < fin)

### Casos de prueba US-081:
- ✅ Aplicar cupón válido
- ✅ Rechazar cupón inactivo
- ✅ Rechazar cupón expirado
- ✅ Rechazar cupón con usos agotados
- ✅ Rechazar si compra no alcanza mínimo
- ✅ Rechazar si cliente ya lo usó
- ✅ Rechazar si pedido ya tiene cupón
- ✅ Calcular descuento porcentaje correctamente
- ✅ Aplicar descuento máximo en porcentajes
- ✅ Calcular descuento fijo correctamente
- ✅ Incrementar usos_actuales
- ✅ Registrar en cupones_clientes

---

## Próximos Pasos

1. **Ejecutar migraciones:**
   ```bash
   php artisan migrate
   ```

2. **Probar endpoints con Postman/Thunder Client:**
   - Crear cupones de prueba
   - Validar cupones
   - Aplicar a pedidos
   - Verificar estadísticas

3. **Implementar US-082 (Ofertas por Producto):**
   - Migración para agregar descuento_porcentaje a productos
   - Actualizar ProductoController
   - Modificar cálculo en PedidoController

4. **Implementar US-083 (Ofertas por Volumen):**
   - Crear tabla descuentos_volumen
   - Implementar lógica en PedidoController
   - Decidir regla de combinación con cupones

5. **Documentación adicional:**
   - Ejemplos de uso en Postman
   - Guía para administradores
   - Manual de promociones

---

## Notas Importantes

⚠️ **Limitaciones actuales:**
- Un pedido solo puede tener un cupón aplicado
- Los cupones se aplican al subtotal antes de impuestos y entrega
- El cliente solo puede usar cada cupón una vez

💡 **Mejoras futuras:**
- Cupones específicos por categoría de producto
- Cupones por primera compra
- Cupones referidos (invita amigo)
- Códigos promocionales generados automáticamente
- Cupones escalonados (descuento progresivo)
- Sistema de puntos/recompensas

---

**Última actualización:** 29 de diciembre, 2025  
**Responsable:** Equipo de desarrollo Pizzería API
