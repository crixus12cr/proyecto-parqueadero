<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incidencia extends Model
{
    use HasFactory;

    protected $table = 'incidencias';
    
    protected $fillable = [
        'registro_acceso_id',
        'reportado_por',
        'descripcion',
        'resuelto',
        'resuelto_en',
        'notas_resolucion'
    ];

    protected $casts = [
        'resuelto' => 'boolean',
        'resuelto_en' => 'datetime'
    ];

    // Relaciones
    public function registroAcceso()
    {
        return $this->belongsTo(RegistroAcceso::class, 'registro_acceso_id');
    }

    public function reportadoPor()
    {
        return $this->belongsTo(User::class, 'reportado_por');
    }

    // Scopes
    public function scopeSinResolver($query)
    {
        return $query->where('resuelto', false);
    }

    public function scopeResueltas($query)
    {
        return $query->where('resuelto', true);
    }
}
