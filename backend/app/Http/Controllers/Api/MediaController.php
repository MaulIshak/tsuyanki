<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MediaController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB
        ]);

        $file = $request->file('file');
        $path = $file->store('media', 'public');

        $media = Media::create([
            'owner_user_id' => $request->user()->id,
            'storage_key' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
        ]);

        // Append URL for response
        $media->url = asset('storage/' . $path);

        return response()->json($media, 201);
    }

    public function show(Media $media)
    {
        // Simple authorization: owner or public access usually? 
        // For now restricting to owner or if it's used in a visible note (complex check).
        // Let's just allow owner for now.
        if ($media->owner_user_id !== request()->user()->id) {
            abort(403);
        }

        $media->url = asset('storage/' . $media->storage_key);
        return response()->json($media);
    }

    public function destroy(Media $media)
    {
        if ($media->owner_user_id !== request()->user()->id) {
            abort(403);
        }

        // Check usage?

        $media->delete();

        return response()->json(['message' => 'Media deleted successfully']);
    }
}
