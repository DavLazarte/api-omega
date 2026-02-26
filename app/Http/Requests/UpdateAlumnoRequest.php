<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAlumnoRequest extends FormRequest
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
        $alumno = $this->route('alumno');
        $alumnoId = $alumno->id;
        $userId = $alumno->user_id;

        return [
            'nombre' => 'sometimes|required|string|max:255',
            'telefono' => 'sometimes|required|string',
            'telefono_secundario' => 'nullable|string',
            'email' => [
                'nullable',
                'email',
                'unique:alumnos,email,' . $alumnoId,
                'unique:users,email,' . ($userId ?: 'NULL'),
            ],
            'estado' => 'sometimes|required|in:activo,suspendido,bloqueado',
            'saldo_clases' => 'sometimes|required|integer',
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
            'telefono.required' => 'El teléfono es obligatorio.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'saldo_clases.required' => 'El saldo de clases es obligatorio.',
            'saldo_clases.integer' => 'El saldo de clases debe ser un número entero.',
        ];
    }
}
