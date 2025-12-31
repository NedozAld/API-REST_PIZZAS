@extends('layouts.public')

@section('title', 'La Pizzería - Inicio')

@section('content')
<!-- Hero Banner -->
<div class="bg-gradient-to-r from-red-600 to-red-800 text-white py-20">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-5xl font-bold mb-4">🍕 Bienvenido a La Pizzería</h1>
        <p class="text-xl mb-8">Las mejores pizzas artesanales de la ciudad</p>
        <a href="{{ route('menu.public') }}" class="bg-white text-red-600 px-8 py-3 rounded-full font-bold hover:bg-gray-100 transition inline-block">
            Ver Menú Completo
        </a>
    </div>
</div>

<!-- Categories Tabs -->
<div class="container mx-auto px-4 py-8">
    <div class="flex space-x-4 overflow-x-auto pb-4">
        <a href="{{ route('menu.public') }}" 
           class="px-6 py-3 rounded-full {{ !request('categoria') ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }} transition whitespace-nowrap">
            Todos
        </a>
        @foreach($categorias as $categoria)
            <a href="{{ route('menu.public', ['categoria' => $categoria->id]) }}" 
               class="px-6 py-3 rounded-full {{ request('categoria') == $categoria->id ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }} transition whitespace-nowrap">
                {{ $categoria->nombre }}
            </a>
        @endforeach
    </div>
</div>

<!-- Products Grid -->
<div class="container mx-auto px-4 pb-16">
    <h2 class="text-3xl font-bold mb-8">{{ request('categoria') ? 'Productos de ' . $categorias->firstWhere('id', request('categoria'))->nombre : 'Todos los Productos' }}</h2>
    
    @if($productos->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($productos as $producto)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition">
                    <!-- Image -->
                    <div class="relative h-48 bg-gray-200">
                        @if($producto->imagen_url)
                            <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-6xl">
                                🍕
                            </div>
                        @endif
                        @if($producto->descuento_porcentaje > 0)
                            <span class="absolute top-2 right-2 bg-red-600 text-white px-3 py-1 rounded-full text-sm font-bold">
                                -{{ $producto->descuento_porcentaje }}%
                            </span>
                        @endif
                    </div>

                    <!-- Content -->
                    <div class="p-4">
                        <h3 class="font-bold text-lg mb-2 truncate">{{ $producto->nombre }}</h3>
                        <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ $producto->descripcion }}</p>
                        
                        <!-- Category -->
                        <span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded mb-3">
                            {{ $producto->categoria->nombre }}
                        </span>

                        <!-- Price -->
                        <div class="mb-4">
                            @if($producto->descuento_porcentaje > 0)
                                <div class="flex items-center space-x-2">
                                    <span class="text-gray-400 line-through text-sm">${{ number_format($producto->precio_base, 2) }}</span>
                                    <span class="text-red-600 font-bold text-xl">${{ number_format($producto->precio_con_descuento, 2) }}</span>
                                </div>
                            @else
                                <span class="text-gray-900 font-bold text-xl">${{ number_format($producto->precio_base, 2) }}</span>
                            @endif
                        </div>

                        <!-- Stock -->
                        @if($producto->stock_disponible > 0)
                            <p class="text-green-600 text-sm mb-3">✓ Disponible ({{ $producto->stock_disponible }} unidades)</p>
                        @else
                            <p class="text-red-600 text-sm mb-3">✗ Agotado</p>
                        @endif

                        <!-- Add to Cart -->
                        @if($producto->stock_disponible > 0)
                            <button 
                                onclick="addToCart({{ $producto->id }}, '{{ $producto->nombre }}', {{ $producto->precio_con_descuento ?? $producto->precio_base }})"
                                class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition font-semibold">
                                Agregar al Carrito
                            </button>
                        @else
                            <button disabled class="w-full bg-gray-300 text-gray-500 py-2 rounded-lg cursor-not-allowed font-semibold">
                                No Disponible
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($productos->hasPages())
            <div class="mt-8">
                {{ $productos->links() }}
            </div>
        @endif
    @else
        <div class="text-center py-16">
            <p class="text-gray-500 text-xl">No hay productos disponibles en esta categoría</p>
        </div>
    @endif
</div>

@push('scripts')
<script>
function addToCart(productoId, nombre, precio) {
    // Mostrar modal de confirmación
    if (confirm(`¿Agregar ${nombre} al carrito?`)) {
        // Enviar a backend
        fetch('{{ route("cart.add") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                producto_id: productoId,
                cantidad: 1,
                precio: precio
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✓ Producto agregado al carrito');
                // Actualizar contador del carrito
                location.reload();
            } else {
                alert('✗ Error al agregar al carrito');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('✗ Error de conexión');
        });
    }
}
</script>
@endpush
@endsection
