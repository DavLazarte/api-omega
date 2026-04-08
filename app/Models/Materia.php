<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Institucion;
use App\Models\Nivel;

class Materia extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'anios',
        'duracion_minutos',
    ];

    protected $casts = [
        'anios' => 'array',
    ];

    public function instituciones()
    {
        return $this->belongsToMany(Institucion::class);
    }

    public function niveles()
    {
        return $this->belongsToMany(Nivel::class, 'materia_nivel');
    }
}

