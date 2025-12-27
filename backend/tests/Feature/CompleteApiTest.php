<?php

namespace Tests\Feature;

use App\Models\Deck;
use App\Models\Note;
use App\Models\NoteType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompleteApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_flow_decks_notes_reviews()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 1. Create Deck
        $deckResponse = $this->postJson('/api/v1/decks', [
            'title' => 'Japanese Vocab',
            'is_public' => true
        ]);
        $deckResponse->assertStatus(201);
        $deckId = $deckResponse->json('id');

        // 2. Create Note Type & Template (Seeded or Factory)
        // NoteTypeFactory should create templates automatically if defined? 
        // Or we create manually.
        $noteType = NoteType::factory()->create();
        // Create template for this note type
        \App\Models\CardTemplate::factory()->create([
            'note_type_id' => $noteType->id
        ]);

        // 3. Create Note
        $noteResponse = $this->postJson("/api/v1/decks/{$deckId}/notes", [
            'note_type_id' => $noteType->id,
            'fields' => ['Front' => 'Taberu', 'Back' => 'To Eat'],
            'tags' => ['verb', 'n5']
        ]);
        $noteResponse->assertStatus(201);
        $noteId = $noteResponse->json('note.id');

        // 4. Verify Cards Created
        $this->assertDatabaseHas('cards', ['note_id' => $noteId]);
        $cardId = $noteResponse->json('cards_created.0.id');

        // 5. Review Due (Should be due immediately or need verification of due logic)
        // Update: ReviewController checks for card without ReviewState or due_at <= now
        $dueResponse = $this->getJson('/api/v1/review/due?deck_id=' . $deckId);
        $dueResponse->assertStatus(200);
        // Should find the card we just created (since it has no review state)
        $this->assertEquals($cardId, $dueResponse->json('cards.0.id'));

        // 6. Submit Review
        $reviewResponse = $this->postJson("/api/v1/review/{$cardId}", [
            'quality' => 4
        ]);
        $reviewResponse->assertStatus(200);

        // 7. Verify Review Log and State
        $this->assertDatabaseHas('review_logs', [
            'card_id' => $cardId,
            'quality' => 4
        ]);
        $this->assertDatabaseHas('review_states', [
            'card_id' => $cardId,
            'user_id' => $user->id
        ]);

        // 8. Stats
        $statsResponse = $this->getJson('/api/v1/review/stats');
        $statsResponse->assertStatus(200)
            ->assertJson(['reviews_completed' => 1]);

        // 9. Media Upload (Mock file not possible easily in simple flow without fake storage, skipping for this flow)

        // 10. Tags
        $tagsResponse = $this->getJson('/api/v1/tags');
        $tagsResponse->assertStatus(200)
            ->assertJsonFragment(['name' => 'verb']);
    }
}
