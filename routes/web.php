<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\ProductoController;

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

Route::get('/', function () {
    return view('index');
});

// Rutas del backend (API interna)

// Rutas de Ventas
Route::post('/api/sesiones/iniciar', [VentaController::class, 'iniciarSesion']);
Route::post('/api/ventas/{id}/consumo', [VentaController::class, 'agregarConsumo']);
Route::post('/api/ventas/{id}/cobrar-todo', [VentaController::class, 'cobrarTodo']);
Route::post('/api/ventas/{id}/pago', [VentaController::class, 'registrarPago']);
Route::get('/api/sesiones/activas', [VentaController::class, 'sesionesActivas']);

// Rutas de Caja
Route::get('/api/caja/estado', [CajaController::class, 'estado']);
Route::post('/api/caja/abrir', [CajaController::class, 'abrir']);
Route::post('/api/caja/egreso', [CajaController::class, 'registrarEgreso']);
Route::post('/api/caja/cerrar', [CajaController::class, 'cerrar']);
Route::delete('/api/ventas/consumos/{id}', [VentaController::class, 'eliminarConsumo']);
Route::post('/api/caja/agente', [CajaController::class, 'operacionAgente']);
Route::delete('/api/caja/movimientos/{id}', [CajaController::class, 'eliminarMovimiento']);

// Rutas de Inventario / Productos
Route::get('/api/productos', [ProductoController::class, 'index']);
Route::post('/api/productos', [ProductoController::class, 'store']);
Route::put('/api/productos/{id}', [ProductoController::class, 'update']);
Route::post('/api/productos/{id}/toggle', [ProductoController::class, 'toggleEstado']);
