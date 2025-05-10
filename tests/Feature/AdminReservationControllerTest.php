<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Service;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminReservationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticateAdmin()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        return $this->actingAs($admin, 'sanctum');
    }

    public function test_admin_can_list_reservations()
    {
        $this->authenticateAdmin();

        Reservation::factory()->count(3)->create();

        $response = $this->getJson('/api/reservations/all');

        $response->assertOk()
                 ->assertJsonStructure(['data']);
    }

    public function test_admin_can_filter_reservations_by_status()
    {
        $this->authenticateAdmin();

        Reservation::factory()->create(['status' => 'confirmed']);
        Reservation::factory()->create(['status' => 'pending']);

        $response = $this->getJson('/api/reservations/all?status=confirmed');

        $response->assertOk()
                 ->assertJsonCount(1, 'data');
    }

    public function test_admin_can_view_a_reservation()
    {
        $this->authenticateAdmin();

        $reservation = Reservation::factory()->create();

        $response = $this->getJson("/api/reservations/{$reservation->id}");

        $response->assertOk()
                 ->assertJsonStructure(['data' => ['id', 'user', 'service', 'status']]);
    }

    public function test_admin_can_update_reservation_status()
    {
        $this->authenticateAdmin();

        $reservation = Reservation::factory()->create(['status' => 'pending']);

        $response = $this->putJson("/api/reservations/status/{$reservation->id}", [
            'status' => 'confirmed'
        ]);

        $response->assertOk()
                 ->assertJson(['message' => 'Status updated successfully']);

        $this->assertEquals('confirmed', $reservation->fresh()->status);
    }

    public function test_admin_can_cancel_reservation()
    {
        $this->authenticateAdmin();

        $reservation = Reservation::factory()->create(['status' => 'confirmed']);

        $response = $this->patch("/api/reservations/{$reservation->id}/cancel");

        $response->assertOk()
                 ->assertJson(['message' => 'Reservation cancelled successfully']);

        $this->assertEquals('cancelled', $reservation->fresh()->status);
    }
}
