<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::prefix('v1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle']);
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
        Route::delete('/auth/user', [AuthController::class, 'destroy']);

        Route::apiResource('decks', \App\Http\Controllers\Api\DeckController::class);
        Route::post('/decks/{deck}/fork', [\App\Http\Controllers\Api\DeckController::class, 'fork']);
        Route::post('/decks/{deck}/sync', [\App\Http\Controllers\Api\DeckController::class, 'sync']);

        Route::apiResource('note-types', \App\Http\Controllers\Api\NoteTypeController::class);

        Route::get('note-types/{noteType}/card-templates', [\App\Http\Controllers\Api\CardTemplateController::class, 'index']);
        Route::post('note-types/{noteType}/card-templates', [\App\Http\Controllers\Api\CardTemplateController::class, 'store']);

        Route::get('card-templates/{cardTemplate}', [\App\Http\Controllers\Api\CardTemplateController::class, 'show']);
        Route::put('card-templates/{cardTemplate}', [\App\Http\Controllers\Api\CardTemplateController::class, 'update']);
        Route::delete('card-templates/{cardTemplate}', [\App\Http\Controllers\Api\CardTemplateController::class, 'destroy']);

        // Notes
        Route::get('decks/{deck}/notes', [\App\Http\Controllers\Api\NoteController::class, 'index']);
        Route::post('decks/{deck}/notes', [\App\Http\Controllers\Api\NoteController::class, 'store']);
        Route::get('notes/{note}', [\App\Http\Controllers\Api\NoteController::class, 'show']);
        Route::put('notes/{note}', [\App\Http\Controllers\Api\NoteController::class, 'update']);
        Route::delete('notes/{note}', [\App\Http\Controllers\Api\NoteController::class, 'destroy']);
        Route::post('notes/{note}/media', [\App\Http\Controllers\Api\NoteController::class, 'attachMedia']);
        Route::delete('notes/{note}/media/{media}', [\App\Http\Controllers\Api\NoteController::class, 'detachMedia']);

        // Cards
        Route::get('cards/{card}', [\App\Http\Controllers\Api\CardController::class, 'show']);

        // Review
        Route::get('review/due', [\App\Http\Controllers\Api\ReviewController::class, 'due']);
        Route::get('review/stats', [\App\Http\Controllers\Api\ReviewController::class, 'stats']);
        Route::post('review/{card}', [\App\Http\Controllers\Api\ReviewController::class, 'submit']);

        // Media
        Route::post('media/upload', [\App\Http\Controllers\Api\MediaController::class, 'upload']);
        Route::get('media/{media}', [\App\Http\Controllers\Api\MediaController::class, 'show']);
        Route::delete('media/{media}', [\App\Http\Controllers\Api\MediaController::class, 'destroy']);

        // Tags
        Route::apiResource('tags', \App\Http\Controllers\Api\TagController::class);

        // Import
        Route::post('import/anki', [\App\Http\Controllers\Api\AnkiImportController::class, 'store']);
    });
});
