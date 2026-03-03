<?php

namespace App\Http\Requests\Admin\Administracion;

use Illuminate\Foundation\Http\FormRequest;

class CrearRolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:50|unique:roles,nombre',
            'descripcion' => 'nullable|string|max:255',
            'estado' => 'sometimes|in:activo,inactivo'
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del rol es obligatorio.',
            'nombre.unique' => 'Ya existe un rol con ese nombre.',
            'nombre.max' => 'El nombre no puede tener más de 50 caracteres.',
        ];
    }
}