# 🚀 INSTRUCCIONES DE INICIO - Módulo 4 Continuación

**Proyecto:** Pizzería API REST  
**Módulo:** 4 - Pedidos (Continuación)  
**Versión:** 1.0 - Final  
**Fecha:** 29 Diciembre 2024  

---

## 📌 INICIO RÁPIDO (5 minutos)

### 1. Ejecutar la Migración

```bash
# Navegar al directorio del proyecto
cd c:\Users\HP\Desktop\Proyectos2025\pizzeria_api-rest\pizzeria-api

# Ejecutar migración
php artisan migrate

# Resultado esperado:
# Migrated:  2025_12_29_120000_create_direcciones_cliente_table.php
```

### 2. Testear un Endpoint

```bash
# Obtener todas las direcciones de un cliente
curl -X GET http://localhost:8000/api/clientes/5/direcciones \
  -H "Authorization: Bearer TU_TOKEN_SANCTUM" \
  -H "Accept: application/json"

# Resultado: Lista de direcciones en JSON
```

### 3. Revisar Documentación

```bash
# Abrir documentación técnica
# Ver: docs/pedidos-continuacion.md
# Para: Todos los endpoints documentados
```

---

## 🛠️ CONFIGURACIÓN COMPLETA (30 minutos)

### Paso 1: Entender la Arquitectura

```
Leer archivos en este orden:
1. MODULO4_RESUMEN_EJECUTIVO.md (5 mins)
   → Entender qué se implementó

2. pedidos-continuacion.md (10 mins)
   → Ver endpoints disponibles

3. MODULO4_INTEGRACION_FRONTEND.md (10 mins)
   → Entender cómo integrar en frontend

4. MODULO4_CONTINUACION_VERIFICACION.md (5 mins)
   → Checklist de verificación
```

### Paso 2: Preparar Base de Datos

```bash
# 1. Ejecutar migración
php artisan migrate

# 2. Verificar tabla creada
php artisan tinker
# En tinker:
>> DB::table('direcciones_cliente')->count()
=> 0  # (vacío, es correcto)
>> exit
```

### Paso 3: Preparar Ambiente Frontend

```bash
# Si usas npm/yarn, instalar axios (si no lo tienes)
npm install axios

# O usar CDN
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
```

### Paso 4: Copiar Código Frontend

```javascript
// 1. Copiar instancia axios (de MODULO4_INTEGRACION_FRONTEND.md)
// 2. Copiar funciones JavaScript (8 funciones listos)
// 3. Copiar componentes Vue (2 componentes listos)
// 4. Adaptar URLs y tokens
```

---

## 📖 DOCUMENTACIÓN DISPONIBLE

### Por Usuario

#### Para Desarrollador Backend
```
LEER:
1. MODULO4_CONTINUACION_VERIFICACION.md
   → Validaciones por endpoint
   → Checklist de testing

2. pedidos-continuacion.md
   → Especificación técnica
   → Ejemplos curl

3. MODULO4_CONCLUSIÓN_FINAL.md
   → Características de calidad
   → Mejores prácticas

HACER:
1. Ejecutar migración (php artisan migrate)
2. Testear endpoints (ejemplos curl)
3. Revisar validaciones (Form Requests)
4. Revisar transacciones (BD críticas)
```

#### Para Desarrollador Frontend
```
LEER:
1. MODULO4_INTEGRACION_FRONTEND.md ⭐ COMIENZA AQUÍ
   → Configuración axios
   → 8 funciones JavaScript
   → 2 componentes Vue

2. pedidos-continuacion.md
   → Parámetros de búsqueda
   → Formatos de respuesta
   → Códigos de error

HACER:
1. Copiar instancia axios
2. Copiar funciones JavaScript
3. Copiar componentes Vue
4. Adaptar a tu aplicación
5. Testear con ejemplos curl
```

#### Para Product Manager
```
LEER:
1. MODULO4_RESUMEN_EJECUTIVO.md
   → Visión general
   → Ventajas para negocio

2. PROXIMO_PASO_MODULO9.md
   → Siguiente fase
   → Timeline estimado

REVISAR:
1. Puntos: 235/270 (87%)
2. Módulo 4: 20/20 pts (100%)
3. Próximo: Módulo 9 (30 pts)
```

#### Para QA/Testing
```
LEER:
1. MODULO4_CONTINUACION_VERIFICACION.md
   → Checklist de testing (30 items)
   → Validaciones por endpoint

2. pedidos-continuacion.md
   → Códigos de error
   → Casos de test

TESTEAR:
1. Validaciones de entrada
2. Búsqueda avanzada (6 filtros)
3. Dirección favorita
4. Repetir pedido
5. Marcar entregado
6. Agregar notas
```

---

## 🧪 TESTING RÁPIDO

### Test 1: Crear Dirección (2 minutos)

```bash
# 1. Abrir terminal
curl -X POST http://localhost:8000/api/clientes/5/direcciones \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre_direccion": "Casa",
    "calle": "Carrera 5",
    "numero": "123",
    "ciudad": "Bogotá",
    "codigo_postal": "110111"
  }'

# 2. Respuesta esperada:
# {
#   "data": {
#     "id": 1,
#     "nombre_direccion": "Casa",
#     "direccion_completo": "Carrera 5 #123, Bogotá 110111",
#     ...
#   }
# }
```

### Test 2: Buscar Pedido (2 minutos)

```bash
# Buscar por estado
curl -X GET "http://localhost:8000/api/pedidos/buscar?estado=CONFIRMADO" \
  -H "Authorization: Bearer TU_TOKEN"

# Buscar por número
curl -X GET "http://localhost:8000/api/pedidos/buscar?q=PED-2024" \
  -H "Authorization: Bearer TU_TOKEN"

# Buscar por precio
curl -X GET "http://localhost:8000/api/pedidos/buscar?precio_min=100000&precio_max=500000" \
  -H "Authorization: Bearer TU_TOKEN"
```

### Test 3: Marcar Entregado (2 minutos)

```bash
# Marcar pedido como entregado
curl -X PATCH http://localhost:8000/api/pedidos/1/entregado \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "fecha_entrega": "2024-12-29",
    "comentario": "Entregado al cliente"
  }'

# Respuesta esperada: Estado cambiado a ENTREGADO
```

---

## 💻 INTEGRACIÓN FRONTEND - Paso a Paso

### Opción 1: Vue.js (Recomendado)

```vue
<!-- 1. Importar componentes (copiar de MODULO4_INTEGRACION_FRONTEND.md) -->
<template>
  <div>
    <DireccionesComponent :cliente-id="5" />
    <BusquedaPedidosComponent />
  </div>
</template>

<script>
import DireccionesComponent from '@/components/DireccionesComponent.vue';
import BusquedaPedidosComponent from '@/components/BusquedaPedidosComponent.vue';

export default {
  components: {
    DireccionesComponent,
    BusquedaPedidosComponent
  }
};
</script>

<!-- 2. El componente maneja todo automáticamente -->
<!-- 3. Consultar ejemplos en MODULO4_INTEGRACION_FRONTEND.md -->
```

### Opción 2: JavaScript Vanilla

```javascript
// 1. Configurar axios
import api from '@/services/api'; // (copiar de MODULO4_INTEGRACION_FRONTEND.md)

// 2. Usar funciones (copiar de MODULO4_INTEGRACION_FRONTEND.md)
const direcciones = await obtenerDirecciones(clienteId);
const pedidos = await buscarPedidos({ q: 'PED' });

// 3. Mostrar resultados en el DOM
// 4. Ver ejemplos completos en MODULO4_INTEGRACION_FRONTEND.md
```

### Opción 3: React

```javascript
// 1. Mismo api.js con axios
import api from '@/services/api';

// 2. Crear hooks (convert de funciones JS)
const useDirecciones = (clienteId) => {
  const [direcciones, setDirecciones] = useState([]);
  
  useEffect(() => {
    obtenerDirecciones(clienteId).then(setDirecciones);
  }, [clienteId]);
  
  return direcciones;
};

// 3. Usar en componentes
function DireccionesApp() {
  const direcciones = useDirecciones(5);
  return <div>{...}</div>;
}
```

---

## 🔍 REFERENCIA RÁPIDA

### Endpoints Memorizar (Máximo 11)

```
BÚSQUEDA:
GET    /api/pedidos/buscar

REPETIR:
POST   /api/pedidos/repetir/{id}

ENTREGA:
PATCH  /api/pedidos/{id}/entregado

NOTAS:
PUT    /api/pedidos/{id}/notas

DIRECCIONES:
GET    /api/clientes/{id}/direcciones
POST   /api/clientes/{id}/direcciones
GET    /api/clientes/{id}/direcciones/{id}
PUT    /api/clientes/{id}/direcciones/{id}
DELETE /api/clientes/{id}/direcciones/{id}
PATCH  /api/clientes/{id}/direcciones/{id}/favorita
GET    /api/clientes/{id}/direcciones/favorita/obtener
```

### Parámetros Memorizar

```
BÚSQUEDA: q, estado, cliente_id, fecha_desde, fecha_hasta, precio_min, precio_max
DIRECCIÓN: nombre_direccion, calle, numero, ciudad, codigo_postal
ENTREGA: fecha_entrega, comentario
NOTAS: notas
```

---

## 📋 CHECKLIST DE INICIO

### Semana 1: Setup

```
☐ Día 1: Leer documentación (1 hora)
  ☐ MODULO4_RESUMEN_EJECUTIVO.md
  ☐ pedidos-continuacion.md (primeras 50 líneas)
  ☐ MODULO4_INTEGRACION_FRONTEND.md (primeras 50 líneas)

☐ Día 2: Configurar BD (30 mins)
  ☐ php artisan migrate
  ☐ Verificar tabla creada
  ☐ Insertar datos de prueba (opcional)

☐ Día 3: Testear Backend (1 hora)
  ☐ 5 ejemplos curl funcionales
  ☐ Verificar respuestas JSON
  ☐ Revisar validaciones

☐ Día 4-5: Integrar Frontend (2 horas)
  ☐ Copiar código JavaScript/Vue
  ☐ Adaptar a tu aplicación
  ☐ Testear componentes
```

### Semana 2: Implementación

```
☐ Integración completa (4 horas)
  ☐ Todas las funciones JavaScript
  ☐ Ambos componentes Vue
  ☐ Manejo de errores
  ☐ Validaciones frontend

☐ Testing (2 horas)
  ☐ Casos de éxito (10+)
  ☐ Casos de error (10+)
  ☐ Flujos completos (5+)

☐ Documentación (1 hora)
  ☐ Actualizar README
  ☐ Documentar cambios
  ☐ Ejemplos de uso
```

---

## ⚠️ ERRORES COMUNES Y SOLUCIONES

### Error: "Undefined table: direcciones_cliente"

```
SOLUCIÓN:
php artisan migrate

O si necesitas resetear:
php artisan migrate:reset
php artisan migrate
```

### Error: 401 Unauthorized

```
SOLUCIÓN:
1. Verificar token Sanctum válido
2. Verificar headers en curl:
   -H "Authorization: Bearer TOKEN"
3. Verificar user está autenticado
```

### Error: 422 Unprocessable Entity

```
SOLUCIÓN:
1. Leer error response
2. response.data.errors contiene detalles
3. Ver validaciones en pedidos-continuacion.md
4. Revisar Form Requests
```

### Error: "Cannot find property direccion_completo"

```
SOLUCIÓN:
Ejecutar migración primero:
php artisan migrate

El mutador se define en el modelo.
```

---

## 🎓 CURVA DE APRENDIZAJE

### Principiante (2-3 días)

```
Actividades:
1. Leer MODULO4_RESUMEN_EJECUTIVO.md (30 mins)
2. Ejecutar 5 ejemplos curl (1 hora)
3. Integrar 1 componente Vue (2 horas)
4. Testear manualmente (1 hora)

Resultado: Entender qué se hizo, poder usarlo
```

### Intermedio (1 semana)

```
Actividades:
1. Implementar ambos componentes (4 horas)
2. Integrar todas las funciones JS (3 horas)
3. Hacer 20+ tests manuales (2 horas)
4. Crear documentación adicional (2 horas)

Resultado: Dominar el código, poder modificarlo
```

### Avanzado (2+ semanas)

```
Actividades:
1. Crear tests unitarios (4 horas)
2. Optimizar queries (2 horas)
3. Agregar caché (2 horas)
4. Mejorar seguridad (2 horas)
5. Documentar arquitectura (2 horas)

Resultado: Ser experto, poder extender
```

---

## 📞 SOPORTE RÁPIDO

### ¿Duda sobre...?

| Pregunta | Respuesta |
|----------|-----------|
| ¿Cómo crear dirección? | Ver pedidos-continuacion.md #US-044 |
| ¿Cómo buscar pedidos? | Ver pedidos-continuacion.md #US-028 |
| ¿Cómo integrar en Vue? | Ver MODULO4_INTEGRACION_FRONTEND.md |
| ¿Validaciones del API? | Ver MODULO4_CONTINUACION_VERIFICACION.md |
| ¿Errores esperados? | Ver pedidos-continuacion.md (códigos HTTP) |
| ¿Componentes listos? | Ver MODULO4_INTEGRACION_FRONTEND.md |

---

## 🎯 PRÓXIMO PASO

### Después de Implementar Módulo 4

```
1. ✅ Implementar Módulo 4 Continuación (completado)
2. ⏭️  Implementar Módulo 9 - Pagos (próximo)
   └─ Ver: PROXIMO_PASO_MODULO9.md

Timeline: 4-5 sesiones
Puntos: 30 pts (máximo disponible)
Impacto: Procesamiento de pagos (crítico)
```

---

## 📚 DOCUMENTACIÓN COMPLETA

```
INICIO RÁPIDO:
└─ Este archivo (INSTRUCCIONES_INICIO.md)

TÉCNICA:
├─ pedidos-continuacion.md
├─ MODULO4_CONTINUACION_VERIFICACION.md
└─ MODULO4_CONCLUSIÓN_FINAL.md

INTEGRACIÓN:
├─ MODULO4_INTEGRACION_FRONTEND.md ⭐
└─ INDICE_DOCUMENTACION_M4.md

CONTEXTO:
├─ MODULO4_RESUMEN_EJECUTIVO.md
├─ FASE3_PROGRESO_ACTUALIZADO.md
└─ VISUAL_RESUMEN_FINAL.md

SIGUIENTE:
└─ PROXIMO_PASO_MODULO9.md
```

---

## ✨ ¡LISTO PARA COMENZAR!

```
╔════════════════════════════════════════════════════╗
║                                                    ║
║  1. Ejecuta: php artisan migrate                   ║
║  2. Lee: MODULO4_INTEGRACION_FRONTEND.md           ║
║  3. Copia: Código Vue/JavaScript                   ║
║  4. Integra: En tu aplicación                      ║
║  5. Testea: Con ejemplos curl                      ║
║                                                    ║
║  ¡Listo para usar el módulo completo! 🚀          ║
║                                                    ║
╚════════════════════════════════════════════════════╝
```

---

**Guía de Inicio:** 29 Diciembre 2024  
**Versión:** 1.0 Final  
**Listo para:** Implementación inmediata  
**Soporte:** Ver documentación disponible
