@extends('layouts.public')

@section('content')
<div class="max-w-6xl mx-auto py-12 px-4">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Finalizar Pedido</h1>

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm text-red-700 font-semibold">Hay errores en el formulario:</p>
            <ul class="list-disc list-inside text-sm text-red-600 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-red-700">{{ session('error') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('checkout.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Columna izquierda: Formulario -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Paso 1: Tipo de Entrega -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-600 text-white mr-2">1</span>
                        Tipo de Entrega
                    </h2>

                    <div class="space-y-3">
                        <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition has-[:checked]:border-red-600 has-[:checked]:bg-red-50">
                            <input 
                                type="radio" 
                                name="tipo_entrega" 
                                value="domicilio" 
                                {{ old('tipo_entrega', 'domicilio') == 'domicilio' ? 'checked' : '' }}
                                class="h-4 w-4 text-red-600 focus:ring-red-500"
                                onchange="toggleDireccion(true)"
                            >
                            <div class="ml-3">
                                <p class="font-semibold text-gray-900">🏠 Envío a Domicilio</p>
                                <p class="text-sm text-gray-600">Te lo llevamos a tu dirección</p>
                            </div>
                        </label>

                        <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition has-[:checked]:border-red-600 has-[:checked]:bg-red-50">
                            <input 
                                type="radio" 
                                name="tipo_entrega" 
                                value="local"
                                {{ old('tipo_entrega') == 'local' ? 'checked' : '' }}
                                class="h-4 w-4 text-red-600 focus:ring-red-500"
                                onchange="toggleDireccion(false)"
                            >
                            <div class="ml-3">
                                <p class="font-semibold text-gray-900">🏪 Retiro en Local</p>
                                <p class="text-sm text-gray-600">Recoges tu pedido en nuestro local</p>
                            </div>
                        </label>
                    </div>

                    <!-- Dirección (solo si es domicilio) -->
                    <div id="direccion-section" class="mt-4">
                        <label for="direccion_entrega" class="block text-sm font-medium text-gray-700">Dirección de Entrega *</label>
                        <input 
                            type="text" 
                            name="direccion_entrega" 
                            id="direccion_entrega"
                            value="{{ old('direccion_entrega', $cliente->direccion ?? '') }}"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                            placeholder="Av. Principal 123, Departamento 4B"
                        >
                        <p class="mt-1 text-xs text-gray-600">Incluye referencias (color de casa, número de piso, etc.)</p>
                    </div>
                </div>

                <!-- Paso 2: Datos de Contacto -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-600 text-white mr-2">2</span>
                        Datos de Contacto
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <label for="telefono_contacto" class="block text-sm font-medium text-gray-700">Teléfono de Contacto *</label>
                            <input 
                                type="tel" 
                                name="telefono_contacto" 
                                id="telefono_contacto"
                                value="{{ old('telefono_contacto', $cliente->telefono ?? '') }}"
                                required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                                placeholder="+593 999 888 777"
                            >
                        </div>

                        <div>
                            <label for="notas" class="block text-sm font-medium text-gray-700">Notas Adicionales (Opcional)</label>
                            <textarea 
                                name="notas" 
                                id="notas"
                                rows="3"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                                placeholder="Ej: Sin cebolla, extra queso, etc."
                            >{{ old('notas') }}</textarea>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Columna derecha: Resumen -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow p-6 sticky top-4">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Resumen del Pedido</h2>

                    <!-- Items -->
                    <div class="space-y-3 mb-6 max-h-64 overflow-y-auto">
                        @foreach ($cart as $item)
                            <div class="flex justify-between items-start text-sm">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900">{{ $item['nombre'] }}</p>
                                    <p class="text-gray-600">Cantidad: {{ $item['cantidad'] }}</p>
                                </div>
                                <p class="font-semibold text-gray-900">${{ number_format($item['precio'] * $item['cantidad'], 2) }}</p>
                            </div>
                        @endforeach
                    </div>

                    <hr class="my-4">

                    <!-- Totales -->
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-semibold">${{ number_format($subtotal, 2) }}</span>
                        </div>

                        @if ($descuento > 0)
                            <div class="flex justify-between text-sm text-green-600">
                                <span>Descuento:</span>
                                <span class="font-semibold">-${{ number_format($descuento, 2) }}</span>
                            </div>
                        @endif

                        <div class="flex justify-between text-lg font-bold text-gray-900 pt-2 border-t">
                            <span>Total:</span>
                            <span>${{ number_format($total, 2) }}</span>
                        </div>
                    </div>

                    <!-- Botón Confirmar -->
                    <button 
                        type="submit"
                        class="mt-6 w-full bg-red-600 text-white py-3 rounded-lg hover:bg-red-700 transition font-bold"
                    >
                        CONFIRMAR PEDIDO
                    </button>

                    <a href="{{ route('cart.show') }}" class="block mt-3 text-center text-sm text-gray-600 hover:text-red-600 transition">
                        Volver al carrito
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const domicilioRadio = document.querySelector('input[name="tipo_entrega"][value="domicilio"]');
    const direccionSection = document.getElementById('direccion-section');
    const direccionInput = document.getElementById('direccion_entrega');
    
    // Función para mostrar/ocultar dirección
    window.toggleDireccion = function(show) {
        if (show) {
            direccionSection.style.display = 'block';
            direccionInput.required = true;
        } else {
            direccionSection.style.display = 'none';
            direccionInput.required = false;
            direccionInput.value = '';
        }
    };

    // Mostrar/ocultar dirección según selección inicial
    if (domicilioRadio.checked) {
        toggleDireccion(true);
    } else {
        toggleDireccion(false);
    }
});
</script>
@endsection
