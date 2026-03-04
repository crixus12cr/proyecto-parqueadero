<?php

namespace App\Http\Requests\Admin\Administracion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarParametrosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Configuración general
            'capacidad_total' => 'required|integer|min:1|max:9999',
            'horario_apertura' => 'required|date_format:H:i',
            'horario_cierre' => 'required|date_format:H:i|after:horario_apertura',
            'dias_habiles' => 'required|array|min:1|max:7',
            'dias_habiles.*' => Rule::in(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo']),
            'alerta_ocupacion' => 'required|integer|min:1|max:100',
            'tiempo_gracia' => 'required|integer|min:0|max:60',
            'intentos_maximos_rfid' => 'required|integer|min:1|max:10',
            'notificar_email' => 'boolean',
            'email_notificaciones' => 'nullable|email|required_if:notificar_email,true',
            
            // Tipos de usuario (dinámico)
            'tipos_usuario' => 'required|array',
            'tipos_usuario.*.horas_maximas_estadia' => 'required|integer|min:1|max:48',
            'tipos_usuario.*.prioridad_acceso' => 'required|integer|min:1|max:5',
        ];
    }

    public function messages(): array
    {
        return [
            'capacidad_total.required' => 'La capacidad total es obligatoria.',
            'capacidad_total.integer' => 'La capacidad debe ser un número entero.',
            'horario_apertura.required' => 'El horario de apertura es obligatorio.',
            'horario_cierre.after' => 'El horario de cierre debe ser posterior al de apertura.',
            'dias_habiles.required' => 'Debe seleccionar al menos un día hábil.',
            'alerta_ocupacion.required' => 'El porcentaje de alerta es obligatorio.',
            'tiempo_gracia.required' => 'El tiempo de gracia es obligatorio.',
            'tipos_usuario.*.horas_maximas_estadia.required' => 'Las horas máximas son obligatorias para todos los tipos.',
        ];
    }
}