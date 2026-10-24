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

// Autenticación
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Portada / Welcome
Route::get('/', function () {
    return view('welcome');
});

// Rutas de revisión técnica / registro
Route::get('/revision', [RevisionController::class, 'index'])->name('revision.index');

// Rutas autenticadas
Route::middleware('auth')->group(function () {
    // Dashboard Operativo
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Catálogo de Elementos e Inspector Dual-Pane
    Route::resource('elementos', ElementoController::class);

    // Historial y Consulta de Movimientos (Lectura general)
    Route::get('/movimientos', [MovimientoController::class, 'index'])->name('movimientos.index');

    // Gestión Operativa de Movimientos y Préstamos (exclusivo tec, item_admin, user_admin - NO rev)
    Route::middleware('role:tec,item_admin,user_admin')->group(function () {
        Route::get('/movimientos/rapido', [MovimientoController::class, 'rapido'])->name('movimientos.rapido');
        Route::get('/api/elementos/buscar-rapido', [MovimientoController::class, 'apiBuscar'])->name('api.elementos.buscar');
        Route::post('/movimientos', [MovimientoController::class, 'store'])->name('movimientos.store');
        Route::post('/movimientos/{movimiento}/devolucion', [MovimientoController::class, 'devolucion'])->name('movimientos.devolucion');
        Route::post('/movimientos/{movimiento}/correccion', [MovimientoController::class, 'storeCorreccion'])->name('movimientos.correccion');
    });

    // Módulo de Reportes Oficiales y Auditoría
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/auditoria-sala/{ubicacion}', [ReporteController::class, 'auditoriaSala'])->name('reportes.auditoria');
    Route::get('/reportes/{tipo}', [ReporteController::class, 'show'])->name('reportes.show');
    Route::get('/reportes/{tipo}/export-csv', [ReporteController::class, 'exportCsv'])->name('reportes.exportCsv');

    // Perfil de Usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // API de Atributos por Tipo
    Route::get('/api/tipos/{tipo}/atributos', [\App\Http\Controllers\TipoController::class, 'apiAtributosPorTipo'])->name('api.tipos.atributos');

    // Gestión de Tipos de Activos y Atributos Técnicos (exclusivo item_admin)
    Route::middleware('role:item_admin')->group(function () {
        Route::resource('tipos', \App\Http\Controllers\TipoController::class)->except(['show']);
        Route::post('/atributos', [\App\Http\Controllers\TipoController::class, 'storeAtributo'])->name('atributos.store');
    });

    // Gestión de Usuarios y Trayectorias (user_admin)
    Route::middleware('user_admin')->group(function () {
        Route::resource('users', UserController::class)->only(['index', 'edit', 'update']);
        Route::post('/users/{user}/role', [UserController::class, 'toggleRole'])->name('users.toggleRole');
        Route::post('/users/{user}/toggle-activo', [UserController::class, 'toggleActivo'])->name('users.toggleActivo');
        Route::get('/users/{user}/trayectoria', [UserController::class, 'trayectoria'])->name('users.trayectoria');
        Route::post('/users/{user}/trayectoria', [UserController::class, 'storeTrayectoria'])->name('users.trayectoria.store');
        Route::delete('/trayectorias/{trayectoria}', [UserController::class, 'destroyTrayectoria'])->name('trayectorias.destroy');
    });
});
