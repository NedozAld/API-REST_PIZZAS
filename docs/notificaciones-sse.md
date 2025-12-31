# Guia rapida: Notificaciones en tiempo real (SSE)

Base URL: `http://localhost:8000/api`

Endpoints (auth:sanctum):
- GET /api/notificaciones               -> lista paginada
- PATCH /api/notificaciones/{id}/vista  -> marca como vista
- GET /api/notificaciones/stream        -> SSE 25s, evento `notificaciones`

## Probar SSE con curl
```
curl -N -H "Accept: text/event-stream" \
  -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/notificaciones/stream
```
Deberias ver bloques como:
```
event: notificaciones
data: [{"id":1,"tipo":"pedido_nuevo",...}]
```

## Probar desde navegador simple
Crea un archivo HTML con:
```html
<script>
const es = new EventSource('http://localhost:8000/api/notificaciones/stream', { withCredentials: false });
es.onmessage = (ev) => console.log('msg', ev.data);
es.addEventListener('notificaciones', ev => console.log('evento', JSON.parse(ev.data)));
</script>
```
(Usa fetch previo para obtener el token y ponerlo en header `Authorization` si lo pruebas con fetch + stream; EventSource nativo no permite header, asi que usa un proxy o pega el token en la url como `?token=` si habilitas Sanctum token via query.)

## Marcar como vista
```
curl -X PATCH http://localhost:8000/api/notificaciones/1/vista \
  -H "Authorization: Bearer TOKEN"
```

## Flujo sugerido
1. Crear pedido -> el sistema crea notificacion `pedido_nuevo`.
2. Confirmar pedido (manual o webhook) -> notificacion `pedido_confirmado`.
3. Cambiar estado (`/api/pedidos/{id}/estado`) -> notificacion `pedido_estado`.
4. Consumir SSE en dashboard cocina para sonar alerta cuando llegue evento nuevo.

## Notas
- La conexion SSE dura ~25s y reenvia cada 3s; el cliente debe reconectar automaticamente.
- Ajusta front para reproducir sonido al recibir evento `notificaciones` con items no vistos.
- Si usas ngrok, expone `http://localhost:8000` y consume `https://<tu-ngrok>.ngrok.io/api/notificaciones/stream`.
