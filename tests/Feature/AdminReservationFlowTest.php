<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Service;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminReservationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_admin_reservation_flow()
    {
        // Step 1: Register admin
        $registerResponse = $this->postJson('/api/register', [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password',
            'is_admin' => true,
        ]);

        $registerResponse->assertCreated();
        $admin = User::where('email', 'admin@example.com')->first();
        $admin->update(['is_admin' => true]);

        // Step 2: Login admin
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $loginResponse->assertOk();
        $token = $loginResponse->json('data.access_token');

        // Authenticated headers
        $headers = ['Authorization' => "Bearer $token"];

        // Step 3: Seed data - user, service, reservation
        $user = User::factory()->create();
        $service = Service::factory()->create();
        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'status' => 'pending',
        ]);

        // Step 4: List reservations
        $listResponse = $this->getJson('/api/reservations/all', $headers);
        $listResponse->assertOk();
        $listResponse->assertJsonFragment(['status' => 'pending']);

        // Step 5: Show reservation
        $showResponse = $this->getJson("/api/reservations/{$reservation->id}", $headers);
        $showResponse->assertOk();
        $showResponse->assertJsonFragment(['id' => $reservation->id]);

        // Step 6: Update status
        $updateStatusResponse = $this->putJson("/api/reservations/status/{$reservation->id}", [
            'status' => 'confirmed',
        ], $headers);
        $updateStatusResponse->assertOk();
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'confirmed',
        ]);

        // Step 7: Cancel reservation
        $cancelResponse = $this->patchJson("/api/reservations/{$reservation->id}/cancel", [], $headers);
        $cancelResponse->assertOk();
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'cancelled',
        ]);

        // Step 8: Logout
        $logoutResponse = $this->postJson('/api/logout', [], $headers);
        $logoutResponse->assertOk();
    }
}
