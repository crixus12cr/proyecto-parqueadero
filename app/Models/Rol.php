<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'roles';
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'estado'
    ];

    protected $attributes = [
        'estado' => 'activo'
    ];

    // Relación con usuarios
    public function usuarios()
    {
        return $this->hasMany(User::class, 'rol_id');
    }

    // Scope para roles activos
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }
}
