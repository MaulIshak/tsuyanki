<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$note = App\Models\Note::whereHas('media')->with('media')->first();
if ($note) {
    echo json_encode($note->media->toArray(), JSON_PRETTY_PRINT);
} else {
    echo "No note with media found";
}
