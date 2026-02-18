<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $aulaId = $this->route('aula')->id;
        return [
            'nombre' => 'sometimes|required|string|max:255|unique:aulas,nombre,' . $aulaId,
            'capacidad' => 'sometimes|required|integer|min:1',
            'tipo' => 'sometimes|required|in:presencial,virtual',
            'ubicacion' => 'nullable|string|max:255',
            'estado' => 'sometimes|required|in:disponible,mantenimiento',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del aula es obligatorio.',
            'nombre.unique' => 'Ya existe un aula con este nombre.',
            'capacidad.required' => 'La capacidad es obligatoria.',
            'tipo.required' => 'El tipo de aula es obligatorio.',
            'estado.required' => 'El estado es obligatorio.',
        ];
    }
}
