<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoUsuario extends Model
{
    use HasFactory;

    protected $table = 'tipo_usuarios';
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'horas_maximas_estadia',
        'prioridad_acceso',
        'estado'
    ];

    protected $casts = [
        'horas_maximas_estadia' => 'integer',
        'prioridad_acceso' => 'integer'
    ];

    // Relación con usuarios
    public function usuarios()
    {
        return $this->hasMany(User::class, 'tipo_usuario_id');
    }

    // Scope para tipos activos
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }
}
