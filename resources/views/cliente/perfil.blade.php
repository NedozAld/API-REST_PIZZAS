@extends('layouts.public')

@section('content')
<div class="max-w-2xl mx-auto py-12 px-4">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Mi Perfil</h1>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-700">✓ {{ session('success') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Información Personal -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Información Personal</h2>

            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-600">Nombre</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $cliente['nombre'] ?? 'N/A' }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Email</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $cliente['email'] ?? 'N/A' }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Teléfono</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $cliente['telefono'] ?? 'N/A' }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Dirección</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $cliente['direccion'] ?? 'No registrada' }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Miembro desde</p>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ isset($cliente['created_at']) ? date('d/m/Y', strtotime($cliente['created_at'])) : 'N/A' }}
                    </p>
                </div>
            </div>

            <a href="#" class="mt-6 inline-block bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition">
                Editar Perfil
            </a>
        </div>

        <!-- Acciones Rápidas -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Acciones Rápidas</h2>

            <div class="space-y-3">
                <a href="{{ route('cliente.pedidos') }}" class="block p-4 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition">
                    <p class="font-semibold text-blue-900">📦 Mis Pedidos</p>
                    <p class="text-sm text-blue-700">Ver historial de pedidos</p>
                </a>

                <a href="{{ route('home') }}" class="block p-4 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition">
                    <p class="font-semibold text-green-900">🍕 Continuar Comprando</p>
                    <p class="text-sm text-green-700">Ir al menú de productos</p>
                </a>

                <a href="#" class="block p-4 bg-purple-50 border border-purple-200 rounded-lg hover:bg-purple-100 transition">
                    <p class="font-semibold text-purple-900">🔐 Cambiar Contraseña</p>
                    <p class="text-sm text-purple-700">Actualizar tu contraseña</p>
                </a>

                <form method="POST" action="{{ route('cliente.logout') }}" class="block">
                    @csrf
                    <button type="submit" class="w-full p-4 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition text-left">
                        <p class="font-semibold text-red-900">🚪 Cerrar Sesión</p>
                        <p class="text-sm text-red-700">Salir de tu cuenta</p>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Dirección de Entrega -->
    <div class="mt-8 bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Direcciones de Entrega</h2>

        @if (!empty($cliente['direccion']))
            <div class="mb-4 p-4 bg-gray-50 rounded-lg border-l-4 border-red-600">
                <p class="font-semibold text-gray-900">{{ $cliente['direccion'] }}</p>
                <p class="text-sm text-gray-600 mt-2">Dirección Principal</p>
            </div>
        @endif

        <button class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition">
            + Agregar Nueva Dirección
        </button>
    </div>
</div>
@endsection
