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
        return $this->belongsToMany(
            User::class,         // Modelo relacionado
            'rol_user',          // Tabla pivote
            'rol_id',            // FK de este modelo en la pivote
            'user_id'            // FK del otro modelo en la pivote
        )->withTimestamps();
    }

    // Scope para roles activos
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }
}
