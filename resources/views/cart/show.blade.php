@extends('layouts.public')

@section('title', 'Carrito de Compras')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">🛒 Carrito de Compras</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(count($cart) > 0)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    @foreach($cart as $id => $item)
                        <div class="flex items-center space-x-4 py-4 border-b last:border-b-0">
                            <!-- Image -->
                            <div class="w-20 h-20 bg-gray-200 rounded flex-shrink-0">
                                @if($item['imagen'])
                                    <img src="{{ $item['imagen'] }}" alt="{{ $item['nombre'] }}" class="w-full h-full object-cover rounded">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-3xl">🍕</div>
                                @endif
                            </div>

                            <!-- Info -->
                            <div class="flex-1">
                                <h3 class="font-bold text-lg">{{ $item['nombre'] }}</h3>
                                <p class="text-gray-600">${{ number_format($item['precio'], 2) }} c/u</p>
                            </div>

                            <!-- Quantity -->
                            <div class="flex items-center space-x-2">
                                <form action="{{ route('cart.update', $id) }}" method="POST" class="flex items-center space-x-2">
                                    @csrf
                                    @method('PUT')
                                    <input 
                                        type="number" 
                                        name="cantidad" 
                                        value="{{ $item['cantidad'] }}" 
                                        min="1" 
                                        max="10"
                                        class="w-16 px-2 py-1 border rounded text-center"
                                        onchange="this.form.submit()"
                                    >
                                </form>
                            </div>

                            <!-- Subtotal -->
                            <div class="text-right">
                                <p class="font-bold text-lg">${{ number_format($item['precio'] * $item['cantidad'], 2) }}</p>
                            </div>

                            <!-- Remove -->
                            <form action="{{ route('cart.remove', $id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    ✕
                                </button>
                            </form>
                        </div>
                    @endforeach

                    <div class="mt-4 flex justify-between">
                        <a href="{{ route('home') }}" class="text-red-600 hover:underline">
                            ← Seguir comprando
                        </a>
                        <form action="{{ route('cart.clear') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-red-600">
                                Vaciar carrito
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-lg p-6 sticky top-20">
                    <h2 class="text-xl font-bold mb-4">Resumen del Pedido</h2>
                    
                    <div class="space-y-3 mb-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-semibold">${{ number_format($total, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Descuento:</span>
                            <span class="font-semibold text-green-600">$0.00</span>
                        </div>
                        <hr>
                        <div class="flex justify-between text-xl font-bold">
                            <span>TOTAL:</span>
                            <span class="text-red-600">${{ number_format($total, 2) }}</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm text-gray-600 mb-2">¿Tienes un cupón?</label>
                        <div class="flex space-x-2">
                            <input 
                                type="text" 
                                placeholder="Código de cupón" 
                                class="flex-1 px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-red-500"
                            >
                            <button class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300 transition">
                                Aplicar
                            </button>
                        </div>
                    </div>

                    @if (session('cliente_token'))
                        <a href="{{ route('checkout.show') }}" class="block w-full bg-red-600 text-white text-center py-3 rounded-lg hover:bg-red-700 transition font-bold">
                            PROCEDER AL PAGO
                        </a>
                    @else
                        <a href="{{ route('cliente.login') }}" class="block w-full bg-red-600 text-white text-center py-3 rounded-lg hover:bg-red-700 transition font-bold">
                            INICIAR SESIÓN PARA PAGAR
                        </a>
                        <p class="text-sm text-gray-600 mt-2 text-center">
                            ¿No tienes cuenta? <a href="{{ route('cliente.register') }}" class="text-red-600 hover:underline">Regístrate aquí</a>
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-lg p-12 text-center">
            <div class="text-6xl mb-4">🛒</div>
            <h2 class="text-2xl font-bold mb-4">Tu carrito está vacío</h2>
            <p class="text-gray-600 mb-6">Agrega productos desde nuestro menú</p>
            <a href="{{ route('home') }}" class="inline-block bg-red-600 text-white px-8 py-3 rounded-lg hover:bg-red-700 transition font-bold">
                Ver Menú
            </a>
        </div>
    @endif
</div>
@endsection
