<?php

namespace App\Http\Requests\Admin\GestionVehiculo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarVehiculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|required|exists:users,id',
            'placa' => [
                'sometimes',
                'required',
                'string',
                'max:10',
                Rule::unique('vehiculos', 'placa')->ignore($this->vehiculo_id)
            ],
            'marca' => 'nullable|string|max:50',
            'modelo' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:30',
            'tipo_vehiculo' => 'sometimes|required|in:carro,moto',
            'es_principal' => 'boolean',
            'estado' => 'sometimes|in:activo,inactivo'
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Debe seleccionar un usuario.',
            'placa.required' => 'La placa es obligatoria.',
            'placa.unique' => 'Esta placa ya está registrada.',
            'tipo_vehiculo.required' => 'Debe seleccionar el tipo de vehículo.',
        ];
    }
}