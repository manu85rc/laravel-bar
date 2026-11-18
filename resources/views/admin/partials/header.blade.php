@php
    $pageTitle = match(true) {
        request()->routeIs('admin.dashboard'), request()->routeIs('dashboard') => 'Dashboard',
        request()->routeIs('admin.menu.*') => 'Menú',
        request()->routeIs('admin.productos.*') => 'Productos',
        request()->routeIs('admin.categorias.*') => 'Categorías',
        request()->routeIs('admin.mesas.*') => 'Mesas',
        request()->routeIs('pedidos.*') => 'Pedidos',
        request()->routeIs('reservas.*') => 'Reservas',
        request()->routeIs('admin.users.*') => 'Usuarios',
        request()->routeIs('admin.branding.*') => 'Configuración',
        default => 'Admin',
    };
    $user = auth()->user();
    $initials = strtoupper(substr($user->name, 0, 1) . substr($user->name, strpos($user->name, ' ') + 1, 1));
    if (strlen($initials) < 2) $initials = strtoupper(substr($user->name, 0, 2));
    $dias = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    $hoy = $dias[(int)date('w')] . ', ' . (int)date('d') . ' de ' . $meses[(int)date('n')-1] . ' — ' . date('Y');
@endphp
<button id="hamburger" onclick="openSidebar()"><i class="fa-solid fa-bars"></i></button>
<div class="breadcrumb">
    <span>{{ $brandingSettings->app_name ?? 'Admin' }}</span>
    <i class="fa-solid fa-chevron-right"></i>
    <span>{{ $pageTitle }}</span>
</div>
<div class="header-right">
    <div class="date-chip" id="dateChip" data-tz="{{ config('app.timezone') }}">
        <i class="fa-regular fa-calendar"></i>
        <span id="dateChipText">{{ $hoy }}</span>
    </div>
    @if ($user->isStaff())
        {{-- Campana de notificaciones: el JS monta el panel y sondea /notificaciones --}}
        <div class="notif-wrap" id="notifWrap">
            <button type="button" class="hbtn" id="notifBell" aria-label="Notificaciones" title="Notificaciones" aria-haspopup="true" aria-expanded="false">
                <i class="fa-solid fa-bell"></i>
                <span id="notifBadge" hidden>0</span>
            </button>
        </div>
    @endif
    <button class="hbtn theme-hbtn" onclick="toggleTheme()" title="Cambiar tema">
        <i class="fa-solid fa-moon" id="theme-icon"></i>
    </button>
    <a href="{{ route('profile.edit') }}" class="hbtn" style="border-radius:50%;background:linear-gradient(135deg,var(--gold-dim),var(--gold));border:none;color:var(--bg-2);font-size:11px;font-weight:700;overflow:hidden;text-decoration:none;" title="Mi perfil" aria-label="Ir a perfil">
        @if ($user->profilePhotoUrl())
            <img src="{{ $user->profilePhotoUrl() }}" alt="Foto" style="width:100%;height:100%;object-fit:cover;">
        @else
            {{ $initials }}
        @endif
    </a>
</div>
<script>
    (function() {
        var icon = document.getElementById('theme-icon');
        if (icon) {
            var isDark = document.documentElement.classList.contains('dark');
            icon.className = isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
        }
    })();

    (function() {
        var chip = document.getElementById('dateChip');
        var el = document.getElementById('dateChipText');
        if (!chip || !el) return;
        var tz = chip.getAttribute('data-tz') || 'UTC';
        var dias = {Sunday:'Domingo',Monday:'Lunes',Tuesday:'Martes',Wednesday:'Miércoles',Thursday:'Jueves',Friday:'Viernes',Saturday:'Sábado'};
        var meses = {January:'enero',February:'febrero',March:'marzo',April:'abril',May:'mayo',June:'junio',July:'julio',August:'agosto',September:'septiembre',October:'octubre',November:'noviembre',December:'diciembre'};
        function tick() {
            try {
                var parts = new Intl.DateTimeFormat('en-US', {
                    timeZone: tz,
                    weekday: 'long',
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hourCycle: 'h23'
                }).formatToParts(new Date());
                var v = {};
                parts.forEach(function(p) { if (p.type !== 'literal') v[p.type] = p.value; });
                el.textContent = (dias[v.weekday] || v.weekday) + ', ' + parseInt(v.day, 10) + ' de ' + (meses[v.month] || v.month) + ' \u2014 ' + v.year + ' \u00b7 ' + v.hour + ':' + v.minute;
            } catch (e) {}
        }
        tick();
        setInterval(tick, 1000);
    })();
</script>
