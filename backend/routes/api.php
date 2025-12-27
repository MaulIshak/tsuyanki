<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::prefix('v1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::apiResource('decks', \App\Http\Controllers\Api\DeckController::class);
        Route::post('/decks/{deck}/fork', [\App\Http\Controllers\Api\DeckController::class, 'fork']);

        Route::apiResource('note-types', \App\Http\Controllers\Api\NoteTypeController::class);

        Route::get('note-types/{noteType}/card-templates', [\App\Http\Controllers\Api\CardTemplateController::class, 'index']);
        Route::post('note-types/{noteType}/card-templates', [\App\Http\Controllers\Api\CardTemplateController::class, 'store']);

        Route::get('card-templates/{cardTemplate}', [\App\Http\Controllers\Api\CardTemplateController::class, 'show']);
        Route::put('card-templates/{cardTemplate}', [\App\Http\Controllers\Api\CardTemplateController::class, 'update']);
        Route::delete('card-templates/{cardTemplate}', [\App\Http\Controllers\Api\CardTemplateController::class, 'destroy']);
    });
});
