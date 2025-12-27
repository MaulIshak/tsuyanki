<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnkiImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnkiImportController extends Controller
{
    protected $ankiImportService;

    public function __construct(AnkiImportService $ankiImportService)
    {
        $this->ankiImportService = $ankiImportService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimetypes:application/zip,application/octet-stream|max:51200', // 50MB max
        ]);

        try {
            $file = $request->file('file');
            $user = $request->user();

            $result = $this->ankiImportService->import($file, $user);

            return response()->json([
                'message' => 'Import successful',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Anki Import Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Import failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
