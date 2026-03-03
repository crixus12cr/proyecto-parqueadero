<?php

namespace App\Http\Requests\Admin\Administracion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarRolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rolId = $this->route('id') ?? $this->rol;
        
        return [
            'nombre' => [
                'required',
                'string',
                'max:50'
            ],
            'descripcion' => 'nullable|string|max:255',
            'estado' => 'sometimes|in:activo,inactivo'
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del rol es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 50 caracteres.',
        ];
    }
}