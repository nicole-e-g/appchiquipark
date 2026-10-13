<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
// --- RUTAS PÚBLICAS (Solo Login) ---
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// ==========================================
// RUTAS PROTEGIDAS (El "Candado" de seguridad)
// ==========================================
Route::middleware('auth')->group(function () {

    // 1. Vista principal del POS
    Route::get('/', function () {
        return view('index');
    })->name('home');

    // 2. Cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // 3. API de Ventas y Sesiones
    Route::get('/api/sesiones/activas', [VentaController::class, 'sesionesActivas']);
    Route::post('/api/sesiones/iniciar', [VentaController::class, 'iniciarSesion']);
    Route::post('/api/ventas/rapida', [VentaController::class, 'ventaRapida']);
    Route::post('/api/ventas/{id}/consumo', [VentaController::class, 'agregarConsumo']);
    Route::delete('/api/ventas/consumos/{id}', [VentaController::class, 'eliminarConsumo']);
    Route::post('/api/ventas/{id}/pago', [VentaController::class, 'registrarPago']);
    Route::post('/api/ventas/{id}/cobrar-todo', [VentaController::class, 'cobrarTodo']);
    Route::delete('/api/ventas/{id}/anular', [VentaController::class, 'anular']);
    Route::get('/api/ventas/hoy', [VentaController::class, 'ventasHoy']);

    // 4. API de Caja y Agente
    Route::get('/api/caja/estado', [CajaController::class, 'estado']);
    Route::post('/api/caja/abrir', [CajaController::class, 'abrir']);
    Route::post('/api/caja/egreso', [CajaController::class, 'registrarEgreso']);
    Route::post('/api/caja/agente', [CajaController::class, 'operacionAgente']);
    Route::delete('/api/caja/movimientos/{id}', [CajaController::class, 'eliminarMovimiento']);
    Route::post('/api/caja/cerrar', [CajaController::class, 'cerrar']);

    // 5. API de Inventario y Productos
    Route::get('/api/productos', [ProductoController::class, 'index']);
    Route::post('/api/productos', [ProductoController::class, 'store']);
    Route::put('/api/productos/{id}', [ProductoController::class, 'update']);
    Route::post('/api/productos/{id}/toggle', [ProductoController::class, 'toggleEstado']);

});
