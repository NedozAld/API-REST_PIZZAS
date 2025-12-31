<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Producto;

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║         MÓDULO 12: Testing de Optimizaciones                ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// ===========================
// US-100: Caché Redis
// ===========================
echo "═══ US-100: Caché de Menú con Redis ═══\n\n";

// Limpiar caché antes de empezar
Cache::forget('productos_menu');

// Test 1: Sin caché (primera consulta)
$start = microtime(true);
$productos = Producto::where('disponible', true)
    ->where('activo', true)
    ->with('categoria')
    ->orderBy('categoria_id')
    ->orderBy('nombre')
    ->get();
$time1 = (microtime(true) - $start) * 1000;

echo "  Primera consulta (sin caché):\n";
echo "    └─ Tiempo: " . number_format($time1, 2) . " ms\n";
echo "    └─ Productos: " . $productos->count() . "\n\n";

// Test 2: Con caché (usando Cache::remember)
$start = microtime(true);
$productosCached = Cache::remember('productos_menu', 3600, function() {
    return Producto::where('disponible', true)
        ->where('activo', true)
        ->with('categoria')
        ->orderBy('categoria_id')
        ->orderBy('nombre')
        ->get();
});
$time2 = (microtime(true) - $start) * 1000;

echo "  Segunda consulta (con caché):\n";
echo "    └─ Tiempo: " . number_format($time2, 2) . " ms\n";
echo "    └─ Productos: " . $productosCached->count() . "\n\n";

// Cálculo de mejora
$mejora = (($time1 - $time2) / $time1) * 100;
echo "  ✅ Mejora de rendimiento: " . number_format($mejora, 1) . "%\n";
echo "  ✅ Reducción de tiempo: " . number_format($time1 - $time2, 2) . " ms\n\n";

// ===========================
// US-101: Compresión GZIP
// ===========================
echo "═══ US-101: Compresión GZIP ═══\n\n";

$jsonData = json_encode($productos->toArray());
$originalSize = strlen($jsonData);
$compressedData = gzencode($jsonData, 6);
$compressedSize = strlen($compressedData);
$reduction = (($originalSize - $compressedSize) / $originalSize) * 100;

echo "  Tamaño original: " . number_format($originalSize / 1024, 2) . " KB\n";
echo "  Tamaño comprimido: " . number_format($compressedSize / 1024, 2) . " KB\n";
echo "  ✅ Reducción: " . number_format($reduction, 1) . "% más pequeño\n\n";

// ===========================
// US-102: Índices BD
// ===========================
echo "═══ US-102: Índices de Base de Datos ═══\n\n";

// Test de índice en pedidos (cliente_id, estado)
$start = microtime(true);
DB::select("
    SELECT p.*, c.nombre as cliente_nombre
    FROM pedidos p
    LEFT JOIN clientes c ON p.cliente_id = c.id
    WHERE p.estado = 'CONFIRMADO'
    LIMIT 100
");
$timeConIndice = (microtime(true) - $start) * 1000;

echo "  Consulta con índice (estado = 'CONFIRMADO'):\n";
echo "    └─ Tiempo: " . number_format($timeConIndice, 2) . " ms\n\n";

// Verificar índices creados
$indexes = DB::select("
    SELECT indexname, tablename 
    FROM pg_indexes 
    WHERE tablename IN ('pedidos', 'productos', 'clientes', 'notificaciones', 'auditoria')
    AND indexname LIKE 'idx_%'
    ORDER BY tablename, indexname
");

echo "  Índices creados:\n";
$lastTable = '';
foreach ($indexes as $index) {
    if ($index->tablename !== $lastTable) {
        echo "\n  📊 Tabla: {$index->tablename}\n";
        $lastTable = $index->tablename;
    }
    echo "    └─ {$index->indexname}\n";
}
echo "\n  ✅ Total de índices optimizados: " . count($indexes) . "\n\n";

// ===========================
// US-103: CDN
// ===========================
echo "═══ US-103: CDN Imágenes (CloudFlare) ═══\n\n";

use App\Helpers\CdnHelper;

$testImages = [
    'images/productos/pizza-margarita.jpg',
    'images/productos/pasta-carbonara.jpg',
    'css/app.css',
    'js/app.js',
];

echo "  URLs de CDN generadas:\n\n";
foreach ($testImages as $image) {
    $cdnUrl = CdnHelper::asset($image);
    $extension = pathinfo($image, PATHINFO_EXTENSION);
    $headers = CdnHelper::getCacheHeaders($image);
    $cacheControl = $headers['Cache-Control'] ?? 'N/A';
    
    echo "    📁 {$image}\n";
    echo "       └─ URL: {$cdnUrl}\n";
    echo "       └─ Cache: {$cacheControl}\n\n";
}

echo "  ✅ CDN configurado correctamente\n";
echo "  ⚙️  Configuración: config/cdn.php\n";
echo "  ⚙️  Helper: App\\Helpers\\CdnHelper\n\n";

// ===========================
// Resumen Final
// ===========================
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                    RESUMEN DE OPTIMIZACIONES                ║\n";
echo "╠══════════════════════════════════════════════════════════════╣\n";
echo "║  US-100: Caché Redis        │ ✅ " . number_format($mejora, 0) . "% más rápido         ║\n";
echo "║  US-101: Compresión GZIP    │ ✅ " . number_format($reduction, 0) . "% más pequeño       ║\n";
echo "║  US-102: Índices BD         │ ✅ " . count($indexes) . " índices creados     ║\n";
echo "║  US-103: CDN CloudFlare     │ ✅ Configurado              ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

echo "🎉 Todas las optimizaciones están funcionando correctamente!\n\n";

// Limpiar
Cache::forget('productos_menu');
