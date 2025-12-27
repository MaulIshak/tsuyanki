<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CardController extends Controller
{
    public function show(Card $card)
    {
        Gate::authorize('view', $card->note->deck);

        $card->load(['reviewState', 'note', 'template']);

        // Transform to match API contract if needed
        // For simplicity returning model
        return response()->json($card);
    }
}
