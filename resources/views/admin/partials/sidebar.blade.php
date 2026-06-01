@php
    $brandingInstance = isset($branding) ? $branding : App\Models\Branding::getInstance();
    $user = auth()->user();
    $initials = strtoupper(substr($user->name, 0, 1) . substr($user->name, strpos($user->name, ' ') + 1, 1));
    if (strlen($initials) < 2) $initials = strtoupper(substr($user->name, 0, 2));
    $reservasHoy = \App\Models\Reserva::whereDate('fecha_hora', today())->count();
    $pedidosActivos = \App\Models\Pedido::whereNotIn('estado', ['pagado', 'cancelado'])->count();
    $isAdmin = $user->isAdmin() || $user->isSuperAdmin();
@endphp
<div class="logo">
    <div class="logo-mark"><i class="fa-solid fa-whiskey-glass"></i></div>
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
        <a href="{{ route('admin.dashboard') }}" class="nav-item{{ request()->routeIs('admin.dashboard') ? ' active' : '' }}">
            <i class="fa-solid fa-gauge-high"></i> Dashboard
        </a>
        @endif
        <a href="{{ route('reservas.index') }}" class="nav-item{{ request()->routeIs('reservas.*') ? ' active' : '' }}">
            <i class="fa-solid fa-calendar-check"></i> Reservas
            @if ($reservasHoy > 0)<span class="nav-chip">{{ $reservasHoy }}</span>@endif
        </a>
        <a href="{{ route('pedidos.index') }}" class="nav-item{{ request()->routeIs('pedidos.*') ? ' active' : '' }}">
            <i class="fa-solid fa-receipt"></i> Pedidos
            @if ($pedidosActivos > 0)<span class="nav-chip">{{ $pedidosActivos }}</span>@endif
        </a>
        @if ($isAdmin)
        <a href="{{ route('admin.mesas.index') }}" class="nav-item{{ request()->routeIs('admin.mesas.*') ? ' active' : '' }}">
            <i class="fa-solid fa-table-cells"></i> Mesas
        </a>
        @endif
    </div>

    @if ($isAdmin)
    <div class="nav-section">
        <div class="nav-label">Barra</div>
        <a href="{{ route('admin.menu.index') }}" class="nav-item{{ request()->routeIs('admin.menu.*') ? ' active' : '' }}">
            <i class="fa-solid fa-martini-glass"></i> Menú
        </a>
        <a href="{{ route('admin.productos.index') }}" class="nav-item{{ request()->routeIs('admin.productos.*') ? ' active' : '' }}">
            <i class="fa-solid fa-bottle-droplet"></i> Productos
        </a>
        <a href="{{ route('admin.categorias.index') }}" class="nav-item{{ request()->routeIs('admin.categorias.*') ? ' active' : '' }}">
            <i class="fa-solid fa-tags"></i> Categorías
        </a>
    </div>
    @endif

    @if ($isAdmin)
    <div class="nav-section">
        <div class="nav-label">Personal</div>
        <a href="{{ route('admin.users.index') }}" class="nav-item{{ request()->routeIs('admin.users.*') ? ' active' : '' }}">
            <i class="fa-solid fa-users"></i> Usuarios
        </a>
    </div>
    @endif

    @if ($isAdmin)
    <div class="nav-section">
        <div class="nav-label">Config</div>
        <a href="{{ route('admin.branding.edit') }}" class="nav-item{{ request()->routeIs('admin.branding.*') ? ' active' : '' }}">
            <i class="fa-solid fa-sliders"></i> Branding
        </a>
        <a href="{{ route('admin.reservation-settings.edit') }}" class="nav-item{{ request()->routeIs('admin.reservation-settings.*') ? ' active' : '' }}">
            <i class="fa-regular fa-clock"></i> Horarios
        </a>
        <a href="{{ route('admin.printers.index') }}" class="nav-item{{ request()->routeIs('admin.printers.*') ? ' active' : '' }}">
            <i class="fa-solid fa-print"></i> Impresoras
        </a>
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
        <div class="av">{{ $initials }}</div>
        <div>
            <div class="user-name">{{ $user->name }}</div>
            <div class="user-role">
                @php
                    $roleLabel = match($user->role) {
                        'super_admin' => 'Super Admin',
                        'admin' => 'Admin',
                        'staff' => 'Staff',
                        default => 'Usuario',
                    };
                @endphp
                {{ $roleLabel }}
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
            @csrf
            <button type="submit" style="background:none;border:none;cursor:pointer;color:var(--text-4);padding:0"><i class="fa-solid fa-right-from-bracket"></i></button>
        </form>
    </div>
</div>
