<?php

namespace Database\Factories;

use App\Models\NoteType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CardTemplate>
 */
class CardTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'note_type_id' => NoteType::factory(),
            'name' => 'Standard',
            'front_template' => '{{Front}}',
            'back_template' => '{{Back}}',
        ];
    }
}
