<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Caja;
use App\Models\MovimientoCaja;
use App\Models\Pago;

class CajaController extends Controller
{
    // 1. Obtener el estado actual de la caja y sus totales
    public function estado()
    {
        $caja = Caja::where('estado', 'abierta')->latest()->first();
        if (!$caja) return response()->json(['abierta' => false]);

        $pagos = Pago::where('caja_id', $caja->id)->get();
        $ingresosEfectivo = $pagos->where('metodo', 'efectivo')->sum('monto');
        $ingresosYape = $pagos->whereIn('metodo', ['yape', 'plin', 'tarjeta'])->sum('monto');

        $listaEgresos = MovimientoCaja::where('caja_id', $caja->id)->where('tipo', 'egreso')->get();
        $egresos = $listaEgresos->sum('monto');

        $efectivoEsperado = $caja->monto_inicial + $ingresosEfectivo - $egresos;

        return response()->json([
            'abierta' => true,
            'caja' => $caja,
            'ingresos_efectivo' => $ingresosEfectivo,
            'ingresos_yape' => $ingresosYape,
            'egresos' => $egresos,
            'lista_egresos' => $listaEgresos, // Nueva variable
            'efectivo_esperado' => $efectivoEsperado
        ]);
    }

    // 2. Abrir la caja al inicio del día
    public function abrir(Request $request)
    {
        // Verificar si ya hay una caja abierta para no duplicar
        $cajaActiva = Caja::where('estado', 'abierta')->first();
        if ($cajaActiva) {
            return response()->json(['error' => 'Ya existe una caja abierta'], 400);
        }

        $caja = Caja::create([
            'monto_inicial' => $request->monto_inicial ?? 0,
            'fecha_apertura' => now(),
            'estado' => 'abierta'
        ]);

        return response()->json(['mensaje' => 'Caja abierta exitosamente', 'caja' => $caja]);
    }

    // 3. Registrar un gasto/egreso (Ej: Comprar agua)
    public function registrarEgreso(Request $request)
    {
        $caja = Caja::where('estado', 'abierta')->firstOrFail();

        MovimientoCaja::create([
            'caja_id' => $caja->id,
            'tipo' => 'egreso',
            'monto' => $request->monto,
            'concepto' => $request->concepto // Ej: "Pago proveedor de agua"
        ]);

        return response()->json(['mensaje' => 'Egreso registrado correctamente']);
    }

    // 4. Cerrar la caja al final del turno
    public function cerrar(Request $request)
    {
        $caja = Caja::where('estado', 'abierta')->firstOrFail();

        $caja->update([
            'fecha_cierre' => now(),
            'estado' => 'cerrada'
        ]);

        return response()->json(['mensaje' => 'Caja cerrada. Fin del turno.']);
    }

    // ELIMINAR UN EGRESO ERRÓNEO
    public function eliminarEgreso($id)
    {
        MovimientoCaja::findOrFail($id)->delete();
        return response()->json(['mensaje' => 'Egreso anulado']);
    }
}
