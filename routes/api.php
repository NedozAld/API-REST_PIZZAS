<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Log;

// Rutas públicas de autenticación
Route::prefix('auth')->group(function () {
    // Registro e iniciar sesión (sin protección)
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('auth.forgot-password');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('auth.reset-password');

    // Rutas protegidas (requieren token)
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
        Route::post('/change-password', [AuthController::class, 'changePassword'])->name('auth.change-password');
        Route::get('/verify-token', [AuthController::class, 'verifyToken'])->name('auth.verify-token');
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

Route::get('/menu', [ProductoController::class, 'menuPublico'])->name('productos.menu');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/productos', [ProductoController::class, 'store'])->name('productos.store');
    Route::patch('/productos/{id}', [ProductoController::class, 'update'])->name('productos.update');
    Route::patch('/productos/{id}/precio', [ProductoController::class, 'actualizarPrecio'])->name('productos.actualizar-precio');
});

// Pedidos API
use App\Http\Controllers\Api\PedidoController;

Route::middleware(['auth:sanctum'])->prefix('pedidos')->group(function () {
    Route::get('/', [PedidoController::class, 'index'])->name('pedidos.index'); // Listar pedidos
    Route::post('/', [PedidoController::class, 'store'])->name('pedidos.store'); // US-020: Crear pedido
    Route::get('/{id}', [PedidoController::class, 'show'])->name('pedidos.show'); // US-022: Ver estado pedido
    Route::patch('/{id}/confirmar', [PedidoController::class, 'confirmar'])->name('pedidos.confirmar'); // US-021: Confirmar pedido
});

// Ruta de ejemplo para usuario autenticado
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
