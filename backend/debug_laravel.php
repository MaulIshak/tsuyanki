<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$n = App\Models\Note::get()->first(function ($note) {
    return str_contains(json_encode($note->fields), '2_i.mp3');
});

if ($n) {
    echo "FIELDS_FOUND: " . json_encode($n->fields) . "\n";
    $card = $n->cards->first();
    if ($card) {
        echo "FRONT_HTML_RENDERED: " . $card->front_html . "\n";
        echo "BACK_HTML_RENDERED: " . $card->back_html . "\n";
    } else {
        echo "CARD_NOT_FOUND\n";
    }
} else {
    echo "NOTE_NOT_FOUND\n";
}
