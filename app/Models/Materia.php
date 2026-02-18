<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'nivel',
        'duracion_minutos',
    ];

    /**
     * Optional: Define relationships if needed in the future
     * e.g., to Docentes or Inscriptions
     */
}
