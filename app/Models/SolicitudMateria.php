<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Alumno;
use App\Models\Materia;
use App\Models\Tema;

class SolicitudMateria extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_materias';

    protected $fillable = [
        'alumno_id',
        'materia_id',
        'tema_id',
        'contenidos',
        'disponibilidad',
        'urgente',
        'estado',
    ];

    protected $casts = [
        'disponibilidad' => 'array',
        'urgente' => 'boolean',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    public function tema()
    {
        return $this->belongsTo(Tema::class);
    }
}
