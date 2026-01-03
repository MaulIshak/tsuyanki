<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tags = \App\Models\Tag::with('notes')->whereNull('deck_id')->get();

foreach ($tags as $tag) {
    $deckIds = $tag->notes->pluck('deck_id')->unique()->values();

    if ($deckIds->isEmpty()) {
        echo "Tag {$tag->name} (ID: {$tag->id}) has no notes. Deleting...\n";
        $tag->delete();
        continue;
    }

    // Assign to first deck
    $firstDeckId = $deckIds[0];
    echo "Updating Tag {$tag->name} (ID: {$tag->id}) to Deck ID: {$firstDeckId}\n";
    $tag->deck_id = $firstDeckId;
    $tag->save();

    // Handle multiple decks (split tag)
    if ($deckIds->count() > 1) {
        for ($i = 1; $i < $deckIds->count(); $i++) {
            $otherDeckId = $deckIds[$i];
            echo "Splitting Tag {$tag->name} for Deck ID: {$otherDeckId}\n";

            // Create new tag for this deck
            $newTag = \App\Models\Tag::firstOrCreate([
                'name' => $tag->name,
                'deck_id' => $otherDeckId
            ]);

            // Re-link notes belonging to this deck
            $notesToMove = $tag->notes()->where('deck_id', $otherDeckId)->get();
            foreach ($notesToMove as $note) {
                // Detach old, Attach new
                echo "  Relinking Note {$note->id}\n";
                $note->tags()->detach($tag->id);
                $note->tags()->attach($newTag->id);
            }
        }
    }
}
echo "Done.\n";
