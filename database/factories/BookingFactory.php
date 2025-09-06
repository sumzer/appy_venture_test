<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Bid;
use App\Models\Load;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'load_id' => Load::factory(),
            'bid_id' => Bid::factory(),
            'carrier_id' => User::factory()->carrier(),
            'booked_at' => now(),
        ];
    }
}
