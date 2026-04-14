<?php
// app/Http/Requests/Admin/GestionTarjetas/AsignarTarjetaRequest.php

namespace App\Http\Requests\Admin\GestionTarjetas;

use Illuminate\Foundation\Http\FormRequest;

class AsignarTarjetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tarjeta_id' => 'required|exists:tarjeta_rfids,id',
            'user_id' => 'required|exists:users,id',
            'fecha_asignacion' => 'required|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_asignacion',
            'observaciones' => 'nullable|string'
        ];
    }

    public function messages(): array
    {
        return [
            'tarjeta_id.required' => 'Debe seleccionar una tarjeta.',
            'tarjeta_id.exists' => 'La tarjeta seleccionada no existe.',
            'user_id.required' => 'Debe seleccionar un usuario.',
            'user_id.exists' => 'El usuario seleccionado no existe.',
            'fecha_asignacion.required' => 'La fecha de asignación es obligatoria.',
            'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento debe ser posterior o igual a la fecha de asignación.',
        ];
    }
}