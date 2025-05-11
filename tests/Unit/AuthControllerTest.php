<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;


class AuthControllerTest extends TestCase
{
    Use DatabaseTransactions;
    
    public function test_user_can_sign_up(): void
    {
        $email = fake()->unique()->safeEmail();
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => $email,
            'password' => 'password',
            'is_admin' => false,
        ]);
        $response->assertStatus(201)
            ->assertJson([
                'message' => 'User registered successfully',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => $email,
        ]);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'is_admin',
            ],
            'message',
        ]);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'name' => 'Test User',
            'is_admin' => false,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User logged in successfully',
            ]);

        $response->assertJsonStructure([
            'data' => [
                'access_token',
                'is_admin',
            ],
            'message',
        ]);
    }

    public function test_user_can_login_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'salah@gmail.com',
            'password' => 'wrongpassword',
        ]);
        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Unauthorized',
            ]);

    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'name' => 'Test User',
            'is_admin' => false,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User logged in successfully',
            ]);
        $response->assertJsonStructure([
            'data' => [
                'access_token',
                'is_admin',
            ],
            'message',
        ]);
        $token = $response->json('data.access_token');
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out successfully',
            ]);
    }
    public function test_user_can_get_authenticated_user(): void
    {
        $user = User::factory()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'name' => 'Test User',
            'is_admin' => false,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User logged in successfully',
            ]);
        $response->assertJsonStructure([
            'data' => [
                'access_token',
                'is_admin',
            ],
            'message',
        ]);
        $token = $response->json('data.access_token');
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/user');
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_admin' => false,
                ],
            ]);
    }
}