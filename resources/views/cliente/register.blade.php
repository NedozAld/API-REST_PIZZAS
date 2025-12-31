@extends('layouts.public')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-8">
        <h1 class="text-2xl font-bold text-center text-gray-900 mb-6">Crear Cuenta</h1>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-700 font-semibold">Error en el registro:</p>
                <ul class="list-disc list-inside text-sm text-red-600 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('cliente.register.submit') }}" class="space-y-4">
            @csrf

            <!-- Nombre -->
            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                <input 
                    type="text" 
                    name="nombre" 
                    id="nombre"
                    value="{{ old('nombre') }}"
                    required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                    placeholder="Juan Pérez"
                >
                @error('nombre')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input 
                    type="email" 
                    name="email" 
                    id="email"
                    value="{{ old('email') }}"
                    required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                    placeholder="tu@email.com"
                >
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Teléfono -->
            <div>
                <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
                <input 
                    type="tel" 
                    name="telefono" 
                    id="telefono"
                    value="{{ old('telefono') }}"
                    required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                    placeholder="+34 555 123456"
                >
                @error('telefono')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Dirección -->
            <div>
                <label for="direccion" class="block text-sm font-medium text-gray-700">Dirección (Opcional)</label>
                <input 
                    type="text" 
                    name="direccion" 
                    id="direccion"
                    value="{{ old('direccion') }}"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                    placeholder="Av. Siempre Viva 123"
                >
                @error('direccion')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                <input 
                    type="password" 
                    name="password" 
                    id="password"
                    required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                    placeholder="Mín. 8 caracteres"
                >
                <p class="mt-1 text-xs text-gray-600">
                    Debe contener: mayúsculas, minúsculas, números y caracteres especiales (!@#$%^&*)
                </p>
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
                <input 
                    type="password" 
                    name="password_confirmation" 
                    id="password_confirmation"
                    required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                    placeholder="Repite tu contraseña"
                >
            </div>

            <!-- Terms -->
            <div class="flex items-start">
                <input 
                    type="checkbox" 
                    name="terms" 
                    id="terms"
                    required
                    class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded mt-1"
                >
                <label for="terms" class="ml-2 block text-sm text-gray-700">
                    Acepto los <a href="#" class="text-red-600 hover:underline">términos y condiciones</a>
                </label>
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition font-semibold"
            >
                Crear Cuenta
            </button>
        </form>

        <!-- Login Link -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                ¿Ya tienes cuenta? 
                <a href="{{ route('cliente.login') }}" class="text-red-600 hover:underline font-semibold">
                    Ingresar aquí
                </a>
            </p>
        </div>

        <!-- Back to Home -->
        <a href="{{ route('home') }}" class="block mt-4 text-center text-sm text-gray-600 hover:text-red-600 transition">
            Volver al inicio
        </a>
    </div>
</div>
@endsection
