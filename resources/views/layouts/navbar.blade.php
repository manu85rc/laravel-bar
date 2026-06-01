<!-- Navbar and mobile menu share this scope -->
<div class="relative" x-data="{ open: false }">
    <nav style="background:var(--bg-2);border-bottom:1px solid var(--border)">
        <div style="max-width:1200px;margin:0 auto;padding:0 20px">
            <div style="display:flex;justify-content:space-between;height:64px">
                <div style="display:flex">
                    <div style="display:flex;align-items:center;flex-shrink:0">
                        <a href="{{ url('/') }}" style="display:flex;align-items:center;gap:10px;text-decoration:none">
                            @php
                                $brandingInstance = isset($branding) ? $branding : App\Models\Branding::getInstance();
                            @endphp
                            @if ($brandingInstance->logo_path)
                                <img src="{{ $brandingInstance->logoUrl() }}" alt="Logo" style="height:34px;width:34px;border-radius:var(--radius)">
                            @else
                                <div style="width:34px;height:34px;border-radius:var(--radius);background:var(--gold);display:flex;align-items:center;justify-content:center;color:var(--bg-2);font-size:15px"><i class="fa-solid fa-whiskey-glass"></i></div>
                            @endif
                            <span style="font-family:'Playfair Display',serif;font-size:15px;font-weight:600;color:var(--text-1);letter-spacing:0.01em">{{ $brandingInstance->app_name ?? 'Laravel Bar' }}</span>
                        </a>
                    </div>
                    <div style="display:none;align-items:center" class="nav-links-desk">
                        <div style="display:flex;align-items:center;gap:2px;margin-left:24px">
                            @auth
                                @if (auth()->user()->isStaff())
                                <a href="{{ url('/dashboard') }}"
                                   style="padding:8px 14px;border-radius:var(--radius);font-size:13px;font-weight:500;text-decoration:none;transition:all .15s;{{ request()->is('dashboard*') ? 'background:var(--gold-glow);color:var(--gold-light);' : 'color:var(--text-3);' }}"
                                   onmouseover="this.style.background='var(--gold-subtle)';this.style.color='var(--text-2)'"
                                   onmouseout="this.style.background='{{ request()->is('dashboard*') ? 'var(--gold-glow)' : 'transparent' }}';this.style.color='{{ request()->is('dashboard*') ? 'var(--gold-light)' : 'var(--text-3)' }}'">
                                    Dashboard
                                </a>
                                @endif
                                <a href="{{ route('reservas.index') }}"
                                   style="padding:8px 14px;border-radius:var(--radius);font-size:13px;font-weight:500;text-decoration:none;transition:all .15s;{{ request()->routeIs('reservas.*') ? 'background:var(--gold-glow);color:var(--gold-light);' : 'color:var(--text-3);' }}"
                                   onmouseover="this.style.background='var(--gold-subtle)';this.style.color='var(--text-2)'"
                                   onmouseout="this.style.background='{{ request()->routeIs('reservas.*') ? 'var(--gold-glow)' : 'transparent' }}';this.style.color='{{ request()->routeIs('reservas.*') ? 'var(--gold-light)' : 'var(--text-3)' }}'">
                                    Reservas
                                </a>
                                @if (auth()->user()->isStaff())
                                <a href="{{ route('pedidos.index') }}"
                                   style="padding:8px 14px;border-radius:var(--radius);font-size:13px;font-weight:500;text-decoration:none;transition:all .15s;{{ request()->routeIs('pedidos.*') ? 'background:var(--gold-glow);color:var(--gold-light);' : 'color:var(--text-3);' }}"
                                   onmouseover="this.style.background='var(--gold-subtle)';this.style.color='var(--text-2)'"
                                   onmouseout="this.style.background='{{ request()->routeIs('pedidos.*') ? 'var(--gold-glow)' : 'transparent' }}';this.style.color='{{ request()->routeIs('pedidos.*') ? 'var(--gold-light)' : 'var(--text-3)' }}'">
                                    Pedidos
                                </a>
                                @endif
                                @if (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                                    <div x-data="{ adminOpen: false }" style="position:relative">
                                        <button @click="adminOpen = ! adminOpen"
                                                style="padding:8px 14px;border-radius:var(--radius);font-size:13px;font-weight:500;cursor:pointer;border:none;font-family:inherit;transition:all .15s;display:flex;align-items:center;gap:5px;{{ request()->is('admin*') ? 'background:var(--gold-glow);color:var(--gold-light);' : 'background:transparent;color:var(--text-3);' }}"
                                                onmouseover="this.style.background='var(--gold-subtle)';this.style.color='var(--text-2)'"
                                                onmouseout="this.style.background='{{ request()->is('admin*') ? 'var(--gold-glow)' : 'transparent' }}';this.style.color='{{ request()->is('admin*') ? 'var(--gold-light)' : 'var(--text-3)' }}'">
                                            Admin <i class="fa-solid fa-chevron-down" style="font-size:9px" :class="{ 'rotate-180': adminOpen }"></i>
                                        </button>
                                        <div x-show="adminOpen" @click.away="adminOpen = false"
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0 translate-y-1"
                                             x-transition:enter-end="opacity-100 translate-y-0"
                                             x-transition:leave="transition ease-in duration-150"
                                             x-transition:leave-start="opacity-100 translate-y-0"
                                             x-transition:leave-end="opacity-0 translate-y-1"
                                             style="position:absolute;right:0;top:calc(100% + 8px);width:200px;background:var(--bg-3);border:1px solid var(--border);border-radius:var(--radius-lg);box-shadow:0 8px 24px rgba(0,0,0,.5);z-index:50;overflow:hidden">
                                            <div style="padding:6px">
                                                <a href="{{ route('dashboard') }}"
                                                   style="display:block;padding:9px 12px;border-radius:var(--radius);font-size:13px;text-decoration:none;transition:all .12s;{{ request()->routeIs('dashboard') ? 'background:var(--gold-glow);color:var(--gold-light);' : 'color:var(--text-3);' }}"
                                                   onmouseover="this.style.background='var(--gold-subtle)';this.style.color='var(--text-2)'"
                                                   onmouseout="this.style.background='{{ request()->routeIs('dashboard') ? 'var(--gold-glow)' : 'transparent' }}';this.style.color='{{ request()->routeIs('dashboard') ? 'var(--gold-light)' : 'var(--text-3)' }}'">
                                                    Dashboard
                                                </a>
                                                <a href="{{ route('admin.users.index') }}"
                                                   style="display:block;padding:9px 12px;border-radius:var(--radius);font-size:13px;text-decoration:none;transition:all .12s;{{ request()->routeIs('admin.users.*') ? 'background:var(--gold-glow);color:var(--gold-light);' : 'color:var(--text-3);' }}"
                                                   onmouseover="this.style.background='var(--gold-subtle)';this.style.color='var(--text-2)'"
                                                   onmouseout="this.style.background='{{ request()->routeIs('admin.users.*') ? 'var(--gold-glow)' : 'transparent' }}';this.style.color='{{ request()->routeIs('admin.users.*') ? 'var(--gold-light)' : 'var(--text-3)' }}'">
                                                    Usuarios
                                                </a>
                                                <a href="{{ route('admin.mesas.index') }}"
                                                   style="display:block;padding:9px 12px;border-radius:var(--radius);font-size:13px;text-decoration:none;transition:all .12s;{{ request()->routeIs('admin.mesas.*') ? 'background:var(--gold-glow);color:var(--gold-light);' : 'color:var(--text-3);' }}"
                                                   onmouseover="this.style.background='var(--gold-subtle)';this.style.color='var(--text-2)'"
                                                   onmouseout="this.style.background='{{ request()->routeIs('admin.mesas.*') ? 'var(--gold-glow)' : 'transparent' }}';this.style.color='{{ request()->routeIs('admin.mesas.*') ? 'var(--gold-light)' : 'var(--text-3)' }}'">
                                                    Mesas
                                                </a>
                                                <a href="{{ route('admin.menu.index') }}"
                                                   style="display:block;padding:9px 12px;border-radius:var(--radius);font-size:13px;text-decoration:none;transition:all .12s;{{ request()->routeIs('admin.menu.*') ? 'background:var(--gold-glow);color:var(--gold-light);' : 'color:var(--text-3);' }}"
                                                   onmouseover="this.style.background='var(--gold-subtle)';this.style.color='var(--text-2)'"
                                                   onmouseout="this.style.background='{{ request()->routeIs('admin.menu.*') ? 'var(--gold-glow)' : 'transparent' }}';this.style.color='{{ request()->routeIs('admin.menu.*') ? 'var(--gold-light)' : 'var(--text-3)' }}'">
                                                    Menú
                                                </a>
                                                <a href="{{ route('admin.branding.edit') }}"
                                                   style="display:block;padding:9px 12px;border-radius:var(--radius);font-size:13px;text-decoration:none;transition:all .12s;{{ request()->routeIs('admin.branding.*') ? 'background:var(--gold-glow);color:var(--gold-light);' : 'color:var(--text-3);' }}"
                                                   onmouseover="this.style.background='var(--gold-subtle)';this.style.color='var(--text-2)'"
                                                   onmouseout="this.style.background='{{ request()->routeIs('admin.branding.*') ? 'var(--gold-glow)' : 'transparent' }}';this.style.color='{{ request()->routeIs('admin.branding.*') ? 'var(--gold-light)' : 'var(--text-3)' }}'">
                                                    Branding
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endauth
                        </div>
                    </div>
                </div>
                <div style="display:flex;align-items:center">
                    @auth
                        @php
                            $avatarInits = strtoupper(substr(auth()->user()->name, 0, 1) . substr(auth()->user()->name, strpos(auth()->user()->name, ' ') + 1, 1));
                            if (strlen($avatarInits) < 2) $avatarInits = strtoupper(substr(auth()->user()->name, 0, 2));
                        @endphp
                        <div x-data="{ userOpen: false }" style="display:flex;align-items:center;position:relative">
                            <button @click="userOpen = ! userOpen"
                                    style="display:flex;align-items:center;gap:8px;padding:6px 10px;border-radius:var(--radius);cursor:pointer;border:none;background:transparent;font-family:inherit;transition:background .15s"
                                    onmouseover="this.style.background='var(--gold-subtle)'"
                                    onmouseout="this.style.background='transparent'">
                                @if (auth()->user()->profile_photo_path)
                                    <img src="{{ auth()->user()->profilePhotoUrl() }}" alt="" style="width:32px;height:32px;border-radius:50%;object-fit:cover">
                                @else
                                    <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--gold-dim),var(--gold));display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--bg-2)">{{ $avatarInits }}</div>
                                @endif
                                <div style="display:none;text-align:left" class="nav-user-desk">
                                    <div style="font-size:13px;font-weight:500;color:var(--text-2)">{{ auth()->user()->name }}</div>
                                    <div style="font-size:11px;color:var(--text-3)">
                                        @php
                                            $roleL = match(auth()->user()->role) {
                                                'super_admin' => 'Super Admin',
                                                'admin' => 'Admin',
                                                'staff' => 'Staff',
                                                default => 'Usuario',
                                            };
                                        @endphp
                                        {{ $roleL }}
                                    </div>
                                </div>
                                <i class="fa-solid fa-chevron-down" style="font-size:9px;color:var(--text-4);transition:transform .2s" :class="{ 'rotate-180': userOpen }"></i>
                            </button>
                            <div x-show="userOpen" @click.away="userOpen = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 translate-y-1"
                                 style="position:absolute;right:0;top:calc(100% + 8px);width:200px;background:var(--bg-3);border:1px solid var(--border);border-radius:var(--radius-lg);box-shadow:0 8px 24px rgba(0,0,0,.5);z-index:50;overflow:hidden">
                                <div style="padding:6px">
                                    <a href="{{ route('profile.edit') }}"
                                       style="display:block;padding:9px 12px;border-radius:var(--radius);font-size:13px;text-decoration:none;color:var(--text-3);transition:all .12s"
                                       onmouseover="this.style.background='var(--gold-subtle)';this.style.color='var(--text-2)'"
                                       onmouseout="this.style.background='transparent';this.style.color='var(--text-3)'">
                                        Editar Perfil
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                                style="width:100%;text-align:left;padding:9px 12px;border-radius:var(--radius);font-size:13px;background:transparent;color:var(--red-text);border:none;cursor:pointer;font-family:inherit;transition:all .12s"
                                                onmouseover="this.style.background='var(--red-bg)'"
                                                onmouseout="this.style.background='transparent'">
                                            Cerrar sesión
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <div style="display:none;align-items:center;gap:8px" class="nav-links-desk">
                            <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Iniciar sesión</a>
                            <a href="{{ route('register') }}" class="btn btn-gold btn-sm">Registrarse</a>
                        </div>
                    @endauth

                    <!-- Mobile hamburger -->
                    <div style="display:flex;align-items:center;margin-left:8px" class="nav-hamburger">
                        <button type="button"
                                style="width:36px;height:36px;border-radius:var(--radius);background:var(--bg-3);border:1px solid var(--border-dim);color:var(--text-3);font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center"
                                @click="open = !open"
                                :aria-expanded="open"
                                aria-label="Menú">
                            <svg class="block h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" :class="{'hidden': open, 'inline-flex': !open}">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                            <svg class="hidden h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" :class="{'inline-flex': open, 'hidden': !open}">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile menu -->
    <div style="display:none;background:var(--bg-2);border-bottom:1px solid var(--border);padding:8px 0" class="nav-mobile" x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div style="padding:4px 12px">
            @auth
                @if (auth()->user()->isStaff())
                <a href="{{ url('/dashboard') }}"
                   style="display:block;padding:10px 12px;border-radius:var(--radius);font-size:14px;font-weight:500;text-decoration:none;transition:all .15s;{{ request()->is('dashboard*') ? 'background:var(--gold-glow);color:var(--gold-light);' : 'color:var(--text-3);' }}"
                   onmouseover="this.style.background='var(--gold-subtle)';this.style.color='var(--text-2)'"
                   onmouseout="this.style.background='{{ request()->is('dashboard*') ? 'var(--gold-glow)' : 'transparent' }}';this.style.color='{{ request()->is('dashboard*') ? 'var(--gold-light)' : 'var(--text-3)' }}'"
                   @click="setTimeout(() => { open = false; }, 50)">
                    Dashboard
                </a>
                @endif
                <a href="{{ route('reservas.index') }}"
                   style="display:block;padding:10px 12px;border-radius:var(--radius);font-size:14px;font-weight:500;text-decoration:none;transition:all .15s;{{ request()->routeIs('reservas.*') ? 'background:var(--gold-glow);color:var(--gold-light);' : 'color:var(--text-3);' }}"
                   onmouseover="this.style.background='var(--gold-subtle)';this.style.color='var(--text-2)'"
                   onmouseout="this.style.background='{{ request()->routeIs('reservas.*') ? 'var(--gold-glow)' : 'transparent' }}';this.style.color='{{ request()->routeIs('reservas.*') ? 'var(--gold-light)' : 'var(--text-3)' }}'"
                   @click="setTimeout(() => { open = false; }, 50)">
                    Reservas
                </a>
                @if (auth()->user()->isStaff())
                <a href="{{ route('pedidos.index') }}"
                   style="display:block;padding:10px 12px;border-radius:var(--radius);font-size:14px;font-weight:500;text-decoration:none;transition:all .15s;{{ request()->routeIs('pedidos.*') ? 'background:var(--gold-glow);color:var(--gold-light);' : 'color:var(--text-3);' }}"
                   onmouseover="this.style.background='var(--gold-subtle)';this.style.color='var(--text-2)'"
                   onmouseout="this.style.background='{{ request()->routeIs('pedidos.*') ? 'var(--gold-glow)' : 'transparent' }}';this.style.color='{{ request()->routeIs('pedidos.*') ? 'var(--gold-light)' : 'var(--text-3)' }}'"
                   @click="setTimeout(() => { open = false; }, 50)">
                    Pedidos
                </a>
                @endif
                @if (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                    <div x-data="{ adminOpen: false }">
                        <button @click="adminOpen = ! adminOpen"
                                style="width:100%;display:flex;align-items:center;justify-content:space-between;padding:10px 12px;border-radius:var(--radius);font-size:14px;font-weight:500;cursor:pointer;border:none;font-family:inherit;transition:all .15s;{{ request()->is('admin*') ? 'background:var(--gold-glow);color:var(--gold-light);' : 'background:transparent;color:var(--text-3);' }}"
                                onmouseover="this.style.background='var(--gold-subtle)';this.style.color='var(--text-2)'"
                                onmouseout="this.style.background='{{ request()->is('admin*') ? 'var(--gold-glow)' : 'transparent' }}';this.style.color='{{ request()->is('admin*') ? 'var(--gold-light)' : 'var(--text-3)' }}'">
                            Admin <i class="fa-solid fa-chevron-down" style="font-size:9px;transition:transform .2s" :class="{ 'rotate-180': adminOpen }"></i>
                        </button>
                        <div x-show="adminOpen" x-transition style="padding-left:12px;margin-top:4px">
                            <a href="{{ route('dashboard') }}"
                               style="display:block;padding:9px 12px;border-radius:var(--radius);font-size:13px;text-decoration:none;{{ request()->routeIs('dashboard') ? 'background:var(--gold-glow);color:var(--gold-light);' : 'color:var(--text-3);' }}"
                               @click="setTimeout(() => { open = false; adminOpen = false; }, 50)"
                               onmouseover="this.style.background='var(--gold-subtle)';this.style.color='var(--text-2)'"
                               onmouseout="this.style.background='{{ request()->routeIs('dashboard') ? 'var(--gold-glow)' : 'transparent' }}';this.style.color='{{ request()->routeIs('dashboard') ? 'var(--gold-light)' : 'var(--text-3)' }}'">
                                Dashboard
                            </a>
                            <a href="{{ route('admin.users.index') }}"
                               style="display:block;padding:9px 12px;border-radius:var(--radius);font-size:13px;text-decoration:none;{{ request()->routeIs('admin.users.*') ? 'background:var(--gold-glow);color:var(--gold-light);' : 'color:var(--text-3);' }}"
                               @click="setTimeout(() => { open = false; adminOpen = false; }, 50)"
                               onmouseover="this.style.background='var(--gold-subtle)';this.style.color='var(--text-2)'"
                               onmouseout="this.style.background='{{ request()->routeIs('admin.users.*') ? 'var(--gold-glow)' : 'transparent' }}';this.style.color='{{ request()->routeIs('admin.users.*') ? 'var(--gold-light)' : 'var(--text-3)' }}'">
                                Usuarios
                            </a>
                            <a href="{{ route('admin.mesas.index') }}"
                               style="display:block;padding:9px 12px;border-radius:var(--radius);font-size:13px;text-decoration:none;{{ request()->routeIs('admin.mesas.*') ? 'background:var(--gold-glow);color:var(--gold-light);' : 'color:var(--text-3);' }}"
                               @click="setTimeout(() => { open = false; adminOpen = false; }, 50)"
                               onmouseover="this.style.background='var(--gold-subtle)';this.style.color='var(--text-2)'"
                               onmouseout="this.style.background='{{ request()->routeIs('admin.mesas.*') ? 'var(--gold-glow)' : 'transparent' }}';this.style.color='{{ request()->routeIs('admin.mesas.*') ? 'var(--gold-light)' : 'var(--text-3)' }}'">
                                Mesas
                            </a>
                            <a href="{{ route('admin.menu.index') }}"
                               style="display:block;padding:9px 12px;border-radius:var(--radius);font-size:13px;text-decoration:none;{{ request()->routeIs('admin.menu.*') ? 'background:var(--gold-glow);color:var(--gold-light);' : 'color:var(--text-3);' }}"
                               @click="setTimeout(() => { open = false; adminOpen = false; }, 50)"
                               onmouseover="this.style.background='var(--gold-subtle)';this.style.color='var(--text-2)'"
                               onmouseout="this.style.background='{{ request()->routeIs('admin.menu.*') ? 'var(--gold-glow)' : 'transparent' }}';this.style.color='{{ request()->routeIs('admin.menu.*') ? 'var(--gold-light)' : 'var(--text-3)' }}'">
                                Menú
                            </a>
                            <a href="{{ route('admin.branding.edit') }}"
                               style="display:block;padding:9px 12px;border-radius:var(--radius);font-size:13px;text-decoration:none;{{ request()->routeIs('admin.branding.*') ? 'background:var(--gold-glow);color:var(--gold-light);' : 'color:var(--text-3);' }}"
                               @click="setTimeout(() => { open = false; adminOpen = false; }, 50)"
                               onmouseover="this.style.background='var(--gold-subtle)';this.style.color='var(--text-2)'"
                               onmouseout="this.style.background='{{ request()->routeIs('admin.branding.*') ? 'var(--gold-glow)' : 'transparent' }}';this.style.color='{{ request()->routeIs('admin.branding.*') ? 'var(--gold-light)' : 'var(--text-3)' }}'">
                                Branding
                            </a>
                        </div>
                    </div>
                @endif
                <div style="border-top:1px solid var(--border-dim);margin:8px 12px;padding-top:8px">
                    <a href="{{ route('profile.edit') }}"
                       style="display:block;padding:10px 12px;border-radius:var(--radius);font-size:14px;font-weight:500;text-decoration:none;color:var(--text-3);transition:all .15s"
                       onmouseover="this.style.background='var(--gold-subtle)';this.style.color='var(--text-2)'"
                       onmouseout="this.style.background='transparent';this.style.color='var(--text-3)'"
                       @click="setTimeout(() => { open = false; }, 50)">
                        Editar Perfil
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                style="width:100%;text-align:left;padding:10px 12px;border-radius:var(--radius);font-size:14px;font-weight:500;background:transparent;color:var(--red-text);border:none;cursor:pointer;font-family:inherit;transition:all .15s"
                                onmouseover="this.style.background='var(--red-bg)'"
                                onmouseout="this.style.background='transparent'"
                                @click="setTimeout(() => { open = false; }, 50)">
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            @else
                <a href="{{ route('login') }}"
                   style="display:block;padding:10px 12px;border-radius:var(--radius);font-size:14px;font-weight:500;text-decoration:none;color:var(--text-3);transition:all .15s"
                   onmouseover="this.style.background='var(--gold-subtle)';this.style.color='var(--text-2)'"
                   onmouseout="this.style.background='transparent';this.style.color='var(--text-3)'"
                   @click="setTimeout(() => { open = false; }, 50)">
                    Iniciar sesión
                </a>
                <a href="{{ route('register') }}"
                   style="display:block;margin:4px 12px;padding:10px 12px;border-radius:var(--radius);font-size:14px;font-weight:600;text-decoration:none;background:var(--gold);color:var(--bg-2);transition:all .15s"
                   onmouseover="this.style.background='var(--gold-light)'"
                   onmouseout="this.style.background='var(--gold)'"
                   @click="setTimeout(() => { open = false; }, 50)">
                    Registrarse
                </a>
            @endauth
        </div>
    </div>
</div>

<style>
    @media(min-width:768px){
        .nav-links-desk{display:flex !important;}
        .nav-hamburger{display:none !important;}
        .nav-user-desk{display:block !important;}
        .nav-mobile{display:none !important;}
    }
    .rotate-180{transform:rotate(180deg) !important;}
</style>
