<?php
// app/Http/Requests/Admin/GestionTarjetas/CrearTarjetaRequest.php

namespace App\Http\Requests\Admin\GestionTarjetas;

use Illuminate\Foundation\Http\FormRequest;

class CrearTarjetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'uid_tarjeta' => 'required|string|max:50|unique:tarjeta_rfids,uid_tarjeta',
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