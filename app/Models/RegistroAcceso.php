<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroAcceso extends Model
{
    use HasFactory;

    protected $table = 'registro_accesos';
    
    protected $fillable = [
        'tarjeta_rfid_id',
        'user_id',
        'vehiculo_id',
        'tipo_acceso',
        'metodo_acceso',
        'fecha_hora',
        'operador_id',
        'estado',
        'motivo'
    ];

    protected $casts = [
        'fecha_hora' => 'datetime'
    ];

    // Relaciones
    public function tarjetaRfid()
    {
        return $this->belongsTo(TarjetaRfid::class, 'tarjeta_rfid_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }

    public function operador()
    {
        return $this->belongsTo(User::class, 'operador_id');
    }

    public function incidencias()
    {
        return $this->hasMany(Incidencia::class, 'registro_acceso_id');
    }

    // Scopes
    public function scopeEntradas($query)
    {
        return $query->where('tipo_acceso', 'entrada');
    }

    public function scopeSalidas($query)
    {
        return $query->where('tipo_acceso', 'salida');
    }

    public function scopeDelDia($query, $fecha = null)
    {
        $fecha = $fecha ?? now();
        return $query->whereDate('fecha_hora', $fecha);
    }

    public function scopeAprobados($query)
    {
        return $query->where('estado', 'aprobado');
    }
}
