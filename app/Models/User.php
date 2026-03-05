<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tipo_usuario_id',
        'numero_documento',
        'telefono',
        'foto',
        'estado'
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function getAvatarAttribute()
    {
        if ($this->foto) {
            return Storage::url($this->foto);
        }
        
        return null;
    }

    // Relaciones
    public function tipoUsuario()
    {
        return $this->belongsTo(TipoUsuario::class, 'tipo_usuario_id');
    }

    public function tarjetasRfid()
    {
        return $this->hasMany(TarjetaRfid::class, 'user_id');
    }

    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'user_id');
    }

    public function registrosAcceso()
    {
        return $this->hasMany(RegistroAcceso::class, 'user_id');
    }

    public function incidenciasReportadas()
    {
        return $this->hasMany(Incidencia::class, 'reportado_por');
    }

    public function visitasComoAnfitrion()
    {
        return $this->hasMany(Visitante::class, 'user_id_anfitrion');
    }

    public function autorizacionesVisitantes()
    {
        return $this->hasMany(Visitante::class, 'autorizado_por');
    }

    public function roles()
    {
        return $this->belongsToMany(
            Rol::class,          // Modelo relacionado
            'rol_user',          // Tabla pivote
            'user_id',           // FK de este modelo en la pivote
            'rol_id'             // FK del otro modelo en la pivote
        )->withTimestamps();
    }

    // Helper: verificar si tiene un rol específico
    public function tieneRol($rolNombre)
    {
        return $this->roles()->where('nombre', $rolNombre)->exists();
    }

    // Helper: verificar si tiene alguno de los roles
    public function tieneAlgunRol(array $roles)
    {
        return $this->roles()->whereIn('nombre', $roles)->exists();
    }

    // Helper: obtener nombres de roles como array
    public function getNombresRolesAttribute()
    {
        return $this->roles->pluck('nombre')->toArray();
    }

    // Helper: verificar si es administrador (cualquier rol admin)
    public function esAdministrador()
    {
        return $this->tieneAlgunRol(['Super Administrador', 'Administrador']);
    }

    // Obtener tarjeta activa actual
    public function tarjetaActiva()
    {
        return $this->hasOne(TarjetaRfid::class, 'user_id')
            ->where('estado', 'activa')
            ->where(function ($query) {
                $query->whereNull('fecha_vencimiento')
                    ->orWhere('fecha_vencimiento', '>', now());
            });
    }

    // Obtener vehículo principal
    public function vehiculoPrincipal()
    {
        return $this->hasOne(Vehiculo::class, 'user_id')
            ->where('es_principal', true)
            ->where('estado', 'activo');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopePorTipo($query, $tipoUsuarioId)
    {
        return $query->where('tipo_usuario_id', $tipoUsuarioId);
    }

    public function scopePorRol($query, $rolId)
    {
        return $query->where('rol_id', $rolId);
    }
}
