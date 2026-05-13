<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\PackClase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackClaseController extends Controller
{
    public function index(Request $request)
    {
        $query = PackClase::with(['alumno', 'packCatalogo', 'validador', 'cargadoPor', 'abonos'])
            ->whereNull('pack_origen_id'); // Solo packs origen, no abonos individuales

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('alumno_id')) {
            $query->where('alumno_id', $request->alumno_id);
        }

        if ($request->has('desde')) {
            $query->whereDate('fecha_pago_informado', '>=', $request->desde);
        }

        if ($request->has('hasta')) {
            $query->whereDate('fecha_pago_informado', '<=', $request->hasta);
        }

        $pagos = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 50);

        return response()->json($pagos);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'alumno_id'            => 'required|exists:alumnos,id',
            'pack_catalogo_id'     => 'nullable|exists:pack_catalogos,id',
            'cantidad_clases'      => 'required|integer|min:1',
            'clases_habilitadas'   => 'nullable|integer|min:0',
            'monto_pagado'         => 'required|numeric|min:0',
            'monto_total'          => 'nullable|numeric|min:0',
            'metodo_pago'          => 'required|in:transferencia,efectivo,mercado_pago',
            'fecha_pago_informado' => 'required|date',
            'comprobante_path'     => 'nullable|string',
        ]);

        // Clases a acreditar: las habilitadas explícitamente, o todas si no se especifica
        $clasesAacreditar = $data['clases_habilitadas'] ?? $data['cantidad_clases'];

        // Determinar si es un pago parcial
        $montoTotal    = isset($data['monto_total']) ? (float) $data['monto_total'] : null;
        $montoPagado   = (float) $data['monto_pagado'];
        $esParcial     = $montoTotal !== null && $montoTotal > $montoPagado;

        if ($esParcial) {
            $data['fecha_deuda_origen'] = $data['fecha_pago_informado'];
        }

        $data['estado']      = 'pendiente_validacion';
        $data['cargado_por'] = $request->user()->id;

        DB::transaction(function () use (&$data, $clasesAacreditar) {
            $pack       = PackClase::create($data);
            $data['_pack'] = $pack;

            // Acreditar solo las clases habilitadas en este pago
            Alumno::where('id', $data['alumno_id'])
                ->increment('saldo_clases', $clasesAacreditar);

            // Recalcular estado según deuda
            $alumno = Alumno::find($data['alumno_id']);
            $alumno->recalcularEstado();
        });

        $pack = $data['_pack'];

        $clasesDesc = $clasesAacreditar < $data['cantidad_clases']
            ? "{$clasesAacreditar} de {$data['cantidad_clases']} clase(s) habilitadas (pago parcial)."
            : "{$clasesAacreditar} clase(s) acreditadas.";

        return response()->json([
            'message' => "Pack asignado. {$clasesDesc}",
            'pack'    => $pack->fresh()->load(['alumno', 'packCatalogo', 'cargadoPor', 'abonos']),
        ], 201);
    }

    /**
     * Registrar un abono parcial sobre un pack existente con deuda.
     */
    public function pagarDeuda(Request $request, PackClase $pack)
    {
        if ($pack->pack_origen_id !== null) {
            return response()->json(['message' => 'Solo se puede abonar sobre el pack original, no sobre un abono.'], 422);
        }

        if ($pack->deuda_restante <= 0) {
            return response()->json(['message' => 'Este pack no tiene deuda pendiente.'], 422);
        }

        $data = $request->validate([
            'monto_pagado'               => 'required|numeric|min:0.01',
            'clases_habilitadas'         => 'nullable|integer|min:0',
            'metodo_pago'                => 'required|in:transferencia,efectivo,mercado_pago',
            'fecha_pago_informado'       => 'required|date',
            'comprobante_path'           => 'nullable|string',
        ]);

        $clasesAdicionales = $data['clases_habilitadas'] ?? 0;

        DB::transaction(function () use ($pack, $data, $clasesAdicionales, $request) {
            // Crear el abono vinculado al pack original
            PackClase::create([
                'alumno_id'            => $pack->alumno_id,
                'pack_catalogo_id'     => $pack->pack_catalogo_id,
                'pack_origen_id'       => $pack->id,
                'cantidad_clases'      => 0,         // No agrega clases base; solo habilita las indicadas
                'clases_habilitadas'   => $clasesAdicionales,
                'monto_pagado'         => $data['monto_pagado'],
                'monto_total'          => null,       // Los abonos no tienen monto_total propio
                'metodo_pago'          => $data['metodo_pago'],
                'fecha_pago_informado' => $data['fecha_pago_informado'],
                'comprobante_path'     => $data['comprobante_path'] ?? null,
                'estado'               => 'pendiente_validacion',
                'cargado_por'          => $request->user()->id,
            ]);

            // Acreditar clases adicionales si se indicaron
            if ($clasesAdicionales > 0) {
                Alumno::where('id', $pack->alumno_id)
                    ->increment('saldo_clases', $clasesAdicionales);
            }

            // Recalcular estado del alumno
            $alumno = Alumno::find($pack->alumno_id);
            $alumno->recalcularEstado();
        });

        $packActualizado = $pack->fresh()->load(['alumno', 'packCatalogo', 'abonos']);

        return response()->json([
            'message'         => 'Abono registrado correctamente.',
            'pack'            => $packActualizado,
            'deuda_restante'  => $packActualizado->deuda_restante,
        ]);
    }

    public function validar(Request $request, PackClase $pack)
    {
        if ($pack->estado !== 'pendiente_validacion') {
            return response()->json(['message' => 'Este pago ya fue procesado.'], 422);
        }

        // Solo confirma el pago — las clases ya fueron acreditadas al crear el pack
        $pack->update([
            'estado'           => 'validado',
            'fecha_validacion' => now(),
            'validado_por'     => $request->user()->id,
        ]);

        // Recalcular estado del alumno tras la validación
        $alumno = Alumno::find($pack->alumno_id);
        $alumno->recalcularEstado();

        return response()->json([
            'message' => 'Pago validado correctamente.',
            'pack'    => $pack->fresh()->load(['alumno', 'packCatalogo', 'validador', 'abonos']),
        ]);
    }

    public function rechazar(Request $request, PackClase $pack)
    {
        if ($pack->estado !== 'pendiente_validacion') {
            return response()->json(['message' => 'Este pago ya fue procesado.'], 422);
        }

        DB::transaction(function () use ($pack, $request) {
            // Clases que se habían acreditado al crear este pack
            $clasesARevertir = $pack->clases_habilitadas ?? $pack->cantidad_clases;

            $pack->update([
                'estado'           => 'rechazado',
                'fecha_validacion' => now(),
                'validado_por'     => $request->user()->id,
            ]);

            // Revertir las clases acreditadas
            Alumno::where('id', $pack->alumno_id)
                ->decrement('saldo_clases', $clasesARevertir);

            // Recalcular estado del alumno
            $alumno = Alumno::find($pack->alumno_id);
            $alumno->recalcularEstado();
        });

        return response()->json([
            'message' => 'Pago rechazado. Las clases fueron revertidas del saldo del alumno.',
            'pack'    => $pack->fresh()->load(['alumno', 'validador']),
        ]);
    }
}
