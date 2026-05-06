<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocenteDisponibilidad extends Model
{
    use HasFactory;

    protected $table = 'docente_disponibilidades';

    protected $fillable = [
        'docente_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'activo',
        'nota',
    ];

    protected $casts = [
        'fecha'  => 'date',
        'activo' => 'boolean',
    ];

    // ─── Relaciones ────────────────────────────────────────────────

    public function docente()
    {
        return $this->belongsTo(Docente::class);
    }

    // ─── Scopes ────────────────────────────────────────────────────

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeHoy($query)
    {
        return $query->whereDate('fecha', today());
    }

    public function scopePorFecha($query, string $fecha)
    {
        return $query->whereDate('fecha', $fecha);
    }
}
