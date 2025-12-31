<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\DetallePedido;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportesService
{
    /**
     * Obtener KPIs del dashboard principal
     */
    public function obtenerKPIs(): array
    {
        $hoy = Carbon::now()->startOfDay();
        $mesActual = Carbon::now()->startOfMonth();

        // Pedidos de hoy
        $pedidosHoy = Pedido::whereDate('created_at', $hoy)->count();
        $ingresoHoy = Pedido::whereDate('created_at', $hoy)->sum('total');

        // Pedidos del mes
        $pedidosMes = Pedido::whereBetween('created_at', [$mesActual, now()])->count();
        $ingresoMes = Pedido::whereBetween('created_at', [$mesActual, now()])->sum('total');

        // Clientes nuevos (últimos 30 días)
        $clientesNuevos = Cliente::where('created_at', '>=', now()->subDays(30))->count();

        // Pedidos pendientes
        $pedidosPendientes = Pedido::where('estado', Pedido::ESTADO_PENDIENTE)->count();

        // Confirmados vs Cancelados
        $confirmados = Pedido::where('estado', Pedido::ESTADO_CONFIRMADO)->count();
        $cancelados = Pedido::where('estado', Pedido::ESTADO_CANCELADO)->count();

        // Tasa de conversión (confirmados / total)
        $totalPedidos = Pedido::count();
        $tasaConversion = $totalPedidos > 0 ? ($confirmados / $totalPedidos) * 100 : 0;

        // Producto más vendido
        $productoMasVendido = DetallePedido::select('producto_id')
            ->selectRaw('SUM(cantidad) as total_cantidad')
            ->groupBy('producto_id')
            ->orderByDesc('total_cantidad')
            ->first();

        $productoInfo = null;
        if ($productoMasVendido) {
            $productoInfo = Producto::find($productoMasVendido->producto_id);
        }

        return [
            'pedidos_hoy' => $pedidosHoy,
            'ingreso_hoy' => (float) $ingresoHoy,
            'pedidos_mes' => $pedidosMes,
            'ingreso_mes' => (float) $ingresoMes,
            'clientes_nuevos' => $clientesNuevos,
            'pedidos_pendientes' => $pedidosPendientes,
            'pedidos_confirmados' => $confirmados,
            'pedidos_cancelados' => $cancelados,
            'tasa_conversion' => round($tasaConversion, 2),
            'producto_mas_vendido' => $productoInfo ? [
                'id' => $productoInfo->id,
                'nombre' => $productoInfo->nombre,
                'cantidad_vendida' => $productoMasVendido->total_cantidad,
            ] : null,
        ];
    }

    /**
     * Reporte diario - últimos 7 días
     */
    public function reporteDiario(): array
    {
        $datos = [];

        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::now()->subDays($i)->startOfDay();
            $fechaFin = Carbon::now()->subDays($i)->endOfDay();

            $pedidos = Pedido::whereBetween('created_at', [$fecha, $fechaFin]);

            $datos[] = [
                'fecha' => $fecha->format('Y-m-d'),
                'dia_semana' => $fecha->locale('es')->dayName,
                'pedidos_totales' => $pedidos->count(),
                'ingresos' => (float) $pedidos->sum('total'),
                'confirmados' => $pedidos->clone()->where('estado', Pedido::ESTADO_CONFIRMADO)->count(),
                'cancelados' => $pedidos->clone()->where('estado', Pedido::ESTADO_CANCELADO)->count(),
                'pendientes' => $pedidos->clone()->where('estado', Pedido::ESTADO_PENDIENTE)->count(),
            ];
        }

        return $datos;
    }

    /**
     * Reporte semanal - últimas 8 semanas
     */
    public function reporteSemanal(): array
    {
        $datos = [];

        for ($i = 7; $i >= 0; $i--) {
            $fecha = Carbon::now()->subWeeks($i);
            $inicioSemana = $fecha->clone()->startOfWeek();
            $finSemana = $fecha->clone()->endOfWeek();

            $pedidos = Pedido::whereBetween('created_at', [$inicioSemana, $finSemana]);

            $datos[] = [
                'semana' => 'Semana ' . $fecha->weekOfYear,
                'periodo' => $inicioSemana->format('Y-m-d') . ' al ' . $finSemana->format('Y-m-d'),
                'pedidos_totales' => $pedidos->count(),
                'ingresos' => (float) $pedidos->sum('total'),
                'confirmados' => $pedidos->clone()->where('estado', Pedido::ESTADO_CONFIRMADO)->count(),
                'cancelados' => $pedidos->clone()->where('estado', Pedido::ESTADO_CANCELADO)->count(),
                'ticket_promedio' => $pedidos->count() > 0 ? (float) round($pedidos->sum('total') / $pedidos->count(), 2) : 0,
            ];
        }

        return $datos;
    }

    /**
     * Reporte mensual - últimos 12 meses
     */
    public function reporteMensual(): array
    {
        $datos = [];

        for ($i = 11; $i >= 0; $i--) {
            $fecha = Carbon::now()->subMonths($i);
            $inicioMes = $fecha->clone()->startOfMonth();
            $finMes = $fecha->clone()->endOfMonth();

            $pedidos = Pedido::whereBetween('created_at', [$inicioMes, $finMes]);

            $datos[] = [
                'mes' => $fecha->locale('es')->monthName,
                'mes_año' => $fecha->format('Y-m'),
                'pedidos_totales' => $pedidos->count(),
                'ingresos' => (float) $pedidos->sum('total'),
                'confirmados' => $pedidos->clone()->where('estado', Pedido::ESTADO_CONFIRMADO)->count(),
                'cancelados' => $pedidos->clone()->where('estado', Pedido::ESTADO_CANCELADO)->count(),
                'clientes_unicos' => $pedidos->clone()->distinct('cliente_id')->count(),
                'ticket_promedio' => $pedidos->count() > 0 ? (float) round($pedidos->sum('total') / $pedidos->count(), 2) : 0,
            ];
        }

        return $datos;
    }

    /**
     * Generar datos para exportación
     */
    public function generarDatosExportacion(string $tipo = 'mensual'): array
    {
        $datos = match ($tipo) {
            'diario' => $this->reporteDiario(),
            'semanal' => $this->reporteSemanal(),
            'mensual' => $this->reporteMensual(),
            default => $this->reporteMensual(),
        };

        return $datos;
    }

    /**
     * Generar CSV
     */
    public function generarCSV(string $tipo = 'mensual'): string
    {
        $datos = $this->generarDatosExportacion($tipo);

        if (empty($datos)) {
            return '';
        }

        $csv = '';
        $headers = array_keys($datos[0]);

        // Encabezados
        $csv .= implode(',', $headers) . "\n";

        // Datos
        foreach ($datos as $fila) {
            $valores = [];
            foreach ($headers as $header) {
                $valor = $fila[$header] ?? '';
                // Escapar comillas y envolver en comillas si contiene comas
                $valor = str_replace('"', '""', $valor);
                if (strpos($valor, ',') !== false) {
                    $valor = '"' . $valor . '"';
                }
                $valores[] = $valor;
            }
            $csv .= implode(',', $valores) . "\n";
        }

        return $csv;
    }

    /**
     * Obtener detalles de productos más vendidos
     */
    public function productosTopVentas(int $limit = 10): array
    {
        return DetallePedido::select('producto_id')
            ->selectRaw('SUM(cantidad) as cantidad_total')
            ->selectRaw('SUM(cantidad * precio_unitario) as ingresos_total')
            ->with('producto')
            ->groupBy('producto_id')
            ->orderByDesc('cantidad_total')
            ->take($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'producto_id' => $item->producto_id,
                    'producto_nombre' => $item->producto->nombre ?? 'N/A',
                    'cantidad_vendida' => $item->cantidad_total,
                    'ingresos' => (float) round($item->ingresos_total, 2),
                ];
            })
            ->toArray();
    }

    /**
     * Obtener clientes más activos
     */
    public function clientesTopActivos(int $limit = 10): array
    {
        return Cliente::withCount('pedidos')
            ->withSum('pedidos', 'total')
            ->orderByDesc('pedidos_count')
            ->take($limit)
            ->get()
            ->map(function ($cliente) {
                return [
                    'cliente_id' => $cliente->id,
                    'nombre' => $cliente->nombre,
                    'email' => $cliente->email,
                    'pedidos_totales' => $cliente->pedidos_count,
                    'gasto_total' => (float) round($cliente->pedidos_sum_total ?? 0, 2),
                ];
            })
            ->toArray();
    }
}
