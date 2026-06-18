<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Docente;

class Egreso extends Model
{
    use HasFactory;

    protected $fillable = [
        'concepto',
        'monto',
        'fecha',
        'metodo_pago',
        'docente_id',
        'horas_pagadas',
        'mes_ejercicio',
        'comprobante_path',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'horas_pagadas' => 'decimal:2',
    ];

    public function docente()
    {
        return $this->belongsTo(Docente::class);
    }
}
