<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionRespaldo extends Model
{
    use HasFactory;

    protected $table = 'configuraciones_respaldo';

    protected $fillable = [
        'frecuencia',
        'hora_programada',
        'dia_semana',
        'dia_mes',
        'mantener_respaldos',
        'incluir_archivos',
        'notificar_email',
        'ultimo_respaldo',
        'proximo_respaldo',
        'activo'
    ];

    protected $casts = [
        'hora_programada' => 'datetime:H:i',
        'ultimo_respaldo' => 'datetime',
        'proximo_respaldo' => 'datetime',
        'incluir_archivos' => 'boolean',
        'activo' => 'boolean'
    ];

    public function getFrecuenciaTextoAttribute()
    {
        return match($this->frecuencia) {
            'diario' => 'Diario',
            'semanal' => 'Semanal',
            'mensual' => 'Mensual',
            'manual' => 'Manual',
            default => 'No configurado'
        };
    }
}