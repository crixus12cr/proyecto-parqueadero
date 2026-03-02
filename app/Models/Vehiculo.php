<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    use HasFactory;

    protected $table = 'vehiculos';
    
    protected $fillable = [
        'user_id',
        'placa',
        'marca',
        'modelo',
        'color',
        'tipo_vehiculo',
        'es_principal',
        'estado'
    ];

    protected $casts = [
        'es_principal' => 'boolean'
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function registrosAcceso()
    {
        return $this->hasMany(RegistroAcceso::class, 'vehiculo_id');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopePorPlaca($query, $placa)
    {
        return $query->where('placa', 'LIKE', "%{$placa}%");
    }

    public function scopePrincipales($query)
    {
        return $query->where('es_principal', true);
    }
}
