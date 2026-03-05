<?php

namespace App\Http\Requests\Admin\GestionUsuario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class CrearUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
            'tipo_usuario_id' => 'required|exists:tipo_usuarios,id',
            'numero_documento' => 'required|string|max:20|unique:users,numero_documento',
            'telefono' => 'nullable|string|max:20',
            'foto' => 'nullable|image|max:2048',
            'estado' => 'sometimes|in:activo,inactivo',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.unique' => 'Este email ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'tipo_usuario_id.required' => 'El tipo de usuario es obligatorio.',
            'numero_documento.required' => 'El número de documento es obligatorio.',
            'numero_documento.unique' => 'Este número de documento ya está registrado.',
        ];
    }
}