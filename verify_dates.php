<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$elements = App\Models\Elemento::take(5)->get();
foreach ($elements as $e) {
    echo "LIA: {$e->nro_lia}, Adq: " . ($e->fecha_adquisicion ? $e->fecha_adquisicion->format('Y-m-d') : 'NULL') . ", Venc: " . ($e->fecha_vencimiento_garantia ? $e->fecha_vencimiento_garantia->format('Y-m-d') : 'NULL') . "\n";
}

$active = App\Models\Elemento::where('fecha_vencimiento_garantia', '>', now())->count();
$expired = App\Models\Elemento::where('fecha_vencimiento_garantia', '<=', now())->count();

echo "Active Warranty: $active\n";
echo "Expired Warranty: $expired\n";
