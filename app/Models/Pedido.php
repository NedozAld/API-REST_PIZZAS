<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'numero_pedido',
        'cliente_id',
        'subtotal',
        'impuesto',
        'costo_entrega',
        'monto_descuento',
        'total',
        'estado',
        'notas',
        'fecha_ticket_enviado',
        'fecha_confirmacion',
        'fecha_confirmacion_whatsapp',
        'fecha_entrega',
        'metodo_confirmacion',
        'whatsapp_message_sid',
        'motivo_cancelacion',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'impuesto' => 'decimal:2',
            'costo_entrega' => 'decimal:2',
            'monto_descuento' => 'decimal:2',
            'total' => 'decimal:2',
            'fecha_ticket_enviado' => 'datetime',
            'fecha_confirmacion' => 'datetime',
            'fecha_confirmacion_whatsapp' => 'datetime',
            'fecha_entrega' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Estados posibles
    public const ESTADO_PENDIENTE = 'PENDIENTE';
    public const ESTADO_TICKET_ENVIADO = 'TICKET_ENVIADO';
    public const ESTADO_CONFIRMADO = 'CONFIRMADO';
    public const ESTADO_EN_PREPARACION = 'EN_PREPARACION';
    public const ESTADO_LISTO = 'LISTO';
    public const ESTADO_EN_ENTREGA = 'EN_ENTREGA';
    public const ESTADO_ENTREGADO = 'ENTREGADO';
    public const ESTADO_CANCELADO = 'CANCELADO';

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'pedido_id');
    }

    // Métodos de utilidad
    public function esPendiente(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE;
    }

    public function esConfirmado(): bool
    {
        return $this->estado === self::ESTADO_CONFIRMADO;
    }

    public function puedeSerEditado(): bool
    {
        return in_array($this->estado, [self::ESTADO_PENDIENTE, self::ESTADO_TICKET_ENVIADO]);
    }

    public function puedeSerCancelado(): bool
    {
        return !in_array($this->estado, [self::ESTADO_ENTREGADO, self::ESTADO_CANCELADO]);
    }

    // Generar número de pedido único
    public static function generarNumeroPedido(): string
    {
        $fecha = now()->format('Ymd');
        $ultimo = self::whereDate('created_at', today())->count() + 1;
        return 'PED-' . $fecha . '-' . str_pad($ultimo, 4, '0', STR_PAD_LEFT);
    }
}
