<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DeckController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'per_page' => 'integer|min:1|max:100',
            'sort' => 'in:created_at,updated_at,title',
            'order' => 'in:asc,desc',
            'is_public' => 'boolean',
            'search' => 'nullable|string',
        ]);

        $query = Deck::query()->withCount('notes');

        // Filter by ownership or public visibility
        $query->where(function ($q) use ($request) {
            $q->where('owner_user_id', $request->user()->id)
                ->orWhere('is_public', true);
        });

        if ($request->has('is_public')) {
            // If strictly asking for public/private decks via query param
            $query->where('is_public', $request->boolean('is_public'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                // Use ilike for case-insensitive search in PostgreSQL
                $q->where('title', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        $sort = $request->input('sort', 'created_at');
        $order = $request->input('order', 'desc');

        $query->orderBy($sort, $order);

        $perPage = $request->input('per_page', $request->input('limit', 6));
        $decks = $query->paginate($perPage);

        return $this->paginateResponse($decks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $deck = $request->user()->decks()->create($validated);

        return response()->json($deck, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Deck $deck)
    {
        Gate::authorize('view', $deck);

        return response()->json($deck);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Deck $deck)
    {
        Gate::authorize('update', $deck);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $deck->update($validated);

        return response()->json($deck);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Deck $deck)
    {
        Gate::authorize('delete', $deck);

        $deck->delete();

        return response()->json(['message' => 'Deck deleted successfully']);
    }

    /**
     * Fork a deck.
     */
    public function fork(Request $request, Deck $deck)
    {
        Gate::authorize('view', $deck);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $newDeck = DB::transaction(function () use ($request, $deck, $validated) {
            $newDeck = Deck::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? $deck->description,
                'is_public' => false,
                'owner_user_id' => $request->user()->id,
                'source_deck_id' => $deck->id,
            ]);

            return $newDeck;
        });

        return response()->json($newDeck, 201);
    }
}
