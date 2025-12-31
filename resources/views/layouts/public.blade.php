<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'La Pizzería - Las mejores pizzas de la ciudad')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center space-x-2">
                    <span class="text-3xl">🍕</span>
                    <span class="text-2xl font-bold text-red-600">La Pizzería</span>
                </a>

                <!-- Search Bar (Desktop) -->
                <div class="hidden md:block flex-1 max-w-xl mx-8">
                    <form action="{{ route('menu.search') }}" method="GET" class="relative">
                        <input 
                            type="search" 
                            name="q"
                            placeholder="Buscar productos..." 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            value="{{ request('q') }}"
                        >
                        <button type="submit" class="absolute right-3 top-2.5 text-gray-500 hover:text-red-600">
                            🔍
                        </button>
                    </form>
                </div>

                <!-- Cart & Auth -->
                <div class="flex items-center space-x-4">
                    <!-- Cart -->
                    <a href="{{ route('cart.show') }}" class="relative hover:text-red-600 transition">
                        <span class="text-2xl">🛒</span>
                        @if(session('cart_count', 0) > 0)
                            <span class="absolute -top-2 -right-2 bg-red-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold">
                                {{ session('cart_count', 0) }}
                            </span>
                        @endif
                    </a>

                    <!-- Auth Links -->
                    @if (session('cliente_token'))
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2 hover:text-red-600 transition">
                                <span>👤</span>
                                <span class="hidden md:inline">{{ session('cliente_nombre', 'Cliente') }}</span>
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2">
                                <a href="{{ route('cliente.perfil') }}" class="block px-4 py-2 hover:bg-gray-100">Mi Perfil</a>
                                <a href="{{ route('cliente.pedidos') }}" class="block px-4 py-2 hover:bg-gray-100">Mis Pedidos</a>
                                <hr class="my-2">
                                <form method="POST" action="{{ route('cliente.logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600">
                                        Cerrar Sesión
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('cliente.login') }}" class="text-gray-700 hover:text-red-600 transition">
                            Ingresar
                        </a>
                    @endif
                </div>
            </div>

            <!-- Mobile Search -->
            <div class="md:hidden mt-3">
                <form action="{{ route('menu.search') }}" method="GET" class="relative">
                    <input 
                        type="search" 
                        name="q"
                        placeholder="Buscar..." 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                    >
                </form>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="min-h-screen">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-16">
        <div class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Contact -->
                <div>
                    <h3 class="text-lg font-bold mb-4">Contacto</h3>
                    <p class="text-gray-400">📞 +593 99 912 3456</p>
                    <p class="text-gray-400">✉️ info@lapizzeria.ec</p>
                    <p class="text-gray-400">📍 Guayaquil, Ecuador</p>
                </div>

                <!-- Hours -->
                <div>
                    <h3 class="text-lg font-bold mb-4">Horarios</h3>
                    <p class="text-gray-400">Lun - Dom: 11:00 AM - 11:00 PM</p>
                </div>

                <!-- Links -->
                <div>
                    <h3 class="text-lg font-bold mb-4">Enlaces</h3>
                    <a href="{{ route('menu.public') }}" class="block text-gray-400 hover:text-white mb-2">Menú</a>
                    <a href="#" class="block text-gray-400 hover:text-white mb-2">Términos y Condiciones</a>
                    <a href="#" class="block text-gray-400 hover:text-white">Política de Privacidad</a>
                </div>
            </div>

            <hr class="my-6 border-gray-700">

            <div class="text-center text-gray-400">
                <p>&copy; {{ date('Y') }} La Pizzería. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
