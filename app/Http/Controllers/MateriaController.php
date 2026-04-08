<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Http\Requests\StoreMateriaRequest;
use App\Http\Requests\UpdateMateriaRequest;
use Illuminate\Http\Request;

class MateriaController extends Controller
{
    public function index(Request $request)
    {
        $query = Materia::with(['instituciones', 'niveles']);

        if ($request->has('search')) {
            $query->where('nombre', 'like', "%{$request->search}%");
        }

        if ($request->has('nivel') && $request->nivel !== 'todos') {
            $query->whereHas('niveles', function ($q) use ($request) {
                // Compatibility for string or ID
                $q->where('niveles.id', $request->nivel)->orWhere('niveles.nombre', $request->nivel);
            });
        }

        // Si se pide una lista simple sin paginación (para selectores)
        if ($request->has('all')) {
            return response()->json($query->orderBy('nombre')->get());
        }

        return response()->json($query->paginate($request->per_page ?? 15));
    }

    public function store(StoreMateriaRequest $request)
    {
        $data = $request->validated();

        $materia = Materia::create([
            'nombre' => $data['nombre'],
            'anios' => $data['anios'] ?? null,
            'duracion_minutos' => $data['duracion_minutos'],
        ]);

        if (isset($data['instituciones'])) {
            $materia->instituciones()->sync($data['instituciones']);
        }
        if (isset($data['niveles'])) {
            $materia->niveles()->sync($data['niveles']);
        }

        $materia->load(['instituciones', 'niveles']);

        return response()->json([
            'message' => 'Materia creada exitosamente',
            'materia' => $materia,
        ], 201);
    }

    public function show(Materia $materia)
    {
        $materia->load(['instituciones', 'niveles']);
        return response()->json($materia);
    }

    public function update(UpdateMateriaRequest $request, Materia $materia)
    {
        $data = $request->validated();

        $updateData = [];
        if (isset($data['nombre'])) $updateData['nombre'] = $data['nombre'];
        if (array_key_exists('anios', $data)) $updateData['anios'] = $data['anios'];
        if (isset($data['duracion_minutos'])) $updateData['duracion_minutos'] = $data['duracion_minutos'];

        if (!empty($updateData)) {
            $materia->update($updateData);
        }

        if (isset($data['instituciones'])) {
            $materia->instituciones()->sync($data['instituciones']);
        }
        if (isset($data['niveles'])) {
            $materia->niveles()->sync($data['niveles']);
        }

        $materia->load(['instituciones', 'niveles']);

        return response()->json([
            'message' => 'Materia actualizada exitosamente',
            'materia' => $materia,
        ]);
    }

    public function destroy(Materia $materia)
    {
        // Verificar si la materia está siendo usada (opcional, pero recomendado)
        // Por ahora eliminamos directo
        $materia->delete();

        return response()->json([
            'message' => 'Materia eliminada exitosamente'
        ]);
    }
}
