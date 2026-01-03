<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$deck = \App\Models\Deck::first(); // Grab any deck
if (!$deck) {
    echo "No deck found\n";
    exit;
}

echo "Deck ID: " . $deck->id . "\n";
echo "Initial Tag Count: " . \App\Models\Tag::count() . "\n";

$tagName = "TestTag_" . time();
try {
    $tag = \App\Models\Tag::firstOrCreate([
        'name' => $tagName,
        'deck_id' => $deck->id
    ]);
    echo "Created Tag: {$tag->name}, DeckID: {$tag->deck_id}\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Final Tag Count: " . \App\Models\Tag::count() . "\n";
