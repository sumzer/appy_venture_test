<?php

namespace App\Http\Controllers;

use App\Models\Load;
use App\Models\Bid;
use App\Models\Booking;
use App\Enums\BidStatus;
use App\Enums\LoadStatus;
use App\Http\Requests\Bid\StoreBidRequest;
use App\Http\Resources\BidResource;
use App\Http\Resources\LoadResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Concerns\ValidatesETag;

class BidController extends Controller
{
    use ValidatesETag;

    public function index(Request $req, Load $load)
    {
        $this->authorize('index', [Bid::class, $load]);

        $paginator = Bid::with('carrier:id,name,email')
            ->where('load_id', $load->id)
            ->latest()
            ->paginate((int) $req->query('per_page', 20))
            ->appends($req->query());

        return BidResource::collection($paginator);
    }

    public function store(StoreBidRequest $req, Load $load)
    {
        $this->authorize('store', [Bid::class, $load]);

        $exists = Bid::where('load_id', $load->id)
            ->where('carrier_id', $req->user()->id)
            ->exists();
        abort_if($exists, 409, 'You already placed a bid for this load.');

        abort_unless($load->status === LoadStatus::Open, 422, 'Load not open for bidding.');

        $bid = Bid::create([
            'load_id' => $load->id,
            'carrier_id' => $req->user()->id,
            'amount' => $req->validated()['amount'],
            'message' => $req->validated()['message'] ?? null,
            'status' => BidStatus::Pending->value,
        ]);

        return (new BidResource($bid))
            ->response()
            ->setStatusCode(201)
            ->header('Location', url("/api/bids/{$bid->id}"));
    }

    public function accept(Request $req, Bid $bid)
    {
        $this->authorize('accept', $bid);

        $this->validateETag($req, $bid->freight);

        $updatedLoad = DB::transaction(function () use ($bid) {
            $load = $bid->freight()->lockForUpdate()->first();

            Bid::where('load_id', $load->id)
                ->where('id', '!=', $bid->id)
                ->update(['status' => BidStatus::Rejected->value]);

            $bid->update(['status' => BidStatus::Accepted->value]);

            Booking::create([
                'load_id' => $load->id,
                'bid_id' => $bid->id,
                'carrier_id' => $bid->carrier_id,
                'booked_at' => now(),
            ]);

            $load->status = LoadStatus::Booked;
            $load->version++;
            $load->save();

            return $load->fresh(['booking', 'acceptedBid', 'shipper']);
        });

        return (new LoadResource($updatedLoad))
            ->response()
            ->header('ETag', $updatedLoad->etag());
    }
}
