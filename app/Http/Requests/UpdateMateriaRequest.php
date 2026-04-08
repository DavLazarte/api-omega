<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMateriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'sometimes|required|string|max:255',
            'anios' => 'nullable|array',
            'anios.*' => 'string|max:255',
            'instituciones' => 'sometimes|required|array|min:1',
            'instituciones.*' => 'exists:instituciones,id',
            'niveles' => 'sometimes|required|array|min:1',
            'niveles.*' => 'exists:niveles,id',
            'duracion_minutos' => 'sometimes|required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'instituciones.required' => 'Debes seleccionar al menos una institución.',
            'instituciones.min' => 'Debes seleccionar al menos una institución.',
            'instituciones.*.exists' => 'Una de las instituciones seleccionadas no es válida.',
            'niveles.required' => 'Debes seleccionar al menos un nivel académico.',
            'niveles.min' => 'Debes seleccionar al menos un nivel académico.',
            'niveles.*.exists' => 'Uno de los niveles seleccionados no es válido.',
            'duracion_minutos.required' => 'La duración es obligatoria.',
            'duracion_minutos.min' => 'La duración debe ser mayor a 0.',
        ];
    }
}

