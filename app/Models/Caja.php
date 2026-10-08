<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function movimientos() {
        return $this->hasMany(MovimientoCaja::class);
    }
    public function ventas() {
        return $this->hasMany(Venta::class);
    }
}
