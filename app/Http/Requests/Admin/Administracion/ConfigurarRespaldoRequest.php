<?php

namespace App\Http\Requests\Admin\Administracion;

use Illuminate\Foundation\Http\FormRequest;

class ConfigurarRespaldoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'frecuencia' => 'required|in:diario,semanal,mensual,manual',
            'hora_programada' => 'required_if:frecuencia,diario,semanal,mensual|nullable|date_format:H:i',
            'dia_semana' => 'required_if:frecuencia,semanal|nullable|integer|between:0,6',
            'dia_mes' => 'required_if:frecuencia,mensual|nullable|integer|between:1,31',
            'mantener_respaldos' => 'nullable|integer|min:1|max:100',
            'incluir_archivos' => 'boolean',
            'notificar_email' => 'nullable|email',
        ];
    }

    public function messages(): array
    {
        return [
            'frecuencia.required' => 'La frecuencia del respaldo es obligatoria.',
            'hora_programada.required_if' => 'La hora programada es obligatoria.',
            'hora_programada.date_format' => 'La hora debe tener formato HH:MM.',
            'dia_semana.required_if' => 'El día de la semana es obligatorio para respaldos semanales.',
            'dia_mes.required_if' => 'El día del mes es obligatorio para respaldos mensuales.',
        ];
    }
}