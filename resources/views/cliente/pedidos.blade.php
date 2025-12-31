@extends('layouts.public')

@section('content')
<div class="max-w-4xl mx-auto py-12 px-4">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Mis Pedidos</h1>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-700">✓ {{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-red-700">✗ {{ session('error') }}</p>
        </div>
    @endif

    @forelse ($pedidos as $pedido)
        <div class="mb-6 bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
            <div class="p-6 border-b border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <p class="text-sm text-gray-600">Pedido #</p>
                        <p class="text-lg font-bold text-gray-900">{{ $pedido['id'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Fecha</p>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ isset($pedido['created_at']) ? date('d/m/Y H:i', strtotime($pedido['created_at'])) : 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Estado</p>
                        <p class="text-lg font-semibold">
                            @if (($pedido['estado'] ?? '') === 'CONFIRMADO')
                                <span class="text-blue-600">✓ Confirmado</span>
                            @elseif (($pedido['estado'] ?? '') === 'ENTREGADO')
                                <span class="text-green-600">✓ Entregado</span>
                            @elseif (($pedido['estado'] ?? '') === 'CANCELADO')
                                <span class="text-red-600">✗ Cancelado</span>
                            @else
                                <span class="text-yellow-600">⏱ {{ $pedido['estado'] ?? 'Pendiente' }}</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total</p>
                        <p class="text-lg font-bold text-red-600">
                            ${{ number_format($pedido['total'] ?? 0, 2) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Detalles -->
            <div class="p-6 bg-gray-50">
                <p class="text-sm font-semibold text-gray-700 mb-3">Productos:</p>
                
                @if (!empty($pedido['detalles']))
                    <ul class="space-y-2">
                        @foreach ($pedido['detalles'] as $detalle)
                            <li class="flex justify-between items-center">
                                <span class="text-gray-700">
                                    {{ $detalle['nombre'] ?? 'Producto' }} x{{ $detalle['cantidad'] ?? 1 }}
                                </span>
                                <span class="text-gray-900 font-semibold">
                                    ${{ number_format($detalle['subtotal'] ?? 0, 2) }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-600 text-sm">Sin detalles disponibles</p>
                @endif

                @if (!empty($pedido['direccion_entrega']))
                    <div class="mt-4 pt-4 border-t border-gray-300">
                        <p class="text-sm font-semibold text-gray-700">Dirección de Entrega:</p>
                        <p class="text-gray-600">📍 {{ $pedido['direccion_entrega'] }}</p>
                    </div>
                @endif
            </div>

            <!-- Acciones -->
            <div class="p-6 bg-white border-t border-gray-200 flex gap-3">
                <a href="#" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                    Ver Detalles
                </a>
                
                @if (($pedido['estado'] ?? '') !== 'ENTREGADO' && ($pedido['estado'] ?? '') !== 'CANCELADO')
                    <a href="#" class="inline-block bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition">
                        Cancelar Pedido
                    </a>
                @endif
            </div>
        </div>
    @empty
        <div class="text-center py-16">
            <p class="text-4xl mb-4">📦</p>
            <p class="text-xl text-gray-600 mb-4">No tienes pedidos aún</p>
            <a href="{{ route('home') }}" class="inline-block bg-red-600 text-white px-8 py-3 rounded-lg hover:bg-red-700 transition font-semibold">
                Ir a hacer un pedido
            </a>
        </div>
    @endforelse
</div>
@endsection
