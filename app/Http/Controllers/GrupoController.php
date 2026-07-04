<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\SolicitudMateria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GrupoController extends Controller
{
    public function index(Request $request)
    {
        $query = Grupo::with(['materia', 'docente', 'aula', 'alumnos']);

        if ($request->has('fecha')) {
            $query->where('fecha', $request->fecha);
        }
        
        if ($request->has('start') && $request->has('end')) {
            $query->whereBetween('fecha', [$request->start, $request->end]);
        }

        $grupos = $query->orderBy('created_at', 'desc')->get();

        return response()->json($grupos);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'materia_id' => 'required|exists:materias,id',
            'docente_id' => 'required|exists:docentes,id',
            'aula_id' => 'required|exists:aulas,id',
            'nombre' => 'nullable|string|max:255',
            'contenidos_clase' => 'nullable|string',
            'cronograma' => 'required|array|min:1',
            'cronograma.*.fecha' => 'required|date',
            'cronograma.*.hora_inicio' => 'required',
            'cronograma.*.hora_fin' => 'required',
            'tipo' => 'required|in:recurrente,intensivo',
            'alumnos_ids' => 'required|array|min:1',
            'solicitudes_ids' => 'nullable|array', // IDs de solicitudes para marcar como agrupadas
            'cantidad_clases' => 'nullable|integer|min:1|max:50', // Agregado para programar múltiples
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return DB::transaction(function () use ($request) {
            $cantidad = $request->input('cantidad_clases', 1);
            $cronograma = $request->input('cronograma');
            $gruposCreados = [];

            for ($i = 0; $i < $cantidad; $i++) {
                foreach ($cronograma as $horario) {
                    $fecha = \Carbon\Carbon::parse($horario['fecha']);
                    $grupo = Grupo::create([
                        'materia_id' => $request->materia_id,
                        'docente_id' => $request->docente_id,
                        'aula_id' => $request->aula_id,
                        'nombre' => $request->nombre,
                        'contenidos_clase' => $request->contenidos_clase,
                        'fecha' => $fecha->copy()->addWeeks($i)->format('Y-m-d'),
                        'hora_inicio' => $horario['hora_inicio'],
                        'hora_fin' => $horario['hora_fin'],
                        'tipo' => $request->tipo
                    ]);

                    $grupo->alumnos()->attach($request->alumnos_ids);
                    $gruposCreados[] = $grupo;
                }
            }

            // Marcar solicitudes como agrupadas
            if ($request->has('solicitudes_ids')) {
                SolicitudMateria::whereIn('id', $request->solicitudes_ids)
                    ->update(['estado' => 'agrupado']);
            }

            return response()->json([
                'message' => $cantidad > 1 ? "{$cantidad} clases programadas correctamente" : 'Grupo creado y alumnos agrupados correctamente',
                'grupo' => $gruposCreados[0]->load(['materia', 'docente', 'aula', 'alumnos']),
            ], 201);
        });
    }

    public function update(Request $request, Grupo $grupo)
    {
        $grupo->update($request->all());

        if ($request->has('alumnos_ids')) {
            $grupo->alumnos()->sync($request->alumnos_ids);
        }

        return response()->json([
            'message' => 'Grupo actualizado correctamente',
            'grupo' => $grupo->load(['materia', 'docente', 'aula', 'alumnos']),
        ]);
    }

    public function addAlumno(Request $request, Grupo $grupo)
    {
        $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'solicitud_id' => 'nullable|exists:solicitudes_materias,id',
        ]);

        return DB::transaction(function () use ($request, $grupo) {
            // Solo agregar si no está ya
            if (!$grupo->alumnos()->where('alumno_id', $request->alumno_id)->exists()) {
                $grupo->alumnos()->attach($request->alumno_id);
            }

            if ($request->has('solicitud_id')) {
                SolicitudMateria::where('id', $request->solicitud_id)
                    ->update(['estado' => 'agrupado']);
            }

            return response()->json([
                'message' => 'Alumno sumado al grupo correctamente',
                'grupo' => $grupo->load(['materia', 'docente', 'aula', 'alumnos']),
            ]);
        });
    }

    public function destroy(Grupo $grupo)
    {
        $grupo->delete();
        return response()->json(['message' => 'Grupo eliminado correctamente']);
    }
}
