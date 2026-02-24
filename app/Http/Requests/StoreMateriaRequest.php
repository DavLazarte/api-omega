<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMateriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'institucion_id' => 'required|exists:instituciones,id',
            'nivel_id' => 'required|exists:niveles,id',
            'duracion_minutos' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'institucion_id.required' => 'La institución es obligatoria.',
            'institucion_id.exists' => 'La institución seleccionada no es válida.',
            'nivel_id.required' => 'El nivel académico es obligatorio.',
            'nivel_id.exists' => 'El nivel seleccionado no es válido.',
            'duracion_minutos.required' => 'La duración es obligatoria.',
            'duracion_minutos.min' => 'La duración debe ser mayor a 0.',
        ];
    }
}
