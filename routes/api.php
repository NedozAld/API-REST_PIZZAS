<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClienteAuthController;
use App\Http\Controllers\Api\WhatsAppController;
use App\Http\Controllers\Api\NotificacionController;
use App\Http\Controllers\Api\ReportesController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\AuditoriaController;
use App\Http\Controllers\Api\DireccionClienteController;
use Illuminate\Support\Facades\Log;

// Rutas públicas de autenticación
Route::prefix('auth')->group(function () {
    // Registro e iniciar sesión (con rate limiting: 3 intentos en 15 minutos)
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register')->name('auth.register');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('auth.login');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:forgot-password')->name('auth.forgot-password');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:reset-password')->name('auth.reset-password');

    // Rutas protegidas (requieren token)
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
        Route::post('/change-password', [AuthController::class, 'changePassword'])->name('auth.change-password');
        Route::get('/verify-token', [AuthController::class, 'verifyToken'])->name('auth.verify-token');
    });
});

// Autenticación de clientes (registro, login, perfil y pedidos propios)
Route::prefix('clientes')->group(function () {
    Route::post('/register', [ClienteAuthController::class, 'register'])->name('clientes.register');
    Route::post('/login', [ClienteAuthController::class, 'login'])->name('clientes.login');

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/me', [ClienteAuthController::class, 'me'])->name('clientes.me');
        Route::get('/me/pedidos', [ClienteAuthController::class, 'pedidos'])->name('clientes.pedidos');
        Route::post('/logout', [ClienteAuthController::class, 'logout'])->name('clientes.logout');
        
        // Direcciones del cliente (US-044: Múltiples direcciones)
        Route::get('/{cliente_id}/direcciones', [DireccionClienteController::class, 'index'])->name('direcciones.index');
        Route::post('/{cliente_id}/direcciones', [DireccionClienteController::class, 'store'])->name('direcciones.store');
        Route::get('/{cliente_id}/direcciones/favorita/obtener', [DireccionClienteController::class, 'obtenerFavorita'])->name('direcciones.favorita');
        Route::get('/{cliente_id}/direcciones/{id}', [DireccionClienteController::class, 'show'])->name('direcciones.show');
        Route::put('/{cliente_id}/direcciones/{id}', [DireccionClienteController::class, 'update'])->name('direcciones.update');
        Route::patch('/{cliente_id}/direcciones/{id}/favorita', [DireccionClienteController::class, 'marcarFavorita'])->name('direcciones.favorita.update');
        Route::delete('/{cliente_id}/direcciones/{id}', [DireccionClienteController::class, 'destroy'])->name('direcciones.destroy');
    });
});

// Rutas de diagnóstico rápidas
Route::post('/ping', function (Request $request) {
    return response()->json([
        'ok' => true,
        'method' => $request->method(),
        'path' => $request->path(),
        'content_type' => $request->header('Content-Type'),
    ], 200);
});

Route::any('/echo', function (Request $request) {
    Log::info('Echo request', [
        'method' => $request->method(),
        'path' => $request->path(),
        'headers' => $request->headers->all(),
    ]);
    return response()->json([
        'method' => $request->method(),
        'path' => $request->path(),
        'query' => $request->query(),
        'body' => $request->all(),
    ], 200);
});

// Prueba directa de inserción en usuarios (temporal)
Route::post('/test-register', function (Request $request) {
    $email = 'tester_' . uniqid() . '@example.com';
    $nombre = $request->input('nombre', 'Tester');
    $telefono = $request->input('telefono', null);
    $password = $request->input('password', 'Aa1@aaaa');

    $usuario = \App\Models\Usuario::create([
        'nombre' => $nombre,
        'email' => $email,
        'password_hash' => Illuminate\Support\Facades\Hash::make($password),
        'telefono' => $telefono,
        'rol_id' => 4,
        'estado' => 'activo',
    ]);

    return response()->json([
        'ok' => true,
        'id' => $usuario->id,
        'email' => $usuario->email,
    ], 201);
});

// Productos API
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\CategoriaController;

Route::get('/menu', [ProductoController::class, 'menuPublico'])->name('productos.menu'); // US-012 + US-014

Route::middleware(['auth:sanctum'])->group(function () {
    // Productos
    Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index'); // US-014: Listar con filtros
    Route::get('/productos/stock-bajo', [ProductoController::class, 'stockBajo'])->name('productos.stock-bajo'); // US-015: Alerta stock bajo
    Route::post('/productos', [ProductoController::class, 'store'])->name('productos.store'); // US-010
    Route::patch('/productos/{id}', [ProductoController::class, 'update'])->name('productos.update'); // US-011
    Route::patch('/productos/{id}/precio', [ProductoController::class, 'actualizarPrecio'])->name('productos.actualizar-precio'); // US-011
    Route::patch('/productos/{id}/descuento', [ProductoController::class, 'actualizarDescuento'])->name('productos.actualizar-descuento'); // US-082

    // Categorías
    Route::get('/categorias', [CategoriaController::class, 'index'])->name('categorias.index'); // US-013
    Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store');
    Route::get('/categorias/{id}', [CategoriaController::class, 'show'])->name('categorias.show');
    Route::put('/categorias/{id}', [CategoriaController::class, 'update'])->name('categorias.update');
    Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');
    Route::get('/categorias/{id}/estadisticas', [CategoriaController::class, 'estadisticas'])->name('categorias.estadisticas');
});

// Pedidos API
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\CuponController;

Route::middleware(['auth:sanctum'])->prefix('pedidos')->group(function () {
    Route::get('/', [PedidoController::class, 'index'])->name('pedidos.index'); // Listar pedidos (US-025)
    Route::get('/buscar', [PedidoController::class, 'buscar'])->name('pedidos.buscar'); // US-028: Búsqueda avanzada
    Route::post('/', [PedidoController::class, 'store'])->name('pedidos.store'); // US-020: Crear pedido
    Route::post('/repetir/{id}', [PedidoController::class, 'repetirPedido'])->name('pedidos.repetir'); // US-029: Reasumir pedido
    Route::get('/{id}', [PedidoController::class, 'show'])->name('pedidos.show'); // US-022: Ver estado pedido
    Route::patch('/{id}/confirmar', [PedidoController::class, 'confirmar'])->name('pedidos.confirmar'); // US-021: Confirmar pedido
    Route::patch('/{id}/entregado', [PedidoController::class, 'marcarEntregado'])->name('pedidos.entregado'); // US-026: Marcar entregado
    Route::put('/{id}/notas', [PedidoController::class, 'agregarNotas'])->name('pedidos.notas'); // US-027: Agregar notas
    Route::post('/{id}/cupon', [PedidoController::class, 'aplicarCupon'])->name('pedidos.aplicar-cupon'); // US-081: Aplicar cupón
    Route::put('/{id}', [PedidoController::class, 'update'])->name('pedidos.update'); // US-024: Editar pedido
    Route::delete('/{id}', [PedidoController::class, 'destroy'])->name('pedidos.destroy'); // US-023: Cancelar pedido
    Route::patch('/{id}/estado', [PedidoController::class, 'actualizarEstado'])->name('pedidos.actualizar-estado'); // US-035: Cambiar estado
});

// Cupones API (Módulo 10 - Fase 4)
Route::middleware(['auth:sanctum'])->prefix('cupones')->group(function () {
    Route::get('/', [CuponController::class, 'index'])->name('cupones.index'); // Listar cupones
    Route::post('/', [CuponController::class, 'store'])->name('cupones.store'); // US-080: Crear cupón
    Route::get('/{id}', [CuponController::class, 'show'])->name('cupones.show'); // Ver cupón
    Route::put('/{id}', [CuponController::class, 'update'])->name('cupones.update'); // Actualizar cupón
    Route::delete('/{id}', [CuponController::class, 'destroy'])->name('cupones.destroy'); // Eliminar cupón
    Route::post('/validar', [CuponController::class, 'validar'])->name('cupones.validar'); // Validar cupón
    Route::get('/{id}/estadisticas', [CuponController::class, 'estadisticas'])->name('cupones.estadisticas'); // Estadísticas
});

// Descuentos por Volumen API (US-083 - Módulo 10)
use App\Http\Controllers\Api\DescuentoVolumenController;

Route::middleware(['auth:sanctum'])->prefix('descuentos-volumen')->group(function () {
    Route::get('/', [DescuentoVolumenController::class, 'index'])->name('descuentos-volumen.index'); // Listar
    Route::post('/', [DescuentoVolumenController::class, 'store'])->name('descuentos-volumen.store'); // Crear
    Route::get('/{id}', [DescuentoVolumenController::class, 'show'])->name('descuentos-volumen.show'); // Ver detalle
    Route::put('/{id}', [DescuentoVolumenController::class, 'update'])->name('descuentos-volumen.update'); // Actualizar
    Route::delete('/{id}', [DescuentoVolumenController::class, 'destroy'])->name('descuentos-volumen.destroy'); // Eliminar
    Route::post('/calcular', [DescuentoVolumenController::class, 'calcular'])->name('descuentos-volumen.calcular'); // Calcular descuento
    Route::get('/vigentes', [DescuentoVolumenController::class, 'vigentes'])->name('descuentos-volumen.vigentes'); // Ver vigentes
});

// WhatsApp / Twilio
Route::middleware(['auth:sanctum'])->prefix('whatsapp')->group(function () {
    Route::post('/pedidos/{id}/ticket', [WhatsAppController::class, 'enviarTicket'])->name('whatsapp.pedidos.ticket'); // US-031
    Route::post('/pedidos/{id}/notificar-cliente', [WhatsAppController::class, 'notificarCliente'])->name('whatsapp.pedidos.notificar'); // US-034
});

// Webhook público Twilio (US-032)
Route::post('/whatsapp/webhook', [WhatsAppController::class, 'webhook'])->name('whatsapp.webhook');

// Notificaciones en tiempo real (SSE)
Route::middleware(['auth:sanctum'])->prefix('notificaciones')->group(function () {
    Route::get('/', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::get('/stream', [NotificacionController::class, 'stream'])->name('notificaciones.stream'); // US-041
    Route::patch('/{id}/vista', [NotificacionController::class, 'marcarVista'])->name('notificaciones.marcar-vista'); // US-040
});

// Reportes y Analytics (Módulo 7 - Fase 3)
Route::middleware(['auth:sanctum'])->prefix('reportes')->group(function () {
    Route::get('/dashboard', [ReportesController::class, 'dashboard'])->name('reportes.dashboard'); // US-050
    Route::get('/diario', [ReportesController::class, 'reporteDiario'])->name('reportes.diario'); // US-051
    Route::get('/semanal', [ReportesController::class, 'reporteSemanal'])->name('reportes.semanal'); // US-052
    Route::get('/mensual', [ReportesController::class, 'reporteMensual'])->name('reportes.mensual'); // US-053
    Route::post('/exportar', [ReportesController::class, 'exportar'])->name('reportes.exportar'); // US-054
    Route::get('/productos-top', [ReportesController::class, 'productosTop'])->name('reportes.productos-top');
    Route::get('/clientes-top', [ReportesController::class, 'clientesTop'])->name('reportes.clientes-top');
});

// Alias para dashboard (nombre único para evitar colisión con web.php)
Route::middleware(['auth:sanctum'])->get('/dashboard', [ReportesController::class, 'dashboard'])->name('api.dashboard');

// Gestión de Usuarios (Módulo 8 - Fase 3)
Route::middleware(['auth:sanctum'])->prefix('usuarios')->group(function () {
    Route::post('/', [UsuarioController::class, 'store'])->name('usuarios.store'); // US-060: Crear usuario
    Route::get('/', [UsuarioController::class, 'index'])->name('usuarios.index'); // US-062: Ver usuarios
    Route::get('/{id}', [UsuarioController::class, 'show'])->name('usuarios.show');
    Route::put('/{id}/rol', [UsuarioController::class, 'asignarRol'])->name('usuarios.asignar-rol'); // US-061: Asignar rol
    Route::patch('/{id}/estado', [UsuarioController::class, 'cambiarEstado'])->name('usuarios.cambiar-estado'); // US-063: Cambiar estado
});

// Auditoría (Módulo 8 - US-064)
Route::middleware(['auth:sanctum'])->prefix('auditoria')->group(function () {
    Route::get('/', [AuditoriaController::class, 'index'])->name('auditoria.index'); // US-064: Historial de auditoría
    Route::get('/estadisticas', [AuditoriaController::class, 'estadisticas'])->name('auditoria.estadisticas');
    Route::get('/usuario/{usuario_id}', [AuditoriaController::class, 'usuarioAuditoria'])->name('auditoria.usuario');
});

// Ruta de ejemplo para usuario autenticado
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
