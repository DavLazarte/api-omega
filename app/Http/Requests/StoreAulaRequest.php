<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255|unique:aulas,nombre',
            'capacidad' => 'required|integer|min:1',
            'tipo' => 'required|in:presencial,virtual',
            'ubicacion' => 'nullable|string|max:255',
            'estado' => 'required|in:disponible,mantenimiento',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del aula es obligatorio.',
            'nombre.unique' => 'Ya existe un aula con este nombre.',
            'capacidad.required' => 'La capacidad es obligatoria.',
            'capacidad.integer' => 'La capacidad debe ser un número entero.',
            'capacidad.min' => 'La capacidad mínima es 1.',
            'tipo.required' => 'El tipo de aula es obligatorio.',
            'tipo.in' => 'El tipo debe ser presencial o virtual.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser disponible o mantenimiento.',
        ];
    }
}
