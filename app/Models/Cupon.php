<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Cupon extends Model
{
    use HasFactory;

    protected $table = 'cupones';

    protected $fillable = [
        'codigo',
        'descripcion',
        'tipo_descuento',
        'valor_descuento',
        'descuento_maximo',
        'compra_minima',
        'usos_maximos',
        'usos_actuales',
        'fecha_inicio',
        'fecha_fin',
        'activo'
    ];

    protected $casts = [
        'valor_descuento' => 'decimal:2',
        'descuento_maximo' => 'decimal:2',
        'compra_minima' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'boolean',
        'usos_maximos' => 'integer',
        'usos_actuales' => 'integer'
    ];

    /**
     * Relación con clientes que han usado este cupón
     */
    public function clientes()
    {
        return $this->belongsToMany(Cliente::class, 'cupones_clientes')
                    ->withPivot('fecha_uso')
                    ->withTimestamps();
    }

    /**
     * Verifica si el cupón es válido
     * @return array ['valido' => bool, 'mensaje' => string]
     */
    public function esValido($montoCompra = 0)
    {
        // Verificar si está activo
        if (!$this->activo) {
            return [
                'valido' => false,
                'mensaje' => 'El cupón no está activo'
            ];
        }

        // Verificar fechas
        $hoy = Carbon::today();
        if ($hoy->lt($this->fecha_inicio)) {
            return [
                'valido' => false,
                'mensaje' => 'El cupón aún no es válido'
            ];
        }

        if ($hoy->gt($this->fecha_fin)) {
            return [
                'valido' => false,
                'mensaje' => 'El cupón ha expirado'
            ];
        }

        // Verificar usos máximos
        if ($this->usos_maximos !== null && $this->usos_actuales >= $this->usos_maximos) {
            return [
                'valido' => false,
                'mensaje' => 'El cupón ha alcanzado su límite de usos'
            ];
        }

        // Verificar monto mínimo de compra
        if ($this->compra_minima && $montoCompra < $this->compra_minima) {
            return [
                'valido' => false,
                'mensaje' => "La compra debe ser de al menos $" . number_format($this->compra_minima, 2)
            ];
        }

        return [
            'valido' => true,
            'mensaje' => 'Cupón válido'
        ];
    }

    /**
     * Calcula el descuento a aplicar
     * @param float $montoCompra
     * @return float
     */
    public function calcularDescuento($montoCompra)
    {
        if ($this->tipo_descuento === 'porcentaje') {
            $descuento = ($montoCompra * $this->valor_descuento) / 100;
            
            // Aplicar descuento máximo si está configurado
            if ($this->descuento_maximo && $descuento > $this->descuento_maximo) {
                $descuento = $this->descuento_maximo;
            }
            
            return round($descuento, 2);
        }

        // Monto fijo
        // No puede exceder el monto de la compra
        return min($this->valor_descuento, $montoCompra);
    }

    /**
     * Incrementa el contador de usos y registra el cliente
     */
    public function registrarUso($clienteId)
    {
        $this->increment('usos_actuales');
        
        // Registrar en la tabla pivot
        $this->clientes()->attach($clienteId, [
            'fecha_uso' => now()
        ]);
    }

    /**
     * Verifica si un cliente ya usó este cupón
     */
    public function fueUsadoPor($clienteId)
    {
        return $this->clientes()->where('cliente_id', $clienteId)->exists();
    }

    /**
     * Scope para cupones activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para cupones vigentes
     */
    public function scopeVigentes($query)
    {
        $hoy = Carbon::today();
        return $query->where('fecha_inicio', '<=', $hoy)
                     ->where('fecha_fin', '>=', $hoy);
    }

    /**
     * Scope para cupones disponibles
     */
    public function scopeDisponibles($query)
    {
        return $query->activos()
                     ->vigentes()
                     ->whereColumn('usos_actuales', '<', 'usos_maximos');
    }

    /**
     * Obtiene información formateada del cupón
     */
    public function getInformacionFormateadaAttribute()
    {
        $info = "Cupón {$this->codigo}: ";
        
        if ($this->tipo_descuento === 'porcentaje') {
            $info .= "{$this->valor_descuento}% de descuento";
        } else {
            $info .= "$" . number_format($this->valor_descuento, 2) . " de descuento";
        }

        if ($this->compra_minima) {
            $info .= " (Compra mínima: $" . number_format($this->compra_minima, 2) . ")";
        }

        if ($this->descuento_maximo && $this->tipo_descuento === 'porcentaje') {
            $info .= " (Máx descuento: $" . number_format($this->descuento_maximo, 2) . ")";
        }

        return $info;
    }
}
