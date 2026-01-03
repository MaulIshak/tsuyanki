<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deck;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Deck $deck)
    {
        Gate::authorize('view', $deck);

        $request->validate([
            'per_page' => 'integer|min:1|max:100',
            'tag' => 'nullable|string',
            'search' => 'nullable|string',
        ]);

        $query = $deck->notes()->with(['cards.reviewState', 'media', 'tags']);

        if ($request->filled('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('name', $request->input('tag'));
            });
        }

        if ($request->filled('search')) {
            // JSON fields search is Postgres specific usually
            $search = $request->input('search');
            // Postgres supports casting jsonb to text
            $query->whereRaw("fields::text ilike ?", ["%{$search}%"]);
        }

        $notes = $query->paginate($request->input('per_page', 20));

        return $this->paginateResponse($notes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Deck $deck)
    {
        Gate::authorize('update', $deck);

        $validated = $request->validate([
            'note_type_id' => 'required|exists:note_types,id',
            'fields' => 'required|array',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
        ]);

        $note = DB::transaction(function () use ($deck, $validated) {
            $note = $deck->notes()->create([
                'note_type_id' => $validated['note_type_id'],
                'fields' => $validated['fields'],
            ]);

            if (!empty($validated['tags'])) {
                // Find or create tags
                $tagIds = [];
                foreach ($validated['tags'] as $tagName) {
                    $tag = \App\Models\Tag::firstOrCreate(['name' => $tagName]);
                    $tagIds[] = $tag->id;
                }
                $note->tags()->sync($tagIds);
            }

            // Create cards based on template
            $noteType = \App\Models\NoteType::find($validated['note_type_id']);
            foreach ($noteType->cardTemplates as $template) {
                $note->cards()->create([
                    'card_template_id' => $template->id,
                ]);
            }

            return $note;
        });

        $note->load(['cards', 'tags']);

        return response()->json([
            'note' => $note,
            'cards_created' => $note->cards
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Note $note)
    {
        Gate::authorize('view', $note->deck);

        $note->load(['cards', 'media', 'tags', 'noteType']);

        return response()->json($note);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Note $note)
    {
        Gate::authorize('update', $note->deck);

        $validated = $request->validate([
            'fields' => 'sometimes|required|array',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
        ]);

        DB::transaction(function () use ($note, $validated) {
            if (isset($validated['fields'])) {
                $note->update(['fields' => $validated['fields']]);
            }

            if (isset($validated['tags'])) {
                $tagIds = [];
                foreach ($validated['tags'] as $tagName) {
                    $tag = \App\Models\Tag::firstOrCreate(['name' => $tagName]);
                    $tagIds[] = $tag->id;
                }
                $note->tags()->sync($tagIds);
            }
        });

        return response()->json($note->refresh()->load(['tags']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Note $note)
    {
        Gate::authorize('delete', $note->deck);

        $count = $note->cards()->count();
        $note->delete();

        return response()->json([
            'message' => 'Note deleted successfully',
            'cards_deleted' => $count
        ]);
    }

    public function attachMedia(Request $request, Note $note)
    {
        Gate::authorize('update', $note->deck);

        $validated = $request->validate([
            'media_id' => 'required|exists:media,id',
            'field_name' => 'required|string',
        ]);

        $note->media()->attach($validated['media_id'], ['field_name' => $validated['field_name']]);

        return response()->json(['message' => 'Media attached']);
    }

    public function detachMedia(Note $note, $mediaId)
    {
        Gate::authorize('update', $note->deck);

        $note->media()->detach($mediaId);

        return response()->json(['message' => 'Media detached']);
    }
}
