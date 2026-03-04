<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'configuraciones';
    
    protected $fillable = [
        'capacidad_total',
        'horario_apertura',
        'horario_cierre',
        'dias_habiles',
        'alerta_ocupacion',
        'tiempo_gracia',
        'intentos_maximos_rfid',
        'notificar_email',
        'email_notificaciones'
    ];

    protected $casts = [
        'capacidad_total' => 'integer',
        'alerta_ocupacion' => 'integer',
        'tiempo_gracia' => 'integer',
        'intentos_maximos_rfid' => 'integer',
        'notificar_email' => 'boolean',
        'dias_habiles' => 'array', 
    ];

    protected $attributes = [
        'capacidad_total' => 500,
        'horario_apertura' => '06:00:00',
        'horario_cierre' => '22:00:00',
        'alerta_ocupacion' => 80,
        'tiempo_gracia' => 15,
        'intentos_maximos_rfid' => 3,
        'notificar_email' => true,
    ];

    // Accesor para obtener días hábiles como array
    public function getDiasHabilesListaAttribute()
    {
        return is_array($this->dias_habiles) ? $this->dias_habiles : [];
    }

    // Verificar si un día es hábil
    public function esDiaHabil($dia)
    {
        $dias = $this->dias_habiles_lista;
        return in_array($dia, $dias);
    }

    // Obtener horario de apertura formateado
    public function getHorarioAperturaFormateadoAttribute()
    {
        return substr($this->horario_apertura, 0, 5);
    }

    // Obtener horario de cierre formateado
    public function getHorarioCierreFormateadoAttribute()
    {
        return substr($this->horario_cierre, 0, 5);
    }
}