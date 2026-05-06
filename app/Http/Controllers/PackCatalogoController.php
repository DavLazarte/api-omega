<?php

namespace App\Http\Controllers;

use App\Models\PackCatalogo;
use Illuminate\Http\Request;

class PackCatalogoController extends Controller
{
    public function index(Request $request)
    {
        $query = PackCatalogo::query();

        if ($request->has('nivel')) {
            $query->where('nivel', $request->nivel);
        }

        if ($request->boolean('solo_activos', true)) {
            $query->where('activo', true);
        }

        return response()->json(
            $query->orderBy('nivel')->orderBy('cantidad_clases')->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'          => 'required|string|max:255',
            'nivel'           => 'required|in:universitario_terciario,secundario_ingresos',
            'cantidad_clases' => 'nullable|integer|min:1',
            'precio'          => 'required|numeric|min:0',
            'es_clase_suelta' => 'boolean',
        ]);

        $data['es_clase_suelta'] = $request->boolean('es_clase_suelta', false);
        if ($data['es_clase_suelta']) {
            $data['cantidad_clases'] = null;
        }

        $pack = PackCatalogo::create($data);

        return response()->json([
            'message' => 'Pack creado exitosamente',
            'pack'    => $pack,
        ], 201);
    }

    public function show(PackCatalogo $packCatalogo)
    {
        return response()->json($packCatalogo);
    }

    public function update(Request $request, PackCatalogo $packCatalogo)
    {
        $data = $request->validate([
            'nombre'          => 'sometimes|string|max:255',
            'nivel'           => 'sometimes|in:universitario_terciario,secundario_ingresos',
            'cantidad_clases' => 'nullable|integer|min:1',
            'precio'          => 'sometimes|numeric|min:0',
            'es_clase_suelta' => 'sometimes|boolean',
            'activo'          => 'sometimes|boolean',
        ]);

        $packCatalogo->update($data);

        return response()->json([
            'message' => 'Pack actualizado exitosamente',
            'pack'    => $packCatalogo->fresh(),
        ]);
    }

    public function destroy(PackCatalogo $packCatalogo)
    {
        // Desactivar en lugar de borrar para preservar historial de ventas
        $packCatalogo->update(['activo' => false]);

        return response()->json(['message' => 'Pack desactivado exitosamente']);
    }
}
