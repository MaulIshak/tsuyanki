<?php

namespace Database\Factories;

use App\Models\Deck;
use App\Models\NoteType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Note>
 */
class NoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'deck_id' => Deck::factory(),
            'note_type_id' => NoteType::factory(),
            'fields' => ['Front' => 'Hello', 'Back' => 'World'],
        ];
    }
}
