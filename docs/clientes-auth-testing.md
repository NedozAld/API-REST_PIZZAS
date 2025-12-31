# Guia rapida: probar autenticacion de clientes

Base URL de ejemplo: `http://localhost:8000/api`

## 1) Registrar cliente
POST /api/clientes/register

Body JSON:
```
{
  "nombre": "Juan Cliente",
  "email": "juan@example.com",
  "telefono": "555123456",
  "direccion": "Av. Siempre Viva 123",
  "password": "Aa1@aaaa",
  "password_confirmation": "Aa1@aaaa"
}
```
Curl:
```
curl -X POST http://localhost:8000/api/clientes/register \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Juan Cliente",
    "email": "juan@example.com",
    "telefono": "555123456",
    "direccion": "Av. Siempre Viva 123",
    "password": "Aa1@aaaa",
    "password_confirmation": "Aa1@aaaa"
  }'
```
Respuesta 201 esperada:
```
{
  "exito": true,
  "mensaje": "Cliente registrado exitosamente",
  "cliente": { ... },
  "token": "<token_sanctum>"
}
```

## 2) Login cliente
POST /api/clientes/login

Body JSON:
```
{
  "email": "juan@example.com",
  "password": "Aa1@aaaa"
}
```
Curl:
```
curl -X POST http://localhost:8000/api/clientes/login \
  -H "Content-Type: application/json" \
  -d '{"email": "juan@example.com", "password": "Aa1@aaaa"}'
```
Respuesta 200 esperada:
```
{
  "exito": true,
  "mensaje": "Login exitoso",
  "cliente": { ... },
  "token": "<token_sanctum>"
}
```

## 3) Ver perfil (requiere token)
GET /api/clientes/me

Curl (reemplaza TOKEN):
```
curl -X GET http://localhost:8000/api/clientes/me \
  -H "Authorization: Bearer TOKEN"
```
Respuesta 200:
```
{
  "exito": true,
  "cliente": { ... }
}
```

## 4) Ver mis pedidos (requiere token)
GET /api/clientes/me/pedidos

Curl:
```
curl -X GET http://localhost:8000/api/clientes/me/pedidos \
  -H "Authorization: Bearer TOKEN"
```
Respuesta 200:
```
{
  "exito": true,
  "pedidos": [ ... ]
}
```

## 5) Logout (requiere token)
POST /api/clientes/logout

Curl:
```
curl -X POST http://localhost:8000/api/clientes/logout \
  -H "Authorization: Bearer TOKEN"
```
Respuesta 200:
```
{
  "exito": true,
  "mensaje": "Logout exitoso"
}
```

## Notas
- Usa `php artisan serve` o la URL donde tengas desplegada la API.
- Todos los endpoints usan tokens Sanctum devueltos en login/registro.
- Si obtienes 401/403, verifica que el token corresponda a un cliente activo y que se envie en el header Authorization.
