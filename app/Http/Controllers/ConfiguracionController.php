<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    public function show()
    {
        return response()->json(Configuracion::instancia());
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'mora_porcentaje'         => 'sometimes|numeric|min:0|max:100',
            'mora_dias'               => 'sometimes|integer|min:1',
            'max_deuda_suspension'    => 'sometimes|integer|min:1',
            'porcentaje_primer_cuota' => 'sometimes|integer|min:1|max:100',
            'horarios_atencion'       => 'sometimes|array',
            'horarios_atencion.*.dia'    => 'required_with:horarios_atencion|string',
            'horarios_atencion.*.inicio' => 'required_with:horarios_atencion|string',
            'horarios_atencion.*.fin'    => 'required_with:horarios_atencion|string',
        ]);

        $config = Configuracion::instancia();
        $config->update($data);

        return response()->json([
            'message'       => 'Configuración actualizada exitosamente',
            'configuracion' => $config->fresh(),
        ]);
    }
}
