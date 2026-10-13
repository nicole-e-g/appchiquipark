<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Pago;
use App\Models\Producto;
use App\Models\Caja;

class VentaController extends Controller
{
    // 1. INICIAR UNA SESIÓN DE JUEGO (Entra un niño)
    public function iniciarSesion(Request $request)
    {
        // Asumimos que la caja 1 está abierta (luego haremos la lógica de cajas)
        $caja = Caja::where('estado', 'abierta')->first();
        if (!$caja) {
            return response()->json(['error' => 'Debes abrir la caja antes de registrar una venta.'], 403);
        }

        $productoTiempo = Producto::find($request->producto_id);

        // Creamos el ticket principal
        $venta = Venta::create([
            'caja_id' => $caja->id,
            'tipo' => 'sesion',
            'nombre_nino' => $request->nombre_nino,
            'inicio_sesion' => now(),
            'total' => $productoTiempo->precio,
            'estado' => 'pendiente'
        ]);

        // Añadimos el tiempo al detalle de la venta
        DetalleVenta::create([
            'venta_id' => $venta->id,
            'producto_id' => $productoTiempo->id,
            'cantidad' => 1,
            'precio_unitario' => $productoTiempo->precio,
            'subtotal' => $productoTiempo->precio,
            'estado_pago' => 'pendiente'
        ]);

        return response()->json(['mensaje' => 'Sesión iniciada con éxito', 'venta' => $venta]);
    }

    // 2. AGREGAR CONSUMO (Jugo, galleta, etc.)
    public function agregarConsumo(Request $request, $venta_id)
    {
        $venta = Venta::findOrFail($venta_id);
        $producto = Producto::findOrFail($request->producto_id);
        $cantidad = $request->cantidad ?? 1;
        $subtotal = $producto->precio * $cantidad;

        DetalleVenta::create([
            'venta_id' => $venta->id,
            'producto_id' => $producto->id,
            'cantidad' => $cantidad,
            'precio_unitario' => $producto->precio,
            'subtotal' => $subtotal,
            'estado_pago' => 'pendiente'
        ]);

        // Actualizamos el total adeudado en el ticket
        $venta->increment('total', $subtotal);

        return response()->json(['mensaje' => 'Consumo agregado']);
    }

    // 3. EL BOTÓN MÁGICO: COBRAR TODO (Corregido)
    public function cobrarTodo(Request $request, $venta_id)
    {
        $venta = Venta::with('detalles')->findOrFail($venta_id);

        // Calculamos la deuda real matemáticamente
        $montoACobrar = $venta->total - $venta->monto_pagado;

        if ($montoACobrar > 0) {
            // 1. Ingresamos solo el saldo restante a la caja
            Pago::create([
                'venta_id' => $venta->id,
                'caja_id' => $venta->caja_id,
                'monto' => $montoACobrar,
                'metodo' => $request->metodo_pago ?? 'efectivo'
            ]);
        }

        // 2. Pasamos TODOS los ítems a 'pagado' de golpe
        $venta->detalles()->update(['estado_pago' => 'pagado']);

        // 3. Cerramos el ticket principal, asegurando que el pagado iguale al total
        //$venta->update([
            //'monto_pagado' => $venta->total,
           // 'estado' => 'completado'
        //]);

        return response()->json(['mensaje' => 'Cobro total exitoso', 'cobrado' => $montoACobrar]);
    }

    // 4. REGISTRAR UN PAGO ESPECÍFICO (Para pagos mixtos, adelantos o Yape/Plin)
    public function registrarPago(Request $request, $id)
    {
        $venta = Venta::findOrFail($id);
        $monto = floatval($request->monto);

        // 1. Calculamos la deuda real
        $deudaActual = $venta->total - $venta->monto_pagado;

        // 2. Validaciones estrictas
        if ($monto <= 0) {
            return response()->json(['error' => 'El monto debe ser mayor a 0.'], 400);
        }

        if ($monto > $deudaActual) {
            return response()->json(['error' => 'El monto ingresado (S/ '.$monto.') supera la deuda actual (S/ '.$deudaActual.').'], 400);
        }

        // 3. Registrar el pago en la caja
        Pago::create([
            'venta_id' => $venta->id,
            'caja_id' => $venta->caja_id,
            'monto' => $monto,
            'metodo' => $request->metodo
        ]);

        // 4. Actualizamos el monto pagado del ticket
        $venta->increment('monto_pagado', $monto);

        // 5. ¡MAGIA! Si con este pago la deuda llega a cero, cerramos la cuenta automáticamente
        if ($venta->fresh()->monto_pagado >= $venta->total) {
            //$venta->update(['estado' => 'completado']);
            $venta->detalles()->update(['estado_pago' => 'pagado']); // Pasa todo a pagado
        }

        return response()->json(['mensaje' => 'Pago registrado correctamente']);
    }

    public function sesionesActivas()
    {
        // Traemos los tickets que no están completados, incluyendo sus detalles
        $sesiones = Venta::with(['detalles.producto'])
            ->whereIn('estado', ['pendiente', 'parcial'])
            ->get();

        // Calculamos los minutos de tiempo que compró cada niño para facilitar el trabajo al Frontend
        $sesiones->transform(function ($venta) {
            $minutosComprados = 0;
            foreach ($venta->detalles as $detalle) {
                if ($detalle->producto->es_tiempo) {
                    $minutosComprados += ($detalle->producto->minutos_otorgados * $detalle->cantidad);
                }
            }
            $venta->minutos_totales = $minutosComprados;
            return $venta;
        });

        return response()->json($sesiones);
    }

    // ELIMINAR UN CONSUMO ERRÓNEO
    public function eliminarConsumo($id)
    {
        $detalle = DetalleVenta::findOrFail($id);

        // Regla de negocio: No se puede eliminar algo que ya fue pagado
        if ($detalle->estado_pago === 'pagado') {
            return response()->json(['error' => 'No se puede eliminar un ítem ya pagado'], 403);
        }

        $venta = Venta::findOrFail($detalle->venta_id);

        // Restar el monto del total de la venta
        $venta->decrement('total', $detalle->subtotal);

        $detalle->delete();

        return response()->json(['mensaje' => 'Consumo eliminado correctamente']);
    }

    // CREAR UNA CUENTA PARA VENTA DE MOSTRADOR (Sin tiempo)
    public function ventaRapida(Request $request)
    {
        $caja = Caja::where('estado', 'abierta')->first();
        if (!$caja) {
            return response()->json(['error' => 'Debes abrir la bóveda primero.'], 403);
        }

        $venta = Venta::create([
            'caja_id' => $caja->id,
            'tipo' => 'directa',
            'nombre_nino' => 'Venta Libre',
            'inicio_sesion' => now(),
            'total' => 0,
            'monto_pagado' => 0,
            'estado' => 'pendiente'
        ]);

        return response()->json(['mensaje' => 'Ticket libre creado']);
    }

    // ANULAR / ELIMINAR UN TICKET POR ERROR
    public function anular($id)
    {
        $venta = Venta::findOrFail($id);

        // 1. Borramos los consumos/detalles asociados a este ticket
        \App\Models\DetalleVenta::where('venta_id', $id)->delete();

        // 2. Si por error se llegó a registrar algún pago parcial, lo borramos para que no descuadre la caja
        \App\Models\Pago::where('venta_id', $id)->delete();

        // 3. Borramos el ticket principal
        $venta->delete();

        return response()->json(['mensaje' => 'Ticket anulado correctamente']);
    }

    // TRAER TODAS LAS VENTAS DEL TURNO ACTUAL (Pagadas y Pendientes)
    public function ventasHoy()
    {
        $caja = Caja::where('estado', 'abierta')->first();
        if (!$caja) return response()->json([]);

        // Traemos las ventas ordenadas desde la más reciente
        $ventas = Venta::where('caja_id', $caja->id)->orderBy('id', 'desc')->get();
        return response()->json($ventas);
    }

    // FINALIZAR EL JUEGO (Retirar al niño del tablero)
    public function finalizarJuego($id)
    {
        $venta = \App\Models\Venta::findOrFail($id);

        // Validamos que no tenga deudas antes de dejarlo ir
        if ($venta->total > $venta->monto_pagado) {
            return response()->json(['error' => 'No puedes finalizar el juego. El cliente aún tiene saldo pendiente.'], 403);
        }

        // Ahora sí, cerramos el ticket y lo sacamos del tablero
        $venta->update(['estado' => 'completado']);

        return response()->json(['mensaje' => 'Juego finalizado y ticket cerrado.']);
    }
}
