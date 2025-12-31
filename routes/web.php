<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\CartController;
use App\Http\Controllers\Web\ClienteAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Frontend Público (Cliente)
|--------------------------------------------------------------------------
*/

// Home / Menú público
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/menu', [HomeController::class, 'index'])->name('menu.public');
Route::get('/buscar', [HomeController::class, 'search'])->name('menu.search');

// Carrito (sin autenticación requerida)
Route::get('/carrito', [CartController::class, 'show'])->name('cart.show');
Route::post('/carrito/agregar', [CartController::class, 'add'])->name('cart.add');
Route::put('/carrito/actualizar/{producto}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/carrito/eliminar/{producto}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/carrito/vaciar', [CartController::class, 'clear'])->name('cart.clear');

/*
|--------------------------------------------------------------------------
| Cliente Auth (Registro, Login, Perfil)
|--------------------------------------------------------------------------
*/

Route::get('/cliente/login', [ClienteAuthController::class, 'showLogin'])->name('cliente.login');
Route::post('/cliente/login', [ClienteAuthController::class, 'login'])->name('cliente.login.submit');

Route::get('/cliente/registro', [ClienteAuthController::class, 'showRegister'])->name('cliente.register');
Route::post('/cliente/registro', [ClienteAuthController::class, 'register'])->name('cliente.register.submit');

Route::post('/cliente/logout', [ClienteAuthController::class, 'logout'])->name('cliente.logout');

// Rutas protegidas para cliente autenticado
Route::middleware('auth.cliente')->group(function () {
    Route::get('/cliente/perfil', [ClienteAuthController::class, 'perfil'])->name('cliente.perfil');
    Route::get('/cliente/pedidos', [ClienteAuthController::class, 'pedidos'])->name('cliente.pedidos');
    
    // Checkout (requiere autenticación)
    Route::get('/checkout', [App\Http\Controllers\Web\CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout', [App\Http\Controllers\Web\CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/exito/{pedido}', [App\Http\Controllers\Web\CheckoutController::class, 'success'])->name('checkout.success');
});

/*
|--------------------------------------------------------------------------
| Dashboard Admin (Auth requerido)
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
