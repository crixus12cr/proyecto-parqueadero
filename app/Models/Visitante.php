<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitante extends Model
{
    use HasFactory;

    protected $table = 'visitantes';

    protected $fillable = [
        'nombre_completo',
        'numero_documento',
        'placa_vehiculo',
        'user_id_anfitrion',
        'fecha_ingreso',
        'fecha_salida',
        'autorizado_por',
        'estado'
    ];

    protected $casts = [
        'fecha_ingreso' => 'datetime',
        'fecha_salida' => 'datetime'
    ];

    // Relaciones
    public function anfitrion()
    {
        return $this->belongsTo(User::class, 'user_id_anfitrion');
    }

    public function autorizadoPor()
    {
        return $this->belongsTo(User::class, 'autorizado_por');
    }

    // Verificar si está dentro del parqueadero
    public function estaDentro()
    {
        return $this->fecha_ingreso && !$this->fecha_salida;
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeDentro($query)
    {
        return $query->whereNotNull('fecha_ingreso')
            ->whereNull('fecha_salida');
    }
}
