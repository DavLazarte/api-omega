<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Materia;
use App\Models\User;
use App\Models\DocenteDisponibilidad;

class Docente extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'email',
        'materias',
        'disponibilidad_semanal',
        'estado',
        'tipo_contrato',
        'valor_contrato',
        'user_id',
    ];

    /**
     * The subjects assigned to the teacher.
     */
    public function subjects()
    {
        return $this->belongsToMany(Materia::class, 'docente_materia');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'materias' => 'array',
            'disponibilidad_semanal' => 'array',
        ];
    }

    /**
     * Get the user that owns the docente
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Disponibilidades puntuales del docente
     */
    public function disponibilidades()
    {
        return $this->hasMany(DocenteDisponibilidad::class);
    }

    /**
     * Disponibilidades activas de hoy
     */
    public function disponibilidadesHoy()
    {
        return $this->hasMany(DocenteDisponibilidad::class)
                    ->whereDate('fecha', today())
                    ->where('activo', true);
    }

    /**
     * Egresos/pagos realizados al docente
     */
    public function egresos()
    {
        return $this->hasMany(Egreso::class);
    }
}
