# FASE 4 - PLAN ACTUALIZADO (Semanas 7-8)

**Estado:** En curso  
**Puntos totales fase:** 55 pts  
**Fecha:** 29 dic 2025  

---

## ⚠️ Alcance Ajustado por el Dueño
- **Pagos en línea (Stripe/PayPal) SUSPENDIDOS**: no se implementará validación ni cobro en pasarela. El cobro será **transferencia directa a la cuenta bancaria** coordinada por WhatsApp.
- **No tocar migraciones/tablas de pagos** ya previstas, por si el dueño reactiva la integración a futuro.
- El resto de módulos de la Fase 4 continúan normalmente.

---

## 📌 Módulos de Fase 4

### 1) Módulo 9: Pagos y Billing (30 pts) — PAUSADO
- Alcance en esta fase: **solo flujo manual** (mensaje WhatsApp con datos bancarios).
- Sin endpoints de pago, sin pasarelas, sin validación de recibos en sistema.
- Mantener cualquier tabla existente, pero **sin usarla**.

### 2) Módulo 10: Descuentos y Promociones (15 pts) — ACTIVO
User stories previstas:
- US-080 Crear cupón (4 pts)
- US-081 Aplicar cupón (5 pts)
- US-082 Ofertas por producto (3 pts)
- US-083 Ofertas por volumen (3 pts)

### 3) Módulo 3: Productos (Continuación) (10 pts) — ACTIVO
User stories previstas:
- US-013 Categorías productos (4 pts)
- US-014 Filtrar por categoría (3 pts)
- US-015 Alerta stock bajo (3 pts)

---

## 🚀 Orden de ejecución recomendada (ajustado)
1) **Módulo 3 (continuación)** – 10 pts  
   - Categorías, filtros, alerta de stock bajo.
2) **Módulo 10 (descuentos)** – 15 pts  
   - Cupones y promociones aplicables a pedidos.
3) **Módulo 9 (pagos)** – 30 pts  
   - **Solo mensaje bancario por WhatsApp** (sin cobro/validación en sistema).  
   - Dejar documentado el flujo manual y las tablas en stand-by.

---

## ✅ Definición de Hecho (DoD) para esta fase
- Documentación actualizada indicando que pagos en línea están suspendidos y se usa transferencia bancaria manual.
- Endpoints activos solo para Módulo 3 y 10 según los US definidos.
- No se crean endpoints de cobro ni validación de pagos.
- Migraciones de pagos existentes: **intactas** (sin borrar/editar).
- Casos de prueba manuales para descuentos y productos.

---

## 📄 Documentos relevantes
- [PROXIMO_PASO_MODULO9.md](PROXIMO_PASO_MODULO9.md) → Nota de suspensión al inicio.
- (Nuevo) Este archivo: **FASE4_PLAN_ACTUALIZADO.md** → Alcance ajustado y prioridades.

---

## 🧭 Próximos pasos inmediatos
1) Confirmar si mantenemos la tabla de pagos solo como placeholder (sin endpoints).  
2) Implementar **Módulo 3 (categorías/filtros/stock bajo)**.  
3) Implementar **Módulo 10 (cupones/promos)**.  
4) Documentar el flujo de pago **manual vía WhatsApp** (sin pasarela) y los datos bancarios del dueño (si los provee).

---

**Nota:** Este plan respeta la solicitud del dueño: **no implementar pagos en línea ni validación de pasarela** en esta fase. Si el dueño cambia de idea, las migraciones se mantienen listas para reactivarlo rápidamente.
