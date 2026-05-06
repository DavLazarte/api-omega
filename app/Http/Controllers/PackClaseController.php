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
        $query = PackClase::with(['alumno', 'packCatalogo', 'validador', 'cargadoPor']);

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
            'monto_pagado'         => 'required|numeric|min:0',
            'metodo_pago'          => 'required|in:transferencia,efectivo,mercado_pago',
            'fecha_pago_informado' => 'required|date',
            'comprobante_path'     => 'nullable|string',
        ]);

        $data['estado']      = 'pendiente_validacion';
        $data['cargado_por'] = $request->user()->id;

        DB::transaction(function () use (&$data) {
            $pack = PackClase::create($data);
            $data['_pack'] = $pack;

            // Acreditar clases inmediatamente al alumno (pagan de a poco)
            Alumno::where('id', $data['alumno_id'])
                ->increment('saldo_clases', $data['cantidad_clases']);

            // Si estaba suspendido y ahora tiene saldo, reactivar
            $alumno = Alumno::find($data['alumno_id']);
            if ($alumno->estado === 'suspendido' && $alumno->saldo_clases > 0) {
                $alumno->update(['estado' => 'activo']);
            }
        });

        $pack = $data['_pack'];

        return response()->json([
            'message' => "Pack asignado. Se acreditaron {$data['cantidad_clases']} clase(s) al alumno.",
            'pack'    => $pack->load(['alumno', 'packCatalogo', 'cargadoPor']),
        ], 201);
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

        return response()->json([
            'message' => 'Pago validado correctamente.',
            'pack'    => $pack->fresh()->load(['alumno', 'packCatalogo', 'validador']),
        ]);
    }

    public function rechazar(Request $request, PackClase $pack)
    {
        if ($pack->estado !== 'pendiente_validacion') {
            return response()->json(['message' => 'Este pago ya fue procesado.'], 422);
        }

        DB::transaction(function () use ($pack, $request) {
            $pack->update([
                'estado'           => 'rechazado',
                'fecha_validacion' => now(),
                'validado_por'     => $request->user()->id,
            ]);

            // Revertir las clases acreditadas
            Alumno::where('id', $pack->alumno_id)
                ->decrement('saldo_clases', $pack->cantidad_clases);

            // Si el saldo quedó negativo o en cero, suspender al alumno
            $config = \App\Models\Configuracion::instancia();
            $alumno = Alumno::find($pack->alumno_id);
            if ($alumno->saldo_clases < 0 || abs($alumno->saldo_clases) >= $config->max_deuda_suspension) {
                $alumno->update(['estado' => 'suspendido']);
            }
        });

        return response()->json([
            'message' => 'Pago rechazado. Las clases fueron revertidas del saldo del alumno.',
            'pack'    => $pack->fresh()->load(['alumno', 'validador']),
        ]);
    }
}
