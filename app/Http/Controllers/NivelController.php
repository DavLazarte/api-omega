<?php

namespace App\Http\Controllers;

use App\Models\Nivel;
use App\Http\Requests\StoreNivelRequest;
use Illuminate\Http\Request;

class NivelController extends Controller
{
    public function index(Request $request)
    {
        $query = Nivel::query();
        if ($request->has('search')) {
            $query->where('nombre', 'like', "%{$request->search}%");
        }

        if ($request->boolean('all')) {
            return response()->json($query->where('estado', 'activo')->get());
        }

        return response()->json($query->paginate($request->per_page ?? 15));
    }

    public function store(StoreNivelRequest $request)
    {
        $nivel = Nivel::create($request->validated());
        return response()->json($nivel, 201);
    }

    public function show(Nivel $nivel)
    {
        return response()->json($nivel);
    }

    public function update(StoreNivelRequest $request, Nivel $nivel)
    {
        $nivel->update($request->validated());
        return response()->json($nivel);
    }

    public function destroy(Nivel $nivel)
    {
        $nivel->delete();
        return response()->json(null, 204);
    }
}
