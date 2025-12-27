<?php

namespace Tests\Feature;

use App\Models\Deck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DecksApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_decks()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Deck::factory()->count(5)->create(['owner_user_id' => $user->id]);

        $response = $this->getJson('/api/v1/decks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'description', 'owner_user_id', 'is_public', 'created_at', 'updated_at']
                ],
                'links',
                'meta'
            ]);
    }

    public function test_can_create_deck()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $payload = [
            'title' => 'My New Deck',
            'description' => 'A description',
            'is_public' => false,
        ];

        $response = $this->postJson('/api/v1/decks', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'title' => 'My New Deck',
                'owner_user_id' => $user->id,
            ]);

        $this->assertDatabaseHas('decks', [
            'title' => 'My New Deck',
            'owner_user_id' => $user->id,
        ]);
    }

    public function test_can_show_deck()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $deck = Deck::factory()->create(['owner_user_id' => $user->id]);

        $response = $this->getJson('/api/v1/decks/' . $deck->id);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $deck->id,
                'title' => $deck->title,
            ]);
    }

    public function test_cannot_show_others_private_deck()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->actingAs($user);

        $deck = Deck::factory()->create(['owner_user_id' => $otherUser->id, 'is_public' => false]);

        $response = $this->getJson('/api/v1/decks/' . $deck->id);

        $response->assertStatus(403);
    }

    public function test_can_update_deck()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $deck = Deck::factory()->create(['owner_user_id' => $user->id]);

        $response = $this->putJson('/api/v1/decks/' . $deck->id, [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Updated Title']);

        $this->assertDatabaseHas('decks', ['id' => $deck->id, 'title' => 'Updated Title']);
    }

    public function test_can_delete_deck()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $deck = Deck::factory()->create(['owner_user_id' => $user->id]);

        $response = $this->deleteJson('/api/v1/decks/' . $deck->id);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('decks', ['id' => $deck->id]);
    }

    public function test_can_fork_public_deck()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $publicDeck = Deck::factory()->create([
            'owner_user_id' => $otherUser->id,
            'is_public' => true,
            'title' => 'Public Deck'
        ]);

        $this->actingAs($user);

        $response = $this->postJson("/api/v1/decks/{$publicDeck->id}/fork", [
            'title' => 'Forked Deck'
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('decks', [
            'owner_user_id' => $user->id,
            'source_deck_id' => $publicDeck->id,
            'title' => 'Forked Deck'
        ]);
    }
}
