@php
    $brandingInstance = isset($branding) ? $branding : App\Models\Branding::getInstance();
    $user = auth()->user();
    $initials = strtoupper(substr($user->name, 0, 1) . substr($user->name, strpos($user->name, ' ') + 1, 1));
    if (strlen($initials) < 2) $initials = strtoupper(substr($user->name, 0, 2));
    $reservasHoy = \App\Models\Reserva::whereDate('fecha_hora', today())->count();
    $pedidosActivos = \App\Models\Pedido::whereNotIn('estado', ['pagado', 'cancelado'])->count();
    $isAdmin = $user->isSuperAdmin() || $user->hasPermission('admin.dashboard');
@endphp
<div class="logo">
    <a href="{{ url('/') }}" style="display:flex;align-items:center;text-decoration:none;">
        @if ($brandingInstance->logo_path)
            <img src="{{ $brandingInstance->logoUrl() }}" alt="Logo" style="width:34px;height:34px;border-radius:var(--radius);object-fit:contain;flex-shrink:0">
        @else
            <div class="logo-mark"><i class="fa-solid fa-whiskey-glass"></i></div>
        @endif
    </a>
    <div class="logo-text">
        <div class="logo-name">{{ $brandingInstance->app_name ?? 'Admin' }}</div>
        <div class="logo-sub">Panel de control</div>
    </div>
    <button id="close-sidebar" onclick="closeSidebar()"><i class="fa-solid fa-xmark"></i></button>
</div>

<nav>
    <div class="nav-section">
        <div class="nav-label">General</div>
        @if ($isAdmin)
        <a href="{{ route('admin.dashboard') }}" class="nav-item{{ request()->routeIs('dashboard') || request()->routeIs('admin.dashboard') ? ' active' : '' }}">
            <i class="fa-solid fa-gauge-high"></i> Dashboard
        </a>
        @endif
        <a href="{{ route('reservas.index') }}" class="nav-item{{ request()->routeIs('reservas.*') ? ' active' : '' }}">
            <i class="fa-solid fa-calendar-check"></i> Reservas
            @if ($reservasHoy > 0)<span class="nav-chip">{{ $reservasHoy }}</span>@endif
        </a>
        @if ($user->isSuperAdmin() || $user->hasPermission('admin.pedidos'))
        <a href="{{ route('pedidos.index') }}" class="nav-item{{ request()->routeIs('pedidos.*') ? ' active' : '' }}">
            <i class="fa-solid fa-receipt"></i> Pedidos
            @if ($pedidosActivos > 0)<span class="nav-chip">{{ $pedidosActivos }}</span>@endif
        </a>
        @endif
        @if ($user->isSuperAdmin() || $user->hasPermission('admin.mesas'))
        <a href="{{ route('admin.mesas.index') }}" class="nav-item{{ request()->routeIs('admin.mesas.*') ? ' active' : '' }}">
            <i class="fa-solid fa-table-cells"></i> Mesas
        </a>
        @endif
    </div>

    @if ($user->isSuperAdmin() || $user->hasPermission('admin.menu'))
    <div class="nav-section">
        <div class="nav-label">Barra</div>
        <a href="{{ route('admin.menu.index') }}" class="nav-item{{ request()->routeIs('admin.menu.*') || request()->routeIs('admin.categorias.*') || request()->routeIs('admin.productos.*') ? ' active' : '' }}">
            <i class="fa-solid fa-martini-glass"></i> Menú
        </a>
    </div>
    @endif

    @if ($user->isSuperAdmin() || $user->hasPermission('admin.users'))
    <div class="nav-section">
        <div class="nav-label">Personal</div>
        <a href="{{ route('admin.users.index') }}" class="nav-item{{ request()->routeIs('admin.users.*') ? ' active' : '' }}">
            <i class="fa-solid fa-users"></i> Usuarios
        </a>
    </div>
    @endif

    @if ($user->isSuperAdmin())
    <div class="nav-section">
        <div class="nav-label">Admin</div>
        <a href="{{ route('admin.roles.index') }}" class="nav-item{{ request()->routeIs('admin.roles.*') ? ' active' : '' }}">
            <i class="fa-solid fa-shield-halved"></i> Roles
        </a>
    </div>
    @endif

    @if ($user->isSuperAdmin() || $user->hasPermission('admin.branding') || $user->hasPermission('admin.reservation-settings') || $user->hasPermission('admin.printers'))
    <div class="nav-section">
        <div class="nav-label">Config</div>
        @if ($user->isSuperAdmin() || $user->hasPermission('admin.branding'))
        <a href="{{ route('admin.branding.edit') }}" class="nav-item{{ request()->routeIs('admin.branding.*') ? ' active' : '' }}">
            <i class="fa-solid fa-sliders"></i> Configuración
        </a>
        @endif
        @if ($user->isSuperAdmin())
        <a href="{{ route('admin.mercado-pago-settings.edit') }}" class="nav-item{{ request()->routeIs('admin.mercado-pago-settings.*') ? ' active' : '' }}">
            <x-mp-icon /> Mercado Pago
        </a>
        @endif
        @if ($user->isSuperAdmin() || $user->hasPermission('admin.reservation-settings'))
        <a href="{{ route('admin.reservation-settings.edit') }}" class="nav-item{{ request()->routeIs('admin.reservation-settings.*') ? ' active' : '' }}">
            <i class="fa-regular fa-clock"></i> Horarios
        </a>
        @endif
        @if ($user->isSuperAdmin() || $user->hasPermission('admin.printers'))
        <a href="{{ route('admin.printers.index') }}" class="nav-item{{ request()->routeIs('admin.printers.*') ? ' active' : '' }}">
            <i class="fa-solid fa-print"></i> Impresoras
        </a>
        @endif
    </div>
    @endif
</nav>

<div class="sidebar-footer">
    <div class="venue-status">
        <div class="status-dot"></div>
        <div class="status-label">Abierto</div>
        <div class="status-time">24 hs</div>
    </div>
    <div class="user-row">
        <a href="{{ route('profile.edit') }}" style="display:flex;align-items:center;gap:10px;text-decoration:none;color:inherit;" title="Mi perfil" aria-label="Ir a perfil">
            <div class="av">
                @if ($user->profilePhotoUrl())
                    <img src="{{ $user->profilePhotoUrl() }}" alt="Foto">
                @else
                    {{ $initials }}
                @endif
            </div>
            <div>
                <div class="user-name">{{ $user->name }}</div>
                <div class="user-role">
                    {{ $user->role->name ?? 'Sin rol' }}
                </div>
            </div>
        </a>
        <form method="POST" action="{{ route('logout') }}" style="display:inline;margin-left:auto">
            @csrf
            <button type="submit" style="background:none;border:none;cursor:pointer;color:var(--text-4);padding:0"><i class="fa-solid fa-right-from-bracket"></i></button>
        </form>
    </div>
</div>
