# Recomendaciones: Próximo Módulo - Pagos (Módulo 9)

> ⚠️ Estado actualizado (dic 29, 2025): el dueño pidió **no implementar pagos en línea ni validación con pasarelas (Stripe/PayPal)**. Los cobros serán **transferencia directa a la cuenta bancaria via WhatsApp**. El módulo de pagos queda **en pausa/suspendido** hasta nuevo aviso. **No tocar las migraciones/tablas ya creadas** por si se reactiva en el futuro. El resto de módulos de Fase 4 siguen su curso.

**Proyecto:** Pizzería API REST  
**Fase Actual:** 3  
**Módulos Completados:** 12/15 (Fase 1 + Fase 2 + Parte de Fase 3)  
**Puntos Completados:** 235/270 (87%)  
**Siguiente Módulo:** Módulo 9 - Pagos y Billing (30 pts)  

---

## 📊 Análisis Situación Actual

### Progreso del Proyecto
```
Fase 1:   45 pts ✅ (100%)
Fase 2:   85 pts ✅ (100%)
Fase 3:  195 pts ✅ (HASTA AHORA)
─────────────────────────────
TOTAL:   235 pts (87%)

FALTA:    55 pts (13%)
├─ Módulo 9 - Pagos:        30 pts
├─ Módulo 3 - Productos:    10 pts
└─ Módulo 10 - Descuentos:  15 pts
```

### Módulo 4 Continuación - Estado Final ✅
- ✅ 5/5 US completadas (20 pts)
- ✅ 11 endpoints funcionales
- ✅ 1 nueva tabla (direcciones_cliente)
- ✅ 2 controladores (1 nuevo + 1 mejorado)
- ✅ ~1,800 líneas de documentación

**Dependencias resueltas:** Listo para Módulo 9

---

## 🎯 Módulo 9: Pagos y Billing (30 pts) - Análisis

### Por qué Módulo 9 es la Mejor Opción

#### 1. Máximo Valor (30 pts)
- Mayor cantidad de puntos disponibles
- Cubriría 55% de lo restante
- Único módulo de 30 pts disponible

#### 2. Criticidad para el Negocio
- **Sin pagos NO hay ingresos**
- Integración directa con pedidos
- Información importante para reportes
- Requisito para MVP

#### 3. Dependencias Resueltas
- ✅ Módulo 4 (Pedidos) - COMPLETADO
- ✅ Módulo 8 (Usuarios) - COMPLETADO
- ✅ Módulo 7 (Reportes) - COMPLETADO
- ✅ Módulo 2 (Auth) - COMPLETADO

#### 4. Impacto en Negocio
- **Ingresos:** Procesamiento de pagos
- **Cliente:** Múltiples métodos de pago
- **Confianza:** Seguridad + Encriptación
- **Análisis:** Historial de transacciones
- **Recuperación:** Sistema de reembolsos

---

## 📋 Módulo 9: Especificación Estimada

### User Stories Esperadas (6 US - 30 pts)

| ID | Descripción | Estimado |
|----|-------------|----------|
| US-049 | Integración Stripe | 7 pts |
| US-050 | Integración PayPal | 6 pts |
| US-051 | Historial de Pagos | 4 pts |
| US-052 | Reembolsos | 5 pts |
| US-053 | Métodos de Pago Guardados | 4 pts |
| US-054 | Facturación/Recibos PDF | 4 pts |

### Features Esperadas

#### Stripe Integration
```javascript
// Crear customer en Stripe
const stripeCustomer = await stripe.customers.create({
  email: cliente.email,
  metadata: { cliente_id: cliente.id }
});

// Crear payment intent
const paymentIntent = await stripe.paymentIntents.create({
  customer: stripeCustomer.id,
  amount: pedido.total,
  currency: 'cop',
  metadata: { pedido_id: pedido.id }
});

// Webhook para confirmar pago
app.post('/webhooks/stripe', (req, res) => {
  const event = req.body;
  if (event.type === 'payment_intent.succeeded') {
    // Actualizar estado del pedido
    // Enviar confirmación al cliente
  }
});
```

#### PayPal Integration
```javascript
// Crear pago en PayPal
const payment = {
  intent: 'sale',
  payer: { payment_method: 'paypal' },
  transactions: [{
    amount: { total: pedido.total, currency: 'USD' },
    description: `Pedido #${pedido.numero}`
  }],
  redirect_urls: {
    return_url: 'http://localhost/success',
    cancel_url: 'http://localhost/cancel'
  }
};

// Ejecutar pago
paypal.payment.execute(paymentId, execution);
```

#### Payment Methods Storage
```javascript
// Guardar método de pago
const metodoPago = {
  cliente_id: cliente.id,
  tipo: 'tarjeta|paypal|transferencia',
  token_stripe: '...', // O token PayPal
  ultimos_digitos: '4242',
  vencimiento: '12/25',
  predeterminado: true
};

// Usar método guardado
const pago = await crearPago({
  metodo_pago_id: 1,
  pedido_id: pedido.id
});
```

#### Refund System
```javascript
// Crear reembolso
const reembolso = {
  pago_id: pago.id,
  monto: 50000,
  razon: 'Cliente solicita devolución',
  estado: 'PENDIENTE'
};

// Procesar reembolso
await stripe.refunds.create({
  payment_intent: pago.stripe_payment_id,
  amount: reembolso.monto
});
```

---

## 🗄️ Base de Datos Estimada

### Nuevas Tablas (5-6)

#### pagos
```sql
CREATE TABLE pagos (
  id PRIMARY KEY
  pedido_id FK → pedidos
  cliente_id FK → clientes
  monto DECIMAL
  estado ENUM ('PENDIENTE', 'COMPLETADO', 'FALLIDO', 'REEMBOLSADO')
  metodo_pago VARCHAR(50) -- 'stripe', 'paypal', 'transferencia'
  referencia_externa VARCHAR(100) -- Stripe ID o PayPal ID
  respuesta_pasarela JSON
  created_at, updated_at
);
```

#### metodos_pago_guardados
```sql
CREATE TABLE metodos_pago_guardados (
  id PRIMARY KEY
  cliente_id FK
  tipo VARCHAR(50)
  token VARCHAR(500) -- Encriptado
  ultimos_digitos VARCHAR(20)
  vencimiento DATE
  predeterminado BOOLEAN
  activo BOOLEAN
  created_at, updated_at
);
```

#### reembolsos
```sql
CREATE TABLE reembolsos (
  id PRIMARY KEY
  pago_id FK
  monto DECIMAL
  razon TEXT
  estado ENUM ('PENDIENTE', 'APROBADO', 'RECHAZADO', 'COMPLETADO')
  referencia_externa VARCHAR(100)
  created_at, updated_at
);
```

#### transacciones_pagos
```sql
CREATE TABLE transacciones_pagos (
  id PRIMARY KEY
  pago_id FK
  tipo VARCHAR(50) -- 'intento', 'completado', 'fallido'
  respuesta TEXT
  timestamp
);
```

#### facturas
```sql
CREATE TABLE facturas (
  id PRIMARY KEY
  pedido_id FK
  pago_id FK
  numero_factura VARCHAR(50) UNIQUE
  ruta_pdf VARCHAR(255)
  estado ENUM ('GENERADA', 'ENVIADA', 'DESCARGADA')
  created_at
);
```

---

## 🔧 Arquitectura Técnica

### Controladores Nuevos (3-4)

```
PagoController
├─ index()           - Listar pagos
├─ store()           - Crear pago
├─ confirmar()       - Confirmar pago (webhook)
├─ obtener()         - Detalles del pago
└─ historial()       - Historial cliente

MetodoPagoController
├─ index()           - Métodos guardados
├─ store()           - Guardar nuevo
├─ marcarPredeterminado()
└─ destroy()         - Eliminar método

ReembolsoController
├─ index()           - Listar reembolsos
├─ store()           - Solicitar reembolso
├─ confirmar()       - Procesar reembolso
└─ historial()       - Historial

FacturaController
├─ generar()         - Generar PDF
├─ descargar()       - Descargar factura
└─ enviar()          - Enviar por email
```

### Services (2-3)

```
PagoService
├─ crearPaymentIntent()    - Crear intent Stripe
├─ confirmarPago()         - Procesar confirmación
├─ validarPago()           - Validar integridad
└─ obtenerHistorial()

RefundService
├─ solicitarReembolso()    - Crear solicitud
├─ procesarReembolso()     - Procesar en pasarela
├─ reversarStock()         - Devolver productos
└─ notificarCliente()

FacturaService
├─ generarPDF()            - Generar factura
├─ guardarArchivo()        - Guardar en storage
└─ enviarPorEmail()        - Enviar al cliente
```

### Form Requests (5-6)

```
CrearPagoRequest
- monto (required, numeric, min:1000)
- metodo_pago (required, in:stripe,paypal)
- metodo_pago_guardado_id (optional)
- referencia_token (conditional)

CrearMetodoPagoRequest
- tipo (required, in:tarjeta,paypal)
- token (required, conditional)
- marcar_predeterminado (optional, boolean)

SolicitarReembolsoRequest
- pago_id (required, exists)
- monto (optional, numeric, max:pago.monto)
- razon (required, string, max:500)

GenerarFacturaRequest
- pedido_id (required, exists)
- enviar_email (optional, boolean)
```

---

## 🔌 Integraciones Externas

### Stripe
```bash
# Instalación
composer require stripe/stripe-php

# Configuración .env
STRIPE_PUBLIC_KEY=pk_test_...
STRIPE_SECRET_KEY=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

# Webhook
STRIPE_WEBHOOK_URL=https://pizzeria.local/webhooks/stripe
```

### PayPal
```bash
# Instalación
composer require paypal/checkout-sdk-php

# Configuración .env
PAYPAL_CLIENT_ID=...
PAYPAL_SECRET=...
PAYPAL_MODE=sandbox|live

# Webhook
PAYPAL_WEBHOOK_URL=https://pizzeria.local/webhooks/paypal
```

### PDF (Para facturas)
```bash
# Instalación
composer require barryvdh/laravel-dompdf

# Uso
$pdf = PDF::loadView('facturas.pdf', $data);
return $pdf->download('factura-PED-2024-001.pdf');
```

---

## 📋 Checklist de Implementación

### Preparación (1 hora)
- [ ] Instalar paquetes Stripe y PayPal
- [ ] Crear credenciales en Stripe Dashboard
- [ ] Crear credenciales en PayPal Dashboard
- [ ] Generar webhook secrets
- [ ] Configurar .env

### Base de Datos (2 horas)
- [ ] Crear migración pagos
- [ ] Crear migración metodos_pago_guardados
- [ ] Crear migración reembolsos
- [ ] Crear migración transacciones_pagos
- [ ] Crear migración facturas
- [ ] Agregar índices
- [ ] Crear modelos con relaciones

### Backend (8-10 horas)
- [ ] PagoController (CRUD + historial)
- [ ] MetodoPagoController (CRUD)
- [ ] ReembolsoController (CRUD + procesamiento)
- [ ] FacturaController (generar + enviar)
- [ ] PagoService (lógica de pagos)
- [ ] RefundService (lógica reembolsos)
- [ ] FacturaService (generar PDFs)
- [ ] Form Requests (validaciones)
- [ ] Webhooks Stripe
- [ ] Webhooks PayPal
- [ ] Transacciones de BD
- [ ] Notificaciones automáticas
- [ ] Encriptación de tokens

### Testing (4 horas)
- [ ] Tests unitarios PagoService
- [ ] Tests de validación
- [ ] Tests de webhooks
- [ ] Tests de reembolsos
- [ ] Pruebas manuales Stripe
- [ ] Pruebas manuales PayPal
- [ ] Pruebas de facturas

### Documentación (4 horas)
- [ ] Endpoints documentados
- [ ] Ejemplos curl (20+)
- [ ] Ejemplos JavaScript
- [ ] Componentes Vue.js
- [ ] Guía de integración frontend
- [ ] Guía de webhooks
- [ ] Troubleshooting

### Total Estimado: 4-5 sesiones

---

## 💡 Recomendaciones Implementación

### 1. Empezar con Stripe
- Más documentación
- Dashboard más intuitivo
- Mejor para desarrollo

### 2. Validar Montos
```php
// En PagoRequest
'monto' => 'required|numeric|min:500|max:99999999'
```

### 3. Guardar Respuesta de Pasarela
```php
$pago->respuesta_json = json_encode($stripeResponse);
$pago->referencia_externa = $stripeResponse['id'];
```

### 4. Transacciones Críticas
```php
DB::beginTransaction();
try {
  $pago = Pago::create([...]);
  $stripe->paymentIntents->create([...]);
  DB::commit();
} catch (Exception $e) {
  DB::rollBack();
}
```

### 5. Webhooks Seguros
```php
// Verificar firma
$event = Stripe\Webhook::constructEvent(
  $body,
  $_SERVER['HTTP_STRIPE_SIGNATURE'],
  config('services.stripe.webhook_secret')
);
```

### 6. Encriptación de Tokens
```php
// NO guardar tokens en texto plano
// Stripe lo maneja automáticamente
// PayPal también tiene tokens seguros
```

### 7. Reembolsos Parciales
```php
// Permitir reembolsos parciales
'monto' => 'nullable|numeric|max:' . $pago->monto
```

### 8. Facturación Automática
```php
// Generar PDF al completar pago
event(new PagoCompletado($pago));
// En listener: generar factura PDF
```

---

## 🔐 Consideraciones de Seguridad

### PCI Compliance
- ✅ NO guardar números de tarjeta
- ✅ NO guardar CVV
- ✅ Usar Stripe/PayPal para tokenización
- ✅ HTTPS obligatorio

### Verificación de Webhooks
```php
// Siempre verificar la firma
if (!verify_webhook_signature($request)) {
  return response('Unauthorized', 401);
}
```

### Rate Limiting
```php
// Limitar intentos de pago
Route::post('/api/pagos', $controller)->throttle('5,1'); // 5 por minuto
```

### Auditoría
```php
// Registrar todos los intentos
TransaccionPago::create([
  'pago_id' => $pago->id,
  'tipo' => 'intento',
  'respuesta' => $response
]);
```

---

## 📊 Impacto Proyectado

### Antes de Módulo 9
```
TOTAL: 235 pts (87%)
```

### Después de Módulo 9
```
TOTAL: 265 pts (98%)
Falta solo: 5 pts (2%) - Bonificaciones
```

### Impacto en Negocio
- ✅ Procesamiento de pagos (crítico)
- ✅ Múltiples métodos (flexibilidad)
- ✅ Reembolsos (satisfacción cliente)
- ✅ Facturación automática (profesionalismo)
- ✅ Historial transacciones (control)

---

## ⚠️ Riesgos Potenciales

### Riesgo 1: Errores de Integración Stripe
**Probabilidad:** Media  
**Impacto:** Alto  
**Mitigación:** Usar sandbox mode, tests completos

### Riesgo 2: Webhooks No Llegar
**Probabilidad:** Baja  
**Impacto:** Alto  
**Mitigación:** Polling fallback, reintentos

### Riesgo 3: Pérdida de Dinero del Cliente
**Probabilidad:** Muy Baja  
**Impacto:** Crítico  
**Mitigación:** Transacciones BD, auditoría completa

### Riesgo 4: Problema de Performance
**Probabilidad:** Baja  
**Impacto:** Medio  
**Mitigación:** Índices, caching, async processing

---

## 🎯 Priorización de Features

### Fase 1 (MVP - Obligatorio)
1. Stripe básico
2. Crear pago
3. Confirmar pago (webhook)
4. Historial pagos

### Fase 2 (Mejora - Importante)
5. PayPal
6. Métodos guardados
7. Reembolsos básicos

### Fase 3 (Optimización - Nice to have)
8. Facturación automática
9. Reembolsos avanzados
10. Análisis pagos (en reportes)

---

## 📚 Recursos Útiles

### Documentación Oficial
- Stripe Docs: https://stripe.com/docs
- PayPal Docs: https://developer.paypal.com
- Laravel Payments: https://laravel.io/

### Librerías Recomendadas
- `stripe/stripe-php` (v13+)
- `paypal/checkout-sdk-php`
- `barryvdh/laravel-dompdf` (facturas)
- `spatie/laravel-webhook-client` (webhooks)

### Herramientas Testing
- Stripe CLI (para webhooks locales)
- Postman (para endpoints)
- ngrok (para exponer localhost)

---

## ✅ Recomendación Final

**PROCEDER CON MÓDULO 9 - PAGOS**

### Razones:
1. ✅ Máximo valor (30 pts)
2. ✅ Dependencias resueltas
3. ✅ Crítico para negocio
4. ✅ Documentación completa
5. ✅ Implementación manejable

### Timing:
- **Próxima sesión:** Empezar con Stripe básico
- **Sesión 2-3:** Completar Stripe + PayPal
- **Sesión 4:** Reembolsos + Facturas
- **Sesión 5:** Testing + Documentación

### Resultado Esperado:
- 30 pts adicionales
- **Total: 265 pts (98%)**
- Sistema de pagos completo y funcional

---

**Documento preparado:** 29 Diciembre 2024  
**Listo para:** Siguiente sesión de desarrollo  
**Recomendación:** Proceder con Módulo 9 inmediatamente
