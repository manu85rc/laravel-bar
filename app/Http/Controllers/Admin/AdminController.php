<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branding;
use App\Models\Categoria;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Reserva;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $branding = Branding::getInstance();

        if (in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            // Admin Stats
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

            $staffCount = User::whereIn('role', ['admin', 'super_admin', 'staff'])->count();
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

            $topProductos = DB::table('pedido_producto')
                ->select('producto_id', DB::raw('SUM(cantidad) as total_qty'), DB::raw('SUM(cantidad * precio_unitario) as total_rev'))
                ->groupBy('producto_id')
                ->orderByDesc('total_qty')
                ->take(5)
                ->get()
                ->map(function ($item) {
                    $producto = Producto::find($item->producto_id);

                    return [
                        'nombre' => $producto ? $producto->nombre : 'Eliminado',
                        'categoria' => $producto && $producto->categoria ? $producto->categoria->name : '—',
                        'cantidad' => $item->total_qty,
                        'total' => (float) $item->total_rev,
                    ];
                });

            $productosNoDisponibles = Producto::where('disponible', false)->take(5)->get();
            $staff = User::whereIn('role', ['admin', 'super_admin', 'staff'])->take(6)->get();

            return view('admin.dashboard', compact(
                'branding', 'ingresosHoy', 'cambioIngresos', 'cubiertosHoy', 'cambioCubiertos',
                'promedioTab', 'staffCount', 'pedidosActivos', 'totalActivos',
                'mesas', 'mesasConPedido', 'mesasConReserva', 'topProductos',
                'productosNoDisponibles', 'staff', 'totalMesas', 'totalProductos',
                'totalCategorias', 'totalReservas', 'totalUsers',
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

        return view('dashboard', compact(
            'branding', 'reservasActivas', 'totalReservas', 'pedidosActivos',
            'misReservas', 'misPedidos',
        ));
    }

    public function index()
    {
        $users = auth()->user()->isSuperAdmin()
            ? User::all()
            : User::where('role', '!=', User::ROLE_SUPER_ADMIN)->get();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $currentUser = $request->user();

        $allowedRoles = $currentUser->isSuperAdmin()
            ? 'super_admin,admin,staff,user'
            : 'staff';

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:'.$allowedRoles,
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $user->role = $request->role;
        $user->save();

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

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $currentUser = $request->user();

        $allowedRoles = $currentUser->isSuperAdmin()
            ? 'super_admin,admin,staff,user'
            : 'staff';

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:'.$allowedRoles,
        ]);

        if ($currentUser->id === $user->id && $request->role !== $user->role) {
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

        $user->role = $request->role;
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }
}
