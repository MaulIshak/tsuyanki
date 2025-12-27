<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'card_id',
        'quality',
        'ease_factor',
        'interval',
        'repetition',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'ease_factor' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}
