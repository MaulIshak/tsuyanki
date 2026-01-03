<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$indexes = \Illuminate\Support\Facades\DB::select("select * from pg_indexes where tablename = 'tags'");
foreach ($indexes as $index) {
    echo $index->indexname . ': ' . $index->indexdef . "\n";
}
