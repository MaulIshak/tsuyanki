<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NoteType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'field_schema',
    ];

    protected $casts = [
        'field_schema' => 'array',
    ];

    public function cardTemplates(): HasMany
    {
        return $this->hasMany(CardTemplate::class);
    }
}
