<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deck extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'is_public',
        'owner_user_id',
        'source_deck_id',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function sourceDeck(): BelongsTo
    {
        return $this->belongsTo(Deck::class, 'source_deck_id');
    }

    public function forks(): HasMany
    {
        return $this->hasMany(Deck::class, 'source_deck_id');
    }

    // Placeholder for notes relationship
    // public function notes(): HasMany
    // {
    //     return $this->hasMany(Note::class);
    // }
}
