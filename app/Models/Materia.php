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
        'nivel', // Keeping for legacy/transition
        'anio',
        'duracion_minutos',
        'institucion_id',
        'nivel_id',
    ];

    public function institucion()
    {
        return $this->belongsTo(Institucion::class);
    }

    public function academicLevel()
    {
        return $this->belongsTo(Nivel::class, 'nivel_id');
    }

    /**
     * Optional: Define relationships if needed in the future
     * e.g., to Docentes or Inscriptions
     */
}
