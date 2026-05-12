<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Tailwind CSS via CDN for simplicity -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="antialiased">
    <div class="min-h-screen bg-gray-100">
        <!-- Navbar -->
        <nav class="bg-white border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <a href="{{ url('/') }}" class="text-xl font-semibold text-indigo-600">
                                {{ config('app.name', 'Laravel') }}
                            </a>
                        </div>
                        <div class="flex-1 ml-10 flex items-baseline space-x-4">
                            <div class="ml-10 flex items-baseline space-x-4">
                                @auth
                                    <a href="{{ url('/dashboard') }}"
                                       class="px-3 py-2 rounded-md text-sm font-medium {{ request()->is('/dashboard') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                                        Dashboard
                                    </a>
                                    @if (auth()->user()->isAdmin())
                                        <a href="{{ url('/mesas') }}"
                                           class="px-3 py-2 rounded-md text-sm font-medium {{ request()->is('/mesas*') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                                            Mesas
                                        </a>
                                    @endif
                                    <form method="POST" action="{{ route('logout') }}"
                                          class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="px-3 py-2 rounded-md text-sm font-medium text-gray-500 hover:text-gray-700">
                                            Logout ({{ auth()->user()->name }})
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('login') }}"
                                       class="px-3 py-2 rounded-md text-sm font-medium {{ request()->is('login*') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                                        Iniciar sesión
                                    </a>
                                    <a href="{{ route('register') }}"
                                       class="ml-4 px-3 py-2 rounded-md text-sm font-medium bg-indigo-600 text-white hover:bg-indigo-700">
                                            Registrarse
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main content -->
        <main>
            @yield('content')
        </main>
    </div>
</body>
</html>
