<?php
// app/Http/Requests/Admin/GestionTarjetas/ActualizarTarjetaRequest.php

namespace App\Http\Requests\Admin\GestionTarjetas;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarTarjetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'uid_tarjeta' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tarjeta_rfids', 'uid_tarjeta')->ignore($this->tarjeta_id)
            ],
            'user_id' => 'nullable|exists:users,id',
            'fecha_asignacion' => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date|after:fecha_asignacion',
            'estado' => 'required|in:activa,inactiva,perdida,dañada,vencida',
            'observaciones' => 'nullable|string'
        ];
    }

    public function messages(): array
    {
        return [
            'uid_tarjeta.required' => 'El UID de la tarjeta es obligatorio.',
            'uid_tarjeta.unique' => 'Esta tarjeta ya está registrada.',
            'fecha_vencimiento.after' => 'La fecha de vencimiento debe ser posterior a la fecha de asignación.',
            'estado.required' => 'El estado es obligatorio.',
        ];
    }
}