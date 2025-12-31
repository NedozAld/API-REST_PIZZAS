# Guia rapida: API WhatsApp / Twilio (Modulo 5)

Variables necesarias (.env):
- TWILIO_ACCOUNT_SID
- TWILIO_AUTH_TOKEN
- TWILIO_WHATSAPP_FROM (ej: +14155238886 numero sandbox)
- TWILIO_WHATSAPP_OWNER (numero del dueño con prefijo + y pais)

Base URL: `http://localhost:8000/api`

## 1) Enviar ticket al dueño (US-031)
POST /api/whatsapp/pedidos/{id}/ticket  (auth:sanctum)

Curl (TOKEN = token de usuario interno o admin):
```
curl -X POST http://localhost:8000/api/whatsapp/pedidos/1/ticket \
  -H "Authorization: Bearer TOKEN"
```
Respuesta 200 esperada:
```
{ "exito": true, "mensaje": "Ticket enviado por WhatsApp", "twilio_sid": "SM..." }
```

## 2) Webhook confirmacion Twilio (US-032)
POST /api/whatsapp/webhook  (publico, Twilio)

Simulacion curl:
```
curl -X POST http://localhost:8000/api/whatsapp/webhook \
  -d "Body=CONFIRMAR 1" \
  -d "From=whatsapp:+14150000000"
```
Respuesta 200 si reconoce el pedido:
```
{ "exito": true, "mensaje": "Pedido confirmado via WhatsApp", "pedido_id": 1 }
```

Reglas de parseo: acepta mensajes como `CONFIRMAR 1` o `CONFIRMAR PED-20251229-0001`.

## 3) Confirmar manual en dashboard (US-033)
PATCH /api/pedidos/{id}/confirmar  (auth:sanctum)
```
curl -X PATCH http://localhost:8000/api/pedidos/1/confirmar \
  -H "Authorization: Bearer TOKEN"
```

## 4) Notificar al cliente su pedido (US-034)
POST /api/whatsapp/pedidos/{id}/notificar-cliente  (auth:sanctum)
```
curl -X POST http://localhost:8000/api/whatsapp/pedidos/1/notificar-cliente \
  -H "Authorization: Bearer TOKEN"
```
Respuesta:
```
{ "exito": true, "mensaje": "Notificación enviada al cliente", "twilio_sid": "SM..." }
```
Nota: el cliente debe tener telefono en la tabla clientes.

## 5) Cambiar estado por cocinero (US-035)
PATCH /api/pedidos/{id}/estado  (auth:sanctum)

Body JSON:
```
{ "estado": "EN_PREPARACION" }
```
Valores permitidos: PENDIENTE, TICKET_ENVIADO, CONFIRMADO, EN_PREPARACION, LISTO, EN_ENTREGA, ENTREGADO, CANCELADO.

Curl:
```
curl -X PATCH http://localhost:8000/api/pedidos/1/estado \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"estado":"EN_PREPARACION"}'
```

## 6) Flujo recomendado
1. Crear pedido (`POST /api/pedidos`).
2. Enviar ticket al dueño (`POST /api/whatsapp/pedidos/{id}/ticket`).
3. Confirmar via webhook o manual (`/confirmar`).
4. Notificar cliente (`/notificar-cliente`).
5. Actualizar estado a LISTO/EN_ENTREGA/ENTREGADO con `/estado`.

## Troubleshooting
- Si Twilio responde error, revisa credenciales y que los numeros tengan formato `+<pais><numero>` y sandbox habilitado.
- Webhook debe estar accesible publicamente; en local usar `ngrok http 8000` y configurar la URL en Twilio console.
- Todos los endpoints protegidos requieren token Sanctum de usuario interno (no cliente) para enviar/gestionar tickets.
