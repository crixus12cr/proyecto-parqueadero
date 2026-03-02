<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TarjetaRfid extends Model
{
    use HasFactory;

    protected $table = 'tarjeta_rfids';

    protected $fillable = [
        'uid_tarjeta',
        'user_id',
        'fecha_asignacion',
        'fecha_vencimiento',
        'estado',
        'ultimo_uso',
        'observaciones'
    ];

    protected $casts = [
        'fecha_asignacion' => 'datetime',
        'fecha_vencimiento' => 'datetime',
        'ultimo_uso' => 'datetime'
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function registrosAcceso()
    {
        return $this->hasMany(RegistroAcceso::class, 'tarjeta_rfid_id');
    }

    public function listaNegra()
    {
        return $this->hasMany(ListaNegra::class, 'tarjeta_rfid_id');
    }

    // Verificar si la tarjeta está vencida
    public function estaVencida()
    {
        return $this->fecha_vencimiento && $this->fecha_vencimiento->isPast();
    }

    // Verificar si es válida para acceso
    public function esValida()
    {
        return $this->estado === 'activa' && !$this->estaVencida();
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }

    public function scopePorVencer($query, $dias = 30)
    {
        return $query->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<=', now()->addDays($dias))
            ->where('fecha_vencimiento', '>', now());
    }

    public function scopeVencidas($query)
    {
        return $query->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<', now());
    }
}
