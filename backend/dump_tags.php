<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tags = \App\Models\Tag::all();
foreach ($tags as $tag) {
    echo "ID: {$tag->id}, Name: {$tag->name}, DeckID: " . ($tag->deck_id ?? 'NULL') . "\n";
}
