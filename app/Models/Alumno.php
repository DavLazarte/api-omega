<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Relations
use App\Models\Grupo;
use App\Models\PackClase;
use App\Models\SolicitudMateria;


class Alumno extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'telefono',
        'telefono_secundario',
        'email',
        'estado',
        'saldo_clases',
        'user_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'saldo_clases' => 'integer',
        ];
    }

    /**
     * Get the user that owns the alumno
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'alumno_grupo')->withTimestamps();
    }

    public function packsClases()
    {
        return $this->hasMany(PackClase::class);
    }

    public function solicitudesMaterias()
    {
        return $this->hasMany(SolicitudMateria::class);
    }

    /**
     * Check if alumno is blocked
     */
    public function isBloqueado(): bool
    {
        return $this->estado === 'bloqueado';
    }

    /**
     * Check if alumno is suspended
     */
    public function isSuspendido(): bool
    {
        return $this->estado === 'suspendido';
    }

    /**
     * Check if alumno is active
     */
    public function isActivo(): bool
    {
        return $this->estado === 'activo';
    }
}
