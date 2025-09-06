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

class LoadSeeder extends Seeder
{
    public function run(): void
    {
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
