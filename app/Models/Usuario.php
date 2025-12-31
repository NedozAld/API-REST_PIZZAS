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
        'dos_fa_habilitado',
        'dos_fa_secret',
        'dos_fa_backup_codes',
        'intentos_fallidos',
        'bloqueado_hasta',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
        'dos_fa_secret',
        'dos_fa_backup_codes',
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
            'bloqueado_hasta' => 'datetime',
            'dos_fa_habilitado' => 'boolean',
            'dos_fa_backup_codes' => 'json',
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
     * Verificar si tiene 2FA habilitado
     */
    public function tieneDosFa(): bool
    {
        return $this->dos_fa_habilitado === true;
    }

    /**
     * US-091: Verificar si el usuario está bloqueado por intentos fallidos
     * Nota: Se renombró el método antiguo estaBloqueado() que verificaba estado
     */
    public function estaBloqueadoPorFallidos(): bool
    {
        if (!$this->bloqueado_hasta) {
            return false;
        }
        
        if ($this->bloqueado_hasta->isPast()) {
            // Desbloquear si ya pasó la hora
            $this->update(['bloqueado_hasta' => null, 'intentos_fallidos' => 0]);
            return false;
        }
        
        return true;
    }

    /**
     * US-091: Registrar intento fallido
     */
    public function registrarIntentoFallido(): void
    {
        $intentos = $this->intentos_fallidos + 1;
        
        if ($intentos >= 3) {
            // Bloquear durante 1 hora
            $this->update([
                'intentos_fallidos' => $intentos,
                'bloqueado_hasta' => now()->addHour()
            ]);
        } else {
            $this->increment('intentos_fallidos');
        }
    }

    /**
     * US-091: Limpiar intentos fallidos (login exitoso)
     */
    public function limpiarIntentosFallidos(): void
    {
        $this->update([
            'intentos_fallidos' => 0,
            'bloqueado_hasta' => null
        ]);
    }

    /**
     * Obtener el nombre de la ruta del modelo
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
