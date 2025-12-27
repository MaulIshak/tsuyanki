<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Deck;
use App\Models\Note;
use App\Models\NoteType;
use App\Models\CardTemplate;
use App\Models\ReviewState;
use App\Models\Card;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = 11;

        // 1. Ensure User exists
        $user = User::find($userId);
        if (!$user) {
            $user = User::create([
                'id' => $userId,
                'name' => 'Demo User',
                'email' => 'demo@tsuyanki.com',
                'password' => Hash::make('password'),
            ]);
            $this->command->info("Created user with ID {$userId}");
        }

        // 2. Ensure Basic Note Type exists
        $basicType = NoteType::firstOrCreate(
            ['name' => 'Basic'],
            [
                'field_schema' => [
                    'fields' => [
                        ['name' => 'Front', 'type' => 'text'],
                        ['name' => 'Back', 'type' => 'text'],
                    ]
                ]
            ]
        );

        // Ensure Card Template exists for Basic
        $template = CardTemplate::firstOrCreate(
            ['note_type_id' => $basicType->id, 'name' => 'Standard'],
            [
                'front_template' => '{{Front}}',
                'back_template' => '{{FrontSide}}<hr id="answer">{{Back}}',
            ]
        );

        // 3. Create Deck
        $deck = Deck::firstOrCreate(
            ['owner_user_id' => $userId, 'title' => 'Japanese Core 100'],
            [
                'description' => 'Essential Japanese vocabulary for beginners.',
                'is_public' => false
            ]
        );

        // 4. Create Notes & Cards
        $vocab = [
            ['Front' => '猫 (Neko)', 'Back' => 'Cat'],
            ['Front' => '犬 (Inu)', 'Back' => 'Dog'],
            ['Front' => '水 (Mizu)', 'Back' => 'Water'],
            ['Front' => '食べる (Taberu)', 'Back' => 'To eat'],
            ['Front' => '見る (Miru)', 'Back' => 'To see'],
            ['Front' => '日本 (Nihon)', 'Back' => 'Japan'],
            ['Front' => '勉強 (Benkyou)', 'Back' => 'Study'],
            ['Front' => '先生 (Sensei)', 'Back' => 'Teacher'],
            ['Front' => '学生 (Gakusei)', 'Back' => 'Student'],
            ['Front' => '学校 (Gakkou)', 'Back' => 'School'],
        ];

        foreach ($vocab as $data) {
            // Check if note exists to prevent dupes on re-run
            $exists = Note::where('deck_id', $deck->id)
                ->whereRaw("fields->>'Front' = ?", [$data['Front']])
                ->exists();

            if (!$exists) {
                $note = Note::create([
                    'deck_id' => $deck->id,
                    'note_type_id' => $basicType->id,
                    'fields' => $data
                ]);

                // Create Card logic (mimics controller)
                $card = Card::create([
                    'note_id' => $note->id,
                    'card_template_id' => $template->id,
                ]);

                // Create initial ReviewState?
                // Per BP: "Sistem otomatis membuat ReviewState awal" during creation.
                // Assuming the controller/observer does this, OR we do it here.
                // Controller did NOT explicitly create ReviewState, expecting the query to handle nulls as "New".
                // BP Says: "ReviewState: interval=0, repetition=0... Tidak ada card tanpa review_state".
                // I should create it to be safe and strictly follow BP.

                ReviewState::create([
                    'user_id' => $userId,
                    'card_id' => $card->id,
                    'ease_factor' => 2.5,
                    'interval' => 0,
                    'repetition' => 0,
                    'due_at' => now(), // Due immediately
                ]);
            }
        }

        $this->command->info("Seeded deck 'Japanese Core 100' with " . count($vocab) . " notes/cards.");
    }
}
