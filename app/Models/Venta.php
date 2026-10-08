<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function caja() {
        return $this->belongsTo(Caja::class);
    }
    public function detalles() {
        return $this->hasMany(DetalleVenta::class);
    }
    public function pagos() {
        return $this->hasMany(Pago::class);
    }
}
