<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\BrandingController;
use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\PrinterController;
use App\Http\Controllers\Admin\ProductoController;
use App\Http\Controllers\Admin\ReservationSettingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservaController;
use App\Models\Branding;
use App\Models\Categoria;
use App\Models\Reserva;
use App\Models\ReservationSetting;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    $categorias = Categoria::with(['children.productos', 'productos'])->whereNull('parent_id')->get();
    $branding = Branding::getInstance();
    $reservationSettings = ReservationSetting::getInstance();
    $daySlotsJson = [];
    foreach (range(1, 7) as $day) {
        $daySettings = $reservationSettings->getDaySettings($day);
        $groups = $reservationSettings->getTimeSlotGroups($day);
        $allSlots = $reservationSettings->generateTimeSlots($day);
        $daySlotsJson[$day] = [
            'activo' => $daySettings && $daySettings->activo,
            'groups' => $groups,
            'slots' => $allSlots,
        ];
    }
    $defaultDate = now();
    for ($i = 0; $i < 14; $i++) {
        if ($reservationSettings->isDiaActivo((int) $defaultDate->format('N'))) {
            break;
        }
        $defaultDate->addDay();
    }
    $activeReserva = null;
    if (auth()->check() && auth()->user()->isUser()) {
        $activeReserva = Reserva::where('user_id', auth()->id())
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->latest()
            ->first();
    }

    return view('welcome', compact('categorias', 'branding', 'reservationSettings', 'daySlotsJson', 'defaultDate', 'activeReserva'));
})->name('home');

Route::get('/test-simple', function () {
    return 'Test simple';
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,60');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
// Password reset routes
Route::get('/forgot-password', [AuthController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->middleware('throttle:3,60')->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'reset'])->name('password.update');

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard')
        ->middleware('role:admin,super_admin,staff');

    // Backward compatibility: redirect old admin.dashboard named route
    Route::get('/admin/dashboard', function () {
        return redirect()->route('dashboard');
    })->name('admin.dashboard');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/theme', [ProfileController::class, 'updateTheme'])->name('profile.updateTheme');

    // Admin routes (admin only)
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/test-admin-no-middleware', function () {
            return 'Test admin no middleware';
        });
        Route::get('/test-admin', function () {
            return 'Test admin route';
        });
        Route::resource('/users', AdminController::class)->except(['show']);
        Route::get('/mesas/plano', [MesaController::class, 'plano'])->name('mesas.plano');
        Route::put('/mesas/plano', [MesaController::class, 'guardarPlano'])->name('mesas.guardar-plano');
        Route::resource('/mesas', MesaController::class)->except(['show']);
        Route::resource('/categorias', CategoriaController::class);
        Route::post('/categorias/reordenar', [CategoriaController::class, 'reordenar'])->name('categorias.reordenar');
        Route::resource('/productos', ProductoController::class);
        Route::get('/branding/edit', [BrandingController::class, 'edit'])->name('branding.edit');
        Route::put('/branding', [BrandingController::class, 'update'])->name('branding.update');

        // Menu routes
        Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');

        // Printer routes
        Route::resource('/printers', PrinterController::class);

        // Reservation settings routes
        Route::get('/reservation-settings', [ReservationSettingController::class, 'edit'])->name('reservation-settings.edit');
        Route::put('/reservation-settings', [ReservationSettingController::class, 'update'])->name('reservation-settings.update');
    });

    // Reserva routes (authenticated users)
    Route::resource('/reservas', ReservaController::class)->except(['show']);

    // Pedido routes (staff only)
    Route::middleware('role:admin,super_admin,staff')->group(function () {
        Route::resource('/pedidos', PedidoController::class)->except(['show']);
        Route::get('/pedidos/{pedido}', [PedidoController::class, 'show'])->name('pedidos.show');
        Route::put('/pedidos/{pedido}/estado', [PedidoController::class, 'updateEstado'])->name('pedidos.updateEstado');
        Route::post('/pedidos/{pedido}/print-factura', [PedidoController::class, 'printFactura'])->name('pedidos.printFactura');
    });
});
Route::get('/test', function () {
    return 'test';
});
