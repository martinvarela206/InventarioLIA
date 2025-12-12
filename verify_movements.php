<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = App\Models\Movimiento::count();
echo "Total Movimientos: $count\n";

$elementos = App\Models\Elemento::with('movimientos')->take(5)->get();
foreach ($elementos as $e) {
    echo "LIA: {$e->nro_lia}, Movimientos: " . $e->movimientos->count() . "\n";
    if ($e->movimientos->count() > 0) {
        echo "  - Fecha: " . $e->movimientos->first()->fecha->format('Y-m-d') . ", Ubic: " . $e->movimientos->first()->ubicacion . "\n";
    }
}
