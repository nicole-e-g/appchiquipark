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

        if (!$caja) {
            return response()->json(['abierta' => false]);
        }

        // Sumar todos los pagos (ventas y abonos) que entraron a esta caja
        $ingresos = Pago::where('caja_id', $caja->id)->sum('monto');

        // Sumar todos los egresos (compras, gastos) de esta caja
        $egresos = MovimientoCaja::where('caja_id', $caja->id)->where('tipo', 'egreso')->sum('monto');

        // La fórmula sagrada del cierre de caja
        $totalEsperado = $caja->monto_inicial + $ingresos - $egresos;

        return response()->json([
            'abierta' => true,
            'caja' => $caja,
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'total_esperado' => $totalEsperado
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
}
