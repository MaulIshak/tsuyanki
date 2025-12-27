<?php

use App\Models\Card;
use App\Models\User;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = User::first(); // Assuming first user
$deckId = 23; // REPLACE WITH ACTUAL DECK ID FROM USER URL IF KNOWN, OR SEARCH

// List all decks
$decks = \App\Models\Deck::all();
echo "Total Decks: " . $decks->count() . "\n";
echo "Total Notes in DB: " . \App\Models\Note::count() . "\n";
echo "Total Users: " . \App\Models\User::count() . "\n";

foreach ($decks as $d) {
    echo "Deck ID: {$d->id} | Title: {$d->title} | Owner: {$d->owner_user_id} | Notes: " . \App\Models\Note::where('deck_id', $d->id)->count() . "\n";
}

// Just pick the one with the most notes for debugging
$deck = $decks->sortByDesc(fn($d) => \App\Models\Note::where('deck_id', $d->id)->count())->first();

if (!$deck) {
    echo "No decks with notes found.\n";
    exit;
}
$deckId = $deck->id;
echo "Debugging for Deck ID: $deckId (Title: {$deck->title})\n";
echo "User ID: {$user->id}\n";

// Check 1: Cards exist?
$notesCount = \App\Models\Note::where('deck_id', $deckId)->count();
$cardsCount = Card::whereHas('note', fn($q) => $q->where('deck_id', $deckId))->count();
echo "Notes in deck: $notesCount\n";
echo "Cards in deck: $cardsCount\n";

// Check 2: New Query
$newQuery = Card::query()
    ->with(['note.noteType', 'template'])
    ->where(function ($q) use ($user) {
        $q->whereDoesntHave('reviewState', function ($sq) use ($user) {
            $sq->where('user_id', $user->id);
        })
            ->orWhereHas('reviewState', function ($sq) use ($user) {
                $sq->where('user_id', $user->id)
                    ->where('repetition', 0)
                    ->where('interval', 0);
            });
    });

$newQuery->whereHas('note', fn($q) => $q->where('deck_id', $deckId));

echo "SQL: " . $newQuery->toSql() . "\n";
echo "Bindings: " . json_encode($newQuery->getBindings()) . "\n";

$count = $newQuery->count();
echo "New Cards Available (Ignoring limits): $count\n";

// Check 4: Review Ahead Query Logic (Simplified Inner Join)
echo "\n--- Debugging Review Ahead (Inner Join) ---\n";

$aheadQuery = Card::query()
    ->join('review_states', 'cards.id', '=', 'review_states.card_id')
    ->where('review_states.user_id', $user->id)
    ->where('review_states.due_at', '>', now());

$aheadQuery->whereHas('note', fn($q) => $q->where('deck_id', $deckId));

echo "Cards with future due date (Raw): " . $aheadQuery->count() . "\n";

$reviewAheadCards = $aheadQuery
    ->select('cards.*', 'review_states.last_reviewed_at as debug_last_reviewed')
    ->orderBy('review_states.last_reviewed_at', 'asc')
    ->orderBy('review_states.due_at', 'asc')
    ->limit(20)
    ->get();

echo "Cards returned by Join Query: " . $reviewAheadCards->count() . "\n";
foreach ($reviewAheadCards as $c) {
    echo " - Card {$c->id}: Last Reviewed: {$c->debug_last_reviewed}\n";
}
