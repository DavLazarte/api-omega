<?php

namespace App\Http\Controllers;

use App\Models\Institucion;
use App\Http\Requests\StoreInstitucionRequest;
use Illuminate\Http\Request;

class InstitucionController extends Controller
{
    public function index(Request $request)
    {
        $query = Institucion::query();
        if ($request->has('search')) {
            $query->where('nombre', 'like', "%{$request->search}%");
        }

        if ($request->boolean('all')) {
            return response()->json($query->where('estado', 'activo')->get());
        }

        return response()->json($query->paginate($request->per_page ?? 15));
    }

    public function store(StoreInstitucionRequest $request)
    {
        $institucion = Institucion::create($request->validated());
        return response()->json($institucion, 201);
    }

    public function show(Institucion $institucion)
    {
        return response()->json($institucion);
    }

    public function update(StoreInstitucionRequest $request, Institucion $institucion)
    {
        $institucion->update($request->validated());
        return response()->json($institucion);
    }

    public function destroy(Institucion $institucion)
    {
        $institucion->delete();
        return response()->json(null, 204);
    }
}
