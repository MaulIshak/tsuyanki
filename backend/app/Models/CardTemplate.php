<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'note_type_id',
        'name',
        'front_template',
        'back_template',
    ];

    public function noteType(): BelongsTo
    {
        return $this->belongsTo(NoteType::class);
    }
}
