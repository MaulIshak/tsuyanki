<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NoteType>
 */
class NoteTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'field_schema' => [
                'fields' => [
                    ['name' => 'Front', 'type' => 'text', 'required' => true],
                    ['name' => 'Back', 'type' => 'text', 'required' => true],
                ]
            ],
        ];
    }
}
