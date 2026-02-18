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
        $materiaId = $this->route('materia')->id;

        return [
            'nombre' => 'sometimes|required|string|max:255|unique:materias,nombre,' . $materiaId,
            'nivel' => 'sometimes|required|in:secundario,universitario',
            'duracion_minutos' => 'sometimes|required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe una materia con este nombre.',
            'nivel.required' => 'El nivel es obligatorio.',
            'nivel.in' => 'El nivel seleccionado no es válido.',
            'duracion_minutos.required' => 'La duración es obligatoria.',
            'duracion_minutos.min' => 'La duración debe ser mayor a 0.',
        ];
    }
}
