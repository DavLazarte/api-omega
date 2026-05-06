<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackCatalogo extends Model
{
    use HasFactory;

    protected $table = 'pack_catalogos';

    protected $fillable = [
        'nombre',
        'nivel',
        'cantidad_clases',
        'precio',
        'es_clase_suelta',
        'activo',
    ];

    protected $casts = [
        'precio'          => 'decimal:2',
        'es_clase_suelta' => 'boolean',
        'activo'          => 'boolean',
    ];

    // ─── Relaciones ────────────────────────────────────────────────

    public function ventas()
    {
        return $this->hasMany(PackClase::class, 'pack_catalogo_id');
    }

    // ─── Scopes ────────────────────────────────────────────────────

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorNivel($query, string $nivel)
    {
        return $query->where('nivel', $nivel);
    }
}
