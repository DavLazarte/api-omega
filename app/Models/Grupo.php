<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Materia;
use App\Models\Docente;
use App\Models\Aula;
use App\Models\Alumno;

class Grupo extends Model
{
    use HasFactory;

    protected $fillable = [
        'materia_id',
        'docente_id',
        'aula_id',
        'nombre',
        'contenidos_clase',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'tipo',
        'estado',
    ];

    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class);
    }

    public function aula()
    {
        return $this->belongsTo(Aula::class);
    }

    public function alumnos()
    {
        return $this->belongsToMany(Alumno::class, 'alumno_grupo')->withTimestamps();
    }
}
