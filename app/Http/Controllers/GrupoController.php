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
            'fecha' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fin' => 'required',
            'tipo' => 'required|in:recurrente,intensivo',
            'alumnos_ids' => 'required|array|min:1',
            'solicitudes_ids' => 'nullable|array', // IDs de solicitudes para marcar como agrupadas
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return DB::transaction(function () use ($request) {
            $grupo = Grupo::create($request->only([
                'materia_id',
                'docente_id',
                'aula_id',
                'nombre',
                'contenidos_clase',
                'fecha',
                'hora_inicio',
                'hora_fin',
                'tipo'
            ]));

            $grupo->alumnos()->attach($request->alumnos_ids);

            // Marcar solicitudes como agrupadas
            if ($request->has('solicitudes_ids')) {
                SolicitudMateria::whereIn('id', $request->solicitudes_ids)
                    ->update(['estado' => 'agrupado']);
            }

            return response()->json([
                'message' => 'Grupo creado y alumnos agrupados correctamente',
                'grupo' => $grupo->load(['materia', 'docente', 'aula', 'alumnos']),
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
