<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackClase extends Model
{
    use HasFactory;

    protected $table = 'packs_clases';

    protected $fillable = [
        'alumno_id',
        'pack_catalogo_id',
        'pack_origen_id',
        'cantidad_clases',
        'clases_habilitadas',
        'monto_pagado',
        'monto_total',
        'fecha_deuda_origen',
        'metodo_pago',
        'estado',
        'fecha_pago_informado',
        'fecha_validacion',
        'validado_por',
        'cargado_por',
        'comprobante_path',
    ];

    protected $casts = [
        'monto_pagado'         => 'decimal:2',
        'monto_total'          => 'decimal:2',
        'clases_habilitadas'   => 'integer',
        'fecha_pago_informado' => 'date',
        'fecha_validacion'     => 'date',
        'fecha_deuda_origen'   => 'date',
    ];

    protected $appends = ['deuda_restante', 'es_parcial'];

    // ─── Relaciones ────────────────────────────────────────────────

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    public function packCatalogo()
    {
        return $this->belongsTo(PackCatalogo::class, 'pack_catalogo_id');
    }

    public function validador()
    {
        return $this->belongsTo(User::class, 'validado_por');
    }

    public function cargadoPor()
    {
        return $this->belongsTo(User::class, 'cargado_por');
    }

    /**
     * Pack original al que pertenece este abono (null si este ES el pack original).
     */
    public function packOrigen()
    {
        return $this->belongsTo(self::class, 'pack_origen_id');
    }

    /**
     * Abonos / cuotas posteriores que se hicieron sobre este pack.
     */
    public function abonos()
    {
        return $this->hasMany(self::class, 'pack_origen_id');
    }

    // ─── Accessors ─────────────────────────────────────────────────

    /**
     * Deuda restante del pack.
     * Considera el monto de este pago + todos sus abonos.
     * Retorna 0 si no hay monto_total definido (pago completo).
     */
    public function getDeudaRestanteAttribute(): float
    {
        if (is_null($this->monto_total) || $this->monto_total <= 0) {
            return 0.0;
        }

        $totalPagado = (float) $this->monto_pagado
            + (float) $this->abonos()->sum('monto_pagado');

        $deuda = (float) $this->monto_total - $totalPagado;

        return max(0.0, round($deuda, 2));
    }

    /**
     * True si el pago es/fue parcial (hay monto_total definido y mayor al monto pagado original).
     */
    public function getEsParcialAttribute(): bool
    {
        return !is_null($this->monto_total)
            && (float) $this->monto_total > (float) $this->monto_pagado;
    }
}
