<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id';
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'email',
        'password_hash',
        'telefono',
        'rol_id',
        'estado',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'fecha_creacion' => 'datetime',
            'ultima_conexion' => 'datetime',
        ];
    }

    /**
     * Obtener el nombre de la columna de la contraseña.
     */
    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    /**
     * Relación con Rol
     */
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    /**
     * Relación con Sesiones
     */
    public function sesiones()
    {
        return $this->hasMany(Sesion::class, 'usuario_id');
    }

    /**
     * Relación con Intentos Fallidos
     */
    public function intentosFallidos()
    {
        return $this->hasMany(IntentoFallido::class, 'usuario_id');
    }

    /**
     * Relación con Auditoría
     */
    public function auditoria()
    {
        return $this->hasMany(Auditoria::class, 'usuario_id');
    }

    /**
     * Verificar si el usuario está activo
     */
    public function estaActivo(): bool
    {
        return $this->estado === 'activo';
    }

    /**
     * Verificar si el usuario está bloqueado
     */
    public function estaBloqueado(): bool
    {
        return $this->estado === 'bloqueado';
    }

    /**
     * Obtener el nombre de la ruta del modelo
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
