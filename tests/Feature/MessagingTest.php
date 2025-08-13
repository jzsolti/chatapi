<?php

namespace Tests\Feature;

use App\Enums\FriendStatus;
use App\Models\Friendship;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MessagingTest extends TestCase
{
    use RefreshDatabase;

    protected function makeVerifiedUser(array $attrs = []): User
    {
        return User::factory()->create(array_merge(['email_verified_at' => now()], $attrs));
    }

    protected function makeFriends(User $a, User $b): void
    {
        [$x, $y] = Friendship::normalizePair($a->id, $b->id);

        Friendship::query()->create([
            'user_id'   => $x,
            'friend_id' => $y,
            'status'    => FriendStatus::Accepted,
        ]);
    }

    #[Test]
    public function cannot_send_message_to_self(): void
    {
        $user = $this->makeVerifiedUser();

        Sanctum::actingAs($user);

        $this->postJson('/api/messages', ['receiver_id' => $user->id, 'body' => 'hello'])
            ->assertStatus(422);
    }

    #[Test]
    public function cannot_send_message_if_not_friends(): void
    {
        $alice = $this->makeVerifiedUser();
        $bob   = $this->makeVerifiedUser();

        Sanctum::actingAs($alice);

        $this->postJson('/api/messages', ['receiver_id' => $bob->id, 'body' => 'hi'])
            ->assertStatus(403);
    }

    #[Test]
    public function can_send_message_when_friends(): void
    {
        $alice = $this->makeVerifiedUser(['name' => 'Alice']);
        $bob   = $this->makeVerifiedUser(['name' => 'Bob']);
        $this->makeFriends($alice, $bob);

        Sanctum::actingAs($alice);

        $resp = $this->postJson('/api/messages', ['receiver_id' => $bob->id, 'body' => 'Hello Bob']);
        $resp->assertCreated();
        $resp->assertJsonPath('data.body', 'Hello Bob');
        $this->assertDatabaseHas('messages', [
            'sender_id' => $alice->id,
            'receiver_id' => $bob->id,
            'body' => 'Hello Bob',
        ]);
    }

    #[Test]
    public function cannot_view_conversation_if_not_friends(): void
    {
        $alice = $this->makeVerifiedUser();
        $bob   = $this->makeVerifiedUser();

        Sanctum::actingAs($alice);

        $this->getJson("/api/messages/{$bob->id}")
            ->assertStatus(403);
    }

    #[Test]
    public function can_view_conversation_when_friends(): void
    {
        $alice = $this->makeVerifiedUser(['name' => 'Alice']);
        $bob   = $this->makeVerifiedUser(['name' => 'Bob']);
        $this->makeFriends($alice, $bob);

        // seed some messages
        Message::factory()->create([
            'sender_id' => $alice->id,
            'receiver_id' => $bob->id,
            'body' => 'hi bob',
        ]);
        Message::factory()->create([
            'sender_id' => $bob->id,
            'receiver_id' => $alice->id,
            'body' => 'hi alice',
        ]);

        Sanctum::actingAs($alice);
        $resp = $this->getJson("/api/messages/{$bob->id}?per_page=10");
        $resp->assertOk();
        $resp->assertJson(
            fn($json) =>
            $json->has('data', 2)
                ->where('data.0.body', 'hi bob')
                ->where('data.1.body', 'hi alice')->etc()
        );
    }
}
