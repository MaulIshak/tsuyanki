<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Request $request)
    {
        $query = Tag::query()->withCount('notes')->orderBy('name', 'asc');

        if ($request->filled('deck_id')) {
            $query->where('deck_id', $request->input('deck_id'));
        }

        $tags = $query->paginate($request->input('limit', 50));

        return $this->paginateResponse($tags);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|unique:tags,name']);

        $tag = Tag::create($validated);

        return response()->json($tag, 201);
    }

    public function update(Request $request, Tag $tag)
    {
        $validated = $request->validate(['name' => 'required|string|unique:tags,name,' . $tag->id]);

        $tag->update($validated);

        return response()->json($tag);
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();

        return response()->json(['message' => 'Tag deleted successfully']);
    }
}
