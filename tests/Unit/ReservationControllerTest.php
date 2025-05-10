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
        ->assertJsonStructure(['data' => ['reservation_datetime']]) // Corrected here
        ->assertJson(['data' => ['reservation_datetime' => $newTime]]);
}

}
