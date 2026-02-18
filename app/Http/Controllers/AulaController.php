<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Http\Requests\StoreAulaRequest;
use App\Http\Requests\UpdateAulaRequest;
use Illuminate\Http\Request;

class AulaController extends Controller
{
    public function index(Request $request)
    {
        $query = Aula::query();

        if ($request->has('search')) {
            $query->where('nombre', 'like', "%{$request->search}%");
        }

        if ($request->has('estado') && $request->estado !== 'todos') {
            $query->where('estado', $request->estado);
        }

        if ($request->has('tipo') && $request->tipo !== 'todos') {
            $query->where('tipo', $request->tipo);
        }

        // Si se pide 'all=true', devolvemos todo sin paginar (para selectores)
        if ($request->boolean('all')) {
            return response()->json($query->get());
        }

        return response()->json($query->paginate($request->per_page ?? 15));
    }

    public function store(StoreAulaRequest $request)
    {
        $aula = Aula::create($request->validated());
        return response()->json([
            'message' => 'Aula creada exitosamente',
            'aula' => $aula
        ], 201);
    }

    public function show(Aula $aula)
    {
        return response()->json($aula);
    }

    public function update(UpdateAulaRequest $request, Aula $aula)
    {
        $aula->update($request->validated());
        return response()->json([
            'message' => 'Aula actualizada exitosamente',
            'aula' => $aula
        ]);
    }

    public function destroy(Aula $aula)
    {
        $aula->delete();
        return response()->json([
            'message' => 'Aula eliminada exitosamente'
        ]);
    }
}
