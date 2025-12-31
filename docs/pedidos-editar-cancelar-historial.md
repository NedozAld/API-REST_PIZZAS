# Continuación Módulo 4: Editar, Cancelar e Historial Pedidos

Base URL: `http://localhost:8000/api`

## US-025: Historial/Listar Pedidos con Filtros
GET /api/pedidos  (auth:sanctum)

Parámetros query opcionales:
- `estado` = PENDIENTE | CONFIRMADO | ENTREGADO | CANCELADO, etc.
- `fecha_desde` = fecha ISO (2025-01-01)
- `fecha_hasta` = fecha ISO
- `numero_pedido` = búsqueda parcial
- `cliente_id` = ID del cliente

Curl:
```
curl -X GET "http://localhost:8000/api/pedidos?estado=CONFIRMADO&fecha_desde=2025-01-01" \
  -H "Authorization: Bearer TOKEN"
```

Respuesta 200:
```json
{
  "success": true,
  "filtros_aplicados": {
    "estado": "CONFIRMADO",
    "fecha_desde": "2025-01-01",
    "fecha_hasta": null,
    "numero_pedido": null
  },
  "data": { /* paginación */ }
}
```

## US-024: Editar Pedido (Solo si está PENDIENTE)
PUT /api/pedidos/{id}  (auth:sanctum)

Body JSON:
```json
{
  "items": [
    {
      "producto_id": 1,
      "cantidad": 3,
      "notas": "Sin cebolla"
    }
  ],
  "costo_entrega": 5,
  "monto_descuento": 0,
  "notas": "Entregar después de las 7pm"
}
```

Curl:
```
curl -X PUT http://localhost:8000/api/pedidos/1 \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [{"producto_id": 1, "cantidad": 3}],
    "costo_entrega": 5
  }'
```

Respuesta 200:
```json
{
  "success": true,
  "message": "Pedido actualizado exitosamente",
  "data": { "pedido": { /* detalles */ } }
}
```

Errores:
- 400: Pedido no está en estado PENDIENTE
- 404: Pedido/producto no encontrado

## US-023: Cancelar Pedido
DELETE /api/pedidos/{id}  (auth:sanctum)

Body JSON:
```json
{
  "motivo": "Cliente solicitó cancelación"
}
```

Curl:
```
curl -X DELETE http://localhost:8000/api/pedidos/1 \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"motivo": "Cliente cambió de opinión"}'
```

Respuesta 200:
```json
{
  "success": true,
  "message": "Pedido cancelado exitosamente",
  "data": {
    "pedido_id": 1,
    "estado": "CANCELADO",
    "motivo": "Cliente cambió de opinión"
  }
}
```

Notas:
- No se eliminan los registros, se marcan como CANCELADO
- Se restaura el stock automáticamente
- No se puede cancelar si está en ENTREGADO o ya CANCELADO

## Flujo de ejemplo
1. Crear pedido: `POST /api/pedidos`
2. Ver en estado PENDIENTE: `GET /api/pedidos/1`
3. Editar si falta algo: `PUT /api/pedidos/1` (antes de confirmar)
4. Si cliente cancela: `DELETE /api/pedidos/1` con motivo
5. Ver historial filtrado: `GET /api/pedidos?estado=CANCELADO`

## Notificaciones generadas
- Edición: `tipo: pedido_editado`
- Cancelación: `tipo: pedido_cancelado`
- Ambas disparan eventos en SSE
