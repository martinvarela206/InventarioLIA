<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ElementoController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RevisionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AppAdminController;
use App\Http\Controllers\TicketController;
use App\Models\EspacioLia;
use App\Models\PersonalLia;
use App\Models\ActividadLia;

// Autenticación
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Portada / Welcome (datos dinámicos administrados por app_admin)
Route::get('/', function () {
    $espacios = EspacioLia::activos()->get();
    $personal = PersonalLia::activos()->get();
    $actividades = ActividadLia::activos()->with('autor')->take(6)->get();
    return view('welcome', compact('espacios', 'personal', 'actividades'));
});

// Rutas de revisión técnica / registro
Route::get('/revision', [RevisionController::class, 'index'])->name('revision.index');

// Rutas autenticadas
Route::middleware('auth')->group(function () {
    // Pantalla de Bienvenida y Espera de Asignación de Permisos (para rol registered)
    Route::get('/bienvenida', function () {
        if (Auth::user()->hasActivePermissions()) {
            return redirect()->route('dashboard.index');
        }
        return view('auth.bienvenida');
    })->name('bienvenida');

    // Perfil de Usuario (requiere rol registered para gestionar sus datos personales)
    Route::middleware('role:registered')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    });

    // Módulos Operativos Protegidos (usuarios con permisos: rev, tec, item_admin, user_admin, app_admin)
    Route::middleware('has_permissions')->group(function () {
        // Dashboard Operativo
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

        // Catálogo de Elementos e Inspector Dual-Pane (Lectura general para módulos operativos)
        Route::get('/elementos', [ElementoController::class, 'index'])->name('elementos.index');
        Route::get('/elementos/export-csv', [ElementoController::class, 'exportCsv'])->name('elementos.exportCsv');
        Route::get('/elementos/imprimir', [ElementoController::class, 'imprimir'])->name('elementos.imprimir');

        // Mutación de Inventario (Alta, Edición y Baja de Activos - exclusivo item_admin)
        Route::middleware('role:item_admin')->group(function () {
            Route::get('/elementos/create', [ElementoController::class, 'create'])->name('elementos.create');
            Route::post('/elementos', [ElementoController::class, 'store'])->name('elementos.store');
            Route::get('/elementos/{elemento}/edit', [ElementoController::class, 'edit'])->name('elementos.edit');
            Route::put('/elementos/{elemento}', [ElementoController::class, 'update'])->name('elementos.update');
            Route::delete('/elementos/{elemento}', [ElementoController::class, 'destroy'])->name('elementos.destroy');
            Route::resource('tipos', \App\Http\Controllers\TipoController::class)->except(['show']);
            Route::post('/atributos', [\App\Http\Controllers\TipoController::class, 'storeAtributo'])->name('atributos.store');
            Route::resource('ubicaciones', \App\Http\Controllers\UbicacionController::class);
        });

        Route::get('/elementos/{elemento}', [ElementoController::class, 'show'])->name('elementos.show');

        // Historial y Consulta de Movimientos (Lectura general)
        Route::get('/movimientos', [MovimientoController::class, 'index'])->name('movimientos.index');
        Route::get('/movimientos/buscar-rapido', [MovimientoController::class, 'apiBuscar'])->name('movimientos.buscar-rapido');

        // Apertura de Tickets de Reparación (accesible para técnicos y revisores autorizados)
        Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');

        // Gestión Operativa de Movimientos, Préstamos y Tickets (exclusivo tec)
        Route::middleware('role:tec')->group(function () {
            Route::get('/movimientos/rapido', [MovimientoController::class, 'rapido'])->name('movimientos.rapido');
            Route::post('/movimientos', [MovimientoController::class, 'store'])->name('movimientos.store');
            Route::post('/movimientos/{movimiento}/devolucion', [MovimientoController::class, 'devolucion'])->name('movimientos.devolucion');
            Route::post('/movimientos/{movimiento}/correccion', [MovimientoController::class, 'storeCorreccion'])->name('movimientos.correccion');

            // Bandeja y Resolución de Tickets para Técnicos
            Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
            Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
            Route::post('/tickets/{ticket}/resolver', [TicketController::class, 'resolver'])->name('tickets.resolver');
        });

        // Módulo de Reportes Oficiales y Auditoría (exclusivo rev)
        Route::middleware('role:rev')->group(function () {
            Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
            Route::get('/reportes/auditoria-sala/{ubicacion}', [ReporteController::class, 'auditoriaSala'])->name('reportes.auditoria');
            Route::get('/reportes/{tipo}', [ReporteController::class, 'show'])->name('reportes.show');
            Route::get('/reportes/{tipo}/export-csv', [ReporteController::class, 'exportCsv'])->name('reportes.exportCsv');
        });

        // API de Atributos por Tipo
        Route::get('/api/tipos/{tipo}/atributos', [\App\Http\Controllers\TipoController::class, 'apiAtributosPorTipo'])->name('api.tipos.atributos');

        // Gestión de Usuarios y Trayectorias (user_admin)
        Route::middleware('user_admin')->group(function () {
            Route::resource('users', UserController::class)->only(['index', 'edit', 'update']);
            Route::post('/users/{user}/role', [UserController::class, 'toggleRole'])->name('users.toggleRole');
            Route::post('/users/{user}/toggle-activo', [UserController::class, 'toggleActivo'])->name('users.toggleActivo');
            Route::get('/users/{user}/trayectoria', [UserController::class, 'trayectoria'])->name('users.trayectoria');
            Route::post('/users/{user}/trayectoria', [UserController::class, 'storeTrayectoria'])->name('users.trayectoria.store');
            Route::delete('/trayectorias/{trayectoria}', [UserController::class, 'destroyTrayectoria'])->name('trayectorias.destroy');
        });

        // Configuración Institucional y Gestión de la App (app_admin, user_admin)
        Route::middleware('app_admin')->group(function () {
            Route::get('/app-admin', [AppAdminController::class, 'index'])->name('app_admin.index');

            // Carreras (Registro con historial inmutable)
            Route::get('/app-admin/carreras', [AppAdminController::class, 'carrerasIndex'])->name('app_admin.carreras');
            Route::post('/app-admin/carreras', [AppAdminController::class, 'carreraStore'])->name('app_admin.carreras.store');
            Route::put('/app-admin/carreras/{carrera}', [AppAdminController::class, 'carreraUpdate'])->name('app_admin.carreras.update');
            Route::post('/app-admin/carreras/{carrera}/toggle', [AppAdminController::class, 'carreraToggle'])->name('app_admin.carreras.toggle');
            Route::delete('/app-admin/carreras/{carrera}', [AppAdminController::class, 'carreraDestroy'])->name('app_admin.carreras.destroy');

            // Espacios y Fotos de Laboratorios
            Route::get('/app-admin/espacios', [AppAdminController::class, 'espaciosIndex'])->name('app_admin.espacios');
            Route::post('/app-admin/espacios', [AppAdminController::class, 'espacioStore'])->name('app_admin.espacios.store');
            Route::put('/app-admin/espacios/{espacio}', [AppAdminController::class, 'espacioUpdate'])->name('app_admin.espacios.update');
            Route::delete('/app-admin/espacios/{espacio}', [AppAdminController::class, 'espacioDestroy'])->name('app_admin.espacios.destroy');

            // Personal y Figuras Institucionales
            Route::get('/app-admin/personal', [AppAdminController::class, 'personalIndex'])->name('app_admin.personal');
            Route::post('/app-admin/personal', [AppAdminController::class, 'personalStore'])->name('app_admin.personal.store');
            Route::put('/app-admin/personal/{personal}', [AppAdminController::class, 'personalUpdate'])->name('app_admin.personal.update');
            Route::delete('/app-admin/personal/{personal}', [AppAdminController::class, 'personalDestroy'])->name('app_admin.personal.destroy');

            // Actividades y Novedades (Feed / Instagram)
            Route::get('/app-admin/actividades', [AppAdminController::class, 'actividadesIndex'])->name('app_admin.actividades');
            Route::post('/app-admin/actividades', [AppAdminController::class, 'actividadStore'])->name('app_admin.actividades.store');
            Route::put('/app-admin/actividades/{actividad}', [AppAdminController::class, 'actividadUpdate'])->name('app_admin.actividades.update');
            Route::delete('/app-admin/actividades/{actividad}', [AppAdminController::class, 'actividadDestroy'])->name('app_admin.actividades.destroy');

            // Gestión y Configuración de Reportes (app_admin)
            Route::post('/app-admin/reportes/guardar-desde-inventario', [AppAdminController::class, 'guardarDesdeInventario'])->name('app_admin.reportes.guardarDesdeInventario');
            Route::get('/app-admin/reportes', [AppAdminController::class, 'reportesIndex'])->name('app_admin.reportes');
            Route::post('/app-admin/reportes', [AppAdminController::class, 'reporteStore'])->name('app_admin.reportes.store');
            Route::put('/app-admin/reportes/{reporte}', [AppAdminController::class, 'reporteUpdate'])->name('app_admin.reportes.update');
            Route::match(['post', 'patch'], '/app-admin/reportes/{reporte}/toggle', [AppAdminController::class, 'reporteToggle'])->name('app_admin.reportes.toggle');
            Route::delete('/app-admin/reportes/{reporte}', [AppAdminController::class, 'reporteDestroy'])->name('app_admin.reportes.destroy');
        });
    });
});
