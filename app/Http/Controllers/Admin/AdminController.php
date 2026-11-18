<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branding;
use App\Models\Categoria;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Reserva;
use App\Models\Role;
use App\Models\User;
use App\Services\NotificacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $branding = Branding::getInstance();

        $range = (int) request()->query('range', 7);
        if (! in_array($range, [7, 30], true)) {
            $range = 7;
        }
        $start = today()->subDays($range - 1)->startOfDay();

        if (auth()->user()->hasPermission('admin.dashboard') || auth()->user()->isSuperAdmin()) {
            $ingresosHoy = (float) Pedido::whereDate('created_at', today())
                ->where('estado', 'pagado')
                ->sum('total');

            $ingresosAyer = (float) Pedido::whereDate('created_at', today()->subDay())
                ->where('estado', 'pagado')
                ->sum('total');

            $cubiertosHoy = Reserva::whereDate('fecha_hora', today())->count();
            $cubiertosAyer = Reserva::whereDate('fecha_hora', today()->subDay())->count();

            $totalPedidos = Pedido::count();
            $totalIngresos = (float) Pedido::where('estado', 'pagado')->sum('total');
            $promedioTab = $totalPedidos > 0 ? round($totalIngresos / $totalPedidos) : 0;

            $staffCount = User::whereHas('role', function ($q) {
                $q->whereIn('slug', ['admin', 'super_admin', 'staff']);
            })->count();
            $totalMesas = Mesa::count();
            $totalProductos = Producto::count();
            $totalCategorias = Categoria::count();
            $totalReservas = Reserva::count();
            $totalUsers = User::count();

            $cambioIngresos = $ingresosAyer > 0 ? round((($ingresosHoy - $ingresosAyer) / $ingresosAyer) * 100, 1) : 0;
            $cambioCubiertos = $cubiertosAyer > 0 ? round((($cubiertosHoy - $cubiertosAyer) / $cubiertosAyer) * 100, 1) : 0;

            $pedidosActivos = Pedido::with(['mesa', 'staff'])
                ->whereNotIn('estado', ['pagado', 'cancelado'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $totalActivos = Pedido::whereNotIn('estado', ['pagado', 'cancelado'])->count();

            $mesas = Mesa::orderBy('numero')->get();
            $mesasConPedido = Pedido::whereNotIn('estado', ['pagado', 'cancelado'])
                ->pluck('mesa_id')
                ->unique()
                ->toArray();
            $mesasConReserva = Reserva::whereDate('fecha_hora', today())
                ->where('estado', 'confirmada')
                ->pluck('mesa_id')
                ->unique()
                ->toArray();

            $topProductos = DB::table('pedido_producto as pp')
                ->leftJoin('productos as p', 'p.id', '=', 'pp.producto_id')
                ->leftJoin('categories as c', 'c.id', '=', 'p.categoria_id')
                ->selectRaw('pp.producto_id, p.nombre as nombre, c.name as categoria, SUM(pp.cantidad) as total_qty, SUM(pp.cantidad * pp.precio_unitario) as total_rev')
                ->groupBy('pp.producto_id', 'p.nombre', 'c.name')
                ->orderByDesc('total_qty')
                ->take(5)
                ->get()
                ->map(fn ($item) => [
                    'nombre' => $item->nombre ?? 'Eliminado',
                    'categoria' => $item->categoria ?? '—',
                    'cantidad' => (int) $item->total_qty,
                    'total' => (float) $item->total_rev,
                ]);

            $productosNoDisponibles = Producto::where('disponible', false)->take(5)->get();
            $staff = User::whereHas('role', function ($q) {
                $q->whereIn('slug', ['admin', 'super_admin', 'staff']);
            })->take(6)->get();

            $ingresosPorDia = Pedido::where('estado', 'pagado')
                ->where('created_at', '>=', $start)
                ->selectRaw('DATE(created_at) as dia, SUM(total) as agregado')
                ->groupBy('dia')
                ->pluck('agregado', 'dia');

            $pedidosPorDia = Pedido::where('created_at', '>=', $start)
                ->selectRaw('DATE(created_at) as dia, COUNT(*) as agregado')
                ->groupBy('dia')
                ->pluck('agregado', 'dia');

            $reservasPorDia = Reserva::where('fecha_hora', '>=', $start)
                ->selectRaw('DATE(fecha_hora) as dia, COUNT(*) as agregado')
                ->groupBy('dia')
                ->pluck('agregado', 'dia');

            [$ingresosSerie, $pedidosSerie, $reservasSerie, $chartLabels] = $this->fillDailySeries(
                $range,
                ['ingresos' => $ingresosPorDia, 'pedidos' => $pedidosPorDia, 'reservas' => $reservasPorDia],
            );

            return view('admin.dashboard', compact(
                'branding', 'range', 'ingresosHoy', 'cambioIngresos', 'cubiertosHoy', 'cambioCubiertos',
                'promedioTab', 'staffCount', 'pedidosActivos', 'totalActivos',
                'mesas', 'mesasConPedido', 'mesasConReserva', 'topProductos',
                'productosNoDisponibles', 'staff', 'totalMesas', 'totalProductos',
                'totalCategorias', 'totalReservas', 'totalUsers',
                'ingresosSerie', 'pedidosSerie', 'reservasSerie', 'chartLabels',
            ));
        }

        $reservasActivas = Reserva::where('user_id', auth()->id())
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->count();

        $totalReservas = Reserva::where('user_id', auth()->id())->count();

        $pedidosActivos = Pedido::where('user_id', auth()->id())
            ->whereNotIn('estado', ['pagado', 'cancelado'])
            ->count();

        $misReservas = Reserva::where('user_id', auth()->id())
            ->with('mesa')
            ->latest()
            ->take(5)
            ->get();

        $misPedidos = Pedido::where('user_id', auth()->id())
            ->whereNotIn('estado', ['pagado', 'cancelado'])
            ->with('mesa')
            ->latest()
            ->take(5)
            ->get();

        $misReservasPorDia = Reserva::where('user_id', auth()->id())
            ->where('fecha_hora', '>=', $start)
            ->selectRaw('DATE(fecha_hora) as dia, COUNT(*) as agregado')
            ->groupBy('dia')
            ->pluck('agregado', 'dia');

        $misPedidosPorDia = Pedido::where('user_id', auth()->id())
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as dia, COUNT(*) as agregado')
            ->groupBy('dia')
            ->pluck('agregado', 'dia');

        [$reservasSerie, $pedidosSerie, $chartLabels] = $this->fillDailySeries(
            $range,
            ['reservas' => $misReservasPorDia, 'pedidos' => $misPedidosPorDia],
        );

        return view('dashboard', compact(
            'branding', 'range', 'reservasActivas', 'totalReservas', 'pedidosActivos',
            'misReservas', 'misPedidos', 'reservasSerie', 'pedidosSerie', 'chartLabels',
        ));
    }

    /**
     * Build aligned daily series (filling missing days with zero) plus
     * a thinned label set suitable for compact charts.
     *
     * @param  array<string, Collection>  $seriesByKey
     * @return array{0: array<int, float>, 1: array<int, float>, 2: array<int, float>, 3: array<int, string>}
     */
    private function fillDailySeries(int $range, array $seriesByKey): array
    {
        $step = max(1, (int) ceil($range / 6));
        $columns = [];

        foreach ($seriesByKey as $key => $source) {
            $columns[$key] = [];
        }

        $labels = [];

        for ($i = 0; $i < $range; $i++) {
            $day = today()->subDays($range - 1 - $i);
            $date = $day->toDateString();

            foreach ($seriesByKey as $key => $source) {
                $columns[$key][] = (float) ($source[$date] ?? 0);
            }

            $labels[] = ($i % $step === 0 || $i === $range - 1) ? $day->format('j/n') : '';
        }

        $result = array_values($columns);
        $result[] = $labels;

        return $result;
    }

    public function index()
    {
        $users = auth()->user()->isSuperAdmin()
            ? User::with('role')->get()
            : User::with('role')->whereHas('role', function ($q) {
                $q->where('slug', '!=', 'super_admin');
            })->get();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $currentUser = $request->user();

        $allowedRoleIds = $currentUser->isSuperAdmin()
            ? Role::pluck('id')->implode(',')
            : Role::where('slug', 'staff')->pluck('id')->implode(',');

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|in:'.$allowedRoleIds,
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role_id' => $request->role_id,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    public function destroy(User $user)
    {
        $currentUser = request()->user();

        if ($currentUser->id === $user->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'No puedes eliminarte a ti mismo.');
        }

        if (! $currentUser->isSuperAdmin() && ($user->isSuperAdmin() || $user->isAdmin())) {
            return redirect()->route('admin.users.index')
                ->with('error', 'No tienes permiso para eliminar este usuario.');
        }

        // Se limpian sus notificaciones antes de borrar el usuario
        // para no dejar filas huérfanas en la tabla notifications.
        app(NotificacionService::class)->limpiarDelUsuario((int) $user->id);

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $currentUser = $request->user();

        $allowedRoleIds = $currentUser->isSuperAdmin()
            ? Role::pluck('id')->implode(',')
            : Role::where('slug', 'staff')->pluck('id')->implode(',');

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|in:'.$allowedRoleIds,
        ]);

        if ($currentUser->id === $user->id && $request->role_id != $user->role_id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'No puedes cambiarte tu propio rol.');
        }

        if (! $currentUser->isSuperAdmin() && ($user->isSuperAdmin() || $user->isAdmin())) {
            return redirect()->route('admin.users.index')
                ->with('error', 'No tienes permiso para editar este usuario.');
        }

        $user->fill([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password ? bcrypt($request->password) : $user->password,
        ]);

        $user->role_id = $request->role_id;
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function loginAs(Request $request, User $user)
    {
        $currentUser = $request->user();

        if (! $currentUser->isSuperAdmin()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'No tienes permiso para realizar esta accion.');
        }

        if ($currentUser->id === $user->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'No puedes entrar a tu propia cuenta.');
        }

        $originalUserId = $currentUser->id;

        Auth::login($user);

        $request->session()->put('admin_original_user_id', $originalUserId);

        if ($user->isUser()) {
            return redirect()->route('home');
        }

        if ($user->isStaffRole()) {
            return redirect()->route('pedidos.index');
        }

        return redirect()->route('dashboard');
    }

    public function switchBack(Request $request)
    {
        $originalUserId = $request->session()->pull('admin_original_user_id');

        if (! $originalUserId) {
            return redirect()->route('dashboard');
        }

        $originalUser = User::find($originalUserId);

        if (! $originalUser) {
            return redirect()->route('login');
        }

        Auth::login($originalUser);
        $request->session()->regenerate();

        return redirect()->route('admin.users.index')
            ->with('success', 'Volviendo a tu cuenta de administrador.');
    }
}
