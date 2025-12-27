<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'deck_id',
        'note_type_id',
        'fields',
    ];

    protected $casts = [
        'fields' => 'array',
    ];

    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    public function noteType(): BelongsTo
    {
        return $this->belongsTo(NoteType::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'note_tags');
    }

    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'note_media')->withPivot('field_name');
    }
}
