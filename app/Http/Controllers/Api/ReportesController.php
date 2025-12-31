<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportesController extends Controller
{
    public function __construct(private ReportesService $reportesService)
    {
    }

    /**
     * US-050: Dashboard Principal
     * GET /api/dashboard
     */
    public function dashboard(): JsonResponse
    {
        $kpis = $this->reportesService->obtenerKPIs();
        $topProductos = $this->reportesService->productosTopVentas(5);
        $topClientes = $this->reportesService->clientesTopActivos(5);

        return response()->json([
            'exito' => true,
            'kpis' => $kpis,
            'top_productos' => $topProductos,
            'top_clientes' => $topClientes,
        ], 200);
    }

    /**
     * US-051: Reporte Diario
     * GET /api/reportes/diario
     */
    public function reporteDiario(): JsonResponse
    {
        $datos = $this->reportesService->reporteDiario();

        return response()->json([
            'exito' => true,
            'tipo' => 'diario',
            'periodo' => 'Últimos 7 días',
            'datos' => $datos,
        ], 200);
    }

    /**
     * US-052: Reporte Semanal
     * GET /api/reportes/semanal
     */
    public function reporteSemanal(): JsonResponse
    {
        $datos = $this->reportesService->reporteSemanal();

        return response()->json([
            'exito' => true,
            'tipo' => 'semanal',
            'periodo' => 'Últimas 8 semanas',
            'datos' => $datos,
        ], 200);
    }

    /**
     * US-053: Reporte Mensual
     * GET /api/reportes/mensual
     */
    public function reporteMensual(): JsonResponse
    {
        $datos = $this->reportesService->reporteMensual();

        return response()->json([
            'exito' => true,
            'tipo' => 'mensual',
            'periodo' => 'Últimos 12 meses',
            'datos' => $datos,
        ], 200);
    }

    /**
     * US-054: Exportar a Excel/CSV
     * POST /api/reportes/exportar
     */
    public function exportar(Request $request): StreamedResponse
    {
        $tipo = $request->input('tipo', 'mensual'); // diario, semanal, mensual
        $formato = $request->input('formato', 'csv'); // csv, excel (por ahora solo CSV)

        $csv = $this->reportesService->generarCSV($tipo);

        $nombreArchivo = 'reporte_' . $tipo . '_' . now()->format('Y-m-d_His') . '.csv';

        $response = new StreamedResponse(function () use ($csv) {
            echo $csv;
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $nombreArchivo . '"');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');

        return $response;
    }

    /**
     * Obtener productos más vendidos
     * GET /api/reportes/productos-top
     */
    public function productosTop(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $datos = $this->reportesService->productosTopVentas($limit);

        return response()->json([
            'exito' => true,
            'total' => count($datos),
            'datos' => $datos,
        ], 200);
    }

    /**
     * Obtener clientes más activos
     * GET /api/reportes/clientes-top
     */
    public function clientesTop(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $datos = $this->reportesService->clientesTopActivos($limit);

        return response()->json([
            'exito' => true,
            'total' => count($datos),
            'datos' => $datos,
        ], 200);
    }
}
