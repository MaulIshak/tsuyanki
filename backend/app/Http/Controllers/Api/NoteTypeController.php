<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NoteType;
use Illuminate\Http\Request;

class NoteTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'per_page' => 'integer|min:1|max:100',
        ]);

        $noteTypes = NoteType::query()
            ->withCount('cardTemplates as templates_count')
            ->paginate($request->input('per_page', 20));

        return $this->paginateResponse($noteTypes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'field_schema' => 'required|array',
            'field_schema.fields' => 'required|array',
            'field_schema.fields.*.name' => 'required|string',
            'field_schema.fields.*.type' => 'required|string',
            'field_schema.fields.*.required' => 'boolean',
        ]);

        $noteType = NoteType::create($validated);

        return response()->json($noteType, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(NoteType $noteType)
    {
        $noteType->loadCount('cardTemplates as templates_count');
        return response()->json($noteType);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, NoteType $noteType)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'field_schema' => 'sometimes|required|array',
            'field_schema.fields' => 'required_with:field_schema|array',
        ]);

        $noteType->update($validated);

        return response()->json($noteType);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NoteType $noteType)
    {
        // Check if in use (api requirement caveat)
        // For now just delete, or check notes count if Note model existed
        $noteType->delete();

        return response()->json(['message' => 'Note type deleted successfully']);
    }
}
