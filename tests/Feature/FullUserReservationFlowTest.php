<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FullUserReservationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_reservation_flow()
    {
        // Step 1: Register Admin
        $admin = User::factory()->create([
            'is_admin' => true,
            'email' => 'admin@example.com',
            'password' => bcrypt('password123')
        ]);

        // Step 2: Login Admin
        $adminLoginResponse = $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $adminToken = $adminLoginResponse['data']['access_token'];

        // Step 3: Admin creates a service
        $serviceResponse = $this->withToken($adminToken)->postJson('/api/services', [
            'name' => 'Test Service',
            'description' => 'Sample description',
            'price' => 100,
            'available' => true
        ]);

        $serviceId = $serviceResponse['data']['id'];

        // Step 4: Register Normal User
        $user = User::factory()->create([
            'is_admin' => false,
            'email' => 'user@example.com',
            'password' => bcrypt('password123')
        ]);

        // Step 5: Login User
        $userLoginResponse = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $userToken = $userLoginResponse['data']['access_token'];

        // Step 6: List Services
        $this->withToken($userToken)->getJson('/api/services')->assertOk();

        // Step 7: User makes a reservation
        $reservationTime = Carbon::now()->addDay()->toDateTimeString();

        $reservationResponse = $this->withToken($userToken)->postJson('/api/reservations', [
            'service_id' => $serviceId,
            'reservation_datetime' => $reservationTime,
        ]);

        $reservationId = $reservationResponse['data']['id'];

        // Step 8: User updates reservation time
        $newTime = Carbon::now()->addDays(2)->toDateTimeString();

        $this->withToken($userToken)->putJson("/api/reservations/{$reservationId}", [
            'reservation_datetime' => $newTime
        ])->assertOk();

        // Step 9: User confirms reservation
        $this->withToken($userToken)->putJson("/api/reservations/confirm/{$reservationId}")
            ->assertJson(['message' => 'Reservation confirmed successfully']);

        // Final check: Ensure confirmed
        $this->assertDatabaseHas('reservations', [
            'id' => $reservationId,
            'status' => 'confirmed',
            'reservation_datetime' => $newTime
        ]);
        
    }
}
