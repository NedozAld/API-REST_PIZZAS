# Módulo 7: Reportes y Analytics - Guía de Pruebas

Base URL: `http://localhost:8000/api`  
Autenticación: Todas las rutas requieren token Sanctum (excepto webhook)

---

## US-050: Dashboard Principal ✅

**Endpoint:** `GET /api/dashboard` (alias también: `GET /api/reportes/dashboard`)

**Auth:** Required (Sanctum)

**Respuesta 200:**
```json
{
  "exito": true,
  "kpis": {
    "pedidos_hoy": 5,
    "ingreso_hoy": 150.50,
    "pedidos_mes": 45,
    "ingreso_mes": 3250.75,
    "clientes_nuevos": 8,
    "pedidos_pendientes": 3,
    "pedidos_confirmados": 42,
    "pedidos_cancelados": 0,
    "tasa_conversion": 93.33,
    "producto_mas_vendido": {
      "id": 1,
      "nombre": "Pizza Margherita",
      "cantidad_vendida": 120
    }
  },
  "top_productos": [
    {
      "producto_id": 1,
      "producto_nombre": "Pizza Margherita",
      "cantidad_vendida": 120,
      "ingresos": 2700.00
    }
  ],
  "top_clientes": [
    {
      "cliente_id": 1,
      "nombre": "Juan Pérez",
      "email": "juan@example.com",
      "pedidos_totales": 12,
      "gasto_total": 450.50
    }
  ]
}
```

**Curl:**
```bash
curl -X GET http://localhost:8000/api/dashboard \
  -H "Authorization: Bearer TOKEN"
```

**Métricas incluidas:**
- Pedidos hoy / mes
- Ingresos hoy / mes
- Clientes nuevos (últimos 30 días)
- Pedidos pendientes
- Tasa de conversión (confirmados/total × 100)
- Producto más vendido
- Top 5 productos y clientes

---

## US-051: Reporte Diario ✅

**Endpoint:** `GET /api/reportes/diario`

**Auth:** Required

**Descripción:** Últimos 7 días con desglose diario

**Respuesta 200:**
```json
{
  "exito": true,
  "tipo": "diario",
  "periodo": "Últimos 7 días",
  "datos": [
    {
      "fecha": "2025-12-23",
      "dia_semana": "martes",
      "pedidos_totales": 5,
      "ingresos": 150.50,
      "confirmados": 5,
      "cancelados": 0,
      "pendientes": 0
    },
    {
      "fecha": "2025-12-24",
      "dia_semana": "miércoles",
      "pedidos_totales": 8,
      "ingresos": 280.00,
      "confirmados": 7,
      "cancelados": 1,
      "pendientes": 0
    }
  ]
}
```

**Curl:**
```bash
curl -X GET http://localhost:8000/api/reportes/diario \
  -H "Authorization: Bearer TOKEN"
```

**Columnas:**
- fecha (YYYY-MM-DD)
- dia_semana (nombre localizado en español)
- pedidos_totales
- ingresos (suma de totales)
- confirmados (count)
- cancelados (count)
- pendientes (count)

---

## US-052: Reporte Semanal ✅

**Endpoint:** `GET /api/reportes/semanal`

**Auth:** Required

**Descripción:** Últimas 8 semanas con promedios

**Respuesta 200:**
```json
{
  "exito": true,
  "tipo": "semanal",
  "periodo": "Últimas 8 semanas",
  "datos": [
    {
      "semana": "Semana 52",
      "periodo": "2025-12-22 al 2025-12-28",
      "pedidos_totales": 42,
      "ingresos": 1450.50,
      "confirmados": 40,
      "cancelados": 2,
      "ticket_promedio": 34.54
    }
  ]
}
```

**Curl:**
```bash
curl -X GET http://localhost:8000/api/reportes/semanal \
  -H "Authorization: Bearer TOKEN"
```

**Columnas:**
- semana
- periodo (rango de fechas)
- pedidos_totales
- ingresos
- confirmados / cancelados
- ticket_promedio (ingresos / pedidos_totales)

---

## US-053: Reporte Mensual ✅

**Endpoint:** `GET /api/reportes/mensual`

**Auth:** Required

**Descripción:** Últimos 12 meses con análisis detallado

**Respuesta 200:**
```json
{
  "exito": true,
  "tipo": "mensual",
  "periodo": "Últimos 12 meses",
  "datos": [
    {
      "mes": "diciembre",
      "mes_año": "2025-12",
      "pedidos_totales": 145,
      "ingresos": 5200.75,
      "confirmados": 138,
      "cancelados": 7,
      "clientes_unicos": 45,
      "ticket_promedio": 35.87
    }
  ]
}
```

**Curl:**
```bash
curl -X GET http://localhost:8000/api/reportes/mensual \
  -H "Authorization: Bearer TOKEN"
```

**Columnas:**
- mes (nombre localizado)
- mes_año (YYYY-MM)
- pedidos_totales
- ingresos
- confirmados / cancelados
- clientes_unicos (distinct cliente_id)
- ticket_promedio

---

## US-054: Exportar a Excel/CSV ✅

**Endpoint:** `POST /api/reportes/exportar`

**Auth:** Required

**Body JSON:**
```json
{
  "tipo": "mensual",
  "formato": "csv"
}
```

**Query params (alternativa):**
- `tipo`: diario | semanal | mensual (default: mensual)
- `formato`: csv | excel (por ahora solo CSV funciona)

**Respuesta:** Archivo descargado (CSV)

**Curl:**
```bash
# POST con body
curl -X POST http://localhost:8000/api/reportes/exportar \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"tipo": "mensual", "formato": "csv"}' \
  -o reporte_mensual.csv

# O como query params
curl -X POST "http://localhost:8000/api/reportes/exportar?tipo=diario&formato=csv" \
  -H "Authorization: Bearer TOKEN" \
  -o reporte_diario.csv
```

**Archivo generado:**
```
reporte_mensual_2025-12-29_235959.csv
```

**Contenido CSV (ejemplo):**
```
mes,mes_año,pedidos_totales,ingresos,confirmados,cancelados,clientes_unicos,ticket_promedio
diciembre,2025-12,145,5200.75,138,7,45,35.87
noviembre,2025-11,98,3250.50,95,3,32,33.17
```

---

## Endpoints Adicionales (Bonus)

### Productos Más Vendidos
```bash
GET /api/reportes/productos-top?limit=10

Respuesta:
{
  "exito": true,
  "total": 5,
  "datos": [
    {
      "producto_id": 1,
      "producto_nombre": "Pizza Margherita",
      "cantidad_vendida": 120,
      "ingresos": 2700.00
    }
  ]
}
```

### Clientes Más Activos
```bash
GET /api/reportes/clientes-top?limit=10

Respuesta:
{
  "exito": true,
  "total": 3,
  "datos": [
    {
      "cliente_id": 1,
      "nombre": "Juan Pérez",
      "email": "juan@example.com",
      "pedidos_totales": 12,
      "gasto_total": 450.50
    }
  ]
}
```

---

## Flujo de Ejemplo

```bash
# 1. Ver dashboard principal
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/dashboard

# 2. Obtener reporte semanal
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/reportes/semanal

# 3. Descargar CSV mensual
curl -H "Authorization: Bearer TOKEN" \
  -o reporte.csv \
  http://localhost:8000/api/reportes/exportar?tipo=mensual

# 4. Ver top 5 productos
curl -H "Authorization: Bearer TOKEN" \
  "http://localhost:8000/api/reportes/productos-top?limit=5"
```

---

## Notas

- **Fechas:** Todos los reportes usan `Carbon` para cálculos precisos
- **Locale:** Nombres de días/meses en español (es_ES)
- **Paginación:** No aplicable; reportes retornan arrays completos
- **Permisos:** Todos requieren token Sanctum (usuario o admin)
- **CSV:** Generado en servidor, descargable directamente
- **Excel:** Por implementar (requiere `maatwebsite/excel`)
- **Performance:** Optimizado con agregaciones de BD (SUM, COUNT, etc.)

---

## Integración con Frontend

```javascript
// Dashboard - Consumir KPIs
fetch('http://localhost:8000/api/dashboard', {
  headers: { 'Authorization': `Bearer ${token}` }
})
  .then(r => r.json())
  .then(data => {
    console.log('KPIs:', data.kpis);
    console.log('Top Productos:', data.top_productos);
  });

// Descargar CSV
fetch('http://localhost:8000/api/reportes/exportar', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({ tipo: 'mensual' })
})
  .then(r => r.blob())
  .then(blob => {
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'reporte_mensual.csv';
    a.click();
  });
```
