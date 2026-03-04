<?php
// app/Models/Respaldo.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Respaldo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'archivo',
        'tamano',
        'tipo',
        'estado',
        'fecha_generacion',
        'usuario_id',
        'observaciones'
    ];

    protected $casts = [
        'fecha_generacion' => 'datetime',
        'tamano' => 'integer'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function getTamanoFormateadoAttribute()
    {
        $bytes = $this->tamano;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}