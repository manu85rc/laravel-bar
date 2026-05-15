<!-- Navbar and mobile menu share this scope -->
<div class="relative" x-data="{ open: false }">
    <!-- Navbar -->
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ url('/') }}" class="text-xl font-semibold text-indigo-600">
                            🍸 {{ isset($branding) ? $branding->app_name : (App\Models\Branding::getInstance()->app_name ?? 'Laravel Bar') }}
                        </a>
                    </div>
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-4">
                            @auth
                                <a href="{{ url('/dashboard') }}"
                                   class="px-3 py-2 rounded-md text-sm font-medium {{ request()->is('/dashboard') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                                    Dashboard
                                </a>
                                
                                <!-- Secciones para usuarios autenticados -->
                                <a href="{{ url('/mesas') }}"
                                   class="px-3 py-2 rounded-md text-sm font-medium {{ request()->is('/mesas*') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                                    Mesas
                                </a>
                                
                                <a href="{{ url('/reservas') }}"
                                   class="px-3 py-2 rounded-md text-sm font-medium {{ request()->is('/reservas*') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                                    Reservas
                                </a>
                                
                                @if (auth()->user()->isAdmin())
                                    <a href="{{ url('/admin/users') }}"
                                       class="px-3 py-2 rounded-md text-sm font-medium {{ request()->is('/admin*') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                                        Admin
                                    </a>
                                    <a href="{{ url('/admin/branding/edit') }}"
                                       class="px-3 py-2 rounded-md text-sm font-medium {{ request()->is('/admin/branding*') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                                        Branding
                                    </a>
                                    <a href="{{ url('/admin/mesas') }}"
                                       class="px-3 py-2 rounded-md text-sm font-medium {{ request()->is('/admin/mesas*') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                                        Mesas
                                    </a>
                                    <a href="{{ url('/admin/categorias') }}"
                                       class="px-3 py-2 rounded-md text-sm font-medium {{ request()->is('/admin/categorias*') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                                        Categorías
                                    </a>
                                    <a href="{{ url('/admin/productos') }}"
                                       class="px-3 py-2 rounded-md text-sm font-medium {{ request()->is('/admin/productos*') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                                        Productos
                                    </a>
                                @endif
                            @endauth
                        </div>
                    </div>
                    <div class="flex items-center">
                        @auth
                            <div class="ml-4 flex items-center md:ml-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <img class="h-8 w-8 rounded-full" 
                                             src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name )}}&background=random&size=128"
                                             alt="">
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-base font-medium text-gray-900">{{ auth()->user()->name }}</div>
                                        <div class="text-sm font-medium text-gray-500">{{ auth()->user()->role == 'admin' ? 'Administrador' : 'Usuario' }}</div>
                                    </div>
                                </div>
                                
                                <form method="POST" action="{{ route('logout') }}"
                                      class="ml-3 inline">
                                    @csrf
                                    <button type="submit"
                                            class="px-3 py-2 rounded-md text-sm font-medium text-gray-500 hover:text-gray-700">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="hidden md:block">
                                <a href="{{ route('login') }}"
                                   class="mr-3 px-3 py-2 rounded-md text-sm font-medium {{ request()->is('login*') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                                    Iniciar sesión
                                </a>
                                <a href="{{ route('register') }}"
                                   class="px-3 py-2 rounded-md text-sm font-medium bg-indigo-600 text-white hover:bg-indigo-700">
                                    Registrarse
                                </a>
                            </div>
                        @endauth
                        
                        <!-- Mobile menu button -->
                        <div class="-mr-2 flex items-center md:hidden">
                            <button type="button"
                                    class="bg-white inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    @click="open = !open"
                                    :aria-expanded="open"
                                    aria-controls="mobile-menu"
                                    aria-label="Open main menu">
                                <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" :class="{'hidden': open, 'inline-flex': !open}">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                </svg>
                                <svg class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" :class="{'inline-flex': open, 'hidden': !open}">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile menu -->
    <div class="md:hidden" id="mobile-menu" x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform translate-x-4 sm:translate-x-0 sm:scale-95" x-transition:enter-end="opacity-100 transform translate-x-0 sm:scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform translate-x-0 sm:scale-100" x-transition:leave-end="opacity-0 transform translate-x-4 sm:translate-x-0 sm:scale-95">
        <div class="pt-2 pb-3 space-y-1">
            @auth
                <a href="{{ url('/dashboard') }}"
                   class="block px-3 py-2 rounded-md text-base font-medium {{ request()->is('/dashboard') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:bg-gray-50' }}"
                   @click="setTimeout(() => { open = false; }, 50)">
                    Dashboard
                </a>
                
                <a href="{{ url('/mesas') }}"
                   class="block px-3 py-2 rounded-md text-base font-medium {{ request()->is('/mesas*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:bg-gray-50' }}"
                   @click="setTimeout(() => { open = false; }, 50)">
                    Mesas
                </a>
                
                <a href="{{ url('/reservas') }}"
                   class="block px-3 py-2 rounded-md text-base font-medium {{ request()->is('/reservas*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:bg-gray-50' }}"
                   @click="setTimeout(() => { open = false; }, 50)">
                    Reservas
                </a>
                
                @if (auth()->user()->isAdmin())
                    <div x-data="{ adminOpen: false }" class="relative w-full">
                        <button @click="adminOpen = ! adminOpen" class="w-full flex items-center justify-between px-3 py-2 rounded-md text-base font-medium {{ request()->is('/admin*') || request()->is('/admin/branding*') || request()->is('/admin/mesas*') || request()->is('/admin/categorias*') || request()->is('/admin/productos*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:bg-gray-50' }}">
                            Admin
                            <svg class="h-4 w-4 ml-2" :class="{ 'transform -rotate-180': adminOpen }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        <div x-show="adminOpen" x-transition x-origin="top" class="mt-2 space-y-1">
                            <a href="{{ url('/admin/users') }}"
                               class="block px-3 py-2 rounded-md text-base font-medium {{ request()->is('/admin/users*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:bg-gray-50' }}"
                               @click="setTimeout(() => { open = false; adminOpen = false; }, 50)">
                                Users
                            </a>
                            <a href="{{ url('/admin/mesas') }}"
                               class="block px-3 py-2 rounded-md text-base font-medium {{ request()->is('/admin/mesas*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:bg-gray-50' }}"
                               @click="setTimeout(() => { open = false; adminOpen = false; }, 50)">
                                Mesas
                            </a>
                            <a href="{{ url('/admin/categorias') }}"
                               class="block px-3 py-2 rounded-md text-base font-medium {{ request()->is('/admin/categorias*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:bg-gray-50' }}"
                               @click="setTimeout(() => { open = false; adminOpen = false; }, 50)">
                                Categorías
                            </a>
                            <a href="{{ url('/admin/productos') }}"
                               class="block px-3 py-2 rounded-md text-base font-medium {{ request()->is('/admin/productos*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:bg-gray-50' }}"
                               @click="setTimeout(() => { open = false; adminOpen = false; }, 50)">
                                Productos
                            </a>
                            <a href="{{ url('/admin/branding/edit') }}"
                               class="block px-3 py-2 rounded-md text-base font-medium {{ request()->is('/admin/branding*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:bg-gray-50' }}"
                               @click="setTimeout(() => { open = false; adminOpen = false; }, 50)">
                                Branding
                            </a>
                        </div>
                    </div>
                @endif
            @else
                <a href="{{ route('login') }}"
                   class="block px-3 py-2 rounded-md text-base font-medium {{ request()->is('login*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:bg-gray-50' }}"
                   @click="setTimeout(() => { open = false; }, 50)">
                    Iniciar sesión
                </a>
                <a href="{{ route('register') }}"
                   class="block px-3 py-2 rounded-md text-base font-medium bg-indigo-600 text-white hover:bg-indigo-700"
                   @click="setTimeout(() => { open = false; }, 50)">
                    Registrarse
                </a>
            @endauth
            
            @auth
                <div class="pt-4 pb-3 border-t border-gray-200">
                    <form method="POST" action="{{ route('logout') }}" class="space-y-1">
                        @csrf
                        <button type="submit"
                                class="w-full text-left px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-50"
                                @click="setTimeout(() => { open = false; }, 50)">
                            Logout ({{ auth()->user()->name }})
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </div>
</div>