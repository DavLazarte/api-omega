<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Nivel;
use App\Models\Institucion;

class Tema extends Model
{
    protected $fillable = [
        'nombre',
        'nivel_id',
        'institucion_id',
    ];

    public function nivel()
    {
        return $this->belongsTo(Nivel::class);
    }

    public function institucion()
    {
        return $this->belongsTo(Institucion::class);
    }
}
