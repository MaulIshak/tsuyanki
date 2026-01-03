<?php

namespace Database\Seeders;

use App\Models\NoteType;
use App\Models\CardTemplate;
use Illuminate\Database\Seeder;

class AdditionalNoteTypesSeeder extends Seeder
{
    public function run()
    {
        // Short Answer
        $shortAnswer = NoteType::firstOrCreate(
            ['name' => 'Short Answer'],
            [
                'field_schema' => [
                    'fields' => [
                        ['name' => 'Question', 'type' => 'text', 'required' => true],
                        ['name' => 'Answer', 'type' => 'text', 'required' => true],
                    ]
                ]
            ]
        );

        CardTemplate::firstOrCreate(
            ['note_type_id' => $shortAnswer->id, 'name' => 'Standard'],
            [
                'front_template' => '{{Question}}<br><br><input type="text" id="user-answer">',
                'back_template' => '{{Question}}<br><br>Answer: {{Answer}}',
            ]
        );

        // Multiple Choice
        $multipleChoice = NoteType::firstOrCreate(
            ['name' => 'Multiple Choice'],
            [
                'field_schema' => [
                    'fields' => [
                        ['name' => 'Question', 'type' => 'text', 'required' => true],
                        ['name' => 'Option A', 'type' => 'text', 'required' => true],
                        ['name' => 'Option B', 'type' => 'text', 'required' => true],
                        ['name' => 'Option C', 'type' => 'text', 'required' => false],
                        ['name' => 'Option D', 'type' => 'text', 'required' => false],
                        ['name' => 'Correct Option', 'type' => 'text', 'required' => true], // e.g. "A"
                    ]
                ]
            ]
        );

        CardTemplate::firstOrCreate(
            ['note_type_id' => $multipleChoice->id, 'name' => 'Standard'],
            [
                'front_template' => '
                    {{Question}}<br>
                    <div class="options">
                        A: {{Option A}}<br>
                        B: {{Option B}}<br>
                        C: {{Option C}}<br>
                        D: {{Option D}}
                    </div>
                ',
                'back_template' => '
                    {{Question}}<br>
                    <div class="options">
                        A: {{Option A}}<br>
                        B: {{Option B}}<br>
                        C: {{Option C}}<br>
                        D: {{Option D}}
                    </div>
                    <hr>
                    Required: {{Correct Option}}
                ',
            ]
        );
    }
}
