<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Service;
use App\Models\Reservation;
use Carbon\Carbon;

class ReservationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_reservation()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/reservations', [
                'service_id' => $service->id,
                'reservation_datetime' => Carbon::now()->addDay()->toDateTimeString(),
            ])
            ->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'service_id', 'user_id', 'reservation_datetime', 'status']]);
    }

    public function test_user_can_get_past_and_upcoming_reservations()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        Reservation::factory()->create([
            'user_id' => $user->id,
            'reservation_datetime' => Carbon::now()->subDay(),
        ]);

        Reservation::factory()->create([
            'user_id' => $user->id,
            'reservation_datetime' => Carbon::now()->addDay(),
        ]);

        $this->getJson('/api/reservations')
            ->assertStatus(200)
            ->assertJsonStructure(['past', 'upcoming']);
    }

    public function test_user_can_cancel_reservation()
    {
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'reservation_datetime' => Carbon::now()->addDay(),
        ]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/reservations/{$reservation->id}")
            ->assertStatus(200)
            ->assertJson(['message' => 'Reservation cancelled successfully']);
    }

    public function test_user_cannot_cancel_past_reservation()
    {
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'reservation_datetime' => Carbon::now()->subDay(),
        ]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/reservations/{$reservation->id}")
            ->assertStatus(400);
    }

    public function test_user_can_confirm_pending_reservation()
    {
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/reservations/confirm/{$reservation->id}")
            ->assertStatus(200)
            ->assertJson(['message' => 'Reservation confirmed successfully']);
    }

    public function test_user_cannot_confirm_already_confirmed_reservation()
    {
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'status' => 'confirmed',
        ]);

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/reservations/confirm/{$reservation->id}")
            ->assertStatus(400);
    }
    public function test_user_can_update_reservation_time()
    {
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $newTime = Carbon::now()->addDays(2)->toDateTimeString();

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/reservations/{$reservation->id}", [
                'reservation_datetime' => $newTime,
            ])
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['reservation_datetime']]) 
            ->assertJson(['data' => ['reservation_datetime' => $newTime]]);
    }

    public function test_cannot_reserve_same_service_at_same_time()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $service = Service::factory()->create(['available' => true]);

        $datetime = now()->addDay()->format('Y-m-d H:i:s');

        // First reservation should succeed
        $this->actingAs($user1, 'sanctum')
            ->postJson('/api/reservations', [
                'service_id' => $service->id,
                'reservation_datetime' => $datetime,
            ])
            ->assertStatus(201);

        // Second reservation with same service and time should fail
        $this->actingAs($user2, 'sanctum')
            ->postJson('/api/reservations', [
                'service_id' => $service->id,
                'reservation_datetime' => $datetime,
            ])
            ->assertStatus(400)
            ->assertJson(['message' => 'This service is already reserved at the selected time.']);
    }

    public function test_user_cannot_reserve_past_time()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();
        $pastTime = Carbon::now()->subDays(1)->toDateTimeString();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/reservations', [
                'service_id' => $service->id,
                'reservation_datetime' => $pastTime,
            ])
            ->assertStatus(422)
            ->assertJson(['message' => 'The reservation datetime field must be a date after now.']);
    }

    public function test_user_cannot_reserve_unavailable_service()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['available' => false]);
        $futureTime = Carbon::now()->addDays(1)->toDateTimeString();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/reservations', [
                'service_id' => $service->id,
                'reservation_datetime' => $futureTime,
            ])
            ->assertStatus(400)
            ->assertJson(['message' => 'The selected service is not available.']);
    }

}
