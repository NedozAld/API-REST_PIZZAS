<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Cliente extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'clientes';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'direccion',
        'password',
        'estado',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Relación con pedidos
    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'cliente_id');
    }

    // Verificar si está activo
    public function estaActivo(): bool
    {
        return $this->estado === 'activo';
    }
}
