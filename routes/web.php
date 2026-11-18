<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\BrandingController;
use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MercadoPagoSettingController;
use App\Http\Controllers\Admin\PrinterController;
use App\Http\Controllers\Admin\ProductoController;
use App\Http\Controllers\Admin\ReservationSettingController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\OfflineSyncController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PrintQueueController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\ZonaController;
use App\Models\Branding;
use App\Models\Categoria;
use App\Models\Reserva;
use App\Models\ReservationSetting;
use Illuminate\Support\Facades\Route;

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
            ->where('fecha_hora', '>=', now())
            ->latest()
            ->first();
    }

    return response()
        ->view('welcome', compact('categorias', 'branding', 'reservationSettings', 'daySlotsJson', 'defaultDate', 'activeReserva'))
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
})->name('home');

Route::get('/test-simple', function () {
    return 'Test simple';
});

Route::get('/offline', function () {
    return view('offline');
})->name('offline');

// Sonda ligera de conectividad real: no es cacheada por el service worker.
Route::get('/ping', function () {
    return response('', 204);
})->name('ping');

// Token CSRF fresco tras una reconexión larga (la sesión puede haberse renovado).
Route::get('/api/offline/csrf', function () {
    return response()->json(['csrf_token' => csrf_token()]);
})->name('offline.csrf');

Route::get('/manifest.webmanifest', function () {
    $branding = Branding::getInstance();
    $primary = $branding->primary_color ?? '#c8922a';
    $name = $branding->app_name ?? 'The Copper Still';

    return response()->json([
        'name' => $name,
        'short_name' => $name,
        'description' => $branding->tagline ?? 'Gestión de bar y restaurante',
        'start_url' => '/',
        'scope' => '/',
        'display' => 'standalone',
        'background_color' => '#0e0d0b',
        'theme_color' => $primary,
        'lang' => 'es',
        'icons' => [
            ['src' => '/pwa-192x192.png', 'sizes' => '192x192', 'type' => 'image/png'],
            ['src' => '/pwa-512x512.png', 'sizes' => '512x512', 'type' => 'image/png'],
            ['src' => '/pwa-512x512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
        ],
    ], 200, [
        'Content-Type' => 'application/manifest+json',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
    ]);
})->name('manifest');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:50,1');
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,60');
    Route::get('/forgot-password', [AuthController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->middleware('throttle:3,60')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'reset'])->name('password.update');
});

Route::get('/auth/{provider}/redirect', [SocialiteController::class, 'redirect'])->name('social.redirect');
Route::match(['get', 'post'], '/auth/{provider}/callback', [SocialiteController::class, 'callback'])->name('social.callback');

Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard')
        ->middleware('permission:admin.dashboard');

    Route::get('/admin/dashboard', function () {
        return redirect()->route('dashboard');
    })->name('admin.dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/theme', [ProfileController::class, 'updateTheme'])->name('profile.updateTheme');
    Route::delete('/profile/social/{socialAccount}', [SocialiteController::class, 'unlink'])->name('social.unlink');

    // Campana de notificaciones. Todos los endpoints leen/escriben
    // unicamente las notificaciones del usuario autenticado.
    Route::prefix('notificaciones')->name('notificaciones.')->group(function () {
        Route::get('/', [NotificacionController::class, 'index'])->name('index');
        Route::post('/leer-todas', [NotificacionController::class, 'leerTodas'])->name('leer-todas');
        Route::delete('/', [NotificacionController::class, 'limpiar'])->name('limpiar');
        Route::post('/{notificacion}/leer', [NotificacionController::class, 'leer'])->name('leer');
        Route::delete('/{notificacion}', [NotificacionController::class, 'destroy'])->name('destroy');
    });

    // Suscripciones Web Push (notificaciones con la app cerrada).
    // El controlador valida que solo el personal pueda suscribirse.
    Route::prefix('push')->name('push.')->group(function () {
        Route::post('/subscribe', [PushSubscriptionController::class, 'subscribe'])->name('subscribe');
        Route::delete('/unsubscribe', [PushSubscriptionController::class, 'unsubscribe'])->name('unsubscribe');
        Route::post('/test', [PushSubscriptionController::class, 'test'])->name('test');
    });

    // Admin routes
    Route::middleware(['permission:admin.users'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('/users', AdminController::class)->except(['show']);
        Route::post('/users/{user}/login-as', [AdminController::class, 'loginAs'])->name('users.login-as');
    });

    Route::post('/admin/switch-back', [AdminController::class, 'switchBack'])->name('admin.switch-back');

    Route::post('/pwa/install-dismissed', function () {
        session()->put('pwa_install_dismissed', true);

        return response()->json(['ok' => true]);
    })->name('pwa.install-dismissed');

    Route::middleware(['permission:admin.mesas'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/mesas/plano', fn () => redirect()->route('admin.mesas.index', ['tab' => 'plano']))->name('mesas.plano');
        Route::put('/mesas/plano', [MesaController::class, 'guardarPlano'])->name('mesas.guardar-plano');
        Route::resource('/mesas', MesaController::class)->except(['show']);
        Route::post('/zonas/reordenar', ZonaController::class.'@reordenar')->name('zonas.reordenar');
        Route::resource('/zonas', ZonaController::class)->except(['show', 'create', 'edit']);
    });

    Route::middleware(['permission:admin.menu'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('/categorias', CategoriaController::class);
        Route::post('/categorias/reordenar', [CategoriaController::class, 'reordenar'])->name('categorias.reordenar');
        Route::resource('/productos', ProductoController::class);
        Route::post('/productos/{producto}/categoria', [ProductoController::class, 'cambiarCategoria'])->name('productos.cambiar-categoria');
        Route::post('/productos/reordenar', [ProductoController::class, 'reordenar'])->name('productos.reordenar');
        Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
    });

    Route::middleware(['permission:admin.branding'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/branding/edit', [BrandingController::class, 'edit'])->name('branding.edit');
        Route::put('/branding', [BrandingController::class, 'update'])->name('branding.update');
    });

    Route::middleware(['permission:admin.printers'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/printers/terminal', [PrinterController::class, 'terminal'])->name('printers.terminal');
        Route::post('/printers/test-print', [PrinterController::class, 'testPrint'])->name('printers.test-print');
        Route::get('/printers/usb-detect', [PrinterController::class, 'usbDetect'])->name('printers.usb-detect');
        Route::get('/printers/bluetooth/scan', [PrinterController::class, 'bluetoothScan'])->name('printers.bluetooth.scan');
        Route::post('/printers/bluetooth/pair', [PrinterController::class, 'bluetoothPair'])->name('printers.bluetooth.pair');
        Route::resource('/printers', PrinterController::class)->except(['show']);
    });

    Route::middleware(['permission:admin.reservation-settings'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/reservation-settings', [ReservationSettingController::class, 'edit'])->name('reservation-settings.edit');
        Route::put('/reservation-settings', [ReservationSettingController::class, 'update'])->name('reservation-settings.update');
    });

    // Super Admin only: roles management + Mercado Pago settings
    Route::middleware(['role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('/roles', RoleController::class)->except(['show']);
        Route::get('/mercado-pago', [MercadoPagoSettingController::class, 'edit'])->name('mercado-pago-settings.edit');
        Route::put('/mercado-pago', [MercadoPagoSettingController::class, 'update'])->name('mercado-pago-settings.update');
    });

    // Print queue for browser-side Bluetooth printing
    Route::get('/print-queue', [PrintQueueController::class, 'poll'])->name('print-queue.poll');
    Route::post('/print-queue/complete', [PrintQueueController::class, 'complete'])->name('print-queue.complete');

    // Reserva routes
    Route::patch('/reservas/{reserva}/status', [ReservaController::class, 'updateStatus'])->name('reservas.updateStatus');
    Route::resource('/reservas', ReservaController::class)->except(['show']);

    // Pedido routes
    Route::middleware('permission:admin.pedidos')->group(function () {
        Route::post('/pedidos/mesa/{mesa}/pagar', [PedidoController::class, 'pagarMesa'])->name('pedidos.pagarMesa');
        Route::post('/pedidos/mesa/{mesa}/cerrar', [PedidoController::class, 'cerrarMesa'])->name('pedidos.cerrarMesa');
        Route::get('/pedidos/fragmento', [PedidoController::class, 'fragmento'])->name('pedidos.fragmento');
        Route::resource('/pedidos', PedidoController::class)->except(['show']);
        Route::get('/pedidos/{pedido}', [PedidoController::class, 'show'])->name('pedidos.show');
        Route::put('/pedidos/{pedido}/estado', [PedidoController::class, 'updateEstado'])->name('pedidos.updateEstado');
        Route::post('/pedidos/{pedido}/print-factura', [PedidoController::class, 'printFactura'])->name('pedidos.printFactura');
        Route::post('/pedidos/{mesa}/print-factura-mesa', [PedidoController::class, 'printFacturaMesa'])->name('pedidos.printFacturaMesa');
    });

    // Offline / PWA sync endpoints (staff siempre autenticado)
    Route::prefix('api/offline')->name('offline.')->group(function () {
        Route::post('/sync', [OfflineSyncController::class, 'sync'])->name('sync');
        Route::get('/mesas', [OfflineSyncController::class, 'mesas'])->name('mesas');
        Route::get('/menu', [OfflineSyncController::class, 'menu'])->name('menu');
        Route::get('/pedidos', [OfflineSyncController::class, 'pedidos'])->name('pedidos');
        Route::get('/session', [OfflineSyncController::class, 'session'])->name('session');
    });
});
Route::get('/test', function () {
    return 'test';
});
