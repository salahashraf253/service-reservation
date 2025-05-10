<?php

namespace Database\Factories;
use App\Models\User;
use App\Models\Service;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'service_id' => Service::factory(),
            'reservation_datetime' => now()->addDays(rand(1, 10)),
            'status' => 'pending',
        ];
    }
}
