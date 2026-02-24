<?php

namespace App\Http\Controllers;

use App\Models\SolicitudMateria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SolicitudMateriaController extends Controller
{
    public function index(Request $request)
    {
        $query = SolicitudMateria::with(['alumno', 'materia.institucion', 'materia.academicLevel', 'tema'])
            ->where('estado', 'pendiente');

        if ($request->has('materia_id')) {
            $query->where('materia_id', $request->materia_id);
        }

        return response()->json($query->orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'alumno_id' => 'required|exists:alumnos,id',
            'materia_id' => 'required|exists:materias,id',
            'tema_id' => 'nullable|exists:temas,id',
            'contenidos' => 'nullable|string',
            'disponibilidad' => 'nullable|array',
            'urgente' => 'boolean',
        ], [
            'alumno_id.required' => 'El alumno es obligatorio.',
            'materia_id.required' => 'La materia es obligatoria.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $solicitud = SolicitudMateria::create($request->all());

        return response()->json([
            'message' => 'Solicitud de interés registrada correctamente',
            'solicitud' => $solicitud->load(['alumno', 'materia', 'tema']),
        ], 201);
    }

    public function destroy(SolicitudMateria $solicitud)
    {
        $solicitud->delete();
        return response()->json(['message' => 'Solicitud eliminada']);
    }

    public function updateEstado(Request $request, SolicitudMateria $solicitud)
    {
        $request->validate(['estado' => 'required|in:pendiente,agrupado,cancelado']);
        $solicitud->update(['estado' => $request->estado]);
        return response()->json(['message' => 'Estado actualizado']);
    }
}
