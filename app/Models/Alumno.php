<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

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

    // ─── Lógica de Deuda ───────────────────────────────────────────

    /**
     * Deuda total monetaria del alumno sumando todos sus packs con deuda pendiente.
     * Solo considera packs validados o pendiente_validacion (no rechazados).
     * Solo considera packs "origen" (sin pack_origen_id) para evitar doble conteo.
     */
    public function deudaTotal(): float
    {
        $packs = $this->packsClases()
            ->whereNull('pack_origen_id')
            ->whereIn('estado', ['validado', 'pendiente_validacion'])
            ->whereNotNull('monto_total')
            ->with('abonos')
            ->get();

        $total = 0.0;
        foreach ($packs as $pack) {
            $total += $pack->deuda_restante;
        }

        return round($total, 2);
    }

    /**
     * Fecha del pack con deuda más antigua (para calcular criticidad).
     * Retorna null si no hay deuda.
     */
    public function fechaDeudaMasAntigua(): ?Carbon
    {
        $pack = $this->packsClases()
            ->whereNull('pack_origen_id')
            ->whereIn('estado', ['validado', 'pendiente_validacion'])
            ->whereNotNull('fecha_deuda_origen')
            ->orderBy('fecha_deuda_origen', 'asc')
            ->first();

        return $pack ? Carbon::parse($pack->fecha_deuda_origen) : null;
    }

    /**
     * Recalcula y persiste el estado del alumno según la deuda y su antigüedad.
     *
     * Reglas:
     *  - Sin deuda monetaria            → activo
     *  - Deuda < 7 días                 → activo  (warning visual en frontend)
     *  - Deuda entre 7 y 21 días        → suspendido
     *  - Deuda > 21 días                → bloqueado
     *
     * El estado solo puede EMPEORAR automáticamente por deuda. Para mejorar
     * el estado (de bloqueado a activo) se requiere acción manual del admin
     * o que la deuda se salde completamente.
     */
    public function recalcularEstado(): void
    {
        $deuda = $this->deudaTotal();

        if ($deuda <= 0) {
            // Sin deuda: si estaba suspendido/bloqueado por deuda, reactivar
            if (in_array($this->estado, ['suspendido', 'bloqueado'])) {
                $this->update(['estado' => 'activo']);
            }
            return;
        }

        $fechaOrigen = $this->fechaDeudaMasAntigua();
        $diasDeuda   = $fechaOrigen ? (int) $fechaOrigen->diffInDays(now()) : 0;

        $nuevoEstado = match (true) {
            $diasDeuda > 21 => 'bloqueado',
            $diasDeuda >= 7 => 'suspendido',
            default         => 'activo',  // < 7 días: solo warning visual
        };

        // Solo cambia si empeora o si la deuda ya fue saldada
        if ($nuevoEstado !== $this->estado) {
            $this->update(['estado' => $nuevoEstado]);
        }
    }
}
