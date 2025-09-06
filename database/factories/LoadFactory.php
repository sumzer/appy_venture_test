<?php

namespace Database\Factories;

use App\Models\Load;
use App\Models\User;
use App\Enums\LoadStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoadFactory extends Factory
{
    protected $model = Load::class;

    public function definition(): array
    {
        $pickup = $this->faker->dateTimeBetween('+1 day', '+10 days');
        $delivery = (clone $pickup)->modify('+' . mt_rand(1, 6) . ' days');

        return [
            'shipper_id' => User::factory()->shipper(),
            'origin_country' => $this->faker->randomElement(['RS', 'DE', 'IT', 'FR', 'ES', 'GB']),
            'origin_city' => $this->faker->city(),
            'destination_country' => $this->faker->randomElement(['DE', 'IT', 'FR', 'NL', 'PL', 'GB']),
            'destination_city' => $this->faker->city(),
            'pickup_date' => $pickup,
            'delivery_date' => $delivery,
            'weight_kg' => $this->faker->numberBetween(500, 20000),
            'price_expectation' => $this->faker->numberBetween(300, 3000),
            'status' => LoadStatus::Open, // default open
            'version' => 1,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn() => ['status' => LoadStatus::Draft]);
    }
    public function open(): static
    {
        return $this->state(fn() => ['status' => LoadStatus::Open]);
    }
    public function booked(): static
    {
        return $this->state(fn() => ['status' => LoadStatus::Booked]);
    }
    public function closed(): static
    {
        return $this->state(fn() => ['status' => LoadStatus::Closed]);
    }
}
