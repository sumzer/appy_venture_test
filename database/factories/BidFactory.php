<?php

namespace Database\Factories;

use App\Models\Bid;
use App\Models\Load;
use App\Models\User;
use App\Enums\BidStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class BidFactory extends Factory
{
    protected $model = Bid::class;

    public function definition(): array
    {
        return [
            'load_id' => Load::factory(),
            'carrier_id' => User::factory()->carrier(),
            'amount' => $this->faker->numberBetween(200, 3000),
            'message' => $this->faker->optional()->sentence(8),
            'status' => BidStatus::Pending,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn() => ['status' => BidStatus::Pending]);
    }
    public function accepted(): static
    {
        return $this->state(fn() => ['status' => BidStatus::Accepted]);
    }
    public function rejected(): static
    {
        return $this->state(fn() => ['status' => BidStatus::Rejected]);
    }
}
