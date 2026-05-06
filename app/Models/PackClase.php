<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackClase extends Model
{
    use HasFactory;

    protected $table = 'packs_clases';

    protected $fillable = [
        'alumno_id',
        'pack_catalogo_id',
        'cantidad_clases',
        'monto_pagado',
        'metodo_pago',
        'estado',
        'fecha_pago_informado',
        'fecha_validacion',
        'validado_por',
        'cargado_por',
        'comprobante_path',
    ];

    protected $casts = [
        'monto_pagado'         => 'decimal:2',
        'fecha_pago_informado' => 'date',
        'fecha_validacion'     => 'date',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    public function packCatalogo()
    {
        return $this->belongsTo(PackCatalogo::class, 'pack_catalogo_id');
    }

    public function validador()
    {
        return $this->belongsTo(User::class, 'validado_por');
    }

    public function cargadoPor()
    {
        return $this->belongsTo(User::class, 'cargado_por');
    }
}
