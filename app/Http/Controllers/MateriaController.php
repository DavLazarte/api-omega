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
        $query = Materia::query();

        if ($request->has('search')) {
            $query->where('nombre', 'like', "%{$request->search}%");
        }

        if ($request->has('nivel') && $request->nivel !== 'todos') {
            $query->where('nivel', $request->nivel);
        }

        // Si se pide una lista simple sin paginación (para selectores)
        if ($request->has('all')) {
            return response()->json($query->orderBy('nombre')->get());
        }

        return response()->json($query->paginate($request->per_page ?? 15));
    }

    public function store(StoreMateriaRequest $request)
    {
        $materia = Materia::create($request->validated());

        return response()->json([
            'message' => 'Materia creada exitosamente',
            'materia' => $materia,
        ], 201);
    }

    public function show(Materia $materia)
    {
        return response()->json($materia);
    }

    public function update(UpdateMateriaRequest $request, Materia $materia)
    {
        $materia->update($request->validated());

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
