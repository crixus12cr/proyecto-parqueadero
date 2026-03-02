<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListaNegra extends Model
{
    use HasFactory;

    protected $table = 'lista_negra';

    protected $fillable = [
        'tarjeta_rfid_id',
        'placa',
        'motivo',
        'expira_en'
    ];

    protected $casts = [
        'expira_en' => 'datetime'
    ];

    // Relaciones
    public function tarjetaRfid()
    {
        return $this->belongsTo(TarjetaRfid::class, 'tarjeta_rfid_id');
    }

    // Verificar si está activa en lista negra
    public function estaActiva()
    {
        return !$this->expira_en || $this->expira_en->isFuture();
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expira_en')
                ->orWhere('expira_en', '>', now());
        });
    }

    public function scopePorPlaca($query, $placa)
    {
        return $query->where('placa', $placa);
    }
}
