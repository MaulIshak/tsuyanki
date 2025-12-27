<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CardTemplate;
use App\Models\NoteType;
use Illuminate\Http\Request;

class CardTemplateController extends Controller
{
    /**
     * Display a listing of the resource for a specific note type.
     */
    public function index(Request $request, NoteType $noteType)
    {
        return response()->json([
            'data' => $noteType->cardTemplates
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, NoteType $noteType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'front_template' => 'required|string',
            'back_template' => 'required|string',
        ]);

        $cardTemplate = $noteType->cardTemplates()->create($validated);

        return response()->json($cardTemplate, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CardTemplate $cardTemplate)
    {
        return response()->json($cardTemplate);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CardTemplate $cardTemplate)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'front_template' => 'sometimes|required|string',
            'back_template' => 'sometimes|required|string',
        ]);

        $cardTemplate->update($validated);

        return response()->json($cardTemplate);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CardTemplate $cardTemplate)
    {
        $count = 0; // Placeholder for cards deleted count
        // if cards existed, delete them or count them.

        $cardTemplate->delete();

        return response()->json([
            'message' => 'Card template deleted successfully',
            'cards_deleted' => $count
        ]);
    }
}
