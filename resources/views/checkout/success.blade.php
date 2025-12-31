@extends('layouts.public')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4">
    <div class="max-w-3xl mx-auto text-center">
        <!-- Icono de éxito -->
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-green-100 mb-4">
                <svg class="w-16 h-16 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">¡Pedido Confirmado!</h1>
            <p class="text-lg text-gray-600">Tu pedido #{{ $pedido->id }} ha sido registrado exitosamente</p>
        </div>

        <!-- Detalles del pedido -->
        <div class="bg-white rounded-lg shadow-md p-8 text-left mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Resumen del Pedido</h2>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <p class="text-sm text-gray-600">Número de Pedido</p>
                    <p class="text-lg font-bold text-red-600">#{{ $pedido->id }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Estado</p>
                    <p class="text-lg font-bold text-yellow-600">{{ $pedido->estado }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Fecha</p>
                    <p class="text-lg font-semibold">{{ $pedido->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total</p>
                    <p class="text-lg font-bold text-gray-900">${{ number_format($pedido->total, 2) }}</p>
                </div>
            </div>

            <hr class="my-4">

            <div class="space-y-3 mb-4">
                <div>
                    <p class="text-sm font-semibold text-gray-700">Dirección de Entrega:</p>
                    <p class="text-gray-900">
                        @if ($pedido->direccion_entrega === 'RETIRO EN LOCAL')
                            🏪 <strong>RETIRO EN LOCAL</strong>
                        @else
                            📍 {{ $pedido->direccion_entrega }}
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-700">Teléfono de Contacto:</p>
                    <p class="text-gray-900">📞 {{ $pedido->telefono_contacto }}</p>
                </div>
            </div>

            <hr class="my-4">

            <h3 class="text-lg font-bold text-gray-900 mb-3">Productos:</h3>
            <ul class="space-y-2">
                @foreach ($pedido->detalles as $detalle)
                    <li class="flex justify-between items-center">
                        <span class="text-gray-700">{{ $detalle->producto->nombre ?? 'Producto' }} x{{ $detalle->cantidad }}</span>
                        <span class="font-semibold text-gray-900">${{ number_format($detalle->subtotal, 2) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Siguiente paso -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-bold text-blue-900 mb-2">¿Qué sigue?</h3>
            <ul class="text-left text-blue-800 space-y-2">
                <li>✓ Recibirás una notificación de confirmación vía WhatsApp</li>
                <li>✓ Tu pedido será preparado por nuestro equipo de cocina</li>
                <li>✓ Un repartidor será asignado para la entrega</li>
                <li>✓ Tiempo estimado de entrega: 30-45 minutos</li>
            </ul>
        </div>

        <!-- Acciones -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('cliente.pedidos') }}" class="bg-red-600 text-white px-8 py-3 rounded-lg hover:bg-red-700 transition font-semibold">
                Ver Mis Pedidos
            </a>
            <a href="{El dueño recibirá tu pedido vía WhatsApp</li>
                <li>✓ Tu pedido será confirmado y preparado</li>
                @if ($pedido->direccion_entrega === 'RETIRO EN LOCAL')
                    <li>✓ Podrás recoger tu pedido en nuestro local</li>
                    <li>✓ Tiempo estimado de preparación: 20-30 minutos</li>
                @else
                    <li>✓ Un repartidor llevará tu pedido a tu dirección</li>
                    <li>✓ Tiempo estimado de entrega: 30-45 minutos</li>
                @endif

        <!-- Mensaje de agradecimiento -->
        <p class="mt-8 text-gray-600">
            Gracias por tu preferencia. ¡Disfruta tu comida! 🍕
        </p>
    </div>
</div>
@endsection
