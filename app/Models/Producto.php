<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio_base',
        'categoria_id',
        'stock_disponible',
        'stock_minimo',
        'disponible',
        'imagen_url',
        'costo',
        'descuento_porcentaje',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'precio_base' => 'decimal:2',
            'costo' => 'decimal:2',
            'descuento_porcentaje' => 'decimal:2',
            'disponible' => 'boolean',
            'activo' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /**
     * Scope para productos con stock bajo (US-015)
     */
    public function scopeStockBajo($query)
    {
        return $query->whereColumn('stock_disponible', '<', 'stock_minimo');
    }

    /**
     * Scope para productos disponibles
     */
    public function scopeDisponibles($query)
    {
        return $query->where('disponible', true)->where('activo', true);
    }

    /**
     * Verificar si el producto tiene stock crítico (0)
     */
    public function tieneStockCritico(): bool
    {
        return $this->stock_disponible == 0;
    }

    /**
     * Verificar si el producto tiene stock bajo
     */
    public function tieneStockBajo(): bool
    {
        return $this->stock_disponible < $this->stock_minimo;
    }

    /**
     * Obtener nivel de alerta de stock
     */
    public function getNivelAlertaAttribute(): string
    {
        if ($this->stock_disponible == 0) {
            return 'CRITICO';
        } elseif ($this->stock_disponible < $this->stock_minimo) {
            return 'BAJO';
        }
        return 'NORMAL';
    }

    /**
     * US-082: Calcular precio final con descuento por producto
     */
    public function getPrecioConDescuentoAttribute(): float
    {
        if ($this->descuento_porcentaje > 0) {
            return round($this->precio_base - ($this->precio_base * $this->descuento_porcentaje / 100), 2);
        }
        return (float) $this->precio_base;
    }

    /**
     * Calcular monto de descuento por producto
     */
    public function getMontoDescuentoProductoAttribute(): float
    {
        if ($this->descuento_porcentaje > 0) {
            return round($this->precio_base * $this->descuento_porcentaje / 100, 2);
        }
        return 0;
    }

    /**
     * Verificar si el producto tiene descuento
     */
    public function tieneDescuentoProducto(): bool
    {
        return $this->descuento_porcentaje > 0;
    }
}
