<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function venta() {
        return $this->belongsTo(Venta::class);
    }
    public function caja() {
        return $this->belongsTo(Caja::class);
    }
}
