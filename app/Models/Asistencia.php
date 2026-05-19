<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Grupo;
use App\Models\Alumno;
use App\Models\Docente;

class Asistencia extends Model
{
    use HasFactory;

    protected $fillable = [
        'grupo_id',
        'alumno_id',
        'estado',           // presente, ausente, justificado
        'descuenta_clase',  // boolean
        'observaciones',
        'registrado_por',   // docente_id
    ];

    protected $casts = [
        'descuenta_clase' => 'boolean',
    ];

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'registrado_por');
    }
}
