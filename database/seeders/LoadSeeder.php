<?php

namespace Database\Seeders;

use App\Enums\BidStatus;
use App\Enums\LoadStatus;
use App\Models\Bid;
use App\Models\Booking;
use App\Models\Load;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class LoadSeeder extends Seeder
{
    public function run(): void
    {
        $shipper1 = User::where('role', 'shipper')->first();
        $carrier1 = User::where('role', 'carrier')->first();

        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();
        $now = Carbon::now();

        // draft load (no bids)
        $load1 = Load::updateOrCreate(
            ['id' => 1],
            [
                'shipper_id' => $shipper1->id,
                'origin_country' => 'ESP',
                'origin_city' => 'Langton',
                'destination_country' => 'NLD',
                'destination_city' => 'Gutmannfurt',
                'pickup_date' => $today->toDateString(),
                'delivery_date' => $tomorrow->toDateString(),
                'weight_kg' => 1000,
                'price_expectation' => 1300,
                'status' => LoadStatus::Draft->value,
                'version' => 1,
            ]
        );

        // open load (no bids)
        $load2 = Load::updateOrCreate(
            ['id' => 2],
            [
                'shipper_id' => $shipper1->id,
                'origin_country' => 'NLD',
                'origin_city' => 'Gutmannfurt',
                'destination_country' => 'ESP',
                'destination_city' => 'Langton',
                'pickup_date' => $today->add('5 day')->toDateString(),
                'delivery_date' => $tomorrow->add('5 day')->toDateString(),
                'weight_kg' => 2000,
                'price_expectation' => 5300,
                'status' => LoadStatus::Open->value,
                'version' => 1,
            ]
        );

        // open load (1 bid)
        $load3 = Load::updateOrCreate(
            ['id' => 3],
            [
                'shipper_id' => $shipper1->id,
                'origin_country' => 'ESP',
                'origin_city' => 'Langton',
                'destination_country' => 'NLD',
                'destination_city' => 'Gutmannfurt',
                'pickup_date' => $today->add('6 day')->toDateString(),
                'delivery_date' => $tomorrow->add('6 day')->toDateString(),
                'weight_kg' => 3000,
                'price_expectation' => 10000,
                'status' => LoadStatus::Open->value,
                'version' => 1,
            ]
        );

        $bid1 = Bid::updateOrCreate(
            ['id' => 1],
            [
                'load_id' => $load3->id,
                'carrier_id' => $carrier1->id,
                'amount' => 2319,
                'message' => null,
                'status' => BidStatus::Pending->value,
            ]
        );
        $bid1->timestamps = false;
        $bid1->created_at = $now;
        $bid1->updated_at = $now;
        $bid1->save();

        $shippers = User::where('role', 'shipper')->pluck('id')->all();
        $carriers = User::where('role', 'carrier')->pluck('id')->all();

        // 20 draft, 40 open, 10 booked, 5 closed
        $draftLoads = Load::factory()->count(20)->draft()->make();
        $openLoads = Load::factory()->count(40)->open()->make();
        $bookedLoads = Load::factory()->count(10)->booked()->make();
        $closedLoads = Load::factory()->count(5)->closed()->make();

        $all = $draftLoads->concat($openLoads)->concat($bookedLoads)->concat($closedLoads)->each(function ($l) use ($shippers) {
            $l->shipper_id = collect($shippers)->random();
            $l->save();
        });

        $all->where('status', LoadStatus::Open)->each(function (Load $load) use ($carriers) {
            $carrierIds = collect($carriers)->shuffle()->take(rand(1, 4));
            foreach ($carrierIds as $cid) {
                // unique (load, carrier)
                Bid::firstOrCreate(
                    ['load_id' => $load->id, 'carrier_id' => $cid],
                    ['amount' => rand(300, 2500), 'status' => BidStatus::Pending]
                );
            }
        });

        $all->where('status', LoadStatus::Booked)->each(function (Load $load) use ($carriers) {
            DB::transaction(function () use ($load, $carriers) {
                if ($load->booking)
                    return;

                $carrierIds = collect($carriers)->shuffle()->take(rand(2, 4));
                $bids = [];
                foreach ($carrierIds as $cid) {
                    $bids[] = Bid::firstOrCreate(
                        ['load_id' => $load->id, 'carrier_id' => $cid],
                        ['amount' => rand(300, 2500), 'status' => BidStatus::Pending]
                    );
                }

                $accepted = collect($bids)->random();
                Bid::where('load_id', $load->id)->where('id', '!=', $accepted->id)
                    ->update(['status' => BidStatus::Rejected]);

                $accepted->update(['status' => BidStatus::Accepted]);

                Booking::firstOrCreate([
                    'load_id' => $load->id,
                    'bid_id' => $accepted->id,
                    'carrier_id' => $accepted->carrier_id,
                ], ['booked_at' => now()]);
            });
        });
    }
}
