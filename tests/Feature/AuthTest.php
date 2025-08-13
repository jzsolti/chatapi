<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_register_and_receives_verification_email(): void
    {
        Notification::fake();

        $payload = [
            'name' => 'Alice',
            'email' => 'alice@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $resp = $this->postJson('/api/register', $payload);
        $resp->assertSuccessful();

        $user = User::where('email', 'alice@example.test')->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->hasVerifiedEmail());

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    #[Test]
    public function cannot_login_if_email_not_verified(): void
    {
        $user = User::factory()->create([
            'email' => 'bob@example.test',
            'password' => bcrypt('password'),
            'email_verified_at' => null,
        ]);

        $resp = $this->postJson('/api/login', [
            'email' => 'bob@example.test',
            'password' => 'password',
        ]);

        $resp->assertStatus(403)
            ->assertJsonFragment(['message' => 'Email not verified.']);
    }

    #[Test]
    public function can_login_with_verified_email_and_gets_token(): void
    {
        $user = User::factory()->create([
            'email' => 'carol@example.test',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $resp = $this->postJson('/api/login', [
            'email' => 'carol@example.test',
            'password' => 'password',
        ]);

        $resp->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'email']]);
        $this->assertIsString($resp->json('token'));
        $this->assertSame('carol@example.test', $resp->json('user.email'));
    }

    #[Test]
    public function cannot_login_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'dave@example.test',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $resp = $this->postJson('/api/login', [
            'email' => 'dave@example.test',
            'password' => 'wrong-password',
        ]);

        $this->assertTrue(in_array($resp->getStatusCode(), [422]), 'Expected 422 on wrong password.');
    }
}
