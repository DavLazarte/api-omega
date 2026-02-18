<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocenteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'email' => 'nullable|email|unique:docentes,email|unique:users,email',
            'materias' => 'nullable|array',
            'materia_ids' => 'nullable|array',
            'materia_ids.*' => 'integer|exists:materias,id',
            'disponibilidad_semanal' => 'nullable|array',
            'estado' => 'required|in:activo,inactivo',
            'crear_usuario' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'materias.array' => 'Las materias deben ser un arreglo.',
            'disponibilidad_semanal.array' => 'La disponibilidad semanal debe ser un arreglo.',
        ];
    }
}
