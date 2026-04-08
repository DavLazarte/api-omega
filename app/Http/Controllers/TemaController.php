<?php

namespace App\Http\Controllers;

use App\Models\Tema;
use Illuminate\Http\Request;

class TemaController extends Controller
{
    public function index(Request $request)
    {
        $query = Tema::query();

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
        ]);

        $tema = Tema::create($data);

        return response()->json([
            'message' => 'Tema creado exitosamente',
            'tema' => $tema
        ], 201);
    }

    public function show(Tema $tema)
    {
        return response()->json($tema);
    }

    public function update(Request $request, Tema $tema)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
        ]);

        $tema->update($data);

        return response()->json([
            'message' => 'Tema actualizado exitosamente',
            'tema' => $tema
        ]);
    }

    public function destroy(Tema $tema)
    {
        $tema->delete();
        return response()->json(['message' => 'Tema eliminado exitosamente']);
    }
}
