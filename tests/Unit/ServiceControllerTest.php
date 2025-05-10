<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\Service;
use App\Http\Resources\ServiceResource;
use Database\Factories\ServiceFactory;

class ServiceControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_authenticated_user_can_create_service(): void
    {
        $user = User::factory()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'name' => 'Test User',
            'is_admin' => true,
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/api/services', [
            'name' => 'Test Service',
            'description' => 'Test Description',
            'price' => 100,
            'available' => true,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'description',
                'price',
                'available',
            ]
        ]);
    }

    public function test_not_authenticated_user_cannot_create_service(): void
    {
        $response = $this->postJson('/api/services', [
            'name' => 'Test Service',
            'description' => 'Test Description',
            'price' => 100,
            'available' => true,
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }
    
    public function test_not_admin_user_cannot_create_service(): void
    {
        $user = User::factory()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'name' => 'Test User',
            'is_admin' => false,
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/api/services', [
            'name' => 'Test Service',
            'description' => 'Test Description',
            'price' => 100,
            'available' => true,
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Unauthorized',
        ]);
    }

    public function test_authenticated_user_can_update_service(): void
    {
        $user = User::factory()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'name' => 'Test User',
            'is_admin' => true,
        ]);

        $this->actingAs($user);

        $service = Service::factory()->create();

        $response = $this->putJson('/api/services/' . $service->id, [
            'name' => 'Updated Service',
            'description' => 'Updated Description',
            'price' => 150,
            'available' => false,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'description',
                'price',
                'available',
            ]
        ]);
    }
    public function test_authenticated_user_can_delete_service(): void
    {
        $user = User::factory()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'name' => 'Test User',
            'is_admin' => true,
        ]);

        $this->actingAs($user);

        $service = Service::factory()->create();

        $response = $this->deleteJson('/api/services/' . $service->id);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Service deleted',
        ]);
    }

    public function test_authenticated_user_can_view_service(): void
    {
        $user = User::factory()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'name' => 'Test User',
            'is_admin' => false,
        ]);

        $this->actingAs($user);

        $service = Service::factory()->create();

        $response = $this->getJson('/api/services/' . $service->id);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'description',
                'price',
                'available',
            ]
        ]);
    }
}