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
        $query = Materia::with(['institucion', 'academicLevel']);

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
        $data = $request->validated();

        // Sync legacy nivel field for backward compatibility
        if (isset($data['nivel_id'])) {
            $nivel = \App\Models\Nivel::find($data['nivel_id']);
            if ($nivel) {
                $data['nivel'] = $nivel->nombre;
            }
        }

        $materia = Materia::create($data);

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
        $data = $request->validated();

        // Sync legacy nivel field for backward compatibility
        if (isset($data['nivel_id'])) {
            $nivel = \App\Models\Nivel::find($data['nivel_id']);
            if ($nivel) {
                $data['nivel'] = $nivel->nombre;
            }
        }

        $materia->update($data);

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
