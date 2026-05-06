<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\DocenteDisponibilidad;
use Illuminate\Http\Request;

class DocenteDisponibilidadController extends Controller
{
    /**
     * Lista disponibilidades con filtros.
     * Endpoint clave para el agente: GET /docentes-disponibilidad?fecha=2026-05-05
     */
    public function index(Request $request)
    {
        $query = DocenteDisponibilidad::with('docente');

        if ($request->has('docente_id')) {
            $query->where('docente_id', $request->docente_id);
        }

        if ($request->has('fecha')) {
            $query->whereDate('fecha', $request->fecha);
        } else {
            // Sin filtro de fecha devuelve solo los de hoy y futuros
            $query->whereDate('fecha', '>=', today());
        }

        if ($request->boolean('solo_activos', false)) {
            $query->where('activo', true);
        }

        return response()->json(
            $query->orderBy('fecha')->orderBy('hora_inicio')->get()
        );
    }

    /**
     * Endpoint especial para el agente: devuelve docentes disponibles HOY
     * con sus slots activos agrupados por docente.
     * GET /docentes-disponibles-hoy
     */
    public function disponiblesHoy()
    {
        $docentes = Docente::with([
            'disponibilidades' => function ($q) {
                $q->whereDate('fecha', today())
                  ->where('activo', true)
                  ->orderBy('hora_inicio');
            }
        ])
        ->whereHas('disponibilidades', function ($q) {
            $q->whereDate('fecha', today())
              ->where('activo', true);
        })
        ->where('estado', 'activo')
        ->get()
        ->map(function ($d) {
            return [
                'id'              => $d->id,
                'nombre'          => $d->nombre,
                'email'           => $d->email,
                'horarios_hoy'    => $d->disponibilidades->map(fn($slot) => [
                    'id'          => $slot->id,
                    'hora_inicio' => $slot->hora_inicio,
                    'hora_fin'    => $slot->hora_fin,
                    'nota'        => $slot->nota,
                ]),
            ];
        });

        return response()->json([
            'fecha'   => today()->toDateString(),
            'docentes' => $docentes,
        ]);
    }

    /**
     * Crear un slot de disponibilidad.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'docente_id'  => 'required|exists:docentes,id',
            'fecha'       => 'required|date|after_or_equal:today',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin'    => 'required|date_format:H:i|after:hora_inicio',
            'nota'        => 'nullable|string|max:255',
        ]);

        $slot = DocenteDisponibilidad::create($data);

        return response()->json([
            'message' => 'Disponibilidad registrada',
            'slot'    => $slot->load('docente'),
        ], 201);
    }

    /**
     * Actualizar un slot (cambiar horario o toggle activo).
     */
    public function update(Request $request, DocenteDisponibilidad $disponibilidad)
    {
        $data = $request->validate([
            'fecha'       => 'sometimes|date',
            'hora_inicio' => 'sometimes|date_format:H:i',
            'hora_fin'    => 'sometimes|date_format:H:i',
            'activo'      => 'sometimes|boolean',
            'nota'        => 'nullable|string|max:255',
        ]);

        $disponibilidad->update($data);

        return response()->json([
            'message'       => 'Disponibilidad actualizada',
            'disponibilidad' => $disponibilidad->fresh()->load('docente'),
        ]);
    }

    /**
     * Eliminar un slot.
     */
    public function destroy(DocenteDisponibilidad $disponibilidad)
    {
        $disponibilidad->delete();
        return response()->json(['message' => 'Disponibilidad eliminada']);
    }
}
