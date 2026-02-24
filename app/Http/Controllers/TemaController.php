<?php

namespace App\Http\Controllers;

use App\Models\Tema;
use Illuminate\Http\Request;

class TemaController extends Controller
{
    public function index(Request $request)
    {
        $query = Tema::with(['nivel', 'institucion']);

        if ($request->has('nivel_id')) {
            $query->where('nivel_id', $request->nivel_id);
        }

        if ($request->has('institucion_id')) {
            $query->where('institucion_id', $request->institucion_id);
        }

        if ($request->has('search')) {
            $query->where('nombre', 'like', "%{$request->search}%");
        }

        if ($request->boolean('all')) {
            return response()->json($query->orderBy('nombre')->get());
        }

        return response()->json($query->paginate($request->per_page ?? 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'nivel_id' => 'required|exists:niveles,id',
            'institucion_id' => 'required|exists:instituciones,id',
        ]);

        $tema = Tema::create($data);

        return response()->json([
            'message' => 'Tema creado exitosamente',
            'tema' => $tema->load(['nivel', 'institucion'])
        ], 201);
    }

    public function show(Tema $tema)
    {
        return response()->json($tema->load(['nivel', 'institucion']));
    }

    public function update(Request $request, Tema $tema)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'nivel_id' => 'sometimes|required|exists:niveles,id',
            'institucion_id' => 'sometimes|required|exists:instituciones,id',
        ]);

        $tema->update($data);

        return response()->json([
            'message' => 'Tema actualizado exitosamente',
            'tema' => $tema->load(['nivel', 'institucion'])
        ]);
    }

    public function destroy(Tema $tema)
    {
        $tema->delete();
        return response()->json(['message' => 'Tema eliminado exitosamente']);
    }
}
