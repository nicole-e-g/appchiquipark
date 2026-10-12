<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Caja;
use App\Models\MovimientoCaja;
use App\Models\Pago;

class CajaController extends Controller
{
    // 1. EL GRAN CÁLCULO DE LAS DOS CAJAS
    public function estado()
    {
        $caja = Caja::where('estado', 'abierta')->latest()->first();
        if (!$caja) return response()->json(['abierta' => false]);

        // Ventas de Chiquipark
        $pagos = Pago::where('caja_id', $caja->id)->get();
        $ingresosVentasEfectivo = $pagos->where('metodo', 'efectivo')->sum('monto');
        $ingresosVentasYape = $pagos->whereIn('metodo', ['yape', 'plin', 'tarjeta'])->sum('monto');

        // Movimientos Manuales y de Agente
        $movimientos = MovimientoCaja::where('caja_id', $caja->id)->get();
        $ingresosMovEfectivo = $movimientos->where('tipo', 'ingreso')->where('metodo', 'efectivo')->sum('monto');
        $egresosMovEfectivo = $movimientos->where('tipo', 'egreso')->where('metodo', 'efectivo')->sum('monto');

        $ingresosMovYape = $movimientos->where('tipo', 'ingreso')->where('metodo', 'yape')->sum('monto');
        $egresosMovYape = $movimientos->where('tipo', 'egreso')->where('metodo', 'yape')->sum('monto');

        // Cuadres Finales
        $efectivoTotal = $caja->monto_inicial + $ingresosVentasEfectivo + $ingresosMovEfectivo - $egresosMovEfectivo;
        $yapeTotal = $caja->monto_inicial_yape + $ingresosVentasYape + $ingresosMovYape - $egresosMovYape;

        return response()->json([
            'abierta' => true,
            'caja' => $caja,
            'efectivo_esperado' => $efectivoTotal,
            'yape_esperado' => $yapeTotal,
            'lista_movimientos' => $movimientos
        ]);
    }

    public function abrir(Request $request)
    {
        $cajaActiva = Caja::where('estado', 'abierta')->first();
        if ($cajaActiva) return response()->json(['error' => 'Ya existe una caja abierta'], 400);

        Caja::create([
            'monto_inicial' => $request->monto_inicial ?? 0,
            'monto_inicial_yape' => $request->monto_inicial_yape ?? 0,
            'fecha_apertura' => now(),
            'estado' => 'abierta'
        ]);

        return response()->json(['mensaje' => 'Caja abierta exitosamente']);
    }

    public function registrarEgreso(Request $request)
    {
        $caja = Caja::where('estado', 'abierta')->firstOrFail();
        MovimientoCaja::create([
            'caja_id' => $caja->id,
            'tipo' => 'egreso',
            'metodo' => $request->metodo, // ¿Pagaste de tu bolsillo o con tu Yape?
            'monto' => $request->monto,
            'concepto' => $request->concepto
        ]);
        return response()->json(['mensaje' => 'Gasto registrado']);
    }

    // 2. LA MAGIA DEL AGENTE (Cruzando dinero)
    public function operacionAgente(Request $request)
    {
        $caja = Caja::where('estado', 'abierta')->firstOrFail();
        $monto = floatval($request->monto);
        $comision = floatval($request->comision);
        $tipo = $request->tipo_operacion;

        if ($tipo === 'retiro') {
            // Cliente necesita billete. Nos Yapea (Monto + Comision) y le damos Efectivo (Monto)
            MovimientoCaja::create(['caja_id' => $caja->id, 'tipo' => 'ingreso', 'metodo' => 'yape', 'monto' => $monto + $comision, 'concepto' => "Agente: Ingreso Yape (Retiro)"]);
            MovimientoCaja::create(['caja_id' => $caja->id, 'tipo' => 'egreso', 'metodo' => 'efectivo', 'monto' => $monto, 'concepto' => "Agente: Salida Efectivo (Retiro)"]);
        } else {
            // Cliente tiene billete. Nos da Efectivo (Monto + Comision) y le Yapeamos (Monto)
            MovimientoCaja::create(['caja_id' => $caja->id, 'tipo' => 'ingreso', 'metodo' => 'efectivo', 'monto' => $monto + $comision, 'concepto' => "Agente: Ingreso Efectivo (Depósito)"]);
            MovimientoCaja::create(['caja_id' => $caja->id, 'tipo' => 'egreso', 'metodo' => 'yape', 'monto' => $monto, 'concepto' => "Agente: Salida Yape (Depósito)"]);
        }

        return response()->json(['mensaje' => 'Operación procesada correctamente']);
    }

    public function eliminarMovimiento($id)
    {
        MovimientoCaja::findOrFail($id)->delete();
        return response()->json(['mensaje' => 'Anulado']);
    }

    public function cerrar(Request $request)
    {
        Caja::where('estado', 'abierta')->firstOrFail()->update(['fecha_cierre' => now(), 'estado' => 'cerrada']);
        return response()->json(['mensaje' => 'Caja cerrada.']);
    }
}
