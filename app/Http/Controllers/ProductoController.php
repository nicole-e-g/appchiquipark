<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;

class ProductoController extends Controller
{
    // 1. Listar todos los productos (activos e inactivos)
    public function index()
    {
        return response()->json(Producto::orderBy('es_tiempo', 'desc')->get());
    }

    // 2. Crear un nuevo producto
    public function store(Request $request)
    {
        $producto = Producto::create([
            'nombre' => $request->nombre,
            'precio' => $request->precio,
            'es_tiempo' => $request->es_tiempo,
            'minutos_otorgados' => $request->es_tiempo ? $request->minutos : null,
            'estado' => true
        ]);
        return response()->json(['mensaje' => 'Producto creado', 'producto' => $producto]);
    }

    // 3. Actualizar un producto existente
    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);
        $producto->update([
            'nombre' => $request->nombre,
            'precio' => $request->precio,
            'es_tiempo' => $request->es_tiempo,
            'minutos_otorgados' => $request->es_tiempo ? $request->minutos : null,
        ]);
        return response()->json(['mensaje' => 'Producto actualizado']);
    }

    // 4. Activar / Desactivar producto (Borrado seguro)
    public function toggleEstado($id)
    {
        $producto = Producto::findOrFail($id);
        $producto->update(['estado' => !$producto->estado]); // Invierte el estado actual
        return response()->json(['mensaje' => 'Estado actualizado', 'estado' => $producto->estado]);
    }
}
