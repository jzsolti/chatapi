<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UsersIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function makeVerifiedUser(array $attrs = []): User
    {
        return User::factory()->create(array_merge(['email_verified_at' => now()], $attrs));
    }

    #[Test]
    public function list_users_requires_auth_and_verified(): void
    {
        $this->getJson('/api/users')->assertStatus(401); // not authenticated

        $unverified = User::factory()->create(['email_verified_at' => null]);
        Sanctum::actingAs($unverified);
        $this->getJson('/api/users')->assertStatus(403); // not verified
    }

    #[Test]
    public function can_list_users_and_marks_current_user(): void
    {
        $me   = $this->makeVerifiedUser(['name' => 'Me']);
        $john = $this->makeVerifiedUser(['name' => 'John']);
        $jane = $this->makeVerifiedUser(['name' => 'Jane']);

        Sanctum::actingAs($me);

        $resp = $this->getJson('/api/users?per_page=10');
        $resp->assertOk();
        $resp->assertJson(
            fn($json) =>
            $json->has('data')
                ->whereType('data', 'array')->etc()
        );

        // Find "me" entry and check is_current_user flag
        $data = $resp->json('data');
        $meRow = collect($data)->firstWhere('id', $me->id);
        $this->assertNotNull($meRow);
        $this->assertTrue((bool)($meRow['is_current_user'] ?? false));
    }
}
