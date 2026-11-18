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
                                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('admin.dashboard'))
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
                                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('admin.pedidos'))
                                <a href="{{ route('pedidos.index') }}"
                                   style="padding:8px 14px;border-radius:var(--radius);font-size:13px;font-weight:500;text-decoration:none;transition:all .15s;{{ request()->routeIs('pedidos.*') ? 'background:var(--gold-glow);color:var(--gold-light);' : 'color:var(--text-3);' }}"
                                   onmouseover="this.style.background='var(--gold-subtle)';this.style.color='var(--text-2)'"
                                   onmouseout="this.style.background='{{ request()->routeIs('pedidos.*') ? 'var(--gold-glow)' : 'transparent' }}';this.style.color='{{ request()->routeIs('pedidos.*') ? 'var(--gold-light)' : 'var(--text-3)' }}'">
                                    Pedidos
                                </a>
                                @endif
                                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('admin.users') || auth()->user()->hasPermission('admin.mesas') || auth()->user()->hasPermission('admin.menu') || auth()->user()->hasPermission('admin.branding'))
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
                                                     Configuración
                                                 </a>
                                             </div>







                                        <div class="dropdown-item-category">Admin</div>
                                        <a href="{{ route('admin.branding.edit') }}" class="dropdown-item"
                                            onmouseover="this.style.background='var(--gold-subtle)';this.style.color='var(--text-2)'"
                                            onmouseout="this.style.background='{{ request()->routeIs('admin.branding.*') ? 'var(--gold-glow)' : 'transparent' }}';this.style.color='{{ request()->routeIs('admin.branding.*') ? 'var(--gold-light)' : 'var(--text-3)' }}'">
                                             Configuración
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
