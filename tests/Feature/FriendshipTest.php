<?php

namespace Tests\Feature;

use App\Enums\FriendStatus;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class FriendshipTest extends TestCase
{
    use RefreshDatabase;

    protected function makeVerifiedUser(array $attrs = []): User
    {
        return User::factory()->create(array_merge(['email_verified_at' => now()], $attrs));
    }

    #[Test]
    public function cannot_send_friend_request_to_self(): void
    {
        $user = $this->makeVerifiedUser();

        Sanctum::actingAs($user);

        $this->postJson('/api/friends/send', ['user_id' => $user->id])
            ->assertStatus(422);
    }

    #[Test]
    public function can_send_and_accept_friend_request(): void
    {
        $alice = $this->makeVerifiedUser(['name' => 'Alice']);
        $bob   = $this->makeVerifiedUser(['name' => 'Bob']);

        Sanctum::actingAs($alice);

        // Send friend request
        $send = $this->postJson('/api/friends/send', ['user_id' => $bob->id]);
        $send->assertSuccessful();
        $send->assertJsonPath('data.status', FriendStatus::Pending->value);

        // Accept as Bob
        Sanctum::actingAs($bob);
        $accept = $this->postJson('/api/friends/accept', ['user_id' => $alice->id]);
        $accept->assertSuccessful();
        $accept->assertJsonPath('data.status', FriendStatus::Accepted->value);

        // List friends as Alice
        Sanctum::actingAs($alice);
        $list = $this->getJson('/api/friends');
        $list->assertOk();
        $list->assertJson(
            fn($json) =>
            $json->has('data', 1)
                ->where('data.0.name', 'Bob')
                ->where('data.0.status', FriendStatus::Accepted->value)
        );
    }
}
