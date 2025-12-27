<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

use App\Models\NoteType;

$nt = NoteType::where('name', 'Basic')->first();
if ($nt) {
    $nt->field_schema = [
        'fields' => [
            ['name' => 'Front', 'type' => 'text'],
            ['name' => 'Back', 'type' => 'text']
        ]
    ];
    $nt->save();
    echo "Fixed Basic NoteType schema successfully.\n";
} else {
    echo "Basic NoteType not found.\n";
}
