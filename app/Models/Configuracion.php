<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'configuracion';

    protected $fillable = [
        'mora_porcentaje',
        'mora_dias',
        'max_deuda_suspension',
        'porcentaje_primer_cuota',
        'horarios_atencion',
    ];

    protected $casts = [
        'mora_porcentaje'        => 'decimal:2',
        'mora_dias'              => 'integer',
        'max_deuda_suspension'   => 'integer',
        'porcentaje_primer_cuota'=> 'integer',
        'horarios_atencion'      => 'array',
    ];

    /**
     * Devuelve la única fila de configuración, o la crea con defaults.
     */
    public static function instancia(): self
    {
        return self::firstOrCreate([], [
            'mora_porcentaje'        => 15,
            'mora_dias'              => 10,
            'max_deuda_suspension'   => 6,
            'porcentaje_primer_cuota'=> 50,
        ]);
    }
}
