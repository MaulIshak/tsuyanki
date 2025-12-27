<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'note_id',
        'card_template_id',
    ];

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CardTemplate::class, 'card_template_id');
    }

    public function reviewState(): HasOne
    {
        return $this->hasOne(ReviewState::class);
    }

    public function reviewLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ReviewLog::class);
    }
}
